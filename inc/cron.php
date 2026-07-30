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

        // Opbrengst van de kogelfabriek.
        'uur' => [3600, static function (): void {
            q("UPDATE `casino` SET `winst` = `winst` + 100 WHERE `spel` = 'kogelfabriek'");
        }],

        // Dagelijkse reset: familietellers, krasloten, markt­prijzen.
        'day' => [86400, static function (): void {
            q('UPDATE `famillie` SET `crusher` = 0, `aantal` = 0');
            q('DELETE FROM `kras`');

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
    ];
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
