<?php

namespace App\Controller;
use App\Model\AuctionModel;
use App\Model\UserModel;
use App\Model\BidModel;
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
        $db = Factory::GetDataInterface('default');

        echo "Creating tables...\n";
        try
        {
            Timing::start('user');
            $oUserModel = new UserModel($db);
            $oRet = $oUserModel->CreateTable();
            if( $oRet->HasFailure() )
            {
                echo "Error on user table\n";
                var_dump($oRet);
                return;
            }
            Timing::end('user');

            Timing::start('auction');
            $oAuctionModel = new AuctionModel($db);
            $oRet = $oAuctionModel->CreateTable();
            if( $oRet->HasFailure() )
            {
                echo "Error on auction table\n";
                var_dump($oRet);
                return;
            }
            Timing::end('auction');

            Timing::start('bid');
            $oBidModel = new BidModel($db);
            $oRet = $oBidModel->CreateTable();
            if( $oRet->HasFailure() )
            {
                echo "Error on bid table\n";
                var_dump($oRet);
                return;
            }
            Timing::end('bid');

            if( !$oRet->HasFailure() )
            {
                echo "Tables created successfully.\n";
            }
        } catch (\Exception $e) {
            echo "Problem creating tables.\n";
            var_dump($e);
        }
    }

    public function upgrade()
    {
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

            Timing::start('auction');
            $oAuctionModel = new AuctionModel($db);
            $oRet = $oAuctionModel->VerifyTableDefinition();
            if( $oRet->HasFailure() )
            {
                echo "Error on auction table\n";
                var_dump($oRet);
                return;
            }
            Timing::end('auction');

            Timing::start('bid');
            $oBidModel = new BidModel($db);
            $oRet = $oBidModel->VerifyTableDefinition();
            if( $oRet->HasFailure() )
            {
                echo "Error on bid table\n";
                var_dump($oRet);
                return;
            }
            Timing::end('bid');

            if( !$oRet->HasFailure() )
            {
                echo "Tables updated successfully.\n";
            }
        } catch (\Exception $e) {
            echo "Problem creating tables.\n";
            var_dump($e);
        }
    }
}