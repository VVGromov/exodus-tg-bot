<?php
namespace app\controllers;

use \app\models\TransfersModel as Transfers;
use \app\models\TagsModel as Tags;
use \app\models\CommandsModel as Commands;
use \app\models\TagsUsersModel as TagsUsers;
use \app\models\UsersModel as Users;
use \app\models\UsersRequisitesModel as UsersRequisites;
use \app\models\ObjectsPropertiesModel as ObjectsProperties;
use \app\models\dir\DirObjectsPropertiesModel as DirObjectsProperties;
use \app\models\dir\DirTransferModel as DirTransfer;
use \app\components\NotifyComponent as Notify;
use \app\components\TextComponent as Text;
use \app\components\SessionsComponent as Session;
use \app\components\ErrorsComponent as Errors;
use \app\utilities\DirPropertiesUtility as PropertiesUtility;
use \app\base\Controller;

/**
 *
 */
class TransferController extends Controller
{
    public function transfer()
    {
        $tag = (new Tags())->getOne([
            'where' => [
                'user_id = ' . $this->object->user->id,
            ]
        ]);

        $dir_transfer = (new DirTransfer())->getOne([
            'where' => [
                'code = "finished"'
            ]
        ]);

        $offset = 0;
        if (isset($this->object->params['offset'])) {
            $offset = $this->object->params['offset'];
        }

        if ($tag->color->code !== 'green') {
            $transfers = (new Transfers())->getAll([
                'where' => [
                    'tag_id = ' . $tag->id,
                    'status = ' . $dir_transfer->id
                ],
                'order' => [
                    'id ASC'
                ],
                'offset' => $this->object->params['offset'],
                'limit' => 11
            ]);
        } else {
            $transfers = (new Transfers())->getAll([
                'where' => [
                    'user_from = ' . $this->object->user->id,
                    'created >= "' . $tag->created . '"',
                    'status = ' . $dir_transfer->id
                ],
                'order' => [
                    'id ASC'
                ],
                'offset' => $this->object->params['offset'],
                'limit' => 11
            ]);
        }

        if ($transfers !== false) {
            $message = 'Ваши транзакции:' . PHP_EOL . PHP_EOL;
            $i = 0;
            foreach ($transfers as $key => $one) {
                $i++;
                if ($i <= 10) {
                    $user = (new Users())->getById($tag->color->code !== 'green' ? $one->user_from : $one->user_to);
                    $text = $i . ') ' . date('d.m.Y H:i', strtotime($one->created)) . ' %firstname% %lastname% %username% ' . $one->amount . ' %currency%' . PHP_EOL;
                    $message = $message . (new Text($this->object, ['user' => $user, 'currency' => $this->object->user->currency_id]))->getText($text);
                } else {
                    break;
                }
            }

            if (count($tags) > 10) {
                $chat_buttons = [];
                $chat_buttons[] = [
                    'text' => 'Загрузить ещё',
                    'callback_data' => $this->object->command->code . '?offset=' . ($offset + 10)
                ];
                $buttons['inline_keyboard'][] = $chat_buttons;
                $this->object->replyKeyboard = json_encode($buttons);
            }
        } else {
            $message = 'У вас нету транзакций.';
        }

        $this->object->reply = $message;
        return $this->object->send();
    }

    public function transfer_best()
    {
        $dir_transfer = (new DirTransfer())->getOne([
            'where' => [
                'code = "finished"'
            ]
        ]);

        $offset = 0;
        if (isset($this->object->params['offset'])) {
            $offset = $this->object->params['offset'];
        }

        $transfers = (new Transfers())->getAll([
            'where' => [
                '(user_from = ' . $this->object->user->id . ' OR user_to = ' . $this->object->user->id . ')',
                'status = ' . $dir_transfer->id
            ],
            'order' => [
                'amount DESC'
            ],
            'offset' => $this->object->params['offset'],
            'limit' => 11
        ]);


        if ($transfers !== false) {
            $message = 'Самые большие транзакции:' . PHP_EOL . PHP_EOL;
            $i = 0;
            foreach ($transfers as $key => $one) {
                $i++;
                if ($i <= 10) {
                    $user = (new Users())->getById($tag->color->code !== 'green' ? $one->user_from : $one->user_to);
                    $text = $i . ') ' . date('d.m.Y H:i', strtotime($one->created)) . ' %firstname% %lastname% %username% ' . $one->amount . ' %currency%' . PHP_EOL;
                    $message = $message . (new Text($this->object, ['user' => $user, 'currency' => $this->object->user->currency_id]))->getText($text);
                } else {
                    break;
                }
            }

            if (count($tags) > 10) {
                $chat_buttons = [];
                $chat_buttons[] = [
                    'text' => 'Загрузить ещё',
                    'callback_data' => $this->object->command->code . '?offset=' . ($offset + 10)
                ];
                $buttons['inline_keyboard'][] = $chat_buttons;
                $this->object->replyKeyboard = json_encode($buttons);
            }
        } else {
            $message = 'Трназакции не найдены.';
        }

        $this->object->reply = $message;
        return $this->object->send();
    }

