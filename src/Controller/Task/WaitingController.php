<?php

declare(strict_types=1);

namespace App\Controller\Task;

use App\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/waiting', name: 'task_waiting')]
class WaitingController extends Controller
{
    public function __invoke(TaskRepository $repository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $now = $this->now();

        return $this->render('task/waiting.html.twig', [
            'now' => $now,
            'tasks' => $repository->findWaitingByUser($this->getUser(), $request->get('q'), $now),
        ]);
    }
}
