<?php

declare(strict_types=1);

namespace App\Libraries;

// This class is NOT in a Database/Migrations path -> rule must not apply.
final class NotAMigration
{
    public function doSomething(): void
    {
        $this->db->query('CREATE TRIGGER `foo` AFTER INSERT ON `bar` FOR EACH ROW BEGIN END');
    }
}

