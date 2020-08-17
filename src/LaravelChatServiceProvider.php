<?php namespace Dominservice\LaravelChat;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

/**
 * Class LaravelChatServiceProvider
 * @package Dominservice\LaravelChat
 */
class LaravelChatServiceProvider extends ServiceProvider {

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

        // Publish your migrations
//        $this->publishes([
//            __DIR__ . '/../migrations/' => base_path('/database/migrations')
//        ], 'migrations');

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

//        $userModel = \Config::get('laravel_chat.user_model', \App\User::class);

//        // Register 'laravel_chat'
//        $this->app['laravel_chat'] = $this->app->singleton('laravel_chat', function($app) {
//            return new LaravelChat(
//                $app['Dominservice\LaravelChat\Repositories\Contracts\iLaravelChatRepository'],
//                $app['Illuminate\Contracts\Events\Dispatcher'] //Illuminate\Events\Dispatcher
//            );
//        });
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
