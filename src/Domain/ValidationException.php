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

namespace OpenCFP\Domain;

class ValidationException extends \Exception
{
    /**
     * @var array
     */
    private $errors;

    public static function withErrors(array $errors = []): self
    {
        $instance         = new static('There was an error.');
        $instance->errors = $errors;

        return $instance;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
