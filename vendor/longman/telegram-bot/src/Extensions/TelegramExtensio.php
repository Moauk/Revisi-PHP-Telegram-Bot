<?php

/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Extensions;

defined('TB_BASE_PATH') || define('TB_BASE_PATH', __DIR__);
defined('TB_BASE_COMMANDS_PATH') || define('TB_BASE_COMMANDS_PATH', TB_BASE_PATH . '/Commands');

use Exception;
use InvalidArgumentException;
use Longman\TelegramBot\Telegram;

class TelegramExtensio extends Telegram
{

     /**
     * Telegram constructor.
     *
     * @param string $api_key
     * @param string $bot_username
     *
     */
    public function __construct(string $api_key, string $bot_username = '')
    {
        parent::__construct($api_key, $bot_username);
    }

     /**
     * Canvi de api_key i nom del bot
     *
     * @param string $api_key
     * @param string $bot_username
     *
     */
    public function changeCredentials(string $api_key, string $bot_username = '')
    {
        $this->api_key = $api_key;
        $this->bot_username = $bot_username;
    }    
}

?>