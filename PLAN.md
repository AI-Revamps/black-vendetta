# Black Vendetta — herbouwplan

Levend document. Wordt bijgewerkt zodra er stappen afgerond zijn.

**Laatst bijgewerkt:** 30-07-2026
**Voortgang:** Fase 0, 1 en 2 afgerond en getest · het spel is speelbaar · Fase 3 volgt

---

## Uitgangspunten

| Eis | Invulling |
|---|---|
| Geen frameworks | Kaal PHP 8.1+, PDO. Geen Composer, geen build-stap, geen npm. |
| Shared webhost | Alleen FTP + phpMyAdmin. Geen SSH, geen shell. Eén bestand per pagina, dus geen rewrite-regels nodig. |
| Database instellen | `schema.sql` + browser-installer op `/install/`. |
| Draaien | Uploaden, installer doorlopen, klaar. |
| Config aanpasbaar | `inc/config.php`: database, URL, spelnaam, mail, steden, cron. |
| Data | Schone start — geen migratie van oude spelers. Schema is daarom vrij herontworpen. |

---

## Waarom een herbouw en geen reparatie

De oude base start niet op moderne hosting. Dit is niet één probleem maar tien tegelijk:

| # | Probleem | Bewijs | Gevolg |
|---|---|---|---|
| 1 | `mysql_*` verwijderd in PHP 7 | 2.022 aanroepen, `config.php:3` | Fatal op elke pagina |
| 2 | Short open tags `<?` | 51 van 91 bestanden | Parse error bij `short_open_tag=Off` |
| 3 | Kale woorden als string | `crime.php:59` `== kind`, `config.php:44` `!= dood` — 34 bestanden | Fatal `Error` in PHP 8 |
| 4 | `register_globals` nodig | `admin.php:4` `$HTTP_POST_VARS`, `$PHP_SELF` | Adminpaneel + installer kapot |
| 5 | `session_register()` | `admin.php:9,29` | Verwijderd in PHP 5.4 |
| 6 | `sql.sql` importeert niet | 74× `type=MyISAM`, zero-dates | Database niet op te zetten |
| 7 | Ontbrekende bestanden | `config.php:57` include `nl.php`; `bar.php` wil `text.php` | Warnings per request |
| 8 | Wachtwoord in git | `config.php:3` | Credentials openbaar |
| 9 | Bestanden in ISO-8859-1 | `crime.php`, `oc.php`, `famman.php`, `fam.php`, `msn.php` | `knieën` → `knie<?>n` |
| 10 | Frameset-UI | `index.php` | Geen mobiel, geen deeplinks |

**Beveiliging:** SQL-injectie in 66 bestanden (inloggen als wie dan ook via `login.php:4`), kale MD5-wachtwoorden, geen CSRF (`adm-bo.php` geeft xp via GET), XSS in forum/PM/profiel, `sql.sql` publiek opvraagbaar, captcha van één teken die hergebruikt kan worden.

**Prestaties:** `right.php` doet dertien volledige tabelscans per paginaweergave — waaronder álle spelers ophalen om één ranglijstpositie te bepalen.

---

## Doelstructuur

```
/                          document root
  index.php  login.php  crime.php  …    pagina's (URL's blijven gelijk)
  cron.php                              periodieke taken
  schema.sql                            database (afgeschermd via .htaccess)
  .htaccess                             afscherming, caching, headers
  /assets/css/style.css                 opmaak
  /assets/js/app.js                     menuknop + aftellers
  /images/                              bestaande afbeeldingen
  /inc/                                 afgeschermd, niet via browser bereikbaar
    config.php        ← JOUW instellingen (niet in git)
    config.sample.php    voorbeeld
    bootstrap.php        startpunt van elk verzoek
    db.php               PDO + prepared statements + transacties
    auth.php             sessie, inloggen, rechten, CSRF
    helpers.php          e(), url(), money(), flash()
    game.php             rangen, wachttijden, gevangenis, steden
    cron.php             periodieke taken
    layout.php           kop, menu, statuspaneel, voet
  /install/index.php                    eenmalige installatie
```

Elke pagina begint met één regel:

```php
require __DIR__ . '/inc/bootstrap.php';
```

---

## Fase 0 — Fundering ✅ afgerond

