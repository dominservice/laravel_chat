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

namespace Dominservice\LaravelChat;


use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

/**
 * Class LaravelChatServiceProvider
 * @package Dominservice\LaravelChat
 */
class LaravelChatServiceProvider extends ServiceProvider
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

    public function boot(Filesystem $filesystem) {
        $this->publishes([
            __DIR__ . '/../config/laravel_chat.php' => config_path('laravel_chat.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations/create_conversations_tables.php.stub' => $this->getMigrationFileName($filesystem),
        ], 'migrations');
    }

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel_chat.php',
            'laravel_chat'
        );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param Filesystem $filesystem
     * @return string
     */
    protected function getMigrationFileName(Filesystem $filesystem): string
    {
        $timestamp = date('Y_m_d_His');

        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem) {
                return $filesystem->glob($path.'*_create_conversations_tables.php');
            })->push($this->app->databasePath()."/migrations/{$timestamp}_create_conversations_tables.php")
            ->first();
    }

}
