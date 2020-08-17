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

    public function status() {
        return $this->hasMany(MessageStatus::class, 'message_id', 'id');
    }

    public function statusForUser($userId = null) {
        $userId = !$userId && \Auth::check() ? \Auth::user()->id : $userId;

        if (!empty($this->status)) {
            foreach ($this->status as $status) {
                if ((int)$status->user_id === (int)$userId) {
                    return $status;
                }
            }
        }
        return null;
    }
}
