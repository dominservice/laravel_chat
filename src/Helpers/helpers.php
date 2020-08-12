<?php

if (!function_exists('get_conversations')) {
    /**
     * @param null $userId
     * @return array
     */
    function get_conversations($userId = null, $relationType = null, $relationId = null)
    {
        $users = collect([]);
        $userId = !$userId && \Auth::check() ? \Auth::user()->id : $userId;
        $convs = (new Dominservice\LaravelChat\LaravelChat)->getConversations($userId, $relationType, $relationId);
        foreach ($convs as $conv) {
            if ($conv->users) {
                foreach($conv->users as $user) {
                    if (empty($users[$user->id])) {
                        $users[$user->id] = $user;
                    }
                }
            }
        }
        return ['conversations'=>$convs, 'users'=>$users];
    }
}

if (!function_exists('set_conversation')) {
    /**
     * @param array $users
     * @return array|\Dominservice\LaravelChat\Models\Eloquent\Conversation
     * @throws \Dominservice\LaravelChat\Exceptions\NotEnoughUsersInConvException
     */
    function set_conversation($users = [], $relationType = null, $relationId = null)
    {
        if (!empty($users)) {
            return (new Dominservice\LaravelChat\LaravelChat)->createConversation($users, $relationType, $relationId);
        }
        return [
            'convId' => null
        ];

    }
}

if (!function_exists('delete_conversation')) {
    /**
     * @param $convId
     * @param null $userId
     */
    function delete_conversation($convId, $userId = null)
    {
        if (!empty($convId)) {
            $userId = !$userId && \Auth::check() ? \Auth::user()->id : $userId;
            (new Dominservice\LaravelChat\LaravelChat)->deleteConversation($convId, $userId);
        }
    }
}

if (!function_exists('in_conversation')) {
    /**
     * @param $convId
     * @param null $userId
     * @return bool
     */
    function in_conversation($convId, $userId = null)
    {
        if (!empty($convId)) {
            $userId = !$userId && \Auth::check() ? \Auth::user()->id : $userId;
            return (new Dominservice\LaravelChat\LaravelChat)->isUserInConversation($convId, $userId);
        }
        return false;
    }
}

if (!function_exists('conversation_add_message')) {
    /**
     * @param $convId
     * @param $content
     * @param null $userId
     * @return array|null[]
     */
    function conversation_add_message($convId, $content, $userId = null)
    {
        if (!empty($convId) && !empty($content)) {
            $userId = !$userId && \Auth::check() ? \Auth::user()->id : $userId;
            return (new Dominservice\LaravelChat\LaravelChat)->addMessageToConversation($convId, $userId, $content);
        }
        return [
            'convId' => null
        ];
    }
}

if (!function_exists('conversation_add_message_between')) {
    /**
     * @param $content
     * @param $receiverId
     * @param null $senderId
     * @param null $relationType
     * @param null $relationId
     * @return array
     */
    function conversation_add_message_between($content, $receiverId, $senderId = null, $relationType = null, $relationId = null)
    {
        $senderId = !$senderId && \Auth::check() ? \Auth::user()->id : $senderId;
        return (new Dominservice\LaravelChat\LaravelChat)->sendMessageBetweenTwoUsers($senderId, $receiverId, $content, $relationType, $relationId);
    }
}

if (!function_exists('conversation_unread_count')) {
    /**
     * @param null $userId
     * @return int
     */
    function conversation_unread_count($userId = null)
    {
        $userId = !$userId && \Auth::check() ? \Auth::user()->id : $userId;
        return (new Dominservice\LaravelChat\LaravelChat)->getNumOfUnreadMsgs($userId);
    }
}

if (!function_exists('conversation_between')) {
    /**
     * @param $receiverId
     * @param null $senderId
     * @return int
     * @throws \Dominservice\LaravelChat\Exceptions\ConversationNotFoundException
     */
    function conversation_between($receiverId, $senderId = null)
    {
        $senderId = !$senderId && \Auth::check() ? \Auth::user()->id : $senderId;
        return (new Dominservice\LaravelChat\LaravelChat)->getConversationByTwoUsers($receiverId, $senderId);
    }
}

if (!function_exists('conversation_messages')) {
    /**
     * @param $convId
     * @param null $userId
     * @param bool $newToOld
     * @return mixed
     */
    function conversation_messages($convId, $userId = null, $newToOld = true)
    {
        $userId = !$userId && \Auth::check() ? \Auth::user()->id : $userId;
        return (new Dominservice\LaravelChat\LaravelChat)->getConversationMessages($convId, $userId, $newToOld);
    }
}

// Mark messages as DELETED | UNREAD | READ | ARCHIVED

if (!function_exists('conversation_mark_as_archived')) {
    /**
     * @param $msgId
     * @param null $userId
     */
    function conversation_mark_as_archived($msgId, $userId = null)
    {
        $userId = !$userId && \Auth::check() ? \Auth::user()->id : $userId;
        (new Dominservice\LaravelChat\LaravelChat)->markMessageAsArchived($msgId, $userId);
    }
}

if (!function_exists('conversation_mark_as_deleted')) {
    /**
     * @param $msgId
     * @param null $userId
     */
    function conversation_mark_as_deleted($msgId, $userId = null)
    {
        $userId = !$userId && \Auth::check() ? \Auth::user()->id : $userId;
        (new Dominservice\LaravelChat\LaravelChat)->markMessageAsDeleted($msgId, $userId);
    }
}

if (!function_exists('conversation_mark_as_unread')) {
    /**
     * @param $msgId
     * @param null $userId
     */
    function conversation_mark_as_unread($msgId, $userId = null)
    {
        $userId = !$userId && \Auth::check() ? \Auth::user()->id : $userId;
        (new Dominservice\LaravelChat\LaravelChat)->markMessageAsUnread($msgId, $userId);
    }
}

if (!function_exists('conversation_mark_as_read')) {
    /**
     * @param $msgId
     * @param null $userId
     */
    function conversation_mark_as_read($msgId, $userId = null)
    {
        $userId = !$userId && \Auth::check() ? \Auth::user()->id : $userId;
        (new Dominservice\LaravelChat\LaravelChat)->markMessageAsRead($msgId, $userId);
    }
}

if (!function_exists('conversation_mark_as_read_all')) {
    /**
     * @param $convId
     * @param null $userId
     */
    function conversation_mark_as_read_all($convId, $userId = null)
    {
        if (!empty($convId)) {
            $userId = !$userId && \Auth::check() ? \Auth::user()->id : $userId;
            (new Dominservice\LaravelChat\LaravelChat)->markReadAllMessagesInConversation($convId, $userId);
        }
    }
}

if (!function_exists('conversation_mark_as_unread_all')) {
    /**
     * @param $convId
     * @param null $userId
     */
    function conversation_mark_as_unread_all($convId, $userId = null)
    {
        if (!empty($convId)) {
            $userId = !$userId && \Auth::check() ? \Auth::user()->id : $userId;
            (new Dominservice\LaravelChat\LaravelChat)->markUnreadAllMessagesInConversation($convId, $userId);
        }
    }
}
