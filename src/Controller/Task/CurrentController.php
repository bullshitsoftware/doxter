<?php

namespace App\Controller\Task;

use App\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[
    Route('/', name: 'home'),
    Route('/current', name: 'task_current'),
]
class CurrentController extends Controller
{
    public function __invoke(TaskRepository $repository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $now = $this->now();

        return $this->render('task/current.html.twig', [
            'now' => $now,
            'tasks' => $repository->findCurrentByUser($this->getUser(), $request->get('q'), $now),
        ]);
    }
}
