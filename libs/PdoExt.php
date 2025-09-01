<?php

class PdoExt extends PDO
{

    public bool $isConnected = false;
    public int $querycount = 0;
    
    function __construct(string $dsn, string $username, string $password)
    {
        if(!$this->isConnected)
        {
            try
            {
                parent::__construct($dsn, $username, $password);
                $this->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                $this->isConnected = true;
            }
            catch(PDOException $e)
            {
                error_log($e->getMessage());
                $this->isConnected = false;
            }
        }
    }
    
    
    /**
     * Simplifies a database insert query
     * @param string $table table to insert to
     * @param array $data key value pairs of field names and values
     * @param int $lastInsertedId
     * @return bool
     */
    public function insert(string $table, array $data, int &$lastInsertedId = -1): bool
    {
        $fieldNames = implode('`, `', array_keys($data));
        $fieldValues = ':' . implode(', :', array_keys($data));
        
        $query = "INSERT INTO $table (`$fieldNames`) VALUES($fieldValues)";
        
        $dbh = $this->prepare($query);

        foreach($data as $key => $value)
            $dbh->bindValue($key, $value);

        if($dbh->execute() === false)
        {
            print_r($dbh->errorInfo());
            return false;
        }

        $lastInsertedId = $this->lastInsertId();

        return true;        
    }
    
    
    /**
     * Performs a simple query that requires no results
     * @param string $query query to perform with named parameters
     * @param array $data key value pairs named parameters => value
     * @param int $affectedRows
     * @return bool
     */
    public function update(string $query, array $data, int &$affectedRows = 0): bool
    {
        $this->querycount++;

        $dbh = $this->prepare($query);

        foreach($data as $key => $value)
            $dbh->bindValue($key, $value);

        if($dbh->execute() === false)
        {
            print_r($dbh->errorInfo());
            return false;
        }

        $affectedRows = $dbh->rowCount();

        return true;
    }
    
    
    /**
     * Performs a select query that expects multiple results
     * @param string $query query to perform with named parameters
     * @param array $data key value pairs named parameters => value
     * @param int $fetchMode PDO Fetch Mode
     * @return false|array false if error or no results
     */
    public function select(string $query, array $data = [], int $fetchMode = PDO::FETCH_ASSOC): false|array
    {
        $this->querycount++;

        $dbh = $this->prepare($query);

        foreach($data as $key => $value)
            $dbh->bindValue($key, $value);

        if(!$dbh->execute())
            return false;        

        $result = $dbh->fetchAll($fetchMode);

        if(count($result) == 0)
            return false;

        return $result;

    }
    
    
    /**
     * Performs a select query that expects one result
     * @param string $query query to perform with named parameters
     * @param array $data key value pairs named parameters => value
     * @param int $fetchMode PDO Fetch Mode
     * @return false|array false if error or no results
     */
    public function selectOne(string $query, array $data = [], int $fetchMode = PDO::FETCH_ASSOC): false|array
    {
        $this->querycount++;
        
        $dbh = $this->prepare($query);

        foreach($data as $key => $value)
            $dbh->bindValue($key, $value);

        $dbh->execute();
        return $dbh->fetch($fetchMode);

    }

}

