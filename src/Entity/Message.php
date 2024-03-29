<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: "telegram_message")]
#[ORM\Index(name: "chat_message_idx", columns: ["chat_id", "message_id"])]
#[ORM\Index(name: "chat_recipient_idx", columns: ["chat_id", "recipient"])]
class Message
{

    #[ORM\Id()]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: "bigint", name: "id")]
    private int $id;

    #[ORM\Column(name: "created_at", type: "datetime")]
    private ?DateTimeInterface $createdAt;


    #[ORM\Column(name: "updated_at", type: "datetime")]
    private ?DateTimeInterface $updatedAt;


    #[ORM\Column(type: "bigint", name: "chat_id")]
    private int $chatId;


    #[ORM\Column(type: "bigint", name: "message_id")]
    private int $messageId;


    #[ORM\Column(type: "string", length: 255, name: "callback", nullable: true)]
    private ?string $callback;

    #[ORM\Column(type: "string", length: 255, name: "text", nullable: true)]
    private ?string $text;

    #[ORM\ManyToOne(targetEntity: "User")]
    #[ORM\JoinColumn(name: "user", referencedColumnName: "id", nullable: true)]
    private ?User $user;

    #[ORM\ManyToOne(targetEntity: "User")]
    #[ORM\JoinColumn(name: "recipient", referencedColumnName: "id", nullable: true)]
    private ?User $recipient;


    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $datetime = new \DateTime();

        $this->createdAt = $datetime;
        $this->updatedAt = $datetime;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        try {
            $datetime = new \DateTime();
        } catch (\Exception $e) {
            $datetime = null;
        }

        $this->updatedAt = $datetime;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Message
     */
    public function setId(int $id): Message
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeInterface|null $createdAt
     *
     * @return Message
     */
    public function setCreatedAt(?DateTimeInterface $createdAt): Message
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTimeInterface|null $updatedAt
     *
     * @return Message
     */
    public function setUpdatedAt(?DateTimeInterface $updatedAt): Message
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return int
     */
    public function getChatId(): int
    {
        return $this->chatId;
    }

    /**
     * @param int $chatId
     *
     * @return Message
     */
    public function setChatId(int $chatId): Message
    {
        $this->chatId = $chatId;

        return $this;
    }

    /**
     * @return int
     */
    public function getMessageId(): int
    {
        return $this->messageId;
    }

    /**
     * @param int $messageId
     *
     * @return Message
     */
    public function setMessageId(int $messageId): Message
    {
        $this->messageId = $messageId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCallback(): ?string
    {
        return $this->callback;
    }

    /**
     * @param string|null $callback
     *
     * @return Message
     */
    public function setCallback(?string $callback): Message
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param string|null $text
     *
     * @return Message
     */
    public function setText(?string $text): Message
    {
        $this->text = $text;

        return $this;
    }


    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }


    /**
     * @param User|null $user
     *
     * @return Message
     */
    public function setUser(?User $user): Message
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    /**
     * @param User|null $recipient
     *
     * @return Message
     */
    public function setRecipient(?User $recipient): Message
    {
        $this->recipient = $recipient;

        return $this;
    }

}
