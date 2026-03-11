<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\VideoStorageInterface;
use App\Models\VideoConversionTask;
use App\Repositories\VideoConversionTaskRepository;
use Illuminate\Http\UploadedFile;

class VideoUploadService
{
    public function __construct(
        private readonly VideoConversionTaskRepository $repository,
        private readonly VideoStorageInterface $storage
    ) {
    }

    public function uploadAndCreateTask(UploadedFile $file): VideoConversionTask
    {
        $path = $this->storage->storeUpload($file);

        $task = $this->repository->create([
            'input_path' => $path,
            'status'     => VideoConversionTask::STATUS_QUEUED,
            'progress'   => 0,
            'metadata'   => [
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
            ],
        ]);

        return $task;
    }
}
