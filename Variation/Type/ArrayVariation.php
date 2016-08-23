<?php

declare (strict_types=1);

namespace Coshi\Variator\Variation\Type;

use Coshi\Variator\Variation\AbstractVariation;

abstract class ArrayVariation extends AbstractVariation
{
    /**
     * @var array
     */
    protected $values = [];

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->values);
    }

    /**
     * {@inheritdoc}
     */
    public function previous()
    {
        return prev($this->values);
    }

    /**
     * {@inheritdoc}
     */
    protected function doRewind()
    {
        return reset($this->values);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrent()
    {
        return current($this->values);
    }

    /**
     * {@inheritdoc}
     */
    public function pushForward()
    {
        next($this->values);
    }

    /**
     * {@inheritdoc}
     */
    public function isValid() : bool
    {
        return false !== $this->getCurrent();
    }

    /**
     * {@inheritdoc}
     */
    public static function validateParameters(array $parameters) : bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function validateValue($value) : bool
    {
        return true;
    }
}
