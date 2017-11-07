<?php

namespace OpenCFP\Http\Controller\Reviewer;

use OpenCFP\Domain\Services\Authentication;

trait ReviewerAccessTrait
{
    protected function userHasAccess()
    {
        /** @var Authentication $auth */
        $auth = $this->app[Authentication::class];
        if (!$auth->check()) {
            return false;
        }

        $user = $auth->user();
        if (!$user->hasPermission('reviewer')) {
            return false;
        }
        return true;
    }
}
