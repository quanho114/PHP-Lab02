<?php

namespace TrainingApp\Support;

class TrainingStorage
{
    private string $storageDir;
    private string $sessionsFile;
    private string $enrollmentsFile;
    private string $lockFile;
    private array $seedSessions;

    public function __construct(string $storageDir, array $seedSessions)
    {
        $this->storageDir = rtrim($storageDir, '/\\');
        $this->sessionsFile = $this->storageDir . '/sessions.json';
        $this->enrollmentsFile = $this->storageDir . '/enrollments.json';
        $this->lockFile = $this->storageDir . '/storage.lock';
        $this->seedSessions = array_values($seedSessions);

        $this->initialize();
    }

    public function getSessions(): array
    {
        return $this->readJsonFile($this->sessionsFile, $this->seedSessions);
    }

    public function getEnrollments(): array
    {
        return $this->readJsonFile($this->enrollmentsFile, []);
    }

    public function registerEnrollment(int $sessionId, string $employeeName, string $email, int $seats): array
    {
        $lockHandle = fopen($this->lockFile, 'c+');
        if ($lockHandle === false) {
            return [
                'ok' => false,
                'reason' => 'storage_unavailable',
            ];
        }

        try {
            if (!flock($lockHandle, LOCK_EX)) {
                return [
                    'ok' => false,
                    'reason' => 'storage_unavailable',
                ];
            }

            $sessions = $this->readJsonFile($this->sessionsFile, $this->seedSessions);
            $enrollments = $this->readJsonFile($this->enrollmentsFile, []);

            $sessionIndex = null;
            foreach ($sessions as $index => $session) {
                if ((int) ($session['id'] ?? 0) === $sessionId) {
                    $sessionIndex = $index;
                    break;
                }
            }

            if ($sessionIndex === null) {
                return [
                    'ok' => false,
                    'reason' => 'session_not_found',
                ];
            }

            foreach ($enrollments as $enrollment) {
                $existingSessionId = (int) ($enrollment['session_id'] ?? 0);
                $existingEmail = strtolower(trim((string) ($enrollment['email'] ?? '')));
                if ($existingSessionId === $sessionId && $existingEmail === strtolower($email)) {
                    return [
                        'ok' => false,
                        'reason' => 'duplicate_enrollment',
                    ];
                }
            }

            $availableSeats = (int) ($sessions[$sessionIndex]['seats_available'] ?? 0);
            if ($availableSeats < $seats) {
                return [
                    'ok' => false,
                    'reason' => 'not_enough_seats',
                ];
            }

            $sessions[$sessionIndex]['seats_available'] = $availableSeats - $seats;

            $enrollmentId = time() . random_int(1000, 9999);
            $enrollment = [
                'enrollment_id' => $enrollmentId,
                'employee_name' => $employeeName,
                'email' => $email,
                'session_id' => $sessionId,
                'seats' => $seats,
                'created_at' => date(DATE_ATOM),
            ];

            $enrollments[] = $enrollment;

            $this->writeJsonFile($this->sessionsFile, $sessions);
            $this->writeJsonFile($this->enrollmentsFile, $enrollments);

            return [
                'ok' => true,
                'enrollment' => $enrollment,
                'session' => $sessions[$sessionIndex],
            ];
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    private function initialize(): void
    {
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0777, true);
        }

        if (!file_exists($this->sessionsFile)) {
            $this->writeJsonFile($this->sessionsFile, $this->seedSessions);
        }

        if (!file_exists($this->enrollmentsFile)) {
            $this->writeJsonFile($this->enrollmentsFile, []);
        }

        if (!file_exists($this->lockFile)) {
            file_put_contents($this->lockFile, '');
        }
    }

    private function readJsonFile(string $path, array $fallback): array
    {
        if (!file_exists($path)) {
            return $fallback;
        }

        $raw = file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            return $fallback;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : $fallback;
    }

    private function writeJsonFile(string $path, array $data): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($path, $json === false ? '[]' : $json, LOCK_EX);
    }
}