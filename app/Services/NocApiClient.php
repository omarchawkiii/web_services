<?php
namespace App\Services;

use App\Models\NocInstance;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NocApiClient
{
    private NocInstance $noc;

    public function __construct(NocInstance $noc)
    {
        $this->noc = $noc;
    }

    public function get(string $path): ?array
    {
        try {
            $token = $this->resolveToken();
            if (!$token) return null;

            $response = Http::timeout(15)
                ->withToken($token)
                ->get($this->noc->getBaseUrl() . $path);

            if ($response->unauthorized()) {
                // Token expired — force re-login once
                $token = $this->login();
                if (!$token) return null;
                $response = Http::timeout(15)->withToken($token)->get($this->noc->getBaseUrl() . $path);
            }

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("NOC API {$this->noc->name} {$path} returned {$response->status()}");
            return null;

        } catch (\Exception $e) {
            Log::error("NOC API {$this->noc->name} {$path} error: " . $e->getMessage());
            return null;
        }
    }

    private function resolveToken(): ?string
    {
        if ($this->noc->sanctum_token && $this->tokenIsValid()) {
            return $this->noc->sanctum_token;
        }
        return $this->login();
    }

    private function tokenIsValid(): bool
    {
        if (!$this->noc->token_expires_at) return false;
        return $this->noc->token_expires_at->isFuture();
    }

    private function login(): ?string
    {
        try {
            $response = Http::timeout(10)->post(
                $this->noc->getBaseUrl() . '/api/mobile/login',
                [
                    'username' => $this->noc->admin_username,
                    'password' => $this->noc->admin_password,
                ]
            );

            if ($response->successful() && $response->json('token')) {
                $token = $response->json('token');

                $this->noc->update([
                    'sanctum_token'    => $token,
                    'token_expires_at' => now()->addHours(23),
                    'sync_status'      => 'online',
                ]);

                return $token;
            }

            $this->noc->update(['sync_status' => 'offline']);
            Log::warning("NOC {$this->noc->name}: login failed ({$response->status()})");
            return null;

        } catch (\Exception $e) {
            $this->noc->update(['sync_status' => 'offline']);
            Log::error("NOC {$this->noc->name}: login exception: " . $e->getMessage());
            return null;
        }
    }
}
