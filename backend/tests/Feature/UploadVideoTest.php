<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\ConvertVideoJob;
use App\Models\VideoConversionTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadVideoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Queue::fake();
    }

    public function test_upload_video_creates_task_and_dispatches_job(): void
    {
        $file = UploadedFile::fake()->create('video.mp4', 1024, 'video/mp4');

        $operations = [
            'query' => 'mutation UploadVideo($video: Upload!) {
                uploadVideo(video: $video) {
                    id
                    status
                    progress
                    downloadUrl
                }
            }',
            'variables' => [
                'video' => null,
            ],
        ];

        $map = [
            '0' => ['variables.video'],
        ];

        $response = $this->multipartGraphQL($operations, $map, ['0' => $file]);

        $response->assertJsonStructure([
            'data' => [
                'uploadVideo' => [
                    'id',
                    'status',
                    'progress',
                    'downloadUrl',
                ],
            ],
        ]);

        $response->assertJsonPath('data.uploadVideo.status', 'queued');
        $response->assertJsonPath('data.uploadVideo.progress', 0);

        $taskId = $response->json('data.uploadVideo.id');
        $this->assertNotNull($taskId);

        $task = VideoConversionTask::query()->find($taskId);
        $this->assertInstanceOf(VideoConversionTask::class, $task);
        $this->assertSame(VideoConversionTask::STATUS_QUEUED, $task->status);

        Queue::assertPushed(ConvertVideoJob::class);
    }
}
