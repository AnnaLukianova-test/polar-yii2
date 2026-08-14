<?php

namespace app\dto\polar;

class PolarSyncResultDto
{
    /**
     * @param string[] $errors
     */
    public function __construct(
        public readonly int $syncedCount,
        public readonly array $errors = [],
        public readonly bool $noNewData = false,
    ) {
    }
}
