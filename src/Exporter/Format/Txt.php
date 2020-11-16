<?php

namespace Dominservice\laravelListGenerator\Exporter\Format;

use Dominservice\laravelListGenerator\Exporter\Exporter;

class Txt extends Exporter
{
    /**
     * {@inheritdoc}
     */
    public function export(array $data)
    {
        $txt = '';
        foreach ($data as $id => $name) {
            $txt .= sprintf('%s (%s)%s', $name, $id, PHP_EOL);
        }

        return $txt;
    }
}
