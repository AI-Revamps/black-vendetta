<?php
/**
 * Spelregels die op veel plaatsen nodig zijn: rangen, wachttijden en de
 * gevangenis. Vervangt de oude rangen.php, tijden.php en de losse blokken
 * die in config.php stonden.
 */

declare(strict_types=1);

defined('BV_INC') || exit;

// --- Rangen ---------------------------------------------------------------

/**
 * De rangladder: [minimale xp, naam, naam voor vrouwelijke spelers].
 * De bovengrens van een rang is de ondergrens van de volgende.
 */
function rank_ladder(): array
{
    return [
        [0,     'Empty-Suit',  null],
        [10,    'Deliveryboy', 'Delivery Girl'],
        [20,    'Picciotto',   null],
        [50,    'Shoplifter',  null],
        [150,   'Pickpocket',  null],
        [500,   'Thief',       null],
        [1000,  'Associate',   null],
        [2000,  'Mobster',     null],
        [3000,  'Soldier',     null],
        [4500,  'Swindler',    null],
        [6000,  'Assassin',    null],
        [8000,  'Local Chief', null],
        [11000, 'Chief',       null],
        [15000, 'Bruglione',   null],
        [20000, 'Godfather',   'First Lady'],
    ];
}

/** Naam van de rang die bij dit aantal ervaringspunten hoort. */
function rank_name(int $xp, string $gender = 'Man'): string
{
    $ladder = rank_ladder();
    $match  = $ladder[0];

    foreach ($ladder as $step) {
        if ($xp >= $step[0]) {
            $match = $step;
        }
    }

    return ($gender === 'Vrouw' && $match[2] !== null) ? $match[2] : $match[1];
}

/**
 * Voortgang binnen de huidige rang, in hele procenten (0-100).
 * De hoogste rang geeft altijd 100 terug.
 */
function rank_progress(int $xp): int
{
    $ladder = rank_ladder();
    $last   = count($ladder) - 1;

    for ($i = $last; $i >= 0; $i--) {
        if ($xp >= $ladder[$i][0]) {
            if ($i === $last) {
                return 100;
            }
            $from = $ladder[$i][0];
            $to   = $ladder[$i + 1][0];
            return (int) max(0, min(100, floor(($xp - $from) / ($to - $from) * 100)));
        }
    }
    return 0;
}

/**
 * Positie op de rangladder, 1 (Empty-Suit) tot en met 15 (Godfather).
 * Wordt gebruikt om te bepalen hoeveel rangen je omhoog mag moorden.
 */
function rank_index(int $xp): int
{
    $index = 1;
    foreach (rank_ladder() as $i => $step) {
        if ($xp >= $step[0]) {
            $index = $i + 1;
        }
    }
    return $index;
}

// --- Wachttijden ----------------------------------------------------------

/**
 * Afkoeltijden in seconden. Pas hier de balans van het spel aan; alle
 * modules lezen dezelfde waarden.
 */
function cooldowns(): array
{
    return [
        'crime'    => 60,
        'auto'     => 120,
        'bank'     => 300,
        'route'    => 3600,
        'kill'     => 3600,
        'slaap'    => 300,
        'fitness'  => 120,
        'schieten' => 60,
    ];
}

/** Unix-tijdstip waarop een afkoeltijd afloopt, vanaf nu. */
function cooldown_until(string $name): int
{
    return time() + (cooldowns()[$name] ?? 60);
}

/**
 * Resterende seconden van een afkoeltijd.
 * $timestamp is een van de `*_ts` velden uit current_user().
 */
function cooldown_left(?int $timestamp): int
{
    return max(0, (int) $timestamp - time());
}

// --- Gevangenis ------------------------------------------------------------

/**
 * Straftijd en boete bij arrestatie, oplopend met ervaring.
 *
 * @return array{seconden:int, boete:int}
 */
function jail_sentence(int $xp): array
{
    $ladder = [
        [10,    20,  250],
        [20,    40,  500],
        [50,    60,  750],
        [150,   90,  1000],
        [500,   120, 5000],
        [1000,  180, 7500],
        [2000,  190, 12500],
        [3000,  230, 25000],
        [4500,  270, 50000],
        [6000,  320, 60000],
        [8000,  370, 75000],
        [11000, 420, 85000],
        [15000, 480, 100000],
        [20000, 540, 125000],
    ];

    foreach ($ladder as [$maxXp, $seconden, $boete]) {
        if ($xp < $maxXp) {
            return ['seconden' => $seconden, 'boete' => $boete];
        }
    }
    return ['seconden' => 600, 'boete' => 150000];
}

/** Zet een speler vast. */
function jail_put(string $login, int $xp, string $stad, string $famillie = ''): void
{
    $s = jail_sentence($xp);
    q(
        'INSERT INTO `jail` (`login`, `boete`, `stad`, `famillie`, `time`)
              VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))',
        [$login, $s['boete'], $stad, $famillie, $s['seconden']]
    );
}

/**
 * Zit deze speler vast? Geeft null terug als hij vrij is, anders de cel
 * met een extra veld `resterend` (seconden).
 */
