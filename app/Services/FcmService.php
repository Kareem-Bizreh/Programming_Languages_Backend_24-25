<?php

namespace App\Services;

use App\Models\Order;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;


class FcmService
{
    protected $messaging;

    public function __construct()
    {
        $firebase = (new Factory)
            ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));

        $this->messaging = $firebase->createMessaging();
    }

    public function sendNotification($deviceToken, $title, $body, array $data = [])
    {
        $notification = Notification::create($title, $body);

        $message = CloudMessage::new()->toToken($deviceToken)
            ->withNotification($notification)
            ->withData($data);

        return $this->messaging->send($message);
    }

    public function notifyUser(Order $order, int $status)
    {
        $user = $order->user;
        if (! $user->fcm_token)
            return;
        if ($status == 2) {
            $title = 'Order delivered';
            $body = 'Order has been placed for delivery.';
        } elseif ($status == 3) {
            $title = 'Order complete';
            $body = 'Order has been completed.';
        }

        $this->sendNotification($user->fcm_token, $title, $body, ['order_id' => $order->id]);
    }
}
