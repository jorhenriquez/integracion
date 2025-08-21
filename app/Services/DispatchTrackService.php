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

        Log::channel('integracion')->info('DispatchTrack request', ['payload' => $payload]);
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
            'identifier'       => $p['numero_documento'] ?? null,
            'client_code'      => $p['codigo_cliente'] ?? null,
            'recipient'        => [
                'name'    => $p['nombre_cliente'] ?? ($p['cliente']['NOMBRE'] ?? 'Cliente'),
                'tax_id'  => $p['rut'] ?? null,
                'address' => $direccion,
                'city'    => $p['comuna'] ?? null,
            ],
            'items'            => [
                [
                    'sku'         => $p['numero_material'] ?? 'SKU',
                    'description' => $p['descripcion'] ?? 'Item',
                    'quantity'    => (int) ($p['cantidad'] ?? 1),
                ],
            ],
            'estimated_date'   => $p['fecha_estimada'] ?? null, // YYYY-mm-dd
            'notes'            => $p['observaciones'] ?? null,
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
