<?php
namespace app\components;

use \app\config\Config;
use \app\models\UsersModel;
use \app\models\TagsModel;
use \app\models\TagsUsersModel;
use \app\models\UsersRelationsModel;
use \app\models\dir\DirTransferModel as DirTransfer;
use \app\components\TextComponent as Text;

/**
 *
 */
class ReferralComponent
{
    public $object;

    function __construct($object)
    {
        $this->object = $object;
    }

    public function run($hash)
    {
        $user_host = false;

        $result = [];

        $model = new TagsModel();
        $tag = $model->getOne([
            'where' => [
                'ref_hash = "' . $hash . '"'
            ]
        ]);

        if ($tag !== false && $tag->user_id != $this->object->user->id) {
            $rel = (new TagsUsersModel())->getOne([
                'where' => [
                    'tag_id = ' . $tag->id,
                    'user_id = ' . $this->object->user->id
                ]
            ]);

            if ($rel === false) {
                (new TagsUsersModel())->insert([
                    'tag_id' => $tag->id,
                    'user_id' => $this->object->user->id
                ]);
                $user_host = $tag->user_id;
            }

            $result['tag'] = $tag;
        }

        $model = new UsersModel();
        $user = $model->getOne([
            'where' => [
                'ref_hash = "' . $hash . '"'
            ]
        ]);
        if ($user !== false) {
            $user_host = $user->id;
        }

        if ($user_host !== false && $user_host != $this->object->user->id) {
            $rel = (new UsersRelationsModel())->getOne([
                'where' => [
                    'user_host = ' . $user_host,
                    'user_invited = ' . $this->object->user->id
                ]
            ]);

            if ($rel === false) {
                (new UsersRelationsModel())->insert([
                    'user_host' => $user_host,
                    'user_invited' => $this->object->user->id
                ]);
            }

            $result['user'] = $model->getById($user_host);
        }

        return $result;
    }

    public function setAnswer($array)
    {
        if (isset($array['tag'])) {
            $text = $array['tag']->color->text;
            $tag = $array['tag'];
        } elseif (isset($array['user'])) {
            $tag = (new TagsModel())->getOne([
                'where' => [
                    'user_id = ' . intval($array['user']->id),
                ]
            ]);
            $text = $tag->color->text;
        }

        if ($text !== false && ($user = (new UsersModel())->getById($tag->user_id)) !== false) {
            $this->object->reply = (new Text($this->object, ['parent' => TagsModel::TABLE_NAME, 'parent_id' => $tag->id, 'user' => $user]))->getText($tag->color->text, Text::TEMPLATE['notify_new']);

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
                $this->object->send();
            }
        }
    }

    public function getReferralLink($hash)
    {
        return Config::TG_LINK . Config::BOT_LINK . '?start=' . $hash;
    }

}

?>
