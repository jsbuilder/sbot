<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Exception;
use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Psr\Log\LoggerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{

    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Message::class);
        $this->logger = $logger;
    }

    public function save(Message $message): ?Message
    {
        try {
            $this->_em->persist($message);
            $this->_em->flush($message);

            return $message;
        } catch (Exception $exception) {
            $this->logger->error(sprintf('Message was not saved: %s', $exception->getMessage()));

            return null;
        }
    }

    public function findByChatMessage(int $chatId, int $messageId): ?Message
    {
        $message = $this->findOneBy(
            [
                'chatId'    => $chatId,
                'messageId' => $messageId
            ],
            [
                'id' => 'desc'
            ]
        );

        return $message instanceof Message ? $message : null;
    }

    public function lastMessageChatRecipient(int $chatId, User $recipient): ?Message
    {
        $message = $this->findOneBy(
            [
                'chatId'    => $chatId,
                'recipient' => $recipient
            ],
            [
                'id' => 'desc'
            ]
        );

        return $message instanceof Message ? $message : null;
    }


}
