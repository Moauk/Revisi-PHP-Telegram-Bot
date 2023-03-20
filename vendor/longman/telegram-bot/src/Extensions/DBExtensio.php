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

use Exception;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\DB;
use PDO;

class DBExtensio extends DB
{
    /**
     * Verifica si un userId es el bot
     *
     * @param string/null userId
     * 
     * @return bool
     * @throws TelegramException
     */
    public static function isBot(?string $userId): bool
    {
        if (!self::isDbConnected() || is_null($userId)) {
            return false;
        }

        try {
            $sql = '
              SELECT *
              FROM `' . TB_USER . '`
              WHERE `id` = :user_id
                AND `is_bot` = 1
                AND `username` = :username
            ';

            $sth = self::$pdo->prepare($sql);

            $sth->bindValue(':user_id', self::$telegram->getBotId());
            $sth->bindValue(':username', self::$telegram->getBotUsername());

            $sth->execute();

            $result = $sth->fetchAll(PDO::FETCH_ASSOC);
            return (count($result) > 0) && ($result[0]['id'] == $userId);
        } catch (Exception $e) {
            throw new TelegramException($e->getMessage());
        }
    }   
    
    /**
     * Carrega menu
     *
     * @param string $label
     * 
     * @return bool/array (false si no ha llegit dades)
     * @throws TelegramException
     */
    public static function menu(string $label)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sql = '
              SELECT m.name, s.text, s.nivell, s.callback_data
              FROM `' . TB_MENUS . '` as m
              LEFT JOIN `' . TB_SUBMENUS . '` as s
              ON m.label = s.label
              WHERE m.label = :label
              ORDER BY s.menuItem
            ';

            $sth = self::$pdo->prepare($sql);

            $sth->bindValue(':label', $label);

            $sth->execute();

            $result = $sth->fetchAll(PDO::FETCH_ASSOC);
            if (count($result) > 0) {
                $data['text'] = $result[0]['name'];
                $nivell = -1;
                foreach($result as $item) {
                    if ($item['nivell'] != $nivell) {
                        $nivell = $item['nivell'];
                        $data['menu'][] = [];
                    }
                    $data['menu'][array_key_last($data['menu'])][] = ['text' => $item['text'], 'callback_data' => $item['callback_data']];
                }
            }
            return isset($data) ? $data : false;
        } catch (Exception $e) {
            throw new TelegramException($e->getMessage());
        }
    }  
    
}
