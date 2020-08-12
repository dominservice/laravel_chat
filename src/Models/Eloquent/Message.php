<?php
namespace Dominservice\LaravelChat\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Message
 * @package Dominservice\LaravelChat\Models\Eloquent
 */
class Message extends Model
{
    public function sender() {
        $userModel = \Config::get('laravel_chat.user_model', \App\User::class);
        return $this->hasOne($userModel, 'id', 'sender_id');
    }
}
