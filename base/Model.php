<?php
namespace app\base;

use \app\base\DataBaseConnect;

/**
 *
 */
class Model extends DataBaseConnect
{

    protected $db;

    function __construct()
    {
        $this->db = parent::init();
    }

    public function buildQuery($table_name, $data = null, $del = false)
    {
        if ($data === null) {
            $query = 'SELECT * FROM ' . $table_name . ' WHERE del=0';
        } else {
            $select_data = isset($data['select']) ? $data['select'] : null;
            $where_data = isset($data['where']) ? $data['where'] : null;
            $order_data = isset($data['order']) ? $data['order'] : null;
            $limit_data = isset($data['limit']) ? $data['limit'] : null;
            $offset_data = isset($data['offset']) ? $data['offset'] : null;

            $select = self::select($table_name, $select_data);
            $where = self::where($where_data, $del);
            $order = self::order($order_data);
            $limit = self::limit($limit_data);
            $offset = self::offset($offset_data);

            $query = $select . $where . $order . $limit . $offset;
        }

        return $query;
    }

    private function select($table_name, $data = null)
    {
        if ($data == null) {
            $select = 'SELECT * FROM ' . $table_name;
        } else {
            $select = 'SELECT ' . $data . ' FROM ' . $table_name;
        }

        return $select;
    }

    private function where($data = null, $del = false)
    {

        $start = $del === false ? 'del = 0' : 'del IN (0,1)';
        if ($data == null || empty($data)) {
            $where = ' WHERE ' . $start;
        } else {
            $data = implode(' AND ', $data);
            $where = ' WHERE ' . $start . ' AND ' . $data;
        }

        return $where;
    }

    private function order($data = null)
    {
        if ($data == null || empty($data)) {
            $order = null;
        } else {
            $data = implode(', ', $data);
            $order = ' ORDER BY ' . $data;
        }

        return $order;
    }

    private function limit($data = null)
    {
        if ($data == null) {
            $limit = null;
        } else {
            $limit = ' LIMIT ' . (int)$data;
        }

        return $limit;
    }

    private function offset($data = null)
    {
        if ($data == null) {
            $offset = null;
        } else {
            $offset = ' OFFSET ' . (int)$data;
        }

        return $offset;
    }

    public function startTransaction()
    {
        $this->db->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
        $this->db->autocommit(FALSE);
    }

    public function endTransaction($result)
    {
        if ($result === true) {
            $this->db->commit();
        } else {
            $this->db->rollback();
        }
    }
}
