<?php

use Dero\Core\Retval;

class RetvalTest extends PHPUnit_Framework_TestCase
{
    public function testSuccess()
    {
        $oRetval = new Retval();
        $oRetval->Set(['key'=>'value']);

        $this->assertFalse($oRetval->HasFailure());

        $mRet = $oRetval->Get();

        $this->assertNotEmpty($mRet);
        $this->assertTrue(is_array($mRet));
        $this->assertArrayHasKey('key', $mRet);
        $this->assertEquals(
            $mRet['key'],
            'value'
        );
    }

    public function testSingleError()
    {
        $oException = new Exception('test');
        $oRetval = new Retval();
        $oRetval->AddError('string error', $oException);

        $this->assertTrue($oRetval->HasFailure());
        $this->assertEquals('string error', $oRetval->GetError());
        $this->assertEquals($oException, $oRetval->GetException());
    }

    public function testMultiError()
    {
        $aExceptions = [
            new Exception('first exception'),
            new Exception('second exception')
        ];
        $aErrors = [
            'first error',
            'second error'
        ];
        $oRetval = new Retval();
        $oRetval->AddError($aErrors[0], $aExceptions[0]);
        $oRetval->AddError($aErrors[1], $aExceptions[1]);

        $this->assertTrue($oRetval->HasFailure());
        $this->assertEquals($aErrors, $oRetval->GetError());
        $this->assertEquals($aExceptions, $oRetval->GetException());
    }
}