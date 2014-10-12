<?php

class AuctionControllerTest extends PHPUnit_Framework_TestCase
{
    use \Test\Unit\Php\Traits\assertHeaders;

    /** @var  App\Controller\AuctionController */
    private $oController;

    /** @var App\Model\AuctionModel */
    private $oModel;

    public function setUp()
    {
        $this->oModel = $this->getMock(
            'App\Model\AuctionModel',
            ['getAuction','validate','insertAuction'],
            [$this->getMock(
                'Dero\Data\PDOMysql',
                null,
                ['default']
            )]
        );
        $this->oController = new \App\Controller\AuctionController(
            $this->oModel
        );
    }

    public function tearDown()
    {
        $this->oController = null;
        $this->oModel = null;
        unset($this->oController);
        unset($this->oModel);
    }

    public function testGetAuctionsEmpty()
    {
        $oRetval = new \Dero\Core\Retval();
        $oRetval->Set([]);
        $this->oModel->method('getAuction')->willReturn($oRetval);
        $mResult = $this->oController->getAuctions();

        $this->assertNotEmpty($mResult);
        $this->assertArrayHasKey('success', $mResult);
        $this->assertArrayHasKey('count', $mResult);
        $this->assertEquals(0, $mResult['count']);
    }

    public function testGetAuctionsNotEmpty()
    {
        $oRetval = new \Dero\Core\Retval();
        $oRetval->Set([1,2,3]);
        $this->oModel->method('getAuction')->willReturn($oRetval);
        $mResult = $this->oController->getAuctions();

        $this->assertNotEmpty($mResult);
        $this->assertArrayHasKey('success', $mResult);
        $this->assertArrayHasKey('count', $mResult);
        $this->assertEquals(3, $mResult['count']);
    }

    public function testGetAuctionsFailure()
    {
        $oRetval = new \Dero\Core\Retval();
        $oRetval->AddError('failed to fetch data');
        $this->oModel->method('getAuction')->willReturn($oRetval);
        $mResult = $this->oController->getAuctions();

        $this->assertNotEmpty($mResult);
        $this->assertArrayHasKey('error', $mResult);
        $this->assertHeaderStatus(200, 'Internal Server Error');
    }

    public function testAddAuctionsFailureNoAuction()
    {
        $mResult = $this->oController->addAuction();

        $this->assertNotEmpty($mResult);
        $this->assertArrayHasKey('error', $mResult);
        $this->assertHeaderStatus(422, 'Unprocessable Entity');
    }

    public function testAddAuctionFailureNoUser()
    {
        $_POST['auction'] = [];
        $mResult = $this->oController->addAuction();

        $this->assertNotEmpty($mResult);
        $this->assertArrayHasKey('error', $mResult);
        $this->assertHeaderStatus(403, 'Forbidden');
    }

    public function testAddAuctionFailureValidation()
    {
        $_POST['auction'] = [];
        $_SESSION['user_id'] = 1;

        $oRetval = new \Dero\Core\Retval();
        $oRetval->AddError('did not validate');
        $this->oModel->method('validate')->willReturn($oRetval);

        $mResult = $this->oController->addAuction();

        $this->assertNotEmpty($mResult);
        $this->assertArrayHasKey('error', $mResult);
        $this->assertHeaderStatus(422, 'Unprocessable Entity');
    }

    public function testAddAuctionFailureModel()
    {
        $_POST['auction'] = [];
        $_SESSION['user_id'] = 1;

        $oValidateRetval = new \Dero\Core\Retval();
        $this->oModel->method('validate')->willReturn($oValidateRetval);

        $oInsertRetval = new \Dero\Core\Retval();
        $oInsertRetval->AddError('failed to insert');
        $this->oModel->method('insertAuction')->willReturn($oInsertRetval);

        $mResult = $this->oController->addAuction();

        $this->assertNotEmpty($mResult);
        $this->assertArrayHasKey('error', $mResult);
        $this->assertHeaderStatus(422, 'Unprocessable Entity');
    }

    public function testAddAuctionSuccess()
    {
        $_POST['auction'] = [];
        $_SESSION['user_id'] = 1;

        $oValidateRetval = new \Dero\Core\Retval();
        $this->oModel->method('validate')->willReturn($oValidateRetval);

        $oInsertRetval = new \Dero\Core\Retval();
        $oInsertRetval->Set([]);
        $this->oModel->method('insertAuction')->willReturn($oInsertRetval);

        $mResult = $this->oController->addAuction();

        $this->assertNotEmpty($mResult);
        $this->assertArrayHasKey('success', $mResult);
        $this->assertArrayHasKey('auction', $mResult);
        $this->assertHeaderStatus(210, 'Created');
    }
}