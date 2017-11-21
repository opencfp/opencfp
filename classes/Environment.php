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

    private function __construct($slug)
    {
        if (! in_array($slug, ['production', 'development', 'testing'])) {
            throw new \InvalidArgumentException('Invalid environment specified.');
        }

        $this->slug = (string) $slug;
    }

    /**
     * @return Environment
     */
    public static function production()
    {
        return new self('production');
    }

    /**
     * @return Environment
     */
    public static function development()
    {
        return new self('development');
    }

    /**
     * @return Environment
     */
    public static function testing()
    {
        return new self('testing');
    }

    /**
     * @return Environment
     */
    public static function fromEnvironmentVariable()
    {
        $environment = $_SERVER['CFP_ENV'] ?? 'development';

        return new self($environment);
    }

    /**
     * @param $environmentString
     *
     * @return Environment
     */
    public static function fromString($environmentString)
    {
        return new self($environmentString);
    }

    /**
     * @param Environment $environment
     *
     * @return bool
     */
    public function equals(Environment $environment)
    {
        return $this->slug === $environment->slug;
    }

    /**
     * Tells if application is in production environment.
     *
     * @return bool
     */
    public function isProduction(): bool
    {
        return $this->equals(Environment::production());
    }

    /**
     * Tells if application is in development environment.
     *
     * @return bool
     */
    public function isDevelopment(): bool
    {
        return $this->equals(Environment::development());
    }

    /**
     * Tells if application is in testing environment.
     *
     * @return bool
     */
    public function isTesting(): bool
    {
        return $this->equals(Environment::testing());
    }

    public function __toString()
    {
        return $this->slug;
    }
}
