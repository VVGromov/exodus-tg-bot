<?php namespace app\models;

use \app\base\Model;
use \app\models\data\NotifyData;
use \app\models\UsersModel;

/**
 *
 */
class NotifyModel extends Model
{
    const TABLE_NAME = 'notify';

    public $notify;

    function __construct()
    {
        parent::__construct();
        $this->notify = new NotifyData();
    }

    public function insert($object)
    {
        $this->notify->set($object);
        self::convert(true);

        $query = 'INSERT INTO ' . self::TABLE_NAME . ' (user_id, text, buttons, type, every, created, updated) VALUES (' . $this->notify->user_id . ',"' . $this->notify->text . '","' . $this->notify->buttons . '",' . $this->notify->type . ',' . $this->notify->every . ',"' . $this->notify->created . '","' . $this->notify->updated . '")';
        $result = $this->db->query($query);
        $data = [
            'where' => [
                'id = ' . $this->db->insert_id,
            ]
        ];

        return self::getOne($data);
    }

    public function getAll($data = [], $del = false)
    {
        $list = [];

        $query = $this->buildQuery(self::TABLE_NAME, $data, $del);
        $result = $this->db->query($query);

        if ($result !== false && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $object = new NotifyData();
                $object->set($row);
                $object->user = $object->user_id != 0 ? (new UsersModel())->getOne(['where' => ['id = ' . $object->user_id]]) : 0;
                $list[] = $object;
            }
        }

        if (empty($list)) {
            return false;
        } else {
            return $list;
        }
    }

    public function getOne($data)
    {
        $data['limit'] = 1;

        $query = $this->buildQuery(self::TABLE_NAME, $data);
        $result = $this->db->query($query);

        if ($result !== false && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $this->notify->set($row);
                $this->notify->user = $this->notify->user_id != 0 ? (new UsersModel())->getOne(['where' => ['id = ' . $this->notify->user_id]]) : 0;
                $list[] = $this->notify;
            }
        }

        if (empty($list)) {
            return false;
        } else {
            return $list[0];
        }
    }

    public function delete($array)
    {
        $data = implode(' AND ', $array['where']);
        $result = $this->db->query('UPDATE ' . self::TABLE_NAME . ' SET del = 1 WHERE ' . $data);
        return $result;
    }

    private function convert($new = false)
    {
        $this->notify->id = intval($this->notify->id);
        $this->notify->user_id = intval($this->notify->user_id);
        $this->notify->text = $this->db->real_escape_string($this->notify->text);
        $this->notify->buttons = $this->db->real_escape_string($this->notify->buttons);
        $this->notify->type = intval($this->notify->type);
        $this->notify->every = intval($this->notify->every);
        $this->notify->created = $new == true ? date('Y-m-d H:i:s') : $this->notify->created;
        $this->notify->updated = date('Y-m-d H:i:s');
        $this->notify->status = intval($this->notify->status);
        $this->notify->del = intval($this->notify->del);
    }
}
