<?php

declare(strict_types=1);

namespace Materijal\Database\Migrations;

final class MultipleQueriesReplaced extends BaseMigration
{
    public function up(): void
    {
        $this->db->query('DROP TRIGGER IF EXISTS `trg_a`');
        $this->db->query('CREATE TRIGGER `trg_a` BEFORE INSERT ON `orders` FOR EACH ROW BEGIN SET NEW.created_at := UNIX_TIMESTAMP(); END');
        $this->db->query('DROP TRIGGER IF EXISTS `trg_b`');
        $this->db->query('CREATE TRIGGER `trg_b` BEFORE UPDATE ON `orders` FOR EACH ROW BEGIN SET NEW.updated_at := UNIX_TIMESTAMP(); END');
    }

    public function down(): void
    {
        $this->db->query('DROP TRIGGER IF EXISTS `trg_a`');
        $this->db->query('DROP TRIGGER IF EXISTS `trg_b`');
    }
}

-----
<?php

declare(strict_types=1);

namespace Materijal\Database\Migrations;

final class MultipleQueriesReplaced extends BaseMigration
{
    public function up(): void
    {
        $this->queryOrFail('multiple_queries_replaced', 'DROP TRIGGER IF EXISTS `trg_a`');
        $this->queryOrFail('multiple_queries_replaced', 'CREATE TRIGGER `trg_a` BEFORE INSERT ON `orders` FOR EACH ROW BEGIN SET NEW.created_at := UNIX_TIMESTAMP(); END');
        $this->queryOrFail('multiple_queries_replaced', 'DROP TRIGGER IF EXISTS `trg_b`');
        $this->queryOrFail('multiple_queries_replaced', 'CREATE TRIGGER `trg_b` BEFORE UPDATE ON `orders` FOR EACH ROW BEGIN SET NEW.updated_at := UNIX_TIMESTAMP(); END');
    }

    public function down(): void
    {
        $this->queryOrFail('multiple_queries_replaced', 'DROP TRIGGER IF EXISTS `trg_a`');
        $this->queryOrFail('multiple_queries_replaced', 'DROP TRIGGER IF EXISTS `trg_b`');
    }
}

