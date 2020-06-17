<?php namespace app\models;

use \app\base\Model;
use \app\models\data\TagUserData;

/**
 *
 */
 class TagsUsersModel extends Model
 {
     const TABLE_NAME = 'tags_users';

    public $tag_user;

    function __construct()
    {
        parent::__construct();
        $this->tag_user = new TagUserData();
    }

    public function insert($object)
    {
        $this->tag_user->set($object);
        self::convert(true);

        $query = 'INSERT INTO ' . self::TABLE_NAME . ' (tag_id, user_id, created, updated) VALUES (' . $this->tag_user->tag_id . ',' . $this->tag_user->user_id . ',"' .  $this->tag_user->created . '","' . $this->tag_user->updated . '")';
        $result = $this->db->query($query);

        return $result;
    }

    public function getAll($data = [])
    {
        $list = [];

        $query = $this->buildQuery(self::TABLE_NAME, $data);
        $result = $this->db->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $object = new TagUserData();
                $object->set($row);
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

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $this->tag_user->set($row);
                $list[] = $this->tag_user;
            }
        }

        if (empty($list)) {
            return false;
        } else {
            return $list[0];
        }
    }

    private function convert($new = false)
    {
        $this->tag_user->id = intval($this->tag_user->id);
        $this->tag_user->tag_id = intval($this->tag_user->tag_id);
        $this->tag_user->user_id = intval($this->tag_user->user_id);
        $this->tag_user->created = $new == true ? date('Y-m-d H:i:s') : $this->tag_user->created;
        $this->tag_user->updated = date('Y-m-d H:i:s');
        $this->tag_user->del = intval($this->tag_user->del);
    }
}
