<?php declare(strict_types=1);
/**
 * This file is part of the TelegramBotManager package.
 *
 * (c) Mohauk
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TelegramBot\TelegramBotManager\Extensions;

use Longman\TelegramBot\Extensions\TelegramExtensio;
use Longman\TelegramBot\Request;
use TelegramBot\TelegramBotManager\BotManager;
use TelegramBot\TelegramBotManager\Action;
use TelegramBot\TelegramBotManager\Extensions\DB;

class BotManagerExtensio extends BotManager
{
    private string $output = '';
    private TelegramExtensio $telegram;

    public function __construct(array $params)
    {
        parent::__construct($params);
        if (isset($params['stackdb'])) {
            DB::initialize($params['stackdb'], $this->getTelegram());
        }
        $this->telegram = new TelegramExtensio(
            $this->getParams()->getBotParam('api_key'),
            $this->getParams()->getBotParam('bot_username') ?? ''
        );
    }

     /**
     * @throws TelegramException
     * @throws InvalidAccessException
     * @throws InvalidWebhookException
     * @throws Exception
     */
    public function run(): static
    {
        $this->validateSecret();
  //      $this->validateRequest();

        if ($this->getAction()->isAction('stack') && DB::isDbConnected()) {
            $this->handleStackRequest();
            return $this;
        } else {
            return parent::run();
        }
    }

     /**
     * @throws TelegramException
     */
    public function handleStackRequest(): static
    {
        if ($loop_time = $this->getLoopTime()) {
            return $this->handleStackUpdatesLoop($loop_time, $this->getLoopInterval());
        }

        return $this->handleStackUpdates();
    }

    /**
     * @throws TelegramException
     */
    public function handleStackUpdatesLoop(int $loop_time_in_seconds, int $loop_interval_in_seconds = 2): static
    {
        // Remember the time we started this loop.
        $now = time();

        $this->handleStackOutput('Looping Stack Updates until ' . date('d/m/Y H:i:s', $now + $loop_time_in_seconds) . PHP_EOL);

        while ($now > time() - $loop_time_in_seconds) {
            $this->handleStackUpdates();
            // Chill a bit.
            sleep($loop_interval_in_seconds);
        }

        return $this;
    }   
    
    /**
     * @throws TelegramException
     */
    public function handleStackUpdates(): static
    {
        $request = DB::loadResposta();

        $maxid = count($request) > 0 ? end($request)['id'] : 0;
        $this->output = sprintf(
            '%s - Stack Updates processed: %d Max id: %d' . PHP_EOL,
            date('d/m/Y H:i:s'),
            count($request),
            $maxid
        );
        if ($maxid > 0) {
            foreach($request as $item) {
                $this->telegram->changeCredentials($item['api_key'], $item['bot_name']);
                $data = [
                    'chat_id' => $item['user_id'],
                    'text' => $item['comanda']
                ];
                Request::sendMessage($data);
                $this->handleStackOutput('Resposta: '.$item['comanda'].PHP_EOL);
            }
            DB::deleteRespostes($maxid);
        }

        return $this;
    }  
    
    private function handleStackOutput(?string $output = null): static
    {
        if (!is_null($output)) {
            $this->output .= $output;
        }
        if (!self::inTest()) {
            echo $this->output;
        }

        return $this;
    }    
}

?>