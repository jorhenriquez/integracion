<?php

namespace App\Console\Commands;

use App\Models\Meribia\Pedext;
use App\Models\Meribia\Devlinext;
use App\Services\DispatchTrackService;
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
        $this->info('== Iniciando integración ==');

        try {
            // 1) Traer pedidos pendientes desde plataforma (MySQL)
            $q = DB::connection('plataforma')->table('pedidos')
                ->join('users', 'users.id', '=', 'pedidos.user_id')
                ->join('comunas', 'comunas.CODCOM', '=', 'pedidos.comuna_id')
                ->selectRaw('pedidos.*, users.codigo_cliente, users.rut, comunas.CODRUT');

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
                dd();
                // 3.1) Crear dispatch en DispatchTrack
                $res = $dispatchTrack->createDispatch($p);

                if (($res['response']->status ?? '') !== 'ok' || ($res['status'] ?? '500') !== '200') {
                    $msg = "Error al ingresar pedido a DispatchTrack: ".json_encode($res);
                    Log::channel('integracion')->error($msg, ['pedido' => $p['numero_documento']]);
                    $this->warn($msg);
                    continue;
                }
                $this->info("-- Creado con éxito en DispatchTrack");

                // 3.2) PEDEXT + DEVLINEXT con Eloquent (transacción en 'meribia')
                try {
                    DB::connection('meribia')->transaction(function () use ($p) {
                        // Normalización básica de dirección
                        $direccion = $this->normalize($p['direccion'] ?? '');

                        /** @var Pedext $pedext */
                        $pedext = Pedext::query()->create([
                            // AJUSTA estos nombres a tu esquema real:
                            'NUMERO'        => (string) ($p['numero_documento'] ?? ''),
                            'CODCLI'        => (int) ($p['codigo_cliente'] ?? 0),
                            'RUT'           => $p['rut'] ?? null,
                            'DIRECCION'     => $direccion,
                            'CODCOM'        => (int) ($p['comuna_id'] ?? 0),
                            'FECHA_ENTREGA' => $p['fecha_estimada'],          // yyyy-mm-dd
                            'CANTIDAD'      => (int) ($p['cantidad'] ?? 1),
                            'USRCREA'       => 'integracion',
                            'FECCREA'       => now(),                         // datetime
                        ]);

                        // Una línea de ejemplo (si tienes varias, itera)
                        $pedext->lineas()->create([
                            // AJUSTA estos nombres a tu esquema real:
                            'ITEM'      => 1,
                            'SKU'       => $p['sku'] ?? ($p['numero_material'] ?? 'SKU'),
                            'DESCRIP'   => $p['descripcion'] ?? 'Item',
                            'CANTIDAD'  => (int) ($p['cantidad'] ?? 1),
                            'NUMERO'    => (string) ($p['numero_documento'] ?? ''),
                        ]);
                    });

                    $this->info("-- Creado con éxito en PEDEXT/DEVLINEXT (Meribia)");
                    Log::channel('integracion')->info('Meribia insert OK', ['numero_documento' => $p['numero_documento']]);

                } catch (Throwable $e) {
                    Log::channel('integracion')->error("Error creando Pedext/Devlinext", ['e' => $e->getMessage(), 'pedido' => $p['numero_documento']]);
                    $this->warn("Error creando Pedext/Devlinext: ".$e->getMessage());
                    continue;
                }

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
}
