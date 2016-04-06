<?php
// ****************************************************************************
/**
 * Nurogames SeaClouds Casestudy
 * 
 * JeDb: just enough database 
 *
 * @author      Christian Tismer, Nurogames GmbH
 * 
 * @copyright   Copyright (c) 2011/2014, Nurogames GmbH
 */
// ****************************************************************************

/**
 * The JeDb database class
 *
 * using the singelton pattern
 * depending on:
 * http://de3.php.net/manual/en/language.oop5.patterns.php
 * https://github.com/Quixotix/PHP-MySQL-Database-Class/blob/master/mysqldatabase.php
 */

//ToDo: upgrade to mysqli or pdo

// CLASS ======================================================================
/**
 * @brief db connection
 */
class JeDb
{
    /// @brief date format
    const MYSQL_DATE_FORMAT = 'Y-m-d';
    
    /// @brief time format
    const MYSQL_TIME_FORMAT = 'H:i:s';
    
    /// @brief date time format
    const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

    /// @brief get auto increment id
    const INSERT_GET_AUTO_INCREMENT_ID = 1;
    
    /// @brief get affected rows
    const INSERT_GET_AFFECTED_ROWS = 2;

    /// @brief data object
    const DATA_OBJECT = 1;
    
    /// @brief numeric array
    const DATA_NUMERIC_ARRAY = 2;
    
    /// @brief associative array
    const DATA_ASSOCIATIVE_ARRAY = 3;
    
    /// @brief array
    const DATA_ARRAY = 4;

    /// @brief instance
    private static $instance;

    /// @brief member db object
    private $m_kDb;

    /// @brief member connectionString
    private $m_sConnectionString;

    // FUNCTIONS ==============================================================
    
    // ------------------------------------------------------------------------
    /**
     * @brief constructs db connection
     */
    private function __construct()
    {
        // $this->m_kDb = @mysql_pconnect(g_DatabaseHost, g_DatabaseUser, g_DatabasePassword);
        $this->m_kDb = mysqli_connect(
            g_DatabaseHost,
            g_DatabaseUser,
            g_DatabasePassword,
            g_DatabaseName
            //,g_DatabasePort
            );

        if ( mysqli_connect_errno())
        {
            throw new Exception(
                'Unable to establish database connection: '
                . 'Error (' . mysqli_connect_errno() . ') '
                . mysqli_connect_error());
        }

        if (!$this->m_kDb)
        {
            print 'FATAL ERROR: Unable to establish database connection: '
//                                .mysql_error());
                                . mysqli_error( $this->m_kDb);
            die;
        }

        if (!$this->m_kDb->autocommit( TRUE))
        {
            print 'FATAL ERROR: Unable to activate autocommit: '
                                . mysqli_error( $this->m_kDb);
            die;
        }

        //if (!@mysql_select_db(g_DatabaseName, $this->m_kDb))
        //{
            //throw new Exception('Unable to select database: ' . mysql_error($this->m_kDb));
        //}

        $this->m_sConnectionString = g_DatabaseUser . '@' . g_DatabaseHost . ':' . g_DatabaseName;

    }

    // ------------------------------------------------------------------------
    /**
     * @brief gets connection string
     * 
     * @return connectionString
     */
    public function getConnectionString()
    {
        return $this->m_sConnectionString;
    }

    // ------------------------------------------------------------------------
    /**
     * @brief gets instance
     * 
     * @return instance
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $className = __CLASS__;
            self::$instance = new $className;
        }
        return self::$instance;
    }

    // ------------------------------------------------------------------------
    /**
     * @brief fetches all query rows
     * 
     * @param $query - mysql query
     * @param $kType - type object
     * 
     * @return all rows
     */
    public function fetchAllRows($query, $kType = NULL)
    {
        $allRows = array();

        //return new MySqlResultSet($sql, $data_type, $this->m_kDb);

        $result = $this->getResult($query);

        if ( $kType)
        {
//            while ( $rowObject = mysql_fetch_array( $result, $kType))
            while ( $rowObject = mysqli_fetch_array( $result, $kType))
            {
                $allRows[] = $rowObject;
            }
        }
        else
        {
//            while ( $rowObject = mysql_fetch_object( $result))
            while ( $rowObject = mysqli_fetch_object( $result))
            {
                $allRows[] = $rowObject;
            }
        }

        return $allRows;

    }

