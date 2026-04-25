<?php

namespace TrainingApp\Controllers;

use TrainingApp\Support\ApiResponder;

class SessionController
{
    public function list(array $sessions): void
    {
        ApiResponder::json(200, [
            'message' => 'Training session list',
            'data' => $sessions,
        ]);
    }
}
