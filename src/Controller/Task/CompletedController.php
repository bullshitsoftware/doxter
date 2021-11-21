<?php

declare(strict_types=1);

namespace App\Controller\Task;

use App\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/completed', name: 'task_completed')]
class CompletedController extends Controller
{
    public function __invoke(TaskRepository $repository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $page = $request->get('page', 1);
        $pagination = $repository->findCompletedByUser($this->getUser(), $page, $request->get('q'));

        return $this->render('task/completed.html.twig', [
            'now' => $this->now(),
            'tasks' => $pagination->getItems(),
            'page' => $page,
            'more' => $pagination->hasMore(),
        ]);
    }
}
