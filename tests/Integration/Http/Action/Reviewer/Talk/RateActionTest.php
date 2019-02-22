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

namespace OpenCFP\Test\Integration\Http\Action\Reviewer\Talk;

use OpenCFP\Domain\Model;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class RateActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     * @dataProvider providerValidRating
     *
     * @param mixed $rating
     */
    public function rateActionWorksCorrectly($rating)
    {
        /** @var Model\User $reviewer */
        $reviewer = factory(Model\User::class)->create()->first();

        /** @var Model\Talk $talk */
        $talk = factory(Model\Talk::class, 1)->create()->first();

        $response = $this
            ->asReviewer($reviewer->id)
            ->post('/reviewer/talks/' . $talk->id . '/rate', [
                'rating' => $rating,
            ]);

        $this->assertResponseIsSuccessful($response);

        $this->assertSame('1', $response->getContent());
    }

    public function providerValidRating(): array
    {
        return [
            'int' => [
                1,
            ],
            'integerish' => [
                '0',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider providerInvalidRating
     *
     * @param mixed $rating
     */
    public function rateActionReturnsFalseOnWrongRate($rating)
    {
        /** @var Model\User $reviewer */
        $reviewer = factory(Model\User::class)->create()->first();

        /** @var Model\Talk $talk */
        $talk = factory(Model\Talk::class, 1)->create()->first();

        $response = $this
            ->asReviewer($reviewer->id)
            ->post('/reviewer/talks/' . $talk->id . '/rate', [
                'rating' => $rating,
            ]);

        $this->assertResponseIsSuccessful($response);

        $this->assertSame('', $response->getContent());
    }

    public function providerInvalidRating(): array
    {
        return [
            'int-too-large' => [
                12,
            ],
            'string' => [
                'blabla',
            ],
        ];
    }
}
