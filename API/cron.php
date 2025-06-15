<?php
    if (php_sapi_name() !== 'cli' && !defined('API_UPDATE')) {
        http_response_code(403);
        echo 'Access denied. This script can only be run from the command line.';
        exit;
    }

    printStatus('[INFO] Starting email status check...');

    require_once 'credentials.php';
    global $emails;

    $status = [];

    foreach ($emails as $email => $credentials) {
        printStatus("[INFO] Checking email: $email");

        $hostname = $credentials['host'];
        $username = $credentials['username'];
        $password = $credentials['password'];

        $inbox = imap_open($hostname, $username, $password);
        if ($inbox === false) {
            printStatus("[WARNING] Failed to connect to $email: " . imap_last_error() . "\n");
            $status[$email] = 'failed';
            continue;
        }

        $unseen = imap_search($inbox, 'UNSEEN');

        $count = 0;
        if ($unseen !== false) {
            $count = count($unseen);
        }

        imap_close($inbox);

        $status[$email] = $count;
    }

    $status['timestamp'] = date('Y-m-d H:i:s');

    $statusFile = 'status.json';
    if (file_put_contents($statusFile, json_encode($status, JSON_PRETTY_PRINT))) {
        printStatus('[INFO] Email status check completed successfully.');
    } else {
        printStatus("[ERROR] Failed to write status to $statusFile.");
    }

    function printStatus($message) {
        if (!defined('API_UPDATE')) {
            echo "\n[INFO] $message";
        }
    }
?>
