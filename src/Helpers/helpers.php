<?php

if (!function_exists('get_conversations')) {
    /**
     * @param $name
     * @param null $path
     * @param null $secure
     * @return mixed
     */
    function get_conversations($userId = null)
    {
        $usersClass = config('laravel_chat.user_model');
        $userId = !$userId && \Auth::check() ? \Auth::user()->id : $userId;
        //get the conversations
        $convs = (new Dominservice\LaravelChat\LaravelChat)->getUserConversations($userId);
        //array for storing our users data, as that LaravelChat only provides user id's
        $participants = [];

        //gathering participants
        foreach ($convs as $conv) {
            $participants = array_merge($participants, $conv->getAllParticipants());
        }
        //making sure each user appears once
        $participants = array_unique($participants);

        $users = [];
        if (!empty($participants)) {
            $users = $usersClass()->whereIn($participants)->get();
        }

        return ['conversations'=>$convs, 'users'=>$users];
    }
}