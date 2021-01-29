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
 * Class Conversation
 * @package Dominservice\LaravelChat\Models\Eloquent
 */
class Conversation  extends Model
{
    const GROUP = 'group';
    const COUPLE = 'couple';

    public function users() {
        $userModel = \Config::get('laravel_chat.user_model', \App\User::class);
        return $this->belongsToMany($userModel,
            'conversation_users',
            'conversation_id',
            'user_id'
        );
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id', 'id');
    }

    function getNumOfUsers()
    {
        return !empty($this->users) ? $this->users->count() : 0;
    }

    function getNumOfMessages()
    {
        return !empty($this->messages) ? $this->messages->count() : 0;
    }

    function getTheOtherUser($userId = null)
    {
        $userId = !$userId && \Auth::check() ? \Auth::user()->id : $userId;

        if($users = !empty($this->users) ? clone $this->users : null) {
            foreach ($users as $id=>$user) {
                if ((int)$user->id === (int)$userId) {
                    $users->forget($id);
                    break;
                }
            }
        }

        return $users;
    }

    function getFirstMessage()
    {
        return !empty($this->messages) ? $this->messages->first() : null;
    }

    /**
     * @return Message
     */
    function getLastMessage()
    {
        return !empty($this->messages) ? $this->messages->last() : null;
    }

    /**
     * @return mixed
     */
    public function getType() {
        if ( $this->getNumOfUsers() > 2 )
            return self::GROUP;
        return self::COUPLE;
    }
}
