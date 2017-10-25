<?php

namespace OpenCFP\Test\Http\Controller\Admin;

use Mockery as m;
use OpenCFP\Domain\Entity\Mapper;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Test\TestCase;
use Spot\Query;

/**
 * Class TalksControllerTest
 * @package OpenCFP\Test\Http\Controller\Admin
 * @group db
 */
class TalksControllerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->asAdmin();
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
        $spot = m::mock('Spot\Locator');
        $spot->shouldReceive('mapper')
            ->with(\OpenCFP\Domain\Entity\User::class)
            ->andReturn($userMapper);
        $spot->shouldReceive('mapper')
            ->with(\OpenCFP\Domain\Entity\Talk::class)
            ->andReturn($talkMapper);
        $this->app['spot'] = $spot;

        $req = m::mock('Symfony\Component\HttpFoundation\Request');
        $paramBag = m::mock('Symfony\Component\HttpFoundation\ParameterBag');

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
        // Create some reusable values
        $talkId = uniqid();
        $comment = 'Test Comment';

        // Create a TalkComment and mapper, then add the mapper to $app
        $talkComment = m::mock(\OpenCFP\Domain\Entity\TalkComment::class);
        $talkComment->shouldReceive('set')
            ->andSet('talk_id', $talkId);
        $talkComment->shouldReceive('set')
            ->andSet('comment', $comment);
        $talkComment->shouldReceive('set')
            ->andSet('user_id', uniqid());

        $talkCommentMapper = m::mock(\OpenCFP\Domain\Entity\Mapper\TalkComment::class);
        $talkCommentMapper->shouldReceive('get')->andReturn($talkComment);
        $talkCommentMapper->shouldReceive('save');

        // Override our mapper with the double
        $spot = m::mock('Spot\Locator');
        $spot->shouldReceive('mapper')
            ->with(\OpenCFP\Domain\Entity\TalkComment::class)
            ->andReturn($talkCommentMapper);
        $this->app['spot'] = $spot;

        // Use our pre-configured Application object
        ob_start();
        $this->app->run();
        ob_end_clean();

        // Create our Request object
        $req = m::mock('Symfony\Component\HttpFoundation\Request');
        $req->shouldReceive('get')->with('id')->andReturn($talkId);
        $req->shouldReceive('get')->with('comment')->andReturn($comment);

        // Execute the controller and capture the output
        $controller = new \OpenCFP\Http\Controller\Admin\TalksController();
        $controller->setApplication($this->app);
        $response = $controller->commentCreateAction($req);

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\RedirectResponse',
            $response
        );
    }

    /**
     * Verify that not found talk redirects and sets flash error message
     *
     * @test
     */
    public function talkNotFoundHasFlashMessage()
    {
        $talkId = uniqid();

        $query = m::mock(Query::class);
        $query->shouldReceive('with')->with(['comments'])->andReturnSelf();
        $query->shouldReceive('first')->andReturnNull();

        $talkMapper = m::mock(Mapper\Talk::class);
        $talkMapper->shouldReceive('where')->with(['id' => $talkId])->andReturn($query);

        $talkMetaMapper = m::mock(\Spot\Mapper::class);

        $spot = m::mock('Spot\Locator');
        $spot->shouldReceive('mapper')
            ->with(\OpenCFP\Domain\Entity\Talk::class)
            ->andReturn($talkMapper);
        $spot->shouldReceive('mapper')->with(\OpenCFP\Domain\Entity\TalkMeta::class)->andReturn($talkMetaMapper);

        $this->app['spot'] = $spot;

        // Use our pre-configured Application object
        ob_start();
        $this->app->run();
        ob_end_clean();

        // Create our Request object
        $req = m::mock('Symfony\Component\HttpFoundation\Request');
        $req->shouldReceive('get')->with('id')->andReturn($talkId);

        // Execute the controller and capture the output
        $controller = new \OpenCFP\Http\Controller\Admin\TalksController();
        $controller->setApplication($this->app);
        $response = $controller->viewAction($req);

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\RedirectResponse',
            $response
        );

        $this->assertContains(
            'Could not find requested talk',
            $this->app['session']->get('flash')
        );
    }
}
