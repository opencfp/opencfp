<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Integration;

use Illuminate\Database\Capsule;
use Localheinz\Test\Util\Helper;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenCFP\Application;
use OpenCFP\Domain\CallForPapers;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Environment;
use OpenCFP\Infrastructure\Auth\UserInterface;
use OpenCFP\Test\Helper\MockableAuthenticator;
use OpenCFP\Test\Helper\MockableIdentityProvider;
use OpenCFP\Test\Helper\RefreshDatabase;
use OpenCFP\Test\Helper\ResponseHelper;
use Pimple\Psr11\Container;
use Psr\Container\ContainerInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;

abstract class WebTestCase extends \Silex\WebTestCase
{
    use Helper;
    use MockeryPHPUnitIntegration;
    use ResponseHelper;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Additional headers for a request.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Additional server variables to be sent with a request.
     *
     * @var array
     */
    protected $server = [];

    public static function setUpBeforeClass()
    {
        self::runBeforeClassTraits();
    }

    protected function setUp()
    {
        parent::setUp();

        $this->refreshContainer();

        if ($this instanceof RequiresDatabaseReset) {
            $this->resetDatabase();
        }
    }

    public function createApplication()
    {
        return new Application(
            __DIR__ . '/../..',
            Environment::testing()
        );
    }

    private function refreshContainer()
    {
        $this->container = new Container($this->app);
    }

    private function resetDatabase()
    {
        /** @var Capsule\Manager $capsule */
        $capsule = $this->container->get(Capsule\Manager::class);

        $capsule->getConnection()->unprepared(\file_get_contents(__DIR__ . '/../dump.sql'));
    }

    /**
     * Runs setups from Traits that are needed before the class is setup (as called from setUpBeforeClass)
     */
    private static function runBeforeClassTraits()
    {
        $uses = \array_flip(class_uses_recursive(static::class));

        if (isset($uses[RefreshDatabase::class])) {
            static::setUpDatabase();
        }
    }

    public function call(string $method, string $uri, array $parameters = [], array $cookies = [], array $files = [], array $server = [], string $content = null): Response
    {
        $client = $this->createClient();

        foreach ($cookies as $key => $value) {
            $client->getCookieJar()->set(new Cookie($key, $value));
        }

        $client->request($method, $uri, $parameters, $files, $server, $content);

        return $client->getResponse();
    }

    public function get(string $uri, array $parameters = [], array $cookies = [], array $files = [], array $server = [], string $content = null): Response
    {
        return $this->call('GET', $uri, $parameters, $cookies, $files, $server, $content);
    }

    public function post(string $uri, array $parameters = [], array $cookies = [], array $files = [], array $server = [], string $content = null): Response
    {
        return $this->call('POST', $uri, $parameters, $cookies, $files, $server, $content);
    }

    public function patch(string $uri, array $parameters = [], array $cookies = [], array $files = [], array $server = [], string $content = null): Response
    {
        return $this->call('PATCH', $uri, $parameters, $cookies, $files, $server, $content);
    }

    public function delete(string $uri, array $parameters = [], array $cookies = [], array $files = [], array $server = [], string $content = null): Response
    {
        return $this->call('DELETE', $uri, $parameters, $cookies, $files, $server, $content);
    }

    public function callForPapersIsOpen(): self
    {
        $cfp    = $this->container->get(CallForPapers::class);
        $method = new \ReflectionMethod(CallForPapers::class, 'setEndDate');
        $method->setAccessible(true);
        $method->invoke($cfp, new \DateTimeImmutable('+1 week'));

        $this->container->get('twig')->addGlobal('cfp_open', $cfp->isOpen());

        return $this;
    }

    public function callForPapersIsClosed(): self
    {
        $cfp    = $this->container->get(CallForPapers::class);
        $method = new \ReflectionMethod(CallForPapers::class, 'setEndDate');
        $method->setAccessible(true);
        $method->invoke($cfp, new \DateTimeImmutable('-1 week'));

        $this->container->get('twig')->addGlobal('cfp_open', $cfp->isOpen());

        return $this;
    }

    public function isOnlineConference(): self
    {
        $config                                     = $this->container->get('config');
        $config['application']['online_conference'] = true;
        $this->container->get('twig')->addGlobal('site', $config['application']);

        return $this;
    }

    public function asLoggedInSpeaker(int $id = 1): self
    {
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('id')->andReturn($id);
        $user->shouldReceive('getId')->andReturn($id);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(false);
        $user->shouldReceive('hasPermission')->with('admin')->andReturn(false);
        $user->shouldReceive('hasAccess')->with('reviewer')->andReturn(false);
        $user->shouldReceive('hasPermission')->with('reviewer')->andReturn(false);
        $user->shouldReceive('getLogin')->andReturn('my@email.com');

        /** @var MockableAuthenticator $authentication */
        $authentication = $this->container->get(Authentication::class);
        $authentication->overrideUser($user);

        /** @var MockableIdentityProvider $identityProvider */
        $identityProvider = $this->container->get(IdentityProvider::class);
        $identityProvider->overrideCurrentUser(new User(['id' => $id]));

        return $this;
    }

    public function asAdmin(int $id = 1): self
    {
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('id')->andReturn($id);
        $user->shouldReceive('getId')->andReturn($id);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(true);
        $user->shouldReceive('hasPermission')->with('admin')->andReturn(true);
        $user->shouldReceive('hasAccess')->with('reviewer')->andReturn(false);
        $user->shouldReceive('hasPermission')->with('reviewer')->andReturn(false);

        /** @var MockableAuthenticator $authentication */
        $authentication = $this->container->get(Authentication::class);
        $authentication->overrideUser($user);

        /** @var MockableIdentityProvider $identityProvider */
        $identityProvider = $this->container->get(IdentityProvider::class);
        $identityProvider->overrideCurrentUser(new User(['id' => $id]));

        return $this;
    }

    public function asReviewer(int $id = 1): self
    {
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('id')->andReturn($id);
        $user->shouldReceive('getId')->andReturn($id);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(false);
        $user->shouldReceive('hasPermission')->with('admin')->andReturn(false);
        $user->shouldReceive('hasAccess')->with('reviewer')->andReturn(true);
        $user->shouldReceive('hasPermission')->with('reviewer')->andReturn(true);

        /** @var MockableAuthenticator $authentication */
        $authentication = $this->container->get(Authentication::class);
        $authentication->overrideUser($user);

        /** @var MockableIdentityProvider $identityProvider */
        $identityProvider = $this->container->get(IdentityProvider::class);
        $identityProvider->overrideCurrentUser(new User(['id' => $id]));

        return $this;
    }
}
