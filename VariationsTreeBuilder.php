<?php

declare (strict_types=1);

namespace Coshi\Variator;

use Coshi\Variator\Variation\AbstractVariation;
use Coshi\Variator\Exception\Helper;
use Coshi\Variator\Variation\VariationInterface;

class VariationsTreeBuilder
{
    public function __construct(VariationFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param array $config
     * 
     * @return VariationInterface
     * 
     * @throws Exception\CircularDependencyException
     * @throws \InvalidArgumentException
     */
    public function build(array $config)
    {
        /** @var AbstractVariation[] $variations */
        $variations = [];

        foreach ($config as $name => $variation) {
            $variations[$name] = $this->factory->createNew($name, $variation);
        }

        return new ContainerVariation($this->buildTree($variations));
    }

    /**
     * @param AbstractVariation[] $variations
     * 
     * @return AbstractVariation[]
     * 
     * @throws Exception\CircularDependencyException
     */
    private function buildTree(array $variations)
    {
        $this->detectCircularDependencies($variations);
        $dependent = [];

        foreach ($variations as $root) {
            foreach ($variations as $key => $nested) {
                if ($nested->dependsOn($root->getName())) {
                    $dependent[] = $key;
                    $root->addNested($nested);
                }
            }
        }

        foreach ($dependent as $key) {
            unset($variations[$key]);
        }

        return $variations;
    }

    /**
     * @param array $variations
     *
     * @return array
     *
     * @throws Exception\CircularDependencyException
     */
    private function detectCircularDependencies(array $variations)
    {
        $tree = [];

        foreach ($variations as $variation) {
            $tree[$variation->getName()] = $this->detectCircular($variations, $variation, [$variation->getName()]);
        }

        return $tree;
    }

    /**
     * @param array             $variations
     * @param AbstractVariation $variation
     * @param array             $path
     *
     * @return bool
     *
     * @throws Exception\CircularDependencyException
     */
    private function detectCircular(array $variations, AbstractVariation $variation, array $path)
    {
        foreach ($variations as $item) {
            if ($item->dependsOn($variation->getName())) {
                if ($item === $variation
                    || $variation->dependsOn($item->getName())
                    || in_array($item->getName(), $path, true)) {
                    $path[] = $item->getName();
                    throw Helper::fromPath(implode('.', $path));
                }
                $this->detectCircular($variations, $item, $path);
            }
        }

        return false;
    }
}
