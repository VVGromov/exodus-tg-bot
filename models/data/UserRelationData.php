<?php
namespace app\models\data;

/**
 *
 */
class UserRelationData
{
    public $id;
    public $user_host;
    public $user_invited;
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
        $this->user_host = isset($object['user_host']) ? $object['user_host'] : $this->user_host;
        $this->user_invited = isset($object['user_invited']) ? $object['user_invited'] : $this->user_invited;
        $this->created = isset($object['created']) ? $object['created'] : $this->created;
        $this->updated = isset($object['updated']) ? $object['updated'] : $this->updated;
        $this->del = isset($object['del']) ? $object['del'] : $this->del;
    }
}
