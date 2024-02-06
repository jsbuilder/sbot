<?php

namespace App\Telegram\Command;

use App\Entity\User;
use App\Service\Message\MessageSaver;
use App\Service\Users\UserCreator;
use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use Psr\Log\LoggerInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardRemove;
use TelegramBot\Api\Types\Update;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Chat;
use TelegramBot\Api\Types\User as TgUser;

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

    public function __construct(LoggerInterface $logger, UserCreator $creator, MessageSaver $messageSaver)
    {
        $this->logger       = $logger;
        $this->creator      = $creator;
        $this->messageSaver = $messageSaver;
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

        $user = $this->setUser($this->creator->create($update))->getUser();

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
        if ($update->getMessage()) {
            $replay = $this->setReplayMessage($update->getMessage()->getReplyToMessage())->getReplayMessage();
            if ($replay) {
                $replayId      = $replay->getMessageId();
                $replayChatId  = $replay->getChat()->getId();
                $storedMessage = $this->messageSaver->messageRepository->findByChatMessage($replayChatId, $replayId);

                return $storedMessage->getCallback();
            }

            return $update->getMessage()->getText();
        }
        if ($update->getCallbackQuery()) {
            return $update->getCallbackQuery()->getData();
        }

        return null;
    }

    private function inArrayR($needle, $haystack, $strict = false)
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
        $messageId      = $message->getMessageId();
        $chatId         = $chat->getId();
        $user           = $this->getUser();
        $requestContact = !$user->getPhoneNumber();
        $backButton     = ['text' => '< Назад', 'callback_data' => '/start'];
        $buttons        = [
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
                $text = 'start_part_search';

                $buttons = [
                    [
                        $backButton
                    ]
                ];

                $this->logger->info('/start_part_search');
                break;

            case '/start_part_price':
                $text    = 'start_part_price';
                $buttons = [
                    [
                        $backButton
                    ]
                ];
                break;

            case '/start_order':
                $text    = 'Вам перезвонит наш специалист для подбора запчастей. Мы не передаём номер третьим лицам.';
                $buttons = [
                    [
                        ['text' => 'Узнать статус заказа', 'callback_data' => '/start_order_status'],
                        ['text' => 'Вернуть товары', 'callback_data' => '/start_order_refund']
                    ],
                    [
                        $backButton
                    ]
                ];
                break;

            case '/start_order_status':
                $text    = 'Скопируйте номер заказа из СМС или письма с подтверждением заказа. Например: S8888888, M7777777 или 6666666.';
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
                        ['text' => 'Жалоба', 'callback_data' => '/start_complaint'],
                        ['text' => 'Предложение', 'callback_data' => '/start_offer'],
                        $backButton
                    ]
                ];
                break;

            case '/start_complaint':
                $text    = 'Пожалуйста, расскажите, что случилось. Если проблема связана с заказом, скопируйте его номер из СМС или письма с подтверждением заказа. Обязательно напишите номер телефона для связи с вами. Мы перезвоним в течение 1 часа, чтобы узнать подробности.';
                $buttons = [[$backButton]];
                break;

            case '/start_offer':
                $text    = 'Расскажите, что нам улучшить в работе? Если вы хотите стать нашим партнером, расскажите о себе и оставьте контакты.';
                $buttons = [[$backButton]];
                break;

            default:
                $text = 'Здравствуйте! Я бот AUTO3N.';
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

        $this->messageSaver->save($sendedMessage, $index);
    }
}
