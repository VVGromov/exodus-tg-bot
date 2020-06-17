<?php
namespace app\models\data;

/**
 *
 */
class TransferData
{
    public $id;
    public $user_from;
    public $user_to;
    public $tag_id;
    public $amount;
    public $req_id;
    public $status;
    public $created;
    public $updated;
    public $del;

    public $dir;

    function __construct($object = [])
    {
        if (is_array($object) && !empty($object)) {
            self::set($object);
        }
    }

    public function set($object)
    {
        $this->id = isset($object['id']) ? $object['id'] : $this->id;
        $this->user_from = isset($object['user_from']) ? $object['user_from'] : $this->user_from;
        $this->user_to = isset($object['user_to']) ? $object['user_to'] : $this->user_to;
        $this->tag_id = isset($object['tag_id']) ? $object['tag_id'] : $this->tag_id;
        $this->amount = isset($object['amount']) ? $object['amount'] : $this->amount;
        $this->req_id = isset($object['req_id']) ? $object['req_id'] : $this->req_id;
        $this->status = isset($object['status']) ? $object['status'] : $this->status;
        $this->created = isset($object['created']) ? $object['created'] : $this->created;
        $this->updated = isset($object['updated']) ? $object['updated'] : $this->updated;
        $this->del = isset($object['del']) ? $object['del'] : $this->del;
    }
}
