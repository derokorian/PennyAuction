<?php

class AuctionControllerTest extends PHPUnit_Framework_TestCase
{
    /** @var  App\Controller\AuctionController */
    private $oController;

    public function setUp()
    {
        $oModel = $this->getMock(
            'App\Model\AuctionModel',
            ['getAuction'],
            [$this->getMock(
                'Dero\Data\PDOMysql',
                null,
                ['default']
            )]
        );
        $oRetval = new \Dero\Core\RetVal();
        $oRetval->Set([]);
        $oModel->method('getAuction')->willReturn($oRetval);
        $this->oController = new \App\Controller\AuctionController(
            $oModel
        );
    }

    public function testGetAuctions()
    {
        $this->assertNotEmpty(
            $this->oController->getAuctions()
        );
    }
}