    // ------------------------------------------------------------------------
    /**
     * @brief gets result
     * 
     * @param $query - mysql query
     * 
     * @return result
     * 
     * @throws Exception
     */
    public function getResult($query)
    {
        $allRows = array();

        //return new MySqlResultSet($sql, $data_type, $this->m_kDb);

//        $result = @mysql_query($query);
        $kResult = $this->m_kDb->query( $query);

        if (!$kResult) {
//            throw new Exception(mysql_error());
            throw new Exception( mysqli_error( $this->m_kDb));
        }

//        if (!is_resource($result)
//            || get_resource_type($result) != 'mysql result') {
        if (!is_object( $kResult)
            || get_class( $kResult) != 'mysqli_result') {
print_r( $kResult);
            throw new Exception("Query does not return an mysql result resource. it is :" . get_resource_type( $kResult) );
        }

        return $kResult;

    }

    // ------------------------------------------------------------------------
    /**
     * @brief fetches first row
     * 
     * @param $query - mysql query
     * 
     * @return first row
     */
    public function fetchFirstRow($query)
    {
        $result = $this->getResult($query);

//        return mysql_fetch_object( $result);
        return mysqli_fetch_object( $result);
    }

    // ------------------------------------------------------------------------
    /**
     * @brief insert id
     * 
     * @param $query - mysql query
     * @param $r_type - reference type
     * 
     * @return id
     */
    public function insert($query, $r_type = self::INSERT_GET_AUTO_INCREMENT_ID)
    {
        $r = $this->query( $query);

        if ($r_type == self::INSERT_GET_AFFECTED_ROWS) {
//            return mysql_affected_rows( $this->m_kDb);
            return mysqli_affected_rows( $this->m_kDb);
        } else {
//            return mysql_insert_id( $this->m_kDb);
            return mysqli_insert_id( $this->m_kDb);
        }
    }

    // ------------------------------------------------------------------------
    /**
     * @brief update
     * 
     * @param $query - mysql query
     * 
     * @return updateOrDelete function
     */
    public function update($query)
    {
        return $this->updateOrDelete($query);
    }

    // ------------------------------------------------------------------------
    /**
     * @brief delete
     * 
     * @param $query - mysql query
     * 
     * @return updateOrDelete function
     */
    public function delete($query)
    {
        return $this->updateOrDelete($query);
    }

    // ------------------------------------------------------------------------
    /**
     * @brief update or delete
     * 
     * @param $query - mysql query
     * 
     * @return returns affected rows
     */
    private function updateOrDelete($query)
    {
        $r = $this->query( $query);
//        return @mysql_affected_rows($this->m_kDb);
        return @mysqli_affected_rows( $this->m_kDb);
    }

    // ------------------------------------------------------------------------
    /**
     * @brief gets query
     * 
     * @param $query - mysql query
     * 
     * @return query
     * 
     * @throws Exception
     */
    public function query( $query)
    {
//        $r = @mysql_query($query, $this->m_kDb);
        //$this->m_kDb->begin_transaction ();

        $kResult = $this->m_kDb->query( $query);
        
        //$this->m_kDb->commit();

        if (!$kResult) {
            echo "Mysql-Error:$query \n";
//            throw new Exception("Query Error: " . mysql_error());
            throw new Exception("Query Error: " . mysqli_error( $this->m_kDb));
        }

        return $kResult;
    }

    // ------------------------------------------------------------------------
    /**
     * @brief escape_string
     * 
     * @param $sString
     * 
     * @return string result from real_escape_string( $sString)
     */
    public function escape_string ( $sString)
    {
        return $this->m_kDb->real_escape_string( $sString);
        
    }

    // ------------------------------------------------------------------------
    /**
     * @brief close() Close Database Connection
     */
    public function close()
    {
        $this->m_kDb->commit();

        $this->m_kDb->close();
    }

    // ------------------------------------------------------------------------
    /**
     * @brief triggers error if clone is used
     */
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    // ------------------------------------------------------------------------
    /**
     * @brief triggers error if wakeup is used
     */
    public function __wakeup()
    {
        trigger_error('Unserializing is not allowed.', E_USER_ERROR);
    }
}
?>
