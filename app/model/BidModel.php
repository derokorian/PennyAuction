<?php

/**
 * Bid Model
 * @author Ryan Pallas
 * @package PennyAuction
 * @namespace App\Model
 * @since 2014-10-08
 */
namespace App\Model;

use Dero\Data\BaseModel;
use Dero\Data\DataException;
use Dero\Data\DataInterface;
use Dero\Data\Factory;
use Dero\Data\ParameterCollection;


class BidModel extends BaseModel
{
    protected static $TABLE_NAME = 'bid';

    protected static $COLUMNS = [
        'bid_id' => [
            COL_TYPE => COL_TYPE_INTEGER,
            KEY_TYPE => KEY_TYPE_PRIMARY,
            'required' => false,
            'extra' => [
                DB_AUTO_INCREMENT
            ]
        ],
        'user_id' => [
            COL_TYPE => COL_TYPE_INTEGER,
            KEY_TYPE => KEY_TYPE_FOREIGN,
            'foreign_table' => 'user',
            'foreign_column' => 'user_id',
            'required' => true,
            'validation_pattern' => '/^[0-9]+$/'
        ],
        'auction_id' => [
            COL_TYPE => COL_TYPE_INTEGER,
            KEY_TYPE => KEY_TYPE_FOREIGN,
            'foreign_table' => 'auction',
            'foreign_column' => 'auction_id',
            'required' => true,
            'validation_pattern' => '/^[0-9]+$/'
        ],
        'amount' => [
            COL_TYPE => COL_TYPE_DECIMAL,
            'col_length' => 10,
            'scale' => 2,
            'required' => true,
            'validation_pattern' => '/^[0-9.]+$/i'
        ],
        'created' => [
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

    public function insertBid(&$oBid)
    {
        $oRet = $this->validate((array) $oBid);
        if( !$oRet->HasFailure() )
        {
            $oParams = new ParameterCollection();
            $strSql = 'INSERT INTO `bid` ';
            $strSql .= $this->GenerateInsert($oParams, (array) $oBid);
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
            $oBid->user_id = $oRet->Get();
        }
        return $oRet;
    }
}