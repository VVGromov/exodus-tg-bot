<?php namespace app\models\data\dir;

/**
 *
 */
class DirStatusData
{
    public $id;
    public $title;
    public $code;
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
        $this->title = isset($object['title']) ? $object['title'] : $this->title;
        $this->code = isset($object['code']) ? $object['code'] : $this->code;
        $this->del = isset($object['del']) ? $object['del'] : $this->del;
    }
}
