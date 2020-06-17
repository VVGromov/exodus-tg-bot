<?php namespace app\models\dir;

use \app\base\Model;
use \app\models\data\dir\DirCurrencyData;

/**
 *
 */
class DirCurrencyModel extends Model
{
    const TABLE_NAME = 'dir_currency';

    public $dir_currency;

    function __construct()
    {
        parent::__construct();
        $this->dir_currency = new DirCurrencyData();
    }

    public function getAll($data = [])
    {
        $list = [];

        $query = $this->buildQuery(self::TABLE_NAME, $data);
        $result = $this->db->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $object = new DirCurrencyData();
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
                $this->dir_currency->set($row);
                $list[] = $this->dir_currency;
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
        $this->dir_currency->id = intval($this->dir_currency->id);
        $this->dir_currency->title = $this->db->real_escape_string($this->dir_currency->title);
        $this->dir_currency->code = $this->db->real_escape_string($this->dir_currency->code);
        $this->dir_currency->del = intval($this->dir_currency->del);
    }
}
