<?php

declare(strict_types=1);

namespace Tests;

use Iterator;
use Maniaba\Rector\RectorRules\MigrationQueryToQueryOrFailRector;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @internal
 *
 * Testira {@see MigrationQueryToQueryOrFailRector}.
 *
 * Slucajevi:
 *  1. Jednostavan unassigned query() u migraciji -> zamjenjen sa queryOrFail()
 *  2. Vise unassigned query() poziva -> svi zamjenjeni
 *  3. Assigned query() (SELECT) -> ostaje $this->db->query()
 *  4. Klasa izvan Database/Migrations direktorija -> rule se ne primjenjuje
 *  5. query() na drugom objektu ($this->forge) -> ne mijenja se
 *  6. Mjesovit slucaj: assigned + unassigned -> samo unassigned se zamjenjuje
 */
final class MigrationQueryToQueryOrFailRectorTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function testRule(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    /**
     * @return Iterator<string, array{string}>
     */
    public static function provideData(): Iterator
    {
        yield 'jednostavan unassigned query se zamjenjuje' => [
            __DIR__ . '/Fixtures/Database/Migrations/single_query_replaced.php.inc',
        ];

        yield 'visestruki unassigned queryi se svi zamjenjuju' => [
            __DIR__ . '/Fixtures/Database/Migrations/multiple_queries_replaced.php.inc',
        ];

        yield 'assigned query (SELECT) ostaje, unassigned se zamjenjuje' => [
            __DIR__ . '/Fixtures/Database/Migrations/assigned_query_not_replaced.php.inc',
        ];

        yield 'query() izvan migrations direktorija se ne zamjenjuje' => [
            __DIR__ . '/Fixtures/NonMigration/outside_migration_not_replaced.php.inc',
        ];

        yield 'query() na $this->forge ne smije biti zamjenjen' => [
            __DIR__ . '/Fixtures/Database/Migrations/other_object_query_not_replaced.php.inc',
        ];

        yield 'mjesovit slucaj: samo unassigned queryi se zamjenjuju' => [
            __DIR__ . '/Fixtures/Database/Migrations/mixed_assigned_and_free_query.php.inc',
        ];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/rector_rule.php';
    }
}

