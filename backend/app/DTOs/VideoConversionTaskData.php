<?php

declare(strict_types=1);

namespace App\DTOs;

use Carbon\CarbonInterface;

final readonly class VideoConversionTaskData
{
    public function __construct(
        public string $id,
        public string $status,
        public int $progress,
        public ?string $downloadUrl,
        public ?string $errorMessage,
        public CarbonInterface $createdAt,
        public CarbonInterface $updatedAt,
    ) {
    }
}
