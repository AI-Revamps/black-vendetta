# Black Vendetta — herbouwplan

Levend document. Wordt bijgewerkt zodra er stappen afgerond zijn.

**Laatst bijgewerkt:** 30-07-2026
**Voortgang:** Fase 0 t/m 2 afgerond · Fase 3 bezig (geldblok klaar) · het spel is speelbaar

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

## Fase 3 — Modules migreren ✅ afgerond

Per module: `mysql_*` → prepared statements, uitvoer door `e()`, geldmutaties in transacties.
Volgorde is op risico gekozen — daar waar geld te maken valt eerst.

| | Blok | Bestanden |
|---|---|---|
| ✅ | **Geld** | `bank.php`, `hitlist.php`, `mshop.php`, `donate.php` |
| ✅ | **Gevechten** | `kill.php`, `oc.php`, `inc/combat.php` |
| ✅ | **Bezit en handel** | `garage.php`, `transport.php`, `drank.php`, `drugs.php`, `inc/handel.php` |
| ✅ | **Misdaad** | `nickacar.php`, `heist.php`, `carrace.php`, `shop.php` |
| ✅ | **Casino** | `blackjack.php`, `roulette.php`, `slots.php`, `guess.php`, `krassen.php`, `loterij.php` |
| ✅ | **Sociaal** | `forum.php`, `message.php`, `fam.php`, `famman.php`, `getmarried.php`, `poll.php` |
| ✅ | **Beheer** | de veertien `adm-*.php` bestanden plus de herbouw van `admin.php` |
| ✅ | **Overzicht** | `stats.php`, `members.php`, `news.php`, `jail.php`, `respect.php`, `wallofshame.php`, `user.php`, `profile.php`, `tip.php` |

Bij elk blok geldt: `SELECT … FOR UPDATE` binnen `db_transaction()` zodra er geld of items van eigenaar wisselen. Dat sluit de verdubbelingstrucs die met MyISAM sowieso niet te voorkomen waren.

### Gedeelde helpers (Fase 3a)

In `inc/game.php` staan nu de bouwstenen die elke module gebruikt:

| Functie | Doel |
|---|---|
| `lock_user()` / `lock_user_by_login()` | Speler ophalen met `FOR UPDATE` |
| `afboeken()` | Saldocontrole zit ín de `UPDATE`, dus geen gat tussen controleren en afschrijven |
| `bijschrijven()` | Geld of kogels erbij |
| `notify()` | Systeembericht — verving overal herhaalde `INSERT`-blokken |
| `log_action()` | Vastleggen voor de beheerders |
| `SpelFout` | Draait de transactie terug en toont een nette melding |

### Wat Fase 3a repareerde

| Bestand | Probleem |
|---|---|
| `bank.php` | Saldo werd in PHP berekend en teruggeschreven — twee gelijktijdige opnames verdubbelden geld |
| `hitlist.php` | Premie kwam ongefilterd in de query; afkopen zonder transactie |
| `mshop.php` | Kopen via GET-link (CSRF); geen transactie bij aankoop |
| `mshop.php` | **Iedereen kon andermans ooggetuigenverklaring verkopen** — `login` werd overschreven met de verkoper |
| `donate.php` | Aantal actieve donaties liep uit de pas doordat het los werd bijgehouden |
| overal | `money()` gaf `&euro;` terug, wat door `e()` dubbel geëscaped werd tot `&amp;euro;` |

**De betaalkoppeling in `donate.php` is bewust niet meegenomen.** Die hing aan een Mollie-widget uit 2007 met een hardcoded partner-id en een IP-whitelist. Codes aanmaken en inwisselen werkt wel; wie echt wil incasseren sluit een provider aan op `donatie_code_maken()`.

### Getest

### Wat Fase 3b t/m 3d repareerde

| Bestand | Probleem |
|---|---|
| `kill.php` | **Achterdeurtje:** speler `JanuS` mocht moorden ongeacht niveau |
| `kill.php` | **Achterdeurtje:** `pitbullgirl` kon niet gewond raken |
| `kill.php` | Slachtoffer hield zijn zakgeld terwijl de moordenaar hetzelfde bedrag kreeg |
| `kill.php` | Dubbele backtick maakte de kogel-query ongeldig; die draaide nooit |
| `kill.php` | Premie bij backfire werd uitbetaald uit een niet-bestaande variabele |
| `kill.php` | Gevechtsformule deelde door nul bij `se = 0` — op PHP 5 instant kill, op PHP 8 fataal |
| `oc.php` | **Geldpers:** negatief aantal bommen maakte de kosten negatief |
| `oc.php` | Overval starten via GET-link (`oc.php?go=1`) |
| `garage.php` | Verkopen en crushen via GET-link |
| `garage.php` | "Alles crushen" omzeilde de familielimiet volledig |
| `garage.php` | Repareren kon geld opleveren bij negatieve prijs |
| `transport.php` | `if ($data->rijbewijs = 0)` — toewijzing i.p.v. vergelijking, controle deed niets |
| `transport.php` | Enschede toonde €4.500, schreef €3.500 af |
| `drugs.php`/`drank.php` | **Tweede databaseverbinding met andere hardcoded credentials** |
| `drugs.php`/`drank.php` | Arrestatie bij verkoop testte op `kans == 0`, wat nooit voorkwam |

