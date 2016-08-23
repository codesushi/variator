<?php

declare (strict_types=1);

namespace Coshi\Variator;

use Coshi\Variator\Variation\AbstractVariation;

class ContainerVariation extends AbstractVariation
{
    public function __construct(array $variations)
    {
        $this->nested = $variations;
        $this->rewind();
    }

    /**
    * {@inheritdoc}
    */
    public function getType() : string
    {
        return 'container';
    }

    /**
     * {@inheritdoc}
     */
    protected function doRewind()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrent()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function pushForward()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isValid() : bool
    {
        return false;
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
