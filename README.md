[![Latest Version](https://img.shields.io/github/release/dominservice/laravel_chat.svg?style=flat-square)](https://github.com/dominservice/laravel_chat/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/dominservice/laravel_chat.svg?style=flat-square)](https://packagist.org/packages/dominservice/laravel_chat)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

# Laravel Chat
This package will allow you to add a full user messaging system into your Laravel application.

### Notice
This package is for Laravel >=7.0

This package is an updated copy of [tzookb/tbmsg](https://github.com/tzookb/tbmsg) under laravel 7. *

## Installation
```
composer require dominservice/laravel_chat
```
Or place manually in composer.json:
```
"require": {
    "dominservice/laravel_chat": "^4.3"
}
```
Run:
```
composer update
```
Add the service provider to `config/app.php` 
```php
'providers' => [
    Dominservice\LaravelChat\LaravelChatServiceProvider::class,
],

(...)

'aliases' => [
    'LaravelChat' => Dominservice\LaravelChat\Facade\LaravelChat::class,
]
```
Publish config:

```
php artisan vendor:publish --provider="Dominservice\LaravelChat\LaravelChatServiceProvider"
```
Migrate
```
php artisan migrate
```

# Usage

#### Get User Conversations:

```php
$convs = LaravelChat::getConversations($user_id);
```
This will return you a "Illuminate\Support\Collection" of "Dominservice\LaravelChat\Entities\Conversation" objects.
And foreach Conversation there, you will have the last message of the conversation, and the users of the conversation.
Example:
```php
foreach ( $convs as $conv ) {
    $getNumOfUsers = $conv->getNumOfUsers();
    $users = $conv->users; /* Collection */
            
    /* $lastMessage Dominservice\LaravelChat\Entities\Message */
    $lastMessage = $conv->getLastMessage();
            
    $senderId = $lastMessage->sender;
    $content = $lastMessage->content;
    $status = $lastMessage->status;
}
```

#### Get User specific conversation:

```php
$conv = LaravelChat::getConversationMessages($conv_id, $user_id);
```
This will return you a "Dominservice\LaravelChat\Entities\Conversation" object.
On the object you could get all messages, all users, conv_id, and more, simply browse the object itself.
Example:
```php
foreach ( $conv->messages as $msg ) {
    $senderId = $msg->sender;
    $content = $msg->content;
    $status = $msg->status; /* Collection statuses for all users */
    $statusUser = $msg->statusForUser($userId = null);
}
```
#### Get the conversation id of a conversation between two users:

```php
$conv = LaravelChat::getConversationByTwoUsers($userA_id, $userB_id);
```
Simply gives you an id of the conversation between two users, this was created for redirecting to the conversation page when user tries to send a message to another user, so if there is no id returned that means that those users has no conversation yet, so we could create one.
#### Add a new message to conversation:

```php
$conv = LaravelChat::addMessageToConversation($conv_id, $user_id, $content);
```
Simply add a message to an exiting conversation, content is the message text.
#### Create a new conversation:

```php
$conv = LaravelChat::createConversation($users_ids=array(), $relationType = null, $relationId = null);
```
Creates a new conversation with the users id's you passed in the array.

#### Get all users in conversation:

```php
$conv = LaravelChat::getUsersInConversation($conv_id);
```
returns an array of user id in the conversation.

#### Delete conversation:

```php
$conv = LaravelChat::deleteConversation($conv_id, $user_id);
```
"Deletes" the conversation from a specifc user view.
#### Check if user is in conversation:

```php
$conv = LaravelChat::isUserInConversation($conv_id, $user_id);
```
True or False if user is in conversation.

#### Get number of unread messages for specific user:

```php
$conv = LaravelChat::getNumOfUnreadMsgs($user_id);
```
return an integer of number of unread messages for specific user.

#### Mark all messages as "read" for specifc user in conversation:

```php
$conv = LaravelChat::markReadAllMessagesInConversation($conv_id, $user_id);
```

### Example
```php
public function conversations($convId=null) {
    $currentUser = Auth::user();
    //get the conversations
    $convs = LaravelChat::getConversations( $currentUser->id );
    //array for storing our users data, as that LaravelChat only provides user id's
    $users = [];
    
    //gathering users
    foreach ( $convs as $conv ) {
        $users = array_merge($users, $conv->getAllUsers());
    }
    //making sure each user appears once
    $users = array_unique($users);
    
    //getting all data of users
    if ( !empty($users) ) {
        $users = User::whereIn('id', $users)->with('profileImage')->getDictionary();
    }
            
    return View::make('conversations_page')
        ->with('users', $users)
        ->with('user', $currentUser)
        ->with('convs', $convs);
}
```
## Helpers
Get all conversations for user. If `userId` is `null` then set current user id.
```php
get_conversations($userId = null);
```
Create conversations between selected users, in array must be `id` list.
```php
set_conversation($users = []);
```
Delete conversations for user. If `userId` is `null` then set current user id.
```php
delete_conversation($convId, $userId = null);
```
Check is user in conversations. If `userId` is `null` then set current user id.
```php
in_conversation($convId, $userId = null);
```
Add message to conversations. If `userId` is `null` then set current user id.
```php
conversation_add_message($convId, $content, $userId = null);
```
Add message to conversations between two users, it also create conversation if not exist, or add to exist. If `senderId` is `null` then set current user id.
```php
conversation_add_message_between($content, $receiverId, $senderId = null);
```
Get count unread messages for user. If `userId` is `null` then set current user id.
```php
conversation_unread_count($userId = null);
```
Get conversation between two users. If `senderId` is `null` then set current user id.
```php
conversation_between($receiverId, $senderId = null);
```
Get conversation messages. If `userId` is `null` then set current user id.
```php
conversation_messages($convId, $userId = null, $newToOld = true);
```
Mark messages. If `userId` is `null` then set current user id.
```php
conversation_mark_as_archived($msgId, $userId = null);
conversation_mark_as_deleted($msgId, $userId = null);
conversation_mark_as_unread($msgId, $userId = null);
conversation_mark_as_read($msgId, $userId = null);

conversation_mark_as_read_all($convId, $userId = null);
conversation_mark_as_unread_all($convId, $userId = null);
```


# Credits

#### [tzookb/tbmsg](https://github.com/tzookb/tbmsg)

# changes
If you see any bugs or you want to suggest changes, or you want to create corrections yourself, write to me __biuro@dso.biz.pl__
 
I am willing to cooperate
