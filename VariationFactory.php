<?php

declare (strict_types=1);

namespace Codesushi\Variator;

use Codesushi\Variator\Variation\AbstractVariation;
use Codesushi\Variator\Variation\Type as VariationType;

class VariationFactory
{
    protected $map = [
        'int' => VariationType\IntVariation::class,
        'enum' => VariationType\EnumVariation::class,
        'callback' => VariationType\CallbackVariation::class,
    ];

    /**
     * @var ConfigResolver
     */
    protected $configResolver;

    /**
     * @param string $name
     * @param array  $parameters
     * 
     * @return AbstractVariation
     *
     * @throws \InvalidArgumentException
     */
    public function createNew(string $name, array $parameters)
    {
        if (!$this->configResolver instanceof ConfigResolver) {
            $this->configResolver = $this->getResolver();
        }

        if (!isset($this->map[$parameters['type']])) {
            throw new \InvalidArgumentException(sprintf('Variation type %s is not valid', $parameters['type']));
        }
        $class = $this->map[$parameters['type']];
        $class::validateParameters($parameters);

        return new $class($name, $parameters, $this->configResolver);
    }

    /**
     * @param string $type
     * @param $value
     * 
     * @throws \InvalidArgumentException
     */
    public function validateValue(string $type, $value)
    {
        if (!isset($this->map[$type])) {
            throw new \InvalidArgumentException(sprintf('Variation type %s is not valid', $type));
        }
        $class = $this->map[$type];

        if (false === $class::validateValue($value)) {
            $stringType = in_array(gettype($value), ['string', 'float'], true) ? $value : gettype($value);

            throw new \InvalidArgumentException(sprintf('"%s" is not a valid value for type "%s"', $stringType, $type));
        }
    }

    /**
     * @param string $type
     * @param string $className
     */
    public function registerType(string $type, string $className)
    {
        $this->map[$type] = $className;
    }

    /**
     * @return ConfigResolver
     */
    protected function getResolver()
    {
        return new ConfigResolver();
    }
}
