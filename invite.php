<?php
/**
 * Familie-uitnodigingen bekijken, accepteren of weigeren.
 *
 * Wat hier gerepareerd is ten opzichte van de oude versie:
 *
 *  - Accepteren en weigeren liepen via GET-links. Een afbeelding met
 *    invite.php?accept=1&fam=X in een bericht liet de lezer ongemerkt lid
 *    worden van een familie, of met accept=0 zijn uitnodiging weggooien.
 *  - De bevestiging ging naar `$famillie->recruiter`, een kolom die in de
 *    tabel `famillie` niet bestaat. De ontvanger was dus leeg en het bericht
 *    werd door de opruimtaak weer verwijderd: de uitnodiger hoorde nooit iets.
 *  - Lid worden gebeurde zonder transactie, terwijl er ondertussen ruimte in
 *    de familie vrij of vol kon lopen.
 *  - Er was geen overzicht van je openstaande uitnodigingen; je kwam er alleen
 *    via de link in het bericht.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';
require_once BV_INC . '/familie.php';

/** Hoeveel grondgebied elk lid kost. */
const GROND_PER_LID = 5;

$user = require_login();

$melding = null;
$type    = 'info';

if (is_post()) {
    csrf_check();
    try {
        $melding = verwerk($user, post('actie'), post('fam'));
        $type    = 'ok';
        $user    = current_user(true);
    } catch (SpelFout $e) {
        $melding = $e->getMessage();
        $type    = 'fout';
    }
}

$uitnodigingen = q_all(
    'SELECT i.`famillie`, f.`stad`, f.`grond`,
            (SELECT COUNT(*) FROM `users` u
              WHERE u.`famillie` = i.`famillie` AND u.`status` = \'levend\') AS `leden`
       FROM `invite` i
       JOIN `famillie` f ON f.`name` = i.`famillie`
      WHERE i.`login` = ?
   ORDER BY i.`famillie`',
    [$user['login']]
);

layout_header('Uitnodigingen');

if ($melding !== null) {
    notice(e($melding), $type);
}

panel_open('Familie-uitnodigingen');

if (($user['famillie'] ?? '') !== '') {
    echo '<p>Je zit al in de familie <strong>' . e((string) $user['famillie'])
       . '</strong>. Verlaat die eerst als je wilt overstappen.</p>';
} elseif ($uitnodigingen === []) {
    echo '<p>Je hebt geen openstaande uitnodigingen.</p>';
    echo '<p><a href="' . e(url('fam.php')) . '">Bekijk de families</a></p>';
} else {
    echo '<p>Je bent uitgenodigd voor de volgende ' . (count($uitnodigingen) === 1 ? 'familie' : 'families')
       . '. Je kunt maar bij één familie tegelijk horen.</p>';

    echo '<div class="tabelwikkel"><table class="lijst">';
    echo '<thead><tr><th>Familie</th><th>Stad</th><th class="getal">Leden</th>'
       . '<th class="getal">Ruimte</th><th></th></tr></thead><tbody>';

    foreach ($uitnodigingen as $rij) {
        $leden  = (int) $rij['leden'];
        $ruimte = intdiv((int) $rij['grond'], GROND_PER_LID) - $leden;

        echo '<tr>';
        echo '<td><a href="' . e(url('fam.php?x=' . rawurlencode((string) $rij['famillie']))) . '">'
           . e((string) $rij['famillie']) . '</a></td>';
        echo '<td>' . e((string) $rij['stad']) . '</td>';
        echo '<td class="getal">' . num($leden) . '</td>';
        echo '<td class="getal">' . ($ruimte > 0 ? num($ruimte) : '<span class="waarschuwing-tekst">vol</span>') . '</td>';
        echo '<td>';
        echo '<form method="post" style="display:inline">' . csrf_field()
           . '<input type="hidden" name="fam" value="' . e((string) $rij['famillie']) . '">'
           . '<button type="submit" name="actie" value="accepteer"' . ($ruimte > 0 ? '' : ' disabled')
           . '>Accepteren</button> '
           . '<button type="submit" name="actie" value="weiger">Weigeren</button></form>';
        echo '</td></tr>';
    }

    echo '</tbody></table></div>';
}

panel_close();
layout_footer();

// ==========================================================================

/** @throws SpelFout */
function verwerk(array $user, string $actie, string $familie): string
{
    if ($familie === '') {
        throw new SpelFout('Kies een familie.');
    }

    return match ($actie) {
        'accepteer' => accepteren($user, $familie),
        'weiger'    => weigeren($user, $familie),
        default     => throw new SpelFout('Onbekende handeling.'),
    };
}

/** @throws SpelFout */
function accepteren(array $user, string $naam): string
{
    return db_transaction(static function () use ($user, $naam): string {
        $speler = lock_user((int) $user['id']);

        if ($speler['famillie'] !== '') {
            throw new SpelFout('Je zit al in een familie.');
        }

        // De uitnodiging moet bestaan én voor jou zijn.
        $uitnodiging = q_row(
            'SELECT * FROM `invite` WHERE `login` = ? AND `famillie` = ? FOR UPDATE',
            [$speler['login'], $naam]
        );

        if ($uitnodiging === null) {
            throw new SpelFout('Je hebt geen uitnodiging voor deze familie.');
        }

        $familie = q_row('SELECT * FROM `famillie` WHERE `name` = ? FOR UPDATE', [$naam]);

        if ($familie === null) {
            throw new SpelFout('Die familie bestaat niet meer.');
        }

        // Elk lid kost grondgebied. Binnen de transactie geteld, zodat twee
        // gelijktijdige acceptaties de familie niet over de grens duwen.
        $leden = fam_aantal_leden($naam);

        if (($leden + 1) * GROND_PER_LID > (int) $familie['grond']) {
            throw new SpelFout('Deze familie heeft niet genoeg grondgebied voor een extra lid.');
        }

        q('UPDATE `users` SET `famillie` = ?, `famrang` = ? WHERE `id` = ?',
            [$naam, FAM_LID, $speler['id']]);

        // Alle andere uitnodigingen vervallen.
        q('DELETE FROM `invite` WHERE `login` = ?', [$speler['login']]);

        // De leiding op de hoogte stellen. In de oude versie ging dit naar een
        // kolom die niet bestaat, dus kwam het nergens aan.
        $leiding = q_all(
            'SELECT `login` FROM `users` WHERE `famillie` = ? AND `famrang` >= ?',
            [$naam, FAM_CONSIGLIERI]
        );

        foreach ($leiding as $baas) {
            notify((string) $baas['login'], 'Familie',
                $speler['login'] . ' heeft de uitnodiging voor ' . $naam . ' aangenomen.');
        }

        return 'Je bent nu lid van ' . $naam . '.';
    });
}

/** @throws SpelFout */
function weigeren(array $user, string $naam): string
{
    $verwijderd = q_count('DELETE FROM `invite` WHERE `login` = ? AND `famillie` = ?',
        [$user['login'], $naam]);

    if ($verwijderd === 0) {
        throw new SpelFout('Je hebt geen uitnodiging voor deze familie.');
    }

    $leiding = q_all(
        'SELECT `login` FROM `users` WHERE `famillie` = ? AND `famrang` >= ?',
        [$naam, FAM_CONSIGLIERI]
    );

    foreach ($leiding as $baas) {
        notify((string) $baas['login'], 'Familie',
            $user['login'] . ' heeft de uitnodiging voor ' . $naam . ' geweigerd.');
    }

    return 'Je hebt de uitnodiging voor ' . $naam . ' geweigerd.';
}
