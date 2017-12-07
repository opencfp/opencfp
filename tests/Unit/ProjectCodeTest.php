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

namespace OpenCFP\Test\Unit;

use Localheinz\Classy;
use Localheinz\Test\Util\Helper;
use OpenCFP\Domain;
use OpenCFP\Http;
use OpenCFP\Infrastructure;
use OpenCFP\Provider;
use PHPUnit\Framework;
use Symfony\Component\HttpFoundation;

/**
 * @coversNothing
 */
final class ProjectCodeTest extends Framework\TestCase
{
    use Helper;

    public function testProductionClassesHaveUnitTests()
    {
        $this->assertClassesHaveTests(
            __DIR__ . '/../../classes',
            'OpenCFP\\',
            'OpenCFP\\Test\\Unit\\',
            [
                Domain\Model\Airport::class,
                Domain\Model\Eloquent::class,
                Domain\Model\Favorite::class,
                Domain\Model\Talk::class,
                Domain\Model\TalkComment::class,
                Domain\Model\TalkMeta::class,
                Domain\Model\User::class,
                Domain\Services\ProfileImageProcessor::class,
                Domain\Talk\TalkFormatter::class,
                Domain\Talk\TalkHandler::class,
                Http\Controller\Admin\DashboardController::class,
                Http\Controller\Admin\ExportsController::class,
                Http\Controller\Admin\SpeakersController::class,
                Http\Controller\Admin\TalksController::class,
                Http\Controller\DashboardController::class,
                Http\Controller\ForgotController::class,
                Http\Controller\PagesController::class,
                Http\Controller\ProfileController::class,
                Http\Controller\Reviewer\DashboardController::class,
                Http\Controller\Reviewer\SpeakersController::class,
                Http\Controller\Reviewer\TalksController::class,
                Http\Controller\SecurityController::class,
                Http\Controller\SignupController::class,
                Http\Controller\TalkController::class,
                Http\Form\ForgotForm::class,
                Http\Form\ResetForm::class,
                Http\View\TalkHelper::class,
                Infrastructure\Event\AuthenticationListener::class,
                Infrastructure\Event\CsrfValidationListener::class,
                Infrastructure\Event\ExceptionListener::class,
                Infrastructure\Event\TwigGlobalsListener::class,
                Infrastructure\Templating\TwigExtension::class,
                Provider\ApplicationServiceProvider::class,
                Provider\CallForPapersProvider::class,
                Provider\ControllerResolver::class,
                Provider\ControllerResolverServiceProvider::class,
                Provider\Gateways\RequestCleaner::class,
                Provider\Gateways\WebGatewayProvider::class,
                Provider\Gateways\ConsoleGatewayProvider::class,
                Provider\HtmlPurifierServiceProvider::class,
                Provider\ImageProcessorProvider::class,
                Provider\ResetEmailerServiceProvider::class,
                Provider\TalkFilterProvider::class,
                Provider\TalkHandlerProvider::class,
                Provider\TalkHelperProvider::class,
                Provider\TalkRatingProvider::class,
                Provider\TwigServiceProvider::class,
                Provider\YamlConfigDriver::class,
            ]
        );
    }

    public function testControllerActionsUseResponseReturnType()
    {
        $constructs = Classy\Constructs::fromDirectory(__DIR__ . '/../../classes/Http/Controller');

        $actions = [];

        foreach ($constructs as $construct) {
            $reflection = new \ReflectionClass($construct->name());

            if (!$reflection->isInstantiable()) {
                continue;
            }

            $methods = \array_filter($reflection->getMethods(), function (\ReflectionMethod $method) {
                if ($method->isAbstract() || $method->isConstructor() || !$method->isPublic() || $method->isStatic()) {
                    return false;
                }

                $returnType = (string) $method->getReturnType();

                return $returnType !== HttpFoundation\Response::class;
            });

            /** @var \ReflectionMethod[] $methods */
            foreach ($methods as $method) {
                $actions[] = \sprintf(
                    '%s::%s',
                    $method->getDeclaringClass()->getName(),
                    $method->getName()
                );
            }
        }

        $actions = \array_unique($actions);

        \sort($actions);

        $this->assertEmpty($actions, \sprintf(
            "Failed asserting that the controller actions\n\n%s\n\ndeclare \"%s\" as return type.",
            ' - ' . \implode("\n - ", $actions),
            HttpFoundation\Response::class
        ));
    }
    
    public function testTestClassesAreAbstractOrFinal()
    {
        $this->assertClassesAreAbstractOrFinal(__DIR__ . '/..');
    }

    /**
     * @test
     */
    public function providerClassesAreAbstractOrFinal()
    {
        $this->assertClassesAreAbstractOrFinal(__DIR__ . '/../../classes/Provider');
    }
}
