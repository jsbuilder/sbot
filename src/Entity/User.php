<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: "telegram_user")]
#[ORM\Index(name: "iser_id_idx", columns: ["telegram_id"])]
class User
{

    #[ORM\Id()]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: "bigint", name: "id")]
    private int $id;

    #[ORM\Column(name: "created_at", type: "datetime")]
    private ?DateTimeInterface $createdAt;


    #[ORM\Column(name: "updated_at", type: "datetime")]
    private ?DateTimeInterface $updatedAt;

    #[ORM\Column(type: "bigint", name: "telegram_id")]
    private int $telegramId;


    #[ORM\Column(type: "boolean", name: "is_bot")]
    private bool $isBot;


    #[ORM\Column(type: "string", name: "first_name", nullable: true)]
    private ?string $firstName;


    #[ORM\Column(type: "string", name: "last_name", nullable: true)]
    private ?string $lastName = null;


    #[ORM\Column(type: "string", name: "username", nullable: true)]
    private ?string $username = null;


    #[ORM\Column(type: "string", length: 10, name: "language_code", nullable: true)]
    private ?string $languageCode = null;


    #[ORM\Column(type: "boolean", name: "can_join_groups", nullable: true)]
    private ?bool $canJoinGroups = null;


    #[ORM\Column(type: "boolean", name: "can_read_all_group_messages", nullable: true)]
    private ?bool $canReadAllGroupMessages = null;


    #[ORM\Column(type: "boolean", name: "supports_inline_queries", nullable: true)]
    private ?bool $supportsInlineQueries = null;


    #[ORM\Column(type: "string", length: 255, name: "phone_number", nullable: true)]
    private ?string $phoneNumber = null;


    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $location = null;

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
     * @return User
     */
    public function setId(int $id): User
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
     * @return User
     */
    public function setCreatedAt(?DateTimeInterface $createdAt): User
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
     * @return User
     */
    public function setUpdatedAt(?DateTimeInterface $updatedAt): User
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return int
     */
    public function getTelegramId(): int
    {
        return $this->telegramId;
    }

    /**
     * @param int $telegramId
     *
     * @return User
     */
    public function setTelegramId(int $telegramId): User
    {
        $this->telegramId = $telegramId;

        return $this;
    }


    /**
     * @return bool
     */
    public function isBot(): bool
    {
        return $this->isBot;
    }

    /**
     * @param bool $isBot
     *
     * @return User
     */
    public function setIsBot(bool $isBot): User
    {
        $this->isBot = $isBot;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName(string $firstName): User
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     *
     * @return User
     */
    public function setLastName(?string $lastName): User
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     *
     * @return User
     */
    public function setUsername(?string $username): User
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    /**
     * @param string|null $languageCode
     *
     * @return User
     */
    public function setLanguageCode(?string $languageCode): User
    {
        $this->languageCode = $languageCode;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getCanJoinGroups(): ?bool
    {
        return $this->canJoinGroups;
    }

    /**
     * @param bool|null $canJoinGroups
     *
     * @return User
     */
    public function setCanJoinGroups(?bool $canJoinGroups): User
    {
        $this->canJoinGroups = $canJoinGroups;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getCanReadAllGroupMessages(): ?bool
    {
        return $this->canReadAllGroupMessages;
    }

    /**
     * @param bool|null $canReadAllGroupMessages
     *
     * @return User
     */
    public function setCanReadAllGroupMessages(?bool $canReadAllGroupMessages): User
    {
        $this->canReadAllGroupMessages = $canReadAllGroupMessages;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getSupportsInlineQueries(): ?bool
    {
        return $this->supportsInlineQueries;
    }

    /**
     * @param bool|null $supportsInlineQueries
     *
     * @return User
     */
    public function setSupportsInlineQueries(?bool $supportsInlineQueries): User
    {
        $this->supportsInlineQueries = $supportsInlineQueries;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string|null $phoneNumber
     *
     * @return User
     */
    public function setPhoneNumber(?string $phoneNumber): User
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * @param string|null $location
     *
     * @return User
     */
    public function setLocation(?string $location): User
    {
        $this->location = $location;

        return $this;
    }

}
