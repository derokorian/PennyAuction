<?php

namespace Dero\Core;

class BaseController
{
    protected function setFilter(Array &$aOpts, Array $aParams)
    {
        if( isset($aParams['id']) )
        {
            $aOpts['id'] = $aParams['id'];
        }
        if( isset($aParams['name']) )
        {
            $aOpts['name'] = $aParams['name'];
        }
        if( isset($aParams['order']) )
        {
            $aOpts['order_by'] = $aParams['order'];
        }
        if( isset($aParams['rows']) )
        {
            $aOpts['rows'] = $aParams['rows'];
        }
        if( isset($aParams['skip']) )
        {
            $aOpts['skip'] = $aParams['skip'];
        }
    }
}