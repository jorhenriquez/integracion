<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Meribia\ViajePending;
use App\Models\Meribia\Grupage;
use App\Services\DispatchTrackService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SyncRutas extends Command
{
    protected $signature = 'integracion:sync-rutas';
    protected $description = 'Sincroniza rutas (VIAJES y GRUPAGE) con DispatchTrack';

    public function handle(DispatchTrackService $dispatchTrack)
    {
        $this->info('== Sincronizando rutas con DispatchTrack ==');

        $viajes = ViajePending::all();

        $this->info('Total VIAJES: ' . $viajes->count());

        // Ejemplo: combinar ambos en un solo array de rutas
        $rutas = $viajes->toArray();

        foreach ($rutas as $ruta) {
            // Buscar las Ordenes por cada ruta y agregarlas al payload
            $payload = $this->mapRutaToDispatchPayload($ruta);
            $this->line(print_r($payload));
            dd();
            $res = $dispatchTrack->createRoute($payload);
            Log::channel('integracion')->info('DispatchTrack /routes', ['payload' => $payload, 'response' => $res]);
            $this->line("- Ruta enviada: " . ($payload['identifier'] ?? 'sin id') . " | Status: " . $res['status']);
        }

        $this->info('== Proceso finalizado ==');
        return self::SUCCESS;
    }

    private function mapRutaToDispatchPayload(array $ruta): array
    {
        // Ajusta el mapeo según el contrato real del endpoint /routes
        $dispatches = DB::connection('meribia')
            ->table('CARGA')
            ->select('CARGA.REFERENCIA as identifier')
            ->join('GRUPAGE', 'GRUPAGE.CODCAR', '=', 'CARGA.CODIGO')
            ->join('VIAJES_PENDING', 'VIAJES_PENDING.VIAJE', '=', 'GRUPAGE.CODVIA')
            ->where('VIAJES_PENDING.VIAJE', $ruta['viaje'])   // 👈 ajusta aquí la columna correcta
            ->get();
        
        dd($dispatches);

        return [
            'truck' => $ruta['patente'],
            'date' => \Carbon\Carbon::parse($ruta['fecha'])->format('d-m-Y'),
            'dispatches' => $dispatches,
            // ...otros campos según lo que requiera DispatchTrack...
        ];
    }
}
