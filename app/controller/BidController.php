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

    public function getBids()
    {

    }

    public function addBid()
    {

    }
}