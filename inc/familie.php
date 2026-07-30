<?php
/**
 * Gedeelde onderdelen voor families: rangen, lidmaatschap en de familiebank.
 */

declare(strict_types=1);

defined('BV_INC') || exit;

// Rangen binnen een familie.
const FAM_LID         = 1;
const FAM_CAPO        = 2;
const FAM_CONSIGLIERI = 3;
const FAM_HALFDON     = 4;
const FAM_DON         = 5;

const FAM_OPRICHTKOSTEN = 25_000_000;
const FAM_OPRICHT_XP    = 8000;        // minimaal Local Chief
const FAM_GROND_PRIJS   = 25_000;

/** Naam van elke familierang. */
function fam_rangen(): array
{
    return [
        FAM_LID         => 'Lid',
        FAM_CAPO        => 'Capo',
        FAM_CONSIGLIERI => 'Consiglieri',
        FAM_HALFDON     => 'Onderbaas',
        FAM_DON         => 'Don',
    ];
}

function fam_rangnaam(int $rang): string
{
    return fam_rangen()[$rang] ?? 'Geen';
}

/** De familie van deze speler, of null. */
function fam_van(array $user, bool $vergrendel = false): ?array
{
    if (($user['famillie'] ?? '') === '') {
        return null;
    }

    $sql = 'SELECT * FROM `famillie` WHERE `name` = ?' . ($vergrendel ? ' FOR UPDATE' : '');
    return q_row($sql, [$user['famillie']]);
}

/**
 * Eis een minimale familierang.
 *
 * @throws SpelFout
 */
function fam_eis_rang(array $user, int $minimum): array
{
    $familie = fam_van($user);

    if ($familie === null) {
        throw new SpelFout('Je zit niet in een familie.');
    }
    if ((int) $user['famrang'] < $minimum) {
        throw new SpelFout('Je hebt hiervoor minstens de rang ' . fam_rangnaam($minimum) . ' nodig.');
    }

    return $familie;
}

/** Aantal levende leden. */
function fam_aantal_leden(string $naam): int
{
    return (int) q_val(
        "SELECT COUNT(*) FROM `users` WHERE `famillie` = ? AND `status` = 'levend'", [$naam], 0
    );
}

/** Leden, gesorteerd op rang en ervaring. */
function fam_leden(string $naam): array
{
    return q_all(
        "SELECT `id`, `login`, `famrang`, `xp`, `stad`, `status`, `online`
           FROM `users` WHERE `famillie` = ?
       ORDER BY `famrang` DESC, `xp` DESC",
        [$naam]
    );
}

/**
 * Boek geld af van de familiebank, maar alleen als het er is.
 * De voorwaarde zit in de query, dus er zit geen gat tussen controle en boeking.
 */
function fam_afboeken(string $naam, int $bedrag): bool
{
    if ($bedrag < 1) {
        return false;
    }
    return q_count(
        'UPDATE `famillie` SET `bank` = `bank` - ? WHERE `name` = ? AND `bank` >= ?',
        [$bedrag, $naam, $bedrag]
    ) === 1;
}

function fam_bijschrijven(string $naam, int $bedrag): void
{
    if ($bedrag > 0) {
        q('UPDATE `famillie` SET `bank` = `bank` + ? WHERE `name` = ?', [$bedrag, $naam]);
    }
}

/** Leg een geldbeweging van de familie vast. */
function fam_log(string $familie, string $wie, int $bedrag, string $omschrijving): void
{
    q(
        'INSERT INTO `logs` (`time`, `login`, `person`, `code`, `area`, `com`)
              VALUES (NOW(), ?, ?, ?, ?, ?)',
        [$familie, $wie, $bedrag, 'famibank', mb_substr($omschrijving, 0, 255)]
    );
}
