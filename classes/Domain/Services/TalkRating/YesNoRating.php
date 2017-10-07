<?php

namespace OpenCFP\Domain\Services\TalkRating;

use OpenCFP\Domain\Entity\TalkMeta;
use OpenCFP\Domain\Services\Authentication;
use Spot\MapperInterface;

class YesNoRating implements TalkRatingStrategy
{
    private $mapper;
    private $auth;

    public function __construct(MapperInterface $mapper, Authentication $auth)
    {
        $this->mapper = $mapper;
        $this->auth = $auth;
    }

    public function isValidRating($rating): bool
    {
        if ($rating < -1 || $rating > 1) {
            return false;
        }

        return true;
    }

    public function rate(int $talkId, $rating)
    {
        if (!$this->isValidRating($rating)) {
            throw TalkRatingException::invalidRating($rating);
        }

        $meta = $this->fetchMetaInfo($talkId);
        $meta->rating = $rating;
        $this->mapper->save($meta);
    }

    private function fetchMetaInfo(int $talkId): TalkMeta
    {
        $adminUserId = (int) $this->auth->user()->getId();
        $talkMeta = $this->mapper->where([
            'admin_user_id' => $adminUserId,
            'talk_id' => $talkId,
        ])
            ->first();

        if (!$talkMeta) {
            $talkMeta = $this->mapper->get();
            $talkMeta->admin_user_id = $adminUserId;
            $talkMeta->talk_id = $talkId;
        }

        return $talkMeta;
    }
}
