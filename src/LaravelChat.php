<?php
namespace Dominservice\LaravelChat;

use DB;
use Config;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Dominservice\LaravelChat\Exceptions\ConversationNotFoundException;
use Dominservice\LaravelChat\Exceptions\NotEnoughUsersInConvException;
use Dominservice\LaravelChat\Exceptions\UserNotInConvException;

use Dominservice\LaravelChat\Entities\Conversation;
use Dominservice\LaravelChat\Entities\Message;

use Dominservice\LaravelChat\Models\Eloquent\Message as MessageEloquent;
use Dominservice\LaravelChat\Models\Eloquent\Conversation as ConversationEloquent;
use Dominservice\LaravelChat\Models\Eloquent\ConversationUsers;
use Dominservice\LaravelChat\Models\Eloquent\MessageStatus;
use Dominservice\LaravelChat\Repositories\Contracts\iLaravelChatRepository;


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
        $usersTableKey = \Config::get('laravel_chat.users_table_key', 'id');
        $db = app()->make('Illuminate\Database\DatabaseManager');
        parent::__construct($userModel, $usersTableKey, $db);
    }

    /**
     * @param $msgId
     * @param $userId
     * @param $status must be LaravelChat consts: DELETED, UNREAD, READ, ARCHIVED
     */
    public function markMessageAs($msgId, $userId, $status) {
        $this->markMessageAs($msgId, $userId, $status);
    }

    /**
     * @param $msgId
     * @param $userId
     * marks specific message as read
     */
    public function markMessageAsRead($msgId, $userId) {
        $this->markMessageAsRead($msgId, $userId);
    }

    /**
     * @param $msgId
     * @param $userId
     * marks specific message as unread
     */
    public function markMessageAsUnread($msgId, $userId) {
        $this->markMessageAsUnread($msgId, $userId);
    }

    /**
     * @param $msgId
     * @param $userId
     * marks specific message as delete
     */
    public function markMessageAsDeleted($msgId, $userId) {
        $this->markMessageAsDeleted($msgId, $userId);
    }

    /**
     * @param $msgId
     * @param $userId
     * marks specific message as archived
     */
    public function markMessageAsArchived($msgId, $userId) {
        $this->markMessageAsArchived($msgId, $userId);
    }

    /**
     * @param $user_id
     * @return Collection[Conversation]
     */
    public function getUserConversations($user_id) {
        $return = [];
        $conversations = new Collection();

        $convs = $this->getConversations($user_id);

        $convsIds = [];
        foreach ( $convs as $conv ) {
            //this is for the query later
            $convsIds[] = $conv->conversation_id;

            //this is for the return result
            $conv->users = [];
            $return[$conv->conversation_id] = $conv;

            $conversation = new Conversation();
            $conversation->setId( $conv->conversation_id );

            $message = new Message();
            $message->setId( $conv->msgId );
            $message->setCreated( $conv->created_at );
            $message->setContent( $conv->content );
            $message->setStatus( $conv->status );
            $message->setSelf( $conv->self );
            $message->setSender( $conv->userId );
            $conversation->addMessage($message);
            $conversations[ $conversation->getId() ] = $conversation;
        }
        $convsIds = implode(',',$convsIds);


        if ( $convsIds != '' ) {

            $usersInConvs = $this->getUsersInConvs($convsIds);

            foreach ( $usersInConvs as $usersInConv ) {
                if ( $user_id != $usersInConv->id ) {
                    $user = new \stdClass();
                    $user->id = $usersInConv->id;
                    //this is for the return result
                    $return[$usersInConv->conversation_id]->users[$user->id] = $user;
                }
                $conversations[ $usersInConv->conversation_id ]->addParticipant( $usersInConv->id );
            }
        }


        return $conversations;
    }

    /**
     * @param $conv_id
     * @param $user_id
     * @param bool $newToOld
     * @return Conversation
     *
     * return full conversation of user with his specific messages and messages statuses
     */
    public function getConversationMessages($conv_id, $user_id, $newToOld=true) {

        $results = $this->getConversationMessages($conv_id, $user_id, $newToOld);

        $conversation = new Conversation();
        foreach ( $results as $row )
        {
            $msg = new Message();
            $msg->setId( $row->msgId );
            $msg->setContent( $row->content );
            $msg->setCreated( $row->created_at );
            $msg->setSender( $row->userId );
            $msg->setStatus($row->status);
            $conversation->addMessage( $msg );
        }

        $usersInConv = $this->getUsersInConversation($conv_id);
        foreach ( $usersInConv as $userInConv )
            $conversation->addParticipant( $userInConv );


        return $conversation;
    }

    /**
     * @param $userA_id
     * @param $userB_id
     * @return int
     * @throws ConversationNotFoundException
     * returns the conversation id
     * or
     * -1 if conversation not found
     */
    public function getConversationByTwoUsers($userA_id, $userB_id) {
        try {
            $conv = $this->getConversationByTwoUsers($userA_id, $userB_id);
        } catch (ConversationNotFoundException $ex) {
            return -1;
        }
        return $conv;
    }

    /**
     * @param $conv_id
     * @param $user_id
     * @param $content
     * @return array
     *
     * send message to conversation from specific user, and return the new message data
     */
    public function addMessageToConversation($conv_id, $user_id, $content) {
        return $this->addMessageToConversation($conv_id, $user_id, $content);
    }

    /**
     * @param array $users_ids
     * @throws Exceptions\NotEnoughUsersInConvException
     * @return ConversationEloquent
     */
    public function createConversation( $users_ids ) {
        return $this->createConversation($users_ids);
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
    public function sendMessageBetweenTwoUsers($senderId, $receiverId, $content)
    {
        //get conversation by two users
        try {
            $conv = $this->getConversationByTwoUsers($senderId, $receiverId);
        } catch (ConversationNotFoundException $ex) {
            //if conversation doesnt exist, create it
            $conv = $this->createConversation([$senderId, $receiverId]);
            $conv = $conv['convId'];
        }

        //add message to new conversation
        return $this->addMessageToConversation($conv, $senderId, $content);
    }

    /**
     * @param $conv_id
     * @param $user_id
     *
     * mark all messages for specific user in specific conversation as read
     */
    public function markReadAllMessagesInConversation($conv_id, $user_id) {
        $this->markReadAllMessagesInConversation($conv_id, $user_id);
    }

    /**
     * @param $conv_id
     * @param $user_id
     *
     * mark all messages for specific user in specific conversation as unread
     */
    public function markUnreadAllMessagesInConversation($conv_id, $user_id) {
        $messageStatus = DB::getTablePrefix() . (new MessageStatus())->getTable();
        DB::statement("
            UPDATE `{$messageStatus}` `mst`
            SET `mst`.`status`=?
            WHERE `mst`.`user_id`=?
            AND `mst`.`status`=?
            AND `mst`.`message_id` IN (
              SELECT `msg`.`id`
              FROM `messages` `msg`
              WHERE `msg`.`conversation_id`=?
              AND `msg`.`sender_id`!=?
            )
            ",
            [self::UNREAD, $user_id, self::READ, $conv_id, $user_id]
        );
    }

    public function deleteConversation($conv_id, $user_id) {
        $this->deleteConversation($conv_id, $user_id);
    }

    /**
     * @param $conv_id
     * @param $user_id
     * @return bool
     *
     * checks if specific user is in specific conversation
     */
    public function isUserInConversation($conv_id, $user_id) {
        return $this->isUserInConversation($conv_id, $user_id);
    }

    /**
     * @param $conv_id
     * @return array
     *
     * get an array of user id that participate in specific conversation
     */
    public function getUsersInConversation($conv_id) {
        return $this->getUsersInConversation($conv_id);
    }

    /**
     * @param $user_id
     * @return int
     *
     * get number of unread messages for specific user
     */
    public function getNumOfUnreadMsgs($user_id) {
        return $this->getNumOfUnreadMsgs($user_id);
    }
}
