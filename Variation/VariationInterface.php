<?php

declare (strict_types=1);

namespace Coshi\Variator\Variation;

interface VariationInterface extends \Iterator
{
    /**
     * @return bool
     */
    public function isContextDependent() : bool;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function dependsOn(string $name) : bool;

    /**
     * @return VariationInterface[]
     */
    public function getNested() : array;

    /**
     * @param VariationInterface $variation
     *
     * @return VariationInterface
     */
    public function addNested(VariationInterface $variation) : VariationInterface;

    /**
     * @return string
     */
    public function getType() : string;

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @param array $parameters
     *
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    public static function validateParameters(array $parameters) : bool;

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function validateValue($value) : bool;
}
