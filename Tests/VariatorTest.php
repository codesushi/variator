<?php

declare (strict_types=1);

namespace Coshi\Variator\Tests;

use Coshi\Variator\ConfigResolver;
use Coshi\Variator\Exception\CircularDependencyException;
use Coshi\Variator\Tests\Fixtures\TestClass;
use Coshi\Variator\Variation\VariationInterface;
use Coshi\Variator\VariationFactory;
use Coshi\Variator\VariationsTreeBuilder;

class VariatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VariationsTreeBuilder
     */
    protected $variatorBuilder;

    /**
     * @var TestClass
     */
    protected $instance;

    protected function setUp()
    {
        $factory = new VariationFactory(new ConfigResolver());

        $this->variatorBuilder = new VariationsTreeBuilder($factory);
        $this->instance = new TestClass();
    }

    public function testSingleScalar()
    {
        $min = 1;
        $max = 10;

        $variator = $this->variatorBuilder->build([
            'id' => [
                'type' => 'int',
                'min' => $min,
                'max' => $max,
            ],
        ]);
        $expected = array_map(function ($value) {
            return ['id' => $value];
        }, range(1, 10));

        $this->runVariator($expected, $variator);
    }

    public function testSingleNumericEnum()
    {
        $values = range(2, 20);

        $variator = $this->variatorBuilder->build([
            'id' => [
                'type' => 'enum',
                'values' => $values,
            ],
        ]);
        $expected = array_map(function ($value) {
            return ['id' => $value];
        }, $values);

        $this->runVariator($expected, $variator);
    }

    public function testSingleTextEnum()
    {
        $values = [
            'first', 'second', 'third', 'test', '', 'random',
        ];

        $variator = $this->variatorBuilder->build([
            'text' => [
                'type' => 'enum',
                'values' => $values,
            ],
        ]);
        $expected = array_map(function ($value) {
            return ['text' => $value];
        }, $values);

        $this->runVariator($expected, $variator);
    }

    public function testSingleSimpleStaticCallback()
    {
        $variator = $this->variatorBuilder->build([
            'text' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeStaticValues'],
            ],
        ]);
        $expected = array_map(function ($value) {
            return ['text' => $value];
        }, TestClass::getSomeStaticValues());

        $this->runVariator($expected, $variator);
    }

    public function testSingleSimpleServiceCallback()
    {
        $variator = $this->variatorBuilder->build([
            'text' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeValues'],
            ],
        ]);
        $expected = array_map(function ($value) {
            return ['text' => $value];
        }, $this->instance->getSomeValues());

        $this->runVariator($expected, $variator);
    }

    public function testSingleSimpleServiceCallbackWithArguments()
    {
        for ($i = 0;$i <= 2;++$i) {
            $variator = $this->variatorBuilder->build([
                'text' => [
                    'type' => 'callback',
                    'callback' => [TestClass::class, 'getSomeValuesByParameter', [$i]],
                ],
            ]);
            $expected = array_map(function ($value) {
                return ['text' => $value];
            }, $this->instance->getSomeValuesByParameter($i));

            $this->runVariator($expected, $variator);
        }
    }

    public function testMultipleScalar()
    {
        $variator = $this->variatorBuilder->build([
            'id' => [
                'type' => 'int',
                'min' => 1,
                'max' => 3,
            ],
            'id1' => [
                'type' => 'int',
                'min' => 5,
                'max' => 7,
            ],
        ]);
        $expected = [
            ['id' => 1, 'id1' => 5],
            ['id' => 2, 'id1' => 5],
            ['id' => 3, 'id1' => 5],
            ['id' => 1, 'id1' => 6],
            ['id' => 2, 'id1' => 6],
            ['id' => 3, 'id1' => 6],
            ['id' => 1, 'id1' => 7],
            ['id' => 2, 'id1' => 7],
            ['id' => 3, 'id1' => 7],
        ];

        $this->runVariator($expected, $variator);
    }

    public function testMultipleMixed()
    {
        $variator = $this->variatorBuilder->build([
            'id' => [
                'type' => 'int',
                'min' => 1,
                'max' => 3,
            ],
            'text' => [
                'type' => 'enum',
                'values' => ['first', 'second'],
            ],
        ]);
        $expected = [
            ['id' => 1, 'text' => 'first'],
            ['id' => 2, 'text' => 'first'],
            ['id' => 3, 'text' => 'first'],
            ['id' => 1, 'text' => 'second'],
            ['id' => 2, 'text' => 'second'],
            ['id' => 3, 'text' => 'second'],
        ];
        $this->runVariator($expected, $variator);
    }

    public function testThreeDimensional()
    {
        $variator = $this->variatorBuilder->build([
            'id' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeValues'],
            ],
            'id1' => [
                'type' => 'int',
                'min' => 15,
                'max' => 20,
            ],
            'text' => [
                'type' => 'enum',
                'values' => ['first', 'second', 'third'],
            ],
        ]);
        $expected = [];
        $values = $this->instance->getSomeValues();

        for ($i = 15;$i <= 20;++$i) {
            foreach ($values as $value) {
                foreach (['first', 'second', 'third'] as $item) {
                    $stub = ['id' => $value, 'id1' => $i, 'text' => $item];
                    ksort($stub);
                    $expected[] = $stub;
                }
            }
        }
        $this->runVariator($expected, $variator);
    }

    public function testFourDimensional()
    {
        $variator = $this->variatorBuilder->build([
            'id' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeValues'],
            ],
            'id1' => [
                'type' => 'int',
                'min' => 15,
                'max' => 20,
            ],
            'text' => [
                'type' => 'enum',
                'values' => ['first', 'second', 'third'],
            ],
            'another_text' => [
                'type' => 'enum',
                'values' => ['fifth', 'sixth', 'seventh'],
            ],
        ]);
        $expected = [];
        $values = $this->instance->getSomeValues();

        for ($i = 15;$i <= 20;++$i) {
            foreach ($values as $value) {
                foreach (['first', 'second', 'third'] as $item) {
                    foreach (['fifth', 'sixth', 'seventh'] as $text) {
                        $stub = ['another_text' => $text, 'id' => $value, 'id1' => $i, 'text' => $item];
                        ksort($stub);
                        $expected[] = $stub;
                    }
                }
            }
        }
        $this->runVariator($expected, $variator);
    }

    public function testTwoDimensionalContextDependent()
    {
        $variator = $this->variatorBuilder->build([
            'text' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeValuesByParameter', ['@id']],
                'context_dependent' => true,
            ],
            'id' => [
                'type' => 'int',
                'min' => 0,
                'max' => 2,
            ],
        ]);
        $expected = [];

        for ($i = 0;$i <= 2;++$i) {
            $values = $this->instance->getSomeValuesByParameter($i);

            foreach ($values as $value) {
                $item = ['id' => $i, 'text' => $value];
                ksort($item);
                $expected[] = $item;
            }
        }
        $this->runVariator($expected, $variator);
    }

    public function testThreeDimensionalContextDependent()
    {
        $variator = $this->variatorBuilder->build([
            'text' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeValuesByParameter', ['@id']],
                'context_dependent' => true,
            ],
            'id' => [
                'type' => 'int',
                'min' => 0,
                'max' => 2,
            ],
            'enum' => [
                'type' => 'enum',
                'values' => ['first', 'second', 'third'],
            ],
        ]);
        $expected = [];

        for ($i = 0;$i <= 2;++$i) {
            $values = $this->instance->getSomeValuesByParameter($i);

            foreach ($values as $value) {
                foreach (['first', 'second', 'third'] as $enum) {
                    $item = ['id' => $i, 'text' => $value, 'enum' => $enum];
                    ksort($item);
                    $expected[] = $item;
                }
            }
        }
        $this->runVariator($expected, $variator);
    }

    public function testExceptionOnCircularDependency()
    {
        $this->expectException(CircularDependencyException::class);
        $this->variatorBuilder->build([
            'text' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeValuesByParameter', ['@text']],
                'context_dependent' => true,
            ],
            'id' => [
                'type' => 'int',
                'min' => 0,
                'max' => 2,
            ],
            'enum' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeTextByParameter', ['@id']],
                'context_dependent' => true,
            ],
        ]);
    }

    public function testStuff()
    {
        $this->expectException(CircularDependencyException::class);
        $this->variatorBuilder->build([
            'text' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeValuesByParameter', ['@enum']],
                'context_dependent' => true,
            ],
            'id' => [
                'type' => 'int',
                'min' => 0,
                'max' => 2,
            ],
            'enum' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeTextByParameter', ['@text']],
                'context_dependent' => true,
            ],
        ]);
    }

    public function testMoreStuff()
    {
        $this->expectException(CircularDependencyException::class);
        $this->variatorBuilder->build([
            'text' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeValuesByParameter', ['@enum']],
                'context_dependent' => true,
            ],
            'enum' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeTextByParameter', ['@anotherEnum']],
                'context_dependent' => true,
            ],
            'anotherEnum' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeTextByParameter', ['@text']],
                'context_dependent' => true,
            ],
        ]);
    }

    public function testEvenMoreStuff()
    {
        $this->expectException(CircularDependencyException::class);
        $this->variatorBuilder->build([
            'text' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeValuesByParameter', ['@enum']],
                'context_dependent' => true,
            ],
            'enum' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeTextByParameter', ['@anotherEnum']],
                'context_dependent' => true,
            ],
            'anotherEnum' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeTextByParameter', ['@brandNewEnum']],
                'context_dependent' => true,
            ],
            'brandNewEnum' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeTextByParameter', ['@text']],
                'context_dependent' => true,
            ],
        ]);
    }

    public function testThreeDimensionalWithTwoContextDependent()
    {
        $dummy = [];
        $variator = $this->variatorBuilder->build([
            'text' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeValuesByParameter', ['@id']],
                'context_dependent' => true,
            ],
            'id' => [
                'type' => 'int',
                'min' => ['count', [$dummy]],
                'max' => 2,
            ],
            'enum' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeTextByParameter', ['@id']],
                'context_dependent' => true,
            ],
        ]);
        $expected = [];

        for ($i = 0;$i <= 2;++$i) {
            $values = $this->instance->getSomeValuesByParameter($i);
            $enum = $this->instance->getSomeTextByParameter($i);

            foreach ($values as $value) {
                foreach ($enum as $enumValue) {
                    $item = ['enum' => $enumValue, 'id' => $i, 'text' => $value];
                    ksort($item);
                    $expected[] = $item;
                }
            }
        }
        $this->runVariator($expected, $variator);
    }

    public function testDoubleDependency()
    {
        $variator = $this->variatorBuilder->build([
            'id1' => [
                'type' => 'int',
                'min' => 0,
                'max' => 2,
            ],
            'text' => [
                'type' => 'enum',
                'values' => ['first', 'second'],
            ],
            'enum' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeValuesByKeyAndText', ['@id1', '@text']],
                'context_dependent' => true,
            ],
        ]);
        $expected = [];

        for ($i1 = 0;$i1 <= 2;++$i1) {
            foreach (['first', 'second'] as $text) {
                $values = $this->instance->getSomeValuesByKeyAndText($i1, $text);

                foreach ($values as $value) {
                    $item = ['id1' => $i1, 'text' => $text, 'enum' => $value];
                    ksort($item);
                    $expected[] = $item;
                }
            }
        }
        $this->runVariator($expected, $variator);
    }

    public function testTransitiveContextDependent()
    {
        $variator = $this->variatorBuilder->build([
            'id' => [
                'type' => 'int',
                'min' => 0,
                'max' => 2,
            ],
            'text' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeValuesByParameter', ['@id']],
                'context_dependent' => true,
            ],
            'enum' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeTextByTextParameter', ['@text']],
                'context_dependent' => true,
            ],
        ]);
        $expected = [];

        for ($i = 0;$i <= 2;++$i) {
            $values = $this->instance->getSomeValuesByParameter($i);

            foreach ($values as $value) {
                $enum = $this->instance->getSomeTextByTextParameter($value);
                foreach ($enum as $enumValue) {
                    $item = ['enum' => $enumValue, 'id' => $i, 'text' => $value];
                    ksort($item);
                    $expected[] = $item;
                }
            }
        }
        $this->runVariator($expected, $variator);
    }

    public function testTwoDimensionalTransitiveContextDependent()
    {
        $variator = $this->variatorBuilder->build([
            'id' => [
                'type' => 'int',
                'min' => 0,
                'max' => 2,
            ],
            'text' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeValuesByParameter', ['@id']],
                'context_dependent' => true,
            ],
            'enum' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeTextByTextParameter', ['@text']],
                'context_dependent' => true,
            ],
            'enum2' => [
                'type' => 'enum',
                'values' => ['holy', 'molly', 'too_much'],
            ],

        ]);
        $expected = [];

        for ($i = 0;$i <= 2;++$i) {
            $values = $this->instance->getSomeValuesByParameter($i);

            foreach ($values as $value) {
                $enum = $this->instance->getSomeTextByTextParameter($value);
                foreach ($enum as $enumValue) {
                    foreach (['holy', 'molly', 'too_much'] as $item) {
                        $item = ['enum' => $enumValue, 'id' => $i, 'text' => $value, 'enum2' => $item];
                        ksort($item);
                        $expected[] = $item;
                    }
                }
            }
        }
        $this->runVariator($expected, $variator);
    }

    public function testTwoDimensionalDoubleDependentTransitive()
    {
        $variator = $this->variatorBuilder->build([
            'id1' => [
                'type' => 'int',
                'min' => 0,
                'max' => 2,
            ],
            'text' => [
                'type' => 'enum',
                'values' => ['first', 'second'],
            ],
            'enum' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeValuesByKeyAndText', ['@id1', '@text']],
                'context_dependent' => true,
            ],
            'stuff' => [
                'type' => 'callback',
                'callback' => [TestClass::class, 'getSomeValuesByKeyAndTextValue', ['@enum']],
                'context_dependent' => true,
            ],
        ]);
        $expected = [];

        for ($i1 = 0;$i1 <= 2;++$i1) {
            foreach (['first', 'second'] as $text) {
                $values = $this->instance->getSomeValuesByKeyAndText($i1, $text);

                foreach ($values as $value) {
                    $stuff = $this->instance->getSomeValuesByKeyAndTextValue($value);

                    foreach ($stuff as $st) {
                        $item = ['id1' => $i1, 'text' => $text, 'enum' => $value, 'stuff' => $st];
                        ksort($item);
                        $expected[] = $item;
                    }
                }
            }
        }
        $this->runVariator($expected, $variator);
    }

    private function runVariator(array $expected, VariationInterface $variation)
    {
        $keys = array_keys(reset($expected));

        foreach ($variation as $value) {
            ksort($value);

            foreach ($keys as $key) {
                static::assertArrayHasKey($key, $value);
            }
            static::assertTrue(in_array($value, $expected, true));
            unset($expected[array_search($value, $expected, true)]);
        }
        static::assertEmpty($expected);
    }
}
