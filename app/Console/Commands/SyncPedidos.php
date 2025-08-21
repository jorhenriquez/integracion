<?php

namespace App\Console\Commands;

use App\Models\Meribia\Pedext;
use App\Models\Meribia\Devlinext;
use App\Models\ComunaCatalog;
use App\Services\DispatchTrackService;
use App\Services\IntegracionService;
use App\Support\LeadTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncPedidos extends Command
{
    protected $signature = 'integracion:sync-pedidos {--documento=}';
    protected $description = 'Sincroniza pedidos: Plataforma -> DispatchTrack / Meribia -> actualiza plataforma';

    public function handle(DispatchTrackService $dispatchTrack)
    {

        // Ejemplo en el Command handle():
        $count = app(IntegracionService::class)->syncComunas();
        $this->info("Catálogo de comunas sincronizado: {$count} filas.");

        $this->info('== Iniciando integración ==');

        try {
            // 1) Traer pedidos pendientes desde plataforma (MySQL)
            $q = DB::connection('plataforma')->table('pedidos')
                ->join('users', 'users.id', '=', 'pedidos.user_id')
                ->join('comunas', 'comunas.CODCOM', '=', 'pedidos.comuna_id')
                ->selectRaw('pedidos.*, users.codigo_cliente, users.rut, comunas.CODRUT, comunas.CODCD');

            if ($doc = $this->option('documento')) {
                $q->where('numero_documento', $doc);
            } else {
                $q->where('esta_respaldado', 0)
                  ->where('estado_recepcion', 3);
            }

            $pedidos = $q->orderBy('pedidos.id')->get()->map(fn($r) => (array) $r)->all();

            $this->line('Total pedidos: ' . count($pedidos));
            Log::channel('integracion')->info('Pedidos a procesar', ['count' => count($pedidos)]);
            
            if (!count($pedidos)) {
                $this->info('No hay pedidos pendientes.');
                return self::SUCCESS;
            }

            // 2) Cargar calendario (Meribia) y clientes
            $zonas = DB::connection('meribia')->select("
                SELECT CAST(COMUNAS_AU.CODCOM AS int) AS CODIGO, COMUNAS_AU.*, RUTAS.DIATRA,
                       ZONAS.L, ZONAS.M, ZONAS.X, ZONAS.J, ZONAS.V,
                       ZONAS.L + ZONAS.M*2 + ZONAS.X*4 + ZONAS.J*8 + ZONAS.V*16 as FREC
                FROM COMUNAS_AU
                INNER JOIN RUTAS ON RUTAS.CODIGO = COMUNAS_AU.CODRUT
                INNER JOIN ZONAS ON ZONAS.CODIGO = COMUNAS_AU.CODCOM
                ORDER BY COMUNAS_AU.CODCOM
            ");

            $calendario = [];
            foreach ($zonas as $z) {
                $z = (array) $z;
                $calendario[(int) $z['CODIGO']] = $z;
            }

            $clientesRows = DB::connection('meribia')->select("SELECT CODIGO, NOMBRE, NOMFIS, NIF FROM CLIENTES");
            $clientes = [];
            foreach ($clientesRows as $c) {
                $c = (array) $c;
                $clientes[(int) $c['CODIGO']] = $c;
            }
        
            // 3) Procesar cada pedido
            foreach ($pedidos as $p) {

                $this->line("- Pedido: ".$p['numero_documento']);

                // Determinar fecha base (recepción) y fecha estimada
                $fechaAuditoria = $p['fecha_auditoria'] ?? now()->toDateTimeString();
                $p['fecha_estimada'] = isset($calendario[$p['comuna_id']])
                    ? LeadTime::calcular($fechaAuditoria, $calendario[$p['comuna_id']], /* feriados */ [])
                    : now()->addDays(15)->toDateString();

                $this->line("--- Recepción: $fechaAuditoria");
                $this->line("--- Entrega: ".$p['fecha_estimada']);

                // Datos de cliente desde Meribia según codigo_cliente
                $codigoCliente = (int) ($p['codigo_cliente'] ?? 0);
                $p['cliente'] = $clientes[$codigoCliente] ?? [
                    'CODIGO' => $codigoCliente, 'NOMBRE' => 'Cliente', 'NOMFIS' => null, 'NIF' => null,
                ];
                
                // 3.1) Crear dispatch en DispatchTrack
                $res = $dispatchTrack->createDispatch($p);
                
                if (($res['response']->status ?? '') !== 'ok' || ($res['status'] ?? '500') !== '200') {
                    $msg = "Error al ingresar pedido a DispatchTrack: ".json_encode($res);
                    Log::channel('integracion')->error($msg, ['pedido' => $p['numero_documento']]);
                    $this->warn($msg);
                    continue;
                }
                $this->info("-- Creado con éxito en DispatchTrack");

                if ($p['agencia_id'] ?? null) {
                    $cd = ComunaCatalog::where('CODCD', $p['agencia_id'])->first();
                }

                // 3.2) PEDEXT + DEVLINEXT con Eloquent (transacción en 'meribia')
                try {
                    DB::connection('meribia')->transaction(function () use ($p) {
                        // Normalización básica de dirección
                        $direccion = $this->normalize($p['direccion'] ?? '');

                        /** @var Pedext $pedext */
                        $pedext = Pedext::query()->create([
                            // AJUSTA estos nombres a tu esquema real:
                            'TIPO'        => 'D',
                            'NIFCLI'      => $p['cliente']['NIF'],
                            'NIFCON' => $cd->nif ?? null,
                            'NIFREM'      => $p['rut'] ?? null,
                            'NIFDES'     => $p['cliente']['NIF'],
                            'NOMCOM'    =>'API',
                            'FECHA'         => null,
                            'FECLIM' => null,
                            'REFERENCIA' => $p['codigo_cliente'].'-'.$p['numero_documento'],
                            'NUMPLA' => '0',
                            'CMR' => $p['folio_interno'] ?? '',
                            'CRT' => '',
                            'DTMI' => '',
                            'NOMTIPMER' => '1. Estandar',
                            'NOMCAT' => '',
                            'NOMCLA' => '',
                            'CODPRY' => '',
                            'CODRUT' => 'cambiar este codigo',
                            'PGORI' => 13677,
                            'ORIGEN' => 'API',
                            'DIRORI1' => 'CARGA POR SISTEMA',
                            'DIRORI2' => '',
                            'CPORI' => 'API',
                            'POBLAORI' => 'Comuna API',
                            'PROVIORI' => 'API',
                            'NOMPAIORI' => 'CHILE',
                            'ZONORI' => 'API',
                            'FECHORORI' => now()->toDateString(), // fecha actual
                            'HASFECHORORI' => now()->toDateString(), // fecha actual
                            'REFORI' => $p['codigo_cliente'].'-'.$p['numero_documento'],
                            'CONORI' => '',
                            'TELORI' => '',
                            'MOVORI' => '',
                            'PGDES' => 0,
                            'DESTINO' => $p['destino'] ?? 'Sin información',
                            'DIRDES1' => $direccion,
                            'DIRDES2' => '',
                            'CPDES' => $p['comuna_id'].'0000',
                            'POBLADES' => $p['comuna'] ?? 'Sin información',
                            'PROVIDES' => $p['provincia'] ?? '',
                            'NOMPAIDES' => $p['CODCOM'],
                            'ZONDES' => $p['zona'] ?? 'Sin información',
                            'FECHORDES' => $p['fecha_estimada'],
                            'HASFECHORDES' => $p['fecha_estimada'],
                            'REFDES' => $p['codigo_cliente'].'-'.$p['numero_documento'],
                            'CONDES' => '',
                            'TELDES' => '',
                            'MOVDES' => '',
                            'NOMMER' => 'Varios',
                            'PESO' => (float) ($p['peso'] ?? 0),
                            'NOMENV' => 'Palet',
                            'ENVASES' => (int) ($p['cantidad'] ?? 1),
                            'UNIDADES' => 0,
                            'VOLUMEN' => (float) ($p['volumen'] ?? 0),
                            'METROS' => 0,
                            'DOCUMENTOS' => $p['numero_documento'] ?? '',
                            'NUMDOC' => 1,
                            'REQMAT' => '',
                            'NOMTIPMAT' => '',
                            'TIPFAC' => 'K',
                            'CANTIDAD' => 0,
                            'PREUNI' => 0,
                            'VALMER' => $p['valor_neto'] ?? 0,
                            'TELEFONO' => '',
                            'DESCRIPCION' => '',
                            'CLAVE1' => '',
                            'OBSERVACIONES' => '',
                            'PROCESANDO' => 0,
                            'PROCESADO' => 0,
                            'ESTCARGA' => NULL,
                            'NOMDELCLI' => NULL,
                            'INSCAR' => NULL,
                            'MEDIO' => NULL,
                            'DISTRIBUCION' => NULL,
                            'NOTASERROR' => '',
                            'ERROR' => 0,
                            'BORRADO' => 0,
                            'CODPDC' => 0,
                        ]);

                        /*
                        
                        // Una línea de ejemplo (si tienes varias, itera)
                        $pedext->lineas()->create([
                            // AJUSTA estos nombres a tu esquema real:
                            'ITEM'      => 1,
                            'SKU'       => $p['sku'] ?? ($p['numero_material'] ?? 'SKU'),
                            'DESCRIP'   => $p['descripcion'] ?? 'Item',
                            'CANTIDAD'  => (int) ($p['cantidad'] ?? 1),
                            'NUMERO'    => (string) ($p['numero_documento'] ?? ''),
                        ]);
                        */
                    });
                    

                    $this->info("-- Creado con éxito en PEDEXT/DEVLINEXT (Meribia)");
                    Log::channel('integracion')->info('Meribia insert OK', ['numero_documento' => $p['numero_documento']]);

                } catch (Throwable $e) {
                    Log::channel('integracion')->error("Error creando Pedext/Devlinext", ['e' => $e->getMessage(), 'pedido' => $p['numero_documento']]);
                    $this->warn("Error creando Pedext/Devlinext: ".$e->getMessage());
                    continue;
                }
                dd();
                // 3.3) Actualizar plataforma (esta_respaldado=1, fecha_estimada)
                try {
                    DB::connection('plataforma')->table('pedidos')
                        ->where('id', $p['id'])
                        ->update([
                            'esta_respaldado' => 1,
                            'fecha_estimada'  => $p['fecha_estimada'],
                            'updated_at'      => now(),
                        ]);

                    $this->info("-- Modificado con éxito en plataforma");
                    Log::channel('integracion')->info('Pedido respaldado', [
                        'codigo_cliente'   => $p['codigo_cliente'] ?? null,
                        'numero_documento' => $p['numero_documento'] ?? null
                    ]);

                } catch (Throwable $e) {
                    Log::channel('integracion')->error("Error al actualizar plataforma", ['e' => $e->getMessage(), 'pedido' => $p['numero_documento']]);
                    $this->warn("Error al actualizar plataforma: ".$e->getMessage());
                    continue;
                }
            }

            $this->info('== Integración finalizada ==');
            return self::SUCCESS;

        } catch (Throwable $e) {
            Log::channel('integracion')->error('Fallo global integración', ['e' => $e->getMessage()]);
            $this->error('Error global: '.$e->getMessage());
            return self::FAILURE;
        }
    }

    private function normalize(?string $s): string
    {
        if ($s === null) return '';
        $s = mb_convert_encoding($s, 'UTF-8', 'UTF-8');
        return trim(preg_replace('/\s+/', ' ', $s));
    }

    protected function insertarPedext($pedido, ?array $cliente): Pedext
    {
        // Mapea campos reales de tu PEDEXT:
        $data = [
            'NUMERO'        => (string) ($pedido->numero_documento ?? ''),
            'CODCLI'        => (int) ($pedido->codigo_cliente ?? 0),
            'RUT'           => $pedido->rut ?? null,
            'DIRECCION'     => $pedido->direccion ?? null,
            'CODCOM'        => (int) ($pedido->comuna_id ?? 0),
            'FECHA_ENTREGA' => $pedido->fecha_estimada,    // yyyy-mm-dd
            'CANTIDAD'      => (int) ($pedido->cantidad ?? 1),
            'USRCREA'       => 'integracion',
            'FECCREA'       => now(),                      // datetime
        ];

        /** @var Pedext $pedext */
        $pedext = Pedext::query()->create($data); // conexión 'meribia' ya configurada en el modelo
        return $pedext;
    }

    protected function insertarDevlinext($pedido, Pedext $pedext): Devlinext
    {
        // Si tienes más de una línea, itéralas; aquí va 1 de ejemplo:
        $line = [
            'ID_PEDEXT' => $pedext->getKey(),
            'ITEM'      => 1,
            'SKU'       => $pedido->sku ?? ($pedido->numero_material ?? null),
            'DESCRIP'   => $pedido->descripcion ?? 'Item',
            'CANTIDAD'  => (int) ($pedido->cantidad ?? 1),
            'NUMERO'    => (string) ($pedido->numero_documento ?? ''),
        ];

        /** @var Devlinext $dev */
        $dev = Devlinext::query()->create($line);

        return $dev;
    }
}
