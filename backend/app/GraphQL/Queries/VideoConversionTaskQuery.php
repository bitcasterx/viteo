<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\DTOs\VideoConversionTaskData;
use App\Models\VideoConversionTask;
use App\Repositories\VideoConversionTaskRepository;
use App\Services\VideoConversionService;

final readonly class VideoConversionTaskQuery
{
    public function __construct(
        private VideoConversionTaskRepository $repository,
        private VideoConversionService $conversionService
    ) {
    }

    public function __invoke(mixed $_root, array $args): ?VideoConversionTaskData
    {
        $task = $this->repository->findById($args['id']);

        if ($task === null) {
            return null;
        }

        return $this->mapToDto($task);
    }

    private function mapToDto(VideoConversionTask $task): VideoConversionTaskData
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
