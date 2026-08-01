<?php
/**
 * Paginaopmaak: kop, menu, statuspaneel en voet.
 *
 * Vervangt de oude frameset (index.php + upper.php + menu.php + right.php).
 * Gebruik in een pagina:
 *
 *     require __DIR__ . '/inc/bootstrap.php';
 *     $user = require_login();
 *     layout_header('Misdaad');
 *     panel_open('Misdaad');
 *     ... inhoud ...
 *     panel_close();
 *     layout_footer();
 */

declare(strict_types=1);

defined('BV_INC') || exit;

/** Bestandsnaam van de pagina die nu getoond wordt, bv. 'crime.php'. */
function current_page(): string
{
    return basename((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH)) ?: 'home.php';
}

/**
 * Het zijmenu. Per groep: titel => [bestand => label].
 * Items die van de speler afhangen worden hieronder toegevoegd.
 */
function menu_groups(array $user): array
{
    $groepen = [
        'Status' => [
            'home.php'                  => 'Status',
            'profile.php'               => 'Profiel',
            'message.php'               => 'Berichten',
            'respect.php'               => 'Eerpunten',
            'wallofshame.php'           => 'Schandpaal',
            'getmarried.php'            => 'Trouwen',
            'stats.php'                 => 'Statistieken',
            'hitlist.php?watch'         => 'Premielijst',
            'members.php?filter=levend' => 'Spelers',
            'premium.php'               => 'Premium',
        ],
        'Plaatsen' => [
            'shop.php'            => 'Winkel / Markt',
            'bank.php'            => 'Bank',
            'mbulletfactory.php'  => 'Lokale kogelfabriek',
            'bulletfactory.php'   => 'Kogelfabriek',
            'garage.php'          => 'Garage',
            'bloodbank.php'       => 'Bloedbank',
            'transport.php'       => 'Transport',
            'detectives.php'      => 'Detectivebureau',
            'mshop.php'           => 'Zwarte markt',
        ],
        'Misdaden' => [
            'drank.php'     => 'Drank',
            'drugs.php'     => 'Drugs',
            'crime.php'     => 'Misdaad',
            'nickacar.php'  => 'Auto stelen',
            'heist.php'     => 'Route 66',
            'oc.php'        => 'Organised Crime',
            'carrace.php'   => 'Race',
            'kill.php'      => 'Moorden',
            'jail.php'      => 'Gevangenis',
        ],
        'Familie' => [
            'fam.php?p=list' => 'Familielijst',
        ],
        'Gokken' => [
            'guess.php'    => 'Nummerraden',
            'roulette.php' => 'Roulette',
            'blackjack.php'=> 'Blackjack',
            'krassen.php'  => 'Krassen',
            'slots.php'    => 'Fruitmachine',
        ],
    ];

    // Familie-items hangen af van lidmaatschap en rang binnen de familie.
    $fam  = (string) ($user['famillie'] ?? '');
    $rang = (int) ($user['famrang'] ?? 0);

    if ($fam === '') {
        $groepen['Familie']['fam.php?p=new'] = 'Maak een familie';
    } else {
        $groepen['Familie']['fam.php?x=' . rawurlencode($fam)] = 'Familiepagina';
        if ($rang > 1) { $groepen['Familie']['famman.php?p=invite']  = 'Uitnodigen'; }
        if ($rang > 3) {
            $groepen['Familie']['famman.php?p=info']    = 'Familie-info';
            $groepen['Familie']['famman.php?p=message'] = 'Familiebericht';
            $groepen['Familie']['famman.php?p=members'] = 'Members';
        }
        if ($rang > 2) {
            $groepen['Familie']['famman.php?p=bulfac'] = 'Crusher';
            $groepen['Familie']['famman.php?p=log']    = 'Logboek';
            $groepen['Familie']['famman.php?p=land']   = 'Koop gebied';
        }
        if ($rang >= 3) { $groepen['Familie']['famman.php?p=bank'] = 'Familiebank'; }
        if ($rang === 5) { $groepen['Familie']['fam.php?p=delete'] = 'Verwijder familie'; }
    }

    if ((int) $user['level'] >= LEVEL_MODERATOR) {
        $groepen['Beheer'] = [
            'adm-search.php'   => 'Zoeken',
            'adm-online.php'   => 'Online',
            'adm-addnews.php'  => 'Nieuws',
            'adm-ban.php'      => 'Bannen',
            'adm-addmulti.php' => 'Multi-accounts',
            'adm-msg.php'      => 'Adminbericht',
            'adm-bo.php'       => 'Userstats',
            'adm-drdrpr.php'   => 'Steden',
            'adm-prison.php'   => 'Gevangenis',
            'adm-items.php'    => 'Items',
            'adm-shame.php'    => 'Wall of Shame',
            'adm-poll.php'     => 'Poll',
            'adm-getuigen.php' => 'Ooggetuigen',
            'adm-premium.php'  => 'Premium',
        ];
    }

    return $groepen;
}

