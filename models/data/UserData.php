<?php namespace app\models\data;

/**
 *
 */
class UserData
{
    public $id;
    public $user_id;
    public $username;
    public $first_name;
    public $last_name;
    public $chat_id;
    public $is_bot;
    public $created;
    public $updated;
    public $language_code;
    public $status;
    public $currency_id;
    public $ref_hash;
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
        $this->username = isset($object['username']) ? $object['username'] : $this->username;
        $this->first_name = isset($object['first_name']) ? $object['first_name'] : $this->first_name;
        $this->last_name = isset($object['last_name']) ? $object['last_name'] : $this->last_name;
        $this->chat_id = isset($object['chat_id']) ? $object['chat_id'] : $this->chat_id;
        $this->is_bot = isset($object['is_bot']) ? $object['is_bot'] : $this->is_bot;
        $this->created = isset($object['created']) ? $object['created'] : $this->created;
        $this->updated = isset($object['updated']) ? $object['updated'] : $this->updated;
        $this->language_code = isset($object['language_code']) ? $object['language_code'] : $this->language_code;
        $this->status = isset($object['status']) ? $object['status'] : $this->status;
        $this->currency_id = isset($object['currency_id']) ? $object['currency_id'] : $this->currency_id;
        $this->ref_hash = isset($object['ref_hash']) ? $object['ref_hash'] : $this->ref_hash;
        $this->del = isset($object['del']) ? $object['del'] : $this->del;
    }
}
