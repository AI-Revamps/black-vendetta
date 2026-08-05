# Economiebalans vroege spel — Implementatieplan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** de vroege-spel-economie herbalanceren zodat een nieuwe speler binnen 30-60 minuten actief spelen genoeg kan verdienen voor het goedkoopste wapen (€10.000), puur uit misdaad — plus de rijles- en voertuigprijzen die reizen (en dus drugs/drank-handel) nu wekenlang onbereikbaar maken.

**Architectuur:** drie onafhankelijke wijzigingen in bestaande bestanden (`crime.php`, `rijbewijs.php`, `install/schema.sql`) plus regressietests die de doelstellingen daadwerkelijk narekenen in plaats van er alleen op te vertrouwen. Geen nieuwe tabellen, geen nieuwe pagina's.

**Tech Stack:** vanilla PHP 8, MySQL, de bestaande testscripts in `/tests` (HTTP-integratietests tegen een draaiende server).

**Ontwerpdocument:** [`docs/superpowers/specs/2026-08-05-economie-balans-design.md`](../specs/2026-08-05-economie-balans-design.md) — daar staat de onderbouwing van elke keuze hieronder. Dit plan zet dat ontwerp om in concrete stappen.

## Global Constraints

- Geen frameworks, geen Composer, geen bouwstap (project-eis uit `CLAUDE.md`).
- Alles wat een speler ziet: in het Nederlands.
- **Bewerk PHP-bestanden nooit via PowerShell** — gebruik de Write/Edit-tools, anders krijgt het bestand een BOM voor `<?php`.
- Draai voor elke commit minimaal `php tests/rook.php`; draai `php tests/geld.php` voor elke taak in dit plan (dat is waar de nieuwe tests in staan).
- De exacte getallen in dit plan zijn een onderbouwd startpunt (zie ontwerpdocument), geen eindpunt: als een simulatietest een andere uitkomst laat zien dan verwacht, stel dan de constanten bij totdat de test slaagt — pas daarna de tabel in dit plan/het ontwerp bij als de uiteindelijke waarden afwijken.
- Reikwijdte is precies wat in het ontwerpdocument staat: misdaad, rijlessen, voertuigprijzen. Drugs/drank-prijzen, kraslot, wapen/bescherming/huisprijzen blijven onaangeroerd.

---

### Task 1: Misdaadopbrengsten, slaagkansen en xp-winst

**Root cause / doel:** bij xp 0 is de slaagkans voor elke misdaad 1% (de `max(1, ...)`-vloer), en de opbrengst van de meest toegankelijke misdaad ("steel van een kind") is €1-10. Elke geslaagde misdaad geeft precies +1 xp, ongeacht het risico. Dat maakt misdaad geen levensvatbare vroege inkomstenbron. Zie het ontwerpdocument voor de volledige onderbouwing van de nieuwe waarden.

**Files:**
- Modify: `crime.php:62-145` (`misdaden()`), `crime.php:227-245` (`misdaad_geslaagd()`)
- Test: `tests/geld.php`

- [x] **Stap 1: Schrijf de falende test**

Voeg in `tests/geld.php` een nieuwe sectie toe, na de bestaande "Wapens: effect"-sectie en vóór "Cron":

```php
// --- Misdaad: opbrengst, kans en xp -----------------------------------------

kop('misdaad: nieuwe speler kan binnen ~40 pogingen het goedkoopste wapen verdienen');

$db->exec("UPDATE users SET xp=0, zak=0, crime=NULL WHERE login='Speler'");

for ($poging = 0; $poging < 40; $poging++) {
    $db->exec("UPDATE users SET crime=NULL WHERE login='Speler'");
    doe('crime.php', ['crime' => 'juwelier']);
}

$zak = (int) $db->query("SELECT zak FROM users WHERE login='Speler'")->fetchColumn();
$xp  = (int) $db->query("SELECT xp FROM users WHERE login='Speler'")->fetchColumn();

check('minstens €5.000 verdiend binnen 40 pogingen op "beroof een juwelier"',
    $zak >= 5000, 'zak ' . $zak . ', xp ' . $xp);
```

- [x] **Stap 2: Draai de test, bevestig FAIL**

```bash
php tests/geld.php
```
Verwacht: FAIL — met de huidige waarden (kans 1% bij xp 0, opbrengst €500-1.000) ligt de verwachte opbrengst na 40 pogingen ruim onder €5.000.

