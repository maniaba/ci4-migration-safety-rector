# platforma-rector

Custom [Rector](https://getrector.com/) rules for **Platforma** — a CodeIgniter 4 application.

## Rules

### `MigrationQueryToQueryOrFailRector`

Replaces fire-and-forget `$this->db->query($sql)` calls inside CI4 migration files
with `$this->queryOrFail('ClassName', $sql)`.

**Why?**

On production CI4 deployments `DBDebug = false`, which means `$this->db->query()`
silently returns `false` on SQL error instead of throwing an exception.
The CI4 migration runner marks a migration as successful whenever `up()` returns
without throwing — so a failed `CREATE TRIGGER` (or any DDL) is silently recorded
as "run" while the trigger was never actually created in the database.

`MigrationHelper::queryOrFail()` wraps the call and throws a `RuntimeException`
on failure, making migration failures visible in deployment logs.

**What is replaced:**

```php
// Before — silently ignored on prod if the SQL fails
$this->db->query('CREATE TRIGGER `trg` AFTER INSERT ON `foo` FOR EACH ROW BEGIN END');
$this->db->query('DROP TRIGGER IF EXISTS `trg`');

// After
$this->queryOrFail('MyMigration', 'CREATE TRIGGER `trg` AFTER INSERT ON `foo` FOR EACH ROW BEGIN END');
$this->queryOrFail('MyMigration', 'DROP TRIGGER IF EXISTS `trg`');
```

**What is NOT replaced:**

```php
// Assigned calls (SELECT queries) — the result is needed, left untouched
$result = $this->db->query('SELECT COUNT(*) FROM `orders`');

// Calls outside Database/Migrations/ path — rule doesn't activate
// Calls on other objects ($this->forge->query) — only $this->db->query is targeted
```

## Installation

```bash
composer require --dev maniaba/platforma-rector
```

## Usage

In your project's `rector.php`:

```php
use Maniaba\Rector\RectorRules\MigrationQueryToQueryOrFailRector;use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        MigrationQueryToQueryOrFailRector::class,
    ]);
```

## Local development (path repository)

While developing locally alongside the main project, add to the main project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./platforma-rector"
        }
    ],
    "require-dev": {
        "maniaba/platforma-rector": "*"
    }
}
```

Then run `composer update maniaba/platforma-rector`.

## Running tests

```bash
composer test
```

> Tests use `XDEBUG_MODE=off` (set in `phpunit.xml.dist`) to prevent a Windows
> segfault caused by Xdebug coverage mode conflicting with Rector's heavy
> AST/reflection processing.

## Coding style

```bash
composer cs       # check
composer cs-fix   # fix
```

Uses [CodeIgniter Coding Standard](https://github.com/CodeIgniter/coding-standard).

