# Het spel draaien

Voor de eigenaar en de staf. Wat je kunt instellen, wie wat mag, en waar je op
moet letten.

## Rechten

Vier niveaus, in de kolom `users`.`level`:

| Niveau | Wie | Wat |
|---|---|---|
| 1 | speler | gewoon spelen |
| 200 | moderator | zoeken, online, gevangenis, waarschuwen |
| 255 | admin | plus bannen, berichten, nieuws, polls, forum, schandpaal, ooggetuigen |
| 1000 | eigenaar | plus items, steden, premium, spelers bewerken |

Een niveau geef je met **Speler bewerken** (`adm-bo.php`). Je kunt niemand een
niveau geven dat gelijk is aan of hoger dan dat van jezelf, en je eigen account
niet bewerken.

## Beheerpagina's

Bereikbaar via het menu onder **Beheer**.

| Pagina | Waarvoor | Vanaf |
|---|---|---|
| Zoeken | speler opzoeken, IP-geschiedenis, inloggen als | moderator |
| Online | wie er nu speelt | moderator |
| Gevangenis | opsluiten en vrijlaten | moderator |
| Waarschuwen | waarschuwing sturen | moderator |
| Bericht sturen | bericht aan iedereen | admin |
| Bannen | verbannen op naam of IP, en opheffen | admin |
| Multi-accounts | uitzondering per IP-adres | admin |
| Wall of Shame | schandpaal | admin |
| Forum opruimen | topics en reacties verwijderen | admin |
| Nieuws | nieuwsberichten | admin |
| Polls | polls aanmaken en sluiten | admin |
| Ooggetuigen | hoe getuigen bij een moord worden aangewezen | admin |
| Premium | advertenties, diamanten, codes | eigenaar |
| Items | wapens, bescherming, vervoer | eigenaar |
| Steden | prijzen, voorraad, reisprijs, steden aanmaken | eigenaar |
| Speler bewerken | statistieken rechtstreeks aanpassen | eigenaar |

Elke ingreep komt met oude en nieuwe waarde in het logboek te staan, met de
naam van wie het deed.

## Premium en advertenties

**Diamanten** zijn de munt. Spelers vinden ze bij toeval tijdens een geslaagde
misdaad of autodiefstal; standaard één op de 500. Die kans stel je zelf in.

**Premium** duurt 14 dagen en kost 250 diamanten, of een code die je zelf
uitgeeft. Verlengen telt de dagen erbij op.

Wat premium doet: **geen advertenties**. Verder niets — dat is een bewuste
keuze, zodat wie betaalt geen voorsprong koopt.

**Advertenties** verschijnen om de zoveel paginabezoeken (standaard 25) op een
tussenpagina met een doorgaan-knop, eventueel met een controlecode. De inhoud
is HTML die je zelf inplakt, dus je kiest je eigen netwerk.

> Dat veld gaat **ongefilterd** naar de browser van je spelers. Dat moet ook,
> anders werkt geen enkel advertentienetwerk, maar het betekent dat wie erbij
> kan script kan laten draaien bij iedereen. Daarom staat die pagina op
> eigenaarsniveau. Plak alleen code waarvan je weet waar hij vandaan komt.

Betalen gaat buiten het spel om: iemand betaalt je via Ko-fi of iets anders, jij
maakt een code op zijn naam aan en hij krijgt die meteen als bericht.

## Moord en ooggetuigen

Een slachtoffer krijgt **niet** te horen wie hem omlegde. Dat staat nergens.
Alleen ooggetuigen weten het, en die kunnen hun verklaring op de zwarte markt
verkopen.

Op de pagina **Ooggetuigen** kies je wie er getuige wordt:

| Keuze | Wat het doet |
|---|---|
| Twee die online zijn *(standaard)* | twee willekeurige online spelers in die stad |
| Twee willekeurige spelers | twee levende spelers in die stad, online of niet |
| Iedereen die online is in die stad | een moord wordt vrijwel meteen bekend |

Bij de eerste twee wordt aangevuld met spelers van elders als de stad te weinig
kandidaten heeft. Een verklaring is twee dagen geldig.

## Doodgaan

Doodgaan is geen einde. Wie omgelegd wordt begint met hetzelfde account
opnieuw: rang, geld, wapens, auto's, huizen, familie en huwelijk weg;
gebruikersnaam, profiel en lopende premium blijven. Hoe vaak iemand is omgelegd
staat op zijn profiel.

Daarom geldt **één account per IP-adres en per e-mailadres**, ook voor dode
accounts. Voor huisgenoten geef je uitzondering via **Multi-accounts**.

## Steden

Een stad toevoegen is twee handelingen: de naam in `game.cities` in
`inc/config.php` zetten, en op de pagina **Steden** op *Aanmaken* klikken. Geen
schemawijziging nodig.

Hernoemen vraagt wel om wat queries in phpMyAdmin; die staan kant-en-klaar op
diezelfde pagina.

## Waar je op moet letten

| | |
|---|---|
| `debug` | moet **`false`** staan. Op `true` zien bezoekers foutmeldingen met database-details |
| Map `install/` | verwijderd na de installatie. Daar zit ook `schema.sql` in |
| `cron_key` | veranderd in iets willekeurigs |
| Advertentiecode | alleen van een bron die je vertrouwt |

## Cheaters

Wie betrapt wordt op valsspelen zet je op de **schandpaal**. Andere spelers
kunnen daar tomaten naar hem gooien, wat hem uiteindelijk het leven kost.

Voor het opsporen van meerdere accounts: **Zoeken** toont per speler zijn
IP-geschiedenis en welke andere accounts vanaf datzelfde adres actief zijn
geweest. Dat logboek gaat negentig dagen terug.
