# Lokaal draaien en verder bouwen

## Opzetten

Je hebt PHP 8.1 of nieuwer nodig met `pdo_mysql` en `mbstring`, en een MySQL of
MariaDB. Op Windows werkt Laragon of XAMPP prima; die brengen allebei alles mee.

```bash
git clone <deze repo>
cd black-vendetta
```

Database aanmaken en het schema erin laden:

```sql
CREATE DATABASE bv_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
mysql -u root bv_dev < install/schema.sql
```

`inc/config.php` staat niet in de repo — er hoort een databasewachtwoord in, dus
hij staat in `.gitignore`. Er ligt een voorbeeld klaar:

```bash
cp inc/config.voorbeeld.php inc/config.php
```

Zet daarin de naam van je database goed; de rest kan blijven staan.

En starten:

```bash
php -S 127.0.0.1:8149 -t . tests/router.php
php tests/seed.php
```

Je logt in als `Baas` met `baaswachtwoord12345`. Zie
[tests/LEESMIJ.md](../tests/LEESMIJ.md) voor de andere accounts.

Je kunt ook `install/` doorlopen in plaats van het schema handmatig te laden;
die maakt `inc/config.php` voor je aan en zet meteen een eigenaarsaccount klaar.

## Alle instellingen in `inc/config.php`

Alles wat de code kan lezen. Wat niet in het bestand staat, valt terug op de
standaardwaarde.

| Sleutel | Standaard | Waarvoor |
|---|---|---|
| `db.host` `db.port` `db.name` `db.user` `db.pass` | — | de database |
| `site.url` | — | zonder afsluitende slash; alle links worden hierop gebouwd |
| `site.name` | `Black Vendetta` | in de kopbalk en in e-mail |
| `site.mail_from` `site.mail_from_name` | | afzender van e-mail |
| `site.mail_admin` | | waar foutmeldingen heen gaan |
| `site.timezone` | `Europe/Amsterdam` | tijdzone |
| `game.cities` | `[]` | de steden; zie [BEHEER.md](BEHEER.md) voor wijzigen |
| `game.start_money` | `1000` | startgeld van een nieuwe speler |
| `game.require_activation` | `true` | activatiemail bij registratie |
| `game.allow_multi_accounts` | `false` | meerdere accounts per IP en e-mail |
| `cron_mode` | `request` | `request`, `cron` of `both` |
| `cron_key` | leeg | het geheim in het cron-adres |
| `mooie_urls` | `false` | adressen zonder `.php`; vereist `mod_rewrite` |
| `captcha` | plaatje als `gd` er is | zet op `tekst` bij een onleesbaar plaatje |
| `debug` | `false` | toont databasefouten; **nooit aan op een live site** |

Instellingen die de beheerder tijdens het spelen wil kunnen wijzigen staan
niet hier maar in de tabel `instellingen`, te bereiken via `instelling()`.
De grens: staat het in dit bestand, dan hoort er een FTP-sessie bij.

## Testen

```bash
php tests/rook.php        # elke pagina laadt schoon
php tests/geld.php        # de geldbalans klopt
php tests/veiligheid.php  # XSS, rechten, CSRF
php tests/opbouw.php      # de indeling per toestand
php tests/adressen.php    # adressen zonder .php
```

`rook.php` is de test die je na élke wijziging draait; hij kost een paar
seconden en vangt alles wat echt kapot is. Zie
[tests/LEESMIJ.md](../tests/LEESMIJ.md) voor de rest.

## Een pagina toevoegen

Alle pagina's volgen hetzelfde stramien. Zie
[ARCHITECTUUR.md](ARCHITECTUUR.md) voor de uitleg per onderdeel; kort:

```php
<?php
declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

$user = require_login();

if (is_dead()) {
    redirect('rip.php');
}
block_if_jailed();

if (is_post()) {
    csrf_check();

    $bedrag = int_input('bedrag', 0, 0);

    try {
        db_transaction(static function () use ($user, $bedrag) {
            lock_user((int) $user['id']);

            if (!afboeken((int) $user['id'], $bedrag, 'zak')) {
                throw new SpelFout('Zoveel geld heb je niet op zak.');
            }
            // ... en hier waar het geld heen gaat
        });

        flash('ok', 'Gelukt.');
    } catch (SpelFout $e) {
        flash('fout', $e->getMessage());
    }

    redirect('mijnpagina.php');
}

layout_header('Titel van de pagina');
panel_open('Kopje');
// ...
panel_close();
layout_footer();
```

Vier dingen die niet mogen ontbreken:

1. **`csrf_check()` bovenaan elke POST-verwerking.** Zonder dat kan een andere
   site het formulier namens de speler versturen.
2. **Na een POST altijd `redirect()`.** Anders herhaalt vernieuwen de handeling.
3. **Alles wat een speler heeft ingetypt door `e()`**, of door `bericht_html()`
   als er opmaak in mag.
4. **Geld nooit met een losse `UPDATE`.** Gebruik `afboeken()`; die controleert
   het saldo binnen de query zelf, zodat twee gelijktijdige verzoeken niet
   allebei kunnen slagen.

Neem de pagina daarna op in het menu in `inc/layout.php`, en voeg hem toe aan de
spelregels in `help.php` als een speler er iets over moet weten.

## Werkwijze

- **Kleine commits, in het Nederlands.** De code, de commentaren en de
  commitberichten zijn allemaal Nederlands; dat is met opzet, want het spel is
  dat ook.
- **Commentaar legt uit waarom, niet wat.** `// tel op bij het saldo` voegt
  niets toe; `// binnen de UPDATE controleren, anders kunnen twee verzoeken
  allebei slagen` wel.
- **Reken niets uit in de sjabloon.** De pagina bepaalt eerst wat er waar is,
  en toont dat daarna.
- **Kolomnamen in de database zijn kort en Nederlands** (`zak`, `bank`,
  `kogels`, `famrang`). Dat komt uit het origineel en blijft zo; er zijn te veel
  plekken om dat nu nog te veranderen, en het is consequent.

## Waar je op moet letten

**Bewerk PHP-bestanden nooit via PowerShell.** `Set-Content -Encoding utf8`
schrijft een BOM vóór `<?php`, waarmee `declare(strict_types=1)` breekt en er
witruimte vóór de headers komt. Gebruik een editor of een tool die de codering
met rust laat.

**Herhaal geen benoemde plaatshouder in een query.** De PDO-verbinding staat op
`ATTR_EMULATE_PREPARES => false`; `:naam` twee keer gebruiken geeft dan een
fout. Geef ze aparte namen: `:a`, `:b`.

**Spelers worden gekoppeld op `login`, niet op `id`.** Ook dat komt uit het
origineel. Namen zijn uniek, dus het werkt, maar hernoemen kan daarom niet.

**Zet `debug` uit voordat je uploadt.** Met `debug` aan komt de volledige query
inclusief tabelnamen op het scherm bij een fout.
