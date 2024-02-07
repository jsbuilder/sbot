<?php

namespace App\Telegram\Command;

use App\Entity\User;
use App\Entity\Message as MessageEntity;
use App\Service\Message\MessageSaver;
use App\Service\Users\UserCreator;
use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use Psr\Log\LoggerInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\Update;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Chat;

/**
 *
 */
class StartCommand extends AbstractCommand implements PublicCommandInterface
{

    private LoggerInterface $logger;

    private UserCreator $creator;

    private MessageSaver $messageSaver;

    private ?User $user;

    private ?Message $replayMessage;

    private ?MessageEntity $lastMessage;

    private string $userText;

    public function __construct(LoggerInterface $logger, UserCreator $creator, MessageSaver $messageSaver)
    {
        $this->logger       = $logger;
        $this->creator      = $creator;
        $this->messageSaver = $messageSaver;
        $this->userText     = '';
    }

    /**
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return StartCommand
     */
    public function setUser(?User $user = null): StartCommand
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Message
     */
    public function getReplayMessage(): ?Message
    {
        return $this->replayMessage;
    }

    /**
     * @param Message $replayMessage
     *
     * @return StartCommand
     */
    public function setReplayMessage(?Message $replayMessage = null): StartCommand
    {
        $this->replayMessage = $replayMessage;

        return $this;
    }

    /**
     * @return MessageEntity|null
     */
    public function getLastMessage(): ?MessageEntity
    {
        return $this->lastMessage;
    }

