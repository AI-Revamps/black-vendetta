# Black Vendetta installeren

Bedoeld voor een gewone shared webhost met alleen FTP en phpMyAdmin. Je hebt
geen SSH nodig, geen Composer, en geen rechten om iets op de server te
installeren.

Reken op een kwartier.

---

## Wat je host moet kunnen

| Nodig | Waarom |
|---|---|
| **PHP 8.1 of nieuwer** | het spel gebruikt taalonderdelen die daarvoor niet bestaan |
| **MySQL 5.7+ of MariaDB 10.2+** | `InnoDB`, `utf8mb4` en rijvergrendeling |
| **`pdo_mysql`** | de databaselaag |
| **`mbstring`** | omgaan met accenten en emoji in namen en berichten |
| `gd` | *aanbevolen*, voor de plaatjes met de beveiligingscode. Ontbreekt die, dan valt het spel automatisch terug op een som in tekst |

Vrijwel elk pakket bij Antagonist, Vimexx, Argeweb, Hostnet, TransIP, Strato
en Hostinger voldoet hieraan. Staat PHP nog op 7.4, dan is dat in het
configuratiescherm van je host met één keuzelijst om te zetten.

---

## Stap 1 — Database aanmaken

In het configuratiescherm van je host (cPanel, DirectAdmin of iets eigens):

1. Maak een **lege MySQL-database** aan.
2. Maak een **databasegebruiker** aan met een eigen wachtwoord.
3. Koppel die gebruiker aan de database, met **alle rechten**.

Schrijf vier dingen op: de **servernaam** (vrijwel altijd `localhost`), de
**databasenaam**, de **gebruikersnaam** en het **wachtwoord**.

> Op cPanel en DirectAdmin krijgen database en gebruiker automatisch je
> accountnaam ervoor: je typt `vendetta` en er staat `jansen_vendetta`. Gebruik
> die volledige naam.

Je hoeft `schema.sql` **niet** zelf te importeren; de installer doet dat. Het
bestand staat in de map `install/` en verdwijnt dus samen met de installer.

---

## Stap 2 — Bestanden uploaden

Upload de hele inhoud van deze map naar de webmap van je domein — meestal
`public_html`, soms `httpdocs` of `www`.

Wil je het spel in een submap (`jouwdomein.nl/spel`), upload dan naar
`public_html/spel`. Dat werkt zonder aanpassingen; de installer vraagt straks
naar het volledige adres.

Zet de rechten van de map **`inc`** op `755`. Lukt schrijven daarna nog niet,
zet hem dan tijdelijk op `775` of `777` en na de installatie terug op `755`.

---

## Stap 3 — Installer draaien

Ga in je browser naar:

```
https://jouwdomein.nl/install/
```

De installer doet drie dingen:

1. **Servercontrole.** Controleert PHP-versie, uitbreidingen en schrijfrechten,
   en zegt per punt wat je moet doen als iets niet klopt.
2. **Gegevens invullen.** Databasegegevens, het adres van het spel, het
   afzenderadres voor e-mail, en je eigen beheerdersaccount.
3. **Installeren.** Schrijft `inc/config.php`, importeert `schema.sql` en maakt
   je beheerdersaccount aan.

Lukt het schrijven van `inc/config.php` niet, dan toont de installer de inhoud
van dat bestand op het scherm. Kopieer die naar `inc/config.php` en upload hem
per FTP.

---

## Stap 4 — Installer verwijderen

**Verwijder de map `install/` van je server.**

De installer zet zichzelf na afloop op slot met het bestand
`install/.geinstalleerd`, maar dat is een grendel, geen slot. Zolang de map er
staat, kan iemand die grendel weghalen als hij ergens anders schrijftoegang
heeft. Weghalen dus.

Daarmee verdwijnt ook `schema.sql`. Dat is de bedoeling: de volledige
databasestructuur hoeft na de installatie niet meer opvraagbaar te zijn.

---

## Stap 5 — Cron instellen

Het spel heeft periodieke taken: kogelvoorraad aanvullen, drank- en
drugsprijzen verversen, eerpunten uitdelen, de loterij trekken en detectives
laten rapporteren.

Er zijn twee manieren, en je kiest ze met `cron_mode` in `inc/config.php`.

### `'request'` — standaard, werkt altijd

De taken draaien mee op gewone paginabezoeken. Je hoeft niets in te stellen.
Een databaselock plus een claim-`UPDATE` zorgen dat een taak nooit dubbel
draait, ook niet bij veel gelijktijdige bezoekers.

Nadeel: komt er een nacht lang niemand langs, dan draait er ook niets. De taken
lopen daarna gewoon één keer in.

### `'cron'` — netter, als je host het aanbiedt

Zet in het cronjob-scherm van je host een taak neer die elke minuut draait:

```
php /home/JOUW_ACCOUNT/public_html/cron.php
```

Zet daarna `cron_mode` op `'cron'`.

Biedt je host alleen een *webcron* (een URL die periodiek opgehaald wordt), zet
dan `cron_mode` op `'both'` en gebruik:

```
https://jouwdomein.nl/cron.php?key=JOUW_CRON_KEY
```

De sleutel staat als `cron_key` in `inc/config.php`. **Verander die in iets
willekeurigs**, anders kan iedereen je taken aanroepen.

---

## Stap 6 — E-mail controleren

Het spel verstuurt e-mail bij registratie en bij "wachtwoord vergeten", via de
gewone PHP-functie `mail()`.

