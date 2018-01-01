<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2018 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Action\Reviewer\Talk;

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation;
use Illuminate\Validation;
use OpenCFP\Domain\Talk;
use OpenCFP\Domain\ValidationException;
use Symfony\Component\HttpFoundation;

final class RateAction
{
    /**
     * @var Talk\TalkHandler
     */
    private $talkHandler;

    public function __construct(Talk\TalkHandler $talkHandler)
    {
        $this->talkHandler = $talkHandler;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        try {
            $this->validate($request, [
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

    /**
     * @param HttpFoundation\Request $request
     * @param array                  $rules
     * @param array                  $messages
     * @param array                  $customAttributes
     *
     * @throws ValidationException
     */
    private function validate(HttpFoundation\Request $request, $rules = [], $messages = [], $customAttributes = [])
    {
        $data = $request->query->all() + $request->request->all() + $request->files->all();

        $validation = new Validation\Factory(
            new Translation\Translator(
                new Translation\FileLoader(
                    new Filesystem(),
                    __DIR__ . '/../../../resources/lang'
                ),
                'en'
            ),
            new Container()
        );

        $validator = $validation->make(
            $data,
            $rules,
            $messages,
            $customAttributes
        );

        if ($validator->fails()) {
            throw ValidationException::withErrors(array_flatten($validator->errors()->toArray()));
        }

        unset($validation, $validator);
    }
}
