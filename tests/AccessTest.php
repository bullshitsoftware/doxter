<?php

namespace App\Test;

use App\Repository\TaskRepository;
use function array_key_exists;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class AccessTest extends WebTestCase
{
    private KernelBrowser $client;
    private RouterInterface $router;
    private TaskRepository $taskRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->router = static::getContainer()->get(RouterInterface::class);
        $this->taskRepository = static::getContainer()->get(TaskRepository::class);
    }

    /**
     * @dataProvider routesProvider
     */
    public function testAccess(string $route, ?callable $params = null): void
    {
        $routeParams = [];
        if (null !== $params) {
            $routeParams = $params($this);
        }

        $method = 'GET';
        $redirect = '/login';
        if (array_key_exists('route_overrides', $routeParams)) {
            $overrides = $routeParams['route_overrides'];
            if (array_key_exists('method', $overrides)) {
                $method = $overrides['method'];
                unset($overrides['method']);
            }
            if (array_key_exists('redirect', $overrides)) {
                $redirect = $overrides['redirect'];
                unset($overrides['redierect']);
            }
            unset($routeParams['route_overrides']);
        }

        $this->client->request($method, $this->router->generate($route, $routeParams));
        self::assertResponseRedirects($redirect);
    }

    public function testRoutesProvider(): void
    {
        $appRoutes = array_keys(iterator_to_array($this->router->getRouteCollection()));
        $testRoutes = array_map(fn (array $config) => $config[0], $this->routesProvider());
        self::assertSame(['login'], array_diff($appRoutes, $testRoutes));
    }

    public function routesProvider(): array
    {
        return [
            ['logout', fn () => ['route_overrides' => ['redirect' => 'http://localhost/login']]],
            ['home'],
            ['task_current'],
            ['task_waiting'],
            ['task_completed'],
            ['task_add'],
            ['task_view', fn (self $s) => ['id' => $s->taskRepository->findOneByTitle('Current task 1')->getId()]],
            ['task_edit', fn (self $s) => ['id' => $s->taskRepository->findOneByTitle('Current task 1')->getId()]],
            [
                'task_delete',
                fn (self $s) => [
                    'route_overrides' => ['method' => 'POST'],
                    'id' => $s->taskRepository->findOneByTitle('Current task 1')->getId(),
                ],
            ],
            ['settings'],
        ];
    }
}
