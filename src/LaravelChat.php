<?php
namespace Dominservice\LaravelChat;

use Config;
use Dominservice\LaravelChat\Exceptions\ConversationNotFoundException;



use Dominservice\LaravelChat\Repositories\EloquentLaravelChatRepository;

/**
 * Class LaravelChat
 * @package Dominservice\LaravelChat
 */
class LaravelChat extends EloquentLaravelChatRepository{

    const DELETED = 0;
    const UNREAD = 1;
    const READ = 2;
    const ARCHIVED = 3;
    protected $usersTableKey;

    /**
     * LaravelChat constructor.
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct() {
        $userModel = \Config::get('laravel_chat.user_model', \App\User::class);
        $usersTableKey = \Config::get('laravel_chat.user_primary_key', 'id');
        $db = app()->make('Illuminate\Database\DatabaseManager');
        parent::__construct($userModel, $usersTableKey, $db);
    }
    
    /**
     * @param $senderId
     * @param $receiverId
     * @param $content
     * @return array
     *
     * send message to specific user from specific user, and return the new message data
     * if conversation is not existing yet between users it will create it
     */
    public function sendMessageBetweenTwoUsers($senderId, $receiverId, $content, $relationType = null, $relationId = null)
    {
        //get conversation by two users
        try {
            $conv = $this->getConversationByTwoUsers($senderId, $receiverId, $relationType, $relationId);
        } catch (ConversationNotFoundException $ex) {
            //if conversation doesnt exist, create it
            $conv = $this->createConversation([$senderId, $receiverId], $relationType, $relationId);
            $conv = $conv['convId'];
        }

        //add message to new conversation
        return $this->addMessageToConversation($conv, $senderId, $content);
    }
}
