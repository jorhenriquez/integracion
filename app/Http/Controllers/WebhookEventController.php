<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WebhookEvent;

class WebhookEventController extends Controller
{
    /**
     * Recibe el webhook y guarda el payload.
     */
    public function receive(Request $request)
    {
        $event = WebhookEvent::create([
            'payload' => $request->all(),
            'processed' => false,
            'received_at' => now()
        ]);

        return response()->json([
            'message' => 'Webhook recibido correctamente',
            'event_id' => $event->id
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