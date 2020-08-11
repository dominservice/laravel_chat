<?php 

namespace Dominservice\LaravelChat\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Class LaravelChat
 * @package Dominservice\LaravelChat\Facade
 */
class LaravelChat extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'laravel_chat'; }

}