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

namespace OpenCFP\Test\AutoReview;

use Localheinz\Test\Util\Helper;
use OpenCFP\Domain;
use OpenCFP\Http;
use OpenCFP\Infrastructure;
use OpenCFP\Kernel;
use PHPUnit\Framework;

final class TestCodeTest extends Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function productionClassesHaveUnitTests(): void
    {
        $this->assertClassesHaveTests(
            __DIR__ . '/../../src',
            'OpenCFP\\',
            'OpenCFP\\Test\\Unit\\',
            [
                Domain\Model\Airport::class,
                Domain\Model\Eloquent::class,
                Domain\Model\Favorite::class,
                Domain\Model\Persistence::class,
                Domain\Model\Reminder::class,
                Domain\Model\Talk::class,
                Domain\Model\TalkComment::class,
                Domain\Model\TalkMeta::class,
                Domain\Model\Throttle::class,
                Domain\Model\User::class,
                Domain\Services\ProfileImageProcessor::class,
                Domain\Talk\TalkFormatter::class,
                Domain\Talk\TalkHandler::class,
                Http\Action\Admin\DashboardAction::class,
                Http\Action\Admin\Talk\IndexAction::class,
                Http\Action\Admin\Talk\RateAction::class,
                Http\Action\Profile\ChangePasswordProcessAction::class,
                Http\Action\Profile\DeleteAction::class,
                Http\Action\Profile\ProcessDeleteAction::class,
                Http\Action\Reviewer\DashboardAction::class,
                Http\Action\Reviewer\Talk\IndexAction::class,
                Http\Action\Reviewer\Talk\RateAction::class,
                Http\Action\Signup\PrivacyAction::class,
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
    public function classesAreAbstractOrFinal(): void
    {
        $this->assertClassesAreAbstractOrFinal(__DIR__ . '/..');
    }
}
