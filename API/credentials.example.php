<?php
    define('API_TOKEN', 'YOUR_API_TOKEN'); // CHANGE THIS TO YOUR RANDOMLY GENERATED TOKEN

    $emails = [
        'first@gmail.com' => [
            "host" => '{imap.gmail.com:993/imap/ssl}INBOX', //https://www.php.net/manual/en/function.imap-open.php
            "username" => 'first@gmail.com',
            "password" => 'password123',
        ],
        'second@yahoo.com' => [
            "host" => '{imap.mail.yahoo.com:993/imap/ssl}INBOX', //https://www.php.net/manual/en/function.imap-open.php
            "username" => 'second@yahoo.com',
            "password" => 'password42',
        ],
    ];
?>