/**
 * Kerncijfers voor het statuspaneel: één query in plaats van dertien
 * tabelscans zoals de oude right.php deed.
 */
function status_summary(array $user): array
{
    $rij = q_row(
        "SELECT
            (SELECT COUNT(*) + 1 FROM `users`
              WHERE `status` = 'levend' AND `activated` = 1 AND `xp` > :xp)      AS positie,
            (SELECT COUNT(*) FROM `users`
              WHERE `status` = 'levend' AND `activated` = 1)                     AS spelers,
            (SELECT COUNT(*) FROM `messages`
              WHERE `to` = :login AND `read` = 0)                                AS ongelezen,
            (SELECT COUNT(*) FROM `users`
              WHERE `online` > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
                AND `status` = 'levend')                                         AS online",
        ['xp' => (int) $user['xp'], 'login' => $user['login']]
    ) ?? [];

    return [
        'positie'   => (int) ($rij['positie'] ?? 0),
        'spelers'   => (int) ($rij['spelers'] ?? 0),
        'ongelezen' => (int) ($rij['ongelezen'] ?? 0),
        'online'    => (int) ($rij['online'] ?? 0),
    ];
}

// --- Opbouw van de pagina ---------------------------------------------------

/** Open de pagina: <head>, kopbalk, menu en de opening van het inhoudsvak. */
function layout_header(string $titel = ''): void
{
    $user     = current_user();
    $siteNaam = (string) config('site.name', 'Black Vendetta');
    $volTitel = $titel !== '' ? "{$titel} - {$siteNaam}" : $siteNaam;
    $huidig   = current_page();

    echo '<!doctype html>' . "\n";
    echo '<html lang="nl">' . "\n<head>\n";
    echo '<meta charset="utf-8">' . "\n";
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";
    echo '<title>' . e($volTitel) . "</title>\n";
    echo '<link rel="stylesheet" href="' . e(url('assets/css/style.css')) . '?v=2">' . "\n";
    echo '<link rel="icon" href="' . e(url('favicon.ico')) . '">' . "\n";
    echo "</head>\n<body>\n";

    // Zijmenu en statuspaneel horen bij een levende, ingelogde speler. Zijn ze
    // er niet, dan moet het raster één kolom zijn in plaats van drie — anders
    // valt de inhoud in de smalle menukolom.
    $metZijkanten = $user !== null && !is_dead();

    // --- Kopbalk ---
    echo '<header class="topbar">' . "\n";
    echo '<a class="brand" href="' . e(url('home.php')) . '">' . e($siteNaam) . "</a>\n";
    echo '<nav class="topnav">';
    if ($user !== null) {
        $top = [
            'news.php'   => 'Nieuws',
            'forum.php'  => 'Forum',
            'poll.php'   => 'Poll',
            'tip.php'    => 'Ticket',
            'help.php'   => 'FAQ',
        ];
    } else {
        $top = [
            'index.php'    => 'Home',
            'news.php'     => 'Nieuws',
            'register.php' => 'Registreer',
            'help.php'     => 'FAQ',
        ];
    }
    foreach ($top as $bestand => $label) {
        $klasse = $bestand === $huidig ? ' class="actief"' : '';
        echo '<a href="' . e(url($bestand)) . '"' . $klasse . '>' . e($label) . '</a>';
    }
    if ($user !== null) {
        // Uitloggen is een handeling, dus via POST met CSRF-token.
        echo '<form method="post" action="' . e(url('logout.php')) . '" class="logout-form">'
           . csrf_field()
           . '<button type="submit">Uitloggen</button></form>';
    } else {
        echo '<a href="' . e(url('login.php')) . '">Inloggen</a>';
    }
    echo "</nav>\n";

    // Knop om het menu op smalle schermen open te klappen. Alleen tonen als er
    // ook werkelijk een menu is; uitgelogd bediende hij niets.
    if ($metZijkanten) {
        echo '<button class="menu-toggle" type="button" aria-controls="zijmenu" '
           . 'aria-expanded="false" aria-label="Menu">&#9776;</button>' . "\n";
    }
    echo "</header>\n";

    echo '<div class="layout' . ($metZijkanten ? '' : ' alleen-inhoud') . '">' . "\n";

    // --- Zijmenu ---
    if ($metZijkanten) {
        echo '<nav class="sidebar" id="zijmenu">' . "\n";
        foreach (menu_groups($user) as $groep => $items) {
            echo '<details class="menugroep" open><summary>' . e($groep) . "</summary>\n<ul>\n";
            foreach ($items as $bestand => $label) {
                $klasse = str_starts_with($bestand, $huidig) ? ' class="actief"' : '';
                echo '<li><a href="' . e(url($bestand)) . '"' . $klasse . '>' . $label . "</a></li>\n";
            }
            echo "</ul>\n</details>\n";
        }
        echo '<form class="zoekform" method="get" action="' . e(url('profile.php')) . '">'
           . '<input type="search" name="login" maxlength="16" placeholder="Zoek speler" aria-label="Zoek speler">'
           . '<button type="submit">Ga</button></form>' . "\n";
        echo "</nav>\n";
    }

    // --- Inhoud ---
    echo '<main class="content">' . "\n";

    foreach (flash_take() as $melding) {
        echo '<div class="melding melding-' . e($melding['type']) . '">' . e($melding['msg']) . "</div>\n";
    }
}

