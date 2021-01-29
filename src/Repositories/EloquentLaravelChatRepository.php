<?php
namespace Dominservice\LaravelChat\Repositories;

use DB;
use Dominservice\LaravelChat\Models\Eloquent\ConversationUsers;
use Dominservice\LaravelChat\Models\Eloquent\MessageStatus;
use Dominservice\LaravelChat\Models\Eloquent\Message;
use Dominservice\LaravelChat\Models\Eloquent\Conversation;

/**
 * Class EloquentLaravelChatRepository
 * @package Dominservice\LaravelChat\Repositories
 */
class EloquentLaravelChatRepository
{
    const DELETED = 0;
    const UNREAD = 1;
    const READ = 2;
    const ARCHIVED = 3;

    protected $messagesTable;
    protected $messagesStatusTable;

    public function __construct() {
        $this->messagesTable = DB::getTablePrefix() . (new Message())->getTable();
        $this->messagesStatusTable = DB::getTablePrefix() . (new MessageStatus())->getTable();
    }

    public function createConversation($usersIds, $relationType = null, $relationId = null)
    {
        if (count((array)$usersIds ) > 1) {
            //create new conv
            $conversation = new Conversation();
            $conversation->parent_type = $relationType;
            $conversation->parent_id = $relationId;
            $conversation->save();

            //get the id of conv, and add foreach user a line in conversation_users
            foreach ( $usersIds as $user_id ) {
                $conversationUser = new ConversationUsers();
                $conversationUser->conversation_id = $conversation->id;
                $conversationUser->user_id = $user_id;
                $conversationUser->parent_type = $relationType;
                $conversationUser->parent_id = $relationId;
                $conversationUser->save();
            }
            return $conversation->id;
        }
        return null;
    }

    public function addMessageToConversation($conv_id, $user_id, $content) {
        //check if user of message is in conversation
        if ( !$this->isUserInConversation($conv_id, $user_id) ) {
            return null;
        }

        //if so add new message
        $message = new Message();
        $message->sender_id = $user_id;
        $message->conversation_id = $conv_id;
        $message->content = $content;
        $message->save();

        //get all users in conversation
        $usersInConv = $this->getUsersInConversation($conv_id);

        //and add msg status for each user in conversation
        foreach ( $usersInConv as $userInConv ) {
            $messageStatus = new MessageStatus();
            $messageStatus->user_id = $userInConv;
            $messageStatus->message_id = $message->id;
            if ( $userInConv == $user_id ) {
                //its the sender user
                $messageStatus->self = 1;
                $messageStatus->status = self::READ;
            } else {
                //other users in conv
                $messageStatus->self = 0;
                $messageStatus->status = self::UNREAD;
            }
            $messageStatus->save();
        }

        return [
            'senderId' => $user_id,
            'convUsersIds' => $usersInConv,
            'content' => $content,
            'convId' => $conv_id
        ];
    }

    public function getConversationByTwoUsers($userA_id, $userB_id, $relationType = null, $relationId = null)
    {
        $results = ConversationUsers::select('conversation_id');
        if (!empty($relationType) && !empty($relationId)) {
            $results->where('parent_type', $relationType)
                ->where('parent_id', $relationId);
        }
        $results->whereIn('user_id', [$userA_id, $userB_id])
            ->groupBy('conversation_id')
            ->havingRaw("COUNT(conversation_id)=2");

        if ($results = $results->first()) {
            return $results->conversation_id;
        }
        return null;
    }

    public function markMessageAs($msgId, $userId, $status) {
        if ($messageStatus = MessageStatus::where('user_id', $userId)->where('message_id', $msgId)->first()) {
            $messageStatus->status = $status;
            $messageStatus->save();
        }
    }

    public function markMessageAsRead($msgId, $userId)
    {
        $this->markMessageAs($msgId, $userId, self::READ);
    }
    public function markMessageAsUnread($msgId, $userId)
    {
        $this->markMessageAs($msgId, $userId, self::UNREAD);
    }
    public function markMessageAsDeleted($msgId, $userId)
    {
        $this->markMessageAs($msgId, $userId, self::DELETED);
    }
    public function markMessageAsArchived($msgId, $userId)
    {
        $this->markMessageAs($msgId, $userId, self::ARCHIVED);
    }

    public function isUserInConversation($convId, $userId) {
        $resp = ConversationUsers::where('user_id', $userId)
            ->where('conversation_id', $convId)
            ->count();
        if($resp) {
            return true;
        }
        return false;
    }

    public function getUsersInConversation($convId)
    {
        $results = ConversationUsers::select('user_id')->where('conversation_id', $convId)->get();
        $usersInConvIds = [];
        if($results) {
            $usersInConvIds = $results->pluck('user_id')->toArray();
        }
        return $usersInConvIds;
    }

    public function getNumOfUnreadMsgs($userId)
    {
        return MessageStatus::where('user_id', $userId)->count();
    }

