<?php
namespace App\MessageHandler;

use App\DTO\NewResponseNotification;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class NewResponseNotificationHandler implements MessageHandlerInterface
{
    public function __invoke(NewResponseNotification $message): void
    {
        // Mercure integration disabled.
    }
}