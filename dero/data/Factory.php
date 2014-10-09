<?php

/**
 * Factory for Data instances
 * @author Ryan Pallas
 * @package SampleSite
 * @namespace App\Model
 * @since 2013-12-15
 */

namespace Dero\Data;
use Dero\Core\Config;

class Factory
{
    /**
     * @throws \UnexpectedValueException
     * @param string $InstanceName The name of the connection
     * @return DataInterface
     */
    public static function GetDataInterface($InstanceName)
    {
        return new PDOMysql($InstanceName);
    }
}