| | Stap | Bestand |
|---|---|---|
| ✅ | Instellingenbestand met database, URL, spelnaam, mail, steden, cron | `inc/config.sample.php` |
| ✅ | Databaselaag: PDO, echte prepared statements, strikte modus, transacties, `GET_LOCK` | `inc/db.php` |
| ✅ | Hulpfuncties: `e()` escaping, `url()`, `money()`, `duration()`, flash-meldingen, foutpagina | `inc/helpers.php` |
| ✅ | Auth: veilige sessiecookies, `password_hash`, rechten, CSRF-token, ban-check, IP-log | `inc/auth.php` |
| ✅ | Spelregels: rangladder, voortgang, afkoeltijden, gevangenisstraf, steden | `inc/game.php` |
| ✅ | Periodieke taken met dubbelloop-bescherming | `inc/cron.php` |
| ✅ | Startpunt: PHP-versiecheck, foutafhandeling, headers, sessie | `inc/bootstrap.php` |
| ✅ | Opmaak: kop, menu, statuspaneel, voet — vervangt de frameset | `inc/layout.php` |
| ✅ | Stylesheet: donker thema, CSS-grid, responsive | `assets/css/style.css` |
| ✅ | Menuknop en aftellers, site werkt ook zónder JS | `assets/js/app.js` |
| ✅ | `/inc/` afgeschermd; `.sql`/`.log`/dotfiles geblokkeerd; caching en headers | `inc/.htaccess`, `.htaccess` |
| ✅ | `config.php` en rommelbestanden buiten git | `.gitignore` |
| ✅ | Cron-eindpunt voor webhost-cronjob én externe cron-dienst | `cron.php` |

**Wat dit oplost:** blockers 1, 4, 5, 8 en 10 uit de tabel hierboven, plus de basis voor CSRF, escaping en wachtwoordbeveiliging.

---

## Fase 1 — Database en installatie ✅ afgerond

| | Stap | Bestand |
|---|---|---|
| ✅ | Nieuw schema: InnoDB, utf8mb4, geen zero-dates, `bigint` voor geld, indexen op zoekkolommen, `backup-users` geschrapt | `schema.sql` |
| ✅ | Installer: vereisten controleren, config wegschrijven, schema importeren, beheerder aanmaken, zichzelf op slot zetten | `install/index.php` |

**Schemawijzigingen die ertoe doen**

- `ENGINE=InnoDB` — transacties, dus geldoverdrachten kunnen niet meer half mislukken.
- `bigint` voor `zak`, `bank`, `prijs`, `bod` — bedragen liepen over op `int(10)`.
- `pass varchar(255)` — ruimte voor `password_hash` in plaats van MD5.
- `ip varchar(45)` — IPv6 past er nu in.
- `datetime NULL` in plaats van `'0000-00-00'` — import faalt niet meer.
- Indexen op `(status, activated, xp)`, `(stad, status)`, `messages(to, read, time)` — de ranglijst en het postvak worden niet traag bij duizenden spelers.
- `jail.login` en `iplog(login,ip)` uniek — geen dubbele rijen meer.

### Getest op PHP 8.3.30 + MySQL 8.4.3

Niet alleen "het parseert", maar daadwerkelijk uitgevoerd tegen een lege database:

| Test | Resultaat |
|---|---|
| Syntaxcontrole 10 nieuwe bestanden | 0 fouten |
| Servercontrole installer | alle 6 punten OK |
| Installatie via POST | voltooid |
| Tabellen aangemaakt | 35 — alle InnoDB, alle utf8mb4 |
| Startdata | 8 steden, 13 items, 15 auto's, 4 crontaken |
| UTF-8 | `Privé-Jet` opgeslagen als `C3A9` (echte UTF-8, geen latin-1) |
| Beheerder | level 1000, bcrypt-hash van 60 tekens (geen MD5) |
| `cron.php` zonder sleutel via browser | HTTP 403 geweigerd |
| Crontaken | prijzen herverdeeld, casinowinst 0 → 100 |
| **8 gelijktijdige cron-runs** | elke taak precies 1× uitgevoerd (winst 100, niet 800) |
| `inc/config.php` in git | genegeerd via `.gitignore` |

