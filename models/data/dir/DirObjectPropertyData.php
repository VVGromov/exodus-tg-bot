<?php namespace app\models\data\dir;

/**
 *
 */
class DirObjectPropertyData
{
    public $id;
    public $object;
    public $object_id;
    public $parent_id;
    public $type;
    public $sort;
    public $del;

    public $text;

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
        $this->parent_id = isset($object['parent_id']) ? $object['parent_id'] : $this->parent_id;
        $this->type = isset($object['type']) ? $object['type'] : $this->type;
        $this->sort = isset($object['sort']) ? $object['sort'] : $this->sort;
        $this->del = isset($object['del']) ? $object['del'] : $this->del;
    }
}
