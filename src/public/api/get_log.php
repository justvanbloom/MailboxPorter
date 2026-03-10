<?php
header('Content-Type: application/json');
$job_id = $_GET['job_id'] ?? '';
$log_file = "/tmp/" . $job_id . ".log";

if ($job_id && file_exists($log_file)) {
    echo json_encode([
        'success' => true,
        'content' => file_get_contents($log_file)
    ]);
} else {
    echo json_encode(['success' => false, 'content' => 'Waiting for data from the server...']);
}
