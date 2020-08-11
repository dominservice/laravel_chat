[![Latest Version](https://img.shields.io/github/release/dominservice/laravel_chat.svg?style=flat-square)](https://github.com/dominservice/laravel_chat/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/dominservice/laravel_chat.svg?style=flat-square)](https://packagist.org/packages/dominservice/laravel_chat)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

# Laravel Messenger
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
php artisan vendor:publish --provider="CDominservice\LaravelChat\LaravelChatServiceProvider" --tag="config"
```
	
# Usage


# Credits

[tzookb/tbmsg](https://github.com/tzookb/tbmsg)
