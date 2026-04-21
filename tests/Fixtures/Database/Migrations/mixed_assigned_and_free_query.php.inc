<?php

declare(strict_types=1);

namespace Materijal\Database\Migrations;

final class MixedAssignedAndFreeQuery extends BaseMigration
{
    public function up(): void
    {
        // Assigned SELECT -> stays unchanged
        $check = $this->db->query('SELECT 1 FROM `process_stages` WHERE `deleted_at` IS NULL LIMIT 1');
        if ($check->getNumRows() === 0) {
            return;
        }

        // Unassigned DDL -> replaced
        $this->db->query('CREATE TRIGGER `trg_mixed` AFTER INSERT ON `dokumenti` FOR EACH ROW BEGIN END');
    }

    public function down(): void
    {
        $this->db->query('DROP TRIGGER IF EXISTS `trg_mixed`');
    }
}

-----
<?php

declare(strict_types=1);

namespace Materijal\Database\Migrations;

final class MixedAssignedAndFreeQuery extends BaseMigration
{
    public function up(): void
    {
        // Assigned SELECT -> stays unchanged
        $check = $this->db->query('SELECT 1 FROM `process_stages` WHERE `deleted_at` IS NULL LIMIT 1');
        if ($check->getNumRows() === 0) {
            return;
        }

        // Unassigned DDL -> replaced
        $this->queryOrFail('mixed_assigned_and_free_query', 'CREATE TRIGGER `trg_mixed` AFTER INSERT ON `dokumenti` FOR EACH ROW BEGIN END');
    }

    public function down(): void
    {
        $this->queryOrFail('mixed_assigned_and_free_query', 'DROP TRIGGER IF EXISTS `trg_mixed`');
    }
}