function jail_status(string $login): ?array
{
    $cel = q_row(
        'SELECT *, UNIX_TIMESTAMP(`time`) - UNIX_TIMESTAMP(NOW()) AS `resterend`
           FROM `jail` WHERE `login` = ? LIMIT 1',
        [$login]
    );

    if ($cel === null || (int) $cel['resterend'] <= 0) {
        return null;
    }

    $cel['resterend'] = (int) $cel['resterend'];
    return $cel;
}

/**
 * Stuur door naar de gevangenispagina als de speler vastzit.
 * Roep dit aan op elke pagina waar je iets mag ondernemen.
 */
function block_if_jailed(): void
{
    $user = current_user();
    if ($user !== null && jail_status($user['login']) !== null) {
        redirect('jisin.php');
    }
}

// --- Geld en spelers vergrendelen -------------------------------------------

/**
 * Haal een speler op en vergrendel de rij tot het einde van de transactie.
 *
 * Gebruik dit vóór elke controle op saldo, kogels of voorraad. Zonder deze
 * vergrendeling kunnen twee gelijktijdige verzoeken allebei "genoeg geld"
 * zien en allebei afboeken — de klassieke manier om geld te verdubbelen.
 *
 *     db_transaction(function () use ($id, $bedrag) {
 *         $speler = lock_user($id);
 *         if ($speler['zak'] < $bedrag) { throw new SpelFout('Te weinig geld.'); }
 *         q('UPDATE `users` SET `zak` = `zak` - ? WHERE `id` = ?', [$bedrag, $id]);
 *     });
 */
function lock_user(int $id): ?array
{
    return q_row('SELECT * FROM `users` WHERE `id` = ? FOR UPDATE', [$id]);
}

/** Zelfde, maar op naam. */
function lock_user_by_login(string $login): ?array
{
    return q_row('SELECT * FROM `users` WHERE `login` = ? FOR UPDATE', [$login]);
}

/**
 * Boek geld af, maar alleen als het er is. De voorwaarde zit in de query zelf,
 * zodat er geen gaatje zit tussen controleren en afboeken.
 *
 * @return bool False als het saldo niet toereikend was.
 */
function afboeken(int $userId, int $bedrag, string $veld = 'zak'): bool
{
    if ($bedrag < 0 || !in_array($veld, ['zak', 'bank', 'kogels'], true)) {
        return false;
    }
    return q_count(
        "UPDATE `users` SET `{$veld}` = `{$veld}` - ? WHERE `id` = ? AND `{$veld}` >= ?",
        [$bedrag, $userId, $bedrag]
    ) === 1;
}

/** Geld of kogels bijschrijven. */
function bijschrijven(int $userId, int $bedrag, string $veld = 'zak'): void
{
    if ($bedrag < 0 || !in_array($veld, ['zak', 'bank', 'kogels'], true)) {
        return;
    }
    q("UPDATE `users` SET `{$veld}` = `{$veld}` + ? WHERE `id` = ?", [$bedrag, $userId]);
}

/** Stuur een systeembericht naar een speler. */
function notify(string $naar, string $onderwerp, string $tekst): void
{
    q(
        'INSERT INTO `messages` (`time`, `from`, `to`, `subject`, `message`)
              VALUES (NOW(), ?, ?, ?, ?)',
        ['Notificatie', $naar, $onderwerp, $tekst]
    );
}

/** Leg een handeling vast in het logboek, voor de beheerders. */
function log_action(string $login, string $area, string $com, int $code = 0, string $person = ''): void
{
    q(
        'INSERT INTO `logs` (`time`, `login`, `person`, `code`, `area`, `com`)
              VALUES (NOW(), ?, ?, ?, ?, ?)',
        [$login, $person, $code, $area, $com]
    );
}

/**
 * Fout die de speler mag zien: "je hebt niet genoeg geld", "die persoon
 * bestaat niet". Draait een transactie netjes terug.
 */
class SpelFout extends RuntimeException
{
}

// --- Steden ----------------------------------------------------------------

/** Lijst met steden uit de configuratie. */
function cities(): array
{
    return (array) config('game.cities', []);
}

/** Bestaat deze stad? Gebruik dit om invoer te valideren. */
function is_city(string $name): bool
{
    return in_array($name, cities(), true);
}

/** Willekeurige startstad voor een nieuwe speler. */
function random_city(): string
{
    $list = cities();
    return $list[array_rand($list)];
}

// --- Forum -----------------------------------------------------------------

/**
 * De vaste forumcategorieën. Alleen deze sleutels komen in de database.
 * Staat hier omdat zowel forum.php als adm-forum.php ze nodig heeft.
 */
function forum_categorieen(): array
{
    return [
        'algemeen' => 'Algemeen',
        'vragen'   => 'Vragen',
        'tip'      => 'Tips',
        'bug'      => 'Bugs',
        'oc'       => 'Organised Crime',
        'race'     => 'Races',
        'familie'  => 'Families',
        'varia'    => 'Varia',
        'rip'      => 'In memoriam',
    ];
}

/** In deze categorie plaatst alleen het spel zelf berichten. */
function forum_alleen_lezen(string $type): bool
{
    return $type === 'rip';
}
