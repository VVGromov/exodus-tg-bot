<?php namespace app\models;

use \app\base\Model;
use \app\models\data\TransferData;
use \app\models\dir\DirTransferModel;

/**
 *
 */
class TransfersModel extends Model
{
    const TABLE_NAME = 'transfers';

    public $transfer;

    function __construct()
    {
        parent::__construct();
        $this->transfer = new TransferData();
    }

    public function insert($object)
    {
        $this->transfer->set($object);
        self::convert(true);

        $query = 'INSERT INTO ' . self::TABLE_NAME . ' (user_from, user_to, tag_id, status, created, updated) VALUES (' . $this->transfer->user_from . ',' . $this->transfer->user_to . ',' . $this->transfer->tag_id . ',' . $this->transfer->status . ',"' . $this->transfer->created . '","' . $this->transfer->updated . '")';
        $result = $this->db->query($query);

        $data = [
            'where' => [
                'id = ' . $this->db->insert_id,
            ]
        ];

        return self::getOne($data);
    }

    public function update($object)
    {
        self::getOne([
            'where' => ['id = ' . $object['id']]
        ]);

        $new = [];

        if (isset($object['status']) && $this->transfer->status != $object['status']) {
            $new[] = 'status';
        }

        if (isset($object['req_id']) && $this->transfer->req_id != $object['req_id']) {
            $new[] = 'req_id';
        }

        if (isset($object['amount']) && $this->transfer->amount != $object['amount']) {
            $new[] = 'amount';
        }

        $this->transfer->set($object);
        self::convert();

        $query = [];

        if (in_array('status', $new)) {
            $query[] = 'status = ' . $this->transfer->status;
        }

        if (in_array('req_id', $new)) {
            $query[] = 'req_id = ' . $this->transfer->req_id;
        }

        if (in_array('amount', $new)) {
            $query[] = 'amount = ' . $this->transfer->amount;
        }

        $query[] = 'updated = "' . $this->transfer->updated . '"';

        if (!empty($query)) {
            $query = implode(', ', $query);
        }

        if (!empty($query) || $query != null) {
            $result = $this->db->query('UPDATE ' . self::TABLE_NAME . ' SET ' . $query . ' WHERE id=' . $this->transfer->id);
            if ($result == true) {
                return $this->getOne([
                    'where' => ['id = ' . $this->transfer->id]
                ]);
            } else {
                return false;
            }
        } else {
            return $this->transfer;
        }
    }

    public function getAll($data = [])
    {
        $list = [];

        $query = $this->buildQuery(self::TABLE_NAME, $data);
        $result = $this->db->query($query);

        if ($result !== false && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $object = new TransferData();
                $object->set($row);
                $object->dir = $object->status != 0 ? (new DirTransferModel())->getOne(['where' => ['id = ' . $object->status]]) : 0;
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
        if (!isset($data['order'])) {
            $data['order'] = [
                'id DESC'
            ];
        }

        $data['limit'] = 1;
        $query = $this->buildQuery(self::TABLE_NAME, $data);
        $result = $this->db->query($query);
        if ($result !== false && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $this->transfer->set($row);
                $this->transfer->dir = $this->transfer->status != 0 ? (new DirTransferModel())->getOne(['where' => ['id = ' . $this->transfer->status]]) : 0;
                $list[] = $this->transfer;
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
        $this->transfer->id = intval($this->transfer->id);
        $this->transfer->user_from = intval($this->transfer->user_from);
        $this->transfer->user_to = intval($this->transfer->user_to);
        $this->transfer->tag_id = intval($this->transfer->tag_id);
        $this->transfer->amount = floatval($this->transfer->amount);
        $this->transfer->req_id = intval($this->transfer->req_id);
        $this->transfer->status = intval($this->transfer->status);
        $this->transfer->created = $new == true ? date('Y-m-d H:i:s') : $this->transfer->created;
        $this->transfer->updated = date('Y-m-d H:i:s');
        $this->transfer->del = intval($this->transfer->del);
    }
}
