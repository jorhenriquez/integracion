<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\Log;

class WebhookEventController extends Controller
{
    /**
     * Recibe el webhook y guarda el payload.
     */
    public function receive(Request $request)
    {
        $event = WebhookEvent::create([
            'payload' => $request->all(),
            'origen' => $request->headers->get('origen') ?? 'desconocido',
            'ip' => $request->ip() ?? $request->server('REMOTE_ADDR'),
            'referer' => $request->headers->get('referer') ?? 'desconocido',
            'user_agent' => $request->header('User-Agent') ?? 'desconocido',
            'processed' => false,
            'estado' => 0,
            'received_at' => now()
        ]);

        Log::channel('webhook')->info('Webhook recepcionado', $event);

        return response()->json([
            'message' => 'Webhook recibido correctamente',
            'event_id' => $event->id,
            'event' => $event
        ], 201);
    }

    /**
     * Marca un evento como procesado.
     */
    public function markAsProcessed($id)
    {
        $event = WebhookEvent::findOrFail($id);
        $event->processed = true;
        $event->save();

        return response()->json([
            'message' => 'Evento marcado como procesado',
            'event_id' => $event->id
        ]);
    }

    /**
     * Lista eventos recibidos (opcional).
     */
    public function index()
    {
        $events = WebhookEvent::latest()->paginate(20);

        return response()->json($events);
    }

    /**
     * Ver un evento específico.
     */
    public function show($id)
    {
        $event = WebhookEvent::findOrFail($id);

        return response()->json($event);
    }
}