<?php

namespace Dominservice\laravelListGenerator\Exporter\Format;

use Dominservice\laravelListGenerator\Exporter\Exporter;

class Json extends Exporter
{
    /**
     * {@inheritdoc}
     */
    public function export(array $data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
