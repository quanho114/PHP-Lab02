<?php

namespace TrainingApp\Controllers;

class DashboardController
{
    public function index(array $config, array $sessions): array
    {
        return [
            'title' => 'Mini Training Session Registration App',
            'app_name' => $config['app']['name'],
            'training_center_name' => $config['app']['training_center_name'],
            'app_env' => $config['app']['env'],
            'app_debug' => $config['app']['debug'] ? 'true' : 'false',
            'sessions' => $sessions,
        ];
    }
}
