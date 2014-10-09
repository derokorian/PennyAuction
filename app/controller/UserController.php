<?php

namespace App\Controller;
use Dero\Core\BaseController;
use App\Model\UserModel;

/**
 * Blog controller
 * @author Ryan Pallas
 * @package SampleSite
 * @namespace App\Controller
 * @since 2014-02-27
 */

class UserController extends BaseController
{
    private $oUserModel;

    public function __construct(UserModel $oUserModel)
    {
        $this->oUserModel = $oUserModel;
    }

    public function login()
    {
        $strUsername = isset($_POST['username']) ? $_POST['username'] : null;
        $strPassword = isset($_POST['password']) ? $_POST['password'] : null;
        if( is_null($strUsername) || is_null($strPassword) )
        {
            header('HTTP/1.1 422 Unprocessable Entity');
            echo "Both a username and a password are required.";
            return;
        }
        $oRet = $this->oUserModel->checkLogin($strUsername, $strPassword);
        if( $oRet->HasFailure() )
        {
            header('HTTP/1.1 500 Internal Server Error');
            $arrRet = json_encode(['error' => $oRet->GetError()]);
        }
        else
        {
            $_SESSION['user_id'] = $oRet->Get();
            $oRet = $this->oUserModel
                         ->getUser(['user_id' => $oRet->Get()]);
            if( $oRet->HasFailure() )
            {
                header('HTTP/1.1 500 Internal Server Error');
                $arrRet = json_encode(['error' => $oRet->GetError()]);
            }
            else
            {
                $arrRet =  ['success' => true, 'user' => $oRet->Get()[0]];
            }
        }
        echo json_encode($arrRet);
    }

    public function getCurrentUser()
    {
        if( isset($_SESSION['user_id']) )
        {
            $oRet = $this->oUserModel->getUser(['user_id' => $_SESSION['user_id']]);
            if( $oRet->HasFailure() )
            {
                echo json_encode(['error' => $oRet->GetError()]);
            }
            else
            {
                echo json_encode(['success' => true, 'user' => $oRet->Get()[0]]);
            }
        }
        else
        {
            echo json_encode(['success' => false]);
        }
    }

    public function logout()
    {
        $_SESSION['user'] = null;
        unset($_SESSION['user']);
    }

    public function saveUser()
    {
        $oUser = isset($_POST['user']) && is_array($_POST['user'])
            ? (object) $_POST['user'] : null;
        if( is_null($oUser) )
        {
            header('HTTP/1.1 422 Unprocessable Entity');
            echo "No user information found";
            return;
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

        echo json_encode($aRet);
    }

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
            if( count($oRet->Get()) > 0 )
            {
                $aChars = array_map(function ($oChar) {
                    $aChar = (array) $oChar;
                    return $aChar;
                }, $oRet->Get());
                $c = count($aChars);
                $aRet = [
                    'success' => "Found $c characters",
                    'count' => $c,
                    'characters' => $aChars
                ];
            }
            else
            {
                $aRet = [
                    'success' => 'Found 0 characters',
                    'count' => 0,
                    'characters' => []
                ];
            }
        }
        else
        {
            header('HTTP/1.1 500 Internal Server Error');
            $aRet = [
                'error' => $oRet->GetError()
            ];
        }
        echo json_encode($aRet);
    }
}