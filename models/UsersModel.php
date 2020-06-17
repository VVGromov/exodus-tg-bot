<?php
namespace app\models;

use \app\base\Model;
use \app\models\data\UserData;

/**
 *
 */
class UsersModel extends Model
{
    const TABLE_NAME = 'users';

    public $user;

    function __construct()
    {
        parent::__construct();
        $this->user = new UserData();
    }

    public function insert($object)
    {
        $this->user->set($object);
        self::convert(true);

        $query = 'INSERT INTO ' . self::TABLE_NAME . ' (user_id, username, first_name, language_code, last_name, chat_id, is_bot, created, updated, ref_hash) VALUES (' . $this->user->user_id . ',"' . $this->user->username . '","' .  $this->user->first_name . '","' .  $this->user->language_code . '","' . $this->user->last_name . '",' . $this->user->chat_id . ',"' . $this->user->is_bot . '","' . $this->user->created . '","' . $this->user->updated . '", "' . $this->user->ref_hash . '")';
        $result = $this->db->query($query);

        if ($result == true) {
            return self::getUserByUserId($this->user->user_id);
        } else {
            return false;
        }
    }

    public function update($object)
    {
        self::getUserByUserId($object['user_id']);

        $new = [];

        if (isset($object['username']) && $this->user->username != $object['username']) {
            $new[] = 'username';
        }

        if (isset($object['first_name']) && $this->user->first_name != $object['first_name']) {
            $new[] = 'first_name';
        }

        if (isset($object['last_name']) && $this->user->last_name != $object['last_name']) {
            $new[] = 'last_name';
        }

        if (isset($object['chat_id']) && $this->user->chat_id != $object['chat_id']) {
            $new[] = 'chat_id';
        }

        if (isset($object['language_code']) && $this->user->language_code != $object['language_code']) {
            $new[] = 'language_code';
        }

        if (isset($object['currency_id']) && $this->user->currency_id != $object['currency_id']) {
            $new[] = 'currency_id';
        }

        $this->user->set($object);
        self::convert();

        $query = [];

        if (in_array('username', $new)) {
            $query[] = 'username = "' . $this->user->username . '"';
        }

        if (in_array('first_name', $new)) {
            $query[] = 'first_name = "' . $this->user->first_name . '"';
        }

        if (in_array('last_name', $new)) {
            $query[] = 'last_name = "' . $this->user->last_name . '"';
        }

        if (in_array('chat_id', $new)) {
            $query[] = 'chat_id = ' . $this->user->chat_id;
        }

        if (in_array('language_code', $new)) {
            $query[] = 'language_code = "' . $this->user->language_code . '"';
        }

        if (in_array('currency_id', $new)) {
            $query[] = 'currency_id = ' . $this->user->currency_id;
        }

        $query[] = 'updated = "' . $this->user->updated . '"';

        if (!empty($query)) {
            $query = implode(', ', $query);
        }

        if (!empty($query) || $query != null) {
            $result = $this->db->query('UPDATE ' . self::TABLE_NAME . ' SET ' . $query . ' WHERE id=' . $this->user->id);
            if ($result == true) {
                return $this->user;
            } else {
                return false;
            }
        } else {
            return $this->user;
        }
    }

    public function getById($id)
    {
        $list = [];

        $this->user->id = $id;
        self::convert();

        $query = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = ' . $this->user->id . ' AND del = 0');
        while ($row = $query->fetch_assoc()) {
            $list[] = $row;
        }

        if (empty($list)) {
            return false;
        } else {
            $this->user->set($list[0]);
            return $this->user;
        }
    }

    public function getUserByUserId($id)
    {
        $this->user->user_id = $id;
        self::convert();
        $list = [];
        if ($this->user->user_id != 0) {
            $query = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' WHERE user_id = ' . $this->user->user_id . ' AND del = 0');
            while ($row = $query->fetch_assoc()) {
                $list[] = $row;
            }
        }

        if (empty($list)) {
            return false;
        } else {
            $this->user->set($list[0]);
            return $this->user;
        }
    }

    public function getByUsername($username)
    {
        $this->user->username = $username;
        self::convert();
        $list = [];
        $query = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' WHERE username = "' . $this->user->username . '" AND del = 0');
        while ($row = $query->fetch_assoc()) {
            $list[] = $row;
        }

        if (empty($list)) {
            return false;
        } else {
            $this->user->set($list[0]);
            return $this->user;
        }
    }

    public function getOne($data)
    {
        $data['limit'] = 1;
        $query = $this->buildQuery(self::TABLE_NAME, $data);
        $result = $this->db->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $this->user->set($row);
                $list[] = $this->user;
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

        if ($result !== false && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $object = new UserData();
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

    private function convert($new = false)
    {
        $this->user->id = intval($this->user->id);
        $this->user->user_id = intval($this->user->user_id);
        $this->user->username = $this->db->real_escape_string($this->user->username);
        $this->user->first_name = $this->db->real_escape_string($this->user->first_name);
        $this->user->last_name = $this->db->real_escape_string($this->user->last_name);
        $this->user->chat_id = intval($this->user->chat_id);
        $this->user->language_code = $this->db->real_escape_string($this->user->language_code);
        $this->user->is_bot = intval($this->user->is_bot);
        $this->user->created = $new == true ? date('Y-m-d H:i:s') : $this->user->created;
        $this->user->updated = date('Y-m-d H:i:s');
        $this->user->status = intval($this->user->status);
        $this->user->currency_id = intval($this->user->currency_id);
        $this->user->ref_hash = sha1('user-' . $this->user->user_id);
        $this->user->del = intval($this->user->del);
    }
}
