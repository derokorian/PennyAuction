<?php

use Dero\Data\Column;

class ColumnTest extends PHPUnit_Framework_TestCase
{
    /** @var  Column */
    private $oCol;

    public function setUp()
    {
        $this->oCol = new Column('test', COL_TYPE_STRING);
    }

    public function testLength()
    {
        $this->oCol->Length(10);
        $this->assertEquals(10, $this->oCol->Length());
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testLengthFailure()
    {
        $this->oCol->Length('string is not valid');
    }

    public function testPrecision()
    {
        $this->oCol->Precision(4);
        $this->assertEquals(4, $this->oCol->Precision());
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testPrecisionFailure()
    {
        $this->oCol->Precision('string is not valid');
    }

    public function testDefault()
    {
        $this->oCol->DefaultValue(123);
        $this->assertEquals(123, $this->oCol->DefaultValue());
    }

    public function testNullable()
    {
        $this->oCol->IsNullable(true);
        $this->assertEquals(true, $this->oCol->IsNullable());
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testNullableFailure()
    {
        $this->oCol->IsNullable('string is not valid');
    }
}