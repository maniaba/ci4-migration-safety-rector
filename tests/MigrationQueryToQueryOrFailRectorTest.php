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
 * Tests {@see MigrationQueryToQueryOrFailRector}.
 *
 * Cases:
 *  1. Simple unassigned query() in a migration -> replaced with queryOrFail()
 *  2. Multiple unassigned query() calls -> all replaced
 *  3. Assigned query() (SELECT) -> remains $this->db->query()
 *  4. Class outside the Database/Migrations directory -> rule is not applied
 *  5. query() on another object ($this->forge) -> unchanged
 *  6. Mixed case: assigned + unassigned -> only unassigned is replaced
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
        yield 'simple unassigned query is replaced' => [
            __DIR__ . '/Fixtures/Database/Migrations/single_query_replaced.php.inc',
        ];

        yield 'multiple unassigned queries are all replaced' => [
            __DIR__ . '/Fixtures/Database/Migrations/multiple_queries_replaced.php.inc',
        ];

        yield 'assigned query (SELECT) stays, unassigned is replaced' => [
            __DIR__ . '/Fixtures/Database/Migrations/assigned_query_not_replaced.php.inc',
        ];

        yield 'query() outside migrations directory is not replaced' => [
            __DIR__ . '/Fixtures/NonMigration/outside_migration_not_replaced.php.inc',
        ];

        yield 'query() on $this->forge must not be replaced' => [
            __DIR__ . '/Fixtures/Database/Migrations/other_object_query_not_replaced.php.inc',
        ];

        yield 'mixed case: only unassigned queries are replaced' => [
            __DIR__ . '/Fixtures/Database/Migrations/mixed_assigned_and_free_query.php.inc',
        ];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/rector_rule.php';
    }
}
