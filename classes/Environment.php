<?php namespace OpenCFP;

use InvalidArgumentException;

class Environment
{
    /**
     * The specified environment.
     * @var string
     */
    protected $slug;

    private function __construct($slug)
    {
        if (! in_array($slug, ['production', 'development', 'testing'])) {
            throw new InvalidArgumentException('Invalid environment specified.');
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
        $environment = isset($_SERVER['CFP_ENV']) ? $_SERVER['CFP_ENV'] : 'development';

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
     * @param  Environment $environment
     * @return bool
     */
    public function equals(Environment $environment)
    {
        return $this->slug === (string) $environment;
    }

    public function __toString()
    {
        return $this->slug;
    }
}
