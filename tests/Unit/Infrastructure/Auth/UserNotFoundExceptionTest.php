<?php

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use OpenCFP\Infrastructure\Auth\UserNotFoundException;
use OpenCFP\Test\Helper\Faker\GeneratorTrait;

/**
 * @covers \OpenCFP\Infrastructure\Auth\UserNotFoundException
 */
class UserNotFoundExceptionTest extends \PHPUnit\Framework\TestCase
{
    use GeneratorTrait;

    public function testIsFinal()
    {
        $reflection = new \ReflectionClass(UserNotFoundException::class);
        $this->assertTrue($reflection->isFinal());
    }

    /**
     * @test
     */
    public function isInstanceOfRuntimeException()
    {
        $exception = new UserNotFoundException();
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testFromEmailReturnsException()
    {
        $email = $this->getFaker()->email;

        $exception = UserNotFoundException::fromEmail($email);

        $this->assertInstanceOf(UserNotFoundException::class, $exception);

        $message = \sprintf(
            'Unable to find a user with email "%s".',
            $email
        );

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testFromIdReturnsException()
    {
        $id = $this->getFaker()->numberBetween(1);

        $exception = UserNotFoundException::fromId($id);

        $this->assertInstanceOf(UserNotFoundException::class, $exception);

        $message = \sprintf(
            'Unable to find a user with id "%d".',
            $id
        );

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }
}