Gebruik als `mail_from` een adres **op je eigen domein**
(`noreply@jouwdomein.nl`). Een Gmail- of Hotmail-adres als afzender belandt
vrijwel gegarandeerd in de spammap, omdat je server niet namens die domeinen
mag verzenden.

Werkt `mail()` bij je host niet, zet dan in `inc/config.php`:

```php
'require_activation' => false,
```

Nieuwe spelers zijn dan meteen actief en hoeven geen activatiemail te
ontvangen.

---

## Stap 7 — Laatste controles voor je opengaat

| Controle | Waar |
|---|---|
| `'debug' => false` | `inc/config.php`. **Op `true` zien bezoekers foutmeldingen inclusief database-details.** |
| `cron_key` veranderd | `inc/config.php` |
| Map `install/` weg | via FTP |
| Rechten van `inc` terug op `755` | via FTP |
| `inc/config.php` niet opvraagbaar | ga naar `https://jouwdomein.nl/inc/config.php` — je hoort een foutmelding of een lege pagina te zien, geen wachtwoord |
| Steden ingevuld | `game.cities` in `inc/config.php` moet overeenkomen met de tabel `stad`. De beheerpagina *Drank en drugs* laat zien welke steden ontbreken |

---

## Accounts en doodgaan

Twee spelregels die met elkaar samenhangen, en die je moet kennen voordat je
spelers uitnodigt.

**Doodgaan is geen einde.** Wie omgelegd wordt, komt op een schermpje waar
staat wie het deed. Met één knop begint dat *zelfde* account opnieuw: rang,
geld, wapens, auto's, familie en huwelijk zijn weg, en je start met het
startkapitaal in een willekeurige stad, met beginnersbescherming. Je houdt je
gebruikersnaam, je profiel en lopende donaties — daar is voor betaald.

Hoe vaak iemand is omgelegd staat op zijn profiel. Dat is de enige teller die
een doorstart overleeft.

**Daarom: één account per persoon.** Met `allow_multi_accounts` op `false`
mag er per IP-adres en per e-mailadres precies één account bestaan, óók als dat
account op dit moment dood is. Er is immers geen reden meer om een tweede te
maken.

Voor huisgenoten op één aansluiting geef je per IP-adres uitzondering via de
beheerpagina **Multi-accounts**. Houd er rekening mee dat een IP-adres bij veel
providers verandert; het IP-logboek onder **Zoeken** laat per speler zien welke
andere accounts vanaf hetzelfde adres actief zijn geweest.

---

## Moord en ooggetuigen

Een slachtoffer krijgt **niet** te horen wie hem omlegde. Dat staat nergens: niet
op zijn overlijdenspagina, niet in de statistieken. Wie het deed weten alleen de
ooggetuigen, en die kunnen hun verklaring op de zwarte markt verkopen. Zo is er
een markt voor informatie in plaats van een mededeling.

Op de beheerpagina **Ooggetuigen** kies je wie er getuige wordt:

| Keuze | Wat het doet |
|---|---|
| **Twee die online zijn** *(standaard)* | twee willekeurige spelers die op dat moment online zijn in de stad van de moord |
| **Twee willekeurige spelers** | twee willekeurige levende spelers in die stad, online of niet |
| **Iedereen die online is in die stad** | álle online spelers in die stad; een moord wordt dan vrijwel meteen bekend |

Bij de eerste twee wordt aangevuld met spelers van elders als de stad te weinig
kandidaten heeft, zodat er altijd twee getuigen zijn. Bij de derde niet: is er
niemand online in die stad, dan blijft de moord onopgemerkt.

De pagina laat ook zien wat je keuze op dit moment zou opleveren, per stad. Een
verklaring is twee dagen geldig.

---

## Instellingen achteraf aanpassen

Alles zit in `inc/config.php`. De belangrijkste:

```php
'site' => [
    'url'  => 'https://jouwdomein.nl',   // zonder slash op het eind
    'name' => 'Black Vendetta',          // titels en e-mails
],
'game' => [
    'cities'               => ['Brussel', 'Leuven', ...],
    'start_money'          => 1000,   // startkapitaal
    'require_activation'   => true,   // activatiemail verplicht
    'allow_multi_accounts' => false,  // meerdere accounts per IP
],
'cron_mode' => 'request',
'debug'     => false,
```

Verhuis je naar een ander domein, dan hoef je alleen `site.url` aan te passen.

---

## Veelgestelde vragen

**Ik zie een witte pagina.**
Zet `'debug' => true` in `inc/config.php`, laad opnieuw, lees de foutmelding en
zet hem daarna **meteen weer op `false`**.

**"Er is nog geen inc/config.php."**
De installer is niet (helemaal) gelopen. Ga naar `/install/`, of kopieer
`inc/config.sample.php` naar `inc/config.php` en vul hem met de hand in.

**De installer zegt dat `inc` niet beschrijfbaar is.**
Zet de rechten via FTP op `755`, of `775` als dat niet helpt. Sommige hosts
draaien PHP als een andere gebruiker dan je FTP-account; dan is `775` of `777`
tijdelijk nodig.

**Kan ik een bestaande spelersdatabase overzetten?**
Nee. Het databaseschema is opnieuw opgezet en de wachtwoorden zaten in de oude
versie als kale MD5 in de database. Die zijn niet veilig over te nemen.

**Moet ik `schema.sql` in phpMyAdmin importeren?**
Alleen als de installer daar niet aan toekomt. Hij importeert het bestand zelf.
Doe je het met de hand, maak dan wel eerst een lege database aan met
`utf8mb4` als tekenset.
