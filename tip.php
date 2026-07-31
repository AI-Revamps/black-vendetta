<?php
/**
 * Tips en bugmeldingen aan het beheer.
 *
 * Wat hier gerepareerd is:
 *
 *  - Het bericht ging naar de hardcoded gebruikersnaam `JanuS`. Bestaat die
 *    speler niet meer — en dat is het geval — dan verdween elke melding in een
 *    postbus die niemand kon openen. Meldingen gaan nu naar iedereen met
 *    beheerdersrechten, en worden bovendien in het logboek gezet zodat ze niet
 *    verloren gaan als een beheerder zijn berichten opruimt.
 *  - Naam en e-mailadres kwamen uit verborgen formuliervelden, dus die kon je
 *    op elke gewenste waarde zetten. Ze worden nu uit de sessie gehaald.
 *  - De melding werd ongefilterd in de query gezet, samengeplakt met `<br>` in
 *    het berichtveld.
 *  - Het formulier werd altijd getoond, ook nadat je verzonden had, en de
 *    afhandeling stond eronder — dus je zag eerst een leeg formulier en pas
 *    daarna de bevestiging.
 *  - De captchacontrole liet een lege ingevoerde code langs wanneer
 *    `$_SESSION['verify']` ook leeg was.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';
require BV_INC . '/captcha.php';

const TIP_MAX      = 2000;
const TIP_PER_DAG  = 5;

$user    = require_login();
$melding = null;
$type    = 'info';

if (is_post()) {
    csrf_check();
    try {
        $melding = versturen($user, post('tip'), post('captcha'));
        $type    = 'ok';
    } catch (SpelFout $e) {
        $melding = $e->getMessage();
        $type    = 'fout';
    }
}

layout_header('Tip of bug melden');

if ($melding !== null) {
    notice(e($melding), $type);
}

panel_open('Tip of bug melden');

echo '<p>Een bug gevonden, of een idee voor het spel? Laat het hier weten. '
   . 'Je naam wordt automatisch meegestuurd, dus je hoeft die er niet bij te zetten.</p>';

echo '<form method="post">' . csrf_field();
echo '<div class="veldenraster">';
echo '<label for="tip">Je melding</label>';
echo '<textarea id="tip" name="tip" rows="8" maxlength="' . TIP_MAX . '" required></textarea>';
echo '<label for="captcha">Controlecode</label>';
echo '<span>' . captcha_field() . '</span>';
echo '<span></span><button type="submit">Versturen</button>';
echo '</div></form>';

panel_close();

$eerder = q_all(
    "SELECT `time`, `com` FROM `logs`
      WHERE `login` = ? AND `area` = 'tip' ORDER BY `time` DESC LIMIT 10",
    [$user['login']]
);

panel_open('Je eerdere meldingen');

if ($eerder === []) {
    echo '<p>Je hebt nog niets gemeld.</p>';
} else {
    echo '<div class="tabelwikkel"><table class="lijst">';
    echo '<thead><tr><th>Wanneer</th><th>Melding</th></tr></thead><tbody>';

    foreach ($eerder as $regel) {
        echo '<tr><td>' . e(datetime_nl($regel['time'])) . '</td>';
        echo '<td>' . nl2br(e((string) $regel['com']), false) . '</td></tr>';
    }

    echo '</tbody></table></div>';
}

panel_close();
layout_footer();

// ==========================================================================

/** @throws SpelFout */
function versturen(array $user, string $tip, string $captcha): string
{
    if (!captcha_check($captcha)) {
        throw new SpelFout('De controlecode klopt niet. Er staat een nieuwe klaar.');
    }

    $tip = trim($tip);

    if ($tip === '') {
        throw new SpelFout('Vul een melding in.');
    }
    if (mb_strlen($tip) > TIP_MAX) {
        throw new SpelFout('Je melding mag hoogstens ' . num(TIP_MAX) . ' tekens lang zijn.');
    }

    $vandaag = (int) q_val(
        "SELECT COUNT(*) FROM `logs`
          WHERE `login` = ? AND `area` = 'tip' AND `time` > DATE_SUB(NOW(), INTERVAL 1 DAY)",
        [$user['login']],
        0
    );

    if ($vandaag >= TIP_PER_DAG) {
        throw new SpelFout('Je hebt vandaag al ' . TIP_PER_DAG
            . ' meldingen gestuurd. Probeer het morgen weer.');
    }

    $bericht = 'Van: ' . $user['login'] . "\n"
             . 'E-mail: ' . $user['email'] . "\n"
             . 'IP: ' . client_ip() . "\n\n"
             . $tip;

    db_transaction(static function () use ($user, $tip, $bericht): void {
        // Naar iedereen met beheerdersrechten, in plaats van naar één naam die
        // misschien niet meer bestaat.
        $beheerders = q_all('SELECT `login` FROM `users` WHERE `level` >= ?', [LEVEL_ADMIN]);

        foreach ($beheerders as $beheerder) {
            notify((string) $beheerder['login'], 'Tip of bugmelding', $bericht);
        }

        // Ook in het logboek, zodat de melding blijft bestaan als een
        // beheerder zijn postbus opruimt.
        log_action((string) $user['login'], 'tip', mb_substr($tip, 0, 255));
    });

    return 'Bedankt. Je melding is bij het beheer aangekomen.';
}
