<?php
/**
 * Geldintegriteit: een volledige speelsessie op een verse database, waarbij na
 * elke stap gecontroleerd wordt of de totale geldhoeveelheid klopt.
 *
 *     php tests/geld.php
 *
 * Dit is de belangrijkste test van het spel. In de oude versie zaten twee
 * lekken waar geld werd bijgedrukt (bij een moord en bij de dodelijke tomaat op
 * de schandpaal); zulke fouten vind je alleen door de balans te vergelijken.
 *
 * De captcha staat tijdens deze test op 'tekst', zodat de som uit de pagina te
 * lezen is. Dat is een gewone instelling, geen testluik.
 */

declare(strict_types=1);

require __DIR__ . '/_start.php';

const DB = 'bv_geldtest';

$db = verse_database(DB);

config_tijdelijk([
    "'name' => '" . BV_DB . "'" => "'name' => '" . DB . "'",
    "'debug'     => true"       => "'debug'     => true,\n    'captcha'   => 'tekst'",
]);

register_shutdown_function(static function (): void {
    tserver()->exec('DROP DATABASE IF EXISTS `' . DB . '`');
});

// --- Twee spelers ----------------------------------------------------------

$hash = password_hash('eenlangwachtwoord', PASSWORD_DEFAULT);

$maak = $db->prepare(
    "INSERT INTO users (login,pass,email,level,stad,geslacht,activated,status,health,xp,
                        zak,bank,kogels,wapon,se,start,online)
     VALUES (?,?,?,1,'Brussel','Man',1,'levend',100,25000,?,?,?,6,100,
             DATE_SUB(NOW(), INTERVAL 30 DAY), NOW())"
);
$maak->execute(['Speler', $hash, 's@example.com', 1_000_000, 0, 100_000]);
$maak->execute(['Doelwit', $hash, 'd@example.com', 777_777, 222_222, 0]);

$db->exec("INSERT INTO huizen (login,stad) VALUES ('Speler','Brussel'),('Doelwit','Brussel')");

login('Speler', 'eenlangwachtwoord');

// --- Bank ------------------------------------------------------------------

kop('bank: storten en opnemen laten het totaal ongemoeid');

$db->exec("UPDATE users SET zak=100000, bank=0, bc=NULL WHERE login='Speler'");
$voor = totaal_geld($db);

doe('bank.php', ['amount' => '60000', 'in' => '1']);
$db->exec("UPDATE users SET bc=NULL WHERE login='Speler'");
doe('bank.php', ['amount' => '20000', 'out' => '1']);

$u = $db->query("SELECT zak, bank FROM users WHERE login='Speler'")->fetch();

check('zak 60.000 en bank 40.000', (int) $u['zak'] === 60000 && (int) $u['bank'] === 40000,
    json_encode($u));
check('totaal onveranderd', totaal_geld($db) === $voor);

$db->exec("UPDATE users SET bc=NULL WHERE login='Speler'");
$r = doe('bank.php', ['amount' => '999999', 'out' => '1']);
check('te veel opnemen wordt geweigerd', str_contains(melding($r['body']), '[fout]'));

// --- Winkel ----------------------------------------------------------------

kop('winkel: geld verdwijnt uit het spel, precies het bedrag');

$db->exec("UPDATE users SET zak=50000000, wapon=0 WHERE login='Speler'");
$voor = totaal_geld($db);

$r = doe('shop.php', ['actie' => 'koop_item', 'soort' => 'att', 'nr' => '1']);
$na = totaal_geld($db);

check('aankoop gelukt', str_contains(melding($r['body']), '[ok]'), melding($r['body']));
check('totaal daalt met de koopprijs', $voor - $na === 25000, 'verschil ' . ($voor - $na));

// --- Casino ----------------------------------------------------------------

kop('casino: wat de speler verliest komt in de kas');

