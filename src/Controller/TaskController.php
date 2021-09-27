<?php

namespace App\Controller;

use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    #[
        Route('/', name: 'home'),
        Route('/current', name: 'task_current'),
    ]
    public function current(TaskRepository $repository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('task/current.html.twig', [
            'tasks' => $repository->findCurrentByUser($this->getUser()),
        ]);
    }

    #[Route('/wait', name: 'task_wait')]
    public function wait(TaskRepository $repository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('task/wait.html.twig', [
            'tasks' => $repository->findWaitByUser($this->getUser()),
        ]);
    }

    #[Route('/done', name: 'task_done')]
    public function done(TaskRepository $repository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        return $this->render('task/done.html.twig', [
            'tasks' => $repository->findDoneByUser($this->getUser()),
        ]);
    }
}
