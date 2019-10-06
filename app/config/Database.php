<?php

namespace App\Config;

use mysqli;

class Database
{
    public function connect()
    {
        $connection = DB::getConnectParam();

        $db = new mysqli($connection['host'], $connection['username'], $connection['password'] ,$connection['database']);

        $db->set_charset("utf8");

        return $db;
    }
}