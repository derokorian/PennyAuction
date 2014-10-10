<?php

namespace Dero\Data;
use Dero\Core\RetVal;

/**
 * Base Model class from which all models inherit
 * @author Ryan Pallas
 */
abstract class BaseModel
{
    /**
     * @var DataInterface
     */
    protected $DB;
    /**
     * @var string
     */
    protected static $TABLE_NAME = '';
    /**
     * @var array
     */
    protected static $COLUMNS = [];

    /**
     * @const Used in queries that do a group concatenation
     */
    const CONCAT_SEPARATOR = '--|--';


    /**
     * Initializes a instance of BaseModel
     * @param DataInterface $DB
     */
    public function __construct(DataInterface $DB)
    {
        $this->DB = $DB;
    }

    /**
     * @param array $aVars
     * @return RetVal
     */
    public function validate(Array $aVars)
    {
        $oRetVal = new RetVal();
        foreach( static::$COLUMNS as $strCol => $aCol )
        {
            if( isset($aCol['required']) &&
                $aCol['required'] === true &&
                !isset($aVars[$strCol]) )
            {
                $oRetVal->AddError($strCol . ' is required.');
            }

            if( isset($aCol['col_length']) &&
                isset($aVars[$strCol]) &&
                strlen($aVars[$strCol]) > $aCol['col_length'] )
            {
                $oRetVal->AddError($strCol . ' is longer than max length (' . $aCol['col_length'] . ')');
            }

            if( isset($aCol['validation_pattern']) &&
                isset($aVars[$strCol]) &&
                !preg_match($aCol['validation_pattern'],$aVars[$strCol]) )
            {
                $oRetVal->AddError($strCol . ' did not validate');
            }
        }
        return $oRetVal;
    }

    /**
     * Returns either WHERE or AND for sql
     * First call after resetting returns WHERE, the rest returns AND
     * @param bool $reset True to return where on next call
     * @return void|string
     */
    private function where($reset = FALSE)
    {
        static $Where;
        if( $reset )
        {
            $Where = FALSE;
            return null;
        }
        if( $Where === FALSE )
        {
            $Where = TRUE;
            return 'WHERE ';
        }
        return 'AND ';
    }

    protected function GenerateInsert(ParameterCollection &$oParams, Array $aOpts)
    {
        $strCols = '(';
        $strVals = '(';
        foreach( static::$COLUMNS as $name => $def)
        {
            if( isset($aOpts[$name]) )
            {
                $type = $this->getParamTypeFromColType($aOpts[$name], $def);
                $oParams->Add(new Parameter($name, $aOpts[$name], $type));
                $strCols .= sprintf('`%s`,', $name);
                $strVals .= sprintf(':%s,', $name);
            }
            elseif( strtolower($name) == 'created' || strtolower($name) == 'modified' )
            {
                $strCols .= sprintf('`%s`,', $name);
                $strVals .= 'NOW(),';
            }
        }
        return substr($strCols, 0, -1) . ') VALUES ' . substr($strVals, 0, -1) . ')';
    }

    protected  function GenerateUpdate(ParameterCollection &$oParams, Array $aOpts)
    {
        $strRet = 'SET ';
        $strIdField = '';
        $cIdType = null;
        foreach( static::$COLUMNS as $name => $def)
        {
            if( strtolower($name) == 'created' )
            {
                continue;
            }
            elseif( isset($def[KEY_TYPE]) && $def[KEY_TYPE] === KEY_TYPE_PRIMARY )
            {
                $strIdField = $name;
                $cIdType =  $this->getParamTypeFromColType($aOpts[$name], $def);
            }
            elseif( isset($aOpts[$name]) )
            {
                $type = $this->getParamTypeFromColType($aOpts[$name], $def);
                $oParams->Add(new Parameter($name, $aOpts[$name], $type));
                $strRet .= sprintf("`%s` = :%s,", $name, $name);
            }
            elseif( strtolower($name) == 'modified' )
            {
                $strRet .= sprintf('`%s` = NOW(),', $name);
            }
        }
        $strRet = substr($strRet, 0, -1) . ' ';
        if( strlen($strIdField) > 0 && isset($aOpts[$strIdField]) )
        {
            $oParams->Add(new Parameter($strIdField, $aOpts[$strIdField], $cIdType));
            $strRet .= sprintf("WHERE `%s` = :%s", $strIdField, $strIdField);
        }
        return $strRet;
    }


