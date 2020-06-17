<?php
namespace app\controllers;

use \app\base\Router;
use \app\config\Config;
use \app\components\MessagesComponent;
use \app\components\ReferralComponent;
use \app\controllers\UsersController as Users;

class IndexController
{
    public $result;

    public $update_id;

    public $chat_id;

    public $user;
    public $user_id;
    public $username;
    public $first_name;
    public $last_name;
    public $user_type;
    public $language_code;

    public $message_id;
    public $message;
    public $date;

    public $entities = [];

    public $reply;
    public $replyParse = true;
    public $replyKeyboard;

    public $callback = false;
    public $callback_data;
    public $callback_id;

    public $command;
    public $params = [];

    public function init()
    {
        $this->result = json_decode(file_get_contents('php://input'), true);

        if ($this->result != null) {

            if (isset($this->result['callback_query'])) {
              self::setCallback();
            } else {
              self::set();
            }

            // Записываем пользователя в базу
            $user = new Users($this);
            $this->user = $user->run();

            // Проверяем рефералку
            if (mb_substr($this->message, 0, 7) === '/start ') {
                $refs = (new ReferralComponent($this))->run(mb_substr($this->message, 7));
                $this->message = mb_substr($this->message, 0, 6);
            }

            // Записываем сообщение в базу
            MessagesComponent::add($this);

            // Запускаем роутер
            Router::run($this);

            // Если это келбек кнопки, отдаём келбек.
            if ($this->callback == true) {
                return $this->answerCallback();
            }

        } elseif (isset($_POST) && !empty($_POST)) {
            Router::runExternal($_POST);
        } else {
            $options = getopt('k:r:');
            $this->params = $options;
            Router::runExternal($this->params);
        }

        if (!empty($refs)) {
            return (new ReferralComponent($this))->setAnswer($refs);
        }

    }

    public function set()
    {
        $this->update_id = $this->result['update_id'];

        $this->chat_id = $this->result['message']['chat']['id'];

        $this->user_id = $this->result['message']['from']['id'];
        $this->username = isset($this->result['message']['from']['username']) ? $this->result['message']['from']['username'] : 0;
        $this->first_name = $this->result['message']['from']['first_name'];
        $this->last_name = isset($this->result['message']['from']['last_name']) ? $this->result['message']['from']['last_name'] : 0;
        $this->language_code = isset($this->result['message']['from']['language_code']) ? $this->result['message']['from']['language_code'] : 0;

        $this->user_type = $this->result['message']['chat']['type'];

        $this->message_id = $this->result['message']['message_id'];
        $this->message = $this->result['message']['text'];
        $this->date = $this->result['message']['date'];

        $this->entities = isset($this->result['message']['entities']) ? $this->result['message']['entities'] : [];
    }

    public function setCallback()
    {
        $this->update_id = $this->result['update_id'];

        $this->chat_id = $this->result['callback_query']['message']['chat']['id'];

        $this->user_id = $this->result['callback_query']['from']['id'];
        $this->username = isset($this->result['callback_query']['from']['username']) ? $this->result['callback_query']['from']['username'] : 0;
        $this->first_name = $this->result['callback_query']['from']['first_name'];
        $this->last_name = isset($this->result['callback_query']['from']['last_name']) ? $this->result['callback_query']['from']['last_name'] : 0;
        $this->language_code = isset($this->result['callback_query']['from']['language_code']) ? $this->result['callback_query']['from']['language_code'] : 0;

        $this->user_type = $this->result['callback_query']['message']['chat']['type'];

        $this->message_id = $this->result['callback_query']['message']['message_id'];
        $this->date = $this->result['callback_query']['message']['date'];
        $this->message = $this->result['callback_query']['data'];

        $this->callback = true;
        $this->callback_data = $this->result['callback_query']['data'];
        $this->callback_id = $this->result['callback_query']['id'];
    }

    public function send()
    {
        $url = 'https://api.telegram.org/' . Config::API_TOKEN . '/sendMessage?chat_id=' . $this->chat_id;
        $url = $url . '&text=' . urlencode($this->reply);
        if ($this->replyParse === true) {
            $url = $url . '&parse_mode=html';
        }
        if ($this->replyKeyboard !== null) {
            $url = $url . '&reply_markup=' . urlencode($this->replyKeyboard);
        }

        $ch = curl_init();
        $optArray = array(CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true);
        curl_setopt_array($ch, $optArray);
            $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result);

        if ($result->ok == true) {
            return true;
        } else {
            return false;
        }

    }

    private function answerCallback()
    {
        $url = 'https://api.telegram.org/' . Config::API_TOKEN . '/answerCallbackQuery?callback_query_id=' . $this->callback_id;

        $ch = curl_init();
        $optArray = array(CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true);
        curl_setopt_array($ch, $optArray);
            $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result);

        if ($result->ok == true) {
            return true;
        } else {
            return false;
        }
    }

    public function var_dump($value)
    {
        file_put_contents('test.txt', var_export($value, true));
    }
}
