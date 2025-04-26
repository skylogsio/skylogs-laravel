<?php

namespace App\Helpers;


use App\interfaces\Messageable;
use Illuminate\Mail\Message;
use Mail;
use Str;

/**
 * Signup form
 */
class Email
{

    public static function sendMessageAlert($users, Messageable $alert): array
    {

        if (!empty($users))
            Mail::raw($alert->emailMessage(), function (Message $message) use ($users) {
                ds($users == ['amirmasoud.asadi@gmail.com']);
                ds(Str::length('amirmasoud.asadi@gmail.com'));
                ds(Str::length($users[0]));
                ds(filter_var($users[0], FILTER_VALIDATE_EMAIL) );
//                dd($users);
                $message->bcc($users)
                    ->subject('Skylogs Alert');
            });

        return [];

    }

}
