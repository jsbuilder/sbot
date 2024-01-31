<?php

namespace App\Telegram\Command;

use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use Psr\Log\LoggerInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Update;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

/**
 *
 */
class StartCommand extends AbstractCommand implements PublicCommandInterface
{
    // private const REGEX_INDEX = '/(\/start_\w+)/';

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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

        $messageId = null;

        if ($update->getCallbackQuery()) {
            $chat = $update->getCallbackQuery()->getMessage()->getChat();
            $messageId = $update->getCallbackQuery()->getMessage()->getMessageId();
        } else {
            $chat = $update->getMessage()->getChat();
        }



        $this->showSection($api, $index, $chat->getId(), $messageId);
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
            return $update->getMessage()->getText();
        }
        if ($update->getCallbackQuery()) {
            return $update->getCallbackQuery()->getData();
        }
        return null;
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
    private function showSection(BotApi $api, $index, $chatId, $messageId = null): void
    {
        $backButton = ['text' => '< Назад', 'callback_data' => '/start'];
        $buttons = [
            ['text' => 'Подобрать запчасть', 'callback_data' => '/start_part'],
            ['text' => 'Заказы и возврат', 'callback_data' => '/start_order'],
            ['text' => 'Справка', 'callback_data' => '/start_help'],
            ['text' => 'Жалобы/Предложения', 'callback_data' => '/start_suggest'],
        ];

        switch ($index) {
            case '/start':
                $text = 'Здравствуйте! Я бот AUTO3N.';
                break;

            case '/start_part':
                $text = 'Вам перезвонит наш специалист для подбора запчастей. Мы не передаём номер третьим лицам.';
                $buttons = [
                    ['text' => 'Подобрать запчасть', 'callback_data' => '/start_part_search'],
                    ['text' => 'Узнать цену/наличие', 'callback_data' => '/start_part_price'],
                    $backButton
                ];
                break;

            case '/start_order':
                $text = 'Вам перезвонит наш специалист для подбора запчастей. Мы не передаём номер третьим лицам.';
                $buttons = [
                    ['text' => 'Узнать статус заказа', 'callback_data' => '/start_order_status'],
                    ['text' => 'Вернуть товары', 'callback_data' => '/start_order_refund'],
                    $backButton
                ];
                break;

            case '/start_order_status':
                $text = 'Скопируйте номер заказа из СМС или письма с подтверждением заказа. Например: S8888888, M7777777 или 6666666.';
                $buttons = [$backButton];
                break;

            case '/start_order_refund':
                $text = 'Чтобы вернуть товар без следов эксплуатации, привезите его в розничный магазин в течение 14 дней после покупки. Возврат товара с недостатками обсудите по телефону с менеджером магазина. Полный список правил https://auto3n.ru/pravila-vozvrata';
                $buttons = [$backButton];
                break;

            case '/start_suggest':
                $text = 'Что нужно сделать?';
                $buttons = [
                    ['text' => 'Жалоба', 'callback_data' => '/start_complaint'],
                    ['text' => 'Предложение', 'callback_data' => '/start_offer'],
                    $backButton
                ];
                break;

            case '/start_complaint':
                $text = 'Пожалуйста, расскажите, что случилось. Если проблема связана с заказом, скопируйте его номер из СМС или письма с подтверждением заказа. Обязательно напишите номер телефона для связи с вами. Мы перезвоним в течение 1 часа, чтобы узнать подробности.';
                $buttons = [$backButton];
                break;

            case '/start_offer':
                $text = 'Расскажите, что нам улучшить в работе? Если вы хотите стать нашим партнером, расскажите о себе и оставьте контакты.';
                $buttons = [$backButton];
                break;

            default:
                $text = 'Здравствуйте! Я бот AUTO3N.';

        }


        $replyMarkup = new InlineKeyboardMarkup([$buttons]);

        if ($messageId) {
            $api->editMessageText(
                $chatId,
                $messageId,
                $text,
                'markdown',
                false,
                $replyMarkup
            );
        } else {
            $api->sendMessage(
                $chatId,
                $text,
                'markdown',
                false,
                null,
                $replyMarkup
            );
        }
    }
}