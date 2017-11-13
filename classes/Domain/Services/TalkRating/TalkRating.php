<?php

namespace OpenCFP\Domain\Services\TalkRating;

use Illuminate\Database\Eloquent\Model;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Domain\Services\Authentication;

abstract class TalkRating implements TalkRatingStrategy
{
    protected $adminId;
    protected $meta;

    public function __construct(TalkMeta $meta, Authentication $auth)
    {
        $this->adminId = $auth->userId();
        $this->meta    = $meta;
    }

    /**
     * @param int $talkId
     * @param $rating
     */
    protected function saveRating(int $talkId, $rating)
    {
        $meta         = $this->fetchMetaInfo($talkId);
        $meta->rating = $rating;
        $meta->save();
    }

    private function fetchMetaInfo(int $talkId): Model
    {
        return $this->meta->firstOrCreate([
            'admin_user_id' => $this->adminId,
            'talk_id'       => $talkId,
        ]);
    }
}
