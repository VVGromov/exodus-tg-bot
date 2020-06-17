<?php
namespace app\components;

use \app\models\UsersRelationsModel as UsersRelations;
use \app\models\TagsModel as Tags;
use \app\models\UsersModel as Users;
use \app\models\UsersRequisitesModel;
use \app\models\dir\DirTransferModel as DirTransfer;
use \app\models\TransfersModel as Transfers;
use \app\controllers\IndexController as Index;
use \app\components\TextComponent as Text;

/**
 *
 */
class NotifyComponent
{
    public $object;

    function __construct($object)
    {
        $this->object = $object;
    }

    public function statusChanged()
    {
        $users = (new UsersRelations())->getAll([
            'where' => [
                'user_host = ' . $this->object->user->id . ' OR user_invited = ' . $this->object->user->id
            ]
        ]);

        $list_users = [];

        if ($users !== false) {
            foreach ($users as $one) {
                $list_users[$one->user_host->id] = $one->user_host;
                $list_users[$one->user_invited->id] = $one->user_invited;
            }
        }

        $green_tags = (new Tags())->getAll([
            'where' => [
                'status = 1'
            ]
        ]);

        if ($green_tags !== false) {
            $ids = [];
            foreach ($green_tags as $one) {
                $ids[] = $one->user_id;
            }

            $ids = implode(', ', $ids);

            $green_users = (new Users())->getAll([
                'where' => [
                    'id IN (' . $ids . ')'
                ]
            ]);

            foreach ($green_users as $one) {
                $list_users[$one->id] = $one;
            }
        }

        $tag = (new Tags())->getOne([
            'where' => [
                'user_id = ' . $this->object->user->id,
            ]
        ]);

        if (!empty($list_users) && $tag !== false) {
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
            $sended = [];
            foreach ($users as $one) {
                $index = new Index();
                if ($one->user_invited->id == $this->object->user->id) {
                    $chat_id = $one->user_host->chat_id;
                } elseif ($one->user_host->id == $this->object->user->id) {
                    $chat_id = $one->user_invited->chat_id;
                } else {
                    return;
                }

                $index->chat_id = $chat_id;
                $index->reply = (new Text($this->object, ['parent' => Tags::TABLE_NAME, 'parent_id' => $tag->id]))->getText($tag->color->text, Text::TEMPLATE['notify_new']);

                $chat_buttons = [];
                $chat_buttons['inline_keyboard'][] = [$btns['want'], $btns['redirect']];
                $chat_buttons['inline_keyboard'][] = [$btns['wait'], $btns['refuse']];
                $index->replyKeyboard = json_encode($chat_buttons);
                if ($index->reply !== false && !in_array($index->chat_id, $sended)) {
                    $sended[] = $index->chat_id;
                    $index->send();
                }
            }
        }

        return;
    }

    public function wantHelp($transfer)
    {
        $user = (new Users())->getOne([
            'where' => [
                'id = ' . $transfer->user_to
            ]
        ]);

        $index = new Index();
        $index->chat_id = $user->chat_id;
        $message = 'Уведомление!' . PHP_EOL . PHP_EOL . 'Пользователь %firstname% %lastname% %username% выразил намерение перевести вам на счет (карту) указанный ниже пожертвование:' . PHP_EOL . PHP_EOL . '%properties%' . PHP_EOL . 'Когда пользователь подтвердит перевод, мы вас уведомим.';
        $index->reply = (new Text($this->object, ['parent' => Transfers::TABLE_NAME, 'parent_id' => $transfer->id]))->getText($message);
        return $index->send();
    }

    public function helped($transfer)
    {
        $user = (new Users())->getOne([
            'where' => [
                'id = ' . $transfer->user_to
            ]
        ]);

        $req = (new UsersRequisitesModel())->getOne([
            'where' => [
                'id = ' . $transfer->req_id,
            ]
        ]);

        $transfers = (new DirTransfer())->getAll();
        $btns = [];
        foreach ($transfers as $one) {
            if ($one->text !== FALSE) {
                $btns[$one->code] = [
                    'text' => (new Text($this->object))->getText($one->text->text, Text::TEMPLATE['finish_button']),
                    'callback_data' => '/transfer_set?tag_id=' . $transfer->tag_id . '&status=' . $one->id . '&user_id=' . $transfer->user_from,
                ];
            }
        }

        $btns['did']['callback_data'] = $btns['did']['callback_data'] . '&amount_other=1';

        $chat_buttons = [];
        $chat_buttons['inline_keyboard'][] = [$btns['finished']];
        $chat_buttons['inline_keyboard'][] = [$btns['did']];
        $chat_buttons['inline_keyboard'][] = [$btns['must']];

        $index = new Index();
        $index->replyKeyboard = json_encode($chat_buttons);
        $index->chat_id = $user->chat_id;
        $message = 'Уведомление!' . PHP_EOL . PHP_EOL . 'Пользователь %firstname% %lastname% %username% перевел вам ' . $transfer->amount . ' %currency%' . PHP_EOL . 'На счет (карту) ' . $req->type_id->title . ': <b>' . $req->number . '</b>' . PHP_EOL . PHP_EOL . 'Пожалуйста проверьте счет (карту) и выберите вариант:';
        $index->reply = (new Text($this->object, ['currency' => $user->currency_id]))->getText($message);
        return $index->send();
    }
}




?>
