<?php namespace app\models;

use \app\base\Model;
use \app\models\data\UserRelationData;

/**
 *
 */
 class UsersRelationsModel extends Model
 {
     const TABLE_NAME = 'users_relations';

    public $user_relation;

    function __construct()
    {
        parent::__construct();
        $this->user_relation = new UserRelationData();
    }

    public function insert($object)
    {
        $this->user_relation->set($object);
        self::convert(true);

        $query = 'INSERT INTO ' . self::TABLE_NAME . ' (user_host, user_invited, created, updated) VALUES (' . $this->user_relation->user_host . ',' . $this->user_relation->user_invited . ',"' .  $this->user_relation->created . '","' . $this->user_relation->updated . '")';
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
                $object = new UserRelationData();
                $object->set($row);
                $object->user_host = $object->user_host != 0 ? (new UsersModel())->getById($object->user_host) : 0;
                $object->user_invited = $object->user_invited != 0 ? (new UsersModel())->getById($object->user_invited) : 0;
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
                $this->user_relation->set($row);
                $this->user_relation->user_host = $this->user_relation->user_host != 0 ? (new UsersModel())->getById($this->user_relation->user_host) : 0;
                $this->user_relation->user_invited = $this->user_relation->user_invited != 0 ? (new UsersModel())->getById($this->user_relation->user_invited) : 0;
                $list[] = $this->user_relation;
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
        $this->user_relation->id = intval($this->user_relation->id);
        $this->user_relation->user_host = intval($this->user_relation->user_host);
        $this->user_relation->user_invited = intval($this->user_relation->user_invited);
        $this->user_relation->created = $new == true ? date('Y-m-d H:i:s') : $this->user_relation->created;
        $this->user_relation->updated = date('Y-m-d H:i:s');
        $this->user_relation->del = intval($this->user_relation->del);
    }
}
