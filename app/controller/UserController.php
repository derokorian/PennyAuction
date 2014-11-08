<?php

namespace App\Controller;
use Dero\Core\BaseController;
use App\Model\UserModel;
use Dero\Core\Timing;

/**
 * User controller
 * @author Ryan Pallas
 * @package PennyAuction
 * @namespace App\Controller
 * @since 2014-10-08
 */

class UserController extends BaseController
{
    /** @var UserModel  */
    private $oUserModel;

    public function __construct(UserModel $oUserModel)
    {
        $this->oUserModel = $oUserModel;
    }

    /**
     * Processes a login request
     * @return array
     */
    public function login()
    {
        $strUsername = !empty($_POST['username']) ? $_POST['username'] : null;
        $strPassword = !empty($_POST['password']) ? $_POST['password'] : null;
        if( is_null($strUsername) || is_null($strPassword) )
        {
            header('HTTP/1.1 422 Unprocessable Entity');
            return ['error' => "Both a username and a password are required."];
        }
        $oRet = $this->oUserModel->checkLogin($strUsername, $strPassword);
        if( $oRet->HasFailure() )
        {
            header('HTTP/1.1 403 Forbidden');
            $aRet = ['error' => $oRet->GetError()];
        }
        else
        {
            $_SESSION['user_id'] = $oRet->Get();
            $oRet = $this->oUserModel
                         ->getUser(['user_id' => $oRet->Get()]);
            if( $oRet->HasFailure() )
            {
                unset($_SESSION['user_id']);
                header('HTTP/1.1 500 Internal Server Error');
                $aRet = ['error' => $oRet->GetError()];
            }
            else
            {
                $aRet =  ['success' => true, 'user' => $oRet->Get()[0]];
            }
        }
        return $aRet;
    }

    /**
     * Returns the currently logged in user
     * @return array
     */
    public function getCurrentUser()
    {
        if( isset($_SESSION['user_id']) )
        {
            $oRet = $this->oUserModel->getUser(['user_id' => $_SESSION['user_id']]);
            if( $oRet->HasFailure() )
            {
                $this->logout();
                $aRet = ['error' => $oRet->GetError()];
            }
            elseif( count($oRet->Get()) === 0 )
            {
                $aRet = ['error' => 'User not found'];
            }
            else
            {
                $aRet = ['success' => true, 'user' => $oRet->Get()[0]];
            }
        }
        else
        {
            $aRet = ['success' => false];
        }
        return $aRet;
    }

    /**
     * Unsets the session for a user to no longer be logged in
     * @return void
     */
    public function logout()
    {
        $_SESSION['user_id'] = null;
        unset($_SESSION['user_id']);
    }

    /**
     * @return array
     */
    public function saveUser()
    {
        $oUser = isset($_POST['user']) && is_array($_POST['user'])
            ? (object) $_POST['user'] : null;
        if( is_null($oUser) )
        {
            header('HTTP/1.1 422 Unprocessable Entity');
            return ["error"=> "No user information found"];
        }
        $oRet = $this->oUserModel->validate((array) $oUser);
        if( $oRet->HasFailure() )
        {
            header('HTTP/1.1 422 Unprocessable Entity');
            $aRet = ['error'=>$oRet->GetError()];
        }
        elseif( isset($oUser->user_id) )
        {
            $oRet = $this->oUserModel->updateUser($oUser);
            if( $oRet->HasFailure() )
            {
                header('HTTP/1.1 422 Unprocessable Entity');
                $aRet = ['error' => $oRet->GetError()];
            }
            else
            {
                $aRet = [
                    'success' => 'Successfully updated ' . $oUser->username,
                    'user' => $oUser
                ];
            }
        }
        else
        {
            if( $oUser->password !== $oUser->password_confirm )
            {
                $oRet->AddError('Passwords do not match');
            }
            if( $oUser->email !== $oUser->email_confirm )
            {
                $oRet->AddError('Emails do not match');
            }
            unset($oUser->password_confirm, $oUser->email_confirm);
            if( !$oRet->HasFailure() )
            {
                $oUser->active = true;
                $oRet = $this->oUserModel->insertUser($oUser);
            }
            if( $oRet->HasFailure() )
            {
                header('HTTP/1.1 422 Unprocessable Entity');
                $aRet = ['error' => $oRet->GetError()];
            }
            else
            {
                header('HTTP/1.1 201 Created');
                $aRet = [
                    'success' => 'Successfully added ' . $oUser->username,
                    'user' => $oUser
                ];
            }
        }

        return $aRet;
    }

    /**
     * @return array
     */
    public function getUsers()
    {
        $aOpts = [];
        $this->setFilter($aOpts, $_GET);
        if( isset($aOpts['id']) )
        {
            $aOpts['user_id'] = $aOpts['id'];
            unset($aOpts['id']);
        }
        $oRet = $this->oUserModel->getUser($aOpts);
        if( !$oRet->HasFailure() )
        {
            $aUsers = $oRet->Get();
            $c = count($aUsers);
            $aRet = [
                'success' => "Found $c users",
                'count' => $c,
                'users' => $c > 0 ? $aUsers : []
            ];
        }
        else
        {
            header('HTTP/1.1 500 Internal Server Error');
            $aRet = [
                'error' => $oRet->GetError()
            ];
        }
        return $aRet;
    }
}