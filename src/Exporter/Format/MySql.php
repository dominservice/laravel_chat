<?php

namespace Dominservice\laravelListGenerator\Exporter\Format;

use Dominservice\laravelListGenerator\Exporter\SqlExporter;

class MySql extends SqlExporter
{
    /**
     * {@inheritdoc}
     */
    public function getDriver()
    {
        return 'pdo_mysql';
    }
}
