<?php
namespace app\models\data;

/**
 *
 */
class NotifyData
{
    public $id;
    public $user_id;
    public $text;
    public $buttons;
    public $type;
    public $every;
    public $status;
    public $created;
    public $updated;
    public $del;

    function __construct($object = [])
    {
        if (is_array($object) && !empty($object)) {
            self::set($object);
        }
    }

    public function set($object)
    {
        $this->id = isset($object['id']) ? $object['id'] : $this->id;
        $this->text = isset($object['text']) ? $object['text'] : $this->text;
        $this->buttons = isset($object['buttons']) ? $object['buttons'] : $this->buttons;
        $this->type = isset($object['type']) ? $object['type'] : $this->type;
        $this->every = isset($object['every']) ? $object['every'] : $this->every;
        $this->status = isset($object['status']) ? $object['status'] : $this->status;
        $this->created = isset($object['created']) ? $object['created'] : $this->created;
        $this->updated = isset($object['updated']) ? $object['updated'] : $this->updated;
        $this->del = isset($object['del']) ? $object['del'] : $this->del;
    }
}
