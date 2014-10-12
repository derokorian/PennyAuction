<?php

namespace Dero\Data;

class Column
{
    private $strColName;
    private $cColType;
    private $iLength = null;
    private $iPrecision = null;
    private $mDefault = null;
    private $bNullable = false;

    /**
     * @param string $strName
     * @param $cType
     * @throws \InvalidArgumentException
     */
    public function __construct($strName, $cType)
    {
        if( is_string($strName) && preg_match('/^[a-z0-9_]+$/i', $strName) )
        {
            $this->strColName = $strName;
        }
        else
        {
            throw new \InvalidArgumentException('Argument 1 to ' . __CLASS__ . '::' . __FUNCTION__ . ' must be a string [a-z0-9_]');
        }

        if( in_array($cType, [COL_TYPE_DATETIME, COL_TYPE_STRING, COL_TYPE_FIXED_STRING,
            COL_TYPE_BOOLEAN, COL_TYPE_INTEGER, COL_TYPE_TEXT, COL_TYPE_DECIMAL]) )
        {
            $this->cColType = $cType;
        }
        else
        {
            throw new \InvalidArgumentException('Argument 2 to ' . __CLASS__ . '::' . __FUNCTION__ . ' must be a constant like COL_TYPE_*');
        }
    }

    /**
     * @param int|null $iLen
     * @returns void|int
     * @throws \UnexpectedValueException
     */
    public function Length($iLen = null)
    {
        if( is_null($iLen) )
        {
            return $this->iLength;
        }
        elseif( is_numeric($iLen) && ceil($iLen) === floor($iLen) )
        {
            $this->iLength = $iLen;
        }
        else
        {
            throw new \UnexpectedValueException(__CLASS__ . '::' . __FUNCTION__ . ' expects no argument, or a whole number.');
        }
    }

    /**
     * @param int|null $iPrec
     * @returns void|int
     * @throws \UnexpectedValueException
     */
    public function Precision($iPrec = null)
    {
        if( is_null($iPrec) )
        {
            return $this->iPrecision;
        }
        elseif( is_numeric($iPrec) && ceil($iPrec) === floor($iPrec) )
        {
            $this->iPrecision = $iPrec;
        }
        else
        {
            throw new \UnexpectedValueException(__CLASS__ . '::' . __FUNCTION__ . ' expects no argument, or a whole number.');
        }
    }

    /**
     * @param null $mDef
     * @return null
     */
    public function DefaultValue($mDef = null)
    {
        if( is_null($mDef) )
        {
            return $this->mDefault;
        }
        // TODO: validate value matches definition
        $this->mDefault = $mDef;
    }

    /**
     * @param null|bool $bVal
     * @returns bool|void
     * @throws \UnexpectedValueException
     */
    public function IsNullable($bVal = null)
    {
        if( is_null($bVal) )
        {
            return $this->bNullable;
        }
        elseif( is_bool($bVal) )
        {
            $this->bNullable = $bVal;
        }
        else
        {
            throw new \UnexpectedValueException(__CLASS__ . '::' . __FUNCTION__ . ' expects no argument, or a boolean.');
        }
    }
}
