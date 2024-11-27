<?php

namespace App\Services;

use Twilio\Rest\Client;

class MessageService
{
    /**
     * send message to some number
     *
     * @param $message
     * @param $recipients
     */
    public function sendMessage($message, $recipients)
    {
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_number = getenv("TWILIO_NUMBER");
        $client = new Client($account_sid, $auth_token);
        $client->messages->create(
            '+963' . substr($recipients, 1),
            ['from' => $twilio_number, 'body' => $message]
        );
    }
}
