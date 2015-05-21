<?php
use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Environment;

class FavoriteEntityTest extends \PHPUnit_Framework_TestCase
{
    public $app;
    public $mapper;
    public $talk;

    public function setup()
    {
        $this->app = new Application(BASE_PATH, Environment::testing());
        // Create an in-memory database
        $cfg = new \Spot\Config;
        $cfg->addConnection('sqlite', [
            'dbname' => 'sqlite::memory',
            'driver' => 'pdo_sqlite'
        ]);
        $this->app['spot'] = new \Spot\Locator($cfg);
        $this->mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Favorite');
        $this->mapper->migrate();

        // Create a talk
        $talk_mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $data = [
            'title' => 'Favorite Entity Test',
            'description' => 'This is a stubbed talk for a Favorite Entity Test',
            'user_id' => 1
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
        $data = [
            'id' => 1,
            'admin_user_id' => 1,
            'talk_id' => $this->talk->id,
            'created' => $created
        ];
        $favorite = $this->mapper->create($data);

        $this->assertEquals($data['id'], $favorite->id);
        $this->assertEquals($data['admin_user_id'], $favorite->admin_user_id);
        $this->assertEquals($this->talk->id, $favorite->talk->id);
        $this->assertEquals($data['created'], $favorite->created);
    }
}
