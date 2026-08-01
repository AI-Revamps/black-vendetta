<?php
/**
 * Black Vendetta - instellingen
 *
 * Kopieer dit bestand naar `config.php` en vul je gegevens in.
 * De installer (/install/) kan dit ook voor je doen.
 *
 * LET OP: config.php bevat je databasewachtwoord. Het staat in /inc/,
 * die map wordt door .htaccess afgeschermd. Zet config.php nooit in git.
 */

return [

    // --- Database -------------------------------------------------------
    // Op de meeste shared webhosts (cPanel/DirectAdmin) is de host 'localhost'
    // en beginnen db-naam en gebruiker met je accountnaam, bv. 'jansen_vendetta'.
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'vendetta',
        'user' => 'vendetta',
        'pass' => '',
    ],

    // --- Site -----------------------------------------------------------
    'site' => [
        // Volledige URL naar de map waarin het spel staat, ZONDER slash op het eind.
        // Bijvoorbeeld: https://www.jouwdomein.nl  of  https://jouwdomein.nl/game
        'url'   => 'https://www.jouwdomein.nl',

        // Naam van het spel. Wordt gebruikt in titels en e-mails.
        'name'  => 'Black Vendetta',

        // Afzender van systeemmails (activatie, wachtwoord vergeten).
        // Gebruik een adres op je eigen domein, anders komt de mail in de spam.
        'mail_from'      => 'noreply@jouwdomein.nl',
        'mail_from_name' => 'Black Vendetta',

        // Waar foutmeldingen en misbruikmeldingen heen gaan.
        'mail_admin'     => 'admin@jouwdomein.nl',

        // Tijdzone waarin het spel draait.
        'timezone' => 'Europe/Amsterdam',
    ],

    // --- Spelinstellingen ------------------------------------------------
    'game' => [
        // Steden waarin gespeeld wordt. Moeten overeenkomen met de `stad` tabel.
        'cities' => [
            'Brussel', 'Leuven', 'Gent', 'Brugge',
            'Hasselt', 'Antwerpen', 'Amsterdam', 'Enschede',
        ],

        // Startkapitaal van een nieuwe speler.
        'start_money' => 1000,

        // Moet een nieuw account per e-mail geactiveerd worden?
        // Zet op false als mail() op je host niet werkt.
        'require_activation' => true,

        // Mogen meerdere accounts vanaf hetzelfde IP-adres registreren?
        //
        // Op false geldt: één account per IP-adres en één per e-mailadres,
        // ook als dat account omgelegd is. Dat kan, omdat je na een moord
        // geen nieuw account nodig hebt: hetzelfde account begint opnieuw.
        // Voor huisgenoten op één aansluiting kun je per IP-adres uitzondering
        // geven via de beheerpagina "Multi-accounts".
        'allow_multi_accounts' => false,
    ],

    // --- Cron -------------------------------------------------------------
    // Het spel heeft periodieke taken (kogelvoorraad, prijzen, respectpunten).
    //
    //  'request' = de taken draaien mee op gewone paginabezoeken. Werkt altijd,
    //              ook zonder cronjob-ondersteuning. Aanbevolen voor shared hosting.
    //  'cron'    = alleen via een echte cronjob. Zet dan bij je host een taak op:
    //              elke minuut  ->  php /home/JOUW_ACCOUNT/public_html/cron.php
    //  'both'    = allebei.
    'cron_mode' => 'request',

    // Geheime sleutel voor cron.php via de browser (https://site/cron.php?key=...).
    // Verander dit in een willekeurige reeks tekens.
    'cron_key' => 'verander-dit-in-iets-willekeurigs',

    // --- Adressen zonder .php -----------------------------------------------
    // Op true worden alle links /home in plaats van /home.php.
    //
    // Dit heeft mod_rewrite nodig; de regels staan al klaar in .htaccess.
    // Controleer eerst of https://jouwdomein.nl/home werkt en zet hem daarna
    // pas op true. Werkt het niet en staat dit toch aan, dan leidt elke link
    // naar een 404 — zet hem dan via FTP weer op false.
    //
    // De oude adressen met .php blijven het altijd doen, dus bestaande links
    // en bladwijzers van spelers blijven werken.
    'mooie_urls' => false,

    // --- Beveiligingscode ---------------------------------------------------
    // 'plaatje' = een plaatje met vijf tekens (heeft de gd-uitbreiding nodig)
    // 'tekst'   = een eenvoudige rekensom
    //
    // Zonder gd wordt automatisch de rekensom gebruikt. Zet dit op 'tekst' als
    // gd wel aanwezig is maar het plaatje leeg of onleesbaar blijft; dat komt
    // voor op hosts zonder bruikbaar lettertype.
    'captcha' => 'plaatje',

    // --- Ontwikkeling -----------------------------------------------------
    // In productie ALTIJD op false: anders zien bezoekers PHP-foutmeldingen
    // inclusief database-details.
    'debug' => false,
];
