<?php

/**
 * This file is part of the TelegramBot package.
 *
 * (c) Mohauk
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Extensions;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Exception;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Extensions\DBExtensio;
use PDO;

class ReplyExtensio
{
    /**
     * Gestió dels replys
     *
     * @param Message $message
     * @param string $download_path
     * 
     * @return ServerResponse
     */    
    public static function reply($message, $download_path): ServerResponse
    {
        $reply = $message->getReplyToMessage();

        if (!is_null($reply) && DBExtensio::isBot($reply->getFrom()->getId())) {
            if ($reply->getText() == 'Write something in reply:') {
                return Request::sendMessage(array_merge([
                    'chat_id' => $message->getFrom()->getId(),
                    'text'    => 'Resposta al bot: '.$message->getText(),
                ], []));
            }
            if ($reply->getText() == 'Please upload the file now') {
                $data = self::carregaDoc($message, $download_path);
                return Request::sendMessage(array_merge([
                    'chat_id' => $message->getFrom()->getId(),
                    'text'    => $data['text'],
                ], []));
            }
        }
        return Request::emptyResponse();
    }

    /**
     * Comproba si hi ha un document per carregar i el carrega
     *
     * @param Message $message
     * @param string $download_path
     * 
     * @return ServerResponse
     */    
    private static function carregaDoc($message, $download_path): array
    {
        if (!is_dir($download_path)) {
            $data['text'] = 'Download path has not been defined or does not exist.';
            return $data;
        }

        $message_type = $message->getType();

        if (in_array($message_type, ['audio', 'document', 'photo', 'video', 'voice'], true)) {
            $doc = $message->{'get' . ucfirst($message_type)}();

            // For photos, get the best quality!
            ($message_type === 'photo') && $doc = end($doc);

            $file_id = $doc->getFileId();
            $file    = Request::getFile(['file_id' => $file_id]);
            if ($file->isOk() && Request::downloadFile($file->getResult())) {
                $data['text'] = $message_type . ' file is located at: ' . $download_path . '/' . $file->getResult()->getFilePath();
            } else {
                $data['text'] = 'Failed to download.';
            }
        } else {
            $data['text'] = 'Please upload the file now';
        }
        return $data;
    }
}

?>