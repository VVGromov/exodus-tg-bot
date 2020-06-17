<?php
namespace app\controllers;

use \app\models\UsersRequisitesModel as Requisites;
use \app\models\UsersRelationsModel as Relations;
use \app\models\TransfersModel as Transfers;
use \app\models\UsersModel as Users;
use \app\models\dir\DirCurrencyModel as DirCurrency;
use \app\components\ErrorsComponent as Errors;
use \app\components\SessionsComponent as Session;
use \app\components\TextComponent as Text;
use \app\config\Config;
use \app\base\Controller;

/**
 *
 */
class ProfileController extends Controller
{
    public function user()
    {
        $rel = (new Relations())->getOne([
            'where' => [
                'user_invited = ' . $this->object->user->id
            ],
            'order' => [
                'created ASC'
            ]
        ]);

        $message = '';

        $start = (new \DateTime())->createFromFormat('Y-m-d H:i:s', $this->object->user->created);
        $now = new \DateTime('now');
        $value = $now->diff($start)->format('%a');

        if ($rel !== false && $rel->created == $this->object->user->created) {
            $text = 'Вас пригласил участник %firstname% %lastname% %username% ' . $value . ' (дней) назад.' . PHP_EOL;
            $message = $message . (new Text($this->object, ['user' => $rel->user_host]))->getText($text);
        } else {
            $message = $message . 'Вы начали пользоваться нашим ботом ' . $value . ' (дней) назад.' . PHP_EOL;
        }

        $message = $message . 'Список участников, с которыми у вас были взаимодействия:' . PHP_EOL . PHP_EOL;

        $users = $this->getAllRelationsUsers();

        if ($users !== false) {
            $i = 0;
            foreach ($users as $one) {
                $i++;
                $text = $i . ') <b>%status%</b> - %firstname% %lastname% %username%' . PHP_EOL;
                $message = $message . (new Text($this->object, ['user' => $one]))->getText($text);
            }
        } else {
            $message = $message . 'Список пуст.';
        }

        $this->object->reply = $message;
        return $this->object->send();
    }

    public function user_currency()
    {

        $chat_buttons = [];

        if (isset($this->object->params['change']) && !isset($this->object->params['new'])) {
            $currency = (new DirCurrency())->getAll();
            $message = 'Выберите валюту:';
            foreach ($currency as $one) {
                $chat_buttons[] = [
                    'text' => $one->title,
                    'callback_data' => $this->object->command->code . '?new=' . $one->id
                ];
            }
        } elseif (isset($this->object->params['new'])) {
            $currency = (new DirCurrency())->getOne([
                'where' => [
                    'id = ' . intval($this->object->params['new'])
                ]
            ]);
            if ($currency !== false) {
                (new Users())->update([
                    'user_id' => $this->object->user->user_id,
                    'currency_id' => $currency->id
                ]);
                $message = 'Ваша валюта изменена на: (' . $currency->title . ')';
                $chat_buttons[] = [
                    'text' => 'Изменить валюту',
                    'callback_data' => $this->object->command->code . '?change=1'
                ];
            } else {
                return (new Errors($this->object))->getError();
            }
        } else {
            $currency = (new DirCurrency())->getOne([
                'where' => [
                    'id = ' . $this->object->user->currency_id,
                ]
            ]);
            if ($currency !== false) {
                $message = 'Ваша валюта: (' . $currency->title . ')';
                $chat_buttons[] = [
                    'text' => 'Изменить валюту',
                    'callback_data' => $this->object->command->code . '?change=1'
                ];
            } else {
                $message = 'Вы ещё не выбрали валюту.';
                $chat_buttons[] = [
                    'text' => 'Выбрать валюту',
                    'callback_data' => $this->object->command->code . '?change=1'
                ];
            }
        }

        $buttons['inline_keyboard'][] = $chat_buttons;
        $this->object->replyKeyboard = json_encode($buttons);
        $this->object->reply = $message;
        return $this->object->send();
    }

    private function getAllRelationsUsers()
    {
        $rels = (new Relations())->getAll([
            'where' => [
                'user_invited = ' . $this->object->user->id . ' OR user_host = ' . $this->object->user->id
            ]
        ]);

        $transfers = (new Transfers())->getAll([
            'where' => [
                'user_from = ' . $this->object->user->id . ' OR user_to = ' . $this->object->user->id
            ]
        ]);

        $users = [];
        $result = false;

        if ($rels !== false) {
            foreach ($rels as $one) {
                if ($one->user_invited->id != $this->object->user->id) {
                    $users[] = $one->user_invited->id;
                } elseif ($one->user_host->id != $this->object->user->id) {
                    $users[] = $one->user_host->id;
                }
            }
        }

        if ($transfers !== false) {
            foreach ($transfers as $one) {
                if ($one->user_from != $this->object->user->id) {
                    $users[] = $one->user_from;
                } elseif ($one->user_to != $this->object->user->id) {
                    $users[] = $one->user_to;
                }
            }
        }

        $users = array_unique($users);

        if ($users !== false) {
            $result = (new Users())->getAll([
                'where' => [
                    'id IN (' . implode(',', $users) . ')'
                ],
                'order' => [
                    'created ASC'
                ]
            ]);
        }

        return $result;
    }
}