- [x] **Stap 3: Pas `misdaden()` aan**

In `crime.php`, vervang de definities van `kind`, `puber` en `juwelier` (laat `bar` en `member` ongewijzigd):

```php
'kind' => [
    'label'     => 'Steel van een kind',
    'kans'      => static fn (int $xp): int => min(60, 20 + (int) round($xp / 2)),
    'opbrengst' => static fn (): int => random_int(20, 60),
    'xp'        => 2,
    'berichten' => [
        'Het kind had niets bij zich.',
        'Het kind begon te schreeuwen, je bent er maar vandoor gegaan.',
        'Je greep naar de portemonnee, maar het kind rende weg.',
        'Een voorbijganger zag je bezig en riep om hulp. Je koos het hazenpad.',
        'Je werd betrapt terwijl je het kind vastgreep. De politie nam je mee.',
    ],
    'cel'    => 4,
    'schade' => null,
],

'puber' => [
    'label'     => 'Steel van een puber',
    'kans'      => static fn (int $xp): int => min(50, 15 + (int) round($xp / 3)),
    'opbrengst' => static fn (): int => random_int(60, 200),
    'xp'        => 3,
    'berichten' => [
        'Hij had niets bij zich.',
        'De puber kende je nog van een eerdere ruzie en kwam woedend op je af. Je moest vluchten.',
        'Ze zag je aankomen en stapte een winkel binnen.',
        'Hij liep snel weg.',
        'Je had zijn portemonnee al te pakken toen de politie arriveerde. Je zit nu vast.',
        'Zijn vrienden kwamen van alle kanten en sloegen je in elkaar.',
    ],
    'cel'    => 4,
    'schade' => 5,
],

'juwelier' => [
    'label'     => 'Beroof een juwelier',
    'kans'      => static fn (int $xp): int => min(40, 12 + (int) round($xp / 5)),
    'opbrengst' => static fn (): int => random_int(1500, 3000),
    'xp'        => 5,
    'berichten' => [
        'Er liep net een andere gangster met een lading juwelen naar buiten.',
        'De juwelier haalde een geweer van achter de toonbank en begon te schieten. Je kon net op tijd wegkomen.',
        'De zaak was gesloten.',
        'Je wilde net naar binnen toen er een politiewagen voorbijreed.',
        'Het alarm ging af zodra je de vitrine opende. De politie stond binnen een minuut buiten.',
        'Je was de winkel uit, maar struikelde over een zwerver. Hij griste de juwelen mee en verdween.',
    ],
    'cel'    => 4,
    'schade' => null,
],
```

De `'label'`, `'berichten'`, `'cel'` en `'schade'` velden blijven letterlijk hetzelfde als nu — alleen `'kans'`, `'opbrengst'` veranderen en er komt een nieuw veld `'xp'` bij. Laat `'bar'` en `'member'` volledig ongewijzigd staan.

- [x] **Stap 4: Pas `misdaad_geslaagd()` aan om de nieuwe `xp`-waarde te gebruiken**

Vervang in `crime.php`:

```php
$buit = ($def['opbrengst'])();
q('UPDATE `users` SET `zak` = `zak` + ?, `xp` = `xp` + 1 WHERE `id` = ?', [$buit, $user['id']]);
```

door:

```php
$buit   = ($def['opbrengst'])();
$xpWinst = (int) ($def['xp'] ?? 1);
q('UPDATE `users` SET `zak` = `zak` + ?, `xp` = `xp` + ? WHERE `id` = ?', [$buit, $xpWinst, $user['id']]);
```

- [x] **Stap 5: Draai de test opnieuw**

```bash
php tests/geld.php
```

Bekijk de daadwerkelijke waarde van `zak` in de testuitvoer. Ligt die ruim boven €5.000 (rond de €8.000-12.000)? Dan is de ondergrens in de test goed gekozen — laat hem staan. Faalt de test nog steeds, of slaagt hij met een waarde die nauwelijks boven €5.000 uitkomt? Verhoog dan de opbrengst- of kanswaarden in `misdaden()` totdat een normale simulatie ruim boven de grens uitkomt, en draai de test opnieuw tot hij stabiel slaagt.

- [x] **Stap 6: Draai de volledige suite**

```bash
php tests/rook.php
php tests/geld.php
```

- [x] **Stap 7: Commit**

