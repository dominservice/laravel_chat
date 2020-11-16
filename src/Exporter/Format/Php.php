<?php

namespace Dominservice\laravelListGenerator\Exporter\Format;

use Dominservice\laravelListGenerator\Exporter\Exporter;

class Php extends Exporter
{
    /**
     * {@inheritdoc}
     */
    public function export(array $data)
    {
        return sprintf('<?php return %s;%s', var_export($data, true), PHP_EOL);
    }
}
