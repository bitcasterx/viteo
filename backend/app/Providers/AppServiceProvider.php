<?php

namespace App\Providers;

use App\Contracts\VideoStorageInterface;
use App\Services\Storage\FilesystemVideoStorage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(VideoStorageInterface::class, function (): FilesystemVideoStorage {
            $config = config('video.storage', []);

            return new FilesystemVideoStorage(
                $config['upload_disk'] ?? 'local',
                $config['converted_disk'] ?? 'public'
            );
        });
    }

    public function boot(): void
    {
    }
}
