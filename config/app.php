<?php

use TrainingApp\Support\AppEnv;

return [
    'app' => [
        'name' => AppEnv::get('APP_NAME', 'Training App'),
        'env' => AppEnv::get('APP_ENV', 'prod'),
        'debug' => AppEnv::bool('APP_DEBUG', false),
        'url' => AppEnv::get('APP_URL', 'http://localhost:8000'),
        'training_center_name' => AppEnv::get('TRAINING_CENTER_NAME', 'Training Center'),
        'max_seats_per_enrollment' => AppEnv::int('MAX_SEATS_PER_ENROLLMENT', 1),
    ],
];
