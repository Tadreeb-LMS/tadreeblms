<?php

return [
    'default_weight' => 1,
    'max_weight' => 100,

    // Centralized KPI type registry. Admin can only select these keys.
    'types' => [
        'completion' => [
            'label' => 'Completion',
            'description' => 'Measures completion progress as a percentage.',
        ],
        'score' => [
            'label' => 'Score',
            'description' => 'Measures result quality based on score outcomes.',
        ],
        'activity' => [
            'label' => 'Activity',
            'description' => 'Measures engagement/activity from platform interactions.',
        ],
        'time' => [
            'label' => 'Time',
            'description' => 'Measures time-based performance against expected duration.',
        ],
    ],
];