Tijdens het testen bleek `cron.php` "geen taken aan de beurt" te melden terwijl ze
wél gedraaid hadden: `bootstrap.php` voerde ze al uit vóór het cron-eindpunt zelf.
Opgelost met de vlag `BV_CRON_ENDPOINT`, zodat de melding klopt met wat er gebeurt —
juist dat bericht gebruik je om te controleren of je cronjob werkt.

---

## Fase 2 — Auth, opmaak en eerste speelbare pagina's ✅ afgerond

| | Stap | Bestand |
|---|---|---|
| ✅ | Inloggen: prepared statements, `password_verify`, vertraging + rem na 8 pogingen | `login.php` |
| ✅ | Wachtwoord vergeten: eenmalige code van 64 tekens, 48 uur geldig, verbruikt na gebruik | `login.php` |
| ✅ | Registreren: validatie, activatiemail, verwijzingsbonus, multi-account-controle | `register.php` |
| ✅ | Uitloggen via POST met CSRF (was een GET-link) | `logout.php` |
| ✅ | Voorpagina zonder frameset | `index.php` |
| ✅ | Statuspagina met bezit, wachttijden en prestaties | `home.php` |
| ✅ | Misdaden met transacties en `FOR UPDATE` bij diefstal van spelers | `crime.php` |
| ✅ | Gevangenis- en overlijdenspagina | `jisin.php`, `rip.php` |
| ✅ | Nieuwe captcha: 5 tekens, eenmalig bruikbaar, verloopt na 10 minuten | `inc/captcha.php`, `img.php` |
| ✅ | Mail met veilige headers en eenmalige codes | `inc/mail.php` |
| ✅ | Ontbrekende tabellen `kogels` en `mgarage` toegevoegd aan het schema | `schema.sql` |
| ⬜ | `admin.php` herschrijven — verschoven naar Fase 3 (hoort bij het beheerblok) | |

**Mijlpaal bereikt:** je kunt registreren, inloggen, je status bekijken en misdaden plegen.

### Getest: echte speelsessie

| Test | Resultaat |
|---|---|
| Syntaxcontrole 23 bestanden | 0 fouten |
| Verse installatie | 37 tabellen |
| Inloggen met fout wachtwoord | geweigerd |
| **Inloggen met `' OR '1'='1`** | geweigerd — injectie werkt niet meer |
| Inloggen met juist wachtwoord | doorgestuurd naar `home.php` |
| 6 misdaadrondes met echte captcha | xp 0→4, €1.000→€1.002, 4 misdaden geteld |
| Arrestatie in ronde 5 | speler vastgezet, rondes daarna geblokkeerd |
| Misdaad met foute captcha | geweigerd |
| **POST zonder CSRF-token** | HTTP 419 |
| XSS in familienaam | geëscapet naar `&lt;img onerror=x&gt;` |
| XSS in bericht van de dader | geëscapet |

Twee dingen kwamen tijdens het testen aan het licht:

1. De tabellen `kogels` en `mgarage` werden door twaalf bestanden gebruikt maar
   stonden niet in de oude `sql.sql`. De zwarte markt was dus al kapot vóór de
   migratie. Beide zijn nu toegevoegd.
2. `.htaccess` beschermt `/inc/` alleen op Apache — niet op nginx en niet bij de
   ingebouwde PHP-server. Elk bestand in `/inc/` heeft nu ook een
   `defined('BV_INC') || exit;` als tweede slot.

---

## Fase 3 — Modules migreren ⬜ gepland

Per module: `mysql_*` → prepared statements, uitvoer door `e()`, geldmutaties in transacties.
Volgorde is op risico gekozen — daar waar geld te maken valt eerst.

| | Blok | Bestanden |
|---|---|---|
| ⬜ | Geld en gevechten | `kill.php`, `oc.php`, `bank.php`, `mshop.php`, `donate.php`, `hitlist.php` |
| ⬜ | Misdaad en bezit | `nickacar.php`, `heist.php`, `carrace.php`, `garage.php`, `shop.php`, `transport.php`, `drank.php`, `drugs.php` |
| ⬜ | Casino | `blackjack.php`, `roulette.php`, `slots.php`, `guess.php`, `krassen.php`, `loterij.php` |
| ⬜ | Sociaal | `forum.php`, `message.php`, `fam.php`, `famman.php`, `getmarried.php`, `poll.php` |
| ⬜ | Beheer | de vijftien `adm-*.php` bestanden |
| ⬜ | Overig | `stats.php`, `members.php`, `news.php`, `help.php`, `jail.php`, `respect.php`, … |

