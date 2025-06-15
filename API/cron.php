<?php
// Nastavení připojení
$hostname = '{imap.example.com:993/imap/ssl}INBOX'; // Nahraď adresou svého IMAP serveru
$username = 'tvoje@email.cz';                       // Tvůj e-mail
$password = 'tvojeheslo';                           // Tvé heslo

// Připojení ke schránce
$inbox = imap_open($hostname, $username, $password) or die('Nelze se připojit: ' . imap_last_error());

// Vyhledání nepřečtených zpráv
$emails = imap_search($inbox, 'UNSEEN');

if ($emails === false) {
    echo "Žádné nepřečtené e-maily nebo chyba při čtení.";
} else {
    $pocet = count($emails);
    echo "Počet nepřečtených e-mailů: $pocet";
}

// Zavření spojení
imap_close($inbox);
?>
