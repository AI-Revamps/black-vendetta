<?php
/**
 * Periodieke spelupdates.
 *
 * Deze taken draaien op twee manieren, in te stellen met `cron_mode` in config:
 *
 *  - 'request' : ze liften mee op gewone paginabezoeken. Werkt op elke host,
 *                ook zonder cronjob-ondersteuning. Dit is de standaard.
 *  - 'cron'    : alleen via /cron.php, aangeroepen door de cronjob van je host.
 *
 * In beide gevallen zorgt een database-lock ervoor dat een taak nooit twee keer
 * tegelijk draait, ook niet bij honderd gelijktijdige bezoekers.
 */

declare(strict_types=1);

defined('BV_INC') || exit;

/**
 * Alle taken die aan de beurt zijn uitvoeren.
 *
 * @param bool $force Negeer cron_mode (gebruikt door /cron.php zelf).
 * @return string[] Namen van de taken die daadwerkelijk gedraaid hebben.
 */
function cron_run(bool $force = false): array
{
    $mode = (string) config('cron_mode', 'request');

    if (!$force && !in_array($mode, ['request', 'both'], true)) {
        return [];
    }

    $gedraaid = [];
    foreach (cron_tasks() as $naam => [$interval, $taak]) {
        if (cron_due($naam, $interval) && cron_claim($naam, $interval)) {
            try {
                $taak();
                $gedraaid[] = $naam;
            } catch (Throwable $e) {
                error_log("[cron] taak '{$naam}' mislukt: " . $e->getMessage());
            } finally {
                db_release_lock('bv_cron_' . $naam);
            }
        }
    }
    return $gedraaid;
}

/**
 * De taken zelf: naam => [interval in seconden, functie].
 */
