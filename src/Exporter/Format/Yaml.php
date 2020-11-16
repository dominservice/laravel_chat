<?php

namespace Dominservice\laravelListGenerator\Exporter\Format;

use Dominservice\laravelListGenerator\Exporter\Exporter;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

class Yaml extends Exporter
{
    /**
     * {@inheritdoc}
     */
    public function export(array $data)
    {
        return SymfonyYaml::dump($data);
    }
}
