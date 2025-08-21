<?php

namespace App\Support;

use Carbon\Carbon;

class LeadTime
{
    /**
     * $zona: fila con flags L,M,X,J,V (0/1) y opcionalmente DIATRA/FREC.
     * $feriados: array de 'Y-m-d' a omitir (opcional).
     */
    public static function calcular(string $fechaRecepcion, array $zona, array $feriados = []): string
    {
        $dt = Carbon::parse($fechaRecepcion)->startOfDay();

        // Si recepción fue >= 17:00, sumar un día (como tu script)
        $hora = (int) Carbon::parse($fechaRecepcion)->format('H');
        if ($hora >= 17) {
            $dt->addDay();
        }

        // Avanza hasta encontrar día con ventana de reparto
        for ($i = 0; $i < 60; $i++) { // límite de seguridad 60 días
            $dow = $dt->isoWeekday(); // 1=lunes ... 7=domingo
            $esDiaValido = match ($dow) {
                1 => (int) ($zona['L'] ?? 0) === 1,
                2 => (int) ($zona['M'] ?? 0) === 1,
                3 => (int) ($zona['X'] ?? 0) === 1,
                4 => (int) ($zona['J'] ?? 0) === 1,
                5 => (int) ($zona['V'] ?? 0) === 1,
                default => false,
            };

            $esFeriado = in_array($dt->toDateString(), $feriados, true);

            if ($esDiaValido && !$esFeriado) {
                return $dt->toDateString(); // yyyy-mm-dd
            }

            $dt->addDay();
        }

        // Fallback 15 días como tu else
        return Carbon::parse($fechaRecepcion)->addDays(15)->toDateString();
    }
}
