<?php

namespace YouCanTellMeBot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use TDApi\LogConfiguration;
use TDApi\TDLibParameters;
use TDLib\JsonClient;

class TestCommand extends Command
{
    protected static $defaultName = 'you-can-tell-me-bot:test';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // TODO: move to env
        $api_id = '';
        $api_hash = '';
        $phone_number = '';

        try {
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

            $authorizationState = $client->getAuthorizationState();

            if (json_decode($authorizationState, true)['@type'] === 'authorizationStateWaitPhoneNumber') {

                // you must check the state and follow workflow. Lines below is just for an example.
                var_dump($client->setAuthenticationPhoneNumber($phone_number, 30)); // wait response 3 seconds. default - 1.

                $code = $this->getHelper('question')->ask(
                    $input,
                    $output,
                    new Question('Security code:')
                );

                var_dump($client->query(
                    json_encode([
                        '@type'      => 'checkAuthenticationCode',
                        'code'       => $code,
                    ]),
                    10
                ));
            }
            var_dump($client->query(json_encode(['@type' => 'getChats'])));

        } catch (\Exception $exception) {
            echo sprintf('something goes wrong: %s', $exception->getMessage());
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