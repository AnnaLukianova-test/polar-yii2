<?php

namespace app\services\polar;

use app\dto\polar\CreatePolarConnectionDto;
use app\dto\polar\PolarSyncResultDto;
use app\dto\polar\PolarTokenDto;
use app\exceptions\polar\PolarApiException;
use app\models\polar\PolarConnection;
use app\models\User;
use app\repositories\PolarConnectionRepository;
use app\repositories\PolarExerciseRepository;
use app\services\security\CipherService;
use RuntimeException;
use Yii;

class PolarSyncService
{
    public function __construct(
        private PolarAccessLinkClient $client,
        private PolarConnectionRepository $connections,
        private PolarExerciseRepository $exercises,
        private CipherService $cipher,
    ) {
    }

    /**
     * Connects Polar to the user.
     */
    public function connect(User $user, PolarTokenDto $token): void
    {
        $memberId = (string) $user->id;
        $polarUserId = $token->polar_user_id;

        try {
            $registered = $this->client->registerUser($token->access_token, $memberId);
            $polarUserId = (int) ($registered['polar-user-id'] ?? $polarUserId);
        } catch (PolarApiException $exception) {
            if (!$exception->isConflict()) {
                throw $exception;
            }
        }

        try {
            $encryptedAccessToken = $this->cipher->encrypt($token->access_token);
        } catch (RuntimeException $exception) {
            throw new PolarApiException('Unable to store Polar connection.', 0, $exception);
        }

        $dto = new CreatePolarConnectionDto(
            user_id: $user->id,
            polar_user_id: $polarUserId,
            access_token: $encryptedAccessToken,
            token_expires_at: date('Y-m-d H:i:s', time() + max(0, $token->expires_in)),
            member_id: $memberId,
        );

        $connection = $this->connections->create($dto);
        if ($connection === null) {
            throw new PolarApiException('Unable to store Polar connection.');
        }
    }

    /**
     * Returns the last connected Polar connection for the user.
     */
    public function getConnection(User $user): ?PolarConnection
    {
        return $this->connections->findLastByUserId($user->id);
    }

    /**
     * Syncs exercises from Polar to the database.
     */
    public function syncExercises(User $user): PolarSyncResultDto
    {
        $connection = $this->connections->findLastByUserId($user->id);
        if ($connection === null) {
            return new PolarSyncResultDto(0, ['Connect Polar before syncing exercises.']);
        }

        if ($connection->isTokenExpired()) {
            return new PolarSyncResultDto(0, ['Polar access expired. Please connect again.']);
        }

        try {
            $accessToken = $this->cipher->decrypt($connection->access_token);
        } catch (RuntimeException $exception) {
            Yii::error($exception->getMessage(), __METHOD__);

            return new PolarSyncResultDto(0, ['Polar access expired. Please connect again.']);
        }

        $transaction = $this->client->createExerciseTransaction(
            $accessToken,
            (int) $connection->polar_user_id,
        );

        if ($transaction === null) {
            $this->touchLastSynced($connection);

            return new PolarSyncResultDto(0, noNewData: true);
        }

        $transactionId = $this->extractTransactionId($transaction);
        if ($transactionId === null) {
            return new PolarSyncResultDto(0, ['Polar did not return a transaction id.']);
        }

        $urls = $this->client->listTransactionExercises(
            $accessToken,
            (int) $connection->polar_user_id,
            $transactionId,
        );

        $synced = 0;
        $errors = [];

        foreach ($urls as $url) {
            try {
                $payload = $this->client->getExercise($accessToken, $url);
                $exerciseId = $this->extractExerciseId($url, $payload);
                if ($this->exercises->upsert($user->id, $exerciseId, $payload)) {
                    $synced++;
                } else {
                    $errors[] = 'Failed to store Polar exercise ' . $exerciseId . '.';
                }
            } catch (PolarApiException $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                $errors[] = 'Failed to download a Polar exercise.';
            }
        }

        if ($errors === []) {
            $this->client->commitExerciseTransaction(
                $accessToken,
                (int) $connection->polar_user_id,
                $transactionId,
            );
            $this->touchLastSynced($connection);
        }

        return new PolarSyncResultDto($synced, $errors);
    }

    private function touchLastSynced(PolarConnection $connection): void
    {
        $connection->last_synced_at = date('Y-m-d H:i:s');
        $this->connections->save($connection);
    }

    /**
     * @param array<string, mixed> $transaction
     */
    private function extractTransactionId(array $transaction): ?int
    {
        if (isset($transaction['transaction-id'])) {
            return (int) $transaction['transaction-id'];
        }

        $resourceUri = (string) ($transaction['resource-uri'] ?? '');
        if ($resourceUri === '') {
            return null;
        }

        $parts = explode('/', rtrim($resourceUri, '/'));
        $last = end($parts);

        return is_numeric($last) ? (int) $last : null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractExerciseId(string $url, array $payload): string
    {
        if (!empty($payload['id'])) {
            return (string) $payload['id'];
        }

        $parts = explode('/', rtrim($url, '/'));

        return (string) end($parts);
    }
}
