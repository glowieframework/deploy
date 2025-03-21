<?php

namespace Glowie\Plugins\Deploy\Core;

use Glowie\Core\Tools\Crawler;
use Util;

class Notify
{

    /**
     * Sends a notification to Telegram.
     * @param string $botId Bot token ID.
     * @param string $chatId Target chat ID.
     * @param string $message Message to send.
     * @return bool Returns true on success, false otherwise.
     */
    public static function telegram(string $botId, string $chatId, string $message)
    {
        return self::performRequest("https://api.telegram.org/bot$botId/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message
        ], 'GET', false);
    }

    /**
     * Sends a notification to Discord.
     * @param string $webhookUrl Webhook URL.
     * @param string $message Message to send.
     * @return bool Returns true on success, false otherwise.
     */
    public static function discord(string $webhookUrl, string $message)
    {
        return self::performRequest($webhookUrl, [
            'content' => Util::limitString($message, 2000)
        ]);
    }

    /**
     * Sends a request.
     * @param string $url URL to request.
     * @param array $data (Optional) Data to pass.
     * @param string $method (Optional) HTTP method.
     * @return bool Returns true on success, false otherwise.
     */
    private static function performRequest(string $url, array $data = [], string $method = 'POST')
    {
        $request = (new Crawler())->throwOnError()->bypassVerification();
        if ($method === 'POST') $request->asJson();
        $result = $request->request($url, $method, $data);
        return !empty($result);
    }
}
