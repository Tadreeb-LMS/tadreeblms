<?php

return [
    'default_weight' => 1,
    'max_weight' => 100,

    'snapshots' => [
        // Increase this when KPI computation logic changes and all snapshots must refresh.
        'version' => 1,
        // Safety net for data sources without explicit invalidation signals.
        'max_age_minutes' => 5,
    ],

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
