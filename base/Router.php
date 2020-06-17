<?php
namespace app\base;

use \app\models\CommandsModel;
use \app\controllers\CommandsController as Commands;
use \app\controllers\CommandsExternalController as CommandsExternal;
use \app\components\SessionsComponent as Session;
use \app\config\Config;
use \app\controllers\UsersController;

/**
 *
 */
class Router
{

    public function run($object)
    {
        $block = (new UsersController($object))->checkBlockUser();
        if ($block == true) {
          return;
        }

        $command = false;
        $commands_model = new CommandsModel();

        $list = $commands_model->getAll();
        foreach ($list as $one) {
            $list_command[] = $one->code;
            $list_command[] = $one->title;
        }

        $array = explode('?', $object->message);
        $command = $array[0];

        if ($command == '/') {
            $command = '/start';
        }

        if (isset($array[1])) {
            parse_str($array[1], $params);
            $object->params = $params;
        }

        $result = array_search($command, $list_command);

        if ($result !== false) {

            $key = $list_command[$result];
            $command = $commands_model->searchByTitleAndCode($key);
            $object->command = $command;

            // Проверка если есть ссесия, то отчистить
            $sessionComponent = new Session();
            $sessionComponent->user_id = $object->user->id;
            $session = $sessionComponent->get();
            if ($session !== false) {
                $sessionComponent->id = $session->id;
                $sessionComponent->delete();
            }

            $commands = new Commands($object);
            return $commands->run();

        } else {

            $sessionComponent = new Session();
            $sessionComponent->user_id = $object->user->id;
            $session = $sessionComponent->get();

            if ($session != null) {
                $object->command = $session->content['command'];
                $object->params = $session->content['params'];
                $commands = new Commands($object);
                return $commands->run();
            } else {
                $object->reply = 'Команда не распознана, введите /start для использования сервиса.';
                return $object->send();
            }

        }

    }

    public function runExternal($params)
    {
        $commands = new CommandsExternal();
        return $commands->run($params);
    }

}
