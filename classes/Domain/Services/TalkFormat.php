<?php

namespace OpenCFP\Domain\Services;

use Illuminate\Support\Collection;

interface TalkFormat
{
    public function formatList(Collection $talkCollection, int $admin_user_id, bool $userData = true): Collection;

    public function createdFormattedOutput($talk, int $admin_user_id, bool $userData = true);
}
