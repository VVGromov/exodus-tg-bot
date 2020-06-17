<?php namespace app\components;

use \app\models\MessagesModel;

/**
 *
 */
class MessagesComponent
{
    public static function add($object)
    {
        $data = [
            'message_id' => $object->message_id,
            'content' => serialize($object->result),
            'user_id' => $object->user->id,
            'response' => '',
        ];

        $result = (new MessagesModel())->insert($data);

        return $result;
    }

}