$db->exec("UPDATE users SET zak=1000000 WHERE login='Speler'");
haal('slots.php');   // maakt het casino aan als het er nog niet is
$db->exec("UPDATE casino SET owner='Doelwit', winst=0, inzet=1000
            WHERE spel='fruitmachine' AND stad='Brussel'");
$db->exec("UPDATE users SET bank=10000000 WHERE login='Doelwit'");

for ($i = 0; $i < 15; $i++) {
    $h = haal('slots.php');
    if (!str_contains($h['body'], 'name="inzet"')) {
        break;
    }
    haal('slots.php', ['_token' => tok($h['body']), 'actie' => 'speel',
        'inzet' => '1000', 'verify' => som($h['body'])]);
}

$zak   = (int) $db->query("SELECT zak FROM users WHERE login='Speler'")->fetchColumn();
$winst = (int) $db->query("SELECT winst FROM casino WHERE spel='fruitmachine'")->fetchColumn();

check('speler plus kas is nog steeds 1.000.000', $zak + $winst === 1_000_000,
    "zak {$zak}, kas {$winst}");

// --- Moord -----------------------------------------------------------------

kop('moord: de buit wordt verplaatst, niet verdubbeld');

$db->exec("UPDATE users SET zak=1000000, bank=0, kogels=100000, health=100, kc=NULL,
                            start=DATE_SUB(NOW(), INTERVAL 30 DAY) WHERE login='Speler'");
$db->exec("UPDATE users SET zak=777777, bank=222222, health=1, defence=0, guard=0,
                            status='levend', testament='', kogels=0, bf=0,
                            start=DATE_SUB(NOW(), INTERVAL 30 DAY) WHERE login='Doelwit'");

$voor = totaal_geld($db);

$h = haal('kill.php?x=Doelwit');
$r = haal('kill.php?x=Doelwit', ['_token' => tok($h['body']), 'victim' => 'Doelwit',
    'kogels' => '50000', 'message' => 'Dag.', 'verify' => som($h['body'])]);

$dood = $db->query("SELECT status, zak, bank FROM users WHERE login='Doelwit'")->fetch();
$na   = totaal_geld($db);

check('doelwit is dood', $dood['status'] === 'dood', melding($r['body']));
check('zak en bank van het slachtoffer op nul',
    (int) $dood['zak'] === 0 && (int) $dood['bank'] === 0, json_encode($dood));
check('totaal daalt met precies het banksaldo', $voor - $na === 222222,
    'verschil ' . ($voor - $na));
check('er is geen geld bijgekomen', $na <= $voor);

// --- Huis --------------------------------------------------------------------

kop('huis: geen gratis huis bij registratie of herstart');

$vraagHuizen = $db->prepare('SELECT COUNT(*) FROM huizen WHERE login = ?');

// Registratie: een gloednieuwe speler krijgt geen huis cadeau dat meteen
// weer verkocht kan worden voor gratis startgeld.
$nieuweLogin = 'Huistest' . random_int(10000, 99999);
doe('register.php', [
    'gebruiker'   => $nieuweLogin,
    'pass'        => 'eenlangwachtwoord',
    'passconfirm' => 'eenlangwachtwoord',
    'email'       => strtolower($nieuweLogin) . '@voorbeeld.test',
    'geslacht'    => 'Man',
], nieuwe_sessie());

$vraagHuizen->execute([$nieuweLogin]);
check('nieuwe speler krijgt geen gratis huis', (int) $vraagHuizen->fetchColumn() === 0);

// Herstart na overlijden: Doelwit is hierboven vermoord. Diezelfde truc mag
// niet herhaalbaar zijn door telkens dood te gaan en opnieuw te beginnen.
$doelwitJar = login('Doelwit', 'eenlangwachtwoord');
doe('rip.php', [], $doelwitJar);

$vraagHuizen->execute(['Doelwit']);
check('herstart na overlijden geeft geen gratis huis', (int) $vraagHuizen->fetchColumn() === 0);

// Registreren en inloggen als Doelwit hebben de gedeelde standaardsessie
// verlegd; de tests hierna verwachten weer Speler als ingelogde speler.
login('Speler', 'eenlangwachtwoord');

// --- Promotie ----------------------------------------------------------------

kop('promotie: bericht ook zonder (betalende) familie');

$db->exec("UPDATE users SET laatste_rang=0, xp=25000, famillie='' WHERE login='Speler'");
$db->exec("DELETE FROM messages WHERE `to`='Speler' AND subject='Promotie'");

haal('home.php');

$aantal = (int) $db->query(
    "SELECT COUNT(*) FROM messages WHERE `to`='Speler' AND subject='Promotie'"
)->fetchColumn();
check('promotiebericht zonder familie', $aantal > 0);

// --- Auto stelen -------------------------------------------------------------

kop('auto stelen: slaagkans daalt nooit als xp stijgt, en neemt af van boven naar onder');

function auto_percentages(string $html): array
{
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
    libxml_clear_errors();

    $percentages = [];
    foreach ($doc->getElementsByTagName('td') as $td) {
        if ($td->getAttribute('class') === 'getal') {
            $percentages[] = (int) rtrim(trim($td->textContent), '%');
        }
    }
    return $percentages;
}

$zetXp  = $db->prepare("UPDATE users SET xp = ? WHERE login = 'Speler'");
$reeksen = [];

foreach ([0, 50, 100, 300, 600, 1000] as $xp) {
    $zetXp->execute([$xp]);
    $reeksen[$xp] = auto_percentages(haal('nickacar.php')['body']);
}

$kolommen = ['parkeerplaats', 'woonwijk', 'tankstation', 'garage van een speler'];

foreach ($kolommen as $index => $naam) {
    $vorige = null;
    $gestegen = false;

    foreach ($reeksen as $xp => $rij) {
        $waarde = $rij[$index] ?? null;
        check($naam . ': percentage aanwezig bij xp ' . $xp, $waarde !== null);

        if ($vorige !== null) {
            check($naam . ': ' . $vorige . '% bij lagere xp stijgt niet naar ' . $waarde . '% bij xp ' . $xp,
                $waarde >= $vorige, $vorige . '% -> ' . $waarde . '%');
            if ($waarde > $vorige) {
                $gestegen = true;
            }
        }
        $vorige = $waarde;
    }

    check($naam . ': stijgt ergens tussen xp 0 en 1000', $gestegen);
}

// De makkelijkste optie staat bovenaan: bij elke xp mag de slaagkans nooit
// hoger zijn verderop in de lijst (parkeerplaats, woonwijk, tankstation,
// garage van een speler — in die volgorde). Bij de vloer (1%, lage xp) en
// het plafond (30%, hoge xp) mogen kolommen gelijk zijn; daartussenin moet
// er minstens één xp-waarde zijn waar het echt afneemt.
foreach ($reeksen as $xp => $rij) {
    for ($i = 1; $i < count($kolommen); $i++) {
        check($kolommen[$i - 1] . ' >= ' . $kolommen[$i] . ' bij xp ' . $xp,
            ($rij[$i - 1] ?? -1) >= ($rij[$i] ?? -1),
            ($rij[$i - 1] ?? '?') . '% vs ' . ($rij[$i] ?? '?') . '%');
    }
}

for ($i = 1; $i < count($kolommen); $i++) {
    $strikt = false;
    foreach ($reeksen as $rij) {
        if (($rij[$i - 1] ?? -1) > ($rij[$i] ?? -1)) {
            $strikt = true;
            break;
        }
    }
    check($kolommen[$i - 1] . ' ligt écht boven ' . $kolommen[$i] . ' bij minstens één xp', $strikt);
}

// --- Cron ------------------------------------------------------------------

kop('cron: alle taken draaien');

$db->exec("UPDATE cron SET time='1970-01-01 00:00:01'");
$r = haal('cron.php?key=testsleutel');

$blijven = (int) $db->query("SELECT COUNT(*) FROM cron WHERE time='1970-01-01 00:00:01'")
    ->fetchColumn();

check('elke taak heeft gedraaid', $blijven === 0, $blijven . ' niet gedraaid');

samenvatting();
