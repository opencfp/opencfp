<?php
use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Environment;

class TalkEntityTest extends \PHPUnit_Framework_TestCase
{
    public $app;
    public $mapper;

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
        $this->mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $this->mapper->migrate();
    }

    /**
     * @test
     */
    public function utf8CharactersCorrectlyEncoded()
    {
        $title = "Battle: Feature Branches VS Feature Switching (╯°□°)╯︵ ┻━┻ ︵ ╯(°□° ╯)";
        $data = array(
            'title' => $title,
            'description' => 'Talk with UTF-8 characters in the title',
            'user_id' => 1
        );
        $talk = $this->mapper->create($data);

        $this->assertEquals(
            $title,
            $talk->title,
            'UTF-8 characters were incorrectly encoded'
        );
    }
}
