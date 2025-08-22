<?php

namespace App\Http\Controllers;

use Throwable;
use App\Support\LeadTime;
use Illuminate\Http\Request;
use App\Models\ComunaCatalog;


use App\Models\Meribia\Carga;
use App\Models\Meribia\Viaje;
use App\Models\Meribia\Pedext;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use App\Models\Meribia\Devlinext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\IntegracionService;
use App\Services\DispatchTrackService;

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

        // En base al tipo de resource, y al tipo de evento, se decide que hacer

        switch($payload['resource']) {
            case 'dispatch':
                // Procesar eventos de dispatch
                return $this->handleDispatchEvent($payload);
            case 'route':
                // Aquí podrías manejar eventos relacionados con rutas si es necesario
                Log::channel('integracion')->info('Evento de ruta recibido', $payload);
                return $this->handleRouteEvent($payload);
            case 'truck':
                // Aquí podrías manejar eventos relacionados con camiones si es necesario
                Log::channel('integracion')->info('Evento de camión recibido', $payload);
                return $this->handleTruckEvent($payload);
            default:
                Log::channel('integracion')->warning('Resource no soportado', ['resource' => $payload['resource']]);
                return response()->json(['status' => 'ignored']);
        }
        
        
    }

    public function handleDispatchEvent(array $payload)
    {
        // Aquí puedes manejar los eventos de dispatch
        Log::channel('integracion')->info('Manejando evento de dispatch', $payload);
        
        if (!isset($payload['event'])) {
            Log::channel('integracion')->info('Evento no viene en payload', $payload);
            return response()->json(['status' => 'ignored']);
        }   

        switch($payload['event']) {
            case 'created':
                // Lógica para manejar el evento de creación
                return response()->json(['status' => 'handled']);
            case 'update':
                // Lógica para manejar el evento de actualización
                return $this->handleUpdatedDispatchEvent($payload);
            case 'deleted':
                // Lógica para manejar el evento de eliminación
                return response()->json(['status' => 'handled']);
            default:
                Log::channel('integracion')->warning('Evento no soportado', ['event' => $payload['event']]);
                return response()->json(['status' => 'ignored']);
        }
    }

    public function handleRouteEvent(array $payload)
    {
        // Aquí puedes manejar los eventos de dispatch
        Log::channel('integracion')->info('Manejando evento de route', $payload);
        
        if (!isset($payload['event'])) {
            Log::channel('integracion')->info('Evento no viene en payload', $payload);
            return response()->json(['status' => 'ignored']);
        }   

        switch($payload['event']) {
            case 'created': return response()->json(['status' => 'handled']);
            case 'start': return $this->handleStartedRouteEvent($payload);
            case 'ended':   return $this->handleEndedRouteEvent($payload);
            case 'updated': return response()->json(['status' => 'handled']);
            default:
                Log::channel('integracion')->warning('Evento no soportado', ['event' => $payload['event']]);
                return response()->json(['status' => 'ignored']);
        }
    }

    public function handleTruckEvent(array $payload)
    {
        // Aquí puedes manejar los eventos de dispatch
        Log::channel('integracion')->info('Manejando evento de dispatch', $payload);
        
        if (!isset($payload['event'])) {
            Log::channel('integracion')->info('Evento no viene en payload', $payload);
            return response()->json(['status' => 'ignored']);
        }   

        switch($payload['event']) {
            case 'created':
                // Lógica para manejar el evento de creación
                return response()->json(['status' => 'handled']);
            case 'updated':
                // Lógica para manejar el evento de actualización
                return $this->handleUpdatedDispatchEvent($payload);
            case 'deleted':
                // Lógica para manejar el evento de eliminación
                return response()->json(['status' => 'handled']);
            default:
                Log::channel('integracion')->warning('Evento no soportado', ['event' => $payload['event']]);
                return response()->json(['status' => 'ignored']);
        }
    }

    public function handleUpdatedDispatchEvent(array $payload)
    {
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

            // Actualizar la carga en Meribia
            // Verificar primero si tiene CODCAR en pedido.
            if (!$pedido['codcar']) {
                $resultado = DB::connection('meribia')->table('pedext')
                    ->join('pedcli', 'pedcli.codigo', '=', 'pedext.codpdc')
                    ->join('carga', 'carga.codigo', '=', 'pedcli.codcar')
                    ->where('pedext.codigo', $pedido['pedext_id'])
                    ->select('pedcli.codcar') // Asegúrate de seleccionar el campo que necesitas
                    ->first();
                // Si el pedext tiene asociada una carga, entoncs se actualiza el valor codcar en plataforma
                if ($resultado) {
                    $pedido['codcar'] = $resultado['codcar'];
                    DB::connection('plataforma')->table('pedidos')
                        ->where('id', $pedido->id)
                        ->update([
                            'codcar'      => $pedido['codcar'],
                        ]);
                }
                else {
                    Log::channel('integracion')->warning('No se encontró CODCAR para el pedido', [
                        'codigo_cliente' => $codigoCliente,
                        'numero_documento' => $numeroDocumento
                    ]);
                    return response()->json(['error' => 'No se encontró CODCAR para el pedido'], 404);
                }
            }
            
            // Volvemos a verificar si ya tenemos CODCAR
            if ($pedido['codcar']) {
                // Modificar estado de la carga en Meribia
                DB::connection('meribia')->table('carga')
                    ->where('codigo', $pedido['codcar'])
                    ->update([
                        'NULA' => $this->estadoMeribia($estado),
                    ]);

                Log::channel('integracion')->info('Carga actualizada en Meribia', [
                    'codcar' => $pedido['codcar'],
                    'estado' => $estado,
                ]);
            } else {
                Log::channel('integracion')->warning('El pedido no tiene CODCAR asignado', [
                    'codigo_cliente' => $codigoCliente,
                    'numero_documento' => $numeroDocumento
                ]);
            }

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

    public function handleStartedRouteEvent(array $payload)
    {
        // Extraer identifier y dividirlo
        $route = $payload['route'] ?? null;
        
        if (!$route) {
            Log::channel('integracion')->warning('Formato de route inválido', ['route' => $route]);
            return response()->json(['error' => 'Invalid route'], 400);
        }

                // Supongamos que este es el valor que recibes del webhook
        $webhookDate = $payload['started_at'];

        // Lo convertimos a un objeto Carbon
        $carbonDate = Carbon::parse($webhookDate);

        // Si quieres guardarlo en UTC o en la zona horaria del servidor, puedes convertirlo
        $carbonDate->setTimezone('America/Santiago'); // o 'America/Santiago' si prefieres tu zona local

        // Luego lo formateas para guardar en la base de datos
        $formattedDate = (string) $carbonDate->format('Y-m-d H:i:s.u');

        // Buscar pedido en la BD de plataforma
        try {
            DB::connection('meribia')->table('viaje')
                ->where('sello', $route)
                ->update([
                    'HORINI'      => $formattedDate,
                ]);

        }
        catch (Throwable $e) {
            Log::channel('integracion')->error('Error al actualizar route', [
                'error' => $e->getMessage(),
                'route' => $route,
            ]);
            return response()->json(['error' => 'Error interno'], 500);
        }

        return response()->json(['status' => 'ok']);
    }

    public function handleEndedRouteEvent(array $payload)
    {
        // Extraer identifier y dividirlo
        $route = $payload['route'] ?? null;
        
        if (!$route) {
            Log::channel('integracion')->warning('Formato de route inválido', ['route' => $route]);
            return response()->json(['error' => 'Invalid route'], 400);
        }

                // Supongamos que este es el valor que recibes del webhook
        $webhookDate = $payload['ended_at'];
        // Lo convertimos a un objeto Carbon
        $carbonDate = Carbon::parse($webhookDate);

        // Si quieres guardarlo en UTC o en la zona horaria del servidor, puedes convertirlo
        $carbonDate->setTimezone('America/Santiago'); // o 'America/Santiago' si prefieres tu zona local

        // Luego lo formateas para guardar en la base de datos
        $formattedDate = (string) $carbonDate->format('Y-m-d H:i:s.u');

        // Buscar pedido en la BD de plataforma
        try {
            DB::connection('meribia')->table('viaje')
                ->where('sello', $route)
                ->update([
                    'HORFIN'      => $formattedDate,
                    'CERRADO'     => 1,
                    'FECLLE'      => $carbonDate->format('Y-m-d'),
                    'FECCIE'      => Carbon::now()->format('Y-m-d'),
                    'ESCCOM'      => 1,
                    'KILLIQ1_1'   => DB::connection('meribia')->table('viaje')->where('sello', $route)->value('kilos'),
                    'KILLIQ2_1'   => DB::connection('meribia')->table('viaje')->where('sello', $route)->value('kilos'),
                    'KILLIQAUX1_1' => DB::connection('meribia')->table('viaje')->where('sello', $route)->value('kilos'),
                    'KILLIQAUX2_1' => DB::connection('meribia')->table('viaje')->where('sello', $route)->value('kilos'),
                ]);

        }
        catch (Throwable $e) {
            Log::channel('integracion')->error('Error al actualizar route', [
                'error' => $e->getMessage(),
                'route' => $route,
            ]);
            return response()->json(['error' => 'Error interno'], 500);
        }

        return response()->json(['status' => 'ok']);
    }

    public function estadoMeribia($status){
        switch($status){
            case 1: return 'R';
            case 2: return 'C';
            case 3: return 'T';
            case 4: return 'M';
            default: return 'R';
        }
    }
}
