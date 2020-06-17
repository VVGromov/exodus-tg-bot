<?php namespace app\models\data;

/**
 *
 */
class UserRequisiteData
{
    public $id;
    public $user_id;
    public $type_id;
    public $number;
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
        $this->user_id = isset($object['user_id']) ? $object['user_id'] : $this->user_id;
        $this->type_id = isset($object['type_id']) ? $object['type_id'] : $this->type_id;
        $this->number = isset($object['number']) ? $object['number'] : $this->number;
        $this->status = isset($object['status']) ? $object['status'] : $this->status;
        $this->created = isset($object['created']) ? $object['created'] : $this->created;
        $this->updated = isset($object['updated']) ? $object['updated'] : $this->updated;
        $this->del = isset($object['del']) ? $object['del'] : $this->del;
    }
}
