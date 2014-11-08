<?php

class BaseModelExtensionTest extends PHPUnit_Framework_TestCase
{
    /** @var Dero\Data\PDOMysql */
    private $oDataInterface;

    /** @var BaseModelExtension */
    private $oModel;

    public function setUp()
    {
        $this->oDataInterface = $this->getMock(
            'Dero\Data\PDOMysql',
            ['Query'],
            ['default']
        );
        $this->oModel = new BaseModelExtension(
            $this->oDataInterface
        );
    }

    public function testInstantiates()
    {
        $this->assertNotNull($this->oModel);
    }

    public function testValidateSuccess()
    {
        $aVars = [
            'test_id' => 1,
            'test_int' => 1234,
            'test_bool' => true,
            'test_dec' => '-1.234',
            'test_nullable' => null,
            'test_string' => 'string',
            'test_fixed' => 'fixed'
        ];
        $oRet = $this->oModel->validate($aVars);
        $this->assertFalse($oRet->HasFailure());
    }

    public function testValidateFailInt()
    {
        $aVars = [
            'test_id' => 1,
            'test_int' => 'not int',
            'test_bool' => true,
            'test_dec' => '-1.234',
            'test_nullable' => null,
            'test_string' => 'string',
            'test_fixed' => 'fixed'
        ];
        $oRet = $this->oModel->validate($aVars);
        $this->assertTrue($oRet->HasFailure());
        $this->assertEquals(
            'test_int must be a valid integer.',
            $oRet->GetError()
        );
    }

    public function testValidateFailBool()
    {
        $aVars = [
            'test_id' => 1,
            'test_int' => '123',
            'test_bool' => 'not true',
            'test_dec' => '-1.234',
            'test_nullable' => null,
            'test_string' => 'string',
            'test_fixed' => 'fixed'
        ];
        $oRet = $this->oModel->validate($aVars);
        $this->assertTrue($oRet->HasFailure());
        $this->assertEquals(
            'test_bool must be a valid boolean.',
            $oRet->GetError()
        );
    }

    public function testValidateFailDec()
    {
        $aVars = [
            'test_id' => 1,
            'test_int' => '123',
            'test_bool' => true,
            'test_dec' => '-1.fail',
            'test_nullable' => null,
            'test_string' => 'string',
            'test_fixed' => 'fixed'
        ];
        $oRet = $this->oModel->validate($aVars);
        $this->assertTrue($oRet->HasFailure());
        $this->assertEquals(
            'test_dec must be a valid decimal.',
            $oRet->GetError()
        );
    }

    public function testValidateFailStringRequired()
    {
        $aVars = [
            'test_id' => 1,
            'test_int' => 123,
            'test_bool' => true,
            'test_dec' => '-1.234',
            'test_nullable' => null,
            'test_string' => null, // required, null is not set!
            'test_fixed' => 'fixed'
        ];
        $oRet = $this->oModel->validate($aVars);
        $this->assertTrue($oRet->HasFailure());
        $this->assertEquals(
            'test_string is required.',
            $oRet->GetError()
        );
    }

    public function testValidateFailStringPattern()
    {
        $aVars = [
            'test_id' => 1,
            'test_int' => 123,
            'test_bool' => true,
            'test_dec' => '-1.234',
            'test_nullable' => null,
            'test_string' => 'hello world', // no spaces allowed
            'test_fixed' => 'fixed'
        ];
        $oRet = $this->oModel->validate($aVars);
        $this->assertTrue($oRet->HasFailure());
        $this->assertEquals(
            'test_string did not validate.',
            $oRet->GetError()
        );
    }

    public function testValidateFailStringLength()
    {
        $aVars = [
            'test_id' => 1,
            'test_int' => 123,
            'test_bool' => true,
            'test_dec' => '-1.234',
            'test_nullable' => null,
            'test_string' => 'thisIsWayTooLong',
            'test_fixed' => 'fixed'
        ];
        $oRet = $this->oModel->validate($aVars);
        $this->assertTrue($oRet->HasFailure());
        $this->assertEquals(
            'test_string is longer than max length (15).',
            $oRet->GetError()
        );
    }

    public function testValidateFailFixedStringLength()
    {
        $aVars = [
            'test_id' => 1,
            'test_int' => 123,
            'test_bool' => true,
            'test_dec' => '-1.234',
            'test_nullable' => null,
            'test_string' => 'string',
            'test_fixed' => 'blah'
        ];
        $oRet = $this->oModel->validate($aVars);
        $this->assertTrue($oRet->HasFailure());
        $this->assertEquals(
            'test_fixed must be fixed length (5).',
            $oRet->GetError()
        );
    }
}

class BaseModelExtension extends \Dero\Data\BaseModel
{
    protected static $TABLE_NAME = 'test';

    protected static $COLUMNS = [
        'test_id' => [
            COL_TYPE => COL_TYPE_INTEGER,
            KEY_TYPE => KEY_TYPE_PRIMARY,
            'required' => false,
            'extra' => [
                DB_AUTO_INCREMENT
            ]
        ],
        'test_int' => [
            COL_TYPE => COL_TYPE_INTEGER,
            'required' => true
        ],
        'test_bool' => [
            COL_TYPE => COL_TYPE_BOOLEAN,
            'required' => true
        ],
        'test_dec' => [
            COL_TYPE => COL_TYPE_DECIMAL,
            'col_length' => 10,
            'scale' => 3,
            'required' => false
        ],
        'test_nullable' => [
            COL_TYPE => COL_TYPE_INTEGER,
            'required' => false,
            'extra' => [
                DB_NULLABLE
            ]
        ],
        'test_string' => [
            COL_TYPE => COL_TYPE_STRING,
            'col_length' => 15,
            'required' => true,
            'validation_pattern' => '/^[a-z][a-z0-9]+$/i'
        ],
        'test_fixed' => [
            COL_TYPE => COL_TYPE_FIXED_STRING,
            'col_length' => 5,
            'required' => true,
            'validation_pattern' => '/^[a-z]+$/i'
        ],
        'created' => [
            COL_TYPE => COL_TYPE_DATETIME,
            'required' => false
        ],
        'modified' => [
            COL_TYPE => COL_TYPE_DATETIME,
            'required' => false
        ]
    ];
}