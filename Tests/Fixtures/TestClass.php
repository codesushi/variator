<?php

declare (strict_types=1);

namespace Coshi\Variator\Tests\Fixtures;

class TestClass
{
    public function getSomeValues() : array
    {
        return [
            'one',
            'two',
            'three',
            'four',
        ];
    }

    public static function getSomeStaticValues() : array
    {
        return [
            'one',
            'two',
            'three',
            'four',
        ];
    }

    public function getSomeValuesByParameter($key) : array
    {
        $values = [
            0 => [
                '0one',
                '0two',
                '0three',
                '0four',
            ],
            1 => [
                '1one',
                '1two',
                '1three',
                '1four',
            ],
            2 => [
                '2one',
                '2two',
                '2three',
                '2four',
            ],
        ];

        return $values[$key];
    }

    public function getSomeTextByParameter($key) : array
    {
        $values = [
            0 => [
                'first',
                'second',
                'third',
            ],
            1 => [
                'first',
                'second',
                'third',
            ],
            2 => [
                'first',
                'second',
                'third',
            ],
        ];

        return $values[$key];
    }

    public function getSomeTextByTextParameter($key) : array
    {
        $values = [
            '0one' => [
                'a',
                'b',
                'c',
            ],
            '0two' => [
                'd',
                'e',
                'f',
            ],
            '0three' => [
                'g',
                'h',
                'i',
            ],
            '0four' => [
                'j',
                'k',
                'l',
            ],
            '1one' => [
                'm',
                'n',
                'o',
            ],
            '1two' => [
                'p',
                'q',
                'r',
            ],
            '1three' => [
                's',
                't',
                'u',
            ],
            '1four' => [
                'v',
                'w',
                'x',
            ],
            '2one' => [
                'y',
                'z',
                '1a',
            ],
            '2two' => [
                '1b',
                '1c',
                '1d',
            ],
            '2three' => [
                '1e',
                '1f',
                '1g',
            ],
            '2four' => [
                '1h',
                '1i',
                '1j',
            ],
        ];

        return $values[$key];
    }

    public function getSomeValuesByKeyAndText($key1, $text)
    {
        $values = [
            0 => [
                'first' => [
                    '00first',
                    '00second',
                    '00third',
                ],
                'second' => [
                    '01first',
                    '01second',
                    '01third',
                ],
            ],
            1 => [
                'first' => [
                    '10first',
                    '10second',
                    '10third',
                ],
                'second' => [
                    '11first',
                    '11second',
                    '11third',
                ],
            ],
            2 => [
                'first' => [
                    '20first',
                    '20second',
                    '20third',
                ],
                'second' => [
                    '21first',
                    '21second',
                    '21third',
                ],
            ],
        ];

        return $values[$key1][$text];
    }

    public function getSomeValuesByKeyAndTextValue($key)
    {
        $values = [
            '00first' => ['001first', '002first', '003first'],
            '00second' => ['001second', '002second', '003second'],
            '00third' => ['001third', '002third', '003third'],
            '01first' => ['011first', '012first', '013first'],
            '01second' => ['011second', '012second', '013second'],
            '01third' => ['011third', '012third', '013third'],
            '10first' => ['101first', '102first', '103first'],
            '10second' => ['101second', '102second', '103second'],
            '10third' => ['101third', '102third', '103third'],
            '11first' => ['111first', '112first', '113first'],
            '11second' => ['111second', '112second', '113second'],
            '11third' => ['111third', '112third', '113third'],
            '20first' => ['201first', '202first', '203first'],
            '20second' => ['201second', '202second', '203second'],
            '20third' => ['201third', '202third', '203third'],
            '21first' => ['211first', '212first', '213first'],
            '21second' => ['211second', '212second', '213second'],
            '21third' => ['211third', '212third', '213third'],
        ];

        return $values[$key];
    }
}
