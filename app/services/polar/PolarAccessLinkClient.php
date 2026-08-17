<?php

namespace app\services\polar;

use app\dto\polar\PolarTokenDto;
use app\exceptions\polar\PolarApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class PolarAccessLinkClient
{
    /** @var string[] */
    private const TRAINING_FEATURES = [
     //   'samples',
        'test-results',
        'training-load-report',
        'laps',
        'hill-splits',
        'routes',
        'statistics',
        'zones',
        'pause-times',
        'strength-training-results',
        'comments',
        'physical-info',
    ];

    /** @var string */
    private const TRAINING_SESSIONS_LIST_URL = '/v4/data/training-sessions/list';

    private const TRAINING_TARGETS_URL = '/v4/data/training-target/calendar-targets';

    public function __construct(
        private string $clientId,
        private string $clientSecret,
        private string $redirectUri,
        private string $authUrl,
        private string $tokenUrl,
        private string $apiBaseUrl,
        private Client $http,
    ) {
    }

    /**
     * Builds Polar OAuth authorization URL.
     */
    public function getAuthorizationUrl(string $state): string
    {
        return $this->authUrl . '?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'training_sessions:read',
            'state' => $state,
        ]);
    }

    /**
     * Exchanges Polar OAuth authorization code for tokens.
     */
    public function exchangeAuthorizationCode(string $code): PolarTokenDto
    {
        $response = $this->requestToken([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
        ]);

        return $this->toTokenDto($response);
    }

    /**
     * Refreshes Polar access token using a refresh token.
     */
    public function refreshAccessToken(string $refreshToken): PolarTokenDto
    {
        $response = $this->requestToken([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);

        return $this->toTokenDto($response, $refreshToken);
    }

    /**
     * Lists training sessions for the given date range (max 90 days).
     *
     * @return array<int, array<string, mixed>>
     */
    public function listTrainingSessions(string $accessToken, string $from, string $to): array
    {
        $query = http_build_query([
            'from' => $from,
            'to' => $to,
        ]);
        foreach (self::TRAINING_FEATURES as $feature) {
            $query .= '&features=' . rawurlencode($feature);
        }

        $response = $this->request(
            'GET',
            $this->apiBaseUrl . self::TRAINING_SESSIONS_LIST_URL . '?' . $query,
            ['headers' => $this->bearerHeaders($accessToken)],
            allowNoContent: true,
        );

        if ($response === null) {
            return [];
        }

        $sessions = $response['trainingSessions'] ?? [];
        if (!is_array($sessions)) {
            return [];
        }

        return array_values(array_filter($sessions, 'is_array'));
    }

    /**
     * @param array<string, string> $formParams
     * @return array<string, mixed>
     */
    private function requestToken(array $formParams): array
    {
        $response = $this->request('POST', $this->tokenUrl, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json;charset=UTF-8',
            ],
            'form_params' => $formParams,
        ]);

        return $response ?? [];
    }

    /**
     * @param array<string, mixed> $response
     */
    private function toTokenDto(array $response, ?string $fallbackRefreshToken = null): PolarTokenDto
    {
        $accessToken = (string) ($response['access_token'] ?? '');
        $refreshToken = (string) ($response['refresh_token'] ?? $fallbackRefreshToken ?? '');
        $expiresIn = (int) ($response['expires_in'] ?? 0);

        if ($accessToken === '' || $refreshToken === '') {
            throw new PolarApiException('Polar token response is missing required fields.');
        }

        return new PolarTokenDto($accessToken, $refreshToken, $expiresIn);
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>|null
     */
    private function request(string $method, string $url, array $options, bool $allowNoContent = false): ?array
    {
        try {
            $response = $this->http->request($method, $url, $options);
        } catch (RequestException $exception) {
            throw $this->toApiException($exception);
        } catch (GuzzleException $exception) {
            throw new PolarApiException("Polar API request failed:{$exception->getMessage()}", 0, $exception);
        }

        $status = $response->getStatusCode();
        $raw = (string) $response->getBody();

        if ($status === 204 || $raw === '') {
            return $allowNoContent ? null : [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new PolarApiException('Polar API returned invalid JSON.', $status);
        }

        return $decoded;
    }

    /**
     * @return array<string, string>
     */
    private function bearerHeaders(string $accessToken): array
    {
        return [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
        ];
    }

    private function toApiException(RequestException $exception): PolarApiException
    {
        $response = $exception->getResponse();
        $status = $response?->getStatusCode() ?? 0;
        $message = match ($status) {
            403 => 'Polar access was denied. Please accept required consents and try again.',
            409 => 'This Polar account is already registered.',
            429 => 'Polar rate limit reached. Please try again later.',
            default => 'Polar API request failed.',
        };

        return new PolarApiException($message . $exception->getMessage(), $status, $exception);
    }
}
