<?php

return [

    /*
    |------------------------------------------------------------------------------------------------------------------
    | Send via mail channel
    |------------------------------------------------------------------------------------------------------------------
    |
    | This option allows you to send Postmark Template notifications via the "mail" notification channel which Laravel
    | provides out of the box. That way, you can still use the actual Postmark Template, but it will be handled by
    | the Laravel mailer instead of the Postmark API. This is mostly useful to work with Postmark Email Template
    | notifications in a local (or any non-prod) environment without sending them to the actual recipient.
    |
    */

    'send_via_mail_channel' => env('POSTMARK_NOTIFICATION_CHANNEL_SEND_VIA_MAIL_CHANNEL', false),

];
