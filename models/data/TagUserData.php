<?php
namespace app\models\data;

/**
 *
 */
class TagUserData
{
    public $id;
    public $tag_id;
    public $user_id;
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
        $this->tag_id = isset($object['tag_id']) ? $object['tag_id'] : $this->tag_id;
        $this->user_id = isset($object['user_id']) ? $object['user_id'] : $this->user_id;
        $this->created = isset($object['created']) ? $object['created'] : $this->created;
        $this->updated = isset($object['updated']) ? $object['updated'] : $this->updated;
        $this->del = isset($object['del']) ? $object['del'] : $this->del;
    }
}
