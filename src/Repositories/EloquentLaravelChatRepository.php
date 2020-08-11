<?php
namespace Dominservice\LaravelChat\Repositories;

use Illuminate\Database\DatabaseManager;
use Dominservice\LaravelChat\Exceptions\ConversationNotFoundException;
use Dominservice\LaravelChat\Exceptions\NotEnoughUsersInConvException;
use Dominservice\LaravelChat\Exceptions\UserNotInConvException;
use Dominservice\LaravelChat\Models\Eloquent\ConversationUsers;
use Dominservice\LaravelChat\Models\Eloquent\MessageStatus;
use Dominservice\LaravelChat\Repositories\Contracts\iLaravelChatRepository;
use Dominservice\LaravelChat\Models\Eloquent\Message as MessageEloquent;
use Dominservice\LaravelChat\Models\Eloquent\Conversation as ConversationEloquent;

/**
 * Class EloquentLaravelChatRepository
 * @package Dominservice\LaravelChat\Repositories
 */
class EloquentLaravelChatRepository implements iLaravelChatRepository
{
    protected $usersTable;
    protected $usersTableKey;
    /**
     * @var DatabaseManager
     */
    private $db;

    public function __construct($usersTable, $usersTableKey, DatabaseManager $db) {
        $this->usersTable = $usersTable;
        $this->usersTableKey = $usersTableKey;
        $this->db = $db;
    }

    public function createConversation( $users_ids ) {
        if ( count($users_ids ) > 1 ) {
            //create new conv
            $conv = new ConversationEloquent();
            $conv->save();

            //get the id of conv, and add foreach user a line in conversation_users
            foreach ( $users_ids as $user_id ) {
                $this->addConvUserRow($conv->id, $user_id);
            }
            $eventData = [
                'usersIds' => $users_ids,
                'convId' => $conv->id
            ];
            return $eventData;
        } else
            throw new NotEnoughUsersInConvException;
    }

    protected function addConvUserRow($conv_id, $user_id) {
        $this->db->table('conversation_users')->insert(
            array('conversation_id' => $conv_id, 'user_id' => $user_id)
        );
    }

