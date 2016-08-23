<?php

declare (strict_types=1);

namespace Coshi\Variator;

use Coshi\Variator\Exception\InvalidConfigurationException;
use Coshi\Variator\Variation\VariationInterface;

class ConfigResolver
{
    /**
     * @var callable[]
     */
    protected $contextCallbacks;

    /**
     * @param array  $arguments
     * @param string $name
     *
     * @return bool
     */
    public function containsArgumentsPattern(array $arguments, string $name) : bool
    {
        foreach ($arguments as $key => $argument) {
            if (is_string($argument) && '@'.$name === $argument) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array  $requiredArgs
     * @param array  $parameters
     * @param string $type
     *
     * @return bool
     *
     * @throws InvalidConfigurationException
     */
    public function requireArguments(array $requiredArgs, array $parameters, string $type) : bool
    {
        foreach ($requiredArgs as $parameter) {
            if (!array_key_exists($parameter, $parameters)) {
                throw new InvalidConfigurationException(sprintf('Parameter "%s" for type "%s" is required', $parameter, $type));
            }
        }

        return true;
    }

    /**
     * @param array $config
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function resolveCallback(array $config)
    {
        if (count($config) < 1) {
            throw new \InvalidArgumentException('Cannot resolve empty callback');
        }

        if (!isset($config[1]) || is_array($config[1])) {
            $callable = $config[0];
            $arguments = $config[1] ?? [];
        } else {
            $callable = [$this->resolveInstance($config), $config[1]];
            $arguments = $config[2] ?? [];
        }

        return [
            'callback' => $callable,
            'arguments' => $arguments,
        ];
    }

    /**
     * @param array $callback - callback config returned from ConfigResolver::resolveCallback() method
     *
     * @return mixed
     */
    public function call(array $callback)
    {
        return call_user_func_array($callback['callback'], $this->processArguments($callback['arguments']));
    }

    /**
     * @param array $arguments
     *
     * @return array
     */
    public function processArguments(array $arguments) : array
    {
        foreach ($arguments as $key => $value) {
            if (is_string($value) && 0 === strpos($value, '@')) {
                $name = substr($value, 1);

                if (isset($this->contextCallbacks[$name])) {
                    $arguments[$key] = call_user_func($this->contextCallbacks[$name]);
                }
            }
        }

        return $arguments;
    }

    /**
     * @param VariationInterface $variation
     *
     * @return bool
     */
    public function addCallback(VariationInterface $variation)
    {
        $closure = (function () {
            if (!$this->isValid()) {
                $this->doRewind();
            }

            return $this->getCurrent();
        })->bindTo($variation, $variation);
        $this->contextCallbacks[$variation->getName()] = $closure;

        return true;
    }

    protected function resolveInstance(array $config)
    {
        $instance = $config[0];

        if (!is_string($instance)) {
            throw new InvalidConfigurationException(sprintf('Expected type "string" for callback class, found "%s"', gettype($instance)));
        }

        if (!class_exists($instance)) {
            throw new \InvalidArgumentException(sprintf('Class %s does not exist', $instance));
        }
        $reflection = new \ReflectionClass($instance);
        $method = $reflection->getMethod($config[1]);

        if (!$method->isStatic()) {
            $instance = new $instance();
        }

        return $instance;
    }
}
