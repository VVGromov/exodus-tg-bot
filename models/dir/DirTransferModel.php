<?php namespace app\models\dir;

use \app\base\Model;
use \app\models\data\dir\DirTransferData;
use \app\models\ObjectsTextsModel;

/**
 *
 */
class DirTransferModel extends Model
{
    const TABLE_NAME = 'dir_transfer';

    public $dir_transfer;

    function __construct()
    {
        parent::__construct();
        $this->dir_transfer = new DirTransferData();
    }

    public function getOne($data)
    {
        $data['limit'] = 1;
        $query = $this->buildQuery(self::TABLE_NAME, $data);
        $result = $this->db->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $this->dir_transfer->set($row);
                $this->dir_transfer->text = $this->dir_transfer->id != 0 ? (new ObjectsTextsModel())->getOne([
                      'where' => ['object_id = ' . $this->dir_transfer->id, 'object = "' . self::TABLE_NAME . '"']
                  ]) : 0;
                $list[] = $this->dir_transfer;
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
                $object = new DirTransferData();
                $object->set($row);
                $object->text = $object->id != 0 ? (new ObjectsTextsModel())->getOne([
                      'where' => ['object_id = ' . $object->id, 'object = "' . self::TABLE_NAME . '"']
                  ]) : 0;
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
        $this->dir_transfer->id = intval($this->dir_transfer->id);
        $this->dir_transfer->title = $this->db->real_escape_string($this->dir_transfer->title);
        $this->dir_transfer->code = $this->db->real_escape_string($this->dir_transfer->code);
        $this->dir_transfer->del = intval($this->dir_transfer->del);
    }
}
