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

namespace OpenCFP\Http\Action\Admin\Talk;

use OpenCFP\Domain\Talk;
use OpenCFP\Domain\ValidationException;
use OpenCFP\Infrastructure\Validation\RequestValidator;
use Symfony\Component\HttpFoundation;

final class RateAction
{
    /**
     * @var Talk\TalkHandler
     */
    private $talkHandler;

    /**
     * @var RequestValidator
     */
    private $requestValidator;

    public function __construct(Talk\TalkHandler $talkHandler, RequestValidator $requestValidator)
    {
        $this->talkHandler      = $talkHandler;
        $this->requestValidator = $requestValidator;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        try {
            $this->requestValidator->validate($request, [
                'rating' => 'required|integer',
            ]);
        } catch (ValidationException $exception) {
            return new HttpFoundation\Response();
        }

        $content = (string) $this->talkHandler
            ->grabTalk((int) $request->get('id'))
            ->rate((int) $request->get('rating'));

        return new HttpFoundation\Response($content);
    }
}
