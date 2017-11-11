<?php

namespace OpenCFP\Test\Http\Controller\Admin;

use Mockery as m;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Test\DatabaseTransaction;
use OpenCFP\Test\WebTestCase;

class TalksControllerTest extends WebTestCase
{
    use DatabaseTransaction;

    public function setUp()
    {
        parent::setUp();
        $this->asAdmin();
        $this->setUpDatabase();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->tearDownDatabase();
    }

    /**
     * Test that the index page grabs a collection of talks
     * and successfully displays them
     *
     * @test
     */
    public function indexPageDisplaysTalksCorrectly()
    {
        /** @var Authentication $auth */
        $auth = $this->app[Authentication::class];
        $userId = $auth->user()->getId();
        // Create our fake talk
        $talk = m::mock(\OpenCFP\Domain\Entity\Talk::class);
        $talk->shouldReceive('save');
        $talk->shouldReceive('set')
            ->with($auth->user())
            ->andSet('speaker', $auth->user());
        $userDetails = [
            'id' => $userId,
            'first_name' => 'Test',
            'last_name' => 'User',
        ];

        $talkData = [0 => [
            'id' => 1,
            'title' => 'Test Title',
            'description' => 'The title should contain this & that',
            'meta' => [
                'rating' => 5,
            ],
            'type' => 'regular',
            'level' => 'entry',
            'category' => 'other',
            'desired' => 0,
            'slides' => '',
            'other' => '',
            'sponsor' => '',
            'user_id' => $userId,
            'created_at' => date('Y-m-d'),
            'user' => $userDetails,
            'favorite' => null,
            'selected' => null,
        ]];
        $userMapper = m::mock(\OpenCFP\Domain\Entity\Mapper\User::class);
        $userMapper->shouldReceive('migrate');
        $userMapper->shouldReceive('build')->andReturn($auth->user());
        $userMapper->shouldReceive('save')->andReturn(true);

        $talkMapper = m::mock(\OpenCFP\Domain\Entity\Mapper\Talk::class);
        $talkMapper->shouldReceive('migrate');
        $talkMapper->shouldReceive('build')->andReturn($talk);
        $talkMapper->shouldReceive('save');
        $talkMapper->shouldReceive('getAllPagerFormatted')->andReturn($talkData);

        // Overide our DB mappers to return doubles
        $spot = m::mock(\Spot\Locator::class);
        $spot->shouldReceive('mapper')
            ->with(\OpenCFP\Domain\Entity\User::class)
            ->andReturn($userMapper);
        $spot->shouldReceive('mapper')
            ->with(\OpenCFP\Domain\Entity\Talk::class)
            ->andReturn($talkMapper);
        $this->app['spot'] = $spot;

        $req = m::mock(\Symfony\Component\HttpFoundation\Request::class);
        $paramBag = m::mock(\Symfony\Component\HttpFoundation\ParameterBag::class);

        $queryParams = [
            'page' => 1,
            'per_page' => 20,
            'sort' => 'ASC',
            'order_by' => 'title',
            'filter' => null,
        ];
        $paramBag->shouldReceive('all')->andReturn($queryParams);

        $req->shouldReceive('get')->with('page')->andReturn($queryParams['page']);
        $req->shouldReceive('get')->with('per_page')->andReturn($queryParams['per_page']);
        $req->shouldReceive('get')->with('sort')->andReturn($queryParams['sort']);
        $req->shouldReceive('get')->with('order_by')->andReturn($queryParams['order_by']);
        $req->shouldReceive('get')->with('filter')->andReturn($queryParams['filter']);
        $req->query = $paramBag;
        $req->shouldReceive('getRequestUri')->andReturn('foo');

        $this->get('/admin/talks')
            ->assertSuccessful()
            ->assertSee('Test Title')
            ->assertSee('Test User');
    }

    /**
     * A test to make sure that comments can be correctly tracked
     *
     * @test
     */
    public function talkIsCorrectlyCommentedOn()
    {
        $talk = factory(Talk::class,1)->create()->first();

        $this->asAdmin()
            ->post(
                '/admin/talks/'. $talk->id.'/comment',
                ['comment' => 'Great Talk i rate 10/10']
            )
            ->assertNotSee('Server Error')
            ->assertRedirect();
    }

    /**
     * Verify that not found talk redirects
     *
     * @test
     */
    public function talkNotFoundRedirectsBackToTalksOverview()
    {
        $this->get('/admin/talks/255')
            ->assertRedirect()
            ->assertNotSee('<strong>Submitted by:</strong>');
    }

    /**
     * @test
     */
    public function talkWithNoMetaDisplaysCorrectly()
    {
        $talk = factory(Talk::class, 1)->create();

        $this->get('/admin/talks/'. $talk->first()->id)
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function previouslyViewedTalksDisplaysCorrectly()
    {
        $meta = factory(TalkMeta::class, 1)->create();
        $this->asAdmin($meta->first()->admin_user_id);

        $this->get('/admin/talks/'. $meta->first()->talk_id)
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function selectActionWorksCorrectly()
    {
        $talk = factory(Talk::class, 1)->create()->first();

        $this->asAdmin()
            ->post('/admin/talks/'. $talk->id. '/select')
            ->assertSee('1')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function selectActionDeletesCorrectly()
    {
        $talk = factory(Talk::class, 1)->create()->first();

        $this->asAdmin()
            ->post('/admin/talks/'. $talk->id. '/select', ['delete' => 1])
            ->assertSee('1')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function selectActionReturnsFalseWhenTalkNotFound()
    {
        $this->asAdmin()
            ->post('/admin/talks/255/select')
            ->assertNotSee('1')
            ->assertSuccessful();
    }


}
