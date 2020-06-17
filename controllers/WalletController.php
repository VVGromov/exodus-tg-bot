<?php
namespace app\controllers;

use \app\models\UsersRequisitesModel as Requisites;
use \app\models\dir\DirRequisitesTypeModel as DirRequisites;
use \app\models\TagsModel as Tags;
use \app\components\SessionsComponent as Session;
use \app\components\TextComponent as Text;
use \app\config\Config;
use \app\base\Controller;

/**
 *
 */
class WalletController extends Controller
{

    public function wallet()
    {
        $tag = (new Tags())->getOne([
            'where' => [
                'user_id = ' . $this->object->user->id
            ]
        ]);

        $data = [
            'parent' => Tags::TABLE_NAME,
            'parent_id' => $tag->id,
            'child_view' => 'wallet'
        ];

        $message = 'Этот кошелёк - информация о том, сколько денег собрано (если ваш статус красный или оранжевый), или о том, сколько денег вы отдали в помощь другим участникам (если ваш статус зелёный)' . PHP_EOL . PHP_EOL . '%properties%';
        $this->object->reply = (new Text($this->object, $data))->getText($message);
        $this->object->send();
    }

    public function wallet_list()
    {
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

        $this->object->reply = 'Список ваших реквизитов:' . PHP_EOL . PHP_EOL . $message;

        $this->object->send();
    }

