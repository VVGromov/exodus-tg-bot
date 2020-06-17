<?php
namespace app\controllers;

use \app\base\DataBaseConnect;
use \app\models\UsersModel;

/**
 *
 */
class UsersController
{
    public $object;

    function __construct($object)
    {
        $this->object = $object;
    }

    public function run()
    {
        $user_model = new UsersModel();
        $user = $user_model->getUserByUserId($this->object->user_id);

        if ($user === false) {
            return self::create();
        } else {
            return self::update($user);
        }
    }

    private function create()
    {
        if ($this->object->user_id == null || $this->object->chat_id == null) {
            $this->object->reply = 'Произошла внутренняя ошибка! Попробуйте ещё раз или обратитесь к администрации бота.';
            return $this->object->send();
        }

        $user_data = [
            'user_id' => $this->object->user_id,
            'username' => $this->object->username,
            'first_name' => $this->object->first_name,
            'last_name' => $this->object->last_name,
            'chat_id' => $this->object->chat_id,
            'language_code' => $this->object->language_code,
            'is_bot' => $this->object->user_type,
        ];

        $user_model = new UsersModel();
        $user = $user_model->insert($user_data);

        if ($user == false) {
            $this->object->reply = 'Произошла внутренняя ошибка! Попробуйте ещё раз или обратитесь к администрации бота.';
            return $this->object->send();
        } else {
            return $user;
        }
    }

    private function update($user)
    {
        $user_data = [
            'user_id' => $user->user_id,
            'username' => $this->object->username,
            'first_name' => $this->object->first_name,
            'last_name' => $this->object->last_name,
            'chat_id' => $this->object->chat_id,
            'language_code' => $this->object->language_code,
        ];

        $user_model = new UsersModel();
        $user = $user_model->update($user_data);

        if ($user == false) {
            $this->object->reply = 'Произошла внутренняя ошибка! Попробуйте ещё раз или обратитесь к администрации бота.';
            return $this->object->send();
        } else {
            return $user;
        }
    }

    public function checkUsername()
    {
        if ($this->object->username == null) {
            $this->object->reply = 'Для использования данной функции вам необходимо создать Username в найстроках своего телеграм профиля.';
            $this->object->send();
            return false;
        } else {
            return true;
        }
    }

    public function checkBlockUser()
    {
        $user_model = new UsersModel();
        $user = $user_model->getUserByUserId($this->object->user_id);
        if ($user !== false && $user->status == 1) {
            $this->object->reply = '<b>Вы заблокированы за нарушение!</b> ' . PHP_EOL . PHP_EOL . 'Обратитесь к администрации @idamascus.';
            $this->object->send();
            return true;
        } else {
            return false;
        }
    }
}
