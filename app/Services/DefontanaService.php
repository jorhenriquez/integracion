<?php
// app/Services/DefontanaService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DefontanaService
{
    private string $base;
    private int $timeout;
    private ?string $token = null;

    public function __construct()
    {
        $cfg = config('services.defontana');
        $this->base    = rtrim($cfg['base_url'], '/');
        $this->timeout = $cfg['timeout'] ?? 20;
    }

    public function login(): string
    {
        $cfg = config('services.defontana');

        $resp = Http::timeout($this->timeout)
            ->baseUrl($this->base)
            ->acceptJson()
            ->post('/Auth/EmailLogin', [
                'email'    => $cfg['username'],
                'password' => $cfg['password'],
            ]);

        if (!$resp->successful()) {
            throw new \RuntimeException("Defontana Auth failed: HTTP {$resp->status()} {$resp->body()}");
        }

        // Swagger indica que hay endpoints de Auth; toma el token del JSON devuelto por tu tenant.
        $json = $resp->json();
        // Ajusta la clave exacta:
        $this->token = $json['token'] ?? $json['accessToken'] ?? null;

        if (!$this->token) {
            throw new \RuntimeException('Defontana: token ausente en respuesta de Auth');
        }

        return $this->token;
    }

    private function client()
    {
        if (!$this->token) $this->login();

        return Http::timeout($this->timeout)
            ->baseUrl($this->base)
            ->withHeaders([
                'Authorization' => 'Bearer '.$this->token, // esquema típico Bearer
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ]);
    }

    /**
     * Crear venta (factura/boleta). Ajusta el payload según tu Swagger.
     */
    public function saveSale(array $payload): array
    {
        $resp = $this->client()->post('/Sale/SaveSale', $payload);

        Log::channel('integracion')->info('Defontana SaveSale', [
            'status'  => $resp->status(),
            'req'     => $payload,
            'resp'    => $resp->json(),
        ]);

        if (!$resp->successful()) {
            throw new \RuntimeException("SaveSale error HTTP {$resp->status()}: ".$resp->body());
        }

        return $resp->json();
    }
}
