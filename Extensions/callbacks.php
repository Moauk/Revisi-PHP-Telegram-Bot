<?php
    use Longman\TelegramBot\Entities\ServerResponse;
    use Longman\TelegramBot\Request;
    use Longman\TelegramBot\Commands\SystemCommands\CallbackqueryCommand;
    use TelegramBot\TelegramBotManager\Extensions\DB;

    /**
     * Gestió dels callbacks query
     *
     * @param Message $callback_query
     * @param Telegram $telegram
     * @return ServerResponse
     */
    function callbacks(): void
    {
        $prova['cd1'] = function($callback_query, $telegram): ServerResponse
        {
            DB::insertResposta(
                $callback_query->getFrom()->getId(),
                $callback_query->getFrom()->getId(),
                $telegram->getApiKey(),
                $telegram->getBotUserName(),
                $callback_query->getData()
            );
            return Request::emptyResponse();
        };

        CallbackqueryCommand::addAssocCallbackHandler($prova);
    }
?>