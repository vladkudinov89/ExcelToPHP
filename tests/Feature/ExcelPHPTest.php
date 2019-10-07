<?php

use App\Config\Database;
use App\Service\ExcelToPHP;
use PHPUnit\Framework\TestCase;

class ExcelPHPTest extends TestCase
{
    /** @test */
    public function success_migrate()
    {
        $migrate = $this->getMockBuilder(ExcelToPHP::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([
                (new Database())->connect(),
                "./сотрудники.xls"
            ])
            ->getMock();

        $migrate->method('migrate')
            ->willReturn(true);

        self::assertEquals(true , $migrate->migrate());
    }

    /** @test */
    public function success_status()
    {
        $status = $this->getMockBuilder(ExcelToPHP::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

        $status
            ->method('getStatus')
            ->willReturn('Migrate SUCCESS');

        self::assertEquals('Migrate SUCCESS' , $status->getStatus());
    }

    /** @test */
    public function fail_status()
    {
        $fail = $this->getMockBuilder(ExcelToPHP::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([
               '',
                "./сотрудники.xls"
            ])
            ->getMock();

        $fail->migrate();

        $fail
            ->method('getStatus')
            ->willReturn('Migrate FAIL');

        self::assertEquals('Migrate FAIL' , $fail->getStatus());
    }

    /** @test
     *
     * @expectedException Exception
     */
    public function fail_file_exception()
    {
        $stub = $this->getMockBuilder(ExcelToPHP::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([
                (new Database())->connect(),
                "./fail.xls"
            ])
            ->getMock();

        $stub->method('migrate')
            ->will($this->throwException(new Exception('File doesn\'t exist')));

        $stub->migrate();

        $stub
            ->method('getStatus')
            ->willReturn('Migrate FAIL');

        self::assertEquals('Migrate FAIL' , $stub->getStatus());
    }

}
