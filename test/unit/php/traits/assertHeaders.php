<?php

namespace Test\Unit\Php\Traits;

trait assertHeaders
{
    protected function assertHeaderStatus($iStatusCode)
    {
        $this->assertEquals($iStatusCode, http_response_code());
    }

    protected function assertHeaderSet($strKey, $mValue)
    {
        $aHeaders = [];
        foreach(xdebug_get_headers() as $strHeader)
        {
            $aHeader = explode(':',$strHeader);
            $aHeaders[$aHeader[0]] = $aHeader[1];
        }

        $this->assertArrayHasKey($strKey, $aHeaders);
        $this->assertContains($mValue, $aHeaders[$strKey]);
    }
}