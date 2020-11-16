<?php

namespace Dominservice\laravelListGenerator\Importer;

abstract class Importer implements ImporterInterface
{
    /**
     * Gets source name.
     *
     * @return string
     */
    public function getSource()
    {
        return strtolower(get_class($this));
    }
}
