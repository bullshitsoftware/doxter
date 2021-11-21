<?php

declare(strict_types=1);

namespace App\Controller\Task;

use App\Entity\Task;
use App\Security\Voter\TaskVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/view/{id}', name: 'task_view')]
class ViewController extends Controller
{
    public function __invoke(Request $request, Task $task): Response
    {
        $this->denyAccessUnlessGranted(TaskVoter::VIEW, $task);

        $request->attributes->set('nav_route', $this->taskListRoute($task));

        return $this->render('task/view.html.twig', [
            'task' => $task,
        ]);
    }
}
