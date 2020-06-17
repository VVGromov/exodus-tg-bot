<?php namespace app\models;

use \app\base\Model;
use \app\models\data\ObjectTextData;
use \app\models\dir\DirStatusModel;

/**
 *
 */
class ObjectsTextsModel extends Model
{
    const TABLE_NAME = 'objects_texts';

    public $object_text;

    function __construct()
    {
        parent::__construct();
        $this->object_text = new ObjectTextData();
    }

    public function getAll($data = [])
    {
        $list = [];

        $query = $this->buildQuery(self::TABLE_NAME, $data);
        $result = $this->db->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $object = new ObjectTextData();
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
                $this->object_text->set($row);
                $list[] = $this->object_text;
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
        $this->object_text->id = intval($this->object_text->id);
        $this->object_text->object = $this->db->real_escape_string($this->object_text->object);
        $this->object_text->object_id = intval($this->object_text->object_id);
        $this->object_text->text = $this->db->real_escape_string($this->object_text->text);
        $this->object_text->status = intval($this->object_text->status);
        $this->object_text->created = $new == true ? date('Y-m-d H:i:s') : $this->object_text->created;
        $this->object_text->updated = date('Y-m-d H:i:s');
        $this->object_text->del = intval($this->object_text->del);
    }
}
