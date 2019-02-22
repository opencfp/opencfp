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

namespace OpenCFP\Http\Action\Talk;

use OpenCFP\Domain\CallForPapers;
use OpenCFP\Domain\Model;
use OpenCFP\Domain\Services;
use Symfony\Component\HttpFoundation;

final class DeleteAction
{
    /**
     * @var Services\Authentication
     */
    private $authentication;

    /**
     * @var CallForPapers
     */
    private $callForPapers;

    public function __construct(Services\Authentication $authentication, CallForPapers $callForPapers)
    {
        $this->authentication = $authentication;
        $this->callForPapers  = $callForPapers;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        if (!$this->callForPapers->isOpen()) {
            return new HttpFoundation\JsonResponse([
                'delete' => 'no',
            ]);
        }

        $talkId = $request->get('tid');

        $userId = $this->authentication->user()->getId();

        /** @var Model\Talk $talk */
        $talk = Model\Talk::find($talkId, ['id', 'user_id']);

        if ($talk->user_id != $userId) {
            return new HttpFoundation\JsonResponse([
                'delete' => 'no',
            ]);
        }

        $talk->delete();

        return new HttpFoundation\JsonResponse([
            'delete' => 'ok',
        ]);
    }
}
