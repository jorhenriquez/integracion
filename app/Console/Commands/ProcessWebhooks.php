<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\Http;


class ProcessWebhooks extends Command
{
    protected $signature = 'webhooks:process';
    protected $description = 'Procesa los webhooks que aún no han sido marcados como procesados';

    public function handle()
    {
        $pending = WebhookEvent::where('processed', false)->get();

        if ($pending->isEmpty()) {
            $this->info('No hay webhooks pendientes.');
            return;
        }

        $this->info("Procesando {$pending->count()} webhook(s)...");

        foreach ($pending as $event) {
            try {
                $payload = is_string($event->payload) ? json_decode($event->payload, true) : $event->payload;

                $response = Http::withHeaders(['X-AUTH-TOKEN' => 'SuperJorge2001',])
                                ->post('http://dispatch.supertrans.cl/webhook/dispatchtrack', $payload);

                if ($response->successful()) {
                    $event->processed = true;
                    $event->estado = 1; // Marca como error
                    $event->save();
                    $event->save();
                    $this->successMessage("✓ Webhook ID {$event->id} procesado correctamente.");
                } else {
                    $data = $response->json();
                    $error = $data['error'];
                    $event->observaciones = $error;
                    $event->estado = 2; // Marca como error
                    $event->save();
                    $this->errorMessage("✗ Falló el procesamiento del webhook ID {$event->id}. Código: " . $error);
                }
            } catch (\Exception $e) {
                $event->observaciones = $e->getMessage();
                $event->estado = 2; // Marca como error
                $event->save();
                $this->errorMessage("✗ Error al enviar el webhook ID {$event->id}: " . $e->getMessage());
            }
        }

        $this->info('Proceso de webhooks completado.');
    }

    protected function errorMessage($message)
    {
        $this->output->writeln("\e[41m\e[97m {$message} \e[0m");
    }

    protected function successMessage($message)
    {
        $this->output->writeln("\e[42m\e[97m {$message} \e[0m");
    }
}