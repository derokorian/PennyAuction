<?php

/**
 * Auction Model
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


class AuctionModel extends BaseModel
{
    protected static $TABLE_NAME = 'auction';

    protected static $COLUMNS = [
        'auction_id' => [
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
        'item_id' => [
            COL_TYPE => COL_TYPE_INTEGER,
            KEY_TYPE => KEY_TYPE_FOREIGN,
            'foreign_table' => 'item',
            'foreign_column' => 'item_id',
            'required' => true,
            'validation_pattern' => '/^[0-9]+$/'
        ],
        'min_amount' => [
            COL_TYPE => COL_TYPE_DECIMAL,
            'col_length' => 10,
            'scale' => 2,
            'required' => false,
            'validation_pattern' => '/^[0-9.]+$/i'
        ],
        'original_end_time' => [
            COL_TYPE => COL_TYPE_DATETIME,
            'required' => true
        ],
        'adjusted_end_time' => [
            COL_TYPE => COL_TYPE_DATETIME,
            'required' => true
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

    public function insertAuction(&$oAuction)
    {
        $oRet = $this->validate((array) $oAuction);
        if( !$oRet->HasFailure() )
        {
            $oParams = new ParameterCollection();
            $strSql = 'INSERT INTO `auction` ';
            $strSql .= $this->GenerateInsert($oParams, (array) $oAuction);
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
            $oAuction->auction_id = $oRet->Get();
        }
        return $oRet;
    }
}