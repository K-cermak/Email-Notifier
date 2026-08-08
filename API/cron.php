<?php
    error_reporting(0);
    ini_set('display_errors', '0');

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

        $type = $credentials['type'];
        $count = 0;
        if ($type == "imap") {
            $hostname = $credentials['host'];
            $username = $credentials['username'];
            $password = $credentials['password'];

            $count = checkImap($hostname, $username, $password);

        } else if ($type == "file") {
            $url = $credentials['url'];
            $status_too_old = $credentials['status_too_old'];

            $count = checkFile($url, $status_too_old);

        } else {
            printStatus("[ERROR] Unknown type '$type' for email: $email");
            $status[$email] = 'failed';
            continue;
        }

        printStatus("[INFO] Retrieved count from file for $email: $count");
        $status[$email] = $count;
    }

    $status['timestamp'] = date('Y-m-d H:i:s');

    $statusFile = 'status.json';
    if (file_put_contents($statusFile, json_encode($status, JSON_PRETTY_PRINT))) {
        printStatus("[INFO] Email status check completed successfully.\n");
    } else {
        printStatus("[ERROR] Failed to write status to $statusFile.\n");
    }

    function printStatus($message) {
        if (!defined('API_UPDATE')) {
            echo "\n$message";
        }
    }

    function checkImap($hostname, $username, $password) {
        $inbox = imap_open($hostname, $username, $password);
        if ($inbox === false) {
            printStatus("[ERROR] Failed to connect to IMAP server for $username: " . imap_last_error());
            return "failed";
        }

        $unseen = imap_search($inbox, 'UNSEEN');
        $count = 0;
        if ($unseen !== false) {
            $count = count($unseen);
        }

        imap_close($inbox);
        return $count;
    }

    function checkFile($url, $status_too_old) {
        $url .= (str_contains($url, '?') ? '&' : '?') . 't=' . time(); // Prevent caching

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_USERAGENT      => 'Email Status Checker/1.0',
        ]);

        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($body === false || $status !== 200) {
            return 'failed';
        }

        if (str_starts_with(ltrim($body), '<')) {
            return 'failed';
        }

        $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);
        $data = json_decode($body, true);

        if (!is_array($data) || !isset($data['unread'], $data['ts'])) {
            return 'failed';
        }

        try {
            $timestamp = new DateTimeImmutable($data['ts']);
        } catch (Exception $e) {
            return 'failed';
        }

        if (time() - $timestamp->getTimestamp() > $status_too_old) {
            return 'failed';
        }

        return (int) $data['unread'];
    }
?>
