<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Meribia\ViajePending;
use App\Models\Meribia\Grupage;
use App\Services\DispatchTrackService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Meribia\Pedext;
use App\Models\Meribia\Devlinext;
use App\Models\ComunaCatalog;
use App\Services\IntegracionService;
use App\Support\LeadTime;
use Throwable;

class SyncFacturas extends Command
{
    protected $signature = 'integracion:mod-pedidos {--documento=}';
    protected $description = 'Modificar fecha de pedidos: Plataforma -> DispatchTrack / Meribia -> actualiza plataforma';

    public function handle()
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
                ->selectRaw('pedidos.*, users.codigo_cliente, users.rut, comunas.CODRUT, comunas.CODCD')
                ->where('pedidos.fecha_entrega', '>=', '2025-08-11');
            
            $pedidos = $q->orderBy('pedidos.id')->get()->map(fn($r) => (array) $r)->all();
            $this->line('Total pedidos: ' . count($pedidos));


            foreach($pedidos as $p){
                $referencia = $p['codigo_cliente'].'-'.$p['numero_documento'];
                $this->line('Pedido: ' . $referencia);
                
                $pedcli = DB::connection('meribia')->select("SELECT * FROM PEDCLI WHERE REFERENCIA = '".$referencia."' AND ESTADO = 'P'");
                
                if (!$pedcli)
                    continue;

                $this->line('Total pedidos en Meribia: ' . $pedcli['REFERENCIA'] ?? 'No existe');  
                DB::connection('meribia')->select("UPDATE PEDCLI SET FECSAL = '".$p['fecha_entrega']."' WHERE REFERENCIA = '".$referencia."' AND ESTADO = 'P'");
                dd($pedcli);
                        /*->update(['FECSAL' => trim($p['fecha_entrega'] ?? ''),]);
                        */
            }

        }
        catch (Throwable $e) {
            $this->warn("Error al obtener pedidos de plataforma: ".$e->getMessage());
        }
    }
}