<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\VideoStorageInterface;
use App\Models\VideoConversionTask;
use RuntimeException;
use Symfony\Component\Process\Process;

class VideoConversionService
{
    private const MAX_HEIGHT = 720;

    private const OUTPUT_CODEC = 'libx264';

    private const OUTPUT_AUDIO_CODEC = 'aac';

    private const OUTPUT_EXTENSION = 'mp4';

    public function __construct(
        private readonly VideoStorageInterface $storage
    ) {
    }

    public function convert(VideoConversionTask $task): void
    {
        $inputPath = $this->storage->getReadablePath($task->input_path);

        $timeout = config('video.conversion.timeout_seconds', 3600);

        try {
            if (! file_exists($inputPath)) {
                throw new RuntimeException(
                    __('messages.video.input_file_not_found', ['path' => $task->input_path])
                );
            }

            $outputRelativePath = $this->getOutputRelativePath($task);
            $tempOutputPath = sys_get_temp_dir().'/viteo_'.uniqid('', true).'.'.self::OUTPUT_EXTENSION;

            $command = [
                'ffmpeg',
                '-y',
                '-i', $inputPath,
                '-vf', sprintf('scale=-2:%d', self::MAX_HEIGHT),
                '-c:v', self::OUTPUT_CODEC,
                '-preset', 'medium',
                '-crf', '23',
                '-movflags', '+faststart',
                '-c:a', self::OUTPUT_AUDIO_CODEC,
                '-b:a', '128k',
                '-ac', '2',
                $tempOutputPath,
            ];

            $process = new Process($command);
            $process->setTimeout($timeout);
            $process->run();

            if (! $process->isSuccessful()) {
                if (file_exists($tempOutputPath)) {
                    @unlink($tempOutputPath);
                }

                throw new RuntimeException(
                    __('messages.video.conversion_failed', ['error' => $process->getErrorOutput()])
                );
            }

            try {
                $this->storage->writeConvertedFromLocal($tempOutputPath, $outputRelativePath);
            } finally {
                if (file_exists($tempOutputPath)) {
                    @unlink($tempOutputPath);
                }
            }
        } finally {
            $this->storage->releaseReadablePath($inputPath);
        }
    }

    public function getOutputRelativePath(VideoConversionTask $task): string
    {
        $inputPath = $task->input_path;
        $baseName = pathinfo($inputPath, PATHINFO_FILENAME);

        return sprintf(
            'converted/%s/%s.%s',
            $task->id,
            $baseName,
            self::OUTPUT_EXTENSION
        );
    }

    public function getDownloadUrl(string $outputPath): string
    {
        return $this->storage->getDownloadUrl($outputPath);
    }
}
