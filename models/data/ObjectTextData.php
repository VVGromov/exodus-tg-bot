<?php
namespace app\models\data;

/**
 *
 */
class ObjectTextData
{
    public $id;
    public $object;
    public $object_id;
    public $text;
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
        $this->object = isset($object['object']) ? $object['object'] : $this->object;
        $this->object_id = isset($object['object_id']) ? $object['object_id'] : $this->object_id;
        $this->text = isset($object['text']) ? $object['text'] : $this->text;
        $this->status = isset($object['status']) ? $object['status'] : $this->status;
        $this->created = isset($object['created']) ? $object['created'] : $this->created;
        $this->updated = isset($object['updated']) ? $object['updated'] : $this->updated;
        $this->del = isset($object['del']) ? $object['del'] : $this->del;
    }
}
