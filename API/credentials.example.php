<?php
    define('API_TOKEN', 'YOUR_API_TOKEN'); // CHANGE THIS TO YOUR RANDOMLY GENERATED TOKEN

    $emails = [
        'first@gmail.com' => [
            'type' => 'imap',
            'host' => '{imap.gmail.com:993/imap/ssl}INBOX', //https://www.php.net/manual/en/function.imap-open.php
            'username' => 'first@gmail.com',
            'password' => 'password123',
        ],
        'second@yahoo.com' => [
            'type' => 'imap',
            'host' => '{imap.mail.yahoo.com:993/imap/ssl}INBOX', //https://www.php.net/manual/en/function.imap-open.php
            'username' => 'second@yahoo.com',
            'password' => 'password42',
        ],
        'third@outlook.com' => [
            'type' => 'file',
            'url' => 'https://example.com/third_outlook_status.json', // URL to the JSON file containing the status for this email
            'status_too_old' => 3600 // after this many seconds, the status is considered and considered failed
        ],
    ];
?>
