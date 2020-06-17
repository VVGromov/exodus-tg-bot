<?php namespace app\models;

use \app\base\Model;
use \app\models\UsersModel;
use \app\models\data\UserRequisiteData;
use \app\models\dir\DirRequisitesTypeModel;

/**
 *
 */
class UsersRequisitesModel extends Model
{
    const TABLE_NAME = 'users_requisites';

    public $requisite;

    function __construct()
    {
        parent::__construct();
        $this->requisite = new UserRequisiteData();
    }

    public function insert($object)
    {
        $this->requisite->set($object);
        self::convert(true);

        $query = 'INSERT INTO ' . self::TABLE_NAME . ' (user_id, type_id, number, created, updated) VALUES (' . $this->requisite->user_id . ',' . $this->requisite->type_id . ',"' . $this->requisite->number . '","' . $this->requisite->created . '","' . $this->requisite->updated . '")';
        $result = $this->db->query($query);

        $data = [
            'where' => [
                'id = ' . $this->db->insert_id,
            ]
        ];

        return self::getOne($data);
    }

    public function update($object)
    {
        $this->requisite->set($object);
        self::convert(true);
        return $this->db->query('UPDATE ' . self::TABLE_NAME . ' SET number = "' . $this->requisite->number . '" WHERE id=' . $this->requisite->id);
    }

    public function delete($id)
    {
        return $this->db->query('UPDATE ' . self::TABLE_NAME . ' SET del = 1 WHERE id=' . $id);
    }

    public function getAll($data = [])
    {
        $list = [];

        $query = $this->buildQuery(self::TABLE_NAME, $data);
        $result = $this->db->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $object = new UserRequisiteData();
                $object->set($row);
                $object->user_id = $object->user_id != 0 ? (new UsersModel())->getById($object->user_id) : 0;
                $object->type_id = $object->type_id != 0 ? (new DirRequisitesTypeModel())->getById($object->type_id) : 0;
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
                $this->requisite->set($row);
                $this->requisite->user_id = $this->requisite->user_id != 0 ? (new UsersModel())->getById($this->requisite->user_id) : 0;
                $this->requisite->type_id = $this->requisite->type_id != 0 ? (new DirRequisitesTypeModel())->getById($this->requisite->type_id) : 0;
                $list[] = $this->requisite;
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
        $this->requisite->id = intval($this->requisite->id);
        $this->requisite->user_id = intval($this->requisite->user_id);
        $this->requisite->type_id = intval($this->requisite->type_id);
        $this->requisite->number = $this->db->real_escape_string(trim($this->requisite->number));
        $this->requisite->created = $new == true ? date('Y-m-d H:i:s') : $this->requisite->created;
        $this->requisite->updated = date('Y-m-d H:i:s');
        $this->requisite->del = intval($this->requisite->del);
    }
}
