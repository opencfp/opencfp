<?php

namespace OpenCFP\Test\Domain\Entity;

use OpenCFP\Application;
use OpenCFP\Environment;

/**
 * @group db
 */
class FavoriteTest extends \PHPUnit\Framework\TestCase
{
    private $app;
    private $mapper;
    private $talk;

    protected function setUp()
    {
        $this->app = new Application(BASE_PATH, Environment::testing());
        // Create an in-memory database
        $cfg = new \Spot\Config;
        $cfg->addConnection('sqlite', [
            'dbname' => 'sqlite::memory',
            'driver' => 'pdo_sqlite',
        ]);
        $spot = new \Spot\Locator($cfg);

        $this->app['spot'] = $spot;
        
        $this->mapper = $spot->mapper(\OpenCFP\Domain\Entity\Favorite::class);
        $this->mapper->migrate();

        // Create a talk
        $talk_mapper = $spot->mapper(\OpenCFP\Domain\Entity\Talk::class);
        $data        = [
            'title'       => 'Favorite Entity Test',
            'description' => 'This is a stubbed talk for a Favorite Entity Test',
            'user_id'     => 1,
        ];
        $talk_mapper->migrate();
        $this->talk = $talk_mapper->create($data);
    }

    /**
     * @test
     */
    public function relationsCreatedCorrectly()
    {
        $created = new \DateTime();
        $data    = [
            'id'            => 1,
            'admin_user_id' => 1,
            'talk_id'       => $this->talk->id,
            'created'       => $created,
        ];
        $favorite = $this->mapper->create($data);

        $this->assertEquals($data['id'], $favorite->id);
        $this->assertEquals($data['admin_user_id'], $favorite->admin_user_id);
        $this->assertEquals($this->talk->id, $favorite->talk->id);
        $this->assertEquals($data['created'], $favorite->created);
    }
}
