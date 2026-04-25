<?php

namespace TrainingApp\Support;

class ApiResponder
{
    public static function json(int $status, array $data = [], array $headers = []): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');

        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }

        if ($status === 204) {
            exit;
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
