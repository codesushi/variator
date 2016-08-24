<?php

declare (strict_types=1);

namespace Coshi\Variator\Variation\Type;

use Coshi\Variator\ConfigResolver;
use Coshi\Variator\Variation\AbstractVariation;

class IntVariation extends AbstractVariation
{
    /**
     * @var array|int
     */
    private $min;

    /**
     * @var array|int
     */
    private $max;

    /**
     * @var int
     */
    private $current;

    /**
     * @var array
     */
    private $minCallback;

    /**
     * @var array
     */
    private $maxCallback;

    public function __construct(string $name, array $parameters, ConfigResolver $configResolver)
    {
        parent::__construct($name, $parameters, $configResolver);

        if (is_array($parameters['max'])) {
            $this->maxCallback = $configResolver->resolveCallback($parameters['max']);
        } else {
            $this->max = (int) $parameters['max'];
        }

        if (is_array($parameters['min'])) {
            $this->minCallback = $configResolver->resolveCallback($parameters['min']);
        } else {
            $this->min = (int) $parameters['min'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function validateParameters(array $parameters) : bool
    {
        if (!isset($parameters['max'], $parameters['min'])) {
            throw new \InvalidArgumentException('Min and max parameters should be defined');
        }

        if (!is_numeric($parameters['max']) && !is_array($parameters['max'])) {
            throw new \InvalidArgumentException('Max parameter should be numeric or callable');
        }

        if (!is_numeric($parameters['min']) && !is_array($parameters['min'])) {
            throw new \InvalidArgumentException('Min parameter should be numeric or callable');
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function dependsOn(string $name) : bool
    {
        if (!parent::dependsOn($name)) {
            return false;
        }

        if (is_array($this->maxCallback)) {
            return $this->containsArgumentsPattern($this->maxCallback['arguments'], $name);
        }

        if (is_array($this->minCallback)) {
            return $this->containsArgumentsPattern($this->minCallback['arguments'], $name);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public static function validateValue($value) : bool
    {
        return is_numeric($value);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getType() : string
    {
        return 'int';
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrent()
    {
        return (int) $this->current;
    }

    /**
     * {@inheritdoc}
     */
    public function pushForward()
    {
        ++$this->current;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid() : bool
    {
        return $this->current <= $this->max;
    }

    protected function doRewind()
    {
        if (is_array($this->maxCallback)) {
            $this->max = (int) $this->configResolver->call($this->maxCallback);
        }

        if (is_array($this->minCallback)) {
            $this->min = (int) $this->configResolver->call($this->minCallback);
        }
        $this->current = $this->min;
    }
}
