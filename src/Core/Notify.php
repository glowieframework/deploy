<?php

namespace Glowie\Plugins\Deploy\Core;

use Exception;
use Util;

/**
 * Deploy notifications utility.
 * @category Plugin
 * @package glowieframework/deploy
 * @author Glowie
 * @copyright Copyright (c) Glowie
 * @license MIT
 * @link https://glowie.gabrielsilva.dev.br
 */
class Notify
{

    /**
     * Sends a notification to Telegram.
     * @param string $message Message to send.
     * @return bool Returns true on success, false otherwise.
     */
    public static function telegram(string $message)
    {
        $botId = config('deploy.notifications.telegram.bot_id');
        $chatId = config('deploy.notifications.telegram.chat_id');
        if (empty($botId) || empty($chatId)) throw new Exception('Telegram notifications: "bot_id" and "chat_id" keys are missing in your deploy config');

        return self::performRequest("https://api.telegram.org/bot$botId/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message
        ], 'GET', true);
    }

    /**
     * Sends a notification to Discord.
     * @param string $message Message to send.
     * @return bool Returns true on success, false otherwise.
     */
    public static function discord(string $message)
    {
        $webhookUrl = config('deploy.notifications.discord');
        if (empty($webhookUrl)) throw new Exception('Discord notifications: "discord" key is missing in your deploy config');

        return self::performRequest($webhookUrl, [
            'content' => Util::limitString($message, 2000)
        ]);
    }

    /**
     * Sends a notification to Slack.
     * @param string $message Message to send.
     * @return bool Returns true on success, false otherwise.
     */
    public static function slack(string $message)
    {
        $webhookUrl = config('deploy.notifications.slack');
        if (empty($webhookUrl)) throw new Exception('Slack notifications: "slack" key is missing in your deploy config');

        return self::performRequest($webhookUrl, [
            'text' => $message
        ]);
    }

    /**
     * Sends a push notification with Alertzy.
     * @param string $message Message to send.
     * @return bool Returns true on success, false otherwise.
     */
    public static function alertzy(string $message)
    {
        $key = config('deploy.notifications.alertzy');
        if (empty($key)) throw new Exception('Alertzy notifications: "alertzy" key is missing in your deploy config');

        return self::performRequest('https://alertzy.app/send', [
            'accountKey' => $key,
            'message' => $message
        ], 'POST', true);
    }

    /**
     * Sends a request.
     * @param string $url URL to request.
     * @param array $data (Optional) Data to pass.
     * @param string $method (Optional) HTTP method.
     * @param bool $asForm (Optional) Send request as form data instead of JSON.
     * @return bool Returns true on success, false otherwise.
     */
    private static function performRequest(string $url, array $data = [], string $method = 'POST', bool $asForm = false)
    {
        $request = http()->throwOnError()
            ->bypassVerification();

        if ($asForm) {
            $request->asForm();
        } else {
            $request->asJson();
        }

        $result = $request->request($url, $method, $data);
        return !empty($result);
    }
}