    public function markReadAllMessagesInConversation($convId, $userId)
    {
        $messageStatuses = MessageStatus::whereIn('message_id', DB::Raw("SELECT `id`
              FROM `{$this->messagesTable}`
              WHERE `conversation_id`='{$convId}'
              AND `sender_id`!='{$userId}'"))
            ->where('status', self::UNREAD)
            ->where('user_id', $userId)
            ->get();

        if($messageStatuses) {
            foreach ($messageStatuses as $messageStatus) {
                $messageStatus->status = self::READ;
                $messageStatus->save();
            }
        }
    }

    /**
     * @param $conv_id
     * @param $user_id
     *
     * mark all messages for specific user in specific conversation as unread
     */
    public function markUnreadAllMessagesInConversation($convId, $userId)
    {
        $messagesT = DB::getTablePrefix() . (new Message())->getTable();
        $messageStatuses = MessageStatus::whereIn('message_id', DB::Raw("SELECT `id`
              FROM `{$messagesT}`
              WHERE `conversation_id`='{$convId}'
              AND `sender_id`!='{$userId}'"))
            ->where('status', self::READ)
            ->where('user_id', $userId)
            ->get();

        if($messageStatuses) {
            foreach ($messageStatuses as $messageStatus) {
                $messageStatus->status = self::UNREAD;
                $messageStatus->save();
            }
        }
    }

    /**
     * @param $convId
     * @param $userId
     */
    public function deleteConversation($convId, $userId)
    {
        $messageStatuses = MessageStatus::whereIn('message_id', DB::Raw("SELECT `msg`.`id`
              FROM `{$this->messagesTable}` `msg`
              WHERE `msg`.`conversation_id`='{$convId}'"))
            ->where('user_id', $userId)
            ->get();

        if($messageStatuses) {
            foreach ($messageStatuses as $messageStatus) {
                $messageStatus->status = self::DELETED;
                $messageStatus->save();
            }
        }
    }

    public function getConversationMessages($convId, $userId, $newToOld = true)
    {
        if ($newToOld) {
            $orderBy = 'desc';
        } else {
            $orderBy = 'asc';
        }
        $messageT = (new Message())->getTable();
        $messageStatusT = (new MessageStatus())->getTable();
        return MessageStatus::select(
            DB::Raw("`{$this->messagesTable}`.`id` as `msgId`"),
            $messageT.'.content',
            $messageStatusT.'.status',
            $messageT.'.created_at',
            DB::Raw("`{$this->messagesTable}`.`sender_id` as `userId`")
        )
            ->join($messageT, $messageT.'.id', $messageStatusT.'.message_id')
            ->where($messageT.'.conversation_id', $convId)
            ->where($messageStatusT.'.user_id', $userId)
            ->whereNotIn($messageStatusT.'.status', [self::DELETED, self::ARCHIVED])
            ->orderBy($messageT.'.created_at', $orderBy)
            ->get();
    }

    public function getConversationUnreadMessages($convId, $userId, $newToOld = true)
    {
        if ($newToOld) {
            $orderBy = 'desc';
        } else {
            $orderBy = 'asc';
        }
        $messageT = (new Message())->getTable();
        $messageStatusT = (new MessageStatus())->getTable();
        return MessageStatus::select(
            DB::Raw("`{$this->messagesTable}`.`id` as `msgId`"),
            $messageT.'.content',
            $messageStatusT.'.status',
            $messageT.'.created_at',
            DB::Raw("`{$this->messagesTable}`.`sender_id` as `userId`")
        )
            ->join($messageT, $messageT.'.id', $messageStatusT.'.message_id')
            ->where($messageT.'.conversation_id', $convId)
            ->where($messageStatusT.'.user_id', $userId)
            ->where($messageStatusT.'.status', self::UNREAD)
            ->orderBy($messageT.'.created_at', $orderBy)
            ->get();
    }

    public function getConversations($userId, $relationType = null, $relationId = null)
    {
        $conversation = Conversation::with(['users'=>function($q) use ($userId) {
        }])->whereRaw(DB::Raw("(SELECT COUNT(`message_id`)
                FROM `{$this->messagesTable}`
                INNER JOIN `{$this->messagesStatusTable}` ON `{$this->messagesTable}`.`id`=`{$this->messagesStatusTable}`.`message_id`
                WHERE `user_id`='{$userId}' AND `{$this->messagesStatusTable}`.`status` NOT IN ('".self::DELETED."', '".self::ARCHIVED."')
            ) > 0"));

        if ($relationType !== null && $relationId !== null) {
            $conversation->where('parent_type', $relationType)->where('parent_id', $relationId);
        }

        $conversation->orderBy('updated_at', 'desc');
        $data = $conversation->get();
        $relations = \Config::get('laravel_chat.related', []);

        foreach ($data as &$datum) {
            if ($datum->parent_type && $datum->parent_id && !empty($relations[$datum->parent_type])) {
                $datum->relation = $relations[$datum->parent_type]::where('id', $datum->parent_id)->first();
            }
        }

        return $data;
    }
}
