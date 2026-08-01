<?php
/**
 * Installatie van Black Vendetta.
 *
 * Bedoeld voor een gewone shared webhost: upload alles per FTP, maak in het
 * configuratiescherm van je host een lege MySQL-database aan en open daarna
 * index.php in deze map in je browser.
 *
 * Ga niet rechtstreeks naar dit bestand: index.php controleert eerst of de
 * PHP-versie nieuw genoeg is. Dit bestand gebruikt schrijfwijzen die een oudere
 * PHP niet eens kan lezen, en dan krijg je een 500 zonder uitleg.
 *
 * De installer draait bewust helemaal op zichzelf: hij gebruikt inc/bootstrap.php
 * niet, want die heeft juist de config nodig die hier nog gemaakt moet worden.
 *
 * Verwijder deze map zodra de installatie klaar is.
 */

declare(strict_types=1);

if (!defined('BV_INSTALLER')) {
    header('Location: index.php');
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', '1');
mb_internal_encoding('UTF-8');

const WORTEL      = __DIR__ . '/..';
const CONFIG_PAD  = WORTEL . '/inc/config.php';
// Het schema staat in deze map en niet in de hoofdmap, zodat het samen met de
// installer verdwijnt. In de oude versie stond `sql.sql` in de webroot en was
// de volledige databasestructuur voor iedereen op te vragen.
const SCHEMA_PAD  = __DIR__ . '/schema.sql';
const SLOT_PAD    = __DIR__ . '/.geinstalleerd';

session_start();

// --- Al geïnstalleerd? --------------------------------------------------
if (is_file(SLOT_PAD)) {
    toon_pagina('Al geïnstalleerd', <<<HTML
        <p>Deze installatie is al uitgevoerd.</p>
        <p><strong>Verwijder de map <code>install/</code> van je server.</strong>
        Zolang die er staat, kan iemand anders proberen je installatie te overschrijven.</p>
        <p>Wil je opnieuw installeren, verwijder dan het bestand
        <code>install/.geinstalleerd</code> én <code>inc/config.php</code>.</p>
        <p><a class="knop" href="../index.php">Naar het spel</a></p>
        HTML);
}

// --- Stappen --------------------------------------------------------------
$stap  = $_GET['stap'] ?? 'controle';
$fout  = null;

if ($stap === 'installeren' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    try {
        installeren($_POST);
        $stap = 'klaar';
    } catch (Throwable $e) {
        $fout = $e->getMessage();
        $stap = 'formulier';
    }
}

match ($stap) {
    'formulier' => toon_formulier($fout),
    'klaar'     => toon_klaar(),
    default     => toon_controle(),
};

// ==========================================================================
// Stap 1: voldoet de server?
// ==========================================================================

function eisen(): array
{
    return [
        [
            'naam' => 'PHP 8.1 of nieuwer',
            'ok'   => PHP_VERSION_ID >= 80100,
            'nu'   => PHP_VERSION,
            'hulp' => 'Kies een nieuwere PHP-versie in het configuratiescherm van je webhost '
                    . '(vaak onder "PHP-versie" of "MultiPHP Manager").',
        ],
        [
            'naam' => 'Database-uitbreiding pdo_mysql',
            'ok'   => extension_loaded('pdo_mysql'),
            'nu'   => extension_loaded('pdo_mysql') ? 'aanwezig' : 'ontbreekt',
            'hulp' => 'Zet de uitbreiding "pdo_mysql" aan bij je host.',
        ],
        [
            'naam' => 'Tekstverwerking mbstring',
            'ok'   => extension_loaded('mbstring'),
            'nu'   => extension_loaded('mbstring') ? 'aanwezig' : 'ontbreekt',
            'hulp' => 'Zet de uitbreiding "mbstring" aan bij je host.',
        ],
        [
            'naam' => 'Afbeeldingen gd (voor de code-plaatjes)',
            'ok'   => extension_loaded('gd'),
            'nu'   => extension_loaded('gd') ? 'aanwezig' : 'ontbreekt',
            'hulp' => 'Zonder gd werkt de beveiligingscode bij misdaden niet. '
                    . 'Zet "gd" aan bij je host.',
        ],
        [
            'naam' => 'Map inc/ is beschrijfbaar',
            'ok'   => is_writable(WORTEL . '/inc'),
            'nu'   => is_writable(WORTEL . '/inc') ? 'ja' : 'nee',
            'hulp' => 'Zet via je FTP-programma de rechten van de map <code>inc</code> op 755 '
                    . '(of tijdelijk 775). Lukt dat niet, dan kun je hieronder het configuratiebestand '
                    . 'ook handmatig aanmaken.',
        ],
        [
            'naam' => 'schema.sql aanwezig',
            'ok'   => is_readable(SCHEMA_PAD),
            'nu'   => is_readable(SCHEMA_PAD) ? 'ja' : 'nee',
            'hulp' => 'Upload <code>schema.sql</code> naar de map <code>install/</code>.',
        ],
    ];
}

function toon_controle(): void
{
    $eisen   = eisen();
    $blokkers = array_filter($eisen, static fn ($e) => !$e['ok'] && $e['naam'] !== 'Afbeeldingen gd (voor de code-plaatjes)');
    $kanDoor  = $blokkers === [];

    $rijen = '';
    foreach ($eisen as $eis) {
        $vlag  = $eis['ok'] ? '<span class="ja">OK</span>' : '<span class="nee">Fout</span>';
        $hulp  = $eis['ok'] ? '' : '<div class="hulp">' . $eis['hulp'] . '</div>';
        $rijen .= '<tr><td>' . h($eis['naam']) . $hulp . '</td>'
                . '<td class="mid">' . h($eis['nu']) . '</td>'
                . '<td class="mid">' . $vlag . '</td></tr>';
    }

    $knop = $kanDoor
        ? '<p><a class="knop primair" href="?stap=formulier">Verder met instellen</a></p>'
        : '<p class="melding fout">Los eerst de punten hierboven op en <a href="?stap=controle">controleer opnieuw</a>.</p>';

    toon_pagina('Stap 1 — Servercontrole', <<<HTML
        <p>Eerst kijken we of je webhost aan de eisen voldoet.</p>
        <table class="controle">
          <thead><tr><th>Onderdeel</th><th class="mid">Gevonden</th><th class="mid">Status</th></tr></thead>
          <tbody>{$rijen}</tbody>
        </table>
        {$knop}
        HTML);
}

// ==========================================================================
// Stap 2: gegevens invullen
// ==========================================================================

function toon_formulier(?string $fout): void
{
    $melding = $fout !== null
        ? '<div class="melding fout">' . h($fout) . '</div>'
        : '';

    // Ingevulde waarden bewaren zodat je bij een fout niet opnieuw hoeft te typen.
    $v = static fn (string $k, string $std = ''): string => h((string) ($_POST[$k] ?? $std));

    $gokUrl = h(gok_site_url());
    $sleutel = h(bin2hex(random_bytes(16)));

    toon_pagina('Stap 2 — Gegevens', <<<HTML
        {$melding}
        <form method="post" action="?stap=installeren">

        <h2>Database</h2>
        <p class="uitleg">Deze gegevens krijg je van je webhost als je een MySQL-database aanmaakt.
        Op cPanel en DirectAdmin beginnen de namen meestal met je accountnaam,
        bijvoorbeeld <code>jansen_vendetta</code>. De database moet al bestaan en mag leeg zijn.</p>

        <div class="raster">
          <label for="db_host">Databaseserver</label>
          <input id="db_host" name="db_host" value="{$v('db_host', 'localhost')}" required>

          <label for="db_name">Databasenaam</label>
          <input id="db_name" name="db_name" value="{$v('db_name')}" required>

          <label for="db_user">Gebruikersnaam</label>
          <input id="db_user" name="db_user" value="{$v('db_user')}" required>

          <label for="db_pass">Wachtwoord</label>
          <input id="db_pass" name="db_pass" type="password" value="{$v('db_pass')}">
        </div>

        <h2>Website</h2>
        <div class="raster">
          <label for="site_url">Adres van het spel</label>
          <input id="site_url" name="site_url" value="{$v('site_url', $gokUrl)}" required>
          <span></span>
          <small>Zonder schuine streep aan het eind. Staat het spel in een submap, neem die dan mee.</small>

          <label for="site_name">Naam van het spel</label>
          <input id="site_name" name="site_name" value="{$v('site_name', 'Black Vendetta')}" required>

          <label for="mail_from">Afzender systeemmail</label>
          <input id="mail_from" name="mail_from" type="email" value="{$v('mail_from')}" required>
          <span></span>
          <small>Gebruik een adres op je eigen domein, anders komen activatiemails in de spam.</small>
        </div>

        <h2>Beheerdersaccount</h2>
        <p class="uitleg">Dit account krijgt alle rechten en kan meteen inloggen.</p>

        <div class="raster">
          <label for="adm_login">Gebruikersnaam</label>
          <input id="adm_login" name="adm_login" maxlength="16" pattern="[A-Za-z0-9_\-]{3,16}"
                 value="{$v('adm_login')}" required>
          <span></span>
          <small>3 tot 16 tekens: letters, cijfers, streepje of liggend streepje.</small>

          <label for="adm_email">E-mailadres</label>
          <input id="adm_email" name="adm_email" type="email" value="{$v('adm_email')}" required>

          <label for="adm_pass">Wachtwoord</label>
          <input id="adm_pass" name="adm_pass" type="password" minlength="10" required autocomplete="new-password">
          <span></span>
          <small>Minimaal 10 tekens.</small>

          <label for="adm_pass2">Herhaal wachtwoord</label>
          <input id="adm_pass2" name="adm_pass2" type="password" minlength="10" required autocomplete="new-password">
        </div>

        <input type="hidden" name="cron_key" value="{$sleutel}">

        <p><button type="submit" class="knop primair">Installeren</button></p>
        </form>
        HTML);
}

// ==========================================================================
// Stap 3: uitvoeren
// ==========================================================================

/** @throws RuntimeException bij ongeldige invoer of een mislukte stap. */
function installeren(array $in): void
{
    // --- Invoer controleren ---
    $dbHost = trim((string) ($in['db_host'] ?? ''));
    $dbName = trim((string) ($in['db_name'] ?? ''));
    $dbUser = trim((string) ($in['db_user'] ?? ''));
    $dbPass = (string) ($in['db_pass'] ?? '');

    $siteUrl  = rtrim(trim((string) ($in['site_url'] ?? '')), '/');
    $siteNaam = trim((string) ($in['site_name'] ?? 'Black Vendetta'));
    $mailFrom = trim((string) ($in['mail_from'] ?? ''));

    $admLogin = trim((string) ($in['adm_login'] ?? ''));
    $admEmail = trim((string) ($in['adm_email'] ?? ''));
    $admPass  = (string) ($in['adm_pass'] ?? '');
    $admPass2 = (string) ($in['adm_pass2'] ?? '');

    if ($dbHost === '' || $dbName === '' || $dbUser === '') {
        throw new RuntimeException('Vul de databaseserver, -naam en -gebruiker in.');
    }
    if (!preg_match('#^https?://[^\s/]+#i', $siteUrl)) {
        throw new RuntimeException('Het adres van het spel moet met http:// of https:// beginnen.');
    }
    if (!filter_var($mailFrom, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Het afzenderadres is geen geldig e-mailadres.');
    }
    if (!preg_match('/^[A-Za-z0-9_\-]{3,16}$/', $admLogin)) {
        throw new RuntimeException('De gebruikersnaam mag alleen letters, cijfers, - en _ bevatten (3 tot 16 tekens).');
    }
    if (!filter_var($admEmail, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Het e-mailadres van de beheerder is ongeldig.');
    }
    if (mb_strlen($admPass) < 10) {
        throw new RuntimeException('Het beheerderswachtwoord moet minstens 10 tekens lang zijn.');
    }
    if ($admPass !== $admPass2) {
        throw new RuntimeException('De twee wachtwoorden zijn niet gelijk.');
    }

    // --- Verbinden ---
    try {
        $pdo = new PDO(
            "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
            $dbUser,
            $dbPass,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    } catch (PDOException $e) {
        throw new RuntimeException(
            'Verbinden met de database lukte niet: ' . $e->getMessage() .
            ' — controleer de servernaam, databasenaam, gebruiker en wachtwoord bij je host.'
        );
    }

    // --- Schema inlezen ---
    $sql = file_get_contents(SCHEMA_PAD);
    if ($sql === false) {
        throw new RuntimeException('schema.sql kon niet gelezen worden.');
    }

    foreach (split_sql($sql) as $stelling) {
        try {
            $pdo->exec($stelling);
        } catch (PDOException $e) {
            throw new RuntimeException(
                'Fout bij het aanmaken van de database: ' . $e->getMessage() .
                ' — je kunt schema.sql ook handmatig importeren via phpMyAdmin.'
            );
        }
    }

    // --- Beheerder aanmaken ---
    $bestaat = $pdo->prepare('SELECT COUNT(*) FROM `users` WHERE `login` = ?');
    $bestaat->execute([$admLogin]);

    if ((int) $bestaat->fetchColumn() > 0) {
        throw new RuntimeException("Er bestaat al een speler met de naam '{$admLogin}'. Kies een andere naam.");
    }

    $stad = 'Brussel';
    $pdo->prepare(
        'INSERT INTO `users`
              (`login`, `pass`, `email`, `ip`, `activated`, `level`, `status`,
               `start`, `online`, `stad`, `zak`)
         VALUES (?, ?, ?, ?, 1, ?, \'levend\', NOW(), NOW(), ?, ?)'
    )->execute([
        $admLogin,
        password_hash($admPass, PASSWORD_DEFAULT),
        $admEmail,
        $_SERVER['REMOTE_ADDR'] ?? '',
        1000,           // LEVEL_OWNER
        $stad,
        1000,
    ]);

    // --- Config wegschrijven ---
    $config = bouw_config([
        'db_host'   => $dbHost,
        'db_name'   => $dbName,
        'db_user'   => $dbUser,
        'db_pass'   => $dbPass,
        'site_url'  => $siteUrl,
        'site_name' => $siteNaam,
        'mail_from' => $mailFrom,
        'adm_email' => $admEmail,
        'cron_key'  => (string) ($in['cron_key'] ?? bin2hex(random_bytes(16))),
    ]);

    if (@file_put_contents(CONFIG_PAD, $config, LOCK_EX) === false) {
        // Niet fataal: de gebruiker kan het bestand zelf plaatsen.
        $_SESSION['config_handmatig'] = $config;
    }

    // --- Installatie op slot ---
    @file_put_contents(SLOT_PAD, date('c') . "\n");

    $_SESSION['adm_login'] = $admLogin;
}

/**
 * Splits een SQL-bestand in losse stellingen.
 * Houdt rekening met tekst tussen aanhalingstekens en met -- en # commentaar,
 * zodat een puntkomma binnen een waarde niet per ongeluk splitst.
 */
function split_sql(string $sql): array
{
    $stellingen = [];
    $huidig     = '';
    $inString   = null;   // ' of " als we in een tekstwaarde zitten
    $lengte     = strlen($sql);

    for ($i = 0; $i < $lengte; $i++) {
        $teken = $sql[$i];

        if ($inString !== null) {
            $huidig .= $teken;
            if ($teken === '\\' && $i + 1 < $lengte) {
                $huidig .= $sql[++$i];        // ontsnapt teken hoort erbij
            } elseif ($teken === $inString) {
                $inString = null;
            }
            continue;
        }

        // Commentaarregel: overslaan tot het einde van de regel.
        if ($teken === '-' && ($sql[$i + 1] ?? '') === '-' || $teken === '#') {
            while ($i < $lengte && $sql[$i] !== "\n") { $i++; }
            $huidig .= "\n";
            continue;
        }

        if ($teken === "'" || $teken === '"') {
            $inString = $teken;
            $huidig  .= $teken;
            continue;
        }

        if ($teken === ';') {
            if (trim($huidig) !== '') { $stellingen[] = trim($huidig); }
            $huidig = '';
            continue;
        }

        $huidig .= $teken;
    }

    if (trim($huidig) !== '') {
        $stellingen[] = trim($huidig);
    }
    return $stellingen;
}

/** Bouw de inhoud van inc/config.php. */
function bouw_config(array $w): string
{
    $q = static fn (string $s): string => var_export($s, true);

    return "<?php\n"
        . "/**\n"
        . " * Black Vendetta - instellingen\n"
        . " * Aangemaakt door de installer op " . date('d-m-Y H:i') . ".\n"
        . " *\n"
        . " * Je mag dit bestand met de hand aanpassen. Zie inc/config.sample.php\n"
        . " * voor uitleg bij elke instelling.\n"
        . " */\n\n"
        . "return [\n"
        . "    'db' => [\n"
        . "        'host' => {$q($w['db_host'])},\n"
        . "        'port' => 3306,\n"
        . "        'name' => {$q($w['db_name'])},\n"
        . "        'user' => {$q($w['db_user'])},\n"
        . "        'pass' => {$q($w['db_pass'])},\n"
        . "    ],\n\n"
        . "    'site' => [\n"
        . "        'url'            => {$q($w['site_url'])},\n"
        . "        'name'           => {$q($w['site_name'])},\n"
        . "        'mail_from'      => {$q($w['mail_from'])},\n"
        . "        'mail_from_name' => {$q($w['site_name'])},\n"
        . "        'mail_admin'     => {$q($w['adm_email'])},\n"
        . "        'timezone'       => 'Europe/Amsterdam',\n"
        . "    ],\n\n"
        . "    'game' => [\n"
        . "        'cities' => ['Brussel', 'Leuven', 'Gent', 'Brugge',\n"
        . "                     'Hasselt', 'Antwerpen', 'Amsterdam', 'Enschede'],\n"
        . "        'start_money'          => 1000,\n"
        . "        'require_activation'   => true,\n"
        . "        'allow_multi_accounts' => false,\n"
        . "    ],\n\n"
        . "    'cron_mode' => 'request',\n"
        . "    'cron_key'  => {$q($w['cron_key'])},\n\n"
        . "    'debug' => false,\n"
        . "];\n";
}

// ==========================================================================
// Stap 4: klaar
// ==========================================================================

function toon_klaar(): void
{
    $login     = h((string) ($_SESSION['adm_login'] ?? ''));
    $handmatig = $_SESSION['config_handmatig'] ?? null;
    unset($_SESSION['config_handmatig'], $_SESSION['adm_login']);

    $configBlok = '';
    if ($handmatig !== null) {
        $inhoud = h($handmatig);
        $configBlok = <<<HTML
            <div class="melding fout">
              <p><strong>Let op: <code>inc/config.php</code> kon niet geschreven worden.</strong>
              De map <code>inc</code> is niet beschrijfbaar. De database is wél aangemaakt.</p>
              <p>Maak zelf een bestand <code>inc/config.php</code> met precies deze inhoud
              en upload het per FTP:</p>
              <textarea readonly rows="14" onclick="this.select()">{$inhoud}</textarea>
            </div>
            HTML;
    }

    toon_pagina('Klaar', <<<HTML
        <div class="melding ok"><strong>De installatie is voltooid.</strong>
        De database is aangemaakt en het beheerdersaccount <code>{$login}</code> kan inloggen.</div>

        {$configBlok}

        <h2>Nog twee dingen</h2>
        <ol>
          <li><strong>Verwijder de map <code>install/</code></strong> van je server.
              Dit is de belangrijkste stap: zolang de map er staat, kan iemand
              proberen je installatie te overschrijven.</li>
          <li><em>Optioneel:</em> zet bij je webhost een cronjob die elke minuut
              <code>php ~/public_html/cron.php</code> draait, en zet daarna
              <code>'cron_mode'</code> in <code>inc/config.php</code> op <code>'cron'</code>.
              Doe je dit niet, dan draaien de speltaken gewoon mee op paginabezoeken.</li>
        </ol>

        <p><a class="knop primair" href="../login.php">Inloggen op het spel</a></p>
        HTML);
}

// ==========================================================================
// Presentatie
// ==========================================================================

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Raad de URL waarop het spel draait, op basis van dit verzoek. */
function gok_site_url(): string
{
    $https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || ($_SERVER['SERVER_PORT'] ?? '') === '443';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $pad    = dirname((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH)); // .../install
    $pad    = rtrim(str_replace('\\', '/', dirname($pad)), '/');                          // een niveau omhoog

    return ($https ? 'https://' : 'http://') . $host . $pad;
}

/**
 * @return never
 */
function toon_pagina(string $titel, string $inhoud): void
{
    $t = h($titel);
    header('Content-Type: text/html; charset=utf-8');
    echo <<<HTML
    <!doctype html>
    <html lang="nl">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$t} - Installatie</title>
    <style>
      :root { --accent:#ff9900; --rand:#383838; --paneel:#1a1a1a; }
      * { box-sizing:border-box; }
      body { margin:0; padding:2rem 1rem; background:#0d0d0d; color:#d8d8d8;
             font:15px/1.6 "Segoe UI",Roboto,system-ui,sans-serif; }
      .kader { max-width:44rem; margin:0 auto; background:var(--paneel);
               border:1px solid var(--rand); border-radius:6px; padding:1.5rem 2rem; }
      h1 { margin:0 0 .3rem; color:var(--accent); font-size:1.3rem; }
      h2 { margin:1.8rem 0 .6rem; font-size:1rem; color:var(--accent);
           border-bottom:1px solid var(--rand); padding-bottom:.3rem; }
      .stappen { color:#8a8a8a; font-size:.85rem; margin:0 0 1.5rem; }
      p, li { margin:.6rem 0; }
      code { background:#000; padding:.1em .35em; border-radius:3px; font-size:.9em; }
      a { color:var(--accent); }
      small { color:#8a8a8a; font-size:.8rem; }
      .uitleg { color:#a5a5a5; font-size:.875rem; }
      .raster { display:grid; grid-template-columns:minmax(9rem,auto) minmax(0,1fr);
                gap:.55rem .9rem; align-items:center; }
      .raster small { grid-column:2; margin-top:-.3rem; }
      label { color:#a5a5a5; font-size:.9rem; }
      input, textarea { width:100%; padding:.45rem .6rem; background:#101010;
                        border:1px solid var(--rand); border-radius:4px; color:#d8d8d8;
                        font:inherit; }
      input:focus, textarea:focus { outline:2px solid #b36b00; outline-offset:1px; }
      textarea { font-family:ui-monospace,Consolas,monospace; font-size:.8rem; }
      .knop { display:inline-block; padding:.55rem 1.2rem; border-radius:4px;
              border:1px solid var(--rand); background:#2a2a2a; color:#d8d8d8;
              font:inherit; cursor:pointer; text-decoration:none; }
      .knop:hover { border-color:#b36b00; color:var(--accent); }
      .primair { background:linear-gradient(var(--accent),#b36b00); color:#111;
                 border-color:#b36b00; font-weight:600; }
      .primair:hover { color:#000; }
      .melding { padding:.7rem 1rem; border-radius:4px; margin:1rem 0;
                 border-left:3px solid #8a8a8a; background:#242424; }
      .melding.ok   { border-left-color:#4caf50; background:rgba(76,175,80,.1); }
      .melding.fout { border-left-color:#e05252; background:rgba(224,82,82,.1); }
      table.controle { width:100%; border-collapse:collapse; font-size:.9rem; margin:1rem 0; }
      table.controle th, table.controle td { padding:.5rem .6rem; border-bottom:1px solid var(--rand);
                                             text-align:left; vertical-align:top; }
      table.controle th { color:var(--accent); }
      .mid { text-align:center; white-space:nowrap; }
      .ja  { color:#4caf50; font-weight:600; }
      .nee { color:#e05252; font-weight:600; }
      .hulp { color:#8a8a8a; font-size:.8rem; margin-top:.25rem; }
    </style>
    </head>
    <body>
      <div class="kader">
        <h1>Black Vendetta installeren</h1>
        <p class="stappen">{$t}</p>
        {$inhoud}
      </div>
    </body>
    </html>
    HTML;
    exit;
}
