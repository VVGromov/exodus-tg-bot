<?php namespace app\models;

use \app\base\Model;
use \app\models\data\TagData;
use \app\models\dir\DirStatusModel;
use \app\models\UsersModel;

/**
 *
 */
class TagsModel extends Model
{
    const TABLE_NAME = 'tags';

    public $tag;

    function __construct()
    {
        parent::__construct();
        $this->tag = new TagData();
    }

    public function insert($object)
    {
        $this->tag->set($object);
        self::convert(true);

        $query = 'INSERT INTO ' . self::TABLE_NAME . ' (user_id, color_id, status, created, updated, ref_hash) VALUES (' . $this->tag->user_id . ',' . $this->tag->color_id . ',' . $this->tag->status . ',"' . $this->tag->created . '","' . $this->tag->updated . '","' . $this->tag->ref_hash . '")';
        $result = $this->db->query($query);
        $data = [
            'where' => [
                'id = ' . $this->db->insert_id,
            ]
        ];

        return self::getOne($data);
    }

    public function getAll($data = [], $del = false)
    {
        $list = [];

        $query = $this->buildQuery(self::TABLE_NAME, $data, $del);
        $result = $this->db->query($query);

        if ($result !== false && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $object = new TagData();
                $object->set($row);
                $object->color = $object->color_id != 0 ? (new DirStatusModel())->getOne(['where' => ['id = ' . $object->color_id]]) : 0;
                $object->user = $object->user_id != 0 ? (new UsersModel())->getOne(['where' => ['id = ' . $object->user_id]]) : 0;
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
                $this->tag->set($row);
                $this->tag->color = $this->tag->color_id != 0 ? (new DirStatusModel())->getOne(['where' => ['id = ' . $this->tag->color_id]]) : 0;
                $this->tag->user = $this->tag->user_id != 0 ? (new UsersModel())->getOne(['where' => ['id = ' . $this->tag->user_id]]) : 0;
                $list[] = $this->tag;
            }
        }

        if (empty($list)) {
            return false;
        } else {
            return $list[0];
        }
    }

    public function delete($array)
    {
        $data = implode(' AND ', $array['where']);
        $result = $this->db->query('UPDATE ' . self::TABLE_NAME . ' SET del = 1 WHERE ' . $data);
        return $result;
    }

    private function convert($new = false)
    {
        $this->tag->id = intval($this->tag->id);
        $this->tag->user_id = intval($this->tag->user_id);
        $this->tag->color_id = intval($this->tag->color_id);
        $this->tag->type = intval($this->tag->type);
        $this->tag->created = $new == true ? date('Y-m-d H:i:s') : $this->tag->created;
        $this->tag->updated = date('Y-m-d H:i:s');
        $this->tag->status = intval($this->tag->status);
        $this->tag->ref_hash = sha1('tag-' . $this->tag->user_id);
        $this->tag->del = intval($this->tag->del);
    }
}
