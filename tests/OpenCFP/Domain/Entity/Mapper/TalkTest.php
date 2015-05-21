<?php
use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Environment;

class TalkMapperTest extends \PHPUnit_Framework_TestCase
{
    public $app;

    public function setup()
    {
        $this->app = new Application(BASE_PATH, Environment::testing());
        $cfp = new \Spot\Config;
        $cfg = new \Spot\Config;
        $cfg->addConnection('sqlite', [
            'dbname' => 'sqlite::memory',
            'driver' => 'pdo_sqlite'
        ]);
        $this->app['spot'] = new \Spot\Locator($cfg);
    }

    /**
     * @test
     */
    public function getAdminFavoritesReturnsCorrectList()
    {
        // Create a test talk
        $admin_user_id = 1;
        $admin_majority = 3;
        $mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $mapper->migrate();
        $talk_data = [
            'title' => 'Admin Favorite Talk',
            'description' => "This talk has {$admin_majority} favorites!",
            'user_id' => 1
        ];
        $talk = $mapper->create($talk_data);

        $this->createAdminFavoredTalks($mapper, $admin_majority, $talk);
        $expected_admin_favorite = $mapper->createdFormattedOutput($talk, $admin_user_id);
        $admin_favorite_collection = $mapper->getAdminFavorites($admin_user_id, $admin_majority);
        $admin_favorite = $admin_favorite_collection[0];

        $this->assertEquals(
            $expected_admin_favorite,
            $admin_favorite,
            "Did not get expected list of admin-favorited talks"
        );
    }

    private function createAdminFavoredTalks($mapper, $admin_majority, $talk)
    {
        // Create a test user
        $user_mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\User');
        $user_mapper->migrate();

        // Create $admin_majority favorite records linked to that talk
        $favorite_mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Favorite');
        $favorite_mapper->migrate();

        for ($x = 1; $x <= $admin_majority; $x++) {
            $admin_id = rand();
            $favorite_data = [
                'admin_user_id' => $admin_id,
                'talk_id' => $talk->id
            ];
            $favorite = $favorite_mapper->create($favorite_data);
        }
    }
}
