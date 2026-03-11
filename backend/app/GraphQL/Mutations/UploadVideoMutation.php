<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\DTOs\VideoConversionTaskData;
use App\Jobs\ConvertVideoJob;
use App\Services\VideoConversionService;
use App\Services\VideoUploadService;
use Illuminate\Http\UploadedFile;

final readonly class UploadVideoMutation
{
    public function __construct(
        private VideoUploadService $uploadService,
        private VideoConversionService $conversionService
    ) {
    }

    public function __invoke(mixed $_root, array $args): VideoConversionTaskData
    {
        /** @var UploadedFile $file */
        $file = $args['video'];

        $task = $this->uploadService->uploadAndCreateTask($file);

        ConvertVideoJob::dispatch($task->id)->onQueue('video-conversion');

        $downloadUrl = null;

        if ($task->output_path !== null) {
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