    /**
     * @param MessageEntity|null $lastMessage
     *
     * @return StartCommand
     */
    public function setLastMessage(?MessageEntity $lastMessage): StartCommand
    {
        $this->lastMessage = $lastMessage;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserText(): string
    {
        return $this->userText;
    }

    /**
     * @param string $userText
     *
     * @return StartCommand
     */
    public function setUserText(string $userText): StartCommand
    {
        $this->userText = $userText;

        return $this;
    }


    public function getName(): string
    {
        return '/start';
    }

    public function getDescription(): string
    {
        return 'Example command';
    }

    public function execute(BotApi $api, Update $update): void
    {
        $index = isset($update) ? $this->getIndex($update) : null;

        $user = $this->getUser();

        $isCallback = false;

        if ($update->getCallbackQuery()) {
            $message    = $update->getCallbackQuery()->getMessage();
            $chat       = $message->getChat();
            $isCallback = true;
        } else {
            $message = $update->getMessage();
            $chat    = $message->getChat();

            if ($message->getContact() && !$user->getPhoneNumber()) {
                $user->setPhoneNumber($message->getContact()->getPhoneNumber());
                $this->setUser($this->creator->update($user));
            }
        }

        $this->showSection($api, $index, $chat, $message, $isCallback);
    }

    public function isApplicable(Update $update): bool
    {
        if (parent::isApplicable($update)) {
            return true;
        }

        return $this->getIndex($update) !== null;
    }

    private function getIndex(Update $update): ?string
    {
        $user = $this->setUser($this->creator->create($update))->getUser();

        if ($update->getMessage()) {
            $replay = $this->setReplayMessage($update->getMessage()->getReplyToMessage())->getReplayMessage();
            if ($replay) {
                $replayId      = $replay->getMessageId();
                $replayChatId  = $replay->getChat()->getId();
                $storedMessage = $this->messageSaver->messageRepository->findByChatMessage($replayChatId, $replayId);

                return $storedMessage->getCallback();
            }

            $lastMessage = $this->setLastMessage(
                $this->messageSaver->getLastMessage(
                    $update->getMessage()->getChat()->getId(),
                    $user
                )
            )->getLastMessage();

            if ($lastMessage) {
                $this->setUserText($update->getMessage()->getText());

                return $lastMessage->getCallback();
            }

            return $update->getMessage()->getText();
        }
        if ($update->getCallbackQuery()) {
            return $update->getCallbackQuery()->getData();
        }

        return null;
    }

    /**
     * @param $needle
     * @param $haystack
     * @param $strict
     *
     * @return bool
     */
    private function inArrayR($needle, $haystack, $strict = false): bool
    {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle)
                || (is_array($item)
                    && $this->inArrayR(
                        $needle,
                        $item,
                        $strict
                    ))) {
                return true;
            }
        }

        return false;
    }


    /**
     * @param BotApi $api
     * @param        $index
     * @param        $chatId
     * @param        $messageId
     *
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    private function showSection(BotApi $api, $index, Chat $chat, Message $message, bool $isCallback): void
    {
        $terminate      = false;
        $messageId      = $message->getMessageId();
        $chatId         = $chat->getId();
        $user           = $this->getUser();
        $userText       = $this->getUserText();
        $requestContact = !$user->getPhoneNumber();
        $regerdsAnswer  = "Спасибо!\n"
            . "Специалист перезвонит в течение 20 минут и уточнит подробности запроса."
            . " Мы работаем без выходных с утра до вечера.";

        $backButton = ['text' => '< Назад', 'callback_data' => '/start'];
        $buttons    = [
            [
                ['text' => 'Подобрать запчасть', 'callback_data' => '/start_part'],
                ['text' => 'Заказы и возврат', 'callback_data' => '/start_order']
            ],
            [
                ['text' => 'Справка', 'callback_data' => '/start_help'],
                ['text' => 'Жалобы/Предложения', 'callback_data' => '/start_suggest']
            ],
        ];

        switch ($index) {
            case '/start':
                $text = 'Здравствуйте! Я бот AUTO3N.';
                break;

            case '/start_part':
                $text    = ($requestContact)
                    ? 'Нам потребуется Ваш номер телефона'
                    : 'Выберите что требуется';
                $buttons = [
                    [
                        [
                            'text'            => 'Подобрать запчасть',
                            'callback_data'   => '/start_part_search',
                            'request_contact' => $requestContact
                        ],
                        [
                            'text'            => 'Узнать цену/наличие',
                            'callback_data'   => '/start_part_price',
                            'request_contact' => $requestContact
                        ]
                    ],
                    [
                        $backButton
                    ]
                ];
                break;

            case '/start_part_search':
                $text = $regerdsAnswer;

                $buttons = [
                    [
                        $backButton
                    ]
                ];
                break;

            case '/start_part_price':
                $text = $regerdsAnswer;

                $buttons = [
                    [
                        $backButton
                    ]
                ];
                break;

            case '/start_order':
                $text    = 'Что нужно сделать?';
                $buttons = [
                    [
                        [
                            'text'            => 'Узнать статус заказа',
                            'callback_data'   => '/start_order_status',
                            'request_contact' => $requestContact
                        ],
                        [
                            'text'          => 'Вернуть товары',
                            'callback_data' => '/start_order_refund'
                        ]
                    ],
                    [
                        $backButton
                    ]
                ];
                break;

            case '/start_order_status':
                $text    = ($userText)
                    ? $regerdsAnswer
                    : 'Скопируйте номер заказа из СМС или письма с подтверждением заказа.'
                    . ' Например: S8888888, M7777777 или 6666666.';
                $buttons = [[$backButton]];
                break;

            case '/start_order_refund':
                $text    = 'Чтобы вернуть товар без следов эксплуатации, привезите его в розничный магазин в течение 14 дней после покупки. Возврат товара с недостатками обсудите по телефону с менеджером магазина. Полный список правил https://auto3n.ru/pravila-vozvrata';
                $buttons = [[$backButton]];
                break;

            case '/start_help':
                $text    = '<a href="https://auto3n.ru/">“Справка” в интернет магазине</a>';
                $buttons = [[$backButton]];
                break;

            case '/start_suggest':
                $text    = 'Что нужно сделать?';
                $buttons = [
                    [
                        ['text'            => 'Жалоба',
                         'callback_data'   => '/start_complaint',
                         'request_contact' => $requestContact
                        ],
                        ['text'            => 'Предложение',
                         'callback_data'   => '/start_offer',
                         'request_contact' => $requestContact
                        ],
                        $backButton
                    ]
                ];
                break;

            case '/start_complaint':
                $text    = ($userText) ? 'Спасибо, я передам ваше предложение специалисту.'
                    : 'Пожалуйста, расскажите, что случилось. Если проблема связана с заказом, скопируйте его номер из СМС или письма с подтверждением заказа. Обязательно напишите номер телефона для связи с вами. Мы перезвоним в течение 1 часа, чтобы узнать подробности.';
                $buttons = [[$backButton]];
                break;

            case '/start_offer':
                $text    = ($userText) ? 'Запрос принят и передан на обработку.'
                    : 'Расскажите, что нам улучшить в работе? Если вы хотите стать нашим партнером, расскажите о себе и оставьте контакты.';
                $buttons = [[$backButton]];
                break;

            default:
                $text = 'Здравствуйте! Я бот AUTO3N.';
        }

        if ($terminate) {
            return;
        }

        if ($this->inArrayR(['request_contact' => true], $buttons, false)) {
            $replyMarkup   = new ReplyKeyboardMarkup($buttons, true, true, true, false);
            $sendedMessage = $api->sendMessage(
                $chatId,
                $text,
                'markdown',
                false,
                null,
                $replyMarkup
            );
        } else {
            $replyMarkup = new InlineKeyboardMarkup($buttons);
            if ($isCallback) {
                $sendedMessage = $api->editMessageText(
                    $chatId,
                    $messageId,
                    $text,
                    'HTML',
                    false,
                    $replyMarkup
                );
            } else {
                $sendedMessage = $api->sendMessage(
                    $chatId,
                    $text,
                    'HTML',
                    false,
                    null,
                    $replyMarkup
                );
            }
        }

        $this->messageSaver->save($sendedMessage, $index, $user);
    }
}
