<?php

namespace OpenCFP;

class Environment
{
    /**
     * The specified environment.
     *
     * @var string
     */
    protected $slug;

    private function __construct(string $slug)
    {
        if (! in_array($slug, ['production', 'development', 'testing'])) {
            throw new \InvalidArgumentException('Invalid environment specified.');
        }

        $this->slug = $slug;
    }

    public static function production(): self
    {
        return new self('production');
    }

    public static function development(): self
    {
        return new self('development');
    }

    public static function testing(): self
    {
        return new self('testing');
    }

    public static function fromEnvironmentVariable(): self
    {
        $environment = $_SERVER['CFP_ENV'] ?? 'development';

        return new self($environment);
    }

    public static function fromString(string $environmentString): self
    {
        return new self($environmentString);
    }

    public function equals(Environment $environment): bool
    {
        return $this->slug === (string) $environment;
    }

    public function __toString(): string
    {
        return $this->slug;
    }
}
