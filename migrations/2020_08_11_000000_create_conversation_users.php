<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationUsers extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversation_users', function(Blueprint $table)
        {
            $table->unsignedBigInteger('conversation_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();


            $table->primary(array('conversation_id', 'user_id'));

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('conversation_users');
    }

}