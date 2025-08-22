<?php 

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IntegracionService
{
    public function syncComunas(): int
    {
        // 1) Traer comunas desde plataforma (DISTINCT)
        $rows = DB::connection('plataforma')
            ->table('comunas')
            ->select('CODCD','NOMCD')
            ->groupBy('CODCD','NOMCD')
            ->get();

        // 2) Preparar payload con NIF
        // Regla simplificada: '48.100.' + CODCD con 3 dígitos (003, 012, 120)
        $now = now();
        $payload = [];
        foreach ($rows as $r) {
            $cod = (int) $r->CODCD;
            $nif = '48.100.' . str_pad((string)$cod, 3, '0', STR_PAD_LEFT).'-1';
            $payload[] = [
                'codcd'      => $cod,
                'nomcd'      => (string) $r->NOMCD,
                'nif'        => $nif,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // 3) UPSERT en la tabla interna (actualiza nomcd/nif si cambian)
        if (!empty($payload)) {
            DB::table('comuna_catalog')->upsert(
                $payload,
                ['codcd'],                // índice único/PK
                ['nomcd','nif','updated_at']
            );
        }

        Log::info('Sync comunas completado', ['total' => count($payload)]);
        return count($payload);
    }
}