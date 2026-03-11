<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\VideoConversionTaskData;
use App\Models\VideoConversionTask;

class VideoConversionTaskMapper
{
    public function __construct(
        private readonly VideoConversionService $conversionService
    ) {
    }

    public function toDto(VideoConversionTask $task): VideoConversionTaskData
    {
        $downloadUrl = null;

        if ($task->isCompleted() && $task->output_path !== null) {
            $downloadUrl = $this->conversionService->getDownloadUrl($task->output_path);
        }

        return new VideoConversionTaskData(
            id: $task->id,
            status: $task->status,
            progress: $task->progress,
            downloadUrl: $downloadUrl,
            errorMessage: $task->error_message,
            createdAt: $task->created_at,
            updatedAt: $task->updated_at,
        );
    }
}
