<?php

declare (strict_types=1);

namespace Codesushi\Variator\Variation;

use Codesushi\Variator\ConfigResolver;

abstract class AbstractVariation implements VariationInterface
{
    /**
     * @var array
     */
    protected $requiredArgs = [];

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $contextDependent;

    /**
     * @var callable[]
     */
    protected $contextCallbacks;

    /**
     * @var VariationInterface[];
     */
    protected $nested = [];

    /**
     * @var ConfigResolver
     */
    protected $configResolver;

    /**
     * @var int
     */
    private $sumKey = 0;

    public function __construct(string $name, array $parameters, ConfigResolver $configResolver)
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->configResolver = $configResolver;

        if (isset($parameters['context_dependent'])) {
            $this->contextDependent = $parameters['context_dependent'];
        }
        $this->requireArguments($parameters);
    }

    /**
     * @return void
     */
    abstract protected function doRewind();

    /**
     * @return void
     */
    abstract protected function pushForward();

    /**
     * @return bool
     */
    abstract protected function isValid() : bool;

    /**
     * @return mixed
     */
    abstract protected function getCurrent();

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $result = [];

        if (!$this->isRoot()) {
            $result = [
                $this->getName() => $this->getCurrent(),
            ];
        }
        $nested = $this->getNested();

        foreach ($nested as $variation) {
            $result = array_merge($result, $variation->current());
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        ++$this->sumKey;
        $nested = $this->getNested();

        foreach ($nested as $variation) {
            if (!$variation->valid()) {
                continue;
            }
            $variation->next();

            if (!$variation->valid()) {
                continue;
            }
            $this->reset();

            return;
        }
        $this->pushForward();

        if (!$this->isValid()) {
            return;
        }
        $this->reset(false);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->sumKey;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        foreach ($this->getNested() as $variation) {
            if ($variation->isValid()) {
                return true;
            }
        }

        return $this->isValid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->doRewind();
        $this->reset(false);
    }

    /**
     * {@inheritdoc}
     */
    public function isContextDependent() : bool
    {
        return (bool) $this->contextDependent;
    }

    /**
     * {@inheritdoc}
     */
    public function dependsOn(string $name) : bool
    {
        return $this->isContextDependent();
    }

    /**
     * {@inheritdoc}
     */
    public function getNested() : array
    {
        return $this->nested;
    }

    /**
     * {@inheritdoc}
     */
    public function addNested(VariationInterface $variation) : VariationInterface
    {
        $this->configResolver->addCallback($this);
        $this->nested[] = $variation;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param array  $arguments
     * @param string $name
     *
     * @return bool
     */
    protected function containsArgumentsPattern(array $arguments, string $name) : bool
    {
        return $this->configResolver->containsArgumentsPattern($arguments, $name);
    }

    /**
     * @param array $parameters
     *
     * @return bool
     *
     * @throws \Codesushi\Variator\Exception\InvalidConfigurationException
     */
    protected function requireArguments(array $parameters) : bool
    {
        return $this->configResolver->requireArguments($this->requiredArgs, $parameters, $this->getType());
    }

    /**
     * @return bool
     */
    protected function isRoot() : bool
    {
        return 'container' === $this->getType();
    }

    /**
     * @param bool $strict
     *
     * @return bool
     */
    private function reset(bool $strict = true) : bool
    {
        foreach ($this->getNested() as $variation) {
            if (!$strict || !$variation->isValid()) {
                $variation->rewind();
            }
        }

        return true;
    }
}
