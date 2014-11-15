<?php namespace OpenCFP; 

use InvalidArgumentException;

final class Environment
{
    /**
     * The specified environment.
     * @var string
     */
    protected $slug;

    private function __construct($slug) {
        if ( ! in_array($slug, ['production', 'development', 'testing'])) {
            throw new InvalidArgumentException('Invalid environment specified.');
        }

        $this->slug = (string) $slug;
    }

    public static function production()
    {
        return new self('production');
    }

    public static function development()
    {
        return new self('development');
    }

    public static function testing()
    {
        return new self('testing');
    }

    public static function fromEnvironmentVariable()
    {
        $environment = isset($_SERVER['CFP_ENV']) ? $_SERVER['CFP_ENV'] : 'development';
        return new self($environment);
    }

    public function equals(Environment $environment)
    {
        return $this->slug === (string)$environment;
    }

    public function __toString()
    {
        return $this->slug;
    }
} 