<?php

/**
 * Auction controller
 * @author Ryan Pallas
 * @package PennyAuction
 * @namespace App\Controller
 * @since 2014-10-08
 */

namespace App\Controller;
use Dero\Core\BaseController;
use App\Model\AuctionModel;
use Dero\Core\Timing;

class AuctionController extends BaseController
{
    /** @var  \App\Model\AuctionModel */
    private $oModel;

    /**
     * @param AuctionModel $oModel
     */
    public function __construct(AuctionModel $oModel)
    {
        $this->oModel = $oModel;
    }

    /**
     * @returns string
     */
    public function addAuction()
    {
        $aRet = [];
        $oAuction = isset($_POST['auction']) && is_array($_POST['auction'])
            ? (object) $_POST['auction'] : null;
        if( is_null($oAuction) )
        {
            header('HTTP/1.1 422 Unprocessable Entity');
            $aRet['error'] = "No auction information found";
        }
        elseif( !isset($_SESSION['user_id']) )
        {
            header('HTTP/1.1 403 Forbidden');
            $aRet['error'] = "You must be logged in to create auctions";
        }
        else
        {
            $oAuction->user_id = $_SESSION['user_id'];

            $oRet = $this->oModel->validate((array) $oAuction);

            if( $oRet->HasFailure() )
            {
                header('HTTP/1.1 422 Unprocessable Entity');
                $aRet['error'] = $oRet->GetError();
            }
            else
            {
                $oRet = $this->oModel->insertAuction($oAuction);
                if( $oRet->HasFailure() )
                {
                    header('HTTP/1.1 422 Unprocessable Entity');
                    $aRet['error'] = $oRet->GetError();
                }
                else
                {
                    header('HTTP/1.1 201 Created');
                    $aRet['success'] = 'Successfully added new auction';
                    $aRet['auction'] = $oAuction;
                }
            }
        }
        return json_encode($aRet);
    }

    /**
     * @returns string
     */
    public function getAuctions()
    {
        $aOpts = ['order_by' => 'adjusted_end_time DESC'];
        $this->setFilter($aOpts, $_GET);
        if( isset($aOpts['id']) )
        {
            $aOpts['auction_id'] = $aOpts['id'];
            unset($aOpts['id']);
        }
        $oRet = $this->oModel->getAuction($aOpts);
        if( !$oRet->HasFailure() )
        {
            if( count($oRet->Get()) > 0 )
            {
                $aAuctions = array_map(function ($oAuction) {
                    $aAuction = (array) $oAuction;
                    return $aAuction;
                }, $oRet->Get());
                $c = count($aAuctions);
                $aRet = [
                    'success' => "Found $c auctions",
                    'count' => $c,
                    'auctions' => $aAuctions
                ];
            }
            else
            {
                $aRet = [
                    'success' => 'Found 0 auctions',
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
        return $aRet;
    }
}