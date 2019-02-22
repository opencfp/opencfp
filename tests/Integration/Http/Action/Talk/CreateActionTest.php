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

namespace OpenCFP\Test\Integration\Http\Action\Talk;

use OpenCFP\Domain\CallForPapers;
use OpenCFP\Domain\Model;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class CreateActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function allowSubmissionsUntilRightBeforeMidnightDayOfClose()
    {
        // Set CFP end to today (whenever test is run)
        // Previously, this fails because it checked midnight
        // for the current date. `isCfpOpen` now uses 11:59pm current date.
        $now = new \DateTime();

        $callForPapers = $this->container->get(CallForPapers::class);

        $method = new \ReflectionMethod(CallForPapers::class, 'setEndDate');

        $method->setAccessible(true);
        $method->invoke($callForPapers, new \DateTimeImmutable($now->format('M. jS, Y')));

        $this->container->get('twig')->addGlobal('cfp_open', $callForPapers->isOpen());

        /** @var Model\User $speaker */
        $speaker = factory(Model\User::class)->create()->first();

        /*
         * This should not have a flash message. The fact that this
         * is true means code is working as intended. Previously this fails
         * because the CFP incorrectly ended at 12:00am the day of, not 11:59pm.
         */
        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->get('/talk/create');

        $this->assertResponseBodyContains('Create Your Talk', $response);
    }

    /**
     * @test
     */
    public function cantCreateTalkAfterCFPIsClosed()
    {
        /** @var Model\User $speaker */
        $speaker = factory(Model\User::class)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->callForPapersIsClosed()
            ->get('/talk/create');

        $this->assertResponseIsRedirect($response);
        $this->assertResponseBodyNotContains('Create Your Talk', $response);
        $this->assertSessionHasFlashMessage('You cannot create talks once the call for papers has ended', $this->session());
    }
}
