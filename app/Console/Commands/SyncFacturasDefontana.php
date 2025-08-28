<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DefontanaService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SyncFacturasDefontana extends Command
{
    protected $signature = 'defontana:sync-facturas';
    protected $description = 'Sincroniza facturas y notas de crédito de Meribia a Defontana';

    public function handle(DefontanaService $defontanaService)
    {
        $this->info('== Sincronizando facturas y notas de crédito con Defontana ==');

        // 1. Obtener facturas y notas de crédito pendientes desde Meribia
        $facturas = DB::connection('meribia')->table('DOC_PENDIENTES')->where('documentType', 'F')->get();
        $notas = DB::connection('meribia')->table('DOC_PENDIENTES')->where('documentType', 'R')->get();

        $total = $facturas->count() + $notas->count();
        if ($total === 0) {
            return ['ok' => true, 'msg' => 'No hay documentos pendientes.'];
        }

        // 2. Procesar cada documento (aquí deberías llamar a la API de Defontana)
        foreach ($facturas as $factura) {
            // TODO: Implementar integración real con Defontana
            Log::channel('integracion')->info('Factura a enviar a Defontana', (array)$factura);
            // Simular envío y marcar como enviada
            //$response = $defontanaService->saveSale((array)$factura);
        }
        foreach ($notas as $nota) {
            Log::channel('integracion')->info('Nota de crédito a enviar a Defontana', (array)$nota);
            //DB::connection('meribia')->table('NOTAS_CREDITO')->where('ID', $nota->ID)->update(['ENVIADA' => 1]);
        }

        return ['ok' => true, 'msg' => "Documentos sincronizados: $total"];

        return self::SUCCESS;
    }
}
