<?php

namespace OpenCFP\Config;

class ParameterResolver implements ParameterResolverInterface
{
    /**
     * The dependency injection container.
     *
     * @var \Pimple
     */
    private $container;

    /**
     * Constructor.
     *
     * @param \Pimple $container
     */
    public function __construct(\Pimple $container)
    {
        $this->container = $container;
    }

    /**
     * {@inherited}
     *
     */
    public function resolve($parameter)
    {
        // Skip non string or array parameters
        if (!is_string($parameter) && !is_array($parameter)) {
            return $parameter;
        }

        // Recursive change for all values of the array
        if (is_array($parameter)) {
            foreach ($parameter as $key => $value) {
                $parameter[$key] = $this->resolve($value);
            }

            return $parameter;
        }

        return $this->replacePlaceholders($parameter);
    }

    /**
     * Replaces placeholders in the parameter by their
     * corresponding values.
     *
     * @param string $parameter
     * @return string
     * @throws \OpenCFP\Config\InvalidParameterException
     */
    private function replacePlaceholders($parameter)
    {
        // Extract placeholders to replace in the given parameter
        if (!preg_match_all('#\$\{([a-z0-9\._-]+)\}#', $parameter, $matches)) {
            return $parameter;
        }

        // Replace the found placeholders by their values.
        $placeholders = array();
        $replacements = array();
        foreach ($matches[1] as $key => $placeholder) {
            if (!isset($this->container[$placeholder])) {
                throw new InvalidParameterException($placeholder, sprintf(
                    'There is no %s registered parameter in the container.',
                    $placeholder
                ));
            }

            $value = $this->container[$placeholder];
            if (!is_string($value) && !is_numeric($value)) {
                throw new InvalidParameterException($placeholder, sprintf(
                    'Only string or numeric placeholders can be replaced. '.
                    'The %s parameter is of type %s.',
                    $placeholder,
                    gettype($value)
                ));
            }

            $placeholders[] = $matches[0][$key];
            $replacements[] = $value;
        }

        return str_replace($placeholders, $replacements, $parameter);
    }
}