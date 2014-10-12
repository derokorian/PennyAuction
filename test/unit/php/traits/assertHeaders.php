<?php

namespace Test\Unit\Php\Traits;

trait assertHeaders
{
    // TODO: Figure out how to implement this
    protected function assertHeaderStatus($iStatusCode, $mMessageContains = '')
    {
        return true;
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