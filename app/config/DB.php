<?php

namespace App\Config;

class DB
{
    public static function getConnectParam()
    {
        return [
            "driver" => "mysql",
            "host" => $_ENV["DB_HOST"],
            "database" => $_ENV["DB_NAME"],
            "username" => $_ENV["DB_USER"],
            "password" => $_ENV["DB_PASSWORD"],
            "charset" => "utf8",
            "collation" => "utf8_unicode_ci",
            "prefix" => "",
        ];
    }
}

