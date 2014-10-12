<?php

use Dero\Data\Parameter;

class ParameterTest extends PHPUnit_Framework_TestCase
{
    /** @var  Parameter */
    private $oParam;

    public function setUp()
    {
        $this->oParam = new Parameter('test',123,DB_PARAM_INT);
    }

    public function testName()
    {
        $this->assertEquals($this->oParam->GetName(), ':test');
        $this->assertEquals($this->oParam->Name, ':test');
    }

    public function testValue()
    {
        $this->assertEquals($this->oParam->GetValue(), 123);
        $this->assertEquals($this->oParam->Value, 123);
    }

    public function testType()
    {
        $this->assertEquals($this->oParam->GetType(), PDO::PARAM_INT);
        $this->assertEquals($this->oParam->Type, PDO::PARAM_INT);
    }
}