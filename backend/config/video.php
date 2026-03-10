<?php

declare(strict_types=1);

return [
    'storage' => [
        'upload_disk' => env('VIDEO_UPLOAD_DISK', 'local'),
        'converted_disk' => env('VIDEO_CONVERTED_DISK', 'public'),
    ],
];