function cron_tasks(): array
{
    return [
        // Kogelvoorraad en -prijs per stad verversen.
        'kogels' => [130, static function (): void {
            q('UPDATE `stad` SET `kogels` = 100, `prijs` = FLOOR(500 + RAND() * 1000)');

            // Wees-records opruimen.
            q("DELETE FROM `messages` WHERE `to` = ''");
            q("DELETE FROM `iplog`    WHERE `login` = ''");
            q('DELETE FROM `jail`     WHERE `time` < NOW()');
            q('DELETE FROM `temp`     WHERE `time` < DATE_SUB(NOW(), INTERVAL 2 DAY)');
        }],

        // Detectives die klaar zijn met zoeken melden zich.
        // In de oude versie stond dit midden in config.php en draaide het dus
        // bij elk paginabezoek van elke speler opnieuw.
        'detective' => [60, static function (): void {
            detectives_afhandelen();
        }],

        // Opbrengst van de kogelfabriek.
        'uur' => [3600, static function (): void {
            q("UPDATE `casino` SET `winst` = `winst` + 100 WHERE `spel` = 'kogelfabriek'");
        }],

        // Dagelijkse reset: familietellers, krasloten, markt­prijzen.
        'day' => [86400, static function (): void {
            q('UPDATE `famillie` SET `crusher` = 0, `aantal` = 0');
            q('DELETE FROM `kras`');

            // Resten van omgelegde spelers opruimen. Dit stond in
            // adm-cleandb.php, een pagina zonder enige rechtencontrole: wie de
            // URL kende kon hem aanroepen en er werden rijen verwijderd.
            //
            // speler_herstarten() ruimt dit ook op zodra iemand doorstart; dit
            // is voor accounts die blijven liggen.
            q("DELETE g FROM `garage` g JOIN `users` u ON u.`login` = g.`login`
                WHERE u.`status` = 'dood'");
            q("DELETE f FROM `friends` f JOIN `users` u ON u.`login` = f.`login`
                WHERE u.`status` = 'dood'");
            q("DELETE f FROM `friends` f JOIN `users` u ON u.`login` = f.`friend`
                WHERE u.`status` = 'dood'");
            q("DELETE h FROM `hitlist` h JOIN `users` u ON u.`login` = h.`login`
                WHERE u.`status` = 'dood'");

            // Het IP-logboek blijft juist staan. Dat stond hier eerder ook bij
            // de opruiming, maar sinds er nog maar één account per IP-adres
            // mag bestaan is dit hét spoor waarmee een moderator meerdere
            // accounts kan herkennen. Weggooien op leeftijd, niet op status.
            q('DELETE FROM `iplog` WHERE `time` < DATE_SUB(NOW(), INTERVAL 90 DAY)');

            // Nieuwe drank- en drugsprijzen per stad.
            foreach (cities() as $stad) {
                q(
                    'UPDATE `stad`
                        SET `drugsp` = FLOOR(6000 + RAND() * 9000),
                            `drankp` = FLOOR(1000 + RAND() * 5000)
                      WHERE `stad` = ?',
                    [$stad]
                );
            }
        }],

        // Wekelijkse eerpunten op basis van ervaring.
        'week' => [604800, static function (): void {
            q(
                "UPDATE `users`
                    SET `rp` = LEAST(255, ROUND(`xp` / 50))
                  WHERE `activated` = 1 AND `status` = 'levend' AND `xp` >= 50"
            );
        }],

        // Wekelijkse loterijtrekking. Deze taak ontbrak volledig: spelers
        // konden loten kopen maar er werd nooit getrokken.
        'loterij' => [604800, static function (): void {
            loterij_trekken();
        }],
    ];
}

/**
 * Handel afgelopen detectiveopdrachten af.
 *
 * Een detective zoekt in één stad. Blijkt het doelwit daar te zijn wanneer hij
 * terugkomt, dan meldt hij dat aan zijn opdrachtgever. Daarna verdwijnt de
 * opdracht, gevonden of niet.
 */
function detectives_afhandelen(): void
{
    $klaar = q_all(
        'SELECT d.*, u.`stad` AS `huidige_stad`, u.`status`
           FROM `detectives` d
      LEFT JOIN `users` u ON u.`login` = d.`naar`
          WHERE d.`time` <= NOW()'
    );

    foreach ($klaar as $opdracht) {
        if (($opdracht['huidige_stad'] ?? null) === $opdracht['stad']
            && ($opdracht['status'] ?? '') === 'levend') {

            notify((string) $opdracht['van'], 'Detective',
                'Je detective heeft ' . $opdracht['naar'] . ' gevonden in ' . $opdracht['stad'] . '.');

            // Bij een treffer hebben de andere detectives op hetzelfde doelwit
            // geen zin meer.
            q('DELETE FROM `detectives` WHERE `van` = ? AND `naar` = ?',
                [$opdracht['van'], $opdracht['naar']]);
        }
    }

    // Wat over is, is zonder resultaat teruggekomen.
    q('DELETE FROM `detectives` WHERE `time` <= NOW()');
}

/**
 * De prijzen van de loterij, in volgorde van trekking.
 *
 * soort: 'jackpot' (alle inleg), 'geld', 'kogels' of 'auto'.
 */
function loterij_prijzen(): array
{
    return [
        ['soort' => 'jackpot', 'waarde' => 0,         'omschrijving' => 'De jackpot: alle inleg samen'],
        ['soort' => 'geld',    'waarde' => 1_000_000, 'omschrijving' => '€ 1.000.000'],
        ['soort' => 'geld',    'waarde' => 500_000,   'omschrijving' => '€ 500.000'],
        ['soort' => 'geld',    'waarde' => 250_000,   'omschrijving' => '€ 250.000'],
        ['soort' => 'auto',    'waarde' => 0,         'omschrijving' => 'Mercedes W124 Avus Streamling, 0% schade'],
        ['soort' => 'kogels',  'waarde' => 3000,      'omschrijving' => '3.000 kogels'],
        ['soort' => 'kogels',  'waarde' => 2000,      'omschrijving' => '2.000 kogels'],
        ['soort' => 'kogels',  'waarde' => 1000,      'omschrijving' => '1.000 kogels'],
        ['soort' => 'kogels',  'waarde' => 500,       'omschrijving' => '500 kogels'],
    ];
}

/**
 * Trek de loterij: wijs per prijs een lot aan, betaal uit en maak de pot leeg.
 *
 * Een lot kan maar één prijs winnen. Zijn er minder loten dan prijzen, dan
 * blijven de resterende prijzen liggen tot de volgende trekking.
 */
function loterij_trekken(): void
{
    db_transaction(static function (): void {
        $loten = q_all('SELECT `id`, `login` FROM `loterij` ORDER BY RAND()');

        // Onder de tien loten is er te weinig inleg; laat staan tot volgende keer.
        if (count($loten) < 10) {
            return;
        }

        $pot = count($loten) * 10_000;

        foreach (loterij_prijzen() as $prijs) {
            $lot = array_shift($loten);
            if ($lot === null) {
                break;
            }

            $winnaar = lock_user_by_login((string) $lot['login']);
            if ($winnaar === null || $winnaar['status'] !== 'levend') {
                continue;
            }

            loterij_uitbetalen($winnaar, $prijs, $pot);
        }

        q('DELETE FROM `loterij`');
    });
}

/** Keer één prijs uit aan de winnaar. */
function loterij_uitbetalen(array $winnaar, array $prijs, int $pot): void
{
    $naam = (string) $winnaar['login'];

    switch ($prijs['soort']) {
        case 'jackpot':
            bijschrijven((int) $winnaar['id'], $pot, 'zak');
            notify($naam, 'Loterij', 'Je hebt de jackpot gewonnen: ' . money($pot) . '!');
            break;

        case 'geld':
            bijschrijven((int) $winnaar['id'], (int) $prijs['waarde'], 'zak');
            notify($naam, 'Loterij', 'Je hebt ' . money((int) $prijs['waarde'])
                . ' gewonnen in de loterij.');
            break;

        case 'kogels':
            bijschrijven((int) $winnaar['id'], (int) $prijs['waarde'], 'kogels');
            notify($naam, 'Loterij', 'Je hebt ' . num((int) $prijs['waarde'])
                . ' kogels gewonnen in de loterij.');
            break;

        case 'auto':
            $model = q_row("SELECT * FROM `cars` ORDER BY `waarde` DESC LIMIT 1");
            if ($model !== null) {
                q('INSERT INTO `garage` (`login`, `naam`, `waarde`, `damage`, `stad`) VALUES (?, ?, ?, 0, ?)',
                    [$naam, $model['naam'], $model['waarde'], $winnaar['stad']]);
                notify($naam, 'Loterij', 'Je hebt een ' . $model['auto']
                    . ' gewonnen in de loterij. Hij staat in je garage in ' . $winnaar['stad'] . '.');
            }
            break;
    }

    log_action($naam, 'loterij', 'Prijs gewonnen: ' . $prijs['omschrijving']);
}

/** Is deze taak aan de beurt? Goedkope controle, zonder lock. */
function cron_due(string $naam, int $interval): bool
{
    $verstreken = q_val(
        'SELECT UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`time`) FROM `cron` WHERE `name` = ?',
        [$naam]
    );

    // Onbekende taak: rij aanmaken en meteen laten draaien.
    if ($verstreken === null) {
        q("INSERT IGNORE INTO `cron` (`name`, `time`) VALUES (?, '1970-01-01 00:00:01')", [$naam]);
        return true;
    }

    return (int) $verstreken >= $interval;
}

/**
 * Claim de taak: pak de lock en zet de tijd vooruit in één UPDATE. Alleen de
 * request die daadwerkelijk een rij wijzigt mag de taak uitvoeren; de rest
 * ziet nul gewijzigde rijen en slaat over.
 */
function cron_claim(string $naam, int $interval): bool
{
    if (!db_try_lock('bv_cron_' . $naam)) {
        return false;
    }

    $geclaimd = q_count(
        'UPDATE `cron` SET `time` = NOW()
          WHERE `name` = ? AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`time`) >= ?',
        [$naam, $interval]
    ) > 0;

    if (!$geclaimd) {
        db_release_lock('bv_cron_' . $naam);
    }
    return $geclaimd;
}
