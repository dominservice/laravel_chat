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
