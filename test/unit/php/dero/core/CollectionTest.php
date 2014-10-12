<?php

use Dero\Core\Collection;

class CollectionTest extends PHPUnit_Framework_TestCase
{
    public function testSmallCollection()
    {
        $oCollection = new Collection();
        $oCollection->add(1);
        $oCollection->add(2);
        $oCollection->add(3);

        $this->assertEquals($oCollection->count(), 3);
        foreach($oCollection as $iKey => $iValue)
        {
            $this->assertEquals($iKey + 1, $iValue);
        }
    }
}