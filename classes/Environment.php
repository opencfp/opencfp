<?php

namespace OpenCFP;

class Environment
{
    const TYPE_PRODUCTION  = 'production';
    const TYPE_DEVELOPMENT = 'development';
    const TYPE_TESTING     = 'testing';

    /**
     * The specified environment.
     *
     * @var string
     */
    protected $type;

    private function __construct(string $type)
    {
        if (! in_array($type, [self::TYPE_PRODUCTION, self::TYPE_DEVELOPMENT, self::TYPE_TESTING])) {
            throw new \InvalidArgumentException('Invalid environment specified.');
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

    public static function fromEnvironmentVariable(): self
    {
        $type = $_SERVER['CFP_ENV'] ?? self::TYPE_DEVELOPMENT;

        return new self($type);
    }

    public static function fromString(string $type): self
    {
        return new self($type);
    }

    public function equals(Environment $environment): bool
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
