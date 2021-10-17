<?php

namespace App\Controller\Task;

use App\Entity\Task;
use App\Form\TaskType;
use App\Security\Voter\TaskVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/edit/{id}', name: 'task_edit')]
class EditController extends Controller
{
    public function __invoke(EntityManagerInterface $entityManager, Request $request, Task $task): Response
    {
        $this->denyAccessUnlessGranted(TaskVoter::EDIT, $task);

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUpdated($this->now());
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash(self::FLASH_SUCCESS, sprintf('Task "%s" updated', $task->getTitle()));

            return $request->request->has('apply')
                ? $this->redirectToRoute('task_view', ['id' => $task->getId()])
                : $this->redirectToRoute($this->taskListRoute($task));
        }

        $request->attributes->set('nav_route', $this->taskListRoute($task));

        return $this->render('task/edit.html.twig', [
            'nav_route_override' => $this->taskListRoute($task),
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }
}
