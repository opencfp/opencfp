<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Domain\Talk;

use OpenCFP\Domain\ValidationException;

class InvalidTalkSubmissionException extends ValidationException
{
    public static function noCategory(): self
    {
        return new self('You must choose what category of talk you are submitting.');
    }

    public static function noValidCategory(): self
    {
        return new self('You did not choose a valid talk category.');
    }

    public static function noDescription(): self
    {
        return new self('The description of the talk must be included.');
    }

    public static function noLevel(): self
    {
        return new self('You must choose when level of talk you are submitting.');
    }

    public static function noValidLevel(): self
    {
        return new self('You did not choose a valid talk level.');
    }

    public static function noTalkType(): self
    {
        return new self('You must choose what type of talk you are submitting.');
    }

    public static function noValidTalkType(): self
    {
        return new self('You did not choose a valid talk type.');
    }

    public static function noTitle(): self
    {
        return new self('The title of the talk must be provided.');
    }

    public static function noValidTitle(int $maxLength): self
    {
        return new self(\sprintf(
            'The title of the talk must be %d characters or less.',
            $maxLength
        ));
    }
}
