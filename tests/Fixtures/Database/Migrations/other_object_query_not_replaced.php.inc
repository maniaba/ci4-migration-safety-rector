<?php

declare(strict_types=1);

namespace Materijal\Database\Migrations;

final class OtherObjectQueryNotReplaced extends BaseMigration
{
    public function up(): void
    {
        // $this->forge->query() must not be touched
        $this->forge->query('irrelevant');
        // $this->db->query() must be replaced
        $this->db->query('CREATE TRIGGER `trg` AFTER INSERT ON `t` FOR EACH ROW BEGIN END');
    }

    public function down(): void
    {
        $this->db->query('DROP TRIGGER IF EXISTS `trg`');
    }
}

-----
<?php

declare(strict_types=1);

namespace Materijal\Database\Migrations;

final class OtherObjectQueryNotReplaced extends BaseMigration
{
    public function up(): void
    {
        // $this->forge->query() must not be touched
        $this->forge->query('irrelevant');
        // $this->db->query() must be replaced
        $this->queryOrFail('other_object_query_not_replaced', 'CREATE TRIGGER `trg` AFTER INSERT ON `t` FOR EACH ROW BEGIN END');
    }

    public function down(): void
    {
        $this->queryOrFail('other_object_query_not_replaced', 'DROP TRIGGER IF EXISTS `trg`');
    }
}

