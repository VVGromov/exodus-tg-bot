<?php
namespace app\models\data;

/**
 *
 */
class ObjectPropertyData
{
    public $id;
    public $object;
    public $object_id;
    public $property_id;
    public $value;
    public $created;
    public $updated;
    public $del;

    public $dir;

    function __construct($object = [])
    {
        if (is_array($object) && !empty($object)) {
            self::set($object);
        }
    }

    public function set($object)
    {
        $this->id = isset($object['id']) ? $object['id'] : $this->id;
        $this->object = isset($object['object']) ? $object['object'] : $this->object;
        $this->object_id = isset($object['object_id']) ? $object['object_id'] : $this->object_id;
        $this->property_id = isset($object['property_id']) ? $object['property_id'] : $this->property_id;
        $this->value = isset($object['value']) ? $object['value'] : $this->value;
        $this->created = isset($object['created']) ? $object['created'] : $this->created;
        $this->updated = isset($object['updated']) ? $object['updated'] : $this->updated;
        $this->del = isset($object['del']) ? $object['del'] : $this->del;
    }
}
