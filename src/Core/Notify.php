<?php

namespace Glowie\Plugins\Deploy\Core;

use Config;
use Util;
use Glowie\Core\Exception\PluginException;
use Glowie\Core\Tools\Crawler;

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
     * @param array $options (Optional) Associative array of options to send in the request body.
     * @param string|null $botId (Optional) Custom bot ID to send the notification. Leave empty to use from your deploy config file.
     * @param string|null $chatId (Optional) Custom chat ID to send the notification. Leave empty to use from your deploy config file.
     * @return bool Returns true on success, false otherwise.
     */
    public static function telegram(string $message, array $options = [], ?string $botId = null, ?string $chatId = null)
    {
        if (empty($botId)) $botId = Config::get('deploy.notifications.telegram.bot_id');
        if (empty($chatId)) $chatId = Config::get('deploy.notifications.telegram.chat_id');
        if (empty($botId) || empty($chatId)) throw new PluginException('[Deploy] Telegram notifications: "bot_id" and "chat_id" keys are missing in your deploy config');

        return self::performRequest("https://api.telegram.org/bot$botId/sendMessage", array_merge([
            'chat_id' => $chatId,
            'text' => $message
        ], $options), 'GET', true);
    }

    /**
     * Sends a notification to Discord.
     * @param string $message Message to send.
     * @param array $options (Optional) Associative array of options to send in the request body.
     * @param string|null $webhookUrl (Optional) Custom webhook URL to send the notification. Leave empty to use from your deploy config file.
     * @return bool Returns true on success, false otherwise.
     */
    public static function discord(string $message, array $options = [], ?string $webhookUrl = null)
    {
        if (empty($webhookUrl)) $webhookUrl = Config::get('deploy.notifications.discord');
        if (empty($webhookUrl)) throw new PluginException('[Deploy] Discord notifications: "discord" key is missing in your deploy config');

        return self::performRequest($webhookUrl, array_merge([
            'content' => Util::limitString($message, 2000)
        ], $options));
    }

    /**
     * Sends a notification to Slack.
     * @param string $message Message to send.
     * @param array $options (Optional) Associative array of options to send in the request body.
     * @param string|null $webhookUrl (Optional) Custom webhook URL to send the notification. Leave empty to use from your deploy config file.
     * @return bool Returns true on success, false otherwise.
     */
    public static function slack(string $message, array $options = [], ?string $webhookUrl = null)
    {
        if (empty($webhookUrl)) $webhookUrl = Config::get('deploy.notifications.slack');
        if (empty($webhookUrl)) throw new PluginException('[Deploy] Slack notifications: "slack" key is missing in your deploy config');

        return self::performRequest($webhookUrl, array_merge([
            'text' => $message
        ], $options));
    }

    /**
     * Sends a push notification with Alertzy.
     * @param string $message Message to send.
     * @param array $options (Optional) Associative array of options to send in the request body.
     * @param string|null $accountKey (Optional) Custom account key to send the notification. Leave empty to use from your deploy config file.
     * @return bool Returns true on success, false otherwise.
     */
    public static function push(string $message, array $options = [], ?string $accountKey = null)
    {
        if (empty($accountKey)) $accountKey = Config::get('deploy.notifications.push');
        if (empty($accountKey)) throw new PluginException('[Deploy] Push notifications: "push" key is missing in your deploy config');

        return self::performRequest('https://alertzy.app/send', array_merge([
            'accountKey' => $accountKey,
            'message' => $message
        ], $options), 'POST', true);
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
        $request = (new Crawler())->throwOnError()
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
