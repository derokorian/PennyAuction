<?php

namespace Dero\Data;
use Dero\Core\Config;

/**
 * PDO wrapper for MySQL
 * @author Ryan Pallas
 * @package DeroFramework
 * @namespace dero\Data
 * @see DataInterface
 */
class PDOMysql implements DataInterface
{
    /**
     * @var \PDOStatement
     */
    protected $oPDOStatement;
    protected $sInstance;
    protected $oInstance;

    public function __construct($Instance = NULL)
    {
        if( is_null($Instance) || !is_string($Instance) )
            throw new \InvalidArgumentException(__CLASS__ . ' requires an instance');
        else
            $this->sInstance = $Instance;

        if( !extension_loaded('pdo_mysql') )
            throw new \Exception('PDO_MySQL is not loaded - please check the server\'s configuration');
    }

    /**
     * Opens a connection for a query
     */
    protected function OpenConnection($bIsRead)
    {
        if( $this->oPDOStatement ) unset($this->oPDOStatement);
        $sType = NULL;
        if( !is_null(Config::GetValue('database', $this->sInstance, 'write')) &&
            !is_null(Config::GetValue('database', $this->sInstance, 'read')))
        {
            if( $bIsRead )
            {
                if( $this->oInstance['read'] )
                    return $this->oInstance['read'];
                $sType = 'read';
            }
            else
            {
                if( $this->oInstance['write'] )
                    return $this->oInstance['write'];
                $sType = 'write';
            }
        }
        elseif( !is_null(Config::GetValue('database', $this->sInstance, 'read')))
        {
            if( $this->oInstance['read'] )
                return $this->oInstance['read'];
            $sType = 'read';
        }
        elseif( !is_null(Config::GetValue('database', $this->sInstance, 'write')))
        {
            if( $this->oInstance['write'] )
                return $this->oInstance['write'];
            $sType = 'write';
        }
        elseif( !is_null(Config::GetValue('database', $this->sInstance, 'name')))
        {
            if( isset($this->oInstance) )
                return $this->oInstance;
        }
        else
        {
            throw new \UnexpectedValueException('Database connection information not properly defined');
        }
        if( is_null($sType) )
        {
            $aOpts['Name'] = Config::GetValue('database', $this->sInstance,'name');
            $aOpts['Host'] = Config::GetValue('database', $this->sInstance,'host');
            $aOpts['User'] = Config::GetValue('database', $this->sInstance,'user');
            $aOpts['Pass'] = Config::GetValue('database', $this->sInstance,'pass');
            $aOpts['Port'] = Config::GetValue('database', $this->sInstance,'port') ?: 3306;
            if( in_array(null, $aOpts) )
            {
                throw new \UnexpectedValueException('Database connection information not properly defined');
            }
            try {
                $this->oInstance = new \PDO(
                    sprintf(
                        'mysql:dbname=%s;host=%s;port=%d',
                        $aOpts['Name'],
                        $aOpts['Host'],
                        $aOpts['Port']
                    ),
                    $aOpts['User'],
                    $aOpts['Pass']
                );
                return $this->oInstance;
            } catch (\PDOException $e) {
                throw new DataException("Unable to open database connection\n" . var_export($aOpts, true), 0, $e);
            }
        }
        else
        {
            $aOpts['Name'] = Config::GetValue('database', $this->sInstance, $sType, 'name');
            $aOpts['Host'] = Config::GetValue('database', $this->sInstance, $sType, 'host');
            $aOpts['User'] = Config::GetValue('database', $this->sInstance, $sType, 'user');
            $aOpts['Pass'] = Config::GetValue('database', $this->sInstance, $sType, 'pass');
            $aOpts['Port'] = Config::GetValue('database', $this->sInstance, $sType, 'port') ?: 3306;
            if( in_array(null, $aOpts) )
            {
                throw new \UnexpectedValueException('Database connection information not properly defined');
            }
            try {
                $this->oInstance[$sType] = new \PDO(
                    sprintf(
                        'mysql:dbname=%s;host=%s;port=%d',
                        $aOpts['Name'],
                        $aOpts['Host'],
                        $aOpts['Port']
                    ),
                    $aOpts['User'],
                    $aOpts['Pass']
                );
                return $this->oInstance[$sType];
            } catch (\PDOException $e) {
                throw new DataException("Unable to open database connection\n" . var_export($aOpts, true), 0, $e);
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @param string $Query
     * @return PDOMysql allows method chaining
     * @throws DataException
     */
    public function Prepare($Query)
    {
        static::LogQuery($Query);
        $oCon = $this->OpenConnection(substr(trim($Query), 0, 6) == 'SELECT');
        try
        {
            $this->oPDOStatement = $oCon->prepare($Query);
            if( $this->oPDOStatement === FALSE )
            {
                $e = $oCon->errorInfo();
                throw new DataException('Error preparing query in '. __CLASS__ . '::'
                    . __FUNCTION__ . '(' . $e[2] . ')');
            }
            return $this;
        }
        catch (\Exception $e)
        {
            throw new DataException('Error preparing query in '. __CLASS__ . '::' . __FUNCTION__, 0, $e);
        }

    }

    /**
     * (non-PHPdoc)
     * @param string $Query
     * @return PDOMysql allows method chaining
     * @throws DataException
     */
    public function Query($Query)
    {
        static::LogQuery($Query);
        $oCon = $this->OpenConnection(substr(trim($Query), 0, 6) == 'SELECT');
        try
        {
            $this->oPDOStatement = $oCon->query($Query);
            if( $this->oPDOStatement === FALSE )
            {
                $e = $oCon->errorInfo();
                throw new DataException('Error preparing query in '. __CLASS__ . '::'
                    . __FUNCTION__ . '(' . $e[2] . ')');
            }
            return $this;
        }
        catch (\Exception $e)
        {
            throw new DataException('Error preparing query in '. __CLASS__ . '::' . __FUNCTION__, 0, $e);
        }
    }

    public static function LogQuery($strQuery)
    {
        static $i = 0;
        if( !IS_DEBUG )
            return;

        if( PHP_SAPI == 'cli' )
        {
            file_put_contents(
                '/tmp/query-'.date('ymdhm').'.log',
                sprintf("query(%d): %s\n", $i++, $strQuery),
                FILE_APPEND
            );
        }
        else
        {
            header(sprintf('x-query(%d): %s', $i++, $strQuery));
        }
    }

    /**
     * (non-PHPdoc)
     * @see DataInterface::BindParams()
     * @param ParameterCollection $Params
     * @return PDOMysql allows method chaining
     * @throws DataException
     */
    public function BindParams(ParameterCollection $Params)
    {
        foreach( $Params as $Param )
        {
            if( $Param instanceof Parameter)
            {
                $this->BindParam($Param);
            }
        }
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see DataInterface::BindParam()
     * @param Parameter $Param
     * @return PDOMysql allows method chaining
     * @throws DataException
     */
    public function BindParam(Parameter $Param)
    {
        if(! $this->oPDOStatement ) return $this;
        try {
            $this->oPDOStatement->bindValue($Param->Name, $Param->Value, $Param->Type);
            return $this;
        } catch(\Exception $e) {
            throw new DataException('unable to bind parameter '. $e->getMessage());
        }
    }

    /**
     * (non-PHPdoc)
     * @see DataInterface::Execute()
     * @return PDOMysql allows method chaining
     * @throws DataException
     */
    public function Execute()
    {
        if(! $this->oPDOStatement ) return $this;
        try {
            $this->oPDOStatement->execute();
            return $this;
        } catch( \PDOException $e) {
            throw new DataException('Error executing query in '. __CLASS__ .'::'. __FUNCTION__, 0, $e);
        }
    }

    /**
     * (non-PHPdoc)
     * @see DataInterface::GetRow()
     * @return \StdClass object with properties mapped to selected columns
     */
    public function Get()
    {
        if(! $this->oPDOStatement ) return FALSE;
        return $this->oPDOStatement->fetch(\PDO::FETCH_OBJ);
    }

    /**
     * (non-PHPdoc)
     * @see DataInterface::GetAll()
     * @return Array(StandardObject) array of objects with properties mapped to selected columns
     */
    public function GetAll()
    {
        if(! $this->oPDOStatement ) return FALSE;
        return $this->oPDOStatement->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * (non-PHPdoc)
     * @see DataInterface::GetScalar()
     * @return mixed
     */
    public function GetScalar()
    {
        if(! $this->oPDOStatement ) return FALSE;
        return $this->oPDOStatement->fetch(\PDO::FETCH_NUM)[0];
    }
}