    public function transfer_history()
    {
        $dir_transfer = (new DirTransfer())->getOne([
            'where' => [
                'code = "finished"'
            ]
        ]);

        $offset = 0;
        if (isset($this->object->params['offset'])) {
            $offset = $this->object->params['offset'];
        }

        $transfers = (new Transfers())->getAll([
            'where' => [
                '(user_from = ' . $this->object->user->id . ' OR user_to = ' . $this->object->user->id . ')',
                'status = ' . $dir_transfer->id
            ],
            'order' => [
                'created DESC'
            ],
            'offset' => $this->object->params['offset'],
            'limit' => 11
        ]);


        if ($transfers !== false) {
            $message = 'История всех транзакций:' . PHP_EOL . PHP_EOL;
            $i = 0;
            foreach ($transfers as $key => $one) {
                $i++;
                if ($i <= 10) {
                    $user = (new Users())->getById($tag->color->code !== 'green' ? $one->user_from : $one->user_to);
                    $text = $i . ') ' . date('d.m.Y H:i', strtotime($one->created)) . ' %firstname% %lastname% %username% ' . $one->amount . ' %currency%' . PHP_EOL;
                    $message = $message . (new Text($this->object, ['user' => $user, 'currency' => $this->object->user->currency_id]))->getText($text);
                } else {
                    break;
                }
            }

            if (count($tags) > 10) {
                $chat_buttons = [];
                $chat_buttons[] = [
                    'text' => 'Загрузить ещё',
                    'callback_data' => $this->object->command->code . '?offset=' . ($offset + 10)
                ];
                $buttons['inline_keyboard'][] = $chat_buttons;
                $this->object->replyKeyboard = json_encode($buttons);
            }
        } else {
            $message = 'Трназакции не найдены.';
        }

        $this->object->reply = $message;
        return $this->object->send();
    }

    public function transfer_tome()
    {
        $dir_transfer = (new DirTransfer())->getOne([
            'where' => [
                'code = "finished"'
            ]
        ]);

        $offset = 0;
        if (isset($this->object->params['offset'])) {
            $offset = $this->object->params['offset'];
        }

        $transfers = (new Transfers())->getAll([
            'where' => [
                'user_to = ' . $this->object->user->id,
                'status = ' . $dir_transfer->id
            ],
            'order' => [
                'created DESC'
            ],
            'offset' => $this->object->params['offset'],
            'limit' => 11
        ]);


        if ($transfers !== false) {
            $message = 'Транзакции мне от других:' . PHP_EOL . PHP_EOL;
            $i = 0;
            foreach ($transfers as $key => $one) {
                $i++;
                if ($i <= 10) {
                    $user = (new Users())->getById($tag->color->code !== 'green' ? $one->user_from : $one->user_to);
                    $text = $i . ') ' . date('d.m.Y H:i', strtotime($one->created)) . ' %firstname% %lastname% %username% ' . $one->amount . ' %currency%' . PHP_EOL;
                    $message = $message . (new Text($this->object, ['user' => $user, 'currency' => $this->object->user->currency_id]))->getText($text);
                } else {
                    break;
                }
            }

            if (count($tags) > 10) {
                $chat_buttons = [];
                $chat_buttons[] = [
                    'text' => 'Загрузить ещё',
                    'callback_data' => $this->object->command->code . '?offset=' . ($offset + 10)
                ];
                $buttons['inline_keyboard'][] = $chat_buttons;
                $this->object->replyKeyboard = json_encode($buttons);
            }
        } else {
            $message = 'Трназакции не найдены.';
        }

        $this->object->reply = $message;
        return $this->object->send();
    }

