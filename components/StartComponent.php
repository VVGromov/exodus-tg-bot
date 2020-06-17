<?php
namespace app\components;

use \app\models\UsersRequisitesModel as Requisites;
use \app\models\TransfersModel as Transfers;
use \app\models\UsersModel as Users;
use \app\models\ObjectsTextsModel as ObjectsTexts;
use \app\models\TagsModel as Tags;
use \app\models\ObjectsPropertiesModel as ObjectsProperties;
use \app\models\dir\DirCurrencyModel as DirCurrency;
use \app\models\dir\DirStatusModel as DirStatus;
use \app\models\dir\DirRequisitesTypeModel as DirRequisites;
use \app\components\NotifyComponent as Notify;
use \app\components\ErrorsComponent as Errors;
use \app\components\SessionsComponent as Session;
use \app\components\TextComponent as Text;
use \app\components\ReferralComponent;
use \app\utilities\DirPropertiesUtility as PropertiesUtility;
use \app\controllers\CommandsController;

/**
 *
 */
class StartComponent
{

    public $object;

    function __construct($object)
    {
        $this->object = $object;
    }

    public function index()
    {
        $link = (new ReferralComponent($this->object))->getReferralLink($this->object->user->ref_hash);

        $tag = (new Tags())->getOne([
            'where' => [
                'user_id = ' . $this->object->user->id,
            ]
        ]);
        if ($tag !== false) {
            $tag_text = (new Text($this->object, ['parent' => Tags::TABLE_NAME, 'parent_id' => $tag->id]))->getText($tag->color->text, Text::TEMPLATE['start']);
        } else {
            $tag_text = (new Errors($this->object))->getText('not-tag');
        }

        $requisites = (new Requisites())->getAll([
            'where' => [
                'user_id = ' . $this->object->user->id
            ],
            'order' => [
                'created ASC',
            ]
        ]);
        $list = '';
        if ($requisites !== NULL) {
            $i = 0;
            foreach ($requisites as $one) {
                $i++;
                $list .= $i . '. ' . $one->type_id->title . ': ' . $one->number . PHP_EOL;
            }
        }
        $message = $list != '' ? $list : '<b>Ваш список реквизитов пуст.</b>';

        $this->object->reply = 'Добро пожаловать в Exodus!' . PHP_EOL . PHP_EOL . 'Если вы хотите пригласить нового участника, поделитесь с ним этой ссылкой:' . PHP_EOL . $link . PHP_EOL . PHP_EOL . $tag_text . PHP_EOL . 'Список ваших реквизитов:' . PHP_EOL . PHP_EOL . $message;
        return $this->object->send();
    }

    public function run()
    {
        if (empty($this->object->params) && $this->object->params !== NULL) {
            $this->object->reply = 'Добро пожаловать в Exodus!' . PHP_EOL . PHP_EOL . 'Для того, чтобы бот начал работу, пожалуйста ответьте на несколько вопросов:';
            $this->object->send();
        }

        $requisite = (new Requisites())->getOne([
            'where' => [
                'user_id = ' . $this->object->user->id
            ]
        ]);

        $tag = (new Tags())->getOne([
            'where' => [
                'user_id = ' . $this->object->user->id,
            ]
        ]);

        $user = (new Users())->getOne([
            'where' => [
                'id = ' . $this->object->user->id,
            ]
        ]);

        if ($user->currency_id == 0) {
            return $this->setCurrency();
        }

        if ($requisite === false) {
            return $this->setRequisite();
        }

        if ($tag === false) {
            return $this->setStatus();
        }

        $this->object->user = $user;
        $this->object->params = [];
        return (new CommandsController($this->object))->run();
    }

