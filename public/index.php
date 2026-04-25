<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use TrainingApp\Controllers\DashboardController;
use TrainingApp\Controllers\EnrollmentController;
use TrainingApp\Controllers\SessionController;
use TrainingApp\Support\ApiResponder;
use TrainingApp\Support\AppEnv;
use TrainingApp\Support\TrainingStorage;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();
$dotenv->required([
    'APP_NAME',
    'APP_ENV',
    'APP_DEBUG',
    'APP_URL',
    'TRAINING_CENTER_NAME',
    'MAX_SEATS_PER_ENROLLMENT',
]);
$dotenv->required('APP_DEBUG')->isBoolean();
$dotenv->required('MAX_SEATS_PER_ENROLLMENT')->isInteger();

error_reporting(E_ALL);

if (AppEnv::get('APP_ENV', 'prod') === 'dev' && AppEnv::bool('APP_DEBUG', false)) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('log_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', dirname(__DIR__) . '/storage/logs/php-error.log');
}

$config = require dirname(__DIR__) . '/config/app.php';
$seedSessions = require dirname(__DIR__) . '/src/Data/training_sessions.php';
$storage = new TrainingStorage(dirname(__DIR__) . '/storage', $seedSessions);
$sessions = $storage->getSessions();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($path === '/' && $method === 'GET') {
    $dashboardController = new DashboardController();
    $data = $dashboardController->index($config, $sessions);
    require dirname(__DIR__) . '/views/home.php';
    exit;
}

if ($path === '/sessions' && $method === 'GET') {
    (new SessionController())->list($storage->getSessions());
}

if ($path === '/sessions' && $method !== 'GET') {
    ApiResponder::json(405, [
        'error' => 'Method Not Allowed',
    ], [
        'Allow' => 'GET',
    ]);
}

if ($path === '/enrollments' && $method === 'POST') {
    (new EnrollmentController())->create($storage, $config);
}

if ($path === '/enrollments' && $method !== 'POST') {
    ApiResponder::json(405, [
        'error' => 'Method Not Allowed',
    ], [
        'Allow' => 'POST',
    ]);
}

ApiResponder::json(404, [
    'error' => 'Not Found',
]);
