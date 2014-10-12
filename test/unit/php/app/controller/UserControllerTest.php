<?php

use Dero\Core\Retval;

class UserControllerTest extends PHPUnit_Framework_TestCase
{
    use \Test\Unit\Php\Traits\assertHeaders;

    /** @var  App\Controller\UserController */
    private $oController;

    /** @var App\Model\UserModel */
    private $oModel;

    public function setUp()
    {
        $this->oModel = $this->getMock(
            'App\Model\UserModel',
            ['validate','getUser','insertUser','updateUser','checkLogin'],
            [$this->getMock(
                'Dero\Data\PDOMysql',
                null,
                ['default']
            )]
        );
        $this->oController = new \App\Controller\UserController(
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

    public function testLoginFailureMissingLogin()
    {
        $mRet = $this->oController->login();

        $this->assertNotEmpty($mRet);
        $this->assertArrayHasKey('error', $mRet);
        $this->assertEquals(
            'Both a username and a password are required.',
            $mRet['error']
        );
        $this->assertHeaderStatus(422);
    }

    public function testLoginFailureBadLogin()
    {
        $_POST['username'] = 'user';
        $_POST['password'] = 'pass';

        $oRetval = new Retval();
        $oRetval->AddError('username/password mismatch');
        $this->oModel->method('checkLogin')->willReturn($oRetval);
        $mRet = $this->oController->login();

        $this->assertNotEmpty($mRet);
        $this->assertArrayHasKey('error', $mRet);
        $this->assertEquals(
            'username/password mismatch',
            $mRet['error']
        );
        $this->assertHeaderStatus(403);
    }

    public function testLoginFailureGoodLoginBadGetUser()
    {
        $_POST['username'] = 'user';
        $_POST['password'] = 'pass';

        $oLoginRetval = new Retval();
        $oLoginRetval->Set(123);
        $this->oModel->method('checkLogin')->willReturn($oLoginRetval);

        $oGetRetval = new Retval();
        $oGetRetval->AddError('problem querying database');
        $this->oModel->method('getUser')->willReturn($oGetRetval);
        $mRet = $this->oController->login();

        $this->assertNotEmpty($mRet);
        $this->assertArrayHasKey('error', $mRet);
        $this->assertEquals(
            'problem querying database',
            $mRet['error']
        );
        $this->assertHeaderStatus(500);
    }

    public function testLoginSuccess()
    {
        $_POST['username'] = 'user';
        $_POST['password'] = 'pass';

        $oLoginRetval = new Retval();
        $oLoginRetval->Set(123);
        $this->oModel->method('checkLogin')->willReturn($oLoginRetval);

        $oGetRetval = new Retval();
        $oGetRetval->Set([['user_id' => 123]]);
        $this->oModel->method('getUser')->willReturn($oGetRetval);
        $mRet = $this->oController->login();

        $this->assertNotEmpty($mRet);
        $this->assertArrayHasKey('success', $mRet);
        $this->assertTrue($mRet['success']);
    }

    public function testGetCurrentFailureNoSession()
    {
        $mRet = $this->oController->getCurrentUser();

        $this->assertNotEmpty($mRet);
        $this->assertArrayHasKey('success', $mRet);
        $this->assertFalse($mRet['success']);
    }

    public function testGetCurrentFailureNoUser()
    {
        $_SESSION['user_id'] = 123;
        $oGetRetval = new Retval();
        $oGetRetval->AddError('user not found');
        $this->oModel->method('getUser')->willReturn($oGetRetval);
        $mRet = $this->oController->getCurrentUser();

        $this->assertNotEmpty($mRet);
        $this->assertArrayHasKey('error', $mRet);
        $this->assertEquals(
            'user not found',
            $mRet['error']
        );
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testGetCurrentSuccess()
    {
        $_SESSION['user_id'] = 123;
        $oGetRetval = new Retval();
        $oGetRetval->Set([['user_id' => 123]]);
        $this->oModel->method('getUser')->willReturn($oGetRetval);
        $mRet = $this->oController->getCurrentUser();

        $this->assertNotEmpty($mRet);
        $this->assertArrayHasKey('success', $mRet);
        $this->assertTrue($mRet['success']);
        $this->assertArrayHasKey('user_id', $_SESSION);
        $this->assertEquals(123, $_SESSION['user_id']);
    }

    public function testLogout()
    {
        $_SESSION['user_id'] = 123;
        $this->oController->logout();

        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testSaveUserFailureNoUser()
    {
        $mRet = $this->oController->saveUser();

        $this->assertNotEmpty($mRet);
        $this->assertArrayHasKey('error', $mRet);
        $this->assertEquals(
            'No user information found',
            $mRet['error']
        );
        $this->assertHeaderStatus(422, 'Unprocessable Entity');
    }

    public function testSaveUserFailureValidation()
    {
        $_POST['user'] = [];

        $oValidationRetval = new Retval();
        $oValidationRetval->AddError('did not validate');
        $this->oModel->method('validate')->willReturn($oValidationRetval);
        $mRet = $this->oController->saveUser();

        $this->assertNotEmpty($mRet);
        $this->assertArrayHasKey('error', $mRet);
        $this->assertEquals(
            'did not validate',
            $mRet['error']
        );
        $this->assertHeaderStatus(422, 'Unprocessable Entity');
    }

    public function testSaveUserFailureUpdate()
    {
        $_POST['user'] = [
            'user_id' => 123
        ];

        $oValidationRetval = new Retval();
        $this->oModel->method('validate')->willReturn($oValidationRetval);

        $oUpdateRetval = new Retval();
        $oUpdateRetval->AddError('failure to update user');
        $this->oModel->method('updateUser')->willReturn($oUpdateRetval);
        $mRet = $this->oController->saveUser();

        $this->assertNotEmpty($mRet);
        $this->assertArrayHasKey('error', $mRet);
        $this->assertEquals(
            'failure to update user',
            $mRet['error']
        );
        $this->assertHeaderStatus(422, 'Unprocessable Entity');
    }

    public function testSaveUserSuccessUpdate()
    {
        $_POST['user'] = [
            'user_id' => 123,
            'username' => 'test user'
        ];

        $oValidationRetval = new Retval();
        $this->oModel->method('validate')->willReturn($oValidationRetval);

        $oUpdateRetval = new Retval();
        $oUpdateRetval->Set((object) $_POST['user']);
        $this->oModel->method('updateUser')->willReturn($oValidationRetval);
        $mRet = $this->oController->saveUser();

        $this->assertNotEmpty($mRet);
        $this->assertArrayHasKey('success', $mRet);
        $this->assertEquals(
            'Successfully updated test user',
            $mRet['success']
        );
    }

    public function testSaveUserFailureInsertPasswordMismatch()
    {
        $_POST['user'] = [
            'username' => 'test user',
            'password' => 'goodpass',
            'password_confirm' => 'badpass',
            'email' => 'email@place.com',
            'email_confirm' => 'email@place.com'
        ];

        $oValidationRetval = new Retval();
        $this->oModel->method('validate')->willReturn($oValidationRetval);
        $mRet = $this->oController->saveUser();

        $this->assertNotEmpty($mRet);
        $this->assertArrayHasKey('error', $mRet);
        $this->assertEquals(
            'Passwords do not match',
            $mRet['error']
        );
    }

    public function testSaveUserFailureInsertEmailMismatch()
    {
        $_POST['user'] = [
            'username' => 'test user',
            'password' => 'goodpass',
            'password_confirm' => 'goodpass',
            'email' => 'email@place.com',
            'email_confirm' => 'email@otherplace.com'
        ];

        $oValidationRetval = new Retval();
        $this->oModel->method('validate')->willReturn($oValidationRetval);
        $mRet = $this->oController->saveUser();

        $this->assertNotEmpty($mRet);
        $this->assertArrayHasKey('error', $mRet);
        $this->assertEquals(
            'Emails do not match',
            $mRet['error']
        );
    }

    public function testSaveUserFailureInsert()
    {
        $_POST['user'] = [
            'username' => 'test user',
            'password' => 'goodpass',
            'password_confirm' => 'goodpass',
            'email' => 'email@place.com',
            'email_confirm' => 'email@place.com'
        ];

        $oValidationRetval = new Retval();
        $this->oModel->method('validate')->willReturn($oValidationRetval);

        $oInsertRetval = new Retval();
        $oInsertRetval->AddError('failure to insert');
        $this->oModel->method('insertUser')->willReturn($oInsertRetval);
        $mRet = $this->oController->saveUser();

        $this->assertNotEmpty($mRet);
        $this->assertArrayHasKey('error', $mRet);
        $this->assertEquals(
            'failure to insert',
            $mRet['error']
        );
    }

    public function testSaveUserSuccessInsert()
    {
        $_POST['user'] = [
            'username' => 'test user',
            'password' => 'goodpass',
            'password_confirm' => 'goodpass',
            'email' => 'email@place.com',
            'email_confirm' => 'email@place.com'
        ];

        $oValidationRetval = new Retval();
        $this->oModel->method('validate')->willReturn($oValidationRetval);

        $oInsertRetval = new Retval();
        $oInsertRetval->Set((object) $_POST['user']);
        $this->oModel->method('insertUser')->willReturn($oInsertRetval);
        $mRet = $this->oController->saveUser();

        $this->assertNotEmpty($mRet);
        $this->assertArrayHasKey('success', $mRet);
        $this->assertEquals(
            'Successfully added test user',
            $mRet['success']
        );
    }

    public function testGetUsersFailure()
    {
        $oGetRetval = new Retval();
        $oGetRetval->AddError('failed to execute query');
        $this->oModel->method('getUser')->willReturn($oGetRetval);
        $mRet = $this->oController->getUsers();

        $this->assertNotEmpty($mRet);
        $this->assertArrayHasKey('error', $mRet);
        $this->assertEquals(
            'failed to execute query',
            $mRet['error']
        );
        $this->assertHeaderStatus(500);
    }

    public function testGetUsersEmpty()
    {
        $oGetRetval = new Retval();
        $oGetRetval->Set([]);
        $this->oModel->method('getUser')->willReturn($oGetRetval);
        $mRet = $this->oController->getUsers();

        $this->assertNotEmpty($mRet);
        $this->assertArrayHasKey('success', $mRet);
        $this->assertEquals(
            'Found 0 users',
            $mRet['success']
        );
        $this->assertArrayHasKey('count', $mRet);
        $this->assertEquals(0, $mRet['count']);
    }

    public function testGetUsersNotEmpty()
    {
        $oGetRetval = new Retval();
        $oGetRetval->Set([new StdClass(),new StdClass()]);
        $this->oModel->method('getUser')->willReturn($oGetRetval);
        $mRet = $this->oController->getUsers();

        $this->assertNotEmpty($mRet);
        $this->assertArrayHasKey('success', $mRet);
        $this->assertEquals(
            'Found 2 users',
            $mRet['success']
        );
        $this->assertArrayHasKey('count', $mRet);
        $this->assertEquals(2, $mRet['count']);
    }
}