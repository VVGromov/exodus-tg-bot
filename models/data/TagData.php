<?php
namespace app\models\data;

/**
 *
 */
class TagData
{
    public $id;
    public $user_id;
    public $color_id;
    public $type;
    public $created;
    public $updated;
    public $status;
    public $ref_hash;
    public $del;

    public $color;
    public $user;

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
        $this->color_id = isset($object['color_id']) ? $object['color_id'] : $this->color_id;
        $this->type = isset($object['type']) ? $object['type'] : $this->type;
        $this->created = isset($object['created']) ? $object['created'] : $this->created;
        $this->updated = isset($object['updated']) ? $object['updated'] : $this->updated;
        $this->status = isset($object['status']) ? $object['status'] : $this->status;
        $this->ref_hash = isset($object['ref_hash']) ? $object['ref_hash'] : $this->ref_hash;
        $this->del = isset($object['del']) ? $object['del'] : $this->del;
    }
}
