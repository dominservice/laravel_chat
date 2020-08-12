<?php
namespace Dominservice\LaravelChat\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Conversation
 * @package Dominservice\LaravelChat\Models\Eloquent
 */
class Conversation  extends Model
{
    public function users() {
        $userModel = \Config::get('laravel_chat.user_model', \App\User::class);
        return $this->belongsToMany($userModel,
            'conversation_users',
            'conversation_id',
            'user_id'
        );
    }
}
