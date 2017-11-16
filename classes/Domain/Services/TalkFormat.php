<?php

namespace OpenCFP\Domain\Services;

use Illuminate\Support\Collection;

interface TalkFormat
{
    public function formatList(Collection $talkCollection, int $adminUserId, bool $userData = true): Collection;

    public function createdFormattedOutput($talk, int $adminUserId, bool $userData = true);
}
