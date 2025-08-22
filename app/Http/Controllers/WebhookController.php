<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Meribia\Carga;
use App\Models\Meribia\Viaje;


use App\Models\Meribia\Pedext;
use App\Models\Meribia\Devlinext;
use App\Models\ComunaCatalog;
use App\Services\DispatchTrackService;
use App\Services\IntegracionService;
use App\Support\LeadTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class WebhookController extends Controller
{
    public function dispatchtrack(Request $request)
    {
        // Validar token de autenticación
        if ($request->header('X-AUTH-TOKEN') !== 'SuperJorge2001') {
            Log::channel('integracion')->warning('Token inválido', [
                'ip' => $request->ip(),
                'headers' => $request->headers->all()
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = $request->all();
        Log::channel('integracion')->info('Webhook recibido', $payload);

        // Validar que el resource sea 'dispatch'
        if ($payload['resource'] !== 'dispatch') {
            return response()->json(['status' => 'ignored']);
        }

        // Extraer identifier y dividirlo
        $identifier = $payload['identifier'] ?? null;
        if (!$identifier || !str_contains($identifier, '-')) {
            Log::channel('integracion')->warning('Formato de identifier inválido', ['identifier' => $identifier]);
            return response()->json(['error' => 'Invalid identifier'], 400);
        }

        [$codigoCliente, $numeroDocumento] = explode('-', $identifier, 2);

        // Buscar pedido en la BD de plataforma
        try {
            $pedido = DB::connection('plataforma')->table('pedidos')
                ->join('users', 'users.id', '=', 'pedidos.user_id')
                ->where('users.codigo_cliente', $codigoCliente)
                ->where('pedidos.numero_documento', $numeroDocumento)
                ->first();

            if (!$pedido) {
                Log::channel('integracion')->warning('Pedido no encontrado', [
                    'codigo_cliente' => $codigoCliente,
                    'numero_documento' => $numeroDocumento
                ]);
                return response()->json(['error' => 'Pedido no encontrado'], 404);
            }

            // Actualizar pedido
            $estado = $payload['status']+1;

            DB::connection('plataforma')->table('pedidos')
                ->where('id', $pedido->id)
                ->update([
                    'updated_at'      => now(),
                    'estado_documento_id' => $estado,
                ]);

            Log::channel('integracion')->info('Pedido actualizado', [
                'codigo_cliente' => $codigoCliente,
                'numero_documento' => $numeroDocumento,
                'estado_documento_id' => $estado,
            ]);

        } catch (Throwable $e) {
            Log::channel('integracion')->error('Error al actualizar pedido', [
                'error' => $e->getMessage(),
                'codigo_cliente' => $codigoCliente,
                'numero_documento' => $numeroDocumento
            ]);
            return response()->json(['error' => 'Error interno'], 500);
        }

        return response()->json(['status' => 'ok']);
    }
}
