<?php

/**
 * Bid controller
 * @author Ryan Pallas
 * @package PennyAuction
 * @namespace App\Controller
 * @since 2014-10-08
 */

namespace App\Controller;
use Dero\Core\BaseController;
use App\Model\BidModel;

class BidController extends BaseController
{
    /** @var  \App\Model\BidModel */
    private $oModel;

    public function __construct(BidModel $oModel)
    {
        $this->oModel = $oModel;
    }

    /**
     * @returns array
     */
    public function getBids()
    {
        $aOpts = ['order_by' => 'created DESC'];
        $this->setFilter($aOpts, $_GET);
        if( isset($aOpts['id']) )
        {
            $aOpts['auction_id'] = $aOpts['id'];
            unset($aOpts['id']);
        }
        $oRet = $this->oModel->getBid($aOpts);
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
                    'success' => "Found $c bids",
                    'count' => $c,
                    'auctions' => $aAuctions
                ];
            }
            else
            {
                $aRet = [
                    'success' => 'Found 0 bids',
                    'count' => 0,
                    'auctions' => []
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

    /**
     * @returns array
     */
    public function addBid()
    {
        $aRet = [];
        $oBid = isset($_POST['bid']) && is_array($_POST['bid'])
            ? (object) $_POST['bid'] : null;
        if( is_null($oBid) )
        {
            header('HTTP/1.1 422 Unprocessable Entity');
            $aRet['error'] = "No bid information found";
        }
        elseif( !isset($_SESSION['user_id']) )
        {
            header('HTTP/1.1 403 Forbidden');
            $aRet['error'] = "You must be logged in to create bids";
        }
        else
        {
            $oBid->user_id = $_SESSION['user_id'];

            $oRet = $this->oModel->validate((array) $oBid);

            if( $oRet->HasFailure() )
            {
                header('HTTP/1.1 422 Unprocessable Entity');
                $aRet['error'] = $oRet->GetError();
            }
            else
            {
                $oRet = $this->oModel->insertBid($oBid);
                if( $oRet->HasFailure() )
                {
                    header('HTTP/1.1 422 Unprocessable Entity');
                    $aRet['error'] = $oRet->GetError();
                }
                else
                {
                    header('HTTP/1.1 201 Created');
                    $aRet['success'] = 'Successfully added new bid';
                    $aRet['auction'] = $oBid;
                }
            }
        }
        return $aRet;
    }
}