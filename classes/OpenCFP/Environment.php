<?php namespace OpenCFP; 

final class Environment
{
    /**
     * The specified environment.
     * @var string
     */
    protected $slug;

    private function __construct($slug) {
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

    public function equals(Environment $environment)
    {
        return $this->slug === (string)$environment;
    }

    public function __toString()
    {
        return $this->slug;
    }
} 