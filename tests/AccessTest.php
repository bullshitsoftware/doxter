<?php

declare(strict_types=1);

namespace App\Test;

use App\Tests\Controller\WebTestCase;
use function array_key_exists;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Routing\RouterInterface;

class AccessTest extends WebTestCase
{
    private KernelBrowser $client;
    private RouterInterface $router;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
        $this->router = self::getContainer()->get(RouterInterface::class);
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

    /**
     * @return array<array{0:string,1?:callable(self=):array<string,mixed>}>
     */
    public function routesProvider(): array
    {
        return [
            ['logout', fn () => ['route_overrides' => ['redirect' => 'http://localhost/login']]],
            ['home'],
            ['task_current'],
            ['task_waiting'],
            ['task_completed'],
            ['task_add'],
            ['task_view', fn () => ['id' => '2c2bbc1d-e729-4fde-935f-2f5faca6d905']],
            ['task_edit', fn () => ['id' => '2c2bbc1d-e729-4fde-935f-2f5faca6d905']],
            [
                'task_delete',
                fn () => [
                    'route_overrides' => ['method' => 'POST'],
                    'id' => '2c2bbc1d-e729-4fde-935f-2f5faca6d905',
                ],
            ],
            ['settings'],
            ['settings_password'],
            ['settings_import'],
        ];
    }
}
