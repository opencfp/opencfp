<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Integration;

use Illuminate\Database\Capsule;
use Illuminate\Database\Connection;
use Localheinz\Test\Util\Helper;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenCFP\Domain\CallForPapers;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Environment;
use OpenCFP\Infrastructure\Auth\UserInterface;
use OpenCFP\Kernel;
use OpenCFP\Test\Helper\MockAuthentication;
use OpenCFP\Test\Helper\MockIdentityProvider;
use OpenCFP\Test\Helper\ResponseHelper;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session;

abstract class WebTestCase extends KernelTestCase
{
    use Helper;
    use MockeryPHPUnitIntegration;
    use ResponseHelper;

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

    protected function setUp()
    {
        $this->refreshContainer();

        if ($this instanceof TransactionalTestCase) {
            $this->databaseConnection()->beginTransaction();
        }
    }

    protected function tearDown()
    {
        if ($this instanceof TransactionalTestCase) {
            $this->databaseConnection()->rollBack();
        }

        parent::tearDown();
    }

    final protected static function getKernelClass()
    {
        return Kernel::class;
    }

    protected function refreshContainer()
    {
        self::bootKernel(['environment' => Environment::TYPE_TESTING, 'debug' => true]);
        $this->container = self::$kernel->getContainer();
    }

    private function databaseConnection(): Connection
    {
        /** @var Capsule\Manager $capsule */
        $capsule = $this->container->get(Capsule\Manager::class);

        return $capsule->getConnection();
    }

    final protected function createClient(): Client
    {
        return $this->container->get('test.client');
    }

    final protected function call(string $method, string $uri, array $parameters = [], array $cookies = [], array $files = [], array $server = [], string $content = null): Response
    {
        $client = $this->createClient();

        foreach ($cookies as $key => $value) {
            $client->getCookieJar()->set(new Cookie($key, $value));
        }

        $client->request($method, $uri, $parameters, $files, $server, $content);

        return $client->getResponse();
    }

    final protected function get(string $uri, array $parameters = [], array $cookies = [], array $files = [], array $server = [], string $content = null): Response
    {
        return $this->call('GET', $uri, $parameters, $cookies, $files, $server, $content);
    }

    final protected function post(string $uri, array $parameters = [], array $cookies = [], array $files = [], array $server = [], string $content = null): Response
    {
        return $this->call('POST', $uri, $parameters, $cookies, $files, $server, $content);
    }

    final protected function patch(string $uri, array $parameters = [], array $cookies = [], array $files = [], array $server = [], string $content = null): Response
    {
        return $this->call('PATCH', $uri, $parameters, $cookies, $files, $server, $content);
    }

    final protected function delete(string $uri, array $parameters = [], array $cookies = [], array $files = [], array $server = [], string $content = null): Response
    {
        return $this->call('DELETE', $uri, $parameters, $cookies, $files, $server, $content);
    }

    final protected function callForPapersIsOpen(): self
    {
        $cfp    = $this->container->get(CallForPapers::class);
        $method = new \ReflectionMethod(CallForPapers::class, 'setEndDate');
        $method->setAccessible(true);
        $method->invoke($cfp, new \DateTimeImmutable('+1 week'));

        $this->container->get('twig')->addGlobal('cfp_open', $cfp->isOpen());

        return $this;
    }

    final protected function callForPapersIsClosed(): self
    {
        $cfp    = $this->container->get(CallForPapers::class);
        $method = new \ReflectionMethod(CallForPapers::class, 'setEndDate');
        $method->setAccessible(true);
        $method->invoke($cfp, new \DateTimeImmutable('-1 week'));

        $this->container->get('twig')->addGlobal('cfp_open', $cfp->isOpen());

        return $this;
    }

    final protected function isOnlineConference(): self
    {
        $config                      = $this->container->getParameter('config.application');
        $config['online_conference'] = true;
        $this->container->get('twig')->addGlobal('site', $config);

        return $this;
    }

    final protected function asLoggedInSpeaker(int $id): self
    {
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('id')->andReturn($id);
        $user->shouldReceive('getId')->andReturn($id);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(false);
        $user->shouldReceive('hasAccess')->with('reviewer')->andReturn(false);
        $user->shouldReceive('getLogin')->andReturn('my@email.com');

        /** @var MockAuthentication $authentication */
        $authentication = $this->container->get(Authentication::class);
        $authentication->overrideUser($user);

        /** @var MockIdentityProvider $identityProvider */
        $identityProvider = $this->container->get(IdentityProvider::class);
        $identityProvider->overrideCurrentUser(new User(['id' => $id]));

        return $this;
    }

    final protected function asAdmin(int $id): self
    {
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('id')->andReturn($id);
        $user->shouldReceive('getId')->andReturn($id);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(true);
        $user->shouldReceive('hasAccess')->with('reviewer')->andReturn(false);

        /** @var MockAuthentication $authentication */
        $authentication = $this->container->get(Authentication::class);
        $authentication->overrideUser($user);

        /** @var MockIdentityProvider $identityProvider */
        $identityProvider = $this->container->get(IdentityProvider::class);
        $identityProvider->overrideCurrentUser(new User(['id' => $id]));

        return $this;
    }

    final protected function asReviewer(int $id): self
    {
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('id')->andReturn($id);
        $user->shouldReceive('getId')->andReturn($id);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(false);
        $user->shouldReceive('hasAccess')->with('reviewer')->andReturn(true);

        /** @var MockAuthentication $authentication */
        $authentication = $this->container->get(Authentication::class);
        $authentication->overrideUser($user);

        /** @var MockIdentityProvider $identityProvider */
        $identityProvider = $this->container->get(IdentityProvider::class);
        $identityProvider->overrideCurrentUser(new User(['id' => $id]));

        return $this;
    }

    final protected function session(): Session\SessionInterface
    {
        return $this->container->get('session');
    }

    final protected function withFakeSwiftMailer(): self
    {
        $fakeMailer = Mockery::mock(\Swift_Mailer::class);
        $fakeMailer->shouldReceive('send')->andThrow(\Swift_TransportException::class);
        $this->container->set(\Swift_Mailer::class, $fakeMailer);

        return $this;
    }
}
