# Economiebalans vroege spel — Ontwerp

**Issue:** [#33 "Balanceren Geld"](https://github.com/AI-Revamps/Vendetta/issues/33)

## Probleem

Een nieuwe speler verdient met de meest toegankelijke misdaad (steel van een
kind) €1-10 per poging, met een slaagkans die bij xp 0 op de wettelijke vloer
van 1% staat. Vrijwel elk ander onderdeel van het spel begint al in de
duizenden tot honderdduizenden euro's:

| Systeem | Bedrag |
|---|---|
| Kraslot | €10.000 |
| Drugs/drank per eenheid | €1.093 – €14.903 |
| Kogels per stuk | €500 – €1.500 |
| Wapens | €10.000 – €140.000 |
| Bescherming | €30.000 – €100.000 |
| Rijles | €25.000 per les |
| Voertuigen | €250.000 – €1.500.000 |
| Huis | €850.000 |

Bijkomende, niet in het oorspronkelijke issue genoemde bevinding: drugs- en
drankhandel (`inc/handel.php`) levert **alleen winst op als je tussen steden
reist** — binnen dezelfde stad is de koop- en verkoopprijs identiek. Reizen
vereist een rijbewijs (gemiddeld ~55-60 geslaagde lessen, tot €1.400.000+ aan
lesgeld) en een voertuig (€250.000-€1.500.000). Daarmee is handel, los van de
xp-drempel van 20, in de praktijk pas na dagen tot weken spelen bereikbaar.
Dat maakt rijlessen en voertuigprijzen een even grote blokkade als de
misdaadopbrengsten zelf.

## Doel

Een nieuwe speler kan **binnen 30-60 minuten actief spelen** (bij de
bestaande afkoeltijd van 60 seconden tussen misdaden, dus ~30-60 pogingen)
genoeg verdienen voor het goedkoopste wapen (€10.000), puur uit
misdaadopbrengsten — handel is in die eerste sessie nog geen realistische
optie en hoeft dat ook niet te zijn.

**Principe:** instapkosten die de vroege voortgang blokkeren (misdaad,
rijlessen, voertuigen) gaan omlaag. Aspiratiedoelen voor later in het spel
(huis, top-voertuigen, safehouse) blijven ongewijzigd — tegen die tijd heeft
een speler ook handel, gokken en andere inkomstenbronnen naast misdaad.

## Reikwijdte

**Wel:**
- Misdaadopbrengsten, -slaagkansen en xp-winst (`crime.php`)
- Rijlessen: prijs en voortgang per les (`rijbewijs.php`)
- Voertuigprijzen (`install/schema.sql`, tabel `items`, type `trans`)

**Niet:**
- Drugs/drank-prijzen en de xp-drempel van 20 (`inc/handel.php`) — deze
  vallen vanzelf weer op hun plek zodra misdaad meer oplevert, en de
  snellere xp-winst uit misdaad maakt de drempel van 20 al binnen een paar
  geslaagde overvallen haalbaar.
- Kraslot (€10.000, `krassen.php`) — wordt met de nieuwe misdaadcurve weer
  een zinnige gok in plaats van een onbereikbaar bedrag.
- Wapen-, bescherming- en huisprijzen — al gerebalanced (wapens, #35) of
  bewust een laat-spel-doel (huis).
- Kogelprijzen, familie-economie, casino/gokspellen anders dan kraslot.

## Nieuwe waarden

### Misdaad (`crime.php`, functie `misdaden()`)

| Misdaad | Slaagkans nu (xp 0) | Slaagkans voorstel | Opbrengst nu | Opbrengst voorstel | xp-winst nu | xp-winst voorstel |
|---|---|---|---|---|---|---|
| Steel van een kind | 1% (`min(50, xp/2)`) | ~20% (`min(60, 20 + xp/2)`) | €1-10 | €20-60 | +1 | +2 |
| Steel van een puber | 1% (`min(50, xp/3.33)`) | ~15% (`min(50, 15 + xp/3)`) | €1-100 | €60-200 | +1 | +3 |
| Beroof een juwelier | 1% (`min(50, xp/6.66)`) | ~12% (`min(40, 12 + xp/5)`) | €500-1.000 | €1.500-3.000 | +1 | +5 |

"Schiet op brievenbussen" (moordervaring, geen geld) en "steel van een
member" (afhankelijk van het slachtoffer) blijven ongewijzigd.

**Waarom juwelier de kern van de fix is:** bij een verwachtingswaarde van
0,12 × €2.250 ≈ €270 per poging haalt een speler die vooral op de juwelier
inzet binnen ~35-40 pogingen (30-45 minuten) de doelstelling van €10.000,
met natuurlijke versnelling doordat xp — en dus de slaagkans — meestijgt met
elk succes. "Kind" en "puber" blijven lagere-risico noodgrepen voor wie
liever een zekerder, kleiner bedrag pakt.

### Rijlessen (`rijbewijs.php`)

| | Nu | Voorstel |
|---|---|---|
| `LES_PRIJS` | €25.000 | €5.000 |
| Voortgang per geslaagde les | `random_int(5, 30) / 10` (0,5%-3,0%) | `random_int(10, 25)` (10%-25%) |
| Verwacht aantal lessen tot 100% | ~55-60 | ~6-9 |
| Verwachte totale kosten | tot €1.400.000+ | ~€45.000 |

`LES_WACHTTIJD` (300s) en de 2/3-slaagkans op elke les blijven ongewijzigd.

### Voertuigen (`install/schema.sql`, tabel `items`, type `trans`)

Alle vier de prijzen door 10 gedeeld; de onderlinge verhouding (trein
goedkoopst/traagst, jet duurst/snelst) blijft gelijk.

| Voertuig | Prijs nu | Prijs voorstel | Reistijd (ongewijzigd) |
|---|---|---|---|
| Treinabonnement | €250.000 | €25.000 | 3600s |
| Taxi | €750.000 | €75.000 | 2400s |
| Limousine | €1.000.000 | €100.000 | 1800s |
| Privé-Jet | €1.500.000 | €150.000 | 900s |

## Validatie

Handmatige kansberekening is bij benadering. Bij de implementatie komt er
een test (`tests/geld.php`, zelfde patroon als de bestaande autodiefstal- en
wapenbalans-tests) die dit narekent in plaats van erop te vertrouwen:

1. **Misdaadsimulatie:** een verse testspeler doet 30-40 misdaadpogingen
   (afkoeltijd voor de test losgelaten). De test controleert of het totale
   verdiende bedrag in een redelijke bandbreedte rond €10.000 valt.
2. **Rijlessimulatie:** zelfde aanpak voor rijlessen — hoeveel pogingen en
   hoeveel geld het gemiddeld kost om van 0% naar 100% te komen, met een
   bovengrens die faalt als het weer te duur of te traag wordt.
3. **Datacontrole:** de nieuwe voertuigprijzen staan ook echt zo in
   `install/schema.sql`.

Blijkt uit de simulatie dat de werkelijke uitkomst te ver van het doel
afligt, dan worden de constanten bijgesteld totdat de test slaagt — de
getallen in dit document zijn een onderbouwd startpunt, geen eindpunt.

## Niet meegenomen (mogelijke vervolgissues)

- Een formele early/mid/end-game-indeling — bewust niet gekozen; deze balans
  werkt op basis van doorlopende curves, niet vaste fases.
- Faalgevolgen (celkans, gezondheidsschade) per misdaad — ongewijzigd.
- Drugs/drank-prijzen zelf, mocht handel na deze fix nog steeds oninteressant
  aanvoelen zodra reizen wél haalbaar is.
