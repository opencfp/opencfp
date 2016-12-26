<?php

namespace OpenCFP\Test\Domain\Entity;

use OpenCFP\Application;
use OpenCFP\Environment;
use Spot\Locator;

/**
 * @group db
 */
class TalkTest extends \PHPUnit_Framework_TestCase
{
    private $app;
    private $mapper;
    private $entities = ['Talk', 'TalkMeta', 'User', 'Favorite'];

    protected function setUp()
    {
        $this->app = new Application(BASE_PATH, Environment::testing());
        $cfg = new \Spot\Config;
        $cfg->addConnection('sqlite', [
            'dbname' => 'sqlite::memory',
            'driver' => 'pdo_sqlite',
        ]);
        $spot = new \Spot\Locator($cfg);

        $this->app['spot'] = $spot;

        $this->mapper = $spot->mapper(\OpenCFP\Domain\Entity\Talk::class);

        foreach ($this->entities as $entity) {
            $spot->mapper('OpenCFP\Domain\Entity\\' . $entity)->migrate();
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
            'user_id' => 1,
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
        /* @var Locator $spot */
        $spot = $this->app['spot'];
        
        // Create a favorites table, can be empty
        $favorite_mapper = $spot->mapper(\OpenCFP\Domain\Entity\Favorite::class);
        $favorite_mapper->migrate();

        // Create users entity
        $user_mapper = $spot->mapper(\OpenCFP\Domain\Entity\User::class);
        $user_mapper->migrate();

        // Create 11 talks
        $this->bulkCreateTalks(11);
        $recent_talks = $this->mapper->getRecent(1);

        $this->assertCount(10, $recent_talks, "Talk::getRecent() did not grab 10 talks out of 11");
    }

    //
    // Factory Methods
    //

    private function bulkCreateTalks($numTalks)
    {
        for ($x = 1; $x <= $numTalks; $x++) {
            $title = uniqid();
            $data = [
                'title' => $title,
                'description' => "Description for $title",
                'user_id' => 1,
            ];
            $this->mapper->create($data);
        }
    }
}
