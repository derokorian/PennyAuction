<?php

use Dero\Core\Retval;

class BidControllerTest extends PHPUnit_Framework_TestCase
{
    use \Test\Unit\Php\Traits\assertHeaders;

    /** @var  App\Controller\BidController */
    private $oController;

    /** @var App\Model\BidModel */
    private $oModel;

    public function setUp()
    {
        $this->oModel = $this->getMock(
            'App\Model\BidModel',
            ['validate','getBid','insertBid'],
            [$this->getMock(
                'Dero\Data\PDOMysql',
                null,
                ['default']
            )]
        );
        $this->oController = new \App\Controller\BidController(
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

    public function testGetBidsEmpty()
    {
        $oRetval = new Retval();
        $oRetval->Set([]);
        $this->oModel->method('getBid')->willReturn($oRetval);
        $mResult = $this->oController->getBids();

        $this->assertNotEmpty($mResult);
        $this->assertArrayHasKey('success', $mResult);
        $this->assertEquals(
            'Found 0 bids',
            $mResult['success']
        );
        $this->assertArrayHasKey('count', $mResult);
        $this->assertEquals(0, $mResult['count']);
    }

    public function testGetBidsNotEmpty()
    {
        $oRetval = new Retval();
        $oRetval->Set([1,2,3]);
        $this->oModel->method('getBid')->willReturn($oRetval);
        $mResult = $this->oController->getBids();

        $this->assertNotEmpty($mResult);
        $this->assertArrayHasKey('success', $mResult);
        $this->assertEquals(
            'Found 3 bids',
            $mResult['success']
        );
        $this->assertArrayHasKey('count', $mResult);
        $this->assertEquals(3, $mResult['count']);
    }

    public function testGetBidsFailure()
    {
        $oRetval = new Retval();
        $oRetval->AddError('failed to fetch data');
        $this->oModel->method('getBid')->willReturn($oRetval);
        $mResult = $this->oController->getBids();

        $this->assertNotEmpty($mResult);
        $this->assertArrayHasKey('error', $mResult);
        $this->assertEquals(
            'failed to fetch data',
            $mResult['error']
        );
        $this->assertHeaderStatus(500, 'Internal Server Error');
    }

    public function testAddBidFailureNoBid()
    {
        $mResult = $this->oController->addBid();

        $this->assertNotEmpty($mResult);
        $this->assertArrayHasKey('error', $mResult);
        $this->assertEquals(
            'No bid information found',
            $mResult['error']
        );
        $this->assertHeaderStatus(422, 'Unprocessable Entity');
    }

    public function testAddBidFailureNoUser()
    {
        $_POST['bid'] = [];
        $mResult = $this->oController->addBid();

        $this->assertNotEmpty($mResult);
        $this->assertArrayHasKey('error', $mResult);
        $this->assertEquals(
            'You must be logged in to create bids',
            $mResult['error']
        );
        $this->assertHeaderStatus(403, 'Forbidden');
    }

    public function testAddBidFailureValidation()
    {
        $_POST['bid'] = [];
        $_SESSION['user_id'] = 1;

        $oRetval = new Retval();
        $oRetval->AddError('did not validate');
        $this->oModel->method('validate')->willReturn($oRetval);

        $mResult = $this->oController->addBid();

        $this->assertNotEmpty($mResult);
        $this->assertArrayHasKey('error', $mResult);
        $this->assertEquals(
            'did not validate',
            $mResult['error']
        );
        $this->assertHeaderStatus(422, 'Unprocessable Entity');
    }

    public function testAddBidFailureModel()
    {
        $_POST['bid'] = [];
        $_SESSION['user_id'] = 1;

        $oValidateRetval = new Retval();
        $this->oModel->method('validate')->willReturn($oValidateRetval);

        $oInsertRetval = new Retval();
        $oInsertRetval->AddError('failed to insert');
        $this->oModel->method('insertBid')->willReturn($oInsertRetval);

        $mResult = $this->oController->addBid();

        $this->assertNotEmpty($mResult);
        $this->assertArrayHasKey('error', $mResult);
        $this->assertEquals(
            'failed to insert',
            $mResult['error']
        );
        $this->assertHeaderStatus(422, 'Unprocessable Entity');
    }

    public function testAddBidSuccess()
    {
        $_POST['bid'] = [];
        $_SESSION['user_id'] = 1;

        $oValidateRetval = new Retval();
        $this->oModel->method('validate')->willReturn($oValidateRetval);

        $oInsertRetval = new Retval();
        $oInsertRetval->Set([]);
        $this->oModel->method('insertBid')->willReturn($oInsertRetval);

        $mResult = $this->oController->addBid();

        $this->assertNotEmpty($mResult);
        $this->assertArrayHasKey('success', $mResult);
        $this->assertArrayHasKey('auction', $mResult);
        $this->assertEquals(
            'Successfully added new bid',
            $mResult['success']
        );
        $this->assertHeaderStatus(201, 'Created');
    }
}