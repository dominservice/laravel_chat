[![Latest Version](https://img.shields.io/github/release/dominservice/laravel_chat.svg?style=flat-square)](https://github.com/dominservice/laravel_chat/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/dominservice/laravel_chat.svg?style=flat-square)](https://packagist.org/packages/dominservice/laravel_chat)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

# Laravel Chat
This package will allow you to add a full user messaging system into your Laravel application.

### Notice
This package is for Laravel >=7.0

## Installation
```
composer require dominservice/laravel_chat
```
Or place manually in composer.json:
```
"require": {
    "dominservice/laravel_chat": "^4.0"
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
        $convs = LaravelChat::getUserConversations($user_id);
```
This will return you a "Illuminate\Support\Collection" of "Dominservice\LaravelChat\Entities\Conversation" objects.
And foreach Conversation there, you will have the last message of the conversation, and the participants of the conversation.
Example:
```php
        foreach ( $convs as $conv ) {
        
            $getNumOfParticipants = $conv->getNumOfParticipants();
            $participants = $conv->getAllParticipants();
            
            /* $lastMessage Dominservice\LaravelChat\Entities\Message */
            $lastMessage = $conv->getLastMessage();
            
            $senderId = $lastMessage->getSender();
            $content = $lastMessage->getContent();
            $status = $lastMessage->getStatus();
        }
```

#### Get User specific conversation:

```php
        $conv = LaravelChat::getConversationMessages($conv_id, $user_id);
```
This will return you a "Dominservice\LaravelChat\Entities\Conversation" object.
On the object you could get all messages, all participants, conv_id, and more, simply browse the object itself.
Example:
```php
        foreach ( $conv->getAllMessages() as $msg ) {
            $senderId = $msg->getSender();
            $content = $msg->getContent();
            $status = $msg->getStatus();
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
        $conv = LaravelChat::createConversation($users_ids=array());
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
            $convs = LaravelChat::getUserConversations( $currentUser->id );
            //array for storing our users data, as that LaravelChat only provides user id's
            $participants = [];
    
            //gathering participants
            foreach ( $convs as $conv ) {
                $participants = array_merge($participants, $conv->getAllParticipants());
            }
            //making sure each user appears once
            $participants = array_unique($participants);
    
            //getting all data of participants
            $viewUsers = [];
            if ( !empty($participants) ) {
                $users = User::whereIn('id', $participants)->with('profileImage')->getDictionary();
                
            }
            
            return View::make('conversations_page')
                ->with('users', $users)
                ->with('user', $currentUser)
                ->with('convs', $convs);
        }
```

# Credits

[tzookb/tbmsg](https://github.com/tzookb/tbmsg)
