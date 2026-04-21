<?php

declare(strict_types=1);

namespace Materijal\Database\Migrations;

final class AssignedQueryNotReplaced extends BaseMigration
{
    public function up(): void
    {
        // Assigned: result is used -> stays as $this->db->query()
        $result = $this->db->query('SELECT COUNT(*) AS cnt FROM `orders`');
        $row = $result->getRow();
        if ($row->cnt > 0) {
            // Unassigned DDL inside if-block -> replaced
            $this->db->query('ALTER TABLE `orders` ADD COLUMN `flag` TINYINT DEFAULT 0');
        }
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE `orders` DROP COLUMN `flag`');
    }
}

-----
<?php

declare(strict_types=1);

namespace Materijal\Database\Migrations;

final class AssignedQueryNotReplaced extends BaseMigration
{
    public function up(): void
    {
        // Assigned: result is used -> stays as $this->db->query()
        $result = $this->db->query('SELECT COUNT(*) AS cnt FROM `orders`');
        $row = $result->getRow();
        if ($row->cnt > 0) {
            // Unassigned DDL inside if-block -> replaced
            $this->queryOrFail('assigned_query_not_replaced', 'ALTER TABLE `orders` ADD COLUMN `flag` TINYINT DEFAULT 0');
        }
    }

    public function down(): void
    {
        $this->queryOrFail('assigned_query_not_replaced', 'ALTER TABLE `orders` DROP COLUMN `flag`');
    }
}

