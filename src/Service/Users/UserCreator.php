<?php
declare(strict_types=1);

namespace App\Service\Users;

use App\Entity\User;
use App\Repository\UserRepository;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;
use TelegramBot\Api\Types\User as TgUser;

class UserCreator
{

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function create(?Update $update = null, ?Message $message = null): ?User {

        if($update){
        if ($update->getCallbackQuery()) {
            $tgUser = $update->getCallbackQuery()->getFrom();
        } else {
            $tgUser = $update->getMessage()->getFrom();
        }
        } else {
            $tgUser = $message->getFrom();
        }

        $user = $this->userRepository->findByTelegramId($tgUser->getId());
        if ($user instanceof User) {
            return $user;
        }
        $user = $this->getUser($tgUser);
        $user->setUsername((string) $tgUser->getUsername())
            ->setFirstName((string) $tgUser->getFirstName())
            ->setLastName((string) $tgUser->getLastName())
            ->setIsBot($tgUser->isBot())
            ->setLanguageCode($tgUser->getLanguageCode());
        if ($this->userRepository->save($user)) {
            return $user;
        }
        return null;
    }

    private function getUser(TgUser $tgUser): User {
        $user = $this->userRepository->findByTelegramId($tgUser->getId());
        if ($user instanceof User) {
            return $user;
        }
        $user = new User();
        $user->setTelegramId($tgUser->getId());
        return $user;
    }

}
