<?php

declare(strict_types=1);

namespace App\Services\Storage;

use App\Contracts\VideoStorageInterface;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;

final class FilesystemVideoStorage implements VideoStorageInterface
{
    public function __construct(
        private readonly string $uploadDisk,
        private readonly string $convertedDisk
    ) {
    }

    public function storeUpload(UploadedFile $file): string
    {
        $path = $file->store('uploads', $this->uploadDisk);

        return is_string($path) ? $path : '';
    }

    public function getReadablePath(string $storedPath): string
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($this->uploadDisk);
        $adapter = $disk->getAdapter();

        if ($adapter instanceof LocalFilesystemAdapter) {
            return $disk->path($storedPath);
        }

        $tempPath = storage_path('app/temp/'.basename($storedPath));
        $directory = dirname($tempPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $contents = $disk->get($storedPath);
        if ($contents !== null) {
            file_put_contents($tempPath, $contents);
        }

        return $tempPath;
    }

    public function releaseReadablePath(string $localPath): void
    {
        $tempDir = storage_path('app/temp');
        if (str_starts_with(realpath($localPath) ?: $localPath, $tempDir) && file_exists($localPath)) {
            @unlink($localPath);
        }
    }

    public function writeConvertedFromLocal(string $localPath, string $relativeKey): string
    {
        $disk = Storage::disk($this->convertedDisk);
        $stream = fopen($localPath, 'r');
        if ($stream === false) {
            throw new \RuntimeException(sprintf('Cannot open local file: %s', $localPath));
        }

        try {
            $disk->put($relativeKey, $stream);
        } finally {
            fclose($stream);
        }

        if (file_exists($localPath)) {
            @unlink($localPath);
        }

        return $relativeKey;
    }

    public function getDownloadUrl(string $storedPath): string
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($this->convertedDisk);

        return $disk->url($storedPath);
    }
}
