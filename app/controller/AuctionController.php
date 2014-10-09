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

class AuctionController extends BaseController
{
    /** @var  \App\Model\AuctionModel */
    private $oModel;

    public function __construct(AuctionModel $oModel)
    {
        $this->oModel = $oModel;
    }
}