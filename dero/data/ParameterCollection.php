<?php

namespace Dero\Data;
use Dero\Core\Collection;

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
    public function Add (Parameter $aParam)
    {
        parent::add($aParam);
        return $this;
    }
}
