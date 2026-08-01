<?php
/**
 * Sessies, inloggen, rechten en CSRF-bescherming.
 */

declare(strict_types=1);

defined('BV_INC') || exit;

// Rechtenniveaus (kolom `users`.`level`).
const LEVEL_SPELER    = 1;
const LEVEL_MODERATOR = 200;
const LEVEL_ADMIN     = 255;
const LEVEL_OWNER     = 1000;

// --- Sessie ---------------------------------------------------------------

/** Start de sessie met veilige cookie-instellingen. */
function session_boot(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['SERVER_PORT'] ?? '') === '443'
        || str_starts_with((string) config('site.url'), 'https://');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,   // cookie alleen over https versturen
        'httponly' => true,     // niet uitleesbaar via JavaScript (blokkeert XSS-diefstal)
        'samesite' => 'Lax',    // wordt niet meegestuurd bij externe POSTs (CSRF)
    ]);
    session_name('bvsid');
    session_start();
}

// --- Huidige speler --------------------------------------------------------

/**
 * De ingelogde speler als array, of null.
 * De rij wordt per request één keer opgehaald.
 */
function current_user(bool $refresh = false): ?array
{
    static $user = null;
    static $loaded = false;

    if ($loaded && !$refresh) {
        return $user;
    }
    $loaded = true;

    $id = $_SESSION['uid'] ?? null;
    if (!is_int($id)) {
        return $user = null;
    }

    $user = q_row(
        'SELECT *,
                UNIX_TIMESTAMP(`crime`)     AS `crime_ts`,
                UNIX_TIMESTAMP(`ac`)        AS `ac_ts`,
                UNIX_TIMESTAMP(`bc`)        AS `bc_ts`,
                UNIX_TIMESTAMP(`pc`)        AS `pc_ts`,
                UNIX_TIMESTAMP(`kc`)        AS `kc_ts`,
                UNIX_TIMESTAMP(`slaap`)     AS `slaap_ts`,
                UNIX_TIMESTAMP(`safe`)      AS `safe_ts`,
                UNIX_TIMESTAMP(`start`)     AS `start_ts`,
                UNIX_TIMESTAMP(`transport`) AS `transport_ts`
           FROM `users` WHERE `id` = ?',
        [$id]
    );

    // Account is verwijderd terwijl de sessie nog liep.
    if ($user === null) {
        auth_logout();
    }

    return $user;
}

/** Is er iemand ingelogd? */
function is_logged_in(): bool
{
    return current_user() !== null;
}

/** Rechtenniveau van de ingelogde speler (0 als niemand ingelogd is). */
function user_level(): int
{
    return (int) (current_user()['level'] ?? 0);
}

/** Stuur naar de loginpagina als er niemand ingelogd is. */
function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        redirect('login.php');
    }
    return $user;
}

/** Stop met een melding als de speler te weinig rechten heeft. */
function require_level(int $level): array
{
    $user = require_login();
    if ((int) $user['level'] < $level) {
        http_response_code(403);
        fail_page('Geen toegang', 'Je hebt niet genoeg rechten voor deze pagina.', 403);
    }
    return $user;
}

/** Is de speler dood? Dan mag hij alleen nog kijken, niet handelen. */
function is_dead(): bool
{
    return (current_user()['status'] ?? 'levend') !== 'levend';
}

// --- In- en uitloggen ------------------------------------------------------

/**
 * Controleer gebruikersnaam + wachtwoord en log in bij succes.
 *
 * @return array{ok:bool, error?:string}
 */
