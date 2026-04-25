# Modernisatie-analyse Vendetta (code + UX)

## Doel en afbakening
Je wilt **geen nieuwe features**, maar wel:
1. modernere, onderhoudbare codebasis;
2. een nieuw uiterlijk dat duidelijk op de klassieke Vendetta-stijl is gebaseerd;
3. betere spelerervaring (snelheid, duidelijkheid, minder fouten).

Deze analyse richt zich dus op **herstructureren, beveiligen, opschonen en restylen** van bestaande flows.

---

## Huidige staat (kort)
Op basis van de huidige code vallen vooral deze punten op:

- Alles zit in losse `*.php` scripts met veel globale state en side-effects (bijv. `config.php` regelt DB, sessie, periodieke updates, bans en spelregels in één bestand).
- Verouderde en onveilige stack (`mysql_*`, `MD5`, directe interpolatie in SQL, weinig consistente validatie).
- Presentatie en logica zitten door elkaar (HTML + SQL + game rules in dezelfde bestanden).
- UI steunt op tabellen, vaste pixel-achtige elementen, gif-achtergronden en inline gedrag.

Concreet zichtbaar in:
- `register.php` (raw `$_POST`, `mysql_query`, `MD5`, mixed rendering).
- `config.php` (centrale "god file" met connectie, auth-context, cron updates, jail/ban checks).
- `style.css` (legacy CSS, 8pt fonts, image-based UI states).

---

## Wat moet er veranderen om dit "modern" te maken

## 1) Architectuur (zonder gameplay-wijziging)
### Van script-per-pagina naar lagen
Splits de applicatie in lagen:

- **Presentation**: controllers + templates.
- **Application**: use-cases (zoals registreren, inloggen, misdaad starten).
- **Domain**: spelregels/entiteiten (User, Crime, City, Family).
- **Infrastructure**: DB, mail, sessie, logging.

Resultaat: dezelfde game-functies, maar code wordt testbaar, herbruikbaar en veiliger.

## 2) Security & betrouwbaarheid
Zonder features toe te voegen kun je direct grote winst boeken:

- `mysql_*` -> `PDO` met prepared statements.
- `MD5` wachtwoorden -> `password_hash()` / `password_verify()`.
- Inputvalidatie centraliseren (DTO/Validator).
- CSRF-bescherming op formulieren.
- Rate limiting op login/register.
- Config secrets via `.env`.
- Sterkere sessie-instellingen (httponly, secure, sameSite).

## 3) Datalaag en migraties
- Introduceer migraties (`database/migrations`) i.p.v. ad-hoc SQL verspreid door scripts.
- Maak repositories i.p.v. directe query strings overal.
- Voeg indexen toe op veelgebruikte kolommen (login, email, status, city, timestamps).

## 4) Front-end moderniseren op basis van oud design
Behoud identiteit, vernieuw uitvoering:

- **Visuele basis behouden**:
  - donker thema;
  - oranje accentkleur;
  - "crime dashboard" sfeer.
- **Moderniseren**:
  - CSS variables, responsive layout, grid/flex.
  - kaart-achtige panelen in plaats van tabelstructuur.
  - consistente spacing/typografie (min 14-16px body voor leesbaarheid).
  - duidelijke states voor loading, success, error.

## 5) Spelerervaring verbeteren (zonder nieuwe features)
- Snellere pagina’s door asset bundling en caching.
- Minder "where am I?" met vaste topbar + actieve sectie.
- Betere formulieren:
  - inline foutmeldingen;
  - veldbehoud na validatiefout;
  - duidelijke bevestigingsboodschappen.
- Betere mobiele ervaring (nu vaak desktop-only look).

---

## Voorstel nieuwe projectstructuur

```text
black-vendetta/
  app/
    Application/
      Auth/
        RegisterUser.php
      Player/
      Crime/
    Domain/
      User/
        User.php
        UserRepository.php
      Shared/
    Infrastructure/
      Persistence/
        PdoConnection.php
        MysqlUserRepository.php
      Mail/
        Mailer.php
      Security/
        PasswordHasher.php
    Http/
      Controller/
        Auth/
          RegisterController.php
      Middleware/
      Request/
      Response/
  config/
    app.php
    database.php
  database/
    migrations/
    seeders/
  public/
    index.php
    assets/
      css/
        app.css
      js/
        app.js
      images/
  resources/
    views/
      layouts/
      auth/
        register.php
  routes/
    web.php
  storage/
    logs/
    cache/
  tests/
    Unit/
    Feature/
```

### Mapping van oud naar nieuw
- `register.php` -> `Http/Controller/Auth/RegisterController.php` + `Application/Auth/RegisterUser.php` + `resources/views/auth/register.php`.
- `config.php` -> meerdere configuraties + middleware + scheduler/worker.
- `style.css` -> opgesplitst in design tokens, components en utilities.

---

## Gefaseerde aanpak (laag risico)

