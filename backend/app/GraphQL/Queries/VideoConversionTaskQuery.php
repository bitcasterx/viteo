<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\DTOs\VideoConversionTaskData;
use App\Repositories\VideoConversionTaskRepository;
use App\Services\VideoConversionTaskMapper;

final readonly class VideoConversionTaskQuery
{
    public function __construct(
        private VideoConversionTaskRepository $repository,
        private VideoConversionTaskMapper $taskMapper
    ) {
    }

    public function __invoke(mixed $_root, array $args): ?VideoConversionTaskData
    {
        $task = $this->repository->findById($args['id']);

        if ($task === null) {
            return null;
        }

        return $this->taskMapper->toDto($task);
    }
}