    public function transfer_fromme()
    {
        $dir_transfer = (new DirTransfer())->getOne([
            'where' => [
                'code = "finished"'
            ]
        ]);

        $offset = 0;
        if (isset($this->object->params['offset'])) {
            $offset = $this->object->params['offset'];
        }

        $transfers = (new Transfers())->getAll([
            'where' => [
                'user_from = ' . $this->object->user->id,
                'status = ' . $dir_transfer->id
            ],
            'order' => [
                'created DESC'
            ],
            'offset' => $this->object->params['offset'],
            'limit' => 11
        ]);


        if ($transfers !== false) {
            $message = 'Транзакции другим от меня:' . PHP_EOL . PHP_EOL;
            $i = 0;
            foreach ($transfers as $key => $one) {
                $i++;
                if ($i <= 10) {
                    $user = (new Users())->getById($tag->color->code !== 'green' ? $one->user_from : $one->user_to);
                    $text = $i . ') ' . date('d.m.Y H:i', strtotime($one->created)) . ' %firstname% %lastname% %username% ' . $one->amount . ' %currency%' . PHP_EOL;
                    $message = $message . (new Text($this->object, ['user' => $user, 'currency' => $this->object->user->currency_id]))->getText($text);
                } else {
                    break;
                }
            }

            if (count($tags) > 10) {
                $chat_buttons = [];
                $chat_buttons[] = [
                    'text' => 'Загрузить ещё',
                    'callback_data' => $this->object->command->code . '?offset=' . ($offset + 10)
                ];
                $buttons['inline_keyboard'][] = $chat_buttons;
                $this->object->replyKeyboard = json_encode($buttons);
            }
        } else {
            $message = 'Трназакции не найдены.';
        }

        $this->object->reply = $message;
        return $this->object->send();
    }

    public function transfer_set()
    {
        if (isset($this->object->params['tag_id']) && isset($this->object->params['status'])) {

            $dir_transfer = (new DirTransfer())->getOne([
                'where' => [
                    'id = ' . intval($this->object->params['status'])
                ]
            ]);

            $tag = (new Tags())->getOne([
                'where' => [
                    'id = ' . intval($this->object->params['tag_id']),
                ]
            ]);

            if (isset($this->object->params['user_id'])) {
                $user_from = $this->object->params['user_id'];
            } else {
                $user_from = $this->object->user->id;
            }

            $transfer = (new Transfers())->getOne([
                'where' => [
                    'tag_id = ' . $tag->id,
                    'user_from = ' . $user_from
                ]
            ]);

            if ($transfer !== false && $transfer->dir->code !== 'finished') {
                $transfer = (new Transfers())->update([
                    'id' => $transfer->id,
                    'status' => $this->object->params['status'],
                ]);
            } else {
                $transfer = (new Transfers())->insert([
                    'tag_id' => $tag->id,
                    'user_to' => $tag->user_id,
                    'user_from' => $user_from,
                    'status' => $this->object->params['status'],
                ]);
            }

            $ts = (new TagsUsers())->getOne([
                'where' => [
                    'tag_id' => $tag->id,
                    'user_id' => $user_from
                ]
            ]);

            if ($ts === false) {
                (new TagsUsers())->insert([
                    'tag_id' => $tag->id,
                    'user_id' => $user_from
                ]);
            }

            $data = [
                'tag' => $tag,
                'parent_id' => $transfer->id,
                'parent' => Transfers::TABLE_NAME,
                'user' => (new Users())->getById($transfer->user_to)
            ];

            $this->object->reply = (new Text($this->object, $data))->getText($dir_transfer->text->text, Text::TEMPLATE['reply_first']);

            if ((!isset($this->object->params['user_id']) && $transfer->dir->code !== 'did') ||  ($transfer->dir->code !== 'must' && !isset($this->object->params['amount_other']))) {
                $this->object->send();
            } elseif (isset($this->object->params['user_id']) && $transfer->dir->code == 'must') {
                $this->object->reply = 'Данные приняты.';
                $this->object->send();
            }

            if ($transfer->dir->code == 'want') {
                $this->object->command = (new Commands())->getOne([
                    'where' => [
                        'code = "/transfer_want"'
                    ]
                ]);
                $this->object->reply = '';
                $this->object->replyKeyboard = '';
                $this->object->params = ['transfer_id' => $transfer->id];
                return $this->transfer_want();
            } elseif ($transfer->dir->code == 'did' && !isset($this->object->params['amount_other'])) {
                $this->object->command = (new Commands())->getOne([
                    'where' => [
                        'code = "/transfer_did"'
                    ]
                ]);
                $this->object->reply = '';
                $this->object->replyKeyboard = '';
                $this->object->params = ['transfer_id' => $transfer->id];
                return $this->transfer_did();
            } elseif ($transfer->dir->code == 'did' && isset($this->object->params['amount_other'])) {
                $this->object->command = (new Commands())->getOne([
                    'where' => [
                        'code = "/transfer_finish"'
                    ]
                ]);
                $this->object->reply = '';
                $this->object->replyKeyboard = '';
                $this->object->params = ['transfer_id' => $transfer->id];
                return $this->transfer_finish();
            } else {
                return;
            }
        } else {
            return (new Errors($this->object))->getError('not-command');
        }
    }

