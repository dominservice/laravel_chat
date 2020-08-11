<?php

use Illuminate\Database\Capsule\Manager;

require __DIR__.'/TestDispatcher.php';

class TestCaseDb extends \PHPUnit_Framework_TestCase {

	protected $db;
	/** @var \Dominservice\LaravelChat\LaravelChat */
	protected $laravel_chat;

	public function setUp() {
		parent::setUp(); // Don't forget this!

		League\FactoryMuffin\Facade::getFaker()->unique($reset = true);
		$this->initDb();
		$this->initTbmsg();
	}

	protected function initDb() {
		$capsule = new Manager();

		$capsule->addConnection([
			'driver'    => 'sqlite',
			'database'  => ':memory:',
			'prefix'    => '',
			'fetch'		=> PDO::FETCH_CLASS
		]);
		$capsule->getConnection()->setFetchMode(PDO::FETCH_CLASS);

		$capsule->setAsGlobal();
		$capsule->bootEloquent();

		$this->db = $capsule->getDatabaseManager();

		$this->createTables();

		$this->initMuffing();
		return $this->db;
	}
	protected function createTables() {
		Manager::schema()->create('conv_users', function($table)
		{
			$table->integer('conv_id')->nullable();
			$table->integer('user_id')->nullable();

			$table->primary(array('conv_id', 'user_id'));
		});

		Manager::schema()->create('conversations', function($table)
		{
			$table->increments('id');

			$table->softDeletes();
			$table->timestamps();
		});

		Manager::schema()->create('messages', function($table)
		{
			$table->increments('id');
			$table->integer('sender_id');
			$table->integer('conv_id');
			$table->text('content');
			$table->timestamps();

			$table->index('sender_id');
			$table->index('conv_id');
		});

		Manager::schema()->create('messages_status', function($table)
		{
			$table->increments('id');
			$table->integer('user_id');
			$table->integer('msg_id');
			$table->boolean('self');
			$table->integer('status');

			$table->index('msg_id');
		});
	}

	public function initTbmsg() {
        $this->laravel_chat = new \Dominservice\LaravelChat\LaravelChat(
            new \Dominservice\LaravelChat\Repositories\EloquentLaravelChatRepository('', 'users', 'id', $this->db),
            new TestDispatcher());
	}

	protected function initMuffing() {
		League\FactoryMuffin\Facade::define('Dominservice\LaravelChat\Models\Eloquent\Conversation', array(
			//'id' => 'int',
			'deleted_at' => 'dateTime',
			'created_at' => 'dateTime',
			'updated_at' => 'dateTime',
		));

		League\FactoryMuffin\Facade::define('Dominservice\LaravelChat\Models\Eloquent\ConversationUsers', array(
			'conv_id' => 'factory|Dominservice\LaravelChat\Models\Eloquent\Conversation',
			'user_id' => 'int'
		));

		League\FactoryMuffin\Facade::define('Dominservice\LaravelChat\Models\Eloquent\Message', array(
			'sender_id' => 'int',
			'conv_id' => 'factory|Dominservice\LaravelChat\Models\Eloquent\Conversation',
			'content' => 'int',
			'created_at' => 'int',
			'updated_at' => 'int',
		));


		League\FactoryMuffin\Facade::define('Dominservice\LaravelChat\Models\Eloquent\MessageStatus', array(
			'user_id' => 'int',
			'msg_id' => 'factory|Dominservice\LaravelChat\Models\Eloquent\Message',
			'self' => 'int',
			'status' => 'int',
		));

	}

}