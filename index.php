<?php

use App\Config\Database;
use App\Excel;

ini_set('display_errors',1);
error_reporting(E_ALL);

define('ROOT', dirname(__FILE__));
require ROOT . '/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

define("EXCEL_MYSQL_DEBUG", false);

$excel_mysql_import_export = new Excel((new Database)->connect(), "./сотрудники.xls");

echo $excel_mysql_import_export->excel_to_mysql_iterate(array("excel_mysql_iterate")) ? "OK\n" : "FAIL\n";