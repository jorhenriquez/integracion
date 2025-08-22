<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Meribia\Carga;
use App\Models\Meribia\Viaje;

class WebhookController extends Controller
{
    public function dispatchtrack(Request $request)
    {
        // Validar header X-AUTH-TOKEN
        if ($request->header('X-AUTH-TOKEN') !== 'SuperJorge2001') {
            Log::channel('integracion')->warning('Webhook rechazado por token inválido', [
                'ip' => $request->ip(),
                'headers' => $request->headers->all()
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = $request->all();
        Log::channel('integracion')->info('Webhook recibido', $payload);

        // Ejemplo: actualizar CARGA y VIAJE según datos recibidos
        if (isset($payload['carga_id'], $payload['viaje_id'])) {
            Carga::where('CODIGO', $payload['carga_id'])->update([
                // 'CAMPO' => $payload['valor'],
            ]);
            Viaje::where('CODIGO', $payload['viaje_id'])->update([
                // 'CAMPO' => $payload['valor'],
            ]);
        }

        // Aquí puedes agregar lógica para actualizar la plataforma

        return response()->json(['status' => 'ok']);
    }
}
