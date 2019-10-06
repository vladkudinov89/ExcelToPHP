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

echo $excel_mysql_import_export->excel_to_mysql_by_index(
    "employees",
    0,
    array(
        "Employer",
        "Birthday",
        "Skills"
    ),
    2,
    false ,
    array(
        "Birthday" =>
            function ($value) {
                return PHPExcel_Shared_Date::ExcelToPHPObject($value)->format('Y-m-d');
            }
    ),
    1,
    array(
        "VARCHAR(50) NOT NULL",
        "DATETIME NOT NULL",
        "VARCHAR(100) NOT NULL"
    )
) ? "OK\n" : "FAIL\n";