function auth_attempt(string $login, string $password): array
{
    // Vaste vertraging tegen het snel afvuren van pogingen.
    usleep(200_000);

    $user = q_row('SELECT * FROM `users` WHERE `login` = ?', [$login]);

    // Altijd een hash verifiëren, ook als de gebruiker niet bestaat. Zo duurt
    // een foute gebruikersnaam even lang als een fout wachtwoord en valt uit
    // de responstijd niet af te leiden welke accounts bestaan.
    $hash = $user['pass'] ?? '$2y$12$invalidinvalidinvalidinvalidinvalidinvalidinvalidinvalidinv';
    $ok   = password_verify($password, $hash);

    if ($user === null || !$ok) {
        return ['ok' => false, 'error' => 'Verkeerde gebruikersnaam of wachtwoord.'];
    }

    if ((int) $user['activated'] !== 1) {
        return ['ok' => false, 'error' => 'Je account is nog niet geactiveerd. Kijk in je e-mail.'];
    }

    // Hash verouderd (bv. na een hogere cost-instelling)? Stilletjes vernieuwen.
    if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
        q('UPDATE `users` SET `pass` = ? WHERE `id` = ?',
            [password_hash($password, PASSWORD_DEFAULT), $user['id']]);
    }

    auth_login_as((int) $user['id']);
    ip_log((int) $user['id'], $user['login']);

    return ['ok' => true];
}

/** Markeer deze sessie als ingelogd voor het gegeven account. */
function auth_login_as(int $userId): void
{
    // Nieuw sessie-id: voorkomt session fixation.
    session_regenerate_id(true);
    $_SESSION['uid'] = $userId;
    $_SESSION['ua']  = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 120);
    q('UPDATE `users` SET `online` = NOW() WHERE `id` = ?', [$userId]);
}

/** Log uit en gooi de sessie weg. */
function auth_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $p['path'],
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => $p['samesite'],
        ]);
    }
    session_destroy();
}

/** Maak een wachtwoordhash. */
function auth_hash(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

// --- CSRF ------------------------------------------------------------------

/**
 * Token voor deze sessie. Zonder dit token kan een andere website geen
 * handelingen namens de speler uitvoeren (geld overmaken, bannen, ...).
 */
function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/** Verborgen invoerveld voor in elk POST-formulier. */
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

/** Is het meegestuurde token geldig? */
function csrf_valid(): bool
{
    $sent = $_POST['_token'] ?? $_GET['_token'] ?? '';
    return is_string($sent)
        && !empty($_SESSION['_csrf'])
        && hash_equals($_SESSION['_csrf'], $sent);
}

/**
 * Breek het verzoek af als het token ontbreekt of niet klopt.
 * Roep dit aan bovenaan elke POST-verwerking.
 */
function csrf_check(): void
{
    if (!csrf_valid()) {
        http_response_code(419);
        fail_page(
            'Sessie verlopen',
            'Je formulier was te oud of kwam niet van deze site. ' .
            '<a href="' . e(url('home.php')) . '">Ga terug naar het spel</a> en probeer het opnieuw.',
            419
        );
    }
}

// --- Bans en IP-logging -----------------------------------------------------

/**
 * Blokkeer verbannen spelers en IP-adressen. Draait bij elk verzoek.
 *
 * @return never|void
 */
function ban_check(): void
{
    $user  = current_user();
    $login = $user['login'] ?? null;

    $ban = $login !== null
        ? q_row('SELECT * FROM `bans` WHERE `ip` = ? OR `login` = ? LIMIT 1', [client_ip(), $login])
        : q_row('SELECT * FROM `bans` WHERE `ip` = ? LIMIT 1', [client_ip()]);

    if ($ban === null) {
        return;
    }

    auth_logout();
    fail_page(
        'Verbannen',
        'Je bent verbannen van ' . e((string) config('site.name')) . '.<br><br>' .
        'Reden: ' . e((string) $ban['reden']),
        403
    );
}

/** Houd bij vanaf welke IP-adressen een account inlogt (voor multi-detectie). */
function ip_log(int $userId, string $login): void
{
    $ip    = client_ip();
    $allo  = (int) q_val('SELECT `allo` FROM `multiple` WHERE `ip` = ?', [$ip], 0);
    $status = (string) q_val('SELECT `status` FROM `users` WHERE `id` = ?', [$userId], 'levend');

    q(
        'INSERT INTO `iplog` (`login`, `ip`, `time`, `allo`, `status`)
              VALUES (?, ?, NOW(), ?, ?)
         ON DUPLICATE KEY UPDATE `time` = NOW(), `allo` = VALUES(`allo`), `status` = VALUES(`status`)',
        [$login, $ip, $allo, $status]
    );
}
