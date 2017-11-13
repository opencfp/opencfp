<?php

namespace OpenCFP\Test\Infrastructure\Auth;

use OpenCFP\Infrastructure\Auth\SentryAccountManagement;
use OpenCFP\Test\BaseTestCase;
use OpenCFP\Test\RefreshDatabase;

/**
 * Class SentryAccountManagementTest
 *
 * @package OpenCFP\Test\Infrastructure\Auth
 * @group db
 */
class SentryAccountManagementTest extends BaseTestCase
{
    use RefreshDatabase;
    use SentryTestHelpers;

    /**
     * @var SentryAccountManagement
     */
    private $sut;

    public function setUp()
    {
        parent::setUp();
        $this->sut = new SentryAccountManagement($this->getSentry());
    }

    /** @test */
    public function can_create_users_with_credentials()
    {
        $this->sut->create('test@example.com', 'secret', [
            'first_name' => 'Test',
            'last_name' => 'Account',
        ]);

        $user = $this->sut->findByLogin('test@example.com');

        $this->assertEquals('Test Account', "{$user->first_name} {$user->last_name}");
    }

    /** @test */
    public function users_are_speakers_by_default()
    {
        $user = $this->sut->create('test@example.com', 'secret', [
            'first_name' => 'Test',
            'last_name' => 'Account',
        ]);

        $group = $user->getGroups()[0];
        
        $this->assertEquals('Speakers', $group->getName());
    }

    /** @test */
    public function can_activate_user()
    {
        $user = $this->sut->create('test@example.com', 'secret');
        $this->assertFalse($user->isActivated());

        $this->sut->activate('test@example.com');

        $this->assertTrue($this->sut->findByLogin('test@example.com')->isActivated());
    }

    /** @test */
    public function can_promote_and_demote_user()
    {
        $this->sut->create('test@example.com', 'secret', [
            'first_name' => 'Test',
            'last_name' => 'Account',
        ]);

        $this->assertCount(0, $this->sut->findByRole('Admin'));

        $this->sut->promoteTo('test@example.com');
        $this->assertCount(1, $this->sut->findByRole('Admin'));

        $this->sut->demoteFrom('test@example.com');
        $this->assertCount(0, $this->sut->findByRole('Admin'));
    }
}
