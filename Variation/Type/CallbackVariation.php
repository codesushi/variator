<?php

declare (strict_types=1);

namespace Codesushi\Variator\Variation\Type;

use Codesushi\Variator\ConfigResolver;

class CallbackVariation extends ArrayVariation
{
    /**
     * @var array
     */
    protected $requiredArgs = ['callback'];

    /**
     * @var array
     */
    protected $callBack;

    public function __construct(string $name, array $parameters, ConfigResolver $configResolver)
    {
        parent::__construct($name, $parameters, $configResolver);

        $this->callBack = $configResolver->resolveCallback($parameters['callback']);
    }

    /**
     * {@inheritdoc}
     */
    public function doRewind()
    {
        $this->values = (array) $this->configResolver->call($this->callBack);
    }

    /**
     * {@inheritdoc}
     */
    public function dependsOn(string $name) : bool
    {
        if (!parent::dependsOn($name)) {
            return false;
        }

        return $this->containsArgumentsPattern($this->callBack['arguments'], $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getType() : string
    {
        return 'callback';
    }
}
