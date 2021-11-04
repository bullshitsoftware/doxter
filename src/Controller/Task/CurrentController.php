<?php

namespace App\Controller\Task;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Service\TaskWeigher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

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
        $tasks = $repository->findCurrentByUser($this->getUser(), $request->get('q'), $now);

        $weigher = new TaskWeigher(
            [
                'tag' => ['foo' => 1, 'bar' => 2],
                'date' => [
                    'age' => 1,
                    'started' => 2,
                    'due' => 10,
                ],
            ],
            $now,
        );

        $id = fn (Uuid $id) => $id->toRfc4122();

        $weights = [];
        foreach ($tasks as $task) {
            $weights[$id($task->getId())] = $weigher->weigh($task);
        }
        usort(
            $tasks,
            function (Task $a, Task $b) use ($id, $weights): int {
                $byWeight = -1 * ($weights[$id($a->getId())] <=> $weights[$id($b->getId())]);
                if (0 !== $byWeight) {
                    return $byWeight;
                }

                return strcmp($a->getTitle(), $b->getTitle());
            }
        );

        return $this->render('task/current.html.twig', [
            'now' => $now,
            'id' => $id,
            'tasks' => $tasks,
            'urgs' => $weights,
        ]);
    }
}
