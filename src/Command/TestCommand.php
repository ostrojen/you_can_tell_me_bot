<?php

namespace YouCanTellMeBot\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use TDApi\LogConfiguration;
use TDApi\TDLibParameters;
use TDLib\JsonClient;

class TestCommand extends Command
{
    private const TIMEOUT = 10;

    protected static $defaultName = 'you-can-tell-me-bot:test';

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $api_id = (int)getenv('API_ID');
        $api_hash = getenv('API_HASH');
        $phone_number = getenv('PHONE_NUMBER');

        LogConfiguration::setLogVerbosityLevel(LogConfiguration::LVL_ERROR);

        $client = new JsonClient();

        $parameters = new TDLibParameters();
        $parameters
            ->setParameter(TDLibParameters::USE_TEST_DC, true)
            ->setParameter(TDLibParameters::DATABASE_DIRECTORY, '/var/tmp/tdlib')
            ->setParameter(TDLibParameters::FILES_DIRECTORY, '/var/tmp/tdlib')
            ->setParameter(TDLibParameters::USE_FILE_DATABASE, false)
            ->setParameter(TDLibParameters::USE_CHAT_INFO_DATABASE, false)
            ->setParameter(TDLibParameters::USE_MESSAGE_DATABASE, false)
            ->setParameter(TDLibParameters::USE_SECRET_CHATS, false)
            ->setParameter(TDLibParameters::API_ID, $api_id)
            ->setParameter(TDLibParameters::API_HASH, $api_hash)
            ->setParameter(TDLibParameters::SYSTEM_LANGUAGE_CODE, 'en')
            ->setParameter(TDLibParameters::DEVICE_MODEL, php_uname('s'))
            ->setParameter(TDLibParameters::SYSTEM_VERSION, php_uname('v'))
            ->setParameter(TDLibParameters::APPLICATION_VERSION, '0.0.10')
            ->setParameter(TDLibParameters::ENABLE_STORAGE_OPTIMIZER, true)
            ->setParameter(TDLibParameters::IGNORE_FILE_NAMES, false);

        $client->setTdlibParameters($parameters);

        $client->setDatabaseEncryptionKey();

        $authorizationState = json_decode($client->getAuthorizationState(), true, 512, JSON_THROW_ON_ERROR);

        if ($authorizationState['@type'] === 'authorizationStateWaitPhoneNumber') {

            $client->setAuthenticationPhoneNumber($phone_number, self::TIMEOUT);

            $code = $this->getHelper('question')->ask(
                $input,
                $output,
                new Question('Authentication code:')
            );

            $client->query(
                json_encode([
                    '@type' => 'checkAuthenticationCode',
                    'code'  => $code
                ], JSON_THROW_ON_ERROR),
                self::TIMEOUT
            );

            $client->query(
                json_encode([
                    '@type'      => 'registerUser',
                    'first_name' => getenv('FIRST_NAME'),
                    'last_name'  => getenv('LAST_NAME'),
                ], JSON_THROW_ON_ERROR),
                self::TIMEOUT
            );
        }

        $chatIds = json_decode(
            $client->query(
                json_encode(['@type' => 'getChats', 'limit' => 9999], JSON_THROW_ON_ERROR),
                self::TIMEOUT
            ),
            true,
            512,
            JSON_THROW_ON_ERROR
        )['chat_ids'];

        foreach ($chatIds as $chatId) {

            $chatHistory = json_decode(
                $client->query(
                    json_encode(
                        [
                            '@type'   => 'getChatHistory',
                            'chat_id' => $chatId,
                            'limit'   => 9999
                        ],
                        JSON_THROW_ON_ERROR
                    ),
                    self::TIMEOUT
                ),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            foreach ($chatHistory['messages'] as $message) {
                var_dump($message);
            }
        }

        // this method must return an integer number with the "exit status code"
        // of the command. You can also use these constants to make code more readable

        // return this if there was no problem running the command
        // (it's equivalent to returning int(0))
        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }
}