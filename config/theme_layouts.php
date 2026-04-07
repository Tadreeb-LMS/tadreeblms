<?php

return [
    // Canonical layout ids mapped to semantic names.
    'layouts' => [
        '1' => [
            'slug' => 'classic',
            'name' => 'Classic',
        ],
        '2' => [
            'slug' => 'modern',
            'name' => 'Modern',
        ],
        '3' => [
            'slug' => 'minimal',
            'name' => 'Minimal',
        ],
        '4' => [
            'slug' => 'magazine',
            'name' => 'Magazine',
        ],
    ],

    // Accepted aliases from settings/db/user input.
    'aliases' => [
        '1' => '1',
        '2' => '2',
        '3' => '3',
        '4' => '4',
        'app1' => '1',
        'app2' => '2',
        'app3' => '3',
        'app4' => '4',
        'classic' => '1',
        'modern' => '2',
        'minimal' => '3',
        'magazine' => '4',
    ],

    'default' => '1',
];