<?php

return [

    /*
    |----------------------------------------------------------------------------------------------------------------
    | Channel
    |----------------------------------------------------------------------------------------------------------------
    |
    | Here's where you specify the channel to use when the "postmark" notification channel is invoked. The default
    | channel (TemplatesChannel) makes use of Postmark Templates (see https://postmarkapp.com/email-templates).
    | You can, however, change this to whichever notification channel you want. This allows you to use another
    | channel for environments in which you don't want notifications to be really sent via the Postmark API.
    |
    | Important: This only applies to the usage of the "postmark" channel. If you reference a channel class
    | directly (such as TemplatesChannel), that implementation will be used instead of the one defined here.
    |
    */

    'channel' => env(
        'POSTMARK_NOTIFICATION_CHANNEL',
        Craftzing\Laravel\NotificationChannels\Postmark\TemplatesChannel::class,
    ),

];
