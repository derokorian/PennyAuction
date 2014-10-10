<?php

namespace App\Controller;
use App\Model\CharacterModel;
use App\Model\UserModel;
use Dero\Data\Factory;
use Dero\Core\BaseController;
use Dero\Core\Timing;

/**
 * Version controller
 * @author Ryan Pallas
 * @package PennyAuction
 * @namespace App\Controller
 * @since 2014-10-08
 */

class VersionController extends BaseController
{
    public function __construct() {
        if( PHP_SAPI !== 'cli' )
        {
            header('Location: ' . $_SERVER[''] . '/error/404');
            exit;
        }
    }

    public function install()
    {
        Timing::start('install');

        $db = Factory::GetDataInterface('default');
        $oUserModel = new UserModel($db);

        echo "Creating tables...\n";
        try
        {
            Timing::start('user');
            $oRet = $oUserModel->CreateTable();
            if( $oRet->HasFailure() )
            {
                echo "Error on user table\n";
                var_dump($oRet);
                return;
            }
            Timing::end('user');

            if( !$oRet->HasFailure() )
            {
                echo "Tables created successfully.\n";
            }
        } catch (\Exception $e) {
            echo "Problem creating tables.\n";
            var_dump($e);
        }
        Timing::end('install');
    }

    public function upgrade()
    {
        Timing::start('upgrade');
        $db = Factory::GetDataInterface('default');

        try {
            Timing::start('user');
            $oUserModel = new UserModel($db);
            $oRet = $oUserModel->VerifyTableDefinition();
            if( $oRet->HasFailure() )
            {
                echo "Error on user table\n";
                var_dump($oRet);
                return;
            }
            Timing::end('user');

            if( !$oRet->HasFailure() )
            {
                echo "Tables updated successfully.\n";
            }
        } catch (\Exception $e) {
            echo "Problem creating tables.\n";
            var_dump($e);
        }

        Timing::end('upgrade');
    }
}