    /**
     * @param ParameterCollection $oParams
     * @param array $aOpts
     * @param string $strColPrefix
     * @return string
     */
    protected function GenerateCriteria(ParameterCollection &$oParams, Array $aOpts, $strColPrefix = '')
    {
        $this->where(true);
        $sql = '';
        foreach( static::$COLUMNS as $name => $def)
        {
            if( isset($aOpts[$name]) )
            {
                $type = $this->getParamTypeFromColType($aOpts[$name], $def);
                if( $type === DB_PARAM_NULL && $aOpts[$name] === null )
                {
                    $sql .= sprintf('%s %s%s IS NULL ',
                        $this->where(),
                        $strColPrefix,
                        $name
                    );
                }
                else
                {
                    $sql .= sprintf('%s %s%s=:%s ',
                        $this->where(),
                        $strColPrefix,
                        $name,
                        $name
                    );
                    $oParams->Add(new Parameter($name, $aOpts[$name], $type));
                }
            }
        }

        if( isset($aOpts['order_by']) &&
            ((strpos($aOpts['order_by'], ' ') !== FALSE &&
              isset(static::$COLUMNS[substr($aOpts['order_by'], 0, strpos($aOpts['order_by'], ' '))])) ||
            isset(static::$COLUMNS[$aOpts['order_by']])) )
        {
            $sql .= 'ORDER BY ' . $strColPrefix . $aOpts['order_by'] . ' ';
        }

        if( isset($aOpts['rows']) )
        {
            $sql .= 'LIMIT :rows ';
            $oParams->Add(new Parameter('rows', $aOpts['rows'], DB_PARAM_INT));
        }

        if( isset($aOpts['skip']) )
        {
            $sql .= 'OFFSET :skip ';
            $oParams->Add(new Parameter('skip', $aOpts['skip'], DB_PARAM_INT));
        }

        return $sql;
    }

    /**
     * @param $val
     * @param $def
     * @return null|string
     * @throws \UnexpectedValueException
     */
    protected function getParamTypeFromColType($val, $def)
    {
        $return = NULL;
        if( isset($def['nullable']) && $def['nullable'] && is_bool($val))
        {
            $return = DB_PARAM_NULL;
        }
        elseif( isset($def[COL_TYPE]) )
        {
            if( $def[COL_TYPE] == COL_TYPE_BOOLEAN )
                $return = DB_PARAM_BOOL;
            elseif( $def[COL_TYPE] == COL_TYPE_INTEGER )
                $return = DB_PARAM_INT;
            elseif( $def[COL_TYPE] == COL_TYPE_DECIMAL )
                $return = DB_PARAM_DEC;
            else
                $return = DB_PARAM_STR;
        }
        if( $return === NULL )
        {
            throw new \UnexpectedValueException('Unknown column definition');
        }
        return $return;
    }

