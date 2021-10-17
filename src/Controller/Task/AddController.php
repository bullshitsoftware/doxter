<?php

namespace App\Controller\Task;

use App\Entity\Task;
use App\Form\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/add', name: 'task_add')]
class AddController extends Controller
{
    public function __invoke(EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $task = new Task();
        $task->setUser($this->getUser());
        $task->setCreated($this->now());
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUpdated($this->now());
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash(self::FLASH_SUCCESS, sprintf('Task "%s" created', $task->getTitle()));

            return $this->redirectToRoute($this->taskListRoute($task));
        }

        return $this->render('task/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
