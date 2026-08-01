# Wat er per versie veranderd is

De nummering is `hoofd.tussen.klein`:

- **hoofd** — je moet iets doen bij het bijwerken (schema aanpassen, instelling
  toevoegen). Staat hieronder altijd expliciet bij.
- **tussen** — nieuwe onderdelen of spelregels, bijwerken kan zonder ingrepen.
- **klein** — reparaties.

---

## 1.0.0 — 1 augustus 2026

De eerste versie. Een volledige herbouw van een base uit ongeveer 2005; van de
oude code is geen regel overgebleven.

### Het spel

Misdaden, Organised Crime, Route 66, auto's stelen en kraken. Handel in drank en
drugs met transport tussen acht steden, een garage en een rijschool. Bank,
winkel, zwarte markt, kogelfabriek, loterij, krasloten en bloedbank.

Drie casinospellen — fruitmachine, roulette en blackjack — die spelers zelf
kunnen bezitten en waarvan de kas naar de eigenaar gaat.

Moorden met kogels, een hitlist, een detectivebureau, gevangenis, bescherming en
lijfwachten. Families met rangen en promoties, trouwen, forum, privéberichten,
nieuws, polls, eerpunten en een wall of shame.

Zestien beheerpagina's op drie rechtenniveaus: moderator, beheerder en eigenaar.

### Wat er in het origineel nooit werkte, en nu wel

| Onderdeel | Wat er mis was |
|---|---|
| Loterij | verkocht loten maar had geen trekking |
| Familiekas | kon niet gevuld worden |
| Promotie-uitbetaling | werd weggeschreven maar nooit uitgelezen |
| Rijbewijs | onhaalbaar door een rekenfout |
| Zwarte markt | de tabellen ontbraken |
| Polls | `adm-poll.php` deed helemaal niets |
| Beide respectoverzichten | toonden nooit iets |
| Forumopruiming | werd nooit uitgevoerd |

### Nieuwe spelregels

**Doodgaan is een doorstart.** Je verliest je voortgang, geld en bezit, maar
houdt je account, profiel en lopende donaties. Een teller houdt bij hoe vaak je
gestorven bent. Omdat er geen reden meer is voor een tweede account, is de grens
één account per IP-adres en per e-mailadres — ook dode tellen mee.

**Moorden is anoniem.** Het slachtoffer ziet alleen het afscheidsbericht, niet
de naam. Bij elke moord krijgen twee spelers een ooggetuigenbericht, en die
verklaring is te koop aan te bieden op de zwarte markt. De beheerder kiest hoe
getuigen worden aangewezen: onder wie online is, onder wie in de stad is, of
willekeurig.

**Premium.** Diamanten vind je bij toeval tijdens een misdaad, standaard één op
de vijfhonderd. Voor 250 diamanten of via Ko-fi krijg je veertien dagen premium.
Dat neemt de advertentiepagina weg die je anders elke 25 paginaweergaven ziet.
Premium geeft geen enkel spelvoordeel — dat is bewust.

### Beveiliging

De oude versie had SQL-injectie in 66 bestanden, MD5-wachtwoorden, geen enkele
CSRF-controle, XSS in forum, profiel en privéberichten, een publiek opvraagbare
`sql.sql`, en een captcha van één teken die je kon hergebruiken.

Nu:

- alles via prepared statements; geen variabele komt een query in
- `csrf_check()` op elke POST, geen handelingen achter een gewone link
- `password_hash()` in plaats van MD5
- BBCode escapet eerst alles en zet daarna alleen terug wat mag
- geldtransacties met rijvergrendeling en de voorwaarde binnen de `UPDATE`, zodat
  twee gelijktijdige verzoeken niet allebei kunnen slagen
- `schema.sql` staat in `install/` en verdwijnt met de installer

Twee geldlekken uit het origineel zijn dichtgezet: bij een moord en bij de
dodelijke tomaat op de schandpaal werd geld verdubbeld in plaats van verplaatst.

### Techniek

PHP 8.1+ met PDO, MySQL 5.7+ of MariaDB 10.2+. Geen frameworks, geen Composer,
geen build-stap. Eén bestand per pagina. 71 pagina's, 40 tabellen, ongeveer
21.800 regels PHP.

Installer op `/install/` die de configuratie schrijft, het schema laadt en vraagt
naar steden, cron-type en activatiemail. De cron draait als echte cronjob of
lift mee op paginabezoek, voor hosts zonder cron.

Adressen zonder `.php` kunnen aangezet worden met `mooie_urls`; dat vraagt
`mod_rewrite`, dus het staat standaard uit.

### Uiterlijk

Donker thema in het blauw van het logo. Mobiel: statusstrook onder de kopbalk,
vaste onderbalk met vijf tabs, zijmenu als uitschuifbare la. Werkt zonder
JavaScript.

### Meegeleverd

Vijf testscripts (`tests/`) die controleren of elke pagina schoon laadt, of de
geldbalans klopt na bank, winkel, casino, moord en cron, of XSS-payloads worden
geneutraliseerd, of de indeling per toestand klopt, en of geen enkele link nog
een `.php` bevat.

Documentatie in `docs/`: installatie, beheer, architectuur, ontwikkelen, en een
verslag van de herbouw dat per onderdeel vertelt wat er kapot was.
