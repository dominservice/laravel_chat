<?php

if (!function_exists('get_conversations')) {
    /**
     * @param null $userId
     * @return array
     */
    function get_conversations($userId = null)
    {
        $participants = [];
        $users = [];
        $usersClass = config('laravel_chat.user_model');
        $userId = !$userId && \Auth::check() ? \Auth::user()->id : $userId;
        $convs = (new Dominservice\LaravelChat\LaravelChat)->getUserConversations($userId);
        foreach ($convs as $conv) {
            $participants = array_merge($participants, $conv->getAllParticipants());
        }
        $participants = array_unique($participants);
        if (!empty($participants)) {
            $users = $usersClass()->whereIn($participants)->get();
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
    function set_conversation($users = [])
    {
        if (!empty($users)) {
            return (new Dominservice\LaravelChat\LaravelChat)->createConversation($users);
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
     * @param $convId
     * @param $content
     * @param null $userId
     * @return array|null[]
     */
    function conversation_add_message_between($content, $receiverId, $senderId = null)
    {
        if (!empty($convId) && !empty($content)) {
            $senderId = !$senderId && \Auth::check() ? \Auth::user()->id : $senderId;
            return (new Dominservice\LaravelChat\LaravelChat)->sendMessageBetweenTwoUsers($senderId, $receiverId, $content);
        }
        return [
            'convId' => null
        ];
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
     * @return \Dominservice\LaravelChat\Entities\Conversation
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