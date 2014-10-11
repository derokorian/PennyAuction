<?php

namespace Dero\Core;

class Retval
{
    private $mRetval = null;
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

    public function AddError($strMessage, \Exception $oException = null)
    {
        $this->strError[] = $strMessage;
        $this->oException[] = $oException;
    }

    public function HasFailure()
    {
        return count($this->strError) > 0;
    }

    public function GetError()
    {
        return count($this->strError) == 0 ? null :
            (count($this->strError) == 1 ? $this->strError[0] :
                $this->strError);
    }

    public function GetException()
    {
        return count($this->oException) == 0 ? null :
            (count($this->oException) == 1 ? $this->oException[0] :
                $this->oException);
    }
} 