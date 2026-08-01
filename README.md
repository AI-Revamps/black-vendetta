# Black Vendetta

Een online tekst-rollenspel in de onderwereld: misdaden plegen, auto's stelen,
handelen in drank en drugs, een familie opbouwen en concurrenten uit de weg
ruimen.

Dit is een volledige herbouw van een spel uit ongeveer 2005. De oude code
draaide niet meer op een moderne server — `mysql_*` en `eregi_replace()` zijn
uit PHP verdwenen — en zat vol beveiligingsgaten. Wat hier staat is opnieuw
geschreven, met dezelfde spelbeleving.

## Uitgangspunten

- **Geen frameworks en geen Composer.** Alleen PHP, MySQL en een handvol regels
  JavaScript.
- **Werkt op gewone shared hosting.** Uploaden per FTP, database aanmaken via
  phpMyAdmin, klaar. Geen SSH nodig.
- **Eén bestand per pagina, geen router.** Voorspelbaar, en er is geen
  `mod_rewrite` voor nodig. Adressen zonder `.php` kunnen wel, als instelling.
- **Werkt zonder JavaScript.** Dat maakt het alleen prettiger.

## Wat je nodig hebt

| | |
|---|---|
| PHP | 8.1 of nieuwer, met `pdo_mysql` en `mbstring` |
| Database | MySQL 5.7+ of MariaDB 10.2+ |
| Aanbevolen | de `gd`-uitbreiding, voor de plaatjes met de beveiligingscode |

## Installeren

1. Maak bij je host een lege MySQL-database en een gebruiker aan.
2. Upload alles naar je webmap.
3. Ga naar `https://jouwdomein.nl/install/`.
4. **Verwijder daarna de map `install/`.**

De uitgebreide versie, inclusief wat je vóór het opengaan moet controleren,
staat in **[docs/INSTALLATIE.md](docs/INSTALLATIE.md)**.

## Documentatie

| Document | Waarover |
|---|---|
| [docs/INSTALLATIE.md](docs/INSTALLATIE.md) | installeren, instellingen, veelgestelde vragen |
| [docs/BEHEER.md](docs/BEHEER.md) | het spel draaien: beheerpagina's, rechten, premium |
| [docs/ARCHITECTUUR.md](docs/ARCHITECTUUR.md) | hoe de code in elkaar zit |
| [docs/ONTWIKKELEN.md](docs/ONTWIKKELEN.md) | lokaal draaien en testen |
| [docs/HERBOUW.md](docs/HERBOUW.md) | verslag van de herbouw en wat er gerepareerd is |
| [docs/LOGOBESTANDEN.md](docs/LOGOBESTANDEN.md) | welke logobestanden waar horen |

Werk je met een AI-assistent aan dit project? Begin dan bij
**[CLAUDE.md](CLAUDE.md)**.

## Mappen

```
/                 de pagina's van het spel, één bestand per pagina
/inc              de fundering: database, sessies, opmaak, spellogica
/install          de installer plus schema.sql; verwijderen na installatie
/assets           stylesheet, script, logo
/images /smilies  plaatjes van het spel
/docs             documentatie
/tests            testscripts
```

## Licentie

MIT — zie [LICENSE](LICENSE). Je mag dit gebruiken, aanpassen en verspreiden,
ook voor een spel waar je geld mee verdient. De enige voorwaarde is dat de
licentietekst meegaat met je kopie. Er zit geen enkele garantie op.

Het logo en de plaatjes in `images/` en `smilies/` vallen daar niet
vanzelfsprekend onder; controleer zelf of je die mag hergebruiken.
