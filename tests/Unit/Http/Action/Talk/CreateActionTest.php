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

namespace OpenCFP\Test\Unit\Http\Action\Talk;

use OpenCFP\Domain\CallForPapers;
use OpenCFP\Http\Action\Talk\CreateAction;
use OpenCFP\Http\View;
use OpenCFP\Test\Unit\Http\Action\AbstractActionTestCase;
use PHPUnit\Framework;
use Symfony\Component\HttpFoundation;

final class CreateActionTest extends AbstractActionTestCase
{
    /**
     * @test
     */
    public function redirectsToDashboardIfCallForPapersIsClosed()
    {
        $url = $this->faker()->url;

        $session = $this->createSessionMock();

        $session
            ->expects($this->once())
            ->method('set')
            ->with(
                $this->identicalTo('flash'),
                $this->identicalTo([
                    'type'  => 'error',
                    'short' => 'Error',
                    'ext'   => 'You cannot create talks once the call for papers has ended',
                ])
            );

        $request = $this->createRequestMock();

        $request
            ->expects($this->once())
            ->method('getSession')
            ->willReturn($session);

        $talkHelper = $this->createTalkHelperMock();

        $talkHelper
            ->expects($this->never())
            ->method($this->anything());

        $callForPapers = $this->createCallForPapersMock();

        $callForPapers
            ->expects($this->once())
            ->method('isOpen')
            ->willReturn(false);

        $twig = $this->createTwigMock();

        $twig
            ->expects($this->never())
            ->method($this->anything());

        $urlGenerator = $this->createUrlGeneratorMock();

        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($this->identicalTo('dashboard'))
            ->willReturn($url);

        $action = new CreateAction(
            $talkHelper,
            $callForPapers,
            $twig,
            $urlGenerator
        );

        /** @var HttpFoundation\RedirectResponse $response */
        $response = $action($request);

        $this->assertInstanceOf(HttpFoundation\RedirectResponse::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame($url, $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function rendersTalkCreationIfCallForPapersIsOpen()
    {
        $faker = $this->faker();

        $content = $faker->text;

        $formAction = $faker->url;

        $categories = $faker->words;
        $types      = $faker->words;
        $levels     = $faker->words;

        $category    = $faker->word;
        $description = $faker->text;
        $desired     = $faker->word;
        $level       = $faker->word;
        $other       = $faker->word;
        $slides      = $faker->word;
        $sponsor     = $faker->word;
        $title       = $faker->sentence;
        $type        = $faker->word;

        $fields = [
            'category'    => $category,
            'description' => $description,
            'desired'     => $desired,
            'level'       => $level,
            'other'       => $other,
            'slides'      => $slides,
            'sponsor'     => $sponsor,
            'title'       => $title,
            'type'        => $type,
        ];

        $request = $this->createRequestMock();

        $request
            ->expects($this->exactly(\count($fields)))
            ->method('get')
            ->willReturnCallback(function (string $name) use ($fields) {
                if (\array_key_exists($name, $fields)) {
                    return $fields[$name];
                }
            });

        $talkHelper = $this->createTalkHelperMock();

        $talkHelper
            ->expects($this->once())
            ->method('getTalkCategories')
            ->willReturn($categories);

        $talkHelper
            ->expects($this->once())
            ->method('getTalkTypes')
            ->willReturn($types);

        $talkHelper
            ->expects($this->once())
            ->method('getTalkLevels')
            ->willReturn($levels);

        $callForPapers = $this->createCallForPapersMock();

        $callForPapers
            ->expects($this->once())
            ->method('isOpen')
            ->willReturn(true);

        $twig = $this->createTwigMock();

        $twig
            ->expects($this->once())
            ->method('render')
            ->with(
                $this->identicalTo('talk/create.twig'),
                $this->identicalTo([
                    'formAction'     => $formAction,
                    'talkCategories' => $categories,
                    'talkTypes'      => $types,
                    'talkLevels'     => $levels,
                    'title'          => $title,
                    'description'    => $description,
                    'type'           => $type,
                    'level'          => $level,
                    'category'       => $category,
                    'desired'        => $desired,
                    'slides'         => $slides,
                    'other'          => $other,
                    'sponsor'        => $sponsor,
                    'buttonInfo'     => 'Submit my talk!',
                ])
            )
            ->willReturn($content);

        $urlGenerator = $this->createUrlGeneratorMock();

        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($this->identicalTo('talk_create'))
            ->willReturn($formAction);

        $action = new CreateAction(
            $talkHelper,
            $callForPapers,
            $twig,
            $urlGenerator
        );

        $response = $action($request);

        $this->assertInstanceOf(HttpFoundation\Response::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($content, $response->getContent());
    }

    /**
     * @deprecated
     *
     * @return Framework\MockObject\MockObject|View\TalkHelper
     */
    private function createTalkHelperMock(): View\TalkHelper
    {
        return $this->createMock(View\TalkHelper::class);
    }

    /**
     * @deprecated
     *
     * @return CallForPapers|Framework\MockObject\MockObject
     */
    private function createCallForPapersMock(): CallForPapers
    {
        return $this->createMock(CallForPapers::class);
    }
}
