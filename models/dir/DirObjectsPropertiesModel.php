<?php namespace app\models\dir;

use \app\base\Model;
use \app\models\data\dir\DirObjectPropertyData;
use \app\models\ObjectsTextsModel;

/**
 *
 */
class DirObjectsPropertiesModel extends Model
{
    const TABLE_NAME = 'dir_objects_properties';

    const TYPE_BUTTONS = 'buttons';
    const TYPE_BUTTONS_OBJECTS = 'buttons-objects';
    const TYPE_INTEGER = 'integer';
    const TYPE_DATE = 'date';
    const TYPE_STRING = 'string';
    const TYPE_MONEY = 'money';

    public $dir_object_property;

    function __construct()
    {
        parent::__construct();
        $this->dir_object_property = new DirObjectPropertyData();
    }

    public function getOne($data)
    {
        $list = [];

        $data['limit'] = 1;
        $query = $this->buildQuery(self::TABLE_NAME, $data);
        $result = $this->db->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $this->dir_object_property->set($row);
                $this->dir_object_property->text = $this->dir_object_property->id != 0 ? (new ObjectsTextsModel())->getOne([
                      'where' => ['object_id = ' . $this->dir_object_property->id, 'object = "' . self::TABLE_NAME . '"']
                  ])->text : 0;
                $list[] = $this->dir_object_property;
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
                $object = new DirObjectPropertyData();
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
        $this->dir_object_property->id = intval($this->dir_object_property->id);
        $this->dir_object_property->object = $this->db->real_escape_string($this->dir_object_property->object);
        $this->dir_object_property->object_id = intval($this->dir_object_property->object_id);
        $this->dir_object_property->parent_id = intval($this->dir_object_property->parent_id);
        $this->dir_object_property->type = $this->db->real_escape_string($this->dir_object_property->type);
        $this->dir_object_property->sort = intval($this->dir_object_property->sort);
        $this->dir_object_property->del = intval($this->dir_object_property->del);
    }
}
