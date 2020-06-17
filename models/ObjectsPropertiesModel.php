<?php namespace app\models;

use \app\base\Model;
use \app\models\data\ObjectPropertyData;
use \app\models\dir\DirObjectsPropertiesModel;

/**
 *
 */
class ObjectsPropertiesModel extends Model
{
    const TABLE_NAME = 'objects_properties';

    public $object_property;

    function __construct()
    {
        parent::__construct();
        $this->object_property = new ObjectPropertyData();
    }

    public function insert($object)
    {
        $this->object_property->set($object);
        self::convert(true);

        $query = 'INSERT INTO ' . self::TABLE_NAME . ' (object, object_id, property_id, value, created, updated) VALUES ("' . $this->object_property->object . '",' . $this->object_property->object_id . ',' . $this->object_property->property_id . ',"' . $this->object_property->value . '","' . $this->object_property->created . '","' . $this->object_property->updated . '")';
        $result = $this->db->query($query);

        $data = [
            'where' => [
                'id = ' . $this->db->insert_id,
            ]
        ];

        return self::getOne($data);
    }

    public function getAll($data = [])
    {
        $list = [];

        $query = $this->buildQuery(self::TABLE_NAME, $data);
        $result = $this->db->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $object = new ObjectPropertyData();
                $object->set($row);
                $object->dir = $object->property_id != 0 ? (new DirObjectsPropertiesModel())->getOne([
                      'where' => ['id = ' . $object->property_id]
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

    public function getOne($data)
    {
        $data['limit'] = 1;
        $query = $this->buildQuery(self::TABLE_NAME, $data);
        $result = $this->db->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $this->object_property->set($row);
                $this->object_property->dir = $this->object_property->property_id != 0 ? (new DirObjectsPropertiesModel())->getOne([
                      'where' => ['id = ' . $this->object_property->property_id]
                  ]) : 0;
                $list[] = $this->object_property;
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
        $this->object_property->id = intval($this->object_property->id);
        $this->object_property->object = $this->db->real_escape_string($this->object_property->object);
        $this->object_property->object_id = intval($this->object_property->object_id);
        $this->object_property->property_id = intval($this->object_property->property_id);
        $this->object_property->value = $this->db->real_escape_string($this->object_property->value);
        $this->object_property->created = $new == true ? date('Y-m-d H:i:s') : $this->object_property->created;
        $this->object_property->updated = date('Y-m-d H:i:s');
        $this->object_property->del = intval($this->object_property->del);
    }
}