    public function transfer_want()
    {
        if (!isset($this->object->params['transfer_id'])) {
            return (new Errors($this->object))->getError('not-command');
        }

        $transfer = (new Transfers())->getOne([
            'where' => [
                'id = ' . intval($this->object->params['transfer_id'])
            ]
        ]);

        $user = (new Users())->getById($transfer->user_to);

        $data = [
            'user' => $user,
            DirObjectsProperties::TYPE_BUTTONS_OBJECTS => (new UsersRequisites())->getAll([
                'where' => [
                    'user_id = ' . $user->id
                ]
            ])
        ];

        $properties = (new PropertiesUtility($this->object, (new Transfers()), $data))->questionResponse();

        if ($properties !== false) {

            $result = (new PropertiesUtility($this->object, (new ObjectsProperties())))->saveProperties($transfer->id, Transfers::TABLE_NAME, $properties);

            if ($result === false) {
                return (new Errors($this->object))->getError();
            }

            $dir_transfer = (new DirTransfer())->getOne([
                'where' => [
                    'code = "must"'
                ]
            ]);

            $transfer = (new Transfers())->update([
                'id' => $transfer->id,
                'status' => $dir_transfer->id,
                'req_id' => $properties[0]['value'],
                'amount' => end($properties)['value']
            ]);

            $transfers = (new DirTransfer())->getAll();
            $btns = [];
            foreach ($transfers as $one) {
                if ($one->text !== FALSE) {
                    $btns[$one->code] = [
                        'text' => (new Text($this->object))->getText($one->text->text, Text::TEMPLATE['button_must_action']),
                        'callback_data' => '/transfer_set?tag_id=' . $transfer->tag_id . '&status=' . $one->id,
                    ];
                }
            }

            $chat_buttons = [];
            $chat_buttons['inline_keyboard'][] = [$btns['did'], $btns['must']];
            $this->object->replyKeyboard = json_encode($chat_buttons);

            $data = [
                'parent' => Transfers::TABLE_NAME,
                'parent_id' => $transfer->id,
                'user' => $user
            ];
            $this->object->reply = (new Text($this->object, $data))->getText($dir_transfer->text->text, Text::TEMPLATE['reply_first']);

            $this->object->send();
            return (new Notify($this->object))->wantHelp($transfer);
        }

        return $this->object->send();
    }

    public function transfer_did()
    {
        if (!isset($this->object->params['transfer_id'])) {
            return (new Errors($this->object))->getError('not-command');
        }

        $transfer = (new Transfers())->getOne([
            'where' => [
                'id = ' . intval($this->object->params['transfer_id'])
            ]
        ]);

        return (new Notify($this->object))->helped($transfer);
    }

    public function transfer_finish()
    {
        if (!isset($this->object->params['transfer_id']) && !isset($this->object->params['amount_other'])) {
            return (new Errors($this->object))->getError('not-command');
        }

        $transfer = (new Transfers())->getOne([
            'where' => [
                'id = ' . intval($this->object->params['transfer_id'])
            ]
        ]);

        // Сессия
        $sessionComponent = new Session();
        $sessionComponent->user_id = $this->object->user->id;
        $session = $sessionComponent->get();

        if ($session === false && !isset($this->object->params['new_amount'])) {
            $this->object->params['new_amount'] = false;
            $sessionComponent->content = [
                'command' => $this->object->command,
                'params' => $this->object->params,
            ];
            $session = $sessionComponent->set();

            $req = (new UsersRequisites())->getOne([
                'where' => [
                    'id = ' . $transfer->req_id,
                ]
            ]);

            $user = (new Users())->getById($transfer->user_from);

            $data = [
                'user' => $user,
                'currency' => $this->object->user->currency_id
            ];

            $message = 'Пожалуйста, укажите сумму, которую вы получили на счёт (карту) ' . $req->type_id->title . ': <b>' . $req->number . '</b> от пользователя %firstname% %lastname% %username%' . PHP_EOL . PHP_EOL . 'Валюта: %currency%, введите только число:';
            $this->object->reply = (new Text($this->object, $data))->getText($message);
        } elseif (isset($this->object->params['new_amount']) && $this->object->params['new_amount'] === false) {
            $this->object->params['new_amount'] = $this->object->message;

            if (is_numeric($this->object->params['new_amount']) === false) {
                $this->object->reply = 'Отправленные данные не корректны. Проверьте и отправьте ещё раз(введите только число):';
            } else {
                $dir_transfer = (new DirTransfer())->getOne([
                    'where' => [
                        'code = "finished"'
                    ]
                ]);

                $transfer = (new Transfers())->update([
                    'id' => $transfer->id,
                    'status' => $dir_transfer->id,
                    'amount' => $this->object->params['new_amount']
                ]);

                $this->object->reply = 'Готово!';
            }
        }

        return $this->object->send();
    }
}
