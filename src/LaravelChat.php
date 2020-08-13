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
        parent::__construct($userModel);
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
        $conv = $this->getConversationByTwoUsers($senderId, $receiverId, $relationType, $relationId);
        if ($conv === null) {
            $conv = $this->createConversation([$senderId, $receiverId], $relationType, $relationId);
        }
        if($conv) {
            return $this->addMessageToConversation($conv, $senderId, $content);
        } else {
            return null;
        }
    }
}
