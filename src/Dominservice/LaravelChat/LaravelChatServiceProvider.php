<?php namespace Dominservice\LaravelChat;

use Illuminate\Support\ServiceProvider;
use Dominservice\LaravelChat\Repositories\EloquentLaravelChatRepository;


class LaravelChatServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

    public function boot() {
        $this->publishes([
            __DIR__.'/../../config/laravel_chat.php' => config_path('laravel_chat.php'),
        ], 'config');

        // Publish your migrations
        $this->publishes([
            __DIR__.'/../../migrations/' => base_path('/database/migrations')
        ], 'migrations');
    }

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $usersTable = \Config::get('laravel_chat.usersTable', 'users');
        $usersTableKey = \Config::get('laravel_chat.usersTableKey', 'id');
        $tablePrefix = \Config::get('laravel_chat.tablePrefix', '');

        $this->app->bind('Dominservice\LaravelChat\Repositories\Contracts\iLaravelChatRepository',
            function($app) use($tablePrefix, $usersTable, $usersTableKey) {
				$db = $app->make('Illuminate\Database\DatabaseManager');
                return new EloquentLaravelChatRepository($tablePrefix, $usersTable, $usersTableKey, $db);
            });

        // Register 'laravel_chat'
        $this->app['laravel_chat'] = $this->app->singleton('laravel_chat', function($app) {
            return new LaravelChat(
                $app['Dominservice\LaravelChat\Repositories\Contracts\iLaravelChatRepository'],
                $app['Illuminate\Contracts\Events\Dispatcher'] //Illuminate\Events\Dispatcher
            );
        });
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

}
