<?php

namespace Dero\Data;

/**
 * Iterable container for multiple Parameters
 * @see Parameter
 * @author Ryan Pallas
 */
class ParameterCollection extends Collection
{
    /**
     * Adds a parameter to the collection
     * @param Parameter $aParam
     * @throws
     * @return ParameterCollection
     */
    public function Add ($aParam)
    {
        if( !$aParam instanceof Parameter )
        {
            throw new \InvalidArgumentException('Only Dero\Data\Parameter may be passed to ' . __METHOD__);
        }
        else
        {
            parent::add($aParam);
            return $this;
        }
    }
}
