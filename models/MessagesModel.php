<?php namespace app\models;

use \app\base\Model;
use \app\models\data\MessageData;

/**
 *
 */
class MessagesModel extends Model
{
    const TABLE_NAME = 'messages';

    public $message;

    function __construct()
    {
        parent::__construct();
        $this->message = new MessageData();
    }

    public function insert($object)
    {
        $this->message->set($object);
        self::convert(true);

        $query = 'INSERT INTO ' . self::TABLE_NAME . ' (message_id, content, created, updated, user_id, response) VALUES (' . $this->message->message_id . ',"' . $this->message->content . '","' .  $this->message->created . '","' . $this->message->updated . '",' . $this->message->user_id . ',"' . $this->message->response . '")';
        $result = $this->db->query($query);

        return $result;
    }

    private function convert($new = false)
    {
        $this->message->id = intval($this->message->id);
        $this->message->message_id = intval($this->message->message_id);
        $this->message->content = $this->message->content != null ? $this->db->real_escape_string($this->message->content) : $this->message->content;
        $this->message->created = $new == true ? date('Y-m-d H:i:s') : $this->message->created;
        $this->message->updated = date('Y-m-d H:i:s');
        $this->message->user_id = intval($this->message->user_id);
        $this->message->response = $this->message->response != null ? $this->db->real_escape_string($this->message->response) : $this->message->response;
    }
}
