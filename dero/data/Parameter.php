<?php

namespace Dero\Data;

/**
 * Classed used to define the parameters for prepared queries
 *
 * @author Ryan Pallas
 *
 * @property string Name
 * @property mixed Value
 * @property int Type
 */
class Parameter
{
    private $Name = '';
    private $Value = '';
    private $Type = 0;

    /**
     * Construct for creating new Parameter
     * @param string $Name the name of the parameter
     * @param string $Value the value to be bound
     * @param int $Type [Optional] Value type (DB_PARAM_INT,DB_PARAM_STR,DB_PARAM_BOOL,DB_PARAM_NULL) [Default: DB_PARAM_STR]
     */
    public function __construct($Name, $Value, $Type = NULL)
    {
        $this->SetName($Name);
        $this->SetValue($Value);
        if( !is_null($Type) ) {
            $this->SetType($Type);
        }
    }

    public function __get($name)
    {
        if( method_exists($this, "Get$name") ) {
            return $this->{"Get$name"}();
        }
        throw new \UnexpectedValueException('Undefined property '.$name.' in '.__CLASS__);
    }

    public function __set($name, $value)
    {
        if( method_exists($this, "Set$name") ) {
            return $this->{"Set$name"}($value);
        }
        throw new \UnexpectedValueException('Undefined property '.$name.' in '.__CLASS__);
    }

    /**
     * Sets the name of the parameter
     * @param string $Name
     * @throws \InvalidArgumentException
     * @return void
     */
    public function SetName($Name)
    {
        if( empty($Name) || !is_string($Name) )
            throw new \InvalidArgumentException('name of parameters must be a string');
        $this->Name = $Name;
    }

    /**
     * Gets the name of the parameter
     * @throws \UnexpectedValueException
     * @return string
     */
    public function GetName()
    {
        return ':' . $this->Name;
    }

    /**
     * Sets the value to be bound
     * @param string|int|null|bool $Value
     * @return void
     */
    public function SetValue($Value)
    {
        if( is_bool($Value) )
            $this->SetType(DB_PARAM_BOOL);
        elseif( is_null($Value) )
            $this->SetType(DB_PARAM_NULL);
        elseif( is_int($Value) )
            $this->SetType(DB_PARAM_INT);
        else
            $this->SetType(DB_PARAM_STR);

        $this->Value = $Value;
    }

    /**
     * Gets the value of the parameter
     * @return string
     */
    public function GetValue()
    {
        return $this->Value;
    }

    /**
     * Sets the type of the parameter
     * @param int $Type One of (DB_PARAM_STR, DB_PARAM_INT, DB_PARAM_BOOl, DB_PARAM_NULL)
     * @throws \InvalidArgumentException
     * @return void
     */
    public function SetType($Type)
    {
        $acceptable = [DB_PARAM_BOOL, DB_PARAM_INT, DB_PARAM_NULL, DB_PARAM_STR];
        if( !in_array($Type, $acceptable, TRUE) )
            throw new \InvalidArgumentException('type of parameters must be DB_PARAM_*');
        $this->Type = $Type;
    }

    /**
     * Gets the type of the parameter, determined by the Engine
     * @throws \UnexpectedValueException
     * @return number
     */
    public function GetType()
    {
        switch($this->Type) {
            case DB_PARAM_INT:
                return \PDO::PARAM_INT;
            case DB_PARAM_BOOL:
                return \PDO::PARAM_BOOL;
            case DB_PARAM_NULL:
                return \PDO::PARAM_NULL;
            case DB_PARAM_STR:
            case DB_PARAM_DEC:
                return \PDO::PARAM_STR;
            default:
                throw new \UnexpectedValueException('Unexpected value found in Parameter::Type for PDO::MySQL');
        }
    }

}

?>