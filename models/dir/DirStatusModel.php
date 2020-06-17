<?php namespace app\models\dir;

use \app\base\Model;
use \app\models\data\dir\DirStatusData;
use \app\models\ObjectsTextsModel;

/**
 *
 */
class DirStatusModel extends Model
{
    const TABLE_NAME = 'dir_status';

    public $dir_status;

    function __construct()
    {
        parent::__construct();
        $this->dir_status = new DirStatusData();
    }

    public function getOne($data)
    {
        $data['limit'] = 1;
        $query = $this->buildQuery(self::TABLE_NAME, $data);
        $result = $this->db->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $this->dir_status->set($row);
                $this->dir_status->text = $this->dir_status->id != 0 ? (new ObjectsTextsModel())->getOne([
                      'where' => ['object_id = ' . $this->dir_status->id, 'object = "' . self::TABLE_NAME . '"']
                  ])->text : 0;
                $list[] = $this->dir_status;
            }
        }

        if (empty($list)) {
            return false;
        } else {
            return $list[0];
        }
    }

    public function getAll($data = [])
    {
        $list = [];

        $query = $this->buildQuery(self::TABLE_NAME, $data);
        $result = $this->db->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $object = new DirStatusData();
                $object->set($row);
                $object->text = $object->id != 0 ? (new ObjectsTextsModel())->getOne([
                      'where' => ['object_id = ' . $object->id, 'object = "' . self::TABLE_NAME . '"']
                  ])->text : 0;
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
        $this->dir_status->id = intval($this->dir_status->id);
        $this->dir_status->title = $this->db->real_escape_string($this->dir_status->title);
        $this->dir_status->code = $this->db->real_escape_string($this->dir_status->code);
        $this->dir_status->del = intval($this->dir_status->del);
    }
}
