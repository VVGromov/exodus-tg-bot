<?php namespace app\models\dir;

use \app\base\Model;
use \app\models\data\dir\DirErrorData;

/**
 *
 */
class DirErrorsModel extends Model
{
    const TABLE_NAME = 'dir_errors';

    public $dir_error;

    function __construct()
    {
        parent::__construct();
        $this->dir_error = new DirErrorData();
    }

    public function getAll($data = [])
    {
        $list = [];

        $query = $this->buildQuery(self::TABLE_NAME, $data);
        $result = $this->db->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $object = new DirErrorData();
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
                $this->dir_error->set($row);
                $list[] = $this->dir_error;
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
        $this->dir_error->id = intval($this->dir_error->id);
        $this->dir_error->title = $this->db->real_escape_string($this->dir_error->title);
        $this->dir_error->code = $this->db->real_escape_string($this->dir_error->code);
        $this->dir_error->number = intval($this->dir_error->number);
        $this->dir_error->del = intval($this->dir_error->del);
    }
}