Bij elk blok geldt: `SELECT … FOR UPDATE` binnen `db_transaction()` zodra er geld of items van eigenaar wisselen. Dat sluit de verdubbelingstrucs die met MyISAM sowieso niet te voorkomen waren.

---

## Fase 4 — Tekst en interface ⬜ gepland

| | Stap |
|---|---|
| ⬜ | ISO-8859-1 → UTF-8 voor `crime.php`, `oc.php`, `famman.php`, `fam.php`, `msn.php`, `install.php` |
| ⬜ | Alle `<?` → `<?php` (51 bestanden) |
| ⬜ | Kale woorden quoten: `== kind` → `== 'kind'` (34 bestanden) |
| ⬜ | Captcha vervangen: langere code, eenmalig bruikbaar, werkt zonder GD-afhankelijkheid |
| ⬜ | `right.php` vervangen door één query (was dertien tabelscans) |
| ⬜ | Oude bestanden opruimen: `install.php`, `admin.php`, `bar.php`, `form.php`, `verander.php`, `wijzig.php`, `copy.php`, `berichtenbalk.php`, `sql.sql`, `Thumbs.db` |

---

## Fase 5 — Live zetten ⬜ gepland

| | Stap |
|---|---|
| ⬜ | Cronjob instellen bij de host, met de request-fallback als vangnet |
| ⬜ | Mail via `mail()` met correcte headers en afzender uit config |
| ⬜ | `display_errors` uit, logging naar bestand buiten de webroot |
| ⬜ | Installer verwijderen of vergrendelen na gebruik |
| ⬜ | `INSTALL.md`: uploaden, database aanmaken, installer draaien, cron instellen |

---

## Installeren (zoals het straks werkt)

1. Maak in het configuratiescherm van je host een MySQL-database en -gebruiker aan.
2. Upload alle bestanden per FTP naar `public_html`.
3. Ga naar `https://jouwdomein.nl/install/`.
4. Vul databasegegevens, site-URL en het beheerdersaccount in.
5. De installer schrijft `inc/config.php`, importeert `schema.sql` en zet zichzelf op slot.
6. Verwijder de map `install/`.
7. Optioneel: cronjob `php /home/JOUW_ACCOUNT/public_html/cron.php` elke minuut, en zet `cron_mode` op `cron`.

**Vereisten:** PHP 8.1 of nieuwer, MySQL 5.7+ of MariaDB 10.2+, extensies `pdo_mysql`, `mbstring` en `gd`.

---

## Bewuste keuzes

**Spelers worden gekoppeld op `login`, niet op `id`.** Netter zou een numerieke sleutel met foreign keys zijn, maar 25 tabellen en zo'n 90 bestanden verwijzen naar spelers via hun naam. Dat omzetten maakt Fase 3 twee keer zo groot zonder dat een speler er iets van merkt. `login` is `UNIQUE` en geïndexeerd, dus snel genoeg. Wel is de naam nu onveranderlijk — een naamwijziging zou losse tabellen uit de pas laten lopen.

**`level` blijft een getal (1 / 200 / 255 / 1000).** Een echte rollentabel is netter, maar de waarde stuurt ook spelgedrag aan (`crime.php` geeft accounts boven 254 gegarandeerd succes). Het type is wel `varchar(4)` → `smallint` geworden en er zijn constanten voor: `LEVEL_SPELER`, `LEVEL_MODERATOR`, `LEVEL_ADMIN`, `LEVEL_OWNER`.

**Eén bestand per pagina, geen router.** Op een host met alleen FTP is dat het meest voorspelbaar: geen `mod_rewrite` nodig en bestaande links blijven werken.

**Cron draait standaard mee op paginabezoeken.** Niet elk shared pakket biedt cronjobs. Een database-lock plus een claim-`UPDATE` zorgen dat een taak nooit dubbel draait, ook niet bij veel gelijktijdige bezoekers.
