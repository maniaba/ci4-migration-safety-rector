<?php

declare(strict_types=1);

namespace Maniaba\Rector\MigrationTrait;

use CodeIgniter\Exceptions\RuntimeException;

trait QueryOrFailMethodTrait
{
    protected function queryOrFail(string $label, string $sql, mixed $binds = null): void
    {
        $result = $this->db->query($sql, $binds);
        $error  = $this->db->error();

        if ($result === false || $error['code'] !== 0) {
            throw new RuntimeException(
                $label . ' failed: Code: ' . ($error['code'] ?? 'unknown') . ' - ' . ($error['message'] ?? 'unknown'),
            );
        }
    }
}