    public function wallet_new()
    {
        if ($this->object->params == NULL) {
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
            $this->object->reply = 'Выберите тип реквизита, который вы хотите добавить:';
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
                        $this->object->reply = 'Кошелек (карта) успешно добавлен.';
                        $this->object->send();
                        return $this->wallet_list();
                    } else {
                        $this->object->reply = 'Произошла внутренняя ошибка! Попробуйте ещё раз или обратитесь к администрации бота.';
                    }
                } else {
                    $this->object->reply = 'Добавьте кошелек (карту) заново. Будьте внимательны при вводе данных!';
                }
            }
        }

        return $this->object->send();
    }

    public function wallet_edit()
    {
        if ($this->object->params == NULL) {
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
                    $list .= '<b>' . $i . '.</b> ' . $one->type_id->title . ': ' . $one->number . PHP_EOL;
                }
            }


            if ($list != '') {
                $this->object->reply = 'Введите порядковый номер кошелька (карты), который вы хотите отредактировать:' . PHP_EOL . PHP_EOL . $list;
                $this->object->params['number'] = 0;

                $sessionComponent = new Session();
                $sessionComponent->user_id = $this->object->user->id;
                $sessionComponent->content = [
                    'command' => $this->object->command,
                    'params' => $this->object->params,
                ];
                $session = $sessionComponent->set();
            } else {
                $this->object->reply = '<b>Ваш список реквизитов пуст.</b>';
            }
        } else {
          if (isset($this->object->params['number']) && !isset($this->object->params['id'])) {
              $sessionComponent = new Session();
              $sessionComponent->user_id = $this->object->user->id;
              $session = $sessionComponent->get();

              $requisites = (new Requisites())->getAll([
                  'where' => [
                      'user_id = ' . $this->object->user->id
                  ],
                  'order' => [
                      'created ASC',
                  ]
              ]);

              $list = [];

              if ($requisites !== NULL) {
                  $i = 0;
                  foreach ($requisites as $one) {
                      $i++;
                      $list[$i] = $one;
                  }
              }
              if (!empty($list) && ($reqs = $list[intval($this->object->message)])) {
                  $this->object->reply = 'Введите новый номер кошелька (карты) ' . $reqs->type_id->title . ': ' . PHP_EOL . '<b>' . $reqs->number . '</b>';
                  $session->content['params']['id'] = $reqs->id;

                  // Обновление ссесии
                  $sessionComponent->id = $session->id;
                  $sessionComponent->content = $session->content;
                  $sessionComponent->update();
              } else {
                  $this->object->reply = 'Произошла внутренняя ошибка! Попробуйте ещё раз или обратитесь к администрации бота.';
              }

          } elseif (isset($this->object->params['id']) && !isset($this->object->params['confirm'])) {
                $sessionComponent = new Session();
                $sessionComponent->user_id = $this->object->user->id;
                $session = $sessionComponent->get();

                $reqs = (new Requisites())->getOne([
                    'where' => [
                        'id = ' . intval($this->object->params['id'])
                    ]
                ]);

                if ($reqs !== false) {
                    $this->object->reply = 'Подтвердите данные кошелька (карты) ' . $reqs->type_id->title . ': ' . PHP_EOL . '<b>' . $this->object->message . '</b>' . PHP_EOL . PHP_EOL . 'Всё верно?';

                    $chat_buttons[] = [
                        'text' => 'Да',
                        'callback_data' => $this->object->command->code . '?id=' . $this->object->params['id'] . '&number=' . $this->object->message . '&confirm=1'
                    ];

                    $chat_buttons[] = [
                        'text' => 'Нет',
                        'callback_data' => $this->object->command->code . '?id=' . $this->object->params['id'] . '&number=' . $this->object->message . '&confirm=0'
                    ];

                    $buttons['inline_keyboard'][] = $chat_buttons;
                    $this->object->replyKeyboard = json_encode($buttons);
                } else {
                    $this->object->reply = 'Произошла внутренняя ошибка! Попробуйте ещё раз или обратитесь к администрации бота.';
                }

            } elseif (isset($this->object->params['id']) && isset($this->object->params['number']) && isset($this->object->params['confirm'])) {

                if ($this->object->params['confirm'] == 1) {
                    $requisite = (new Requisites())->update([
                        'id' => $this->object->params['id'],
                        'number' => $this->object->params['number']
                    ]);

                    if ($requisite !== false) {
                        $this->object->reply = 'Кошелек (карта) успешно обновлен.';
                        $this->object->send();
                        return $this->wallet_list();
                    } else {
                        $this->object->reply = 'Произошла внутренняя ошибка! Попробуйте ещё раз или обратитесь к администрации бота.';
                    }
                } else {
                    $this->object->reply = 'Отредактируйте кошелек (карту) заново. Будьте внимательны при вводе данных!';
                }
            }
        }

        return $this->object->send();
    }

    public function wallet_delete()
    {
        if ($this->object->params == NULL) {
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
                    $list .= '<b>' . $i . '.</b> ' . $one->type_id->title . ': ' . $one->number . PHP_EOL;
                }
            }


            if ($list != '') {
                $this->object->reply = 'Введите порядковый номер кошелька (карты), который вы хотите удалить:' . PHP_EOL . PHP_EOL . $list;
                $this->object->params['number'] = 0;

                $sessionComponent = new Session();
                $sessionComponent->user_id = $this->object->user->id;
                $sessionComponent->content = [
                    'command' => $this->object->command,
                    'params' => $this->object->params,
                ];
                $session = $sessionComponent->set();
            } else {
                $this->object->reply = '<b>Ваш список реквизитов пуст.</b>';
            }
        } else {
            if (isset($this->object->params['number']) && !isset($this->object->params['confirm'])) {
                $sessionComponent = new Session();
                $sessionComponent->user_id = $this->object->user->id;
                $session = $sessionComponent->get();

                // Обновление ссесии
                $sessionComponent->id = $session->id;
                $sessionComponent->delete();

                $requisites = (new Requisites())->getAll([
                    'where' => [
                        'user_id = ' . $this->object->user->id
                    ],
                    'order' => [
                        'created ASC',
                    ]
                ]);

                $list = [];

                if ($requisites !== NULL) {
                    $i = 0;
                    foreach ($requisites as $one) {
                        $i++;
                        $list[$i] = $one;
                    }
                }
                if (!empty($list) && ($reqs = $list[intval($this->object->message)])) {
                    $this->object->reply = 'Вы хотите удалить кошелек (карту) ' . $reqs->type_id->title . ': ' . PHP_EOL . '<b>' . $reqs->number . '</b>' . PHP_EOL . PHP_EOL . 'Всё верно?';

                    $chat_buttons[] = [
                        'text' => 'Да',
                        'callback_data' => $this->object->command->code . '?id=' . $reqs->id . '&confirm=1'
                    ];

                    $chat_buttons[] = [
                        'text' => 'Нет',
                        'callback_data' => $this->object->command->code . '?id=' . $reqs->id . '&confirm=0'
                    ];

                    $buttons['inline_keyboard'][] = $chat_buttons;
                    $this->object->replyKeyboard = json_encode($buttons);
                } else {
                    $this->object->reply = 'Произошла внутренняя ошибка! Попробуйте ещё раз или обратитесь к администрации бота.';
                }

            } elseif (isset($this->object->params['id']) && isset($this->object->params['confirm'])) {

                if ($this->object->params['confirm'] == 1) {
                    $result = (new Requisites())->delete(intval($this->object->params['id']));

                    if ($result !== false) {
                        $this->object->reply = 'Кошелек (карта) успешно удалён.';
                        $this->object->send();
                        return $this->wallet_list();
                    } else {
                        $this->object->reply = 'Произошла внутренняя ошибка! Попробуйте ещё раз или обратитесь к администрации бота.';
                    }
                } else {
                    $this->object->params = [];
                    return $this->wallet_delete();
                }
            }
        }

        return $this->object->send();
    }
}
