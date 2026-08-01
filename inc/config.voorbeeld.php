<?php
/**
 * Voorbeeldconfiguratie.
 *
 * Kopieer dit bestand naar inc/config.php en vul je eigen gegevens in. Op een
 * webhost hoeft dat niet: de installer op /install/ schrijft config.php voor je.
 * Dit bestand is bedoeld om lokaal snel te kunnen beginnen.
 *
 * inc/config.php staat in .gitignore, want er staat een databasewachtwoord in.
 *
 * Alle sleutels staan uitgelegd in docs/ONTWIKKELEN.md.
 */

return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'bv_dev',
        'user' => 'root',
        'pass' => '',
    ],

    'site' => [
        // Zonder afsluitende slash. Alle links worden hierop gebouwd, dus een
        // fout adres hier betekent dat elke link ernaast zit.
        'url'            => 'http://127.0.0.1:8149',
        'name'           => 'Black Vendetta',
        'mail_from'      => 'noreply@example.com',
        'mail_from_name' => 'Black Vendetta',
        'mail_admin'     => 'admin@example.com',
        'timezone'       => 'Europe/Amsterdam',
    ],

    'game' => [
        // Steden toevoegen of hernoemen kan niet alleen hier; zie docs/BEHEER.md.
        'cities' => [
            'Brussel', 'Leuven', 'Gent', 'Brugge',
            'Hasselt', 'Antwerpen', 'Amsterdam', 'Enschede',
        ],
        'start_money' => 1000,

        // Lokaal uit: anders wacht je op een e-mail die nergens aankomt.
        'require_activation' => false,

        // Lokaal aan, zodat je meerdere testaccounts kunt maken.
        // Op een echte site hoort dit uit te staan.
        'allow_multi_accounts' => true,
    ],

    // 'request' laat de cron meeliften op paginabezoek: geen cronjob nodig.
    // Heeft je host wel een cronjob, zet dit dan op 'cron'.
    'cron_mode' => 'request',
    'cron_key'  => 'verzin-hier-iets-lastigs',

    // Adressen zonder .php. Vereist mod_rewrite; standaard uit.
    'mooie_urls' => false,

    // Toont databasefouten op het scherm. NOOIT aan op een live site.
    'debug' => true,
];
