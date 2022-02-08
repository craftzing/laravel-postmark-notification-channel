[![Laravel Postmark notifiation channel](art/banner.jpg)](https://craftzing.com)

![Quality assurance](https://github.com/craftzing/laravel-postmark-notification-channel/workflows/Quality%20assurance/badge.svg)
![Code style](https://github.com/craftzing/laravel-postmark-notification-channel/workflows/Code%20style/badge.svg)
[![Test Coverage](https://api.codeclimate.com/v1/badges/b995846d49f313f8b233/test_coverage)](https://codeclimate.com/github/craftzing/laravel-postmark-notification-channel/test_coverage)
[![Maintainability](https://api.codeclimate.com/v1/badges/b995846d49f313f8b233/maintainability)](https://codeclimate.com/github/craftzing/laravel-postmark-notification-channel/maintainability)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat&color=4D6CB8)](https://github.com/craftzing/laravel-postmark-notification-channel/blob/master/LICENSE)

This package enables you to send notifications using [Postmark Email Templates](https://postmarkapp.com/email-templates).


## 📚 Contents

- [Requirements](#-requirements)
- [Installation](#-installation)
  - [Setting up a default mail sender](#setting-up-a-default-mail-sender)
  - [Setting up Postmark](#setting-up-postmark)
- [Usage](#-usage)
  - [Customizing the Template message](#customizing-the-template-message)
  - [Sending Postmark Template notifications via the mail channel](#sending-postmark-template-notifications-via-the-mail-channel)
- [Changelog](#-changelog)
- [How to contribute](#-how-to-contribute)
- [Thanks to...](#-thanks-to)
- [License](#-license)


## ⚒️ Requirements

This package requires:
- [PHP](https://www.php.net/supported-versions.php) 7.4 or 8
- [Laravel](https://laravel.com) 7 or 8
- A [Postmark Server API Token](https://postmarkapp.com/support/article/1008-what-are-the-account-and-server-api-tokens)


## 🧙 Installation

You can install this package using [Composer](https://getcomposer.org) by running the following command:
```bash
composer require craftzing/laravel-postmark-notification-channel
```

We're using [Laravel's package discovery](https://laravel.com/docs/8.x/packages#package-discovery) to automatically
register the service provider, so you don't have to register it yourself.

### Setting up a default mail sender

Fill out the following in your `mail.php` config file or the according environment variables:
```
// config/mail.php
'from' => [
    'address' => env('MAIL_FROM_ADDRESS', 'hello@your.domain'),
    'name' => env('MAIL_FROM_NAME', 'Your application name'),
],
```

### Setting up Postmark

Add your Postmark Server API Token to the `services.php` config using the according environment variable:
```
// config/services.php
'postmark' => [
    'token' => env('POSTMARK_TOKEN'),
],
```


## 🏄 Usage

To use this channel for a notification, you should use it in the `via()` method of the notification and add a 
`toPostmarkTemplate()` which returns `TemplateMessage`:
```php
use \Craftzing\Laravel\NotificationChannels\Postmark\Resources\DynamicTemplateModel;
use \Craftzing\Laravel\NotificationChannels\Postmark\Resources\TemplateAlias;
use Craftzing\Laravel\NotificationChannels\Postmark\TemplatesChannel;
use \Craftzing\Laravel\NotificationChannels\Postmark\TemplateMessage;
use Illuminate\Notifications\Notification;

final class WelcomeToCraftzing extends Notification 
{
    public function via(): array
    {
        return [TemplatesChannel::class];
    }
    
    public function toPostmarkTemplate(): TemplateMessage
    {
        return (new TemplateMessage(
            TemplateAlias::fromAlias('welcome-to-craftzing'),
            DynamicTemplateModel::fromAttributes([
                'yourTemplateVariable' => 'some value',
            ]),
        ));
    }
}
```

In order to know which email address the notification should be sent to, the channel will look for an `mail` 
notification route on the Notifiable model via a `routeNotificationFor($channel = 'mail')` method. This behaviour is
provided out of the box by Laravel if you use the `Illuminate\Notifications\Notifiable` or 
`Illuminate\Notifications\RoutesNotifications` traits.

### Customizing the Template message

Coming soon...

### Sending Postmark Template notifications via the mail channel

Coming soon...


## 📝 Changelog

Check out our [Change log](/CHANGELOG.md).


## 🤝 How to contribute

Have an idea for a feature? Wanna improve the docs? Found a bug? Check out our [Contributing guide](/CONTRIBUTING.md).


## 💙 Thanks to...

- [The entire Craftzing team](https://craftzing.com)
- [All current and future contributors](https://github.com/creaftzing/laravel-postmark-notification-channel/graphs/contributors)


## 🔑 License

The MIT License (MIT). Please see [License File](/LICENSE) for more information.
