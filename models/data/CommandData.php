<?php namespace app\models\data;

/**
 *
 */
class CommandData
{

    public $id;
    public $title;
    public $code;
    public $description;
    public $parent_id;
    public $sort;
    public $in_menu;
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
        $this->title = isset($object['title']) ? $object['title'] : $this->title;
        $this->code = isset($object['code']) ? $object['code'] : $this->code;
        $this->description = isset($object['description']) ? $object['description'] : $this->description;
        $this->parent_id = isset($object['parent_id']) ? $object['parent_id'] : $this->parent_id;
        $this->sort = isset($object['sort']) ? $object['sort'] : $this->sort;
        $this->in_menu = isset($object['in_menu']) ? $object['in_menu'] : $this->in_menu;
        $this->del = isset($object['del']) ? $object['del'] : $this->del;
    }
}
