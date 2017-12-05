<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use OpenCFP\Domain\EntityNotFoundException;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Speaker\SpeakerRepository;

class IlluminateSpeakerRepository implements SpeakerRepository
{
    /**
     * @var User
     */
    protected $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    public function findById(int $speakerId): User
    {
        try {
            $speaker = $this->userModel->findOrFail($speakerId);
        } catch (ModelNotFoundException $e) {
            throw new EntityNotFoundException();
        }

        return $speaker;
    }

    public function persist(User $speaker)
    {
        $speaker->save();
    }
}