    /**
     * Generates the SQL to create table defined by class properties
     * @return null|string
     * @throws \UnexpectedValueException
     */
    public function GenerateCreateTable()
    {
        if( strlen(static::$TABLE_NAME) == 0 || count(static::$COLUMNS) == 0)
            return null;

        $strCreate = 'CREATE TABLE IF NOT EXISTS `' . static::$TABLE_NAME . '` (';
        foreach( static::$COLUMNS as $strCol => $aCol )
        {
            $sType = '';
            $sKey = '';
            $sExtra = '';
            switch($aCol[COL_TYPE])
            {
                case COL_TYPE_BOOLEAN:
                    $sType = 'TINYINT(1)';
                    break;
                case COL_TYPE_DATETIME;
                    $sType = 'DATETIME';
                    break;
                case COL_TYPE_DECIMAL:
                    if( isset($aCol['col_length']) && isset($aCol['scale']) )
                    {
                        $sType = sprintf('DECIMAL(%d, %d)', $aCol['col_length'], $aCol['scale']);
                    }
                    else
                    {
                        $sType = 'DECIMAL(10, 4)';
                    }
                    break;
                case COL_TYPE_INTEGER:
                    $sType = 'INT';
                    if( isset($aCol['col_length']) && is_numeric($aCol['col_length']) )
                    {
                        $sType .= sprintf("(%d)", $aCol['col_length']);
                    }
                    break;
                case COL_TYPE_TEXT:
                    $sType = 'TEXT';
                    break;
                case COL_TYPE_STRING:
                    if( isset($aCol['col_length']) && is_numeric($aCol['col_length']) )
                    {
                        $sType = sprintf("VARCHAR(%d)", $aCol['col_length']);
                    }
                    else
                    {
                        throw new \UnexpectedValueException(
                            'Bad column definition. COL_TYPE_STRING requires col_length be set.');
                    }
                    break;
                case COL_TYPE_FIXED_STRING:
                    if( isset($aCol['col_length']) && is_numeric($aCol['col_length']) )
                    {
                        $sType = sprintf("CHAR(%d)", $aCol['col_length']);
                    }
                    else
                    {
                        throw new \UnexpectedValueException(
                            'Bad column definition. COL_TYPE_STRING requires col_length be set.');
                    }
                    break;
            }
            if( isset($aCol['extra']) &&
                is_array($aCol['extra']) )
            {
                if( in_array(DB_NULLABLE, $aCol['extra']) )
                {
                    $sExtra .= 'NULL ';
                }
                else
                {
                    $sExtra .= 'NOT NULL ';
                }

                if( in_array(DB_AUTO_INCREMENT, $aCol['extra']) )
                {
                    $sExtra .= 'auto_increment ';
                }
                $def = preg_grep('/^default.*$/i', $aCol['extra']);
                if( count($def) > 0 )
                {
                    $sExtra .= $def[0];
                }
            }
            else
            {
                $sExtra .= 'NOT NULL ';
            }

            if( isset($aCol[KEY_TYPE]) )
            {
                switch($aCol[KEY_TYPE])
                {
                    case KEY_TYPE_PRIMARY:
                        $sKey = 'PRIMARY KEY';
                        break;
                    case KEY_TYPE_UNIQUE:
                        $sKey = 'UNIQUE';
                        break;
                    case KEY_TYPE_FOREIGN:
                        if( isset($aCol['foreign_table']) &&
                            isset($aCol['foreign_column']) )
                        {
                            $sKey = sprintf(
                                ",\n\t\tFOREIGN KEY %s_%s (%s)\n\t\t\tREFERENCES `%s` (%s)",
                                $aCol['foreign_table'],
                                $aCol['foreign_column'],
                                $strCol,
                                $aCol['foreign_table'],
                                $aCol['foreign_column']
                            );
                        }
                }
            }

            $strCreate .= sprintf(
                "\n\t`%s` %s %s %s,",
                $strCol,
                $sType,
                $sExtra,
                $sKey
            );
        }
        $strCreate = substr($strCreate, 0, -1) . "\n) Engine=InnoDB";
        return $strCreate;
    }


    /**
     * Executes the create table script returned by GenerateCreateTable
     * @returns \Dero\Core\RetVal
     */
    public function CreateTable()
    {
        $oRetVal = new RetVal();
        $strSql = $this->GenerateCreateTable();
        try {
            $oRetVal->Set($this->DB->Query($strSql));
        } catch (DataException $e) {
            $oRetVal->AddError('Unable to query database', $e);
        }
        return $oRetVal;
    }

