<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bayesian Network Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the Bayesian network system
    | used for project completion prediction.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Python Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for Python script execution
    |
    */
    'python_executable' => env('BAYESIAN_PYTHON_EXECUTABLE', 'python3'),
    'script_timeout' => env('BAYESIAN_SCRIPT_TIMEOUT', 30), // seconds
    'max_retries' => env('BAYESIAN_MAX_RETRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for prediction caching
    |
    */
    'cache_time_minutes' => env('BAYESIAN_CACHE_TIME', 30),
    'cache_prefix' => env('BAYESIAN_CACHE_PREFIX', 'bayesian_prediction'),

    /*
    |--------------------------------------------------------------------------
    | Network Parameters
    |--------------------------------------------------------------------------
    |
    | Bayesian network configuration parameters
    |
    */
    'network' => [
        'nodes' => [
            'task_progress' => [
                'states' => ['low', 'medium', 'high'],
                'values' => [0, 1, 2]
            ],
            'team_collaboration' => [
                'states' => ['poor', 'good', 'excellent'],
                'values' => [0, 1, 2]
            ],
            'faculty_approval' => [
                'states' => ['pending', 'approved'],
                'values' => [0, 1]
            ],
            'timeline_adherence' => [
                'states' => ['behind', 'on_track', 'ahead'],
                'values' => [0, 1, 2]
            ]
        ],

        'thresholds' => [
            'task_progress' => [
                'high' => 0.8,
                'medium' => 0.4
            ],
            'team_collaboration' => [
                'excellent' => 0.7,
                'good' => 0.4
            ],
            'faculty_approval' => [
                'approved' => 0.5
            ],
            'timeline_adherence' => [
                'ahead' => 0.8,
                'on_track' => 0.6
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Risk Levels
    |--------------------------------------------------------------------------
    |
    | Risk level thresholds for project completion probability
    |
    */
    'risk_levels' => [
        'low' => 0.8,
        'medium' => 0.6,
        'high' => 0.4,
        // Below 0.4 is considered critical
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for prediction logging
    |
    */
    'logging' => [
        'enabled' => env('BAYESIAN_LOGGING_ENABLED', true),
        'log_predictions' => env('BAYESIAN_LOG_PREDICTIONS', true),
        'log_errors' => env('BAYESIAN_LOG_ERRORS', true),
        'log_performance' => env('BAYESIAN_LOG_PERFORMANCE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Settings for performance optimization
    |
    */
    'performance' => [
        'enable_async' => env('BAYESIAN_ENABLE_ASYNC', false),
        'batch_size' => env('BAYESIAN_BATCH_SIZE', 10),
        'memory_limit' => env('BAYESIAN_MEMORY_LIMIT', '256M'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Engineering
    |--------------------------------------------------------------------------
    |
    | Settings for feature calculation
    |
    */
    'features' => [
        'collaboration_window_weeks' => env('BAYESIAN_COLLABORATION_WINDOW', 2),
        'project_cycle_days' => env('BAYESIAN_PROJECT_CYCLE_DAYS', 90),
        'min_team_size' => env('BAYESIAN_MIN_TEAM_SIZE', 1),
        'max_team_size' => env('BAYESIAN_MAX_TEAM_SIZE', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Input validation configuration
    |
    */
    'validation' => [
        'strict_mode' => env('BAYESIAN_STRICT_MODE', true),
        'allow_missing_features' => env('BAYESIAN_ALLOW_MISSING_FEATURES', false),
        'default_values' => [
            'task_progress' => 0,
            'team_collaboration' => 0,
            'faculty_approval' => 0,
            'timeline_adherence' => 0,
        ]
    ]
];
