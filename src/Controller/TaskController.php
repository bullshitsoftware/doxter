<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Security\Voter\TaskVoter;
use App\Service\DateTime\DateTimeFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends Controller
{
    #[
        Route('/', name: 'home'),
        Route('/current', name: 'task_current'),
    ]
    public function current(TaskRepository $repository, DateTimeFactory $dateTimeFactory, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $now = $dateTimeFactory->now();

        return $this->render('task/current.html.twig', [
            'now' => $now,
            'tasks' => $repository->findCurrentByUser($this->getUser(), $request->get('q'), $now),
        ]);
    }

    #[Route('/waiting', name: 'task_waiting')]
    public function waiting(TaskRepository $repository, DateTimeFactory $dateTimeFactory, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $now = $dateTimeFactory->now();

        return $this->render('task/waiting.html.twig', [
            'now' => $now,
            'tasks' => $repository->findWaitingByUser($this->getUser(), $request->get('q'), $now),
        ]);
    }

    #[Route('/completed', name: 'task_completed')]
    public function completed(TaskRepository $repository, DateTimeFactory $dateTimeFactory, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $page = $request->get('page', 1);
        $pagination = $repository->findCompletedByUser($this->getUser(), $page, $request->get('q'));

        return $this->render('task/completed.html.twig', [
            'now' => $dateTimeFactory->now(),
            'tasks' => $pagination->getItems(),
            'page' => $page,
            'more' => $pagination->hasMore(),
        ]);
    }

    #[Route('/add', name: 'task_add')]
    public function add(
        EntityManagerInterface $entityManager, 
        DateTimeFactory $dateTimeFactory, 
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $task = new Task();
        $task->setUser($this->getUser());
        $task->setCreated($dateTimeFactory->now());
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUpdated($dateTimeFactory->now());
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash(self::FLASH_SUCCESS, sprintf('Task "%s" created', $task->getTitle()));

            return $this->redirectToList($dateTimeFactory->now(), $task);
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
    public function edit(
        EntityManagerInterface $entityManager, 
        DateTimeFactory $dateTimeFactory, 
        Request $request, 
        Task $task
    ): Response {
        $this->denyAccessUnlessGranted(TaskVoter::EDIT, $task);

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUpdated($dateTimeFactory->now());
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash(self::FLASH_SUCCESS, sprintf('Task "%s" updated', $task->getTitle()));

            return $request->request->has('apply') 
                ? $this->redirectToRoute('task_view', ['id' => $task->getId()])
                : $this->redirectToList($dateTimeFactory->now(), $task);

        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route('/delete/{id}', name: 'task_delete', methods: ['POST'])]
    public function delete(EntityManagerInterface $entityManager, DateTimeFactory $dateTimeFactory, Request $request, Task $task): Response
    {
        $this->denyAccessUnlessGranted(TaskVoter::DELETE, $task);
        if (!$this->isCsrfTokenValid('task', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($task);
        $entityManager->flush();

        $this->addFlash(self::FLASH_SUCCESS, sprintf('Task "%s" deleted', $task->getTitle()));

        return $this->redirectToList($dateTimeFactory->now(), $task);
    }

    private function redirectToList(\DateTimeInterface $now, Task $task): Response
    {
        if ($task->getEnded() !== null) {
            return $this->redirectToRoute('task_completed');
        }

        if ($task->getWait() === null || $task->getWait() < $now) {
            return $this->redirectToRoute('home');
        }

        return $this->redirectToRoute('task_waiting');
    }
}
