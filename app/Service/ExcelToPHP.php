<?php

namespace App\Service;

class ExcelToPHP
{
    private $execute;

    public function __construct($connection,string $filename)
    {
        $this->execute = new Excel($connection , $filename);
    }

    public function run()
    {
       echo $this->execute->excel_to_mysql(
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
                        return \PHPExcel_Shared_Date::ExcelToPHPObject($value)->format('Y-m-d');
                    }
            ),
            1,
            array(
                "VARCHAR(50) NOT NULL",
                "DATETIME NOT NULL",
                "VARCHAR(100) NOT NULL"
            )
        ) ? "Migrate OK" : "Migrate Fail";
    }
}