    public function setCurrency()
    {
        if (!isset($this->object->params['new'])) {
            $currency = (new DirCurrency())->getAll();
            $message = 'Выберите валюту:';
            $chat_buttons = [];
            foreach ($currency as $one) {
                $chat_buttons[] = [
                    'text' => $one->title,
                    'callback_data' => $this->object->command->code . '?new=' . $one->id
                ];
            }
            $buttons['inline_keyboard'][] = $chat_buttons;
            $this->object->replyKeyboard = json_encode($buttons);
            $this->object->reply = $message;
            return $this->object->send();
        } else {
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
                $this->object->params = NULL;
                return $this->run();
            } else {
                return (new Errors($this->object))->getError();
            }
        }
    }

    public function setRequisite()
    {
        if (empty($this->object->params) || $this->object->params === NULL) {
            $type_requisites = (new DirRequisites())->getAll();
            $chat_buttons = [];

            foreach ($type_requisites as $one) {
                $chat_buttons[] = [
                    'text' => $one->title,
                    'callback_data' => $this->object->command->code . '?type=' . $one->id
                ];
            }

            $buttons['inline_keyboard'][] = $chat_buttons;

            $this->object->replyKeyboard = json_encode($buttons);
            $this->object->reply = 'Выберите тип реквизита:';
        } else {
            if (isset($this->object->params['type']) && !isset($this->object->params['number'])) {
                $type = (new DirRequisites())->getById($this->object->params['params']['type']);
                $this->object->reply = 'Введите номер кошелька (карты) ' . $type->title . ':';

                $this->object->params['number'] = 0;

                $sessionComponent = new Session();
                $sessionComponent->user_id = $this->object->user->id;
                $sessionComponent->content = [
                    'command' => $this->object->command,
                    'params' => $this->object->params,
                ];
                $session = $sessionComponent->set();
            } elseif (isset($this->object->params['type']) && isset($this->object->params['number']) && !isset($this->object->params['confirm'])) {
                $sessionComponent = new Session();
                $sessionComponent->user_id = $this->object->user->id;
                $session = $sessionComponent->get();

                // Обновление ссесии
                $sessionComponent->id = $session->id;
                $sessionComponent->delete();

                $type = (new DirRequisites())->getById($this->object->params['type']);
                $this->object->reply = 'Подтвердите данные кошелька (карты) ' . $type->title . ': ' . PHP_EOL . '<b>' . $this->object->message . '</b>' . PHP_EOL . PHP_EOL . 'Всё верно?';

                $chat_buttons[] = [
                    'text' => 'Да',
                    'callback_data' => $this->object->command->code . '?type=' . $this->object->params['type'] . '&number= ' . $this->object->message . '&confirm=1'
                ];

                $chat_buttons[] = [
                    'text' => 'Нет',
                    'callback_data' => $this->object->command->code . '?type=' . $this->object->params['type'] . '&number= ' . $this->object->message . '&confirm=0'
                ];

                $buttons['inline_keyboard'][] = $chat_buttons;
                $this->object->replyKeyboard = json_encode($buttons);

            } elseif (isset($this->object->params['type']) && isset($this->object->params['number']) && isset($this->object->params['confirm'])) {
                if ($this->object->params['confirm'] == 1) {
                    $requisite = (new Requisites())->insert([
                        'user_id' => $this->object->user->id,
                        'type_id' => $this->object->params['type'],
                        'number' => $this->object->params['number']
                    ]);

                    if ($requisite !== false) {
                        return $this->run();
                    } else {
                        $this->object->reply = 'Произошла внутренняя ошибка! Попробуйте ещё раз или обратитесь к администрации бота.';
                        $this->object->send();
                        $this->object->params = NULL;
                        return $this->setRequisite();
                    }
                } else {
                    $this->object->reply = 'Добавьте кошелек (карту) заново. Будьте внимательны при вводе данных!';
                    $this->object->send();
                    $this->object->params = NULL;
                    return $this->setRequisite();
                }
            }
        }

        return $this->object->send();
    }

    public function setStatus()
    {
        if (isset($this->object->params['parent']) === false) {
            $statuss = (new DirStatus())->getAll();
            $chat_buttons = [];
            foreach ($statuss as $one) {
                $chat_buttons[] = [
                    'text' => $one->title,
                    'callback_data' => $this->object->command->code . '?parent=' . $one->id,
                ];
            }

            $buttons['inline_keyboard'][] = $chat_buttons;

            $this->object->replyKeyboard = json_encode($buttons);
            $this->object->reply = 'Выберите статус:';
        } elseif ($this->object->params['parent'] != '' && ($properties = (new PropertiesUtility($this->object, (new DirStatus())))->questionResponse()) !== false) {

            $tag = (new Tags())->insert([
                'user_id' => $this->object->user->id,
                'color_id' => $this->object->params['parent']
            ]);

            if ($tag === false) {
                return (new Errors($this->object))->getError();
            }

            $result = (new PropertiesUtility($this->object, (new ObjectsProperties())))->saveProperties($tag->id, Tags::TABLE_NAME, $properties);

            if ($result === false) {
                (new Tags())->delete([
                    'where' => [
                        'id = ' . $tag->id,
                    ]
                ]);
                return (new Errors($this->object))->getError();
            } else {
                (new Tags())->delete([
                    'where' => [
                        'id <> ' . $tag->id,
                        'user_id = ' . $tag->user_id,
                    ]
                ]);
            }

            $this->run();
            return (new Notify($this->object))->statusChanged();
        }
        return $this->object->send();
    }
}

?>
