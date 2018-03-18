<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2018 OpenCFP
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
use OpenCFP\Kernel;
use PHPUnit\Framework;
use Symfony\Component\HttpFoundation;

final class ProjectCodeTest extends Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function productionClassesHaveUnitTests()
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
                Http\Action\Admin\DashboardAction::class,
                Http\Action\Admin\Talk\IndexAction::class,
                Http\Action\Profile\ChangePasswordProcessAction::class,
                Http\Action\Reviewer\DashboardAction::class,
                Http\Action\Reviewer\Speaker\IndexAction::class,
                Http\Action\Reviewer\Speaker\ViewAction::class,
                Http\Action\Admin\Talk\RateAction::class,
                Http\Action\Reviewer\Talk\IndexAction::class,
                Http\Action\Reviewer\Talk\RateAction::class,
                Http\Action\Signup\ProcessAction::class,
                Http\Controller\Admin\ExportsController::class,
                Http\Controller\Admin\SpeakersController::class,
                Http\Controller\Admin\TalksController::class,
                Http\Controller\ForgotController::class,
                Http\Form\ForgotFormType::class,
                Http\Form\ResetFormType::class,
                Infrastructure\Event\AuthenticationListener::class,
                Infrastructure\Event\CsrfValidationListener::class,
                Infrastructure\Event\ExceptionListener::class,
                Infrastructure\Event\RequestCleanerListener::class,
                Infrastructure\Event\TwigGlobalsListener::class,
                Infrastructure\Templating\TwigExtension::class,
                Kernel::class,
            ]
        );
    }

    /**
     * @test
     */
    public function controllerActionsUseResponseReturnType()
    {
        $actionsWithoutReturnTypes = $this->methodNames(\array_filter($this->controllerActions(), function (\ReflectionMethod $method) {
            $returnType = (string) $method->getReturnType();

            return $returnType !== HttpFoundation\Response::class;
        }));

        $this->assertEmpty($actionsWithoutReturnTypes, \sprintf(
            "Failed asserting that the controller actions\n\n%s\n\ndeclare \"%s\" as return type.",
            ' - ' . \implode("\n - ", $actionsWithoutReturnTypes),
            HttpFoundation\Response::class
        ));
    }

    /**
     * @test
     */
    public function controllerActionsUseActionSuffix()
    {
        $actionsWithoutSuffix = $this->methodNames(\array_filter($this->controllerActions(), function (\ReflectionMethod $method) {
            return \preg_match('/Action$/', $method->getName()) === 0;
        }));

        $this->assertEmpty($actionsWithoutSuffix, \sprintf(
            "Failed asserting that the controller actions\n\n%s\n\nuse  \"Action\" as suffix.",
            ' - ' . \implode("\n - ", $actionsWithoutSuffix)
        ));
    }

    /**
     * @return \ReflectionMethod[]
     */
    private function controllerActions(): array
    {
        $constructs = Classy\Constructs::fromDirectory(__DIR__ . '/../../classes/Http/Controller');

        $actions = [];

        foreach ($constructs as $construct) {
            $reflection = new \ReflectionClass($construct->name());

            if (!$reflection->isInstantiable()) {
                continue;
            }

            foreach ($reflection->getMethods() as $method) {
                if ($method->isAbstract() || $method->isConstructor() || !$method->isPublic() || $method->isStatic()) {
                    continue;
                }

                $actions[] = $method;
            }
        }

        return $actions;
    }

    /**
     * @param \ReflectionMethod[] $methods
     *
     * @return string[]
     */
    private function methodNames(array $methods): array
    {
        $methodNames = \array_map(function (\ReflectionMethod $method) {
            return \sprintf(
                '%s::%s',
                $method->getDeclaringClass()->getName(),
                $method->getName()
            );
        }, $methods);

        \sort($methodNames);

        return $methodNames;
    }

    /**
     * @test
     */
    public function classesAreAbstractOrFinal()
    {
        $this->assertClassesAreAbstractOrFinal(__DIR__ . '/..');
    }

    /**
     * @dataProvider providerProductionClassesAreAbstractOrFinal
     *
     * @param string $directory
     *
     * @test
     */
    public function productionClassesAreAbstractOrFinal(string $directory)
    {
        $this->assertClassesAreAbstractOrFinal($directory);
    }

    public function providerProductionClassesAreAbstractOrFinal(): array
    {
        $directories = [
            'http-actions' => __DIR__ . '/../../classes/Http/Action',
        ];

        return \array_map(function (string $directory) {
            return [
              $directory,
            ];
        }, $directories);
    }
}
