<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\VideoConversionTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoConversionTaskStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_task_status(): void
    {
        $task = VideoConversionTask::query()->create([
            'input_path' => 'uploads/test.mp4',
            'status'     => VideoConversionTask::STATUS_PROCESSING,
            'progress'   => 50,
        ]);

        $response = $this->graphQL(
            '
            query GetTask($id: ID!) {
                videoConversionTask(id: $id) {
                    id
                    status
                    progress
                    downloadUrl
                    errorMessage
                }
            }
            ',
            [
                'id' => $task->id,
            ]
        );

        $response->assertJsonPath('data.videoConversionTask.id', $task->id);
        $response->assertJsonPath('data.videoConversionTask.status', 'processing');
        $response->assertJsonPath('data.videoConversionTask.progress', 50);
    }

    public function test_returns_null_for_non_existent_task(): void
    {
        $response = $this->graphQL(
            '
            query GetTask($id: ID!) {
                videoConversionTask(id: $id) {
                    id
                }
            }
            ',
            [
                'id' => '00000000-0000-0000-0000-000000000000',
            ]
        );

        $response->assertJsonPath('data.videoConversionTask', null);
    }
}
