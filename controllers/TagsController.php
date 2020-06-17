<?php
namespace app\controllers;

use \app\components\SessionsComponent as Session;
use \app\components\ErrorsComponent as Errors;
use \app\components\ReferralComponent as Referral;
use \app\models\dir\DirTransferModel as DirTransfer;
use \app\models\TransfersModel as Transfers;
use \app\models\TagsModel as Tags;
use \app\models\UsersModel;
use \app\base\Controller;
use \app\components\TextComponent as Text;

/**
 *
 */
class TagsController extends Controller
{
    public function tags()
    {
        $sessionComponent = new Session();
        $sessionComponent->user_id = $this->object->user->id;
        if ($this->object->params == NULL) {
            $transfers = (new Transfers())->getAll([
                'where' => [
                    'user_from = ' . $this->object->user->id,
                ]
            ]);

            $tags_ids = [];
            $tags = false;

            if ($transfers !== false) {
                foreach ($transfers as $one) {
                    if ($one->dir->code !== 'finished' && $one->dir->code !== 'refuse') {
                        $tags_ids[] = $one->tag_id;
                    }
                }

                $tags_ids = implode(',', $tags_ids);

                $tags = (new Tags())->getAll([
                    'where' => [
                        'id IN (' . $tags_ids . ')',
                    ],
                    'order' => [
                        'id ASC'
                    ]
                ]);
            }

            if ($tags !== false) {
                $this->object->params['number'] = 0;
                $sessionComponent->content = [
                    'command' => $this->object->command,
                    'params' => $this->object->params,
                ];
                $session = $sessionComponent->set();

                $message = NULL;
                $i = 0;
                foreach ($tags as $one) {
                    $i++;
                    $username = ($one->user->username !== '0') ? '@' . $one->user->username : '';
                    $message = $message . $i . ') ' . $one->user->first_name . ' ' . $one->user->last_name . ' ' . $username . ', статус: <b>' . $one->color->title . '</b>' . PHP_EOL;
                }

                $message = $message . PHP_EOL . 'Для того чтобы осуществить действия по тегу, отправте его номер.';

                $this->object->reply = 'Теги на которые вы подписаны:' . PHP_EOL . PHP_EOL . $message;
            } else {
                $this->object->reply = 'Вы не подписаны ни на один из существующих тегов или срок тегов был окончен.';
                $session = $sessionComponent->delete();
            }
        } else {
            $session = $sessionComponent->get();
            $sessionComponent->id = $session->id;
            $sessionComponent->delete();
            if (isset($this->object->params['number'])) {
                $this->object->params['number'] = intval($this->object->message) - 1;

                $transfers = (new Transfers())->getAll([
                    'where' => [
                        'user_from = ' . $this->object->user->id,
                    ]
                ]);

                $transfer = false;

                $tags_ids = [];
                $tags = false;

                if ($transfers !== false) {
                    foreach ($transfers as $one) {
                        if ($one->dir->code !== 'finished' && $one->dir->code !== 'refuse') {
                            $tags_ids[] = $one->tag_id;
                        }
                    }

                    $tags_ids = implode(',', $tags_ids);

                    $tags = (new Tags())->getAll([
                        'where' => [
                            'id IN (' . $tags_ids . ')',
                        ],
                        'order' => [
                            'id ASC'
                        ]
                    ]);
                }

                if ($tags === false && array_key_exists($this->object->params['number'], $tags) === false) {
                    return (new Errors($this->object))->getError('tag-not-found');
                }

                $tag = $tags[$this->object->params['number']];

                $transfer = (new Transfers())->getOne([
                    'where' => [
                        'tag_id = ' . $tag->id,
                        'user_from = ' . $this->object->user->id
                    ]
                ]);

                if ($transfer !== false && ($transfer->dir->code == 'wait' || $transfer->dir->code == 'redirect' || $transfer->dir->code == 'want')) {
                    return $this->help($tag);
                } elseif ($transfer !== false && $transfer->dir->code == 'must') {
                    return $this->mustHelp($tag);
                } elseif ($transfer !== false && $transfer->dir->code == 'did') {
                    return $this->didHelp($tag);
                } elseif ($transfer !== false && $transfer->dir->code == 'finished') {
                    return $this->reHelp($tag);
                } else {
                    return (new Errors($this->object))->getError('tag-not-found');
                }

            }
        }

        return $this->object->send();
    }

