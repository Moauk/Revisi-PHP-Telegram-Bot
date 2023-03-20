<?php

/**
 * This file is part of the PHP Telegram Bot example-bot package.
 * https://github.com/php-telegram-bot/example-bot/
 *
 * (c) PHP Telegram Bot Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * User "/upload" command
 *
 * A command that allows users to upload files to your bot, saving them to the bot's "Download" folder.
 *
 * IMPORTANT NOTICE
 * This is a "demo", do NOT use this as-is in your bot!
 * Know the security implications of allowing users to upload arbitrary files to your server!
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class UploadCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'upload';

    /**
     * @var string
     */
    protected $description = 'Upload and save files';

    /**
     * @var string
     */
    protected $usage = '/upload';

    /**
     * @var string
     */
    protected $version = '0.2.0';

    /**
     * @var bool
     */
    protected $need_mysql = true;

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat    = $message->getChat();
        $chat_id = $chat->getId();

        // Initialise the data array for the response
        $data = ['chat_id' => $chat_id];
        $data['text'] = 'Please upload the file now';
        $data['reply_markup'] = Keyboard::forceReply();

        return Request::sendMessage($data);
    }
}
