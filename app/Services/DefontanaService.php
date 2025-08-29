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
    private string $user; // ID de usuario en Defontana, si aplica
    private string $client; // ID de cliente en Defontana
    private string $company; // ID de empresa en Defontana


    public function __construct()
    {
        $cfg = config('services.defontana');
        $this->base    = rtrim($cfg['base_url'], '/');
        $this->timeout = $cfg['timeout'] ?? 20;
        $this->user    = $cfg['user'] ?? '';
        $this->client  = $cfg['client'] ?? '';
        $this->company = $cfg['company'] ?? '';
    }

    public function login(): string
    {
        $cfg = config('services.defontana');

        $params = [
            'client'   => $this->client,
            'company'  => $this->company,
            'user'     => $this->user,
            'password' => $cfg['password'],
            'email'    => $cfg['username'],
        ];

        $resp = Http::timeout($this->timeout)
            ->baseUrl($this->base)
            ->acceptJson()
            ->get('/api/Auth/EmailLogin', $params);

        if (!$resp->successful()) {
            throw new \RuntimeException("Defontana Auth failed: HTTP {$resp->status()} {$resp->body()}");
        }

        $json = $resp->json();
        $this->token = $json['authResult']['access_token'] ?? $json['token'] ?? $json['accessToken'] ?? null;

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
