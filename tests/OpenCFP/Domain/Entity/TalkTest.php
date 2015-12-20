<?php
use OpenCFP\Application;
use OpenCFP\Environment;

class TalkEntityTest extends \PHPUnit_Framework_TestCase
{
    public $app;
    public $mapper;
    private $entities = ['Talk', 'TalkMeta', 'User', 'Favorite'];

    protected function setup()
    {
        $this->app = new Application(BASE_PATH, Environment::testing());
        $cfg = new \Spot\Config;
        $cfg->addConnection('sqlite', [
            'dbname' => 'sqlite::memory',
            'driver' => 'pdo_sqlite'
        ]);
        $this->app['spot'] = new \Spot\Locator($cfg);

        $this->mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Talk');

        foreach ($this->entities as $entity) {
            $this->app['spot']->mapper('OpenCFP\Domain\Entity\\' . $entity)->migrate();
        }
    }

    /**
     * @test
     */
    public function utf8CharactersCorrectlyEncoded()
    {
        $title = "Battle: Feature Branches VS Feature Switching (╯°□°)╯︵ ┻━┻ ︵ ╯(°□° ╯)";
        $data = [
            'title' => $title,
            'description' => 'Talk with UTF-8 characters in the title',
            'user_id' => 1
        ];
        $talk = $this->mapper->create($data);

        $this->assertEquals(
            $title,
            $talk->title,
            'UTF-8 characters were incorrectly encoded'
        );
    }

    /**
     * @test
     */
    public function getRecentFindsMostRecentTalks()
    {
        // Create a favorites table, can be empty
        $favorite_mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Favorite');
        $favorite_mapper->migrate();

        // Create users entity
        $user_mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\User');
        $user_mapper->migrate();

        // Create 11 talks
        $this->bulkCreateTalks(11);
        $recent_talks = $this->mapper->getRecent(1);

        $this->assertTrue(
            count($recent_talks) == 10,
            "Talk::getRecent() did not grab 10 talks out of 11"
        );
    }

    private function bulkCreateTalks($numTalks)
    {
        for ($x = 1; $x <= $numTalks; $x++) {
            $title = uniqid();
            $data = [
                'title' => $title,
                'description' => "Description for $title",
                'user_id' => 1
            ];
            $this->mapper->create($data);
        }
    }
}
