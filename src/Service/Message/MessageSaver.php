<?php
declare(strict_types=1);

namespace App\Service\Message;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\Users\UserCreator;
use TelegramBot\Api\Types\Message as TgMessage;

class MessageSaver
{

    private UserRepository $userRepository;
    /**
     * @var UserCreator
     */
    private $userCreator;

    public MessageRepository $messageRepository;

    public function __construct(
        UserRepository $userRepository,
        UserCreator $userCreator,
        MessageRepository $messageRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->userCreator = $userCreator;
        $this->messageRepository = $messageRepository;
    }

    public function save(TgMessage $tgMessage, string $callback, User $recipient = null): ?Message
    {
        $user = $this->userCreator->create(null, $tgMessage);

        $message = new Message();
        $message->setUser($user);
        $message->setRecipient($recipient);
        $message->setChatId($tgMessage->getChat()->getId());
        $message->setMessageId($tgMessage->getMessageId());
        $message->setText($tgMessage->getText());
        $message->setCallback($callback);
        $this->messageRepository->save($message);

        return $message;
    }

    public function getLastMessage(int $chatId, User $recipient): ?Message
    {
        return $this->messageRepository->lastMessageChatRecipient($chatId, $recipient);
    }


}
