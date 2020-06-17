<?php
namespace app\controllers;

use \app\components\ErrorsComponent as Errors;
use \app\components\TextComponent as Text;
use \app\components\NotifyComponent as Notify;
use \app\models\TagsModel as Tags;
use \app\models\ObjectsPropertiesModel as ObjectsProperties;
use \app\models\dir\DirStatusModel as DirStatus;
use \app\utilities\DirPropertiesUtility as PropertiesUtility;
use \app\base\Controller;

/**
 *
 */
class StatusController extends Controller
{
    public function status($template = 'view')
    {
        $tag = (new Tags())->getOne([
            'where' => [
                'user_id = ' . $this->object->user->id,
            ]
        ]);

        if ($tag !== false) {
            $this->object->reply = (new Text($this->object, ['parent' => Tags::TABLE_NAME, 'parent_id' => $tag->id]))->getText($tag->color->text, Text::TEMPLATE[$template]);
        } else {
            $this->object->reply = (new Errors($this->object))->getText('not-tag');
        }

        $this->object->send();
    }

    public function status_change()
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
            $this->object->reply = 'Выберите новый статус:';
        } elseif ($this->object->params['parent'] != '' && ($properties = (new PropertiesUtility($this->object, (new DirStatus())))->questionResponse()) !== false) {
            $status = (new DirStatus())->getOne([
                'where' => [
                    'id = ' . $this->object->params['parent']
                ]
            ]);

            $st = 0;
            if ($status->code == 'green') {
                $prop = end($properties);
                if ($prop['value'] == 'yes') {
                    $st = 1;
                }
            }

            $tag = (new Tags())->insert([
                'user_id' => $this->object->user->id,
                'color_id' => $this->object->params['parent'],
                'status' => $st
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

            $this->status('new');
            return (new Notify($this->object))->statusChanged();
        }

        $this->object->send();
    }

    public function status_history()
    {
        $offset = 0;
        if (isset($this->object->params['offset'])) {
            $offset = $this->object->params['offset'];
        }

        $message = 'История смены ваших статусов:' . PHP_EOL . PHP_EOL;
        $tags = (new Tags())->getAll([
            'where' => [
                'user_id = ' . $this->object->user->id,
            ],
            'order' => [
                'created DESC'
            ],
            'offset' => $offset,
            'limit' => 11
        ], true);

        if ($tags === false) {
            $message = $message . 'Ваша история пуста.';
        } else {
            $i = 0;
            foreach ($tags as $one) {
                $i++;
                if ($i <= 10) {
                    $now = $one->del == 0 ? 'настоящее время' : date('d.m.Y', strtotime($one->updated));
                    $text = date('d.m.Y', strtotime($one->created)) . ' - ' . $now . ':' . PHP_EOL . $one->color->title . PHP_EOL . '%properties%' . PHP_EOL;
                    $message = $message . (new Text($this->object, ['parent' => Tags::TABLE_NAME, 'parent_id' => $one->id, 'child_view' => Text::TEMPLATE['history']]))->getText($text);
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
            } else {
                $message = $message . PHP_EOL . 'Конец истории.';
            }
        }
        $this->object->reply = $message;
        return $this->object->send();
    }
}
