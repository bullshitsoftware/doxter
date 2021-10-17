<?php

namespace App\Controller\Task;

use App\Controller\Controller as BaseController;
use App\Entity\Task;

abstract class Controller extends BaseController
{
    protected function taskListRoute(Task $task): string
    {
        if (null !== $task->getEnded()) {
            return 'task_completed';
        }

        if (null === $task->getWait() || $task->getWait() < $this->now()) {
            return 'home';
        }

        return 'task_waiting';
    }
}
