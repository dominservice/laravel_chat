<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageStatuses extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('message_statuses', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('message_id');
            $table->boolean('self');
            $table->integer('status');

            $table->index('message_id');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::drop('messages_status');
	}

}