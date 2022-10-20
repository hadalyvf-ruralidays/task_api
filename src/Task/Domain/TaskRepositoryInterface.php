<?php

namespace App\Task\Domain;

interface TaskRepositoryInterface
{
    public function save(Task $task): void;

}