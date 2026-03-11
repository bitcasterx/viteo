<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\VideoConversionTask;
use App\Services\VideoConversionService;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class VideoConversionServiceTest extends TestCase
{
    private VideoConversionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(VideoConversionService::class);
    }

    #[Test]
    public function get_output_relative_path_returns_correct_format(): void
    {
        $task = new VideoConversionTask();
        $task->id = '550e8400-e29b-41d4-a716-446655440000';
        $task->input_path = 'uploads/abc123_video.mp4';

        $path = $this->service->getOutputRelativePath($task);

        $this->assertStringContainsString('converted/550e8400-e29b-41d4-a716-446655440000/', $path);
        $this->assertStringEndsWith('.mp4', $path);
    }

    #[Test]
    public function get_download_url_returns_valid_url(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('converted/test/video.mp4', 'content');

        $url = $this->service->getDownloadUrl('converted/test/video.mp4');

        $this->assertStringContainsString('/storage/converted/test/video.mp4', $url);
    }

    #[Test]
    public function convert_throws_when_input_file_not_found(): void
    {
        $task = new VideoConversionTask([
            'id'         => '550e8400-e29b-41d4-a716-446655440000',
            'input_path' => 'uploads/non_existent.mp4',
        ]);
        $task->exists = true;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Input file not found');

        $this->service->convert($task);
    }
}
