<?php namespace app\models;

use \app\base\Model;
use \app\models\data\CommandData;

/**
 *
 */
class CommandsModel extends Model
{
    const TABLE_NAME = 'commands';

    public $command;

    function __construct()
    {
        parent::__construct();
        $this->command = new CommandData();
    }

    public function getAll($data = [])
    {
        $list = [];

        $query = $this->buildQuery(self::TABLE_NAME, $data);
        $result = $this->db->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $object = new CommandData();
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
                $this->command->set($row);
                $list[] = $this->command;
            }
        }

        if (empty($list)) {
            return false;
        } else {
            return $list[0];
        }
    }

    public function searchByTitleAndCode($key)
    {
        $list = [];
        $this->command->title = $key;
        $this->command->code = $key;
        self::convert();

        $query = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' WHERE title = "' . $this->command->title . '" OR code = "' . $this->command->code . '"');
        while ($row = $query->fetch_array()) {
            $this->command->set($row);
            $list[] = $this->command;
        }

        if (empty($list)) {
            return false;
        } else {
            return $list[0];
        }
    }

    private function convert($new = false)
    {
        $this->command->id = intval($this->command->id);
        $this->command->title = $this->db->real_escape_string($this->command->title);
        $this->command->code = $this->db->real_escape_string($this->command->code);
        $this->command->description = $this->db->real_escape_string($this->command->description);
        $this->command->parent_id = intval($this->command->parent_id);
        $this->command->sort = intval($this->command->sort);
        $this->command->in_menu = intval($this->command->in_menu);
        $this->command->del = intval($this->command->del);
    }
}
