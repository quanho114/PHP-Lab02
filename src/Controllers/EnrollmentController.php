<?php

namespace TrainingApp\Controllers;

use TrainingApp\Support\ApiResponder;
use TrainingApp\Support\TrainingStorage;

class EnrollmentController
{
    public function create(TrainingStorage $storage, array $config): void
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $contentType = $headers['Content-Type'] ?? $headers['content-type'] ?? ($_SERVER['CONTENT_TYPE'] ?? '');

        if (!str_contains(strtolower($contentType), 'application/json')) {
            ApiResponder::json(415, [
                'error' => 'Unsupported Media Type',
                'message' => 'Content-Type must be application/json',
            ]);
        }

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);

        if (!is_array($payload)) {
            ApiResponder::json(400, [
                'error' => 'Bad Request',
                'message' => 'Invalid JSON body',
            ]);
        }

        $sessionId = (int) ($payload['session_id'] ?? 0);
        $employeeName = trim((string) ($payload['employee_name'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $seats = (int) ($payload['seats'] ?? 0);

        if ($sessionId <= 0 || $employeeName === '' || $email === '' || $seats <= 0) {
            ApiResponder::json(422, [
                'error' => 'Unprocessable Content',
                'message' => 'session_id, employee_name, email, seats are required and must be valid',
            ]);
        }

        if ($seats > $config['app']['max_seats_per_enrollment']) {
            ApiResponder::json(422, [
                'error' => 'Unprocessable Content',
                'message' => 'Requested seats exceed maximum seats per enrollment',
            ]);
        }

        $result = $storage->registerEnrollment($sessionId, $employeeName, $email, $seats);

        if (!($result['ok'] ?? false)) {
            $reason = $result['reason'] ?? 'unknown';

            if ($reason === 'session_not_found') {
                ApiResponder::json(422, [
                    'error' => 'Unprocessable Content',
                    'message' => 'Selected training session does not exist',
                ]);
            }

            if ($reason === 'not_enough_seats') {
                ApiResponder::json(422, [
                    'error' => 'Unprocessable Content',
                    'message' => 'Not enough seats available for this training session',
                ]);
            }

            if ($reason === 'duplicate_enrollment') {
                ApiResponder::json(422, [
                    'error' => 'Unprocessable Content',
                    'message' => 'This email has already enrolled in the selected session',
                ]);
            }

            ApiResponder::json(500, [
                'error' => 'Internal Server Error',
                'message' => 'Enrollment could not be processed due to a storage problem',
            ]);
        }

        $enrollment = $result['enrollment'];
        $updatedSession = $result['session'];

        ApiResponder::json(201, [
            'message' => 'Enrollment created successfully',
            'data' => [
                'enrollment' => $enrollment,
                'session' => [
                    'id' => $updatedSession['id'],
                    'topic' => $updatedSession['topic'],
                    'seats_available' => $updatedSession['seats_available'],
                ],
            ],
        ], [
            'Location' => '/enrollments/' . $enrollment['enrollment_id'],
        ]);
    }
}
