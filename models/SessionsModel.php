<?php
namespace app\models;

use \app\base\DataBaseConnect;

/**
 *
 */
class SessionsModel extends DataBaseConnect
{
    const TABLE_NAME = 'sessions';

    private $db;

    public $id;
    public $user_id;
    public $content;
    public $created;
    public $updated;
    public $del;

    function __construct()
    {
        $this->db = parent::init();
    }

    public function insert($object)
    {
        self::set($object);
        self::convert(true);

        $query = 'INSERT INTO ' . self::TABLE_NAME . ' (user_id, content, created, updated) VALUES (' . $this->user_id . ',"' . $this->content . '","' . $this->created . '","' . $this->updated . '")';
        $result = $this->db->query($query);

        $this->id = $this->db->insert_id;

        if ($result == true) {
            return self::getById();
        } else {
            return false;
        }
    }

    public function update($object)
    {
        self::getById($this->id);

        $new = [];

        if (isset($object['content']) && $this->content != $object['content']) {
            $new[] = 'content';
        }

        if (isset($object['del']) && $this->del != $object['del']) {
            $new[] = 'del';
        }

        self::set($object);
        self::convert();

        $query = [];

        if (in_array('content', $new)) {
            $query[] = 'content = "' . $this->content . '"';
        }

        if (in_array('del', $new)) {
            $query[] = 'del = "' . $this->del . '"';
        }

        $query[] = 'updated = "' . $this->updated . '"';

        if (!empty($query)) {
            $query = implode(', ', $query);
        }

        if (!empty($query) || $query != null) {
            $result = $this->db->query('UPDATE ' . self::TABLE_NAME . ' SET ' . $query . ' WHERE id=' . $this->id);
            if ($result == true && $this->del != 1) {
                return self::getById();
            } else {
                return false;
            }
        } else {
            return self::getById();
        }
    }

    public function getById()
    {
        $list = [];

        self::convert();

        $date = date('Y-m-d H:i:s', time() - 3600);
        $query = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' WHERE updated >= "' . $date . '" AND id = ' . $this->id . ' AND del = 0');
        while ($row = $query->fetch_array()) {
            $list[] = $row;
        }

        if (empty($list)) {
            return false;
        } else {
            self::set($list[0]);
            return $this;
        }
    }

    public function getByKeyId()
    {
        $list = [];

        self::convert();

        $date = date('Y-m-d H:i:s', time() - 3600);
        $query = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' WHERE updated >= "' . $date . '" AND user_id = ' . $this->user_id . ' AND del = 0');

        if ($query->num_rows > 0) {
            while ($row = $query->fetch_array()) {
                $list[] = $row;
            }
        }

        if (empty($list)) {
            return false;
        } else {
            self::set($list[0]);
            return $this;
        }
    }

    public function getAll()
    {
        $list = [];

        $query = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' WHERE del = 0 ORDER BY discount');
        while ($row = $query->fetch_array()) {
            $object = new self();
            $object->set($row);
            $list[] = $object;
        }

        if (empty($list)) {
            return false;
        } else {
            return $list;
        }
    }

    private function convert($new = false)
    {
        $this->id = intval($this->id);
        $this->user_id = intval($this->user_id);
        $this->content = $this->content != null ? $this->db->real_escape_string($this->content) : $this->content;
        $this->created = $new == true ? date('Y-m-d H:i:s') : $this->created;
        $this->updated = date('Y-m-d H:i:s');
        $this->del = intval($this->del);
    }

    private function set($object)
    {
        $this->id = isset($object['id']) ? $object['id'] : $this->id;
        $this->user_id = isset($object['user_id']) ? $object['user_id'] : $this->user_id;
        $this->content = isset($object['content']) ? $object['content'] : $this->content;
        $this->created = isset($object['created']) ? $object['created'] : $this->created;
        $this->updated = isset($object['updated']) ? $object['updated'] : $this->updated;
        $this->del = isset($object['del']) ? $object['del'] : $this->del;
    }
}
