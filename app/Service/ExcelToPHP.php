<?php

namespace App\Service;

class ExcelToPHP
{
    private $execute;
    private $status = false;

    const SUCCESS = "Migrate SUCCESS";
    const FAIL = "Migrate FAIL";

    private $connection;
    private $filename;

    public function __construct($connection, string $filename)
    {
        $this->connection = $connection;
        $this->filename = $filename;
    }

    public function migrate()
    {
        if(!file_exists($this->filename)){

            $this->status = false;

            throw new \Exception('File doesn\'t exist');

        }

        if($this->connection->connect_error ){

            $this->status = false;

            throw new \Exception('Connection problem');

        }

        $this->execute = new Excel($this->connection, $this->filename);

       return $this->status = $this->execute->excel_to_mysql(
            "employees",
            0,
            array(
                "Employer",
                "Birthday",
                "Skills"
            ),
            2,
            false,
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
        );
    }

    public function getStatus(): string
    {
        if( $this->status == true){
            return self::SUCCESS;
        } else {
            return self::FAIL;
        }
    }
}