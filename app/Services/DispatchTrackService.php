<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DispatchTrackService
{
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

        Log::channel('integracion')->info('DispatchTrack request', [$payload]);
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
            'identifier' => "hola",
            'contact_name' => "Eric Doe",
            "contact_address" => "458 Fairway Drive, Schererville, IN 46375",
            "load" => 1,
            "priority" => 1,
            "service_time" => 30,
            "items" => [
                    [
                        "code" => "SKU123",
                        "description" => "LED Monitor",
                        "quantity" => 1,
                        "extras" => [
                            [
                                "name" => "Custom Item ID",
                                "value" => "23543"
                            ],
                        ],
                    ],
                ],
            "tags" => [
                [
                    "name" => "Custom Guide Code",
                    "value" => "8932034",
                    "type" => "Tag type (string/date)"
                ]
            ],
            "groups" => [
                [
                    'name' => 'VECTOR',
                    'type' => 'CLIENTES', // Ajusta según tu lógica
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
