<?php
namespace app\utilities;

use \app\components\SessionsComponent as Session;
use \app\components\ErrorsComponent as Errors;
use \app\components\TextComponent as Text;
use \app\models\TagsModel as Tags;
use \app\models\ObjectsPropertiesModel as ObjectsProperties;
use \app\models\dir\DirObjectsPropertiesModel as DirObjectsProperties;

/**
 *
 */
class DirPropertiesUtility
{

    public $object;
    public $model;

    function __construct($object, $model, $data = null)
    {
        $this->object = $object;
        $this->model = $model;
        $this->data = $data;
    }

    public function questionResponse()
    {
        // Сессия
        $sessionComponent = new Session();
        $sessionComponent->user_id = $this->object->user->id;
        $session = $sessionComponent->get();

        if ((isset($session->content['params']['properties']) && $this->checkProperty($session->content['params']['properties'])) === false) {

            if (isset($this->object->params['parent'])) {
                $parent = $this->model->getOne([
                    'where' => [
                        'id = ' . intval($this->object->params['parent'])
                    ]
                ]);

                if ($parent === false) {
                    return (new Errors($this->object))->getError();
                }

                $object_id = $parent->id;
            } else {
                $object_id = 0;
            }

            $properties = (new DirObjectsProperties())->getAll([
                'where' => [
                    'object = "' . $this->model::TABLE_NAME . '"',
                    'object_id = ' . $object_id,
                    'parent_id = 0'
                ],
                'order' => [
                    'sort ASC',
                ]
            ]);

            if ($properties === false) {
                return (new Errors($this->object))->getError();
            }

            if ($session === false) {
                $sessionComponent->content = [
                    'command' => $this->object->command,
                    'params' => $this->object->params,
                ];

                foreach ($properties as $one) {
                    $sessionComponent->content['params']['properties'][] = [
                        'id' => $one->id,
                        'text' => (new Text($this->object, $this->data))->getText($one->text, 'query'),
                        'type' => $one->type,
                        'sort' => $one->sort,
                        'value' => false,
                        'valid' => false
                    ];
                }
                $sessionComponent->content['params']['properties'][0]['value'] = 0;
                $session = $sessionComponent->set();
                $this->object->reply = $session->content['params']['properties'][0]['text'];

                $this->getButtons($session->content['params']['properties'][0]['id']);
            } else {

                foreach ($session->content['params']['properties'] as $key => $item) {
                    if ($item['value'] === 0) {
                        if ($item['type'] === DirObjectsProperties::TYPE_BUTTONS || $item['type'] === DirObjectsProperties::TYPE_BUTTONS_OBJECTS) {
                            $item['value'] = $this->object->callback_data;
                        } elseif ($item['type'] === DirObjectsProperties::TYPE_DATE) {
                            $item['value'] = $this->getDate($this->object->message);
                        } else {
                            $item['value'] = $this->object->message;
                        }
                        $result = $this->validProperty($item['value'], $item['type']);
                        if ($result === false) {
                            $this->object->reply = '<b>' . (new Errors($this->object))->getText('not-valid') . '</b>' . PHP_EOL . PHP_EOL . $item['text'];
                            $this->getButtons($item['id']);
                            $item['valid'] = false;
                            break;
                        } else {
                            $item['valid'] = true;
                        }
                    }

                    if ($item['value'] === false) {
                        $item['text'] = $this->checkOnPrev($item['text'], $session->content['params']['properties'][($key-1)]);
                        $session->content['params']['properties'][$key]['text'] = $item['text'];

                        $session->content['params']['properties'][$key]['value'] = 0;
                        $this->object->reply = $item['text'];
                        $this->getButtons($item['id']);
                        break;
                    }

                    if ($item['value'] !== false && $item['valid'] == true) {
                        $session->content['params']['properties'][$key] = $item;
                    }
                }
            }

            // Обновление сессии
            $sessionComponent->id = $session->id;
            $sessionComponent->content = $session->content;
            $sessionComponent->update();
        }

        if ($session !== false && $this->checkProperty($session->content['params']['properties'])) {
            $sessionComponent->id = $session->id;
            $sessionComponent->delete();
            return $session->content['params']['properties'];
        } else {
            return false;
        }
    }

    public function saveProperties($object_id, $table_name, $properties)
    {
        $this->model->startTransaction();

        $result = 0;

        foreach ($properties as $one) {
            $prop = $this->model->insert([
                'object' => $table_name,
                'object_id' => $object_id,
                'property_id' => $one['id'],
                'value' => $one['value']
            ]);
            if ($prop !== false) {
                $result++;
            }
        }

        $this->model->endTransaction($result === count($properties));

        return $result === count($properties);
    }

