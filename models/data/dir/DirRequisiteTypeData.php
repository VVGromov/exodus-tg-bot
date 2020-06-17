<?php namespace app\models\data\dir;

/**
 *
 */
class DirRequisiteTypeData
{
    public $id;
    public $title;
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
        $this->title = isset($object['title']) ? $object['title'] : $this->title;
        $this->status = isset($object['status']) ? $object['status'] : $this->status;
        $this->created = isset($object['created']) ? $object['created'] : $this->created;
        $this->updated = isset($object['updated']) ? $object['updated'] : $this->updated;
        $this->del = isset($object['del']) ? $object['del'] : $this->del;
    }
}
