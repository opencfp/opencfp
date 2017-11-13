<?php

namespace OpenCFP\Test\Domain\Entity\Mapper;

use OpenCFP\Application;
use OpenCFP\Environment;
use Spot\Locator;

/**
 * @group db
 */
class UserTest extends \PHPUnit\Framework\TestCase
{
    private $app;
    /** @var  \OpenCFP\Domain\Entity\Mapper\User */
    private $mapper;
    private $entities = ['User'];

    protected function setUp()
    {
        $this->app = new Application(BASE_PATH, Environment::testing());
        $cfg = new \Spot\Config;
        $cfg->addConnection('sqlite', [
            'dbname' => 'sqlite::memory',
            'driver' => 'pdo_sqlite',
        ]);

        $spot = new Locator($cfg);

        $this->app['spot'] = $spot;
        $this->mapper = $spot->mapper(\OpenCFP\Domain\Entity\User::class);

        foreach ($this->entities as $entity) {
            $spot->mapper('OpenCFP\Domain\Entity\\' . $entity)->migrate();
        }
    }

    /**
     * @test
     */
    public function findAllUsersWithNoSearch()
    {
        $this->createFiveUsers();
        $result = $this->mapper->search()->toArray();

        $this->assertCount(5, $result);
    }
    /**
     * @test
     */
    public function findUsersStartingWithAOnSearch()
    {
        $this->createFiveUsers();
        $result = $this->mapper->search('a%')->toArray();

        $this->assertCount(3, $result);
    }

    /**
     * @test
     */
    public function findPersonOnSearch()
    {
        $this->createFiveUsers();
        $result = $this->mapper->search('Arthur')->toArray();

        $this->assertCount(1, $result);
        $this->assertEquals('Hunter', $result[0]['last_name']);
    }

    /**
     * A helper function that creates 5 users of which we know the details
     */
    private function createFiveUsers()
    {
        $this->mapper->create(
            [
                'first_name' => 'Jesse',
                'last_name' => 'Kramer',
                'email' => 'jkramer@example.com',
                'password' => 'totallySecure1',
            ]
        );
        $this->mapper->create(
            [
                'first_name' => 'Arthur',
                'last_name' => 'Hunter',
                'email' => 'ahunter@example.com',
                'password' => 'totallySecure1',
            ]
        );
        $this->mapper->create(
            [
                'first_name' => 'Bauke',
                'last_name' => 'the Farmer',
                'email' => 'nvkmn@example.com',
                'password' => 'totallySecure1',
            ]
        );
        $this->mapper->create(
            [
                'first_name' => 'Adrie',
                'last_name' => 'de Slager',
                'email' => 'cowhammer@example.com',
                'password' => 'totallySecure1',
            ]
        );
        $this->mapper->create(
            [
                'first_name' => 'Antonius',
                'last_name' => 'von Bil',
                'email' => 'antonius@example.com',
                'password' => 'totallySecure1',
            ]
        );
    }
}