1. **Fase 0 – Stabilisatie**
   - logging + foutafhandeling centraliseren;
   - read-only inventaris van huidige gameplay-routes.

2. **Fase 1 – Auth migreren**
   - register/login/activatie als eerste domein;
   - oude routes laten doorverwijzen naar nieuwe handlers.

3. **Fase 2 – UI shell**
   - nieuwe layout (header/sidebar/content cards) met oude kleuren/branding.

4. **Fase 3 – Pagina per pagina migreren**
   - bestaande spelmodules overzetten zonder rule changes.

5. **Fase 4 – Opschonen**
   - legacy scripts verwijderen nadat parity bewezen is.

---

## Voorbeeld: moderne registratie (zonder extra features)
Onderstaand voorbeeld laat alleen zien **hoe dezelfde registratie-flow technisch moderner kan** (validatie, unieke checks, hashen, transacties). De functionele inhoud blijft gelijk.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controller\Auth;

use PDO;

final class RegisterController
{
    public function __construct(private PDO $db) {}

    public function __invoke(array $post, string $ipAddress): array
    {
        $username = trim((string)($post['gebruiker'] ?? ''));
        $password = (string)($post['pass'] ?? '');
        $passwordConfirm = (string)($post['passconfirm'] ?? '');
        $email = trim((string)($post['email'] ?? ''));
        $gender = (string)($post['geslacht'] ?? 'Man');
        $referrerId = trim((string)($post['refer'] ?? ''));

        if (!preg_match('/^[a-zA-Z0-9_-]{3,16}$/', $username)) {
            return ['ok' => false, 'message' => 'Gebruikersnaam is ongeldig.'];
        }

        if ($password === '' || $password !== $passwordConfirm || strlen($password) < 8) {
            return ['ok' => false, 'message' => 'Wachtwoordvalidatie mislukt.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'message' => 'E-mailadres is ongeldig.'];
        }

        $this->db->beginTransaction();

        try {
            $existsStmt = $this->db->prepare('SELECT 1 FROM users WHERE login = :login LIMIT 1');
            $existsStmt->execute(['login' => $username]);

            if ($existsStmt->fetchColumn()) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'Gebruikersnaam bestaat al.'];
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $insert = $this->db->prepare(
                'INSERT INTO users (start, login, pass, ip, email, stad, geslacht, activated)
                 VALUES (NOW(), :login, :pass, :ip, :email, :stad, :geslacht, 0)'
            );

            $starterCities = ['Brussel', 'Leuven', 'Gent', 'Brugge', 'Hasselt', 'Antwerpen', 'Amsterdam', 'Enschede'];
            $city = $starterCities[array_rand($starterCities)];

            $insert->execute([
                'login' => $username,
                'pass' => $hash,
                'ip' => $ipAddress,
                'email' => $email,
                'stad' => $city,
                'geslacht' => $gender,
            ]);

            $userId = (int)$this->db->lastInsertId();
            $activationCode = random_int(100000, 999999);

            $tempInsert = $this->db->prepare(
                'INSERT INTO temp (login, ip, code, area, time, forwardedFor)
                 VALUES (:login, :ip, :code, :area, NOW(), :forwardedFor)'
            );

            $tempInsert->execute([
                'login' => $username,
                'ip' => $ipAddress,
                'code' => $activationCode,
                'area' => 'signup',
                'forwardedFor' => $referrerId !== '' ? $referrerId : null,
            ]);

            // Mail-service aanroepen (hier weggelaten), inhoud functioneel gelijk houden.

            $this->db->commit();

            return [
                'ok' => true,
                'message' => 'Je bent geregistreerd. Controleer je e-mail voor activatie.',
                'user_id' => $userId,
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return ['ok' => false, 'message' => 'Er ging iets mis tijdens registreren.'];
        }
    }
}
```

---

## UX-richting: "classic noir, modern usability"
Designrichting voor jouw nieuwe look (op oud gebaseerd):

- **Kleur**: charcoal/zwart als basis, oranje highlight, subtiele metalen/grain textuur.
- **Typografie**: modern sans voor body + optionele display font voor titels.
- **Componenten**:
  - dashboard cards met duidelijke hiërarchie;
  - status badges voor rank/health/energy;
  - primaire CTA’s visueel sterk maar consistent.
- **Interactie**:
  - microfeedback (hover/focus/pressed) op knoppen;
  - snelle, duidelijke feedback na acties (success/error banners).

---

## Concrete prioriteiten voor jouw volgende sprint
1. Technische basis: `PDO + password_hash + centrale validatie`.
2. Nieuwe layout-shell (header/sidebar/main) met huidige huisstijl-kleuren.
3. Registratiepagina als eerste volledig gemoderniseerde flow.
4. Testset voor registratie (happy path + validatiefouten).

Dan heb je direct zichtbaar resultaat voor spelers én een solide basis voor de rest van de game.