### Getest

| Test | Resultaat |
|---|---|
| Storten en opnemen | klopt tot op de euro |
| Premie zetten en afkopen | €5.000.000 → €4.500.000 en €5.000.000 → €4.000.000 |
| Kogelhandel | verkoper +€400.000 / −500 kogels, koper −€400.000 / +500 kogels |
| Eigen aanbod kopen | geweigerd |
| Andermans ooggetuige verkopen | geweigerd; eigen verkoop lukt wel |
| **8 gelijktijdige opnames van €100.000 bij saldo €100.000** | precies 1 geslaagd, 7 geweigerd, totaal onveranderd |
| Moord op speler met €777.777 op zak | geld ging naar de dader, slachtoffer op nul — geen duplicatie |
| Erfenis, premie, casino, familieopvolging | alle vier correct afgehandeld |
| Volledige OC met 4 spelers | balans klopt: €20.000.000 − €465.000 + €2.568.260 = €22.103.260 |
| −100.000 kogels / −50 bommen | geweigerd met duidelijke melding |
| Reis, reparatie, verscheping, drugshandel | balans klopt: €100.000 − €1.500 + €10.000 − €28.464 = €80.036 |

### Wat het beheerblok repareerde (Fase 3t en 3u)

De rechtencontrole stond in elk bestand apart, met alle gevolgen van dien. Die
staat nu op één plek: `beheerpaginas()` in `inc/beheer.php` voedt zowel het menu
als de toegangscontrole, zodat een pagina niet meer per ongeluk zonder controle
kan blijven.

