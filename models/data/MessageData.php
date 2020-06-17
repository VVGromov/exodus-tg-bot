<?php namespace app\models\data;

/**
 *
 */
class MessageData
{
    public $id;
    public $message_id;
    public $content;
    public $created;
    public $updated;
    public $user_id;
    public $response;

    function __construct($object = [])
    {
        if (is_array($object) && !empty($object)) {
            self::set($object);
        }
    }

    public function set($object)
    {
        $this->id = isset($object['id']) ? $object['id'] : $this->id;
        $this->message_id = isset($object['message_id']) ? $object['message_id'] : $this->message_id;
        $this->content = isset($object['content']) ? $object['content'] : $this->content;
        $this->created = isset($object['created']) ? $object['created'] : $this->created;
        $this->updated = isset($object['updated']) ? $object['updated'] : $this->updated;
        $this->user_id = isset($object['user_id']) ? $object['user_id'] : $this->user_id;
        $this->response = isset($object['response']) ? $object['response'] : $this->response;
    }
}
