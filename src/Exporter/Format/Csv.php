<?php

namespace Dominservice\laravelListGenerator\Exporter\Format;

use Dominservice\laravelListGenerator\Exporter\Exporter;

class Csv extends Exporter
{
    /**
     * {@inheritdoc}
     */
    public function export(array $data)
    {
        $outstream = fopen('php://temp', 'r+');
        fputcsv($outstream, array('id', 'value'));
        foreach ($data as $iso => $name) {
            fputcsv($outstream, array($iso, $name));
        }
        rewind($outstream);
        $csv = stream_get_contents($outstream);
        fclose($outstream);

        return $csv;
    }
}
