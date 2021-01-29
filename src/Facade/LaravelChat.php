<?php

/**
 * Data Locale Parser
 *
 * This package will allow you to add a full user messaging system
 * into your Laravel application.
 *
 * @package   Dominservice\LaravelChat
 * @author    DSO-IT Mateusz Domin <biuro@dso.biz.pl>
 * @copyright (c) 2021 DSO-IT Mateusz Domin
 * @license   MIT
 * @version   2.1.0
 */

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
    protected static function getFacadeAccessor() {
        return 'laravel_chat';
    }

}