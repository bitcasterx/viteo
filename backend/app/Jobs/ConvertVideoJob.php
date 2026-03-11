<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\VideoConversionTask;
use App\Repositories\VideoConversionTaskRepository;
use App\Services\VideoConversionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ConvertVideoJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 3600;

    public function __construct(
        private readonly string $taskId
    ) {
    }

    public function handle(
        VideoConversionService $conversionService,
        VideoConversionTaskRepository $repository
    ): void {
        $task = $repository->findById($this->taskId);

        if ($task === null) {
            return;
        }

        if (! $task->isProcessable()) {
            return;
        }

        $repository->updateStatus(
            $task,
            VideoConversionTask::STATUS_PROCESSING,
            0
        );

        try {
            $conversionService->convert($task);

            $outputPath = $conversionService->getOutputRelativePath($task);

            $repository->updateStatus(
                $task,
                VideoConversionTask::STATUS_COMPLETED,
                100,
                null,
                $outputPath
            );
        } catch (Throwable $e) {
            $repository->updateStatus(
                $task,
                VideoConversionTask::STATUS_FAILED,
                null,
                $e->getMessage()
            );

            throw $e;
        }
    }

    public function uniqueId(): string
    {
        return $this->taskId;
    }
}
