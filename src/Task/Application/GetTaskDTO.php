<?php

namespace App\Task\Application;

class GetTaskDTO
{
    private int $userId;
    private int $taskId;

    public function __construct(int $userId, int $taskId)
    {
        $this->userId = $userId;
        $this->taskId = $taskId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getTaskId(): int
    {
        return $this->taskId;
    }
}