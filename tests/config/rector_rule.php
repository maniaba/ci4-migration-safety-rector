<?php

declare(strict_types=1);

use Maniaba\Rector\RectorRules\MigrationQueryToQueryOrFailRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        MigrationQueryToQueryOrFailRector::class,
    ]);

