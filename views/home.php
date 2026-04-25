<?php /** @var array $data */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($data['title']) ?></title>
</head>
<body>
    <h1><?= htmlspecialchars($data['title']) ?></h1>

    <ul>
        <li>APP_NAME: <?= htmlspecialchars($data['app_name']) ?></li>
        <li>TRAINING_CENTER_NAME: <?= htmlspecialchars($data['training_center_name']) ?></li>
        <li>APP_ENV: <?= htmlspecialchars($data['app_env']) ?></li>
        <li>APP_DEBUG: <?= htmlspecialchars($data['app_debug']) ?></li>
    </ul>

    <h2>Training Sessions</h2>
    <?php foreach ($data['sessions'] as $session): ?>
        <div style="margin-bottom: 16px; padding: 12px; border: 1px solid #ccc;">
            <p><strong>Topic:</strong> <?= htmlspecialchars($session['topic']) ?></p>
            <p><strong>Trainer:</strong> <?= htmlspecialchars($session['trainer']) ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($session['date']) ?></p>
            <p><strong>Seats Total:</strong> <?= htmlspecialchars((string) $session['seats_total']) ?></p>
            <p><strong>Seats Available:</strong> <?= htmlspecialchars((string) $session['seats_available']) ?></p>
            <p><strong>Status:</strong> <?= $session['seats_available'] > 0 ? 'Open' : 'Full' ?></p>
        </div>
    <?php endforeach; ?>

    <h2>API Endpoints</h2>
    <ul>
        <li>GET /sessions</li>
        <li>POST /enrollments</li>
    </ul>
</body>
</html>
