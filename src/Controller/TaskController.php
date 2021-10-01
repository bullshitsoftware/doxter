<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Security\Voter\TaskVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/waiting', name: 'task_waiting')]
    public function waiting(TaskRepository $repository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('task/waiting.html.twig', [
            'tasks' => $repository->findWaitingByUser($this->getUser()),
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

    #[Route('/add', name: 'task_add')]
    public function add(EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $task = new Task();
        $task->setUser($this->getUser());
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUpdated(new \DateTimeImmutable());
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('task/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/view/{id}', name: 'task_view')]
    public function view(Task $task): Response
    {
        $this->denyAccessUnlessGranted(TaskVoter::VIEW, $task);

        return $this->render('task/view.html.twig', ['task' => $task]);
    }

    #[Route('/edit/{id}', name: 'task_edit')]
    public function edit(EntityManagerInterface $entityManager, Request $request, Task $task): Response
    {
        $this->denyAccessUnlessGranted(TaskVoter::EDIT, $task);

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUpdated(new \DateTimeImmutable());
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route('/delete/{id}', name: 'task_delete', methods: ['POST'])]
    public function delete(EntityManagerInterface $entityManager, Request $request, Task $task): Response
    {
        $this->denyAccessUnlessGranted(TaskVoter::DELETE, $task);
        if (!$this->isCsrfTokenValid('task', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($task);
        $entityManager->flush();

        return $this->redirectToRoute('home');
    }
}
