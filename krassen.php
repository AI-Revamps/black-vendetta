<?php
/**
 * Krasloten: koop een lot van € 10.000 en kras zes vakjes open. Komt hetzelfde
 * symbool drie keer of vaker voor, dan win je die prijs.
 *
 * Wat hier gerepareerd is ten opzichte van de oude versie:
 *
 *  - Een lot kopen ging via een GET-link (krassen.php?scratch=yes). Een
 *    afbeelding met die URL in een bericht liet de lezer € 10.000 uitgeven.
 *  - Betalen en uitbetalen gebeurde in losse queries zonder transactie.
 *  - De teller van gekochte loten werd opgehoogd vóór de betaalcontrole.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

const KRAS_PRIJS    = 10_000;
const KRAS_PER_DAG  = 10;
const KRAS_VAKJES   = 6;
const KRAS_NODIG    = 3;    // hoe vaak een symbool moet voorkomen om te winnen

/** De symbolen op een kraslot en wat ze opleveren. */
function kras_prijzen(): array
{
    return [
        ['label' => '€ 1.000.000', 'soort' => 'geld',   'waarde' => 1_000_000],
        ['label' => '€ 500.000',   'soort' => 'geld',   'waarde' => 500_000],
        ['label' => '€ 100.000',   'soort' => 'geld',   'waarde' => 100_000],
        ['label' => '500 kogels',  'soort' => 'kogels', 'waarde' => 500],
        ['label' => '250 kogels',  'soort' => 'kogels', 'waarde' => 250],
        ['label' => '100 kogels',  'soort' => 'kogels', 'waarde' => 100],
        ['label' => '€ 50.000',    'soort' => 'geld',   'waarde' => 50_000],
        ['label' => '€ 10.000',    'soort' => 'geld',   'waarde' => 10_000],
        ['label' => '€ 5.000',     'soort' => 'geld',   'waarde' => 5_000],
    ];
}

$user = require_login();

if (is_dead()) {
    redirect('rip.php');
}

$uitslag = null;

if (is_post()) {
    csrf_check();
    try {
        $uitslag = krassen($user);
    } catch (SpelFout $e) {
        $uitslag = ['tekst' => $e->getMessage(), 'type' => 'fout', 'vakjes' => []];
    }
    $user = current_user(true);
}

$vandaag = (int) q_val('SELECT `aantal` FROM `kras` WHERE `login` = ?', [$user['login']], 0);

layout_header('Krassen');

if ($uitslag !== null) {
    notice(e($uitslag['tekst']), $uitslag['type']);

    if ($uitslag['vakjes'] !== []) {
        toon_lot($uitslag['vakjes']);
    }
}

panel_open('Krasloten');

echo '<p>Een kraslot kost ' . money(KRAS_PRIJS) . '. Je krast ' . KRAS_VAKJES
   . ' vakjes open; staat er ' . KRAS_NODIG . ' keer hetzelfde, dan win je die prijs.</p>';
echo '<p>Je hebt vandaag <strong>' . $vandaag . ' van de ' . KRAS_PER_DAG
   . '</strong> loten gekocht.</p>';

if ($vandaag >= KRAS_PER_DAG) {
    echo '<p>Je kunt vandaag geen loten meer kopen. Morgen weer.</p>';
} elseif ((int) $user['zak'] < KRAS_PRIJS) {
    echo '<p>Je hebt niet genoeg geld op zak.</p>';
} else {
    echo '<form method="post">' . csrf_field()
       . '<button type="submit">Koop een lot voor ' . money(KRAS_PRIJS) . '</button></form>';
}

echo '<h3>Prijzen</h3><ul>';
foreach (kras_prijzen() as $prijs) {
    echo '<li>' . e($prijs['label']) . '</li>';
}
echo '</ul>';

panel_close();
layout_footer();

// ==========================================================================

/**
 * @return array{tekst:string, type:string, vakjes:array}
 * @throws SpelFout
 */
function krassen(array $user): array
{
    return db_transaction(static function () use ($user): array {
        $speler = lock_user((int) $user['id']);

        // Rij aanmaken als die er nog niet is, en meteen vergrendelen.
        q('INSERT IGNORE INTO `kras` (`login`, `aantal`) VALUES (?, 0)', [$speler['login']]);
        $vandaag = (int) q_val('SELECT `aantal` FROM `kras` WHERE `login` = ? FOR UPDATE',
            [$speler['login']], 0);

        if ($vandaag >= KRAS_PER_DAG) {
            throw new SpelFout('Je kunt maar ' . KRAS_PER_DAG . ' loten per dag kopen.');
        }
        if (!afboeken((int) $speler['id'], KRAS_PRIJS, 'zak')) {
            throw new SpelFout('Een lot kost ' . money(KRAS_PRIJS) . '.');
        }

        q('UPDATE `kras` SET `aantal` = `aantal` + 1 WHERE `login` = ?', [$speler['login']]);

        // Zes vakjes openkrassen.
        $prijzen = kras_prijzen();
        $vakjes  = [];
        $telling = array_fill(0, count($prijzen), 0);

        for ($i = 0; $i < KRAS_VAKJES; $i++) {
            $nr        = random_int(0, count($prijzen) - 1);
            $vakjes[]  = $prijzen[$nr]['label'];
            $telling[$nr]++;
        }

        // Uitbetalen.
        $gewonnen = [];
        foreach ($telling as $nr => $keer) {
            if ($keer < KRAS_NODIG) {
                continue;
            }

            $prijs = $prijzen[$nr];
            $veld  = $prijs['soort'] === 'kogels' ? 'kogels' : 'zak';
            bijschrijven((int) $speler['id'], (int) $prijs['waarde'], $veld);
            $gewonnen[] = $prijs['label'];
        }

        if ($gewonnen === []) {
            return ['tekst' => 'Helaas, niets gewonnen.', 'type' => 'fout', 'vakjes' => $vakjes];
        }

        log_action((string) $speler['login'], 'krassen', 'Gewonnen: ' . implode(', ', $gewonnen));

        return [
            'tekst'  => 'Gewonnen: ' . implode(' en ', $gewonnen) . '.',
            'type'   => 'ok',
            'vakjes' => $vakjes,
        ];
    });
}

function toon_lot(array $vakjes): void
{
    echo '<div class="kraslot">';
    foreach ($vakjes as $vakje) {
        echo '<span>' . e($vakje) . '</span>';
    }
    echo '</div>';
}
