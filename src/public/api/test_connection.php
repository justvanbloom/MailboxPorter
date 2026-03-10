<?php
header('Content-Type: application/json');

$type = $_POST['test_type'] ?? 'source';
$host = ($type === 'source') ? ($_POST['s_host'] ?? '') : ($_POST['d_host'] ?? '');
$user = ($type === 'source') ? ($_POST['s_user'] ?? '') : ($_POST['d_user'] ?? '');
$pass = ($type === 'source') ? ($_POST['s_pass'] ?? '') : ($_POST['d_pass'] ?? '');

if (empty($host) || empty($user) || empty($pass)) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

/**
 * Comando basato sul tuo log di successo/errore.
 * --password1: specifica la password per host1[cite: 51].
 * --justlogin: effettua solo il login e termina[cite: 250].
 */
$command = sprintf(
    "imapsync --host1 %s --user1 %s --password1 %s --ssl1 --sslargs1 SSL_verify_mode=0 --justlogin --nolog --tmpdir /tmp 2>&1",
    escapeshellarg($host),
    escapeshellarg($user),
    escapeshellarg($pass)
);

exec($command, $output, $return_var);
$full_output = implode("\n", $output);



// Controlliamo i codici di errore specifici del manuale
$is_auth_error = ($return_var === 16 || $return_var === 161 || $return_var === 162); // Errori AUTH
$is_conn_error = ($return_var === 10 || $return_var === 101 || $return_var === 102); // Errori CONN [cite: 287, 288]

// Verifica stringa di successo nell'output (deve esserci il banner e NON il fallimento)
$success_in_log = strpos($full_output, 'Host1 banner:') !== false && strpos($full_output, 'Host1 failure:') === false;

if ($return_var === 0 && $success_in_log) {
    echo json_encode(['success' => true, 'message' => 'Connection Successful!']);
} else {
    $error_msg = 'Errore di connessione';

    if ($is_auth_error || strpos($full_output, 'AUTHENTICATIONFAILED') !== false) {
        $error_msg = 'Authentication failed: Incorrect password or user.';
    } elseif ($is_conn_error) {
        $error_msg = 'Unable to reach IMAP server.';
    }

    echo json_encode([
        'success' => false,
        'message' => $error_msg,
        'debug' => $full_output
    ]);
}