/** Statuspaneel rechts en de afsluiting van de pagina. */
function layout_footer(): void
{
    echo "</main>\n";

    $user = current_user();
    if ($user !== null && !is_dead()) {
        status_panel($user);
    }

    echo "</div>\n"; // .layout

    echo '<footer class="paginavoet">' . e((string) config('site.name'))
       . ' &middot; <a href="' . e(url('help.php')) . '">Spelregels</a>'
       . ' &middot; <a href="' . e(url('tip.php')) . '">Hulp nodig?</a></footer>' . "\n";

    echo '<script src="' . e(url('assets/js/app.js')) . '?v=2" defer></script>' . "\n";
    echo "</body>\n</html>\n";
}

/** Het rechterpaneel met de kerncijfers van de speler. */
function status_panel(array $user): void
{
    $stat = status_summary($user);
    $rang = rank_name((int) $user['xp'], (string) $user['geslacht']);
    $vord = rank_progress((int) $user['xp']);
    $cel  = jail_status($user['login']);

    echo '<aside class="statuspaneel">' . "\n";
    echo '<h2>' . e($user['login']) . "</h2>\n";
    echo '<p class="rang">' . e($rang) . "</p>\n";

    echo '<div class="balk" role="img" aria-label="Vordering naar volgende rang: ' . $vord . ' procent">'
       . '<span style="width:' . $vord . '%"></span></div>' . "\n";
    echo '<p class="balklabel">' . $vord . '% naar de volgende rang</p>' . "\n";

    if ($cel !== null) {
        echo '<p class="waarschuwing">Je zit vast: nog ' . e(duration($cel['resterend'])) . '</p>' . "\n";
    }

    $regels = [
        'Op zak'     => money((int) $user['zak']),
        'Bank'       => money((int) $user['bank']),
        'Kogels'     => num((int) $user['kogels']),
        'Diamanten'  => num((int) ($user['diamanten'] ?? 0)),
        'Gezondheid' => (int) $user['health'] . '%',
        'Energie'    => num((float) $user['energie'], 1) . '%',
        'Moordervaring' => num((float) $user['se'], 1) . '%',
        'Stad'       => e((string) $user['stad']),
        'Familie'    => $user['famillie'] !== '' ? e((string) $user['famillie']) : 'Geen',
        'Positie'    => '#' . num($stat['positie']) . ' van ' . num($stat['spelers']),
    ];

    echo "<dl>\n";
    foreach ($regels as $label => $waarde) {
        echo '<dt>' . e($label) . '</dt><dd>' . $waarde . "</dd>\n";
    }
    echo "</dl>\n";

    if ($stat['ongelezen'] > 0) {
        echo '<a class="knop knop-nadruk" href="' . e(url('message.php')) . '">'
           . $stat['ongelezen'] . ' nieuw bericht' . ($stat['ongelezen'] === 1 ? '' : 'en') . "</a>\n";
    }

    if (is_premium($user)) {
        echo '<p class="premium">Premium tot ' . e(datetime_nl((string) $user['premium_tot']))
           . "</p>\n";
    }

    echo '<p class="online">' . num($stat['online']) . " spelers online</p>\n";
    echo "</aside>\n";
}

// --- Bouwstenen voor in de inhoud -------------------------------------------

/** Open een kader met een titel. */
function panel_open(string $titel): void
{
    echo '<section class="paneel"><h1>' . e($titel) . '</h1><div class="paneelinhoud">' . "\n";
}

function panel_close(): void
{
    echo "</div></section>\n";
}

/** Losse melding binnen de inhoud. $type is 'ok', 'fout' of 'info'. */
function notice(string $tekst, string $type = 'info'): void
{
    echo '<div class="melding melding-' . e($type) . '">' . $tekst . "</div>\n";
}
