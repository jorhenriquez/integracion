<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DispatchTrackService
{
    /**
     * Envía una ruta al endpoint /routes de DispatchTrack
     */

    public function truckExists(string $patente): bool
    {
        $cfg = config('services.dispatchtrack');

        $client = Http::timeout($cfg['timeout'] ?? 15)
            ->baseUrl($cfg['base_url'] ?? '')
            ->withHeaders([
                $cfg['token_name'] ?? 'Authorization' => $cfg['token'] ?? '',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);

        $resp = $client->get('/trucks', ['identifier' => $patente]);

        Log::channel('integracion')->info('DispatchTrack /trucks (check) request', ['identifier' => $patente]);
        Log::channel('integracion')->info('DispatchTrack /trucks (check) response', ['status' => $resp->status(), 'body' => $resp->json()]);

        if ($resp->successful()) {
            //$data = $resp->json();
            //return !empty($data) && isset($data[0]) && $data[0]['identifier'] === $patente;
            return true;
        }
        return false;
    }
    
    public function createTruck(string $patente): array
    {
        $cfg = config('services.dispatchtrack');

        $client = Http::timeout($cfg['timeout'] ?? 15)
            ->baseUrl($cfg['base_url'] ?? '')
            ->withHeaders([
                $cfg['token_name'] ?? 'Authorization' => $cfg['token'] ?? '',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);

        $payload = [
            'identifier' => $patente,
            'vehicle_type' => 'truck',
            // Otros campos según lo que requiera DispatchTrack...
        ];

        $resp = $client->post('/trucks', $payload);

        Log::channel('integracion')->info('DispatchTrack /trucks request', $payload);
        Log::channel('integracion')->info('DispatchTrack /trucks response', ['status' => $resp->status(), 'body' => $resp->json()]);

        return [
            'status'   => (string) $resp->status(),
            'response' => (object) [
                'status'    => $resp->successful() ? 'ok' : 'error',
                'response'  => $resp->json(),
            ],
        ];
    }


    public function createRoute(array $ruta): array
    {
        $cfg = config('services.dispatchtrack');

        $client = Http::timeout($cfg['timeout'] ?? 15)
            ->baseUrl($cfg['base_url'] ?? '')
            ->withHeaders([
                $cfg['token_name'] ?? 'Authorization' => $cfg['token'] ?? '',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);

        //$payload = $this->mapRutaToDispatchPayload($ruta);
        $payload = $ruta;
        $resp = $client->post('/routes', $payload);

        Log::channel('integracion')->info('DispatchTrack /routes request', $payload);
        Log::channel('integracion')->info('DispatchTrack /routes response', ['status' => $resp->status(), 'body' => $resp->json()]);

        return [
            'status'   => (string) $resp->status(),
            'response' => (object) [
                'status'    => $resp->successful() ? 'ok' : 'error',
                'response'  => $resp->json(),
            ],
        ];
    }

    /**
     * Mapea los datos de la ruta al formato esperado por DispatchTrack /routes
     */
    private function mapRutaToDispatchPayload(array $ruta): array
    {
        // Ajusta el mapeo según el contrato real del endpoint /routes
        return [
            'identifier' => $ruta['identifier'] ?? $ruta['ID'] ?? $ruta['id'] ?? uniqid('ruta_'),
            'name' => $ruta['name'] ?? $ruta['NOMBRE'] ?? $ruta['nombre'] ?? 'Ruta',
            'date' => $ruta['date'] ?? $ruta['FECHA'] ?? $ruta['fecha'] ?? now()->toDateString(),
            // ...otros campos según lo que requiera DispatchTrack...
        ];
    }
    public function createDispatch(array $pedido): array
    {
        $cfg = config('services.dispatchtrack');

        $client = Http::timeout($cfg['timeout'] ?? 15)
            ->baseUrl($cfg['base_url'] ?? '')
            ->withHeaders([
                $cfg['token_name'] ?? 'Authorization' => $cfg['token'] ?? '',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);

        // Mapea aquí el payload esperado por tu endpoint:
        $payload = $this->mapPedidoToDispatchPayload($pedido);

        $resp = $client->post('/dispatches', $payload); // <- Ajusta path real

        Log::channel('integracion')->info('DispatchTrack request', $payload);
        Log::channel('integracion')->info('DispatchTrack response', ['status' => $resp->status(), 'body' => $resp->json()]);

        return [
            'status'   => (string) $resp->status(),
            'response' => (object) [
                'status'    => $resp->successful() ? 'ok' : 'error',
                'response'  => $resp->json(),
            ],
        ];
    }

    private function mapPedidoToDispatchPayload(array $p): array
    {
        // Normaliza strings básicos
        $direccion = $this->normalize($p['direccion'] ?? '');

        // EJEMPLO de mapeo. Ajusta claves al contrato real del API:
        return [
            'identifier' => $p['codigo_cliente'].'-'.$p['numero_documento'] ?? '',
            'contact_name' => $p['destino'] ?? 'Cliente Desconocido',
            "contact_address" => $direccion.', '.$p['comuna'],
            "load" => 1,
            "priority" => 1,
            "items" => [
                    [
                        "code" => "",
                        "description" => "Bultos",
                        "quantity" => $p['cantidad'] ?? 1,
                    ],
                ],
            "tags" => [
                [
                    "name" => "Entrega Estimada",
                    "value" => $p['fecha_estimada'],
                    "type" => "Tag type (string/date)"
                ]
            ],
            "groups" => [
                [
                    'name' => $p['cliente']['NOMBRE'],
                    'category' => 'CLIENTES', // Ajusta según tu lógica,
                    'force_create' => true, // Si necesitas forzar creación de grupo
                ],
            ],
        ];
    }

    private function normalize(?string $s): string
    {
        if ($s === null) return '';
        // Equivalente del str_replace utf16->utf8 del script original (si usabas mapeos específicos)
        $s = mb_convert_encoding($s, 'UTF-8', 'UTF-8');
        return trim(preg_replace('/\s+/', ' ', $s));
    }
}
