<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\VideoConversionTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VideoConversionTaskRepository
{
    public function create(array $data): VideoConversionTask
    {
        return VideoConversionTask::query()->create($data);
    }

    public function findById(string $id): ?VideoConversionTask
    {
        return VideoConversionTask::query()->find($id);
    }

    public function findByIdOrFail(string $id): VideoConversionTask
    {
        return VideoConversionTask::query()->findOrFail($id);
    }

    public function updateStatus(
        VideoConversionTask $task,
        string $status,
        ?int $progress = null,
        ?string $errorMessage = null,
        ?string $outputPath = null
    ): bool {
        $data = ['status' => $status];

        if ($progress !== null) {
            $data['progress'] = $progress;
        }

        if ($errorMessage !== null) {
            $data['error_message'] = $errorMessage;
        }

        if ($outputPath !== null) {
            $data['output_path'] = $outputPath;
        }

        return $task->update($data);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return VideoConversionTask::query()
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