    public function getButtons($id)
    {
        $prop = (new DirObjectsProperties())->getOne([
            'where' => [
                'id = ' . $id
            ]
        ]);

        if ($prop !== false && $prop->type === DirObjectsProperties::TYPE_BUTTONS) {
            $childs = (new DirObjectsProperties())->getAll([
                'where' => [
                    'parent_id = ' . $prop->id
                ],
                'order' => [
                    'sort ASC',
                ]
            ]);

            foreach ($childs as $one) {
                $buttons[] = [
                    'text' => $one->text,
                    'callback_data' => $one->type,
                ];
            }

            $chat_buttons = [];
            $chat_buttons['inline_keyboard'][] = $buttons;
            $this->object->replyKeyboard = json_encode($chat_buttons);

            return true;
        } elseif ($prop !== false && $prop->type === DirObjectsProperties::TYPE_BUTTONS_OBJECTS && isset($this->data[DirObjectsProperties::TYPE_BUTTONS_OBJECTS])) {
            foreach ($this->data[DirObjectsProperties::TYPE_BUTTONS_OBJECTS] as $one) {
                $buttons[] = [
                    'text' => $one->type_id->title,
                    'callback_data' => $one->id
                ];
            }

            $chat_buttons = [];
            $chat_buttons['inline_keyboard'][] = $buttons;
            $this->object->replyKeyboard = json_encode($chat_buttons);

            return true;
        } else {
            return false;
        }
    }

    public function validProperty($value, $type)
    {
        $result = [];

        if ($value != '') {
            if ($type === DirObjectsProperties::TYPE_INTEGER) {
                if (is_numeric($value)) {
                    $result[] = 1;
                }
            }

            if ($type === DirObjectsProperties::TYPE_MONEY) {
                if (is_numeric($value) || is_float($value)) {
                    $result[] = 1;
                }
            }

            if ($type === DirObjectsProperties::TYPE_DATE) {
                $now = (new \DateTime())->createFromFormat('d.m.Y', date('d.m.Y'));
                $d = (new \DateTime())->createFromFormat('d.m.Y', $value);
                if ($d && $d->format('d.m.Y') == $value && $d > $now) {
                    $result[] = 1;
                }
            }

            if ($type === DirObjectsProperties::TYPE_STRING) {
                if (is_string('"' . $value . '"')) {
                    $result[] = 1;
                }
            }

            if ($type === DirObjectsProperties::TYPE_BUTTONS || $type === DirObjectsProperties::TYPE_BUTTONS_OBJECTS) {
                $result[] = 1;
            }
        }

        if (!empty($result)) {
            return true;
        } else {
            return false;
        }
    }

    public function checkProperty($array = '')
    {
        $result = 0;

        if ($array != '' && !empty($array)) {
            foreach ($array as $key => $item) {
                if ($item['value'] !== 0 && $item['value'] !== false) {
                    $result++;
                }
            }
        } else {
            return false;
        }

        if ($result == count($array)) {
            return true;
        } else {
            return false;
        }
    }

    public function getDate($value)
    {
        $now = (new \DateTime())->createFromFormat('d.m.Y', date('d.m.Y'));
        $d = (new \DateTime())->createFromFormat('d.m.Y', $value);
        if ($d && $d->format('d.m.Y') == $value && $d > $now) {
            return $value;
        } elseif (is_numeric($value)) {
            $now->add(new \DateInterval('P' . $value . 'D'));
            if ($now > (new \DateTime())->createFromFormat('d.m.Y', date('d.m.Y'))) {
                return $now->format('d.m.Y');
            }
        }
    }

    public function checkOnPrev($item, $prev)
    {
        if (strpos($item, Text::TYPES['prev']['code']) !== false) {
            $child = (new DirObjectsProperties())->getOne([
                'where' => [
                    'parent_id = ' . $prev['id'],
                    'type = "' . $prev['value'] . '"'
                ]
            ]);
            $item = str_replace(Text::TYPES['prev']['code'], mb_strtolower($child->text), $item);
        } elseif (strpos($item, Text::TYPES['prev_object']['code']) !== false && isset($this->data[DirObjectsProperties::TYPE_BUTTONS_OBJECTS])) {
            foreach ($this->data[DirObjectsProperties::TYPE_BUTTONS_OBJECTS] as $one) {
                if ($one->id == $prev['value']) {
                    $selected = $one;
                    break;
                }
            }
            $item = str_replace(Text::TYPES['prev_object']['code'], $selected->type_id->title . ': ' . $selected->number, $item);
        }

        return $item;
    }
}

?>
