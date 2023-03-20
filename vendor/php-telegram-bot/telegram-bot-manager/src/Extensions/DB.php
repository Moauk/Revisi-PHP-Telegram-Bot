<?php

/**
 * This file is part of the TelegramBot package.
 *
 * (c) Mohauk
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Written by Marco Boretto <marco.bore@gmail.com>
 */

namespace TelegramBot\TelegramBotManager\Extensions;

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Exception\TelegramException;
use PDO;
use PDOException;

class DB
{
    /**
     * MySQL credentials
     *
     * @var array
     */
    protected static $mysql_credentials = [];

    /**
     * PDO object
     *
     * @var PDO
     */
    protected static $pdo;

    /**
     * Table prefix
     *
     * @var string
     */
    protected static $table_prefix;

    /**
     * Telegram class object
     *
     * @var Telegram
     */
    protected static $telegram;

    /**
     * Initialize
     *
     * @param array    $credentials  Database connection details
     * @param Telegram $telegram     Telegram object to connect with this object
     * @param string   $table_prefix Table prefix
     * @param string   $encoding     Database character encoding
     *
     * @return PDO PDO database object
     * @throws TelegramException
     */
    public static function initialize(
        array $credentials,
        Telegram $telegram,
        $table_prefix = '',
        $encoding = 'utf8mb4'
    ): PDO {
        if (empty($credentials)) {
            throw new TelegramException('MySQL Stack credentials not provided!');
        }
        if (isset($credentials['unix_socket'])) {
            $dsn = 'mysql:unix_socket=' . $credentials['unix_socket'];
        } else {
            $dsn = 'mysql:host=' . $credentials['host'];
        }
        $dsn .= ';dbname=' . $credentials['database'];

        if (!empty($credentials['port'])) {
            $dsn .= ';port=' . $credentials['port'];
        }

        $options = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $encoding];
        try {
            $pdo = new PDO($dsn, $credentials['user'], $credentials['password'], $options);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }

        self::$pdo               = $pdo;
        self::$telegram          = $telegram;
        self::$mysql_credentials = $credentials;
        self::$table_prefix      = $table_prefix;

        self::defineTables();

        return self::$pdo;
    }

    /**
     * Define all the tables with the proper prefix
     */
    protected static function defineTables(): void
    {
        $tables = [
            'respostes'
        ];
        foreach ($tables as $table) {
            $table_name = 'STB_' . strtoupper($table);
            if (!defined($table_name)) {
                define($table_name, self::$table_prefix . $table);
            }
        }
    }

     /**
     * Check if database connection has been created
     *
     * @return bool
     */
    public static function isDbConnected(): bool
    {
        return self::$pdo !== null;
    }

    /**
     * Carrega files de la taula request
     *
     * @return array
     * @throws TelegramException
     */
    public static function loadResposta(): array
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sql = '
              SELECT *
              FROM `' . STB_RESPOSTES . '`
              LIMIT 20
            ';

            $sth = self::$pdo->prepare($sql);

            $sth->execute();

            $result = $sth->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $e) {
            throw new TelegramException($e->getMessage());
        }
    }     

    /**
     * Inserta dades a la taula request
     *
     * @param int       $chat_id
     * @param int       $user_id
     * @param string    $api_key
     * @param string    $bot_name
     * @param string    $comanda
     *
     * @return bool If the insert was successful
     * @throws TelegramException
     */
    public static function insertResposta(int $chat_id, int $user_id, string $api_key, string $bot_name, string $comanda): bool
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('
                INSERT INTO `' . STB_RESPOSTES . '`
                (`chat_id`, `user_id`, `api_key`, `bot_name`, `comanda`, `created_at`, `updated_at`)
                VALUES
                (:chat_id, :user_id, :api_key, :bot_name, :comanda, :created_at, :updated_at)
            ');

            $sth->bindValue(':chat_id', $chat_id);
            $sth->bindValue(':user_id', $user_id);
            $sth->bindValue(':api_key', $api_key);
            $sth->bindValue(':bot_name', $bot_name);
            $sth->bindValue(':comanda', $comanda);
            $date = self::getTimestamp();
            $sth->bindValue(':created_at', $date);
            $sth->bindValue(':updated_at', $date);

            $status = $sth->execute();
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }
        return $status;
    }    

    /**
     * Elimina files de la taula request
     *
     * @param int $index    darrer id per esborrar
     * 
     * @return int
     * @throws TelegramException
     */
    public static function deleteRespostes(int $index): int
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sql = '
              DELETE
              FROM `' . STB_RESPOSTES . '`
              WHERE `id` <= :index
            ';

            $sth = self::$pdo->prepare($sql);

            $sth->bindValue(':index', $index);

            return $sth->execute();
        } catch (Exception $e) {
            throw new TelegramException($e->getMessage());
        }
    }    

    /**
     * Convert from unix timestamp to timestamp
     *
     * @param ?int $unixtime Unix timestamp (if empty, current timestamp is used)
     *
     * @return string
     */
    protected static function getTimestamp(?int $unixtime = null): string
    {
        return date('Y-m-d H:i:s', $unixtime ?? time());
    }    
}

?>