```bash
git add crime.php tests/geld.php
git commit -m "Misdaadopbrengsten, slaagkansen en xp-winst herbalanceren"
```

---

### Task 2: Rijlessen — prijs en voortgang per les

**Root cause / doel:** bij €25.000 per les en 0,5%-3,0% voortgang per geslaagde les (2/3 kans) kost een rijbewijs gemiddeld ~55-60 geslaagde lessen — tot €1.400.000+. Dat maakt reizen (en dus drugs/drank-handel) in de praktijk wekenlang onbereikbaar, los van de xp-drempel voor handel.

**Files:**
- Modify: `rijbewijs.php:30-32` (constanten), `rijbewijs.php:196-211` (`rijden()`)
- Test: `tests/geld.php`

- [x] **Stap 1: Schrijf de falende test**

Voeg in `tests/geld.php` toe, na de misdaad-sectie uit Task 1:

```php
// --- Rijlessen ---------------------------------------------------------------

kop('rijlessen: een rijbewijs is haalbaar zonder een fortuin uit te geven');

$db->exec("UPDATE users SET zak=300000, rijbewijs=0, rijvord=0, lessen=0,
                            rijbewijstijd=NULL WHERE login='Speler'");

$uitgegeven = 0;
$klaar      = false;

for ($poging = 0; $poging < 40 && !$klaar; $poging++) {
    $lessen = (int) $db->query("SELECT lessen FROM users WHERE login='Speler'")->fetchColumn();

    if ($lessen < 1) {
        doe('rijbewijs.php', ['actie' => 'lessen', 'aantal' => '1']);
        $uitgegeven += 5000;
        continue;
    }

    $db->exec("UPDATE users SET rijbewijstijd=NULL WHERE login='Speler'");
    doe('rijbewijs.php', ['actie' => 'rijden']);
    $klaar = (int) $db->query("SELECT rijbewijs FROM users WHERE login='Speler'")->fetchColumn() === 1;
}

check('rijbewijs gehaald binnen 40 iteraties', $klaar);
check('totale lesgeld blijft onder €150.000', $uitgegeven <= 150000, (string) $uitgegeven);
```

- [x] **Stap 2: Draai de test, bevestig FAIL**

```bash
php tests/geld.php
```
Verwacht: FAIL op minstens één van de twee checks — met de huidige €25.000/les en 0,5%-3,0% voortgang kost dit ruim meer dan €150.000 en/of lukt het niet binnen 40 iteraties.

- [x] **Stap 3: Pas de constanten in `rijbewijs.php` aan**

```php
const LES_PRIJS      = 5_000;
const LES_MAX        = 50;      // hoeveel lessen je tegelijk kunt kopen
const LES_WACHTTIJD  = 300;     // vijf minuten tussen twee lessen
const RIJVORD_KLAAR  = 100.0;   // bij dit percentage haal je je rijbewijs
```

(alleen `LES_PRIJS` verandert; de andere drie blijven zoals ze zijn)

- [x] **Stap 4: Pas de voortgang per geslaagde les aan in `rijden()`**

Vervang:

```php
// Vordering in tienden van procenten, zodat het niet te snel gaat.
$winst = random_int(5, 30) / 10;
```

door:

```php
// Vordering in hele procenten.
$winst = (float) random_int(10, 25);
```

- [x] **Stap 5: Draai de test opnieuw**

```bash
php tests/geld.php
```

Zoals bij Task 1: klopt de daadwerkelijke uitkomst (aantal iteraties, totaal lesgeld) met de bedoeling — een rijbewijs binnen een handvol lessen voor een paar tienduizend euro? Stel zo nodig `LES_PRIJS` of de spreiding in `rijden()` bij totdat de test stabiel en zinvol slaagt (niet nét onder de grens).

- [x] **Stap 6: Draai de volledige suite**

```bash
php tests/rook.php
php tests/geld.php
```

- [x] **Stap 7: Commit**

```bash
git add rijbewijs.php tests/geld.php
git commit -m "Rijlessen goedkoper en sneller: rijbewijs binnen een sessie haalbaar"
```

---

### Task 3: Voertuigprijzen

**Root cause / doel:** de goedkoopste reisoptie (Treinabonnement) kost nu €250.000, de duurste (Privé-Jet) €1.500.000. Zelfs met een rijbewijs op zak is dat, los van Task 2, nog steeds een muur voor een nieuwe speler. Alle vier de prijzen worden door 10 gedeeld; de onderlinge verhouding en de reistijden blijven ongewijzigd.

