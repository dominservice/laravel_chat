<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessages extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('messages', function(Blueprint $table)
        {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('conversation_id');
            $table->text('content');
            $table->timestamps();

            $table->index('sender_id');
            $table->index('conversation_id');

        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::drop('messages');
	}

}