<?php

namespace App\Config;

class DB
{
    public static function getConnectParam()
    {
        return [
            "driver" => "mysql",
            "host" => $_ENV["DB_HOST"] ?? 'db',
            "database" => $_ENV["DB_NAME"] ?? 'app',
            "username" => $_ENV["DB_USER"] ?? 'app',
            "password" => $_ENV["DB_PASSWORD"] ?? 'secret',
            "charset" => "utf8",
            "collation" => "utf8_unicode_ci",
            "prefix" => "",
        ];
    }
}

