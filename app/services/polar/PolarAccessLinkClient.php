<?php

namespace app\services\polar;

use app\dto\polar\PolarTokenDto;
use app\exceptions\polar\PolarApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class PolarAccessLinkClient
{
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
            'scope' => 'accesslink.read_all',
            'state' => $state,
        ]);
    }

    /**
     * Exchanges Polar OAuth authorization code for a token.
     */
    public function exchangeAuthorizationCode(string $code): PolarTokenDto
    {
        $response = $this->request('POST', $this->tokenUrl, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json;charset=UTF-8',
            ],
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
            ],
        ]);

        $accessToken = (string) ($response['access_token'] ?? '');
        $expiresIn = (int) ($response['expires_in'] ?? 0);
        $polarUserId = (int) ($response['x_user_id'] ?? 0);

        if ($accessToken === '' || $polarUserId === 0) {
            throw new PolarApiException('Polar token response is missing required fields.');
        }

        return new PolarTokenDto($accessToken, $expiresIn, $polarUserId);
    }

    /**
     *  must register the user before being able to access its data
     * https://www.polar.com/accesslink-api/?srsltid=AfmBOooEnV3SzWTpF0qBrLwOzS-9npNUsx7FzxpgTBVL8Rk-xnm0fRXE#users
     * @return array<string, mixed>
     */
    public function registerUser(string $accessToken, string $memberId): array
    {
        return $this->request('POST', $this->apiBaseUrl . '/v3/users', [
            'headers' => $this->bearerHeaders($accessToken),
            'json' => [
                'member-id' => $memberId,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>|null transaction payload, or null when there is no new data (HTTP 204)
     */
    public function createExerciseTransaction(string $accessToken, int $polarUserId): ?array
    {
        return $this->request(
            'POST',
            $this->apiBaseUrl . '/v3/users/' . $polarUserId . '/exercise-transactions',
            ['headers' => $this->bearerHeaders($accessToken)],
            allowNoContent: true,
        );
    }

    /**
     * @return string[] exercise resource URLs
     */
    public function listTransactionExercises(string $accessToken, int $polarUserId, int $transactionId): array
    {
        $response = $this->request(
            'GET',
            $this->apiBaseUrl . '/v3/users/' . $polarUserId . '/exercise-transactions/' . $transactionId,
            ['headers' => $this->bearerHeaders($accessToken)],
        );

        $urls = $response['exercises'] ?? [];
        if (!is_array($urls)) {
            return [];
        }

        return array_values(array_filter($urls, 'is_string'));
    }

    /**
     * @return array<string, mixed>
     */
    public function getExercise(string $accessToken, string $exerciseUrl): array
    {
        return $this->request('GET', $exerciseUrl, [
            'headers' => $this->bearerHeaders($accessToken),
        ]);
    }

    public function commitExerciseTransaction(string $accessToken, int $polarUserId, int $transactionId): void
    {
        $this->request(
            'PUT',
            $this->apiBaseUrl . '/v3/users/' . $polarUserId . '/exercise-transactions/' . $transactionId,
            ['headers' => $this->bearerHeaders($accessToken)],
            allowNoContent: true,
        );
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
            throw new PolarApiException('Polar API request failed.', 0, $exception);
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
            'Content-Type' => 'application/json',
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

        return new PolarApiException($message, $status, $exception);
    }
}
