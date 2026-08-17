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
use DateInterval;
use DateTimeImmutable;
use RuntimeException;
use Yii;

class PolarSyncService
{
    private const MAX_SYNC_DAYS = 90;

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
        try {
            $encryptedAccessToken = $this->cipher->encrypt($token->access_token);
            $encryptedRefreshToken = $this->cipher->encrypt($token->refresh_token);
        } catch (RuntimeException $exception) {
            throw new PolarApiException('Unable to store Polar connection.', 0, $exception);
        }

        $dto = new CreatePolarConnectionDto(
            user_id: $user->id,
            access_token: $encryptedAccessToken,
            refresh_token: $encryptedRefreshToken,
            token_expires_at: date('Y-m-d H:i:s', time() + max(0, $token->expires_in)),
            member_id: (string) $user->id,
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
     * Syncs training sessions from Polar to the database.
     */
    public function syncActivities(User $user): PolarSyncResultDto
    {
        $connection = $this->connections->findLastByUserId($user->id);
        if ($connection === null || $connection->isTokenExpired()) {
            return new PolarSyncResultDto(0, ['Connect Polar before syncing exercises.']);
        }

        try {
            $accessToken = $this->ensureValidAccessToken($connection);
        } catch (PolarApiException $exception) {
            Yii::error($exception->getMessage(), __METHOD__);

            return new PolarSyncResultDto(0, [$exception->getMessage()]);
        } catch (RuntimeException $exception) {
            Yii::error($exception->getMessage(), __METHOD__);

            return new PolarSyncResultDto(0, ['Polar access expired. Please connect again.']);
        }

        $dates = $this->resolveSyncRange($connection);

        try {
            $sessions = [];
            $dateCount = count($dates);
            for ($i = 0; $i < $dateCount; $i++) {
                $from = $dates[$i];
                $to = $dates[$i + 1] ?? $this->toIso8601(
                    (new DateTimeImmutable($dates[$i]))->modify('+1 day')
                );
                $currentDayTrainings = $this->client->listTrainingSessions($accessToken, $from, $to);
                foreach ($currentDayTrainings as $training) {
                    if (isset($training['favoriteTarget'])) { // only my running trainings have this field
                        $sessions[] = $training;
                    }
                }
            }
        } catch (PolarApiException $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            //todo log real errors in MongoDB
            var_dump($exception->getMessage());die;
            return new PolarSyncResultDto(0, ['Failed to list Polar training sessions.']);
        }

        if ($sessions === []) {
            $this->touchLastSynced($connection);

            return new PolarSyncResultDto(0, noNewData: true);
        }

        $synced = 0;
        $errors = [];

        foreach ($sessions as $session) {
            $sessionId = $this->extractSessionId($session);
            if ($sessionId === null) {
                $errors[] = 'Polar returned a training session without an id.';
                continue;
            }

            if ($this->exercises->upsert($user->id, $sessionId, $session)) {
                $synced++;
            } else {
                $errors[] = 'Failed to store Polar exercise ' . $sessionId . '.';
            }
        }

        if ($errors === []) {
            $this->touchLastSynced($connection);
        }

        return new PolarSyncResultDto($synced, $errors);
    }

    /**
     * @throws PolarApiException
     * @throws RuntimeException
     */
    private function ensureValidAccessToken(PolarConnection $connection): string
    {
        $accessToken = $this->cipher->decrypt($connection->access_token);

        if (!$connection->isTokenExpired()) {
            return $accessToken;
        }

        if ($connection->refresh_token === null || $connection->refresh_token === '') {
            throw new PolarApiException('Polar access expired. Please connect again.');
        }

        $refreshToken = $this->cipher->decrypt($connection->refresh_token);
        $token = $this->client->refreshAccessToken($refreshToken);

        $connection->access_token = $this->cipher->encrypt($token->access_token);
        $connection->refresh_token = $this->cipher->encrypt($token->refresh_token);
        $connection->token_expires_at = date('Y-m-d H:i:s', time() + max(0, $token->expires_in));

        if (!$this->connections->save($connection)) {
            throw new PolarApiException('Unable to store refreshed Polar tokens.');
        }

        return $token->access_token;
    }

    /**
     * @return string[] ISO 8601 datetimes at 00:00:00, one per calendar day, inclusive of both ends
     */
    private function resolveSyncRange(PolarConnection $connection): array
    {
        $now = new DateTimeImmutable('now');
        $to = $now->modify('+1 day')->setTime(0, 0);

        if ($connection->last_synced_at !== null && $connection->last_synced_at !== '') {
            $from = (new DateTimeImmutable($connection->last_synced_at))->setTime(0, 0);
        } else {
            $from = $to->sub(new DateInterval('P' . self::MAX_SYNC_DAYS . 'D'));
        }

        $earliest = $to->sub(new DateInterval('P' . self::MAX_SYNC_DAYS . 'D'));
        if ($from < $earliest) {
            $from = $earliest;
        }

        if ($from >= $to) {
            $from = $to->sub(new DateInterval('P1D'));
        }

        $dates = [];
        $current = $from;
        while ($current <= $to) {
            $dates[] = $this->toIso8601($current);
            $current = $current->modify('+1 day');
        }

        return $dates;
    }

    private function toIso8601(DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d\TH:i:s');
    }

    /**
     * @param array<string, mixed> $session
     */
    private function extractSessionId(array $session): ?string
    {
        $identifier = $session['identifier'] ?? null;
        if (is_array($identifier) && !empty($identifier['id'])) {
            return (string) $identifier['id'];
        }

        if (!empty($session['id'])) {
            return (string) $session['id'];
        }

        return null;
    }

    private function touchLastSynced(PolarConnection $connection): void
    {
        $connection->last_synced_at = date('Y-m-d H:i:s');
        $this->connections->save($connection);
    }
}
