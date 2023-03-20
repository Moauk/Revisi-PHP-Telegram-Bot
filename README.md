# Revisió i adaptació del PHP Telegram Bot per sistemes4

### Requisits:

[PHP Telegram Bot](https://github.com/php-telegram-bot) (S'instal·la automàticament el [core](https://github.com/php-telegram-bot/core))

```bash
composer require php-telegram-bot/telegram-bot-manager:^2.0
```
[Monolog](https://github.com/Seldaek/monolog) per fer servir els logs 

```bash
composer require monolog/monolog
```
[PHPDotEnv](https://github.com/vlucas/phpdotenv) per accedir a variables d'entorn .env 

```bash
composer require vlucas/phpdotenv
```

### Instal·lació:

git clone https://github.com/Moauk/Revisi-PHP-Telegram-Bot.git

Crear carpetes /Download, /Upload i /logs

Crear la base de dades i importar el fitxer */vendor/longman/telegram-bot/structure.sql*

Establir les variables d'entorn a *.env*

### Descripció:

- PHP Telegram Bot proporciona una interfície per Telegram Bot Api 6.3 (novembre 2022):
- Pot rebre actualitzacions amb els mètodes **webhook** i **getUpdates**.
- Suporta tots els tipus i mètodes de l'API esmentada.
- Suporta supergrups.
- Gestiona comandes per altres bots dins d'un xat.
- Permet gestionar un canal des de la interfície de l'administrador.
- Suporta **inline bots**.
- Disposa d'**inline keyboards**.
- Els **missatges**, **InlineQuery** i **ChosenInlineQuery** s'emmagatzemen a la base de dades.
- Gestiona conversacions.
- Es disposa de mètodes per gestionar totes les dades (vegeu les entitats *Message* i *User*)

L'anàlisi s'ha centrat a aclarir el funcionament bàsic del bot i en comprovar si disposa de les funcionalitats que es necessiten o, en cas contrari, si es poden afegir (veure **Modificacions**). El mètode d'actualització escollit és **getUpdates** (*long polling*).

Les actualitzacions es gestionen amb el procediment *getUpdatesCLI.php*, que es crida des de consola:

```bash
$ ./getUpdatesCLI.php
```
Però es subministra el procediment *manager.php* amb paràmetres, que permet centralitzar la gestió del bot. Aquest procediment es pot cridar via browser o des de consola i és el que es fa servir per a aquesta feina:


| Paràmetre | Descripció |
| --------- | ----------- |
| s | **s**ecret: Valor secret especial definit a *config.php*. |
| | Requerit si es crida l'script via browser. |
| a | **a**ction: Acció que es vol dur a terme: **handle** (defecte), **webhookinfo**, **cron**, **set**, **unset**, **reset**, **stack** (veure Modificacions) |
| | **handle** executa el mètode **getUpdates**; **webhookinfo** obtè el resultat de **getWebhookInfo**, **cron** executa comandes de cron; **set** / **unset** / **reset** el *webhook*, **stack** llegueix periòdicament la taula *respostes* per enviar a l'usuari|
| l | **l**oop: Nombre de segons que estarà actiu l'script for (pels mètodes *getUpdates* i *stack*). |
| | Recomanat via CLI per actualitzar durant un període determinat. |
| i | **i**nterval: Nombre de segons entre sol·licituds *getUpdates* (pels mètodes *getUpdates* i *stack*, defecte 2). |
| | Recomanat via CLI per actualitzar contínuament cada **i** segons. |
| g | **g**roup: Grup de comandes per **cron**, definides a *config.php* (només per l'acció **cron**, el grup per defecte és **default**). |
| | Defineix quin grup de comandes s'han d'executar via **cron**. Pot ser una llista de grups separats per comes. |

#### via browser

Escriu l'URL cap a *manager.php* amb els paràmetres **GET** necessaris:

- 'http://example.com/manager.php?s=&a=&l=&i='

**getUpdates**

Fer un sol update:

- 'http://example.com/manager.php?s=super_secret&a=handle'
- 'http://example.com/manager.php?s=super_secret' (l'acció **handle** és per defecte)

Fer updates durant 30 segons a intervals de 5 segons:

- 'http://example.com/manager.php?s=super_secret&l=30&i=5'

**cron**

Executar comandes via cron:

- 'http://example.com/manager.php?s=super_secret&a=cron&g=maintenance'
- 'http://example.com/manager.php?s=super_secret&a=cron&g=maintenance,cleanup'

#### via CLI

Si es fa servir CLI la clau **secret** no cal.

Cridar *manager.php* directament amb *php* passant els paràmetres:

- '$ php manager.php a= l= i='

**getUpdates**

Fer una sola actualització:

- '$ php manager.php a=handle'
- '$ php manager.php' (l'acció **handle** és per defecte)

Fer actualitzacions durant 30 segons a intervals de 5 segons:

- '$ php manager.php l=30 i=5'

**cron**

Executar comandes via cron:

- '$ php manager.php a=cron g=maintenance'
- '$ php manager.php a=cron g=maintenance,cleanup'

### Flux bàsic:

*Longman\TelegramBot\Telegram::handleGetUpdates(($data = null, ?int $timeout = null): ServerResponse*

Es recullen els missatges enviats al bot i es processen:

*Longman\TelegramBot\Telegram::processUpdate(Update $update): ServerResponse*

*Longman\TelegramBot\Telegram::executeCommand($command)*

Es mira el tipus de missatge:

genèric: s'envia a *\ServiceMessages\GenericmessageCommand*

comanda: es crida la classe a la que correspon o es torna 'comanda inexistent'

callback query: es crida la classe *Longman\TelegramBot\SystemCommands\CallbackqueryCommand*

### Comandes:

Les comandes per defecte ([x] les que no s'han eliminat) són:

[ ] /cancel - Cancel the currently active conversation

[ ] /date - Show date/time by location

[x] /echo - Show text

[x] /editmessage - Edit a message sent by the bot

[x] /forcereply - Force reply with reply markup

[x] /help - Show bot commands help

[x] /hidekeyboard - Hide the custom keyboard

[x] /image - Randomly fetch any uploaded image

[x] /inlinekeyboard - Show inline keyboard

[x] /keyboard - Show a custom keyboard with reply markup

[x] /markdown - Print Markdown Text

[ ] /payment - Create an invoice for the user using Telegram Payments

[ ] /slap - Slap someone with their username

[ ] /survey - Survey for bot users

[x] /upload - Upload and save files

[ ] /weather - Show weather by location

[x] /whoami - Show your id, name and username

### Comandes Admin:

[x] /chats - List or search all chats stored by the bot

[x] /cleanup - Clean up the database from old records

[x] /debug - Debug command to help find issues

[x] /sendtoall - Send the message to all of the bot's users

[ ] /sendtochannel - Send message to a channel

[x] /whois - Lookup user or group info

Es poden afegir comandes personalitzades de 3 tipus:

**de sistema** *Longman\TelegramBot\Commands\SystemCommand.php*

**d'administrador** *Longman\TelegramBot\Commands\AdminCommand.php*

**d'usuari** *Longman\TelegramBot\Commands\UserCommand.php*


### Configuració:

Les dades que necessita el bot es troben al fitxer config.php:

```php
    // Add you bot's API key and name
    'api_key'      => 'api_key',
    // (string) Bot username that was defined when creating the bot.
    'bot_username'     => 'my_own_bot',

    // (string) A secret password required to authorise access to the webhook.
    'secret'           => 'super_secret',

    // (array) All options that have to do with the webhook.
    'webhook'          => [
        // (string) URL to the manager PHP file used for setting up the webhook.
        'url'             => 'https://example.com/manager.php',
        // (string) Path to a self-signed certificate (if necessary).
        'certificate'     => __DIR__ . '/server.crt',
        // (int) Maximum allowed simultaneous HTTPS connections to the webhook.
        'max_connections' => 20,
        // (array) List the types of updates you want your bot to receive.
        'allowed_updates' => ['message', 'edited_channel_post', 'callback_query'],
        // (string) Secret token to validate webhook requests.
        'secret_token'    => 'super_secret_token',
    ],

    // (bool) Only allow webhook access from valid Telegram API IPs.
    'validate_request' => true,
    // (array) When using `validate_request`, also allow these IPs.
    'valid_ips'        => [
        '1.2.3.4',         // single
        '192.168.1.0/24',  // CIDR
        '10/8',            // CIDR (short)
        '5.6.*',           // wildcard
        '1.1.1.1-2.2.2.2', // range
    ],

    // (array) All options that have to do with the limiter.
    'limiter'          => [
        // (bool) Enable or disable the limiter functionality.
        'enabled' => true,
        // (array) Any extra options to pass to the limiter.
        'options' => [
            // (float) Interval between request handles.
            'interval' => 0.5,
        ],
    ],

    // (array) An array of user ids that have admin access to your bot (must be integers).
    'admins'           => [12345],

    // (array) Mysql credentials to connect a database (necessary for [`getUpdates`](#using-getupdates-method) method!).
    'mysql'            => [
        'host'         => '127.0.0.1',
        'port'         => 3306,           // optional
        'user'         => 'root',
        'password'     => 'root',
        'database'     => 'telegram_bot',
        'table_prefix' => 'tbl_prfx_',    // optional
        'encoding'     => 'utf8mb4',      // optional
    ],

    // (array) Credencials Mysql per connectar a DB de piles (necessari per al mètode 'stack').
     'stackdb'        => [
        'host'     => '127.0.0.1',
        'user'     => 'root',
        'password' => 'root',
        'database' => 'telegram_stack',
    ],     

    // (array) List of configurable paths.
    'paths'            => [
        // (string) Custom download path.
        'download' => __DIR__ . '/Download',
        // (string) Custom upload path.
        'upload'   => __DIR__ . '/Upload',
    ],

    // (array) All options that have to do with commands.
    'commands'         => [
        // (array) A list of custom commands paths.
        'paths'   => [
            __DIR__ . '/CustomCommands',
        ],
        // (array) A list of all custom command configs.
        'configs' => [
            'sendtochannel' => ['your_channel' => '@my_channel'],
            'weather'       => ['owm_api_key' => 'owm_api_key_12345'],
        ],
    ],

    // (array) All options that have to do with cron. Opcional.
    'cron'             => [
        // (array) List of groups that contain the commands to execute.
        'groups' => [
            // Each group has a name and array of commands.
            // When no group is defined, the default group gets executed.
            'default'     => [
                '/default_cron_command',
            ],
            'maintenance' => [
                '/db_cleanup',
                '/db_repair',
                '/message_admins Maintenance completed',
            ],
        ],
    ],

    // Logging (Debug, Error and Raw Updates). Opcional.
    'logging'  => [
        'debug'  => __DIR__ . '/logs/php-telegram-bot-debug.log',
        'error'  => __DIR__ . '/logs/php-telegram-bot-error.log',
        'update' => __DIR__ . '/logs/php-telegram-bot-update.log',
    ],

    // (string) Override the custom input of your bot (mostly for testing purposes!).
    'custom_input'     => '{"some":"raw", "json":"update"}',
]);
```

### Modificacions:

### Execució de comandes des de callbacks querys.

La classe *Longman\TelegramBot\SystemCommands\CallbackqueryCommand* es redefineix a *Commands\Keyboard\CallbackqueryCommand* i només recull el resultat i l'ensenya en una finestra d'avis o un *toast*. Cal que el callback pugui executar una comanda, i per això s'ha eliminat la classe hereua i s'ha ampliat la classe base amb un array associatiu de funcions callback on cada clau correspon a una comanda (*protected static $assoc_callbacks*) que s'executarà si es troba. S'omple invocant la funció *Longman\TelegramBot\SystemCommands\CallbackqueryCommand::addAssocCallbackHandler($callbacks): void*. El fitxer */Extensions/callback.php*, que s'executa a *manager.php*, conté el procediment *callbacks()* on es defineixen les funcions per cada comanda.

### Detecció de replys.

Les respostes són gestionades per la clase *\Commands\ServiceMessages\GenericmessagesCommand*, que no fa res. S'ha modificat per detectar si ens trobem amb un reply (*Longman\TelegramBot\Extensions\ReplyExtension*) i actuar en conseqüència. **L'única manera que he trobat de detectar si una resposta es correspon a una demanda és comprovant el missatge d'origen**.

### Extensió de l'accés a la base de dades.

S'ha creat la clase *Longman\TelegramBot\Extensions\DBExtensió*, que estén *Longman\TelegramBot\DB*, per procediments de tractament de la base de dades. 

### Carregar documents de qualsevol mena com a reply a una demanda del bot.

S'ha modificat la classe *Commands\Other\UploadCommand* (/upload) per enviar el missatge 'Please upload the file now' i forçar un reply de l'usuari, que s'envia a *Longman\TelegramBot\Extensions\ReplyExtension* on es carrega el document (o documents) a la carpeta */Download/photos* o */Download/documents* segons pertoqui.

### Afegit suport per menús des de la base de dades

S'han creat les taules *menus* i *submenus* per emmagatzemar menús. La primera conté un text pel menú (títol) i un enllaç a la segona, on es troben les opcions del menú amb un indicador de nivell per controlar la visualització.

### Simulació de pila de missatges de sortida

S'ha afegit l'acció *stack* (manager.php a=stack) per llegir periòdicament la taula *respostes* en una DB separada. Cada actualització llegeix 20 missatges, els processa (sendMessage) i els elimina. S'ha estès la classe BotManager (TelegramBot\TelegramBotManager\Extensions\BotManagerExtensio). Per funcionar s'ha d'incloure l'array *stackdb* amb un altre DB a *config.php*.

### TODO

- Catalanitzar els textes
- Mirar com funciona el *limiter*