| Bestand | Probleem |
|---|---|
| `adm-cleandb.php` | **Geen inlogcontrole en geen rechtencontrole.** Wie de URL kende wiste rijen uit vier tabellen. Was bovendien een opruimtaak, geen beheerpagina — verplaatst naar de dagelijkse cron, bestand weg |
| `admin.php` | Losse gastenboekbeheerder met een eigen wachtwoord uit `config.php`, geen koppeling met de rechten van het spel. Leunde op `$HTTP_POST_VARS`, `session_register()` en `register_globals` — alle drie verwijderd uit PHP |
| `adm-search.php` | Inloggen als een andere speler via `$_SESSION['login'] = $_GET['login']` — een gewone link, zonder token en zonder weg terug |
| `adm-search.php` | Het zoekveld werd als kolomnaam gebruikt: `WHERE $need = $gegevens`, beide uit het formulier |
| `adm-msg.php` | De niveaucontrole zat in de `else`-tak: versturen gebeurde er buitenom. Een moderator kon wel versturen maar het formulier niet zien |
| `adm-bo.php` | Het controlespoor ging als privébericht naar de hardcoded naam `JanuS`; bestond die speler niet, dan was er geen spoor |
| `adm-bo.php` | Alle velden ongefilterd uit `$_POST`, inclusief `login` en `level`. Je eigen niveau ophogen kon |
| `adm-forum.php` | `DELETE … WHERE ` `` topic_id `` ` — spaties binnen de backticks maken er een andere kolomnaam van. Reacties bleven dus altijd staan als een topic verdween |
| `adm-poll.php` | `$_GET['x'] == u` vergelijkt met een ongedefinieerde constante: sinds PHP 8 een fatale fout, dus de pagina deed het niet meer |
| `adm-poll.php` | Er konden meerdere polls tegelijk actief zijn; `poll.php` koos er dan willekeurig een |
| `adm-items.php` | Twee items met hetzelfde soort en nummer waren mogelijk, waarna de winkel er willekeurig een van pakte |
| `adm-drdrpr.php` | Acht bijna identieke blokken met de stadsnaam hardcoded; een stad die niet in `stad` stond werd stil overgeslagen |
| `adm-addnews.php` | Rechtencontrole was `if ($data->level == 1) exit;` — elk niveau daarboven kwam erdoor |
| overal | Verwijderen, bannen en waarschuwen liepen via GET-links, zonder token |

### Getest met drie accounts (speler, moderator, eigenaar)

| Test | Resultaat |
|---|---|
| Toegangsmatrix over veertien pagina's | 403 waar het hoort, toegang waar het hoort |
| POST zonder token | 419 op elke pagina |
| Moderator POST naar `adm-ban.php` | 403 |
| Staflid bannen / opsluiten | geweigerd; gewone speler lukt |
| Geld toekennen via `adm-bo.php` | komt correct in de database én in het logboek, met oude en nieuwe waarde |
| Eigen account bewerken, niveau ≥ eigen niveau, gezondheid 500, stad "Atlantis" | alle vier geweigerd met een leesbare melding |
| Topic met drie reacties verwijderen | reacties achteraf 0 |
| Poll activeren terwijl er al een open stond | aantal open polls blijft 1 |
| Item met verkoopprijs boven koopprijs | geweigerd |
| Rooktest over alle 91 pagina's | alleen de vier nog niet gemigreerde bestanden geven fouten |

### Wat het overzichtsblok repareerde (Fase 3w en 3x)

| Bestand | Probleem |
|---|---|
| `wallofshame.php` | **Geld bijdrukken.** Bij een dodelijke tomaat kreeg de gooier `zak = zak + $victim->zak`, maar de zak van het slachtoffer werd nooit leeggemaakt — alleen `bank` ging op nul. Elke dode verdubbelde zijn contante geld |
| `profile.php` | **Wachtwoorden als kale MD5**, met `$data->pass != MD5($pass)` als controle. Geen enkele eis aan het nieuwe wachtwoord |
| `user.php` | `[color=x" onmouseover="…]` opende een attribuut op de pagina van elke bezoeker: tachtig regels `eregi_replace()` zetten rechtstreeks HTML terug |
| `jail.php` | Het bericht aan wie werd vrijgekocht kwam nooit aan: de INSERT noemde vijf kolommen en gaf vier waarden mee |
| `jail.php` | Bij een mislukte uitbraak werd een cel aangemaakt met `$boete`, `$famillie` en `$jailtime` — drie variabelen die op dat punt niet bestonden |
| `jail.php` | Vrijkopen ging via een GET-link die geld van je rekening haalde |
| `inc/game.php` | `jail_put()` deed een kale `INSERT` terwijl `jail`.`login` uniek is; elke arrestatie van iemand met een verlopen cel liep vast |
| `respect.php` | Beide overzichten filterden op `` `time` >= '{$data->signup}' `` — die kolom bestaat niet, dus de lijsten waren altijd leeg |
| `respect.php` | Schandepunten trokken `respect` onder nul op een `unsigned` kolom |
| `members.php` | `ORDER BY $sort $order` rechtstreeks uit de URL; zoekterm ongefilterd in een `LIKE` inclusief `%` en `_` |
| `members.php` | `$_REQUEST['q']` ongefilterd in het waarde-attribuut van het zoekveld |
| `stats.php` | Las álle spelers uit de database om de totalen in PHP op te tellen, plus zestien queries voor acht steden |
| `tip.php` | Meldingen gingen naar de hardcoded naam `JanuS`, die niet bestaat; naam en e-mail kwamen uit verborgen formuliervelden |

### Getest

| Test | Resultaat |
|---|---|
| Twee spelers gooien tegelijk de dodelijke tomaat | precies één wint de buit, de ander krijgt "is al dood"; totaal daalt met exact de tomaatkosten |
| Vrijkopen, tomaat, respect met te weinig saldo | nooit een negatief saldo |
| Vijf XSS-payloads in het profielveld, DOM-gecontroleerd | geen `on*`-attribuut, geen `javascript:` in href of src |
| Wachtwoord wijzigen | hash verandert, begint met `$2y$`, nieuw wachtwoord werkt |
| `members.php?s=zak;--` | valt terug op de standaardsortering |
| Zoeken op `%` | 0 resultaten in plaats van iedereen |
| Rooktest over alle 70 overgebleven pagina's | 0 problemen |

---

## Fase 4 — Tekst en interface ✅ afgerond

Deze fase was aan het eind grotendeels vanzelf klaar: elk herbouwd bestand is
meteen als UTF-8 met een volledige `<?php`-tag geschreven, en de kale woorden
verdwenen met de `mysql_*`-aanroepen waar ze in stonden.

| | Stap | Resultaat |
|---|---|---|
| ✅ | ISO-8859-1 → UTF-8 | 0 bestanden met ongeldige UTF-8 over |
| ✅ | Alle `<?` → `<?php` | 0 korte open-tags over |
| ✅ | Kale woorden quoten (`== kind` → `== 'kind'`) | weg met de herbouw; sinds PHP 8 zijn het fatale fouten, wat de vindplaatsen vanzelf aanwees |
| ✅ | Captcha: langere code, eenmalig bruikbaar, werkt zonder GD | `inc/captcha.php`, met een tekstsom als terugval |
| ✅ | `right.php` vervangen door één query | `status_summary()` in `inc/layout.php` |
| ✅ | Oude bestanden opruimen | 24 bestanden weg (zie hieronder) |

### Verwijderd, en waarom

| Bestand | Reden |
|---|---|
| `upper.php`, `menu.php`, `right.php`, `clock.php` | de frameset; vervangen door `inc/layout.php` |
| `rangen.php`, `tijden.php`, `rangmsg.php` | losse includes uit `config.php`; nu `inc/game.php` |
| `bar.php`, `form.php`, `verander.php`, `wijzig.php`, `berichtenbalk.php` | gastenboekje met een eigen wachtwoord, los van de rechten van het spel |
| `admin.php` (oud), `install.php` (oud) | vervangen door een echt beheerdersoverzicht en `install/index.php` |
| `adm-cleandb.php` | geen enkele authenticatie; was bovendien een opruimtaak — nu een crontaak |
| `blackmarket.php` | dubbel met `mshop.php` |
| `sql.sql` | importeerde niet (74× `type=MyISAM`, zero-dates) en stond publiek in de webroot |
| `msn.php` | uploadde een MSN-contactenlijst (.ctt) en mailde iedereen daarin; MSN is in 2013 gestopt |
| `chat.php` | formulier naar chat4all.net, ruim vijftien jaar weg |
| `klikmissie.php`, `klikmissie1.php` | betaalden €10.000 voor een klik naar het verdwenen top100nl.net, en de vlag werd nooit teruggezet |
| `red.php`, `paidtimer.php` | hoorden bij het oude menu en de frameset |
| `ban.php` | blokkeerde iedereen wiens reverse DNS niet op `.be` of `.nl` eindigde: hield legitieme bezoekers tegen en proxy's niet |
| `copy.php`, `Thumbs.db` | restanten |

---

## Fase 5 — Live zetten 🔄 bezig

| | Stap |
|---|---|
| ⬜ | Cronjob instellen bij de host, met de request-fallback als vangnet |
| ✅ | Mail via `mail()` met correcte headers en afzender uit config |
| ⬜ | `display_errors` uit, logging naar bestand buiten de webroot |
| ⬜ | Installer verwijderen of vergrendelen na gebruik |
| ⬜ | `INSTALL.md`: uploaden, database aanmaken, installer draaien, cron instellen |
| ✅ | Volledige speelsessie op een verse database |

### De speelsessie

Van een lege database naar een gespeeld account, via de echte formulieren —
geen directe database-aanroepen om ergens te komen.

| Onderdeel | Resultaat |
|---|---|
| Registreren | account aangemaakt met bcrypt-hash, €1.000 startkapitaal, willekeurige startstad |
| Inloggen en `home.php` | rang "Empty-Suit" op de statusbalk |
| 40 misdaden | 5 gelukt, 25 mislukt, 10 keer opgepakt; XP en `nrofcrime` lopen mee |
| Bank | €60.000 storten en €20.000 opnemen komt exact uit; te veel opnemen wordt geweigerd |
| Winkel | wapen, bescherming en vervoer gekocht en weer verkocht, met de juiste bedragen |
| Lijfwacht | gekocht voor €25.000 |
| Fruitmachine, 15 keer | speler €985.000 + casinokas €15.000 = **precies €1.000.000** |
| Moord | slachtoffer's €777.777 gaat naar de dader, zijn €222.222 op de bank verdwijnt; totaal daalt met exact dat bedrag, geen duplicatie |
| Cron | alle zes taken draaien één keer en zetten hun tijdstempel |
| Rooktest | alleen de beheerpagina's geven 403, en dat hoort: de testspeler is niveau 1 |

Onderweg bevestigde het spel drie regels die het hoort te handhaven: je kunt
niet moorden zolang jij of je doelwit nog beginnersbescherming heeft, niet in
een andere stad, en er kan niet gegokt worden als de kas van de casinobaas een
uitbetaling niet zou dekken.

### E-mail

| Test | Resultaat |
|---|---|
| Registreren terwijl verzenden mislukt | account bestaat, met een melding dat de beheerder moet activeren — een mailstoring blokkeert de aanmelding niet |
| Inloggen vóór activatie | geweigerd |
| Activatielink met een verkeerde code | geweigerd, en de echte code blijft geldig |
| Activatielink met de juiste code | account actief, code meteen verbruikt |
| Dezelfde link nog eens | geweigerd |
| Wachtwoord vergeten, drie varianten | alle drie hetzelfde antwoord, zodat je er niet uit kunt afleiden welke accounts bestaan; alleen de geldige aanvraag maakt een code aan |
| `\r\n` plus een extra Bcc-header in het e-mailadres | geweigerd bij registratie |

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
