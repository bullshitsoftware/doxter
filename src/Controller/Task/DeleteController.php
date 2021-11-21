<?php

declare(strict_types=1);

namespace App\Controller\Task;

use App\Entity\Task;
use App\Security\Voter\TaskVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/delete/{id}', name: 'task_delete', methods: ['POST'])]
class DeleteController extends Controller
{
    public function __invoke(EntityManagerInterface $entityManager, Request $request, Task $task): Response
    {
        $this->denyAccessUnlessGranted(TaskVoter::DELETE, $task);
        if (!$this->isCsrfTokenValid('task', (string) $request->request->get('_token'))) {
            $this->addFlash(
                self::FLASH_ERROR,
                sprintf('Failed to delete "%s" task. Please try again', $task->getTitle()),
            );

            return $this->redirectToRoute('task_view', ['id' => $task->getId()]);
        }

        $entityManager->remove($task);
        $entityManager->flush();

        $this->addFlash(self::FLASH_SUCCESS, sprintf('Task "%s" deleted', $task->getTitle()));

        return $this->redirectToRoute($this->taskListRoute($task));
    }
}