**Files:**
- Modify: `install/schema.sql` (tabel `items`, vier rijen met `type = 'trans'`)
- Test: `tests/geld.php`

- [x] **Stap 1: Schrijf de falende test**

Voeg in `tests/geld.php` toe, na de rijlessen-sectie uit Task 2:

```php
// --- Voertuigprijzen -----------------------------------------------------------

kop('voertuigen: prijzen passen bij de nieuwe verdiencurve');

$voertuigen = $db->query(
    "SELECT naam, aprijs FROM items WHERE type='trans' ORDER BY aprijs"
)->fetchAll();

$verwacht = [
    'Treinabonnement' => 25000,
    'Privé-Jet'        => 150000,
];

foreach ($voertuigen as $voertuig) {
    if (isset($verwacht[$voertuig['naam']])) {
        check($voertuig['naam'] . ' kost ' . $verwacht[$voertuig['naam']],
            (int) $voertuig['aprijs'] === $verwacht[$voertuig['naam']],
            (string) $voertuig['aprijs']);
    }
}

check('duurste voertuig kost hoogstens €200.000',
    (int) $voertuigen[count($voertuigen) - 1]['aprijs'] <= 200000,
    (string) $voertuigen[count($voertuigen) - 1]['aprijs']);
```

- [x] **Stap 2: Draai de test, bevestig FAIL**

```bash
php tests/geld.php
```

- [x] **Stap 3: Pas de prijzen aan in `install/schema.sql`**

In de `INSERT IGNORE INTO \`items\`` (rond regel 647), vervang de vier `'trans'`-rijen:

```sql
  (3,  1, 'trans', 'Treinabonnement',   25000,   20000, 3600.00),
  (4,  3, 'trans', 'Limousine',        100000,   75000, 1800.00),
  (8,  2, 'trans', 'Taxi',              75000,   60000, 2400.00),
  (10, 4, 'trans', 'Privé-Jet',        150000,  120000,  900.00),
```

De verkoopprijs (`vprijs`) schaalt mee in dezelfde verhouding als voorheen (80% van de koopprijs). De reistijd (laatste kolom) blijft ongewijzigd.

- [x] **Stap 4: Draai de test opnieuw, bevestig PASS**

```bash
php tests/geld.php
php tests/rook.php
```

- [x] **Stap 5: Commit**

```bash
git add install/schema.sql tests/geld.php
git commit -m "Voertuigprijzen door 10 gedeeld"
```

---

### Task 4: Volledige suite en terugkoppeling naar issue #33

- [x] **Stap 1: Draai de volledige testsuite**

```bash
for t in rook geld veiligheid opbouw adressen; do php tests/$t.php || break; done
```

- [x] **Stap 2: Vergelijk de daadwerkelijke simulatie-uitkomsten met het ontwerpdocument**

Noteer de werkelijke waarden uit de testuitvoer van Task 1-3 (verdiend bedrag na 40 misdaadpogingen, aantal rijlessen/kosten tot een rijbewijs). Wijken die relevant af van de schattingen in `docs/superpowers/specs/2026-08-05-economie-balans-design.md`? Werk dan de tabellen in dat document bij zodat het ontwerp de werkelijke, geteste uitkomst weerspiegelt.

- [x] **Stap 3: Commit als het ontwerpdocument is bijgewerkt**

```bash
git add docs/superpowers/specs/2026-08-05-economie-balans-design.md
git commit -m "Ontwerpdocument bijwerken met werkelijke simulatie-uitkomsten"
```

- [x] **Stap 4: Push en PR**

Volg de gebruikelijke workflow (zie eerdere branches in deze sessie): push de branch, open een PR met "Fixes #33" in de beschrijving en een korte samenvatting van de drie wijzigingen, en plaats een korte update op issue #33 zodra de PR er is.

---

## Volgorde en afhankelijkheden

Taken 1, 2 en 3 zijn onderling onafhankelijk (verschillende bestanden, geen gedeelde code) en kunnen in willekeurige volgorde, al is de volgorde hierboven (misdaad → rijlessen → voertuigen) het logisch opeenvolgende pad dat een speler ook daadwerkelijk aflegt. Taak 4 (suite + terugkoppeling + PR) komt na de andere drie.
