<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface VideoStorageInterface
{
    /**
     * Сохраняет загруженный файл и возвращает ключ/путь для хранения.
     */
    public function storeUpload(UploadedFile $file): string;

    /**
     * Возвращает локальный путь к файлу для чтения (например, FFmpeg).
     * Для облачного хранилища скачивает во временный файл.
     */
    public function getReadablePath(string $storedPath): string;

    /**
     * Освобождает ресурсы после чтения (удаляет временный файл для облачного хранилища).
     */
    public function releaseReadablePath(string $localPath): void;

    /**
     * Записывает сконвертированный файл из локального пути в хранилище.
     * Возвращает ключ/путь сохранённого файла.
     */
    public function writeConvertedFromLocal(string $localPath, string $relativeKey): string;

    /**
     * Возвращает URL для скачивания по сохранённому пути/ключу.
     */
    public function getDownloadUrl(string $storedPath): string;
}
