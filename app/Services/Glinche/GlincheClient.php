<?php

namespace App\Services\Glinche;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GlincheClient
{
    private const TOKEN_CACHE_KEY = 'glinche.api.token';

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $email,
        private readonly string $password,
        private readonly int $timeout = 15,
        private readonly int $tokenTtl = 50,
    ) {
    }

    public function vehicles(): array
    {
        $response = $this->authenticated(fn (PendingRequest $http) => $http->get('partners/vehicles'));

        $payload = $response->json();

        if (is_array($payload) && array_key_exists('data', $payload) && is_array($payload['data'])) {
            $payload = $payload['data'];
        }

        if (! is_array($payload) || ! array_is_list($payload)) {
            throw new GlincheApiException('Réponse inattendue de GET /partners/vehicles.');
        }

        return $payload;
    }

    private function authenticated(callable $call): Response
    {
        $response = $this->send(fn () => $call($this->http()->withToken($this->token())));

        if ($response->status() === 401) {
            $this->forgetToken();
            $response = $this->send(fn () => $call($this->http()->withToken($this->token())));
        }

        if ($response->failed()) {
            throw new GlincheApiException(sprintf(
                'Appel API Glinche en échec (HTTP %d).',
                $response->status(),
            ));
        }

        return $response;
    }

    public function token(): string
    {
        return Cache::remember(
            self::TOKEN_CACHE_KEY,
            now()->addMinutes($this->tokenTtl),
            fn () => $this->login(),
        );
    }

    public function forgetToken(): void
    {
        Cache::forget(self::TOKEN_CACHE_KEY);
    }

    private function login(): string
    {
        if ($this->email === '' || $this->password === '') {
            throw new GlincheApiException(
                'Identifiants API manquants : renseigner GLINCHE_API_EMAIL et GLINCHE_API_PASSWORD dans le .env.'
            );
        }

        $response = $this->send(fn () => $this->http()->post('partners/login', [
            'email' => $this->email,
            'password' => $this->password,
        ]));

        $token = $response->json('token');

        if ($response->failed() || ! is_string($token) || $token === '') {
            throw new GlincheApiException(sprintf(
                'Authentification à l\'API Glinche refusée (HTTP %d).',
                $response->status(),
            ));
        }

        return $token;
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/').'/')
            ->acceptJson()
            ->asJson()
            ->timeout($this->timeout);
    }

    private function send(callable $request): Response
    {
        try {
            return $request();
        } catch (ConnectionException $e) {
            throw new GlincheApiException('API Glinche injoignable : '.$e->getMessage(), previous: $e);
        }
    }
}