    public function addMessageToConversation($conv_id, $user_id, $content) {
        //check if user of message is in conversation
        if ( !$this->isUserInConversation($conv_id, $user_id) )
            throw new UserNotInConvException;

        //if so add new message
        $message = new MessageEloquent();
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
            'convUsersIds' =>$usersInConv,
            'content' => $content,
            'convId' => $conv_id
        ];
    }

    public function getConversationByTwoUsers($userA_id, $userB_id) {
        $results = $this->db->select(
            '
            SELECT `cu`.`conversation_id`
            FROM `conversation_users` `cu`
            WHERE `cu`.`user_id`=? OR `cu`.`user_id`=?
            GROUP BY `cu`.`conversation_id`
            HAVING COUNT(`cu`.`conversation_id`)=2
            '
            , [$userA_id, $userB_id]);
        if( count($results) == 1 ) {
            return (int)$results[0]->conversation_id;
        }
        throw new ConversationNotFoundException;
    }

    public function markMessageAs($msgId, $userId, $status) {
        $this->db->statement(
            '
            UPDATE `message_statuses`
            SET `status`=?
            WHERE `user_id`=?
            AND `message_id`=?
            ',
            [$status, $userId, $msgId]
        );
    }

    public function markMessageAsRead($msgId, $userId) {
        $this->markMessageAs($msgId, $userId, self::READ);
    }
    public function markMessageAsUnread($msgId, $userId) {
        $this->markMessageAs($msgId, $userId, self::UNREAD);
    }
    public function markMessageAsDeleted($msgId, $userId) {
        $this->markMessageAs($msgId, $userId, self::DELETED);
    }
    public function markMessageAsArchived($msgId, $userId) {
        $this->markMessageAs($msgId, $userId, self::ARCHIVED);
    }

    public function isUserInConversation($conv_id, $user_id) {
        $res = $this->db
            ->table('conversation_users')
            ->select('conversation_id', 'user_id')
            ->where('user_id', $user_id)
            ->where('conversation_id', $conv_id)
            ->first();

        if(is_null($res))
            return false;
        return true;
    }

    public function getUsersInConversation($conv_id) {
        $results = $this->db->select(
            '
            SELECT `cu`.`user_id`
            FROM `conversation_users` `cu`
            WHERE `cu`.`conversation_id`=?
            ',
            [$conv_id]
        );

        $usersInConvIds = [];
        foreach ( $results as $row ) {
            $usersInConvIds[] = $row->user_id;
        }
        return $usersInConvIds;
    }

    public function getNumOfUnreadMsgs($user_id) {
        $results = $this->db->select(
            '
            SELECT COUNT(`mst`.`id`) as `numOfUnread`
            FROM `message_statuses` `mst`
            WHERE `mst`.`user_id`=?
            AND `mst`.`status`=?
            ',
            [$user_id, self::UNREAD]
        );
        return (isset($results[0]))? $results[0]->numOfUnread : 0;
    }

    public function markReadAllMessagesInConversation($conv_id, $user_id)
    {

        $this->db->statement('
            UPDATE `message_statuses`
            SET `status`=?
            WHERE `user_id`=?
            AND `status`=?
            AND `message_id` IN (
              SELECT `id`
              FROM `messages`
              WHERE `conversation_id`=?
              AND `sender_id`!=?
            )',
            [self::READ, $user_id, self::UNREAD, $conv_id, $user_id]
        );


    }

    public function deleteConversation($conv_id, $user_id) {
        $this->db->statement('
            UPDATE `message_statuses` mst
            SET `mst`.`status`='.self::DELETED.'
            WHERE `mst`.`user_id`=?
            AND `mst`.`message_id` IN (
              SELECT `msg`.`id`
              FROM `messages` `msg`
              WHERE `msg`.`conversation_id`=?
            )',
            [$user_id, $conv_id]
        );
    }

    public function getConversationMessages($conv_id, $user_id, $newToOld = true)
    {
        if ( $newToOld )
            $orderBy = 'desc';
        else
            $orderBy = 'asc';

        return $this->db->select('
            SELECT `msg`.`id` as `msgId`, `msg`.`content`, `mst`.`status`, `msg`.`created_at`, `msg`.`sender_id` as `userId`
            FROM `message_statuses` `mst`
            INNER JOIN `messages` `msg`
            ON `mst`.`message_id`=`msg`.`id`
            WHERE `msg`.`conversation_id`=?
            AND `mst`.`user_id` = ?
            AND `mst`.`status` NOT IN (?,?)
            ORDER BY `msg`.`created_at` '.$orderBy.'
            '
            , [$conv_id, $user_id, self::DELETED, self::ARCHIVED]);
    }

    public function getConversations($user_id)
    {
        return $this->db->select("
            SELECT `msg`.`conversation_id` as `conversation_id`, `msg`.`created_at`, `msg`.`id` as `msgId`, `msg`.`content`, `mst`.`status`, `mst`.`self`, `us`.`{$this->usersTableKey}` as `userId`
            FROM `messages` `msg`
            INNER JOIN (
                SELECT MAX(`created_at`) `created_at`
                FROM `messages`
                GROUP BY `conversation_id`
            ) `m2` ON `msg`.`created_at` = `m2`.`created_at`
            INNER JOIN `message_statuses` `mst` ON `msg`.`id`=`mst`.`message_id`
            INNER JOIN `$this->usersTable` `us` ON `msg`.`sender_id`=`us`.`{$this->usersTableKey}`
            WHERE `mst`.`user_id` = ? AND `mst`.`status` NOT IN (?, ?)
            ORDER BY `msg`.`created_at` DESC
            "
            , [$user_id, self::DELETED, self::ARCHIVED]);
    }

    public function getUsersInConvs($convsIds)
    {
        return $this->db->select("SELECT `cu`.`conversation_id`, `us`.`{$this->usersTableKey}`
                FROM `conversation_users` `cu`
                INNER JOIN `{$this->usersTable}` `us`
                ON `cu`.`user_id`=`us`.`{$this->usersTableKey}`
                WHERE `cu`.`conversation_id` IN(`{$convsIds}`)
            "
            , []);
    }
}