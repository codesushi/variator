<?php

declare (strict_types=1);

namespace Codesushi\Variator\Variation\Type;

use Codesushi\Variator\ConfigResolver;

class EnumVariation extends ArrayVariation
{
    /**
     * @var array
     */
    protected $requiredArgs = ['values'];

    public function __construct(string $name, array $parameters, ConfigResolver $configResolver)
    {
        parent::__construct($name, $parameters, $configResolver);
        $this->values = $parameters['values'];
    }

    /**
     * {@inheritdoc}
     */
    public function getType() : string
    {
        return 'enum';
    }
}
