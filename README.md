A simple library to generate arrays of values combinations.
-----------------------------------------------------------

Usage:

````
    $factory = new VariationFactory();
    $builder = new VariationsTreeBuilder($factory);
    
    $config = [
        'text' => [
            'type' => 'enum',
            'values' => ['first', 'second', 'third'],
        ],
    ];
    $variations = $builder->build($config);
    
    foreach ($variations as $values) {
        foreach($values as $value) {
            print($value); // displays first, second, third            
        }
    }
````

More complex:

````
    $factory = new VariationFactory();
    $builder = new VariationsTreeBuilder($factory);
    
    $config = [
        'text' => [
            'type' => 'enum',
            'values' => ['first', 'second', 'third'],
        ],
        'number' => [
            'type' => 'int',
            'min' => 0,
            'max' => 2
        ],
    ];
    $variations = $builder->build($config);
    
    foreach ($variations as $values) {
        echo sprintf('text: %s, number: %d', $values['text'], $values['number']);
        echo PHP_EOL;
    }
````

Output will be:
````
    text: first, number: 0
    text: second, number: 0
    text: third, number: 0
    text: first, number: 1
    text: second, number: 1
    text: third, number: 1
    text: first, number: 2
    text: second, number: 2
    text: third, number: 2
````

It can call methods and functions in order to get values:

````
    class DummyClass
    {
        public function someValues()
        {
            return [0, 1, 2];
        }
    }
    $factory = new VariationFactory();
    $builder = new VariationsTreeBuilder($factory);
    
    $config = [
        'text' => [
            'type' => 'enum',
            'values' => ['first', 'second', 'third'],
        ],
        'number' => [
            'type' => 'callback',
            'callback' => [DummyClass::class, 'someValues']
        ],
    ];
    $variations = $builder->build($config);
    
    foreach ($variations as $values) {
        echo sprintf('text: %s, number: %d', $values['text'], $values['number']);
        echo PHP_EOL;
    }
````

Result will be exactly the same as from the previous one.
Result will be the same for callback defined as '['range', [0, 2]]'.


Supports cross-referencing between variations:

````
    class DummyClass
    {
        public function someValues($text)
        {
            $values = [
                'first' => [0, 1],
                'second' => [2, 3],
                'third' => [4, 5],
            ];
    
            return $values[$text];
        }
    }
    $factory = new VariationFactory();
    $builder = new VariationsTreeBuilder($factory);
    
    $config = [
        'text' => [
            'type' => 'enum',
            'values' => ['first', 'second', 'third'],
        ],
        'number' => [
            'type' => 'callback',
            'callback' => [DummyClass::class, 'someValues', ['@text']],
            'context_dependent' => true
        ],
    ];
    $variations = $builder->build($config);
    
    foreach ($variations as $values) {
        echo sprintf('text: %s, number: %d', $values['text'], $values['number']);
        echo PHP_EOL;
    }
````

Output will be:

````
    text: first, number: 0
    text: first, number: 1
    text: second, number: 2
    text: second, number: 3
    text: third, number: 4
    text: third, number: 5
````
