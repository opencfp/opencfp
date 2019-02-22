<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Infrastructure\Validation;

use Illuminate\Support\MessageBag;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Validator;
use OpenCFP\Domain\ValidationException;
use OpenCFP\Infrastructure\Validation\RequestValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class RequestValidatorTest extends TestCase
{
    /**
     * @test
     */
    public function requestIsValid()
    {
        $request = new Request();
        $request->request->set('foo', 'bar');

        $validator = $this->prophesize(Validator::class);
        $validator->fails()->willReturn(false);

        $factory = $this->prophesize(Factory::class);
        $factory->make(['foo' => 'bar'], ['foo' => 'required'])
            ->willReturn($validator->reveal());

        $requestValidator = new RequestValidator($factory->reveal());
        $requestValidator->validate($request, ['foo' => 'required']);

        $this->addToAssertionCount(1);
    }

    /**
     * @test
     */
    public function requestIsInvalid()
    {
        $request = new Request();
        $request->request->set('foo', 'bar');

        $validator = $this->prophesize(Validator::class);
        $validator->fails()->willReturn(true);
        $validator->errors()->willReturn(new MessageBag());

        $factory = $this->prophesize(Factory::class);
        $factory->make(['foo' => 'bar'], ['foo' => 'required'])
            ->willReturn($validator->reveal());

        $requestValidator = new RequestValidator($factory->reveal());

        $this->expectException(ValidationException::class);
        $requestValidator->validate($request, ['foo' => 'required']);
    }
}
