<?php

use App\Config\Database;
use App\Service\ExcelToPHP;

ini_set('display_errors',1);
error_reporting(E_ALL);

define('ROOT', dirname(__FILE__));
require ROOT . '/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$migrate = new ExcelToPHP((new Database)->connect(), "./сотрудники.xls");

$migrate->migrate();

echo $migrate->getStatus();