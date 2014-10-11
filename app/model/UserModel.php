<?php

/**
 * User Model
 * @author Ryan Pallas
 * @package PennyAuction
 * @namespace App\Model
 * @since 2014-10-08
 */
namespace App\Model;

use Dero\Core\Retval;
use Dero\Data\BaseModel;
use Dero\Data\DataException;
use Dero\Data\DataInterface;
use Dero\Data\Factory;
use Dero\Data\Parameter;
use Dero\Data\ParameterCollection;


class UserModel extends BaseModel
{
    protected static $TABLE_NAME = 'user';

    protected static $COLUMNS = [
        'user_id' => [
            COL_TYPE => COL_TYPE_INTEGER,
            KEY_TYPE => KEY_TYPE_PRIMARY,
            'required' => false,
            'extra' => [
                DB_AUTO_INCREMENT
            ]
        ],
        'username' => [
            COL_TYPE => COL_TYPE_STRING,
            KEY_TYPE => KEY_TYPE_UNIQUE,
            'col_length' => 25,
            'required' => true,
            'validation_pattern' => '/^[a-z0-9_-]+$/i'
        ],
        'email' => [
            COL_TYPE => COL_TYPE_STRING,
            KEY_TYPE => KEY_TYPE_UNIQUE,
            'col_length' => 100,
            'required' => true
        ],
        'first_name' => [
            COL_TYPE => COL_TYPE_STRING,
            'col_length' => 50,
            'required' => false,
            'validation_pattern' => '/^[a-z-]+$/i',
            'extra' => [
                DB_NULLABLE
            ]
        ],
        'last_name' => [
            COL_TYPE => COL_TYPE_STRING,
            'col_length' => 50,
            'required' => false,
            'validation_pattern' => '/^[a-z-]+$/i',
            'extra' => [
                DB_NULLABLE
            ]
        ],
        'password' => [
            COL_TYPE => COL_TYPE_FIXED_STRING,
            'col_length' => 128,
            'required' => true
        ],
        'salt' => [
            COL_TYPE => COL_TYPE_FIXED_STRING,
            'col_length' => 128,
            'required' => false
        ],
        'active' => [
            COL_TYPE => COL_TYPE_BOOLEAN,
            'required' => false
        ],
        'created' => [
            COL_TYPE => COL_TYPE_DATETIME,
            'required' => false
        ],
        'modified' => [
            COL_TYPE => COL_TYPE_DATETIME,
            'required' => false
        ]
    ];

    /**
     * Constructor
     */
    public function __construct($db = null)
    {
        if( !$db instanceof DataInterface )
            $db = Factory::GetDataInterface('default');
        parent::__construct($db);
    }

    public function validate(Array $aUser)
    {
        $oRet = parent::validate($aUser);
        if( !filter_var($aUser['email'], FILTER_VALIDATE_EMAIL) )
        {
            $oRet->AddError('Email is not valid');
        }
        return $oRet;
    }

    public function getUser(Array $aOpts)
    {
        $oRet = new Retval();
        $oParams = new ParameterCollection();
        if( !isset($aOpts['order_by']) )
        {
            $aOpts['order_by'] = 'username';
        }
        $strSql = 'SELECT u.user_id, u.username, u.email, u.active, u.first_name,
                          u.last_name, u.created, u.modified
                     FROM `user` u '
            . $this->GenerateCriteria($oParams, $aOpts, 'u.');
        try
        {
            $oRet->Set(
                $this->DB
                    ->Prepare($strSql)
                    ->BindParams($oParams)
                    ->Execute()
                    ->GetAll()
            );
        } catch (DataException $e) {
            $oRet->AddError('Unable to query database', $e);
        }
        return $oRet;
    }

    public function insertUser(&$oUser)
    {
        $oRet = $this->validate((array) $oUser);
        if( !$oRet->HasFailure() )
        {
            $oUser->salt = $this->generateSalt();
            $oUser->password = $this->hashPassword($oUser->password, $oUser->salt );
            $oParams = new ParameterCollection();
            $strSql = 'INSERT INTO `user` ';
            $strSql .= $this->GenerateInsert($oParams, (array) $oUser);
            try
            {
                $oRet->Set(
                    $this->DB
                        ->Prepare($strSql)
                        ->BindParams($oParams)
                        ->Execute()
                );
            } catch (DataException $e) {
                $oRet->AddError('Unable to query database', $e);
            }
        }
        if( !$oRet->HasFailure() )
        {
            $strSql = 'SELECT LAST_INSERT_ID()';
            try
            {
                $oRet->Set(
                    $this->DB
                        ->Prepare($strSql)
                        ->Execute()
                        ->GetScalar()
                );
            } catch (DataException $e) {
                $oRet->AddError('Unable to query database', $e);
            }
        }
        if( !$oRet->HasFailure() )
        {
            $oUser->user_id = $oRet->Get();
        }
        unset($oUser->salt, $oUser->password);
        return $oRet;
    }

    public function updateUser(&$oUser)
    {
        $aUser = (array) $oUser;
        unset($aUser['user_id']);
        $oRet = $this->validate($aUser);
        if( !$oRet->HasFailure() )
        {
            $oParams = new ParameterCollection();
            $strSql = 'UPDATE `user` ';
            $strSql .= $this->GenerateCriteria($oParams, $aUser);
            $strSql = str_replace(['WHERE','AND'], ['SET',','], $strSql);
            $strSql .= $this->GenerateCriteria($oParams, ['user_id' => $oUser->user_id]);
            try
            {
                $oRet->Set(
                    $this->DB
                        ->Prepare($strSql)
                        ->BindParams($oParams)
                        ->Execute()
                );
            } catch (DataException $e) {
                $oRet->AddError('Unable to query database', $e);
            }
        }
        return $oRet;
    }

    public function checkLogin($strUser, $strPass)
    {
        $oRetVal = new Retval();
        $oParam = new Parameter('username', $strUser, DB_PARAM_STR);
        $strSql = 'SELECT user_id, password, salt FROM ' . static::$TABLE_NAME;
        $strSql .= ' WHERE username = :username AND active = 1';
        try
        {
            $oRetVal->Set(
                $this->DB
                     ->Prepare($strSql)
                     ->BindParam($oParam)
                     ->Execute()
                     ->Get()
            );
        } catch (DataException $e) {
            $oRetVal->AddError('Unable to query database', $e);
            return $oRetVal;
        }
        if( $oUser = $oRetVal->Get() )
        {
            if( $this->hashPassword($strPass, $oUser->salt) === $oUser->password )
            {
                $oRetVal->Set($oUser->user_id);
            }
            else
            {
                $oRetVal->AddError('Password mismatch');
            }
        }
        else
        {
            $oRetVal->AddError('Username not found');
        }
        return $oRetVal;
    }

    private function hashPassword($pass, $salt)
    {
        return hash('sha512', substr($salt, 0, 64) . $pass . substr($salt, 64));
    }

    private function generateSalt()
    {
        return hash('sha512', mt_rand());
    }
}