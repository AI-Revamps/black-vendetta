# black-vendetta

This project is a browser based crime game. The original code used legacy MySQL functions and framesets. Parts of the application are gradually being modernised.
The database layer now uses PDO internally via a compatibility wrapper. Every PHP
script loads `config.php`, which exposes old `mysql_*` functions mapped to PDO.
Existing queries continue to run while the codebase is gradually migrated.

## Requirements

- PHP 8.4 or newer
- MySQL

Set the following environment variables to configure the database connection:

- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`

## Running

1. Configure the webserver to serve the repository.
2. Ensure the environment variables are available to PHP.
3. Open `login.php` to log in or register.

The main page `index.php` now uses Bootstrap and `<iframe>` elements instead of the old frameset layout.

## Tests

PHPUnit tests live in the `tests/` directory. Install the development dependencies using Composer:

```bash
composer install
```

Run the tests with:

```bash
composer test
```