    public function help($tag)
    {
        $text = $tag->color->text;
        $user = (new UsersModel())->getById($tag->user_id);

        if ($text !== false && $user !== false) {
            $this->object->reply = (new Text($this->object, ['parent' => Tags::TABLE_NAME, 'parent_id' => $tag->id, 'user' => $user]))->getText($tag->color->text, Text::TEMPLATE['notify_new']);

            $transfers = (new DirTransfer())->getAll();
            $btns = [];
            foreach ($transfers as $one) {
                if ($one->text !== FALSE) {
                    $btns[$one->code] = [
                        'text' => (new Text($this->object))->getText($one->text->text, Text::TEMPLATE['button']),
                        'callback_data' => '/transfer_set?tag_id=' . $tag->id . '&status=' . $one->id,
                    ];
                }
            }

            $chat_buttons = [];
            $chat_buttons['inline_keyboard'][] = [$btns['want'], $btns['redirect']];
            $chat_buttons['inline_keyboard'][] = [$btns['wait'], $btns['refuse']];
            $this->object->replyKeyboard = json_encode($chat_buttons);
            if ($this->object->reply !== false) {
                return $this->object->send();
            }
        }
    }

    public function mustHelp($tag)
    {
        $dir_transfer = (new DirTransfer())->getOne([
            'where' => [
                'code = "must"'
            ]
        ]);

        $transfer = (new Transfers())->getOne([
            'where' => [
                'tag_id = ' . $tag->id,
                'status = ' . $dir_transfer->id,
                'user_from = ' . $this->object->user->id
            ]
        ]);


        if ($transfer === false) {
            return (new Errors($this->object))->getError('tag-not-found');
        }

        $user = (new UsersModel())->getById($transfer->user_to);

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

        return $this->object->send();
    }

    public function didHelp($tag)
    {
        $user = (new UsersModel())->getById($tag->user_id);

        $dir_transfer = (new DirTransfer())->getOne([
            'where' => [
                'code = "did"'
            ]
        ]);

        $transfer = (new Transfers())->getOne([
            'where' => [
                'tag_id = ' . $tag->id,
                'status = ' . $dir_transfer->id,
                'user_from = ' . $this->object->user->id
            ]
        ]);

        if ($transfer === false) {
            return (new Errors($this->object))->getError('tag-not-found');
        }

        $this->object->reply = (new Text($this->object, ['user' => $user]))->getText($transfer->dir->text->text, Text::TEMPLATE['not-confirm']);

        return $this->object->send();
    }

    public function reHelp($tag)
    {
        $text = $tag->color->text;
        $user = (new UsersModel())->getById($tag->user_id);

        if ($text !== false && $user !== false) {
            $this->object->reply = (new Text($this->object, ['parent' => Tags::TABLE_NAME, 'parent_id' => $tag->id, 'user' => $user]))->getText($tag->color->text, Text::TEMPLATE['notify_new']);

            $transfers = (new DirTransfer())->getAll();
            $btns = [];
            foreach ($transfers as $one) {
                $view = $one->code == 'finished' ? Text::TEMPLATE['button_repeat'] : Text::TEMPLATE['button'];
                if ($one->text !== FALSE) {
                    $btns[$one->code] = [
                        'text' => (new Text($this->object))->getText($one->text->text, $view),
                        'callback_data' => '/transfer_set?tag_id=' . $tag->id . '&status=' . $one->id,
                    ];
                }
            }

            $chat_buttons = [];
            $chat_buttons['inline_keyboard'][] = [$btns['want'], $btns['redirect']];
            $chat_buttons['inline_keyboard'][] = [$btns['wait'], $btns['refuse']];
            $this->object->replyKeyboard = json_encode($chat_buttons);
            if ($this->object->reply !== false) {
                return $this->object->send();
            }
        } else {
            return (new Errors($this->object))->getError('tag-not-found');
        }
    }
}
