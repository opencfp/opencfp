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

namespace OpenCFP;

class Environment
{
    public const TYPE_PRODUCTION  = 'production';
    public const TYPE_DEVELOPMENT = 'development';
    public const TYPE_TESTING     = 'testing';

    /**
     * The specified environment.
     *
     * @var string
     */
    protected $type;

    /**
     * @param string $type
     *
     * @throws \InvalidArgumentException
     */
    private function __construct(string $type)
    {
        $types = [
            self::TYPE_PRODUCTION,
            self::TYPE_DEVELOPMENT,
            self::TYPE_TESTING,
        ];

        if (!\in_array($type, $types)) {
            throw new \InvalidArgumentException(\sprintf(
                'Environment needs to be one of "%s"; got "%s" instead.',
                \implode('", "', $types),
                $type
            ));
        }

        $this->type = $type;
    }

    public static function production(): self
    {
        return new self(self::TYPE_PRODUCTION);
    }

    public static function development(): self
    {
        return new self(self::TYPE_DEVELOPMENT);
    }

    public static function testing(): self
    {
        return new self(self::TYPE_TESTING);
    }

    /**
     * @deprecated
     *
     * @return self
     */
    public static function fromEnvironmentVariable(): self
    {
        return self::fromServer($_SERVER);
    }

    /**
     * @param array $server
     *
     * @throws \InvalidArgumentException
     *
     * @return self
     */
    public static function fromServer(array $server): self
    {
        $type = $server['CFP_ENV'] ?? self::TYPE_PRODUCTION;

        return new self($type);
    }

    /**
     * @param string $type
     *
     * @throws \InvalidArgumentException
     *
     * @return self
     */
    public static function fromString(string $type): self
    {
        return new self($type);
    }

    public function equals(self $environment): bool
    {
        return $this->type === $environment->type;
    }

    /**
     * Tells if application is in production environment.
     *
     * @return bool
     */
    public function isProduction(): bool
    {
        return $this->type === self::TYPE_PRODUCTION;
    }

    /**
     * Tells if application is in development environment.
     *
     * @return bool
     */
    public function isDevelopment(): bool
    {
        return $this->type === self::TYPE_DEVELOPMENT;
    }

    /**
     * Tells if application is in testing environment.
     *
     * @return bool
     */
    public function isTesting(): bool
    {
        return $this->type === self::TYPE_TESTING;
    }

    public function __toString(): string
    {
        return $this->type;
    }
}
