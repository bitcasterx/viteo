<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\DTOs\VideoConversionTaskData;
use App\Jobs\ConvertVideoJob;
use App\Services\VideoConversionTaskMapper;
use App\Services\VideoUploadService;
use Illuminate\Http\UploadedFile;

final readonly class UploadVideoMutation
{
    public function __construct(
        private VideoUploadService $uploadService,
        private VideoConversionTaskMapper $taskMapper
    ) {
    }

    /**
     * @param  array<string, mixed>  $args
     */
    public function __invoke(mixed $_root, array $args): VideoConversionTaskData
    {
        /** @var UploadedFile $file */
        $file = $args['video'];

        $task = $this->uploadService->uploadAndCreateTask($file);

        ConvertVideoJob::dispatch($task->id)->onQueue('video-conversion');

        return $this->taskMapper->toDto($task);
    }
}
