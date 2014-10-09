<?php

namespace Dero\Core;

class RetVal
{
    private $mRetval = null;
    private $iErrorCode = [];
    private $strError = [];
    private $oException = [];

    public function Set($mVal)
    {
        $this->mRetval = $mVal;
    }

    public function Get()
    {
        return $this->mRetval;
    }

    public function AddError($strMessage, \Exception $oException = null, $iCode = null)
    {
        $this->strError[] = $strMessage;
        $this->oException[] = $oException;
        $this->iErrorCode[] = $iCode;
    }

    public function HasFailure()
    {
        return count($this->strError) > 0 || count($this->oException) > 0;
    }

    public function GetError()
    {
        return $this->strError;
    }

    public function GetException()
    {
        return $this->oException;
    }
} 