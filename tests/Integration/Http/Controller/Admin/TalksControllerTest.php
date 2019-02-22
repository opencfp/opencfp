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

namespace OpenCFP\Test\Integration\Http\Controller\Admin;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\User;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class TalksControllerTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * A test to make sure that comments can be correctly tracked
     *
     * @test
     */
    public function talkIsCorrectlyCommentedOn()
    {
        /** @var User $admin */
        $admin = factory(User::class, 1)->create()->first();

        /** @var Talk $talk */
        $talk = factory(Talk::class, 1)->create()->first();

        $response = $this
            ->asAdmin($admin->id)
            ->post('/admin/talks/' . $talk->id . '/comment', [
                'comment' => 'Great Talk i rate 10/10',
            ]);

        $this->assertResponseBodyNotContains('Server Error', $response);
        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function selectActionWorksCorrectly()
    {
        /** @var User $admin */
        $admin = factory(User::class, 1)->create()->first();

        /** @var Talk $talk */
        $talk = factory(Talk::class, 1)->create()->first();

        $response = $this
            ->asAdmin($admin->id)
            ->post('/admin/talks/' . $talk->id . '/select');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('1', $response);
    }

    /**
     * @test
     */
    public function selectActionDeletesCorrectly()
    {
        /** @var User $admin */
        $admin = factory(User::class, 1)->create()->first();

        /** @var Talk $talk */
        $talk = factory(Talk::class, 1)->create()->first();

        $response = $this
            ->asAdmin($admin->id)
            ->post('/admin/talks/' . $talk->id . '/select', [
                'delete' => 1,
            ]);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('1', $response);
    }

    /**
     * @test
     */
    public function selectActionReturnsFalseWhenTalkNotFound()
    {
        /** @var User $admin */
        $admin = factory(User::class, 1)->create()->first();

        $response = $this
            ->asAdmin($admin->id)
            ->post(\sprintf(
                '/admin/talks/%s/select',
                $this->faker()->numberBetween(1)
            ));

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyNotContains('1', $response);
    }

    /**
     * @test
     */
    public function favoriteActionWorksCorrectly()
    {
        /** @var User $admin */
        $admin = factory(User::class, 1)->create()->first();

        /** @var Talk $talk */
        $talk = factory(Talk::class, 1)->create()->first();

        $response = $this
            ->asAdmin($admin->id)
            ->post('/admin/talks/' . $talk->id . '/favorite');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('1', $response);
    }

    /**
     * @test
     */
    public function favoriteActionDeletesCorrectly()
    {
        /** @var User $admin */
        $admin = factory(User::class, 1)->create()->first();

        /** @var Talk $talk */
        $talk = factory(Talk::class, 1)->create()->first();

        $response = $this
            ->asAdmin($admin->id)
            ->post('/admin/talks/' . $talk->id . '/favorite', [
                'delete' => 1,
            ]);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('1', $response);
    }

    /**
     * @test
     */
    public function favoriteActionDoesNotErrorWhenTryingToDeleteFavoriteThatDoesNoExist()
    {
        /** @var User $admin */
        $admin = factory(User::class, 1)->create()->first();

        $response = $this
            ->asAdmin($admin->id)
            ->post(
                \sprintf(
                    '/admin/talks/%s/favorite',
                    $this->faker()->numberBetween(1)
                ),
                [
                'delete' => 1,
                ]
            );

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyNotContains('1', $response);
    }
}
