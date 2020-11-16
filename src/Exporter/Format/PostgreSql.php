<?php

namespace Dominservice\laravelListGenerator\Exporter\Format;

use Dominservice\laravelListGenerator\Exporter\SqlExporter;

class PostgreSql extends SqlExporter
{
    /**
     * {@inheritdoc}
     */
    public function getDriver()
    {
        return 'pdo_pgsql';
    }
}
