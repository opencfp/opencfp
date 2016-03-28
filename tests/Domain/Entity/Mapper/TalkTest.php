<?php

namespace OpenCFP\Test\Domain\Entity\Mapper;

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
    public function getAdminFavoritesReturnsCorrectList()
    {
        // Create a test talk
        $admin_user_id = 1;
        $admin_majority = 3;

        /* @var Locator $spot */
        $spot = $this->app['spot'];

        $mapper = $spot->mapper(\OpenCFP\Domain\Entity\Talk::class);

        $talk_data = [
            'title' => 'Admin Favorite Talk',
            'description' => "This talk has {$admin_majority} favorites!",
            'user_id' => $admin_user_id,
            'type' => 'regular',
            'category' => 'api',
            'level' => 'entry',
        ];
        $talk = $mapper->create($talk_data);

        $this->createAdminFavoredTalks($admin_user_id, $admin_majority, $talk);
        $mapper->createdFormattedOutput($talk, $admin_user_id);
        $admin_favorite_collection = $mapper->getFavoritesByUserId($admin_user_id);
        $admin_favorite = $admin_favorite_collection[0];

        $this->assertEquals(
            $talk->id,
            $admin_favorite['id'],
            "Did not get expected list of admin-favorited talks"
        );
    }

    //
    // Factory Methods
    //

    private function createAdminFavoredTalks($admin_user_id, $admin_majority, $talk)
    {
        /* @var Locator $spot */
        $spot = $this->app['spot'];
        
        // Create a test user
        $user_mapper = $spot->mapper(\OpenCFP\Domain\Entity\User::class);
        $user_mapper->create([
            'id' => $admin_user_id,
            'email' => 'test@test.com',
            'password' => 'supersecret',
            'first_name' => 'Testy',
            'last_name' => 'McTesterson',
        ]);

        // Create $admin_majority favorite records linked to that talk
        $favorite_mapper = $spot->mapper(\OpenCFP\Domain\Entity\Favorite::class);
        $favorite_mapper->create(['admin_user_id' => $admin_user_id, 'talk_id' => $talk->id]);

        for ($x = 1; $x <= $admin_majority; $x++) {
            $random_admin_id = rand();
            $favorite_data = [
                'admin_user_id' => $random_admin_id,
                'talk_id' => $talk->id,
            ];
            $favorite_mapper->create($favorite_data);
        }
    }
}
