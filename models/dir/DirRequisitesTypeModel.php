<?php namespace app\models\dir;

use \app\base\Model;
use \app\models\data\dir\DirRequisiteTypeData;

/**
 *
 */
class DirRequisitesTypeModel extends Model
{
    const TABLE_NAME = 'dir_requisites_type';

    public $dir_requisite_type;

    function __construct()
    {
        parent::__construct();
        $this->dir_requisite_type = new DirRequisiteTypeData();
    }

    public function getById($id)
    {
        $list = [];

        $this->dir_requisite_type->id = $id;
        self::convert();

        $query = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = ' . $this->dir_requisite_type->id . ' AND del = 0');
        if ($query->num_rows > 0) {
            while ($row = $query->fetch_assoc()) {
                $list[] = $row;
            }
        }

        if (empty($list)) {
            return false;
        } else {
            $this->dir_requisite_type->set($list[0]);
            return $this->dir_requisite_type;
        }
    }

    public function getAll($data = [])
    {
        $list = [];

        $query = $this->buildQuery(self::TABLE_NAME, $data);
        $result = $this->db->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $object = new DirRequisiteTypeData();
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

    private function convert($new = false)
    {
        $this->dir_requisite_type->id = intval($this->dir_requisite_type->id);
        $this->dir_requisite_type->title = $this->db->real_escape_string($this->dir_requisite_type->title);
        $this->dir_requisite_type->status = intval($this->dir_requisite_type->status);
        $this->dir_requisite_type->created = $new == true ? date('Y-m-d H:i:s') : $this->dir_requisite_type->created;
        $this->dir_requisite_type->updated = date('Y-m-d H:i:s');
        $this->dir_requisite_type->del = intval($this->dir_requisite_type->del);
    }
}
