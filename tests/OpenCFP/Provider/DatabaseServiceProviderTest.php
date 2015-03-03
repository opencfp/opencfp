<?php

namespace OpenCFP\Provider;

use Mockery as m;

/**
 * @covers OpenCFP\Application
 */
class DatabaseServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DatabaseServiceProvider
     */
    private $sut;

    public function setup()
    {
        $this->sut = new DatabaseServiceProvider();
    }

    /**
     * @test
     * @dataProvider invalidDatabaseConnections
     */
    public function it_should_throw_exception_if_dsn_is_invalid($dsn, $user, $password)
    {
        $app = $this->trainApplicationWithDatabaseConfig($dsn, $user, $password);

        $this->setExpectedException('\Exception', 'There was a problem connecting to the database.');
        $this->sut->register($app);
    }

    public function invalidDatabaseConnections()
    {
        return [
            ['mysql://root@localhost/cfp', 'root', null],
            ['mysql://root@localhost/cfp', null, null],
        ];
    }

    /** @test */
    public function it_should_work_just_fine_when_given_valid_dsn()
    {
        $app = $this->trainApplicationWithDatabaseConfig('mysql:dbname=cfp_travis;host=127.0.0.1', 'root', null);

        // Set expectation that database should be registered in the container.
        // $app['db'] = new PDO(...)
        $app->shouldReceive('offsetSet')->with('db', m::type('PDO'));

        $this->sut->register($app);
    }

    /**
     * @param $dsn
     * @param $user
     * @param $password
     *
     * @return m\MockInterface|\Yay_MockObject
     */
    private function trainApplicationWithDatabaseConfig($dsn, $user, $password)
    {
        $app = m::mock('OpenCFP\Application');
        $app->shouldReceive('config')->with('database.dsn')->andReturn($dsn);
        $app->shouldReceive('config')->with('database.user')->andReturn($user);
        $app->shouldReceive('config')->with('database.password')->andReturn($password);

        return $app;
    }
} 