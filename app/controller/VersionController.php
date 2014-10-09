<?php

namespace App\Controller;
use App\Model\CharacterModel;
use App\Model\UserModel;
use Dero\Data\Factory;
use Dero\Core\BaseController;
/**
 * Blog controller
 * @author Ryan Pallas
 * @package SampleSite
 * @namespace App\Controller
 * @since 2014-02-27
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
        $db = Factory::GetDataInterface('default');
        $oUserModel = new UserModel($db);

        echo "Would you likst to execute these create statements [y/n]?\n";
        $f = fopen("php://stdin", 'r');
        $a = fgets($f);
        if( strtolower($a) )
        {
            echo "Creating tables...\n";
            try
            {
                $oRet = $oUserModel->CreateTable();
                if( $oRet->HasFailure() )
                {
                    echo "Error on user table\n";
                    var_dump($oRet);
                    return;
                }

                if( !$oRet->HasFailure() )
                {
                    echo "Tables created successfully.\n";
                }
            } catch (\Exception $e) {
                echo "Problem creating tables.\n";
                var_dump($e);
            }

        }
    }

    public function upgrade()
    {
        $db = Factory::GetDataInterface('default');

        $oUserModel = new UserModel($db);

        $oRet = $oUserModel->VerifyTableDefinition();
        var_dump($oRet->Get());
    }
}