    /**
     * Verifies the current tables definition and updates if necessary
     * @throws \UnexpectedValueException
     * @returns \Dero\Core\RetVal
     */
    public function VerifyTableDefinition()
    {
        $oRetVal = new RetVal();
        $strSql = sprintf("SHOW TABLES LIKE '%s'", static::$TABLE_NAME);
        try {
            $oRetVal->Set(
                $this->DB
                    ->Query($strSql)
                    ->GetAll()
            );
        } catch (DataException $e) {
            $oRetVal->AddError('Unable to query database', $e);
            return $oRetVal;
        }
        if( count($oRetVal->Get()) == 0 )
        {
            $oRetVal = $this->CreateTable();
            return $oRetVal;
        }
        $strSql = sprintf('DESCRIBE `%s`', static::$TABLE_NAME);
        try {
            $oRetVal->Set(
                $this->DB
                    ->Query($strSql)
                    ->GetAll()
            );
        } catch (DataException $e) {
            $oRetVal->AddError('Unable to query database', $e);
            return $oRetVal;
        }
        $aRet = [];
        $strUpdate = 'ALTER TABLE `' . static::$TABLE_NAME . '` ';
        $aTableCols = array_map(function($el) { return (array)$el; }, $oRetVal->Get());
        $aTableCols = array_combine(
            array_column($aTableCols, 'Field'),
            array_values($aTableCols)
        );
        foreach( static::$COLUMNS as $strCol => $aCol )
        {
            $bColMissing = false;
            $bColWrong = false;
            if( !isset($aTableCols[$strCol]) )
            {
                // Column is missing, add it
                $bColMissing = true;
                $aRet[] = "Adding column $strCol";
                $strUpdate .= 'ADD COLUMN ';
            }
            else
            {
                $aColMatch = $aTableCols[$strCol];
                switch($aCol[COL_TYPE])
                {
                    case COL_TYPE_DATETIME:
                        if( $aColMatch['Type'] !== 'datetime' )
                        {
                            $bColWrong = true;
                        }
                        break;
                    case COL_TYPE_DECIMAL:
                        if( isset($aCol['col_length']) && isset($aCol['scale']) )
                        {
                            $sType = sprintf('decimal(%d,%d)', $aCol['col_length'], $aCol['scale']);
                        }
                        else
                        {
                            $sType = 'decimal(10,4)';
                        }
                        if( $aColMatch['Type'] !== $sType )
                        {
                            $bColWrong = true;
                        }
                        break;
                    case COL_TYPE_TEXT:
                        if( $aColMatch['Type'] !== 'text' )
                        {
                            $bColWrong = true;
                        }
                        break;
                    case COL_TYPE_INTEGER:
                        $sType = sprintf(
                            "int(%d)",
                            (isset($aCol['col_length']) && is_numeric($aCol['col_length'])
                                ? $aCol['col_length']
                                : 11
                            )
                        );
                        if( $aColMatch['Type'] !== $sType )
                        {
                            $bColWrong = true;
                        }
                        break;
                    case COL_TYPE_BOOLEAN:
                        if( $aColMatch['Type'] !== 'tinyint(1)' )
                        {
                            $bColWrong = true;
                        }
                        break;
                    case COL_TYPE_STRING:
                        if( isset($aCol['col_length']) && is_numeric($aCol['col_length']) )
                        {
                            $sType = sprintf("varchar(%d)", $aCol['col_length']);
                            if( $aColMatch['Type'] !== $sType )
                            {
                                $bColWrong = true;
                            }
                        }
                        else
                        {
                            throw new \UnexpectedValueException(
                                'Bad column definition. COL_TYPE_STRING requires col_length be set.');
                        }
                        break;
                    case COL_TYPE_FIXED_STRING:
                        if( isset($aCol['col_length']) && is_numeric($aCol['col_length']) )
                        {
                            $sType = sprintf("char(%d)", $aCol['col_length']);
                            if( $aColMatch['Type'] !== $sType )
                            {
                                $bColWrong = true;
                            }
                        }
                        else
                        {
                            throw new \UnexpectedValueException(
                                'Bad column definition. COL_TYPE_STRING requires col_length be set.');
                        }
                        break;
                }

                if( isset($aCol['extra']) &&
                    is_array($aCol['extra']) )
                {
                    if( in_array(DB_NULLABLE, $aCol['extra']) )
                    {
                        if( $aColMatch['Null'] != 'YES' )
                        {
                            $bColWrong = true;
                        }
                    }
                    else
                    {
                        if( $aColMatch['Null'] != 'NO' )
                        {
                            $bColWrong = true;
                        }
                    }

                    if( in_array(DB_AUTO_INCREMENT, $aCol['extra']) &&
                        !strpos($aColMatch['Extra'], 'auto_increment') > -1 )
                    {
                        $bColWrong = true;
                    }

                    $def = preg_grep('/^default.*$/i', $aCol['extra']);
                    if( count($def) > 0 )
                    {
                        if( $aColMatch['Default'] != $def[0] )
                        {
                            $bColWrong = true;
                        }
                    }
                }
                else
                {
                    if( $aColMatch['Null'] != 'NO' )
                    {
                        $bColWrong = true;
                    }
                }

                if( isset($aCol[KEY_TYPE]) )
                {
                    switch($aCol[KEY_TYPE])
                    {
                        case KEY_TYPE_PRIMARY:
                            if( $aColMatch['Key'] != 'PRI' )
                            {
                                $bColWrong = true;
                            }
                            break;
                        case KEY_TYPE_UNIQUE:
                            if( $aColMatch['Key'] != 'UNI' )
                            {
                                $bColWrong = true;
                            }
                            break;
                        case KEY_TYPE_FOREIGN:
                            if( $aColMatch['Key'] != 'MUL' )
                            {
                                $bColWrong = true;
                            }
                            break;
                    }
                }

                if( $bColWrong )
                {
                    $aRet[] = "Updating column $strCol";
                    $strUpdate .= 'CHANGE COLUMN ';
                }
            }
            if( $bColWrong || $bColMissing )
            {
                $sType = '';
                $sKey = '';
                $sExtra = '';
                switch($aCol[COL_TYPE])
                {
                    case COL_TYPE_BOOLEAN:
                        $sType = 'TINYINT(1)';
                        break;
                    case COL_TYPE_DATETIME;
                        $sType = 'DATETIME';
                        break;
                    case COL_TYPE_DECIMAL:
                        if( isset($aCol['col_length']) && isset($aCol['scale']) )
                        {
                            $sType = sprintf('DECIMAL(%d, %d)', $aCol['col_length'], $aCol['scale']);
                        }
                        else
                        {
                            $sType = 'DECIMAL(10, 4)';
                        }
                        break;
                    case COL_TYPE_INTEGER:
                        $sType = 'INT';
                        if( isset($aCol['col_length']) && is_numeric($aCol['col_length']) )
                        {
                            $sType .= sprintf("(%d)", $aCol['col_length']);
                        }
                        break;
                    case COL_TYPE_TEXT:
                        $sType = 'TEXT';
                        break;
                    case COL_TYPE_STRING:
                        if( isset($aCol['col_length']) && is_numeric($aCol['col_length']) )
                        {
                            $sType = sprintf("VARCHAR(%d)", $aCol['col_length']);
                        }
                        else
                        {
                            throw new \UnexpectedValueException(
                                'Bad column definition. COL_TYPE_STRING requires col_length be set.');
                        }
                        break;
                    case COL_TYPE_FIXED_STRING:
                        if( isset($aCol['col_length']) && is_numeric($aCol['col_length']) )
                        {
                            $sType = sprintf("CHAR(%d)", $aCol['col_length']);
                        }
                        else
                        {
                            throw new \UnexpectedValueException(
                                'Bad column definition. COL_TYPE_STRING requires col_length be set.');
                        }
                        break;
                }
                if( isset($aCol['extra']) &&
                    is_array($aCol['extra']) )
                {
                    if( in_array(DB_NULLABLE, $aCol['extra']) )
                    {
                        $sExtra .= 'NULL ';
                    }
                    else
                    {
                        $sExtra .= 'NOT NULL ';
                    }

                    if( in_array(DB_AUTO_INCREMENT, $aCol['extra']) )
                    {
                        $sExtra .= 'auto_increment ';
                    }
                    $def = preg_grep('/^default.*$/i', $aCol['extra']);
                    if( count($def) > 0 )
                    {
                        $sExtra .= $def[0];
                    }
                }
                else
                {
                    $sExtra .= 'NOT NULL ';
                }

                if( isset($aCol[KEY_TYPE]) )
                {
                    switch($aCol[KEY_TYPE])
                    {
                        case KEY_TYPE_PRIMARY:
                            $sKey = 'PRIMARY KEY';
                            break;
                        case KEY_TYPE_UNIQUE:
                            $sKey = 'UNIQUE';
                            break;
                        case KEY_TYPE_FOREIGN:
                            if( isset($aCol['foreign_table']) &&
                                isset($aCol['foreign_column']) )
                            {
                                $sKey = sprintf(
                                    ",\n\t\tFOREIGN KEY %s_%s (%s)\n\t\t\tREFERENCES `%s` (%s)",
                                    $aCol['foreign_table'],
                                    $aCol['foreign_column'],
                                    $strCol,
                                    $aCol['foreign_table'],
                                    $aCol['foreign_column']
                                );
                            }
                    }
                }

                $strUpdate .= sprintf(
                    "\n\t`%s` %s %s %s,",
                    ($bColWrong ?
                        sprintf("%s` `%s", $strCol, $strCol)
                        : $strCol),
                    $sType,
                    $sExtra,
                    $sKey
                );
            }
            unset($strCol, $aCol);
        }
        $strUpdate = substr($strUpdate, 0, -1);
        if( count($aRet) > 0 )
        {
            try
            {
                $oRetVal->Set('Updating ' . static::$TABLE_NAME);
                $this->DB->Query($strUpdate);
                $oRetVal->Set(array_merge($aRet, ['message' => static::$TABLE_NAME . ' has been updated']));
            } catch (\Exception $e) {
                $oRetVal->AddError('Error updating table ' . static::$TABLE_NAME, $e);
            }
        }
        else
        {
            $oRetVal->Set(static::$TABLE_NAME . ' is up to date');
        }
        return $oRetVal;
    }
}