[![Laravel Postmark notifiation channel](art/banner.jpg)](https://craftzing.com)

![Quality assurance](https://github.com/craftzing/laravel-postmark-notification-channel/workflows/Quality%20assurance/badge.svg)
![Code style](https://github.com/craftzing/laravel-postmark-notification-channel/workflows/Code%20style/badge.svg)
[![Test Coverage](https://api.codeclimate.com/v1/badges/b995846d49f313f8b233/test_coverage)](https://codeclimate.com/github/craftzing/laravel-postmark-notification-channel/test_coverage)
[![Maintainability](https://api.codeclimate.com/v1/badges/b995846d49f313f8b233/maintainability)](https://codeclimate.com/github/craftzing/laravel-postmark-notification-channel/maintainability)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat&color=1E114A)](https://github.com/craftzing/laravel-postmark-notification-channel/blob/master/LICENSE)

This package enables you to send notifications using [Postmark Email Templates](https://postmarkapp.com/email-templates).


## 📚 Contents

- [Requirements](#-requirements)
- [Installation](#-installation)
  - [Setting up a default mail sender](#setting-up-a-default-mail-sender)
  - [Setting up Postmark](#setting-up-postmark)
- [Usage](#-usage)
  - [Working with Template variables](#working-with-template-variables)
  - [Available TemplateMessage methods](#available-templatemessage-methods)
  - [Using Template notifications locally](#using-template-notifications-locally)
- [Testing your Template notifications E2E](#-testing-your-template-notifications-e2e)
- [Changelog](#-changelog)
- [How to contribute](#-how-to-contribute)
- [Thanks to...](#-thanks-to)
- [License](#-license)


## ⚒️ Requirements

This package requires:
- [PHP](https://www.php.net/supported-versions.php) 7.4 or 8
- [Laravel](https://laravel.com) 7, 8 or 9
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
```php
// config/mail.php
'from' => [
    'address' => env('MAIL_FROM_ADDRESS', 'hello@your.domain'),
    'name' => env('MAIL_FROM_NAME', 'Your application name'),
],
```

### Setting up Postmark

Add your Postmark Server API Token to the `services.php` config using the according environment variable:
```php
// config/services.php
'postmark' => [
    'token' => env('POSTMARK_TOKEN'),
],
```


## 🏄 Usage

To use this channel for a notification, you should use it in the `via()` method of the notification and add a 
`toPostmarkTemplate()` which returns `TemplateMessage`:
```php
use Craftzing\Laravel\NotificationChannels\Postmark\TemplatesChannel;
use Craftzing\Laravel\NotificationChannels\Postmark\TemplateMessage;
use Illuminate\Notifications\Notification;

final class WelcomeToCraftzing extends Notification 
{
    public function via(): array
    {
        return [TemplatesChannel::class];
    }
    
    public function toPostmarkTemplate(): TemplateMessage
    {
        return TemplateMessage::fromAlias('welcome-to-craftzing');
    }
}
```

In order to know which email address the notification should be sent to, the channel will look for a `mail`notification
route on the Notifiable model via a `routeNotificationFor($channel = 'mail')` method. This behaviour is provided out of
the box by Laravel if you use the `Illuminate\Notifications\Notifiable` or 
`Illuminate\Notifications\RoutesNotifications` traits.

### Working with Template variables

The easiest way to initialize a `TemplateMessage` is to use the template identifier (either an ID or alias). You can do
so using either of the following named constructors:
```php
use Craftzing\Laravel\NotificationChannels\Postmark\TemplateMessage;

TemplateMessage::fromId(943984); 
TemplateMessage::fromAlias('welcome-to-craftzing'); 
```

However, most of the time your template will require a number of variables (e.g. the recipient's name, an MFA code or 
verification link, ...). You can provide such variables by adding a `TemplateModel` to the message:
```php
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\DynamicTemplateModel;
use Craftzing\Laravel\NotificationChannels\Postmark\TemplateMessage;

$model = DynamicTemplateModel::fromVariables([
    'firstName' => 'Jane',
    'lastName' => 'Doe',
    'mfa' => [
        'code' => 846378,
        'verificationLink' => 'https://some.verification.link?token=secret'
    ],
]);

TemplateMessage::fromAlias('welcome-to-craftzing')
    ->model($model);
```

`DynamicTemplateModel` is an implementation of the `TemplateModel` interface we provide out of the box. You can, 
however, also define your own. The `TemplateMessage` accepts any implementation of the interface.

### Available TemplateMessage methods

Beside the options above, `TemplateMessage` accepts a few other parameters as well. The list below gives you an overview
of all available methods.

> 💡 `TemplateMessage` is an immutable value object. This means that each time you call a method that modifies the 
> message, it returns a new instance of it. The original instance will always remain unchanged. 

- `fromAlias(string): TemplateMessage`: A named constructor to initialize a `TemplateMessage` from a template alias.
- `fromId(int): TemplateMessage`: A named constructor to initialize a `TemplateMessage` from a numeric template ID.
- `model(TemplateModel): TemplateMessage`: Returns a new message instance with the provided TemplateModel.
- `from(Sender): TemplateMessage`: Returns a new message instance with the provided `Sender`. When a sender is set 
  explicitly on the message, the channel will not use the default sender defined in the mail configuration.
- `to(Recipients): TemplateMessage`: Returns a new message instance with the provided `Recipients`. When recipients are 
  set explicitly on the message, the channel will not use the provided notifiable.
- `bcc(Recipients): TemplateMessage`: Returns a new message instance with the provided `Recipients` as bcc.
- `headers(array): TemplateMessage`: Returns a new message instance with the provided `headers`.
- `attachments(...PostmarkAttachment): TemplateMessage`: Returns a new message instance with the provided `attachments`.
- `trackOpens(): TemplateMessage`: Returns a new message instance with the opens tracking option enabled.
- `dontTrackOpens(): TemplateMessage`: Returns a new message instance with the opens tracking option disabled.
- `trackLinks(TrackLinks): TemplateMessage`: Returns a new message instance with the provided link tracking strategy. 
  This method expects an instance of our `Craftzing\Laravel\NotificationChannels\Postmark\Enums\TrackLinks` enum:
  ```php
  use Craftzing\Laravel\NotificationChannels\Postmark\TemplateMessage;
  use Craftzing\Laravel\NotificationChannels\Postmark\Enums\TrackLinks;
  
  TemplateMessage::fromAlias('welcome-to-craftzing')
      ->trackLinks(TrackLinks::HTML_ONLY());
  ```
- `trackEverything(): TemplateMessage`: Returns a new message instance with opens tracking enabled and link tracking
  set to `HTML_AND_TEXT`.
- `tag(string): TemplateMessage`: Returns a new message instance with the provided tag.
- `metadata(array): TemplateMessage`: Returns a new message instance with the provided metadata.
- `messageStreams(...string): TemplateMessage`: Returns a new message instance with the provided message streams.

### Using Template notifications locally

When running your application in a local environment (or any non-production environment for that matter), you may not
want Postmark Template notification to be actually sent out (the same way you may not want email notification to be 
sent). Laravel provides several ways to 
["disable" the actual sending of emails](https://laravel.com/docs/9.x/mail#mail-and-local-development) during local 
development.

This notification channel allows you to send Postmark Template notifications via the `mail` notification which Laravel 
provides out of the box. That way, you can still use the actual Postmark Template locally, but it will be handled by the 
Laravel mailer instead of the Postmark API.

Let's consider the following scenario: you've configured your local environment to handle emails with 
[MailHog](https://github.com/mailhog/MailHog). If you want your Postmark Template notifications to be handled by 
MailHog as well, you can either enable the following option in the `postmark-notification-channel.php` config:
```php
// config/postmark-notification-channel.php
'send_via_mail_channel' => true,
```

or set the according environment variable if you didn't publish the package config:
```dotenv
POSTMARK_NOTIFICATION_CHANNEL_SEND_VIA_MAIL_CHANNEL=true
```

Under the hood, when sending a Postmark Template notification, the channel will first fetch the template from Postmark,
render it with the provided variables and then pass along the rendered HTML to the Laravel mailer.

> 💡 Because were actually calling the Postmark API to fetch the Email Template, you will need the Postmark API token 
> to be set for this configuration to work.


## 🧪 Testing your template notifications E2E

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
