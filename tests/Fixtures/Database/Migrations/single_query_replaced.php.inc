<?php

declare(strict_types=1);

namespace Materijal\Database\Migrations;

final class SingleQueryReplaced extends BaseMigration
{
    public function up(): void
    {
        $this->db->query('CREATE TRIGGER `test_trigger` AFTER INSERT ON `foo` FOR EACH ROW BEGIN END');
    }

    public function down(): void
    {
        $this->db->query('DROP TRIGGER IF EXISTS `test_trigger`');
    }
}

-----
<?php

declare(strict_types=1);

namespace Materijal\Database\Migrations;

final class SingleQueryReplaced extends BaseMigration
{
    public function up(): void
    {
        $this->queryOrFail('single_query_replaced', 'CREATE TRIGGER `test_trigger` AFTER INSERT ON `foo` FOR EACH ROW BEGIN END');
    }

    public function down(): void
    {
        $this->queryOrFail('single_query_replaced', 'DROP TRIGGER IF EXISTS `test_trigger`');
    }
}

