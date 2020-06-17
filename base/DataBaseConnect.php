<?php
namespace app\base;

use \app\config\DB;

class DatabaseConnect
{

    public function init()
    {
        $connect = new \mysqli(DB::DB_HOST, DB::DB_USER, DB::DB_PASSWORD, DB::DB_DATABASE);
        if ($connect->connect_error) {
            die("Connection failed: " . $connect->connect_error);
        }
        $connect->set_charset('utf8mb4');
        return $connect;
    }

}
