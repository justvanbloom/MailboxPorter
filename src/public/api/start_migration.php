<?php
header('Content-Type: application/json');

$s_host = $_POST['s_host'] ?? ''; $s_user = $_POST['s_user'] ?? ''; $s_pass = $_POST['s_pass'] ?? '';
$d_host = $_POST['d_host'] ?? ''; $d_user = $_POST['d_user'] ?? ''; $d_pass = $_POST['d_pass'] ?? '';

$maxage = $_POST['opt_maxage'] ?? '';
$exclude = $_POST['opt_exclude'] ?? '';
$dryrun = isset($_POST['opt_dryrun']) && $_POST['opt_dryrun'] !== 'false';

if (empty($s_host) || empty($d_host)) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

$job_id = 'mig_' . time();
$log_file = "/tmp/" . $job_id . ".log";

$extra = "";
if (!empty($maxage)) $extra .= " --maxage " . escapeshellarg($maxage);
if (!empty($exclude)) {
    $folders = explode(',', $exclude);
    foreach($folders as $f) {
        $extra .= " --exclude " . escapeshellarg(trim($f));
    }
}
if ($dryrun) $extra .= " --dry";

$command = sprintf(
    "imapsync --host1 %s --user1 %s --password1 %s --host2 %s --user2 %s --password2 %s " .
    "--ssl1 --ssl2 --sslargs1 SSL_verify_mode=0 --sslargs2 SSL_verify_mode=0 " .
    "--nolog --tmpdir /tmp %s > %s 2>&1 &",
    escapeshellarg($s_host), escapeshellarg($s_user), escapeshellarg($s_pass),
    escapeshellarg($d_host), escapeshellarg($d_user), escapeshellarg($d_pass),
    $extra,
    escapeshellarg($log_file)
);

exec($command);

echo json_encode(['success' => true, 'job_id' => $job_id]);
