<?php

/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if( !defined( 'SH_MARKER' ) ) {
    header( 'location: directCallForbidden.php' );
}

/**
 * class that manages the queries and the database.
 */
class sh_db extends sh_core {

    const CLASS_VERSION = '1.1.11.03.29';

    protected static $needs_db = false;
    protected static $needs_form_verifier = false;
    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params'
    );
    protected $classes = array( );
    protected $host = '';
    protected $user = '';
    protected $password = '';
    protected $db = '';
    protected $prefix;
    protected $activeElement = '';
    public $lastQuery = '';

    /**
     * public function construction
     *
     */
    public function construct() {
        // There is no upgrading process here, because everything is in the database...

        $domain = $_SERVER[ 'SERVER_NAME' ];

        $this->host = $this->linker->params->get( 'sh_db', 'host' );
        $this->user = $this->linker->params->get( 'sh_db', 'user' );
        $this->password = $this->linker->params->get( 'sh_db', 'password' );
        $this->db = $this->linker->params->get( 'sh_db', 'database' );
        $this->prefix = $this->linker->params->get( 'sh_db', 'prefix' );
        return true;
    }

    /**
     * Reloads all the queries for the class $classOrElement
     * @param str $classOrElement The class for which the queries have changed.
     */
    public function updateQueries( $classOrElement ) {
        if( !isset( $this->classes[ $classOrElement ] ) ) {
            $this->addElement( $classOrElement );
        }
        $this->classes[ $classOrElement ]->updateQueries();
    }

    public function getLastError( $className ) {
        if( $this->classes[ $className ]->errorId > 0 ) {
            return array(
                'id' => $this->classes[ $className ]->errorId,
                'text' => $this->classes[ $className ]->error,
                'query' => $this->classes[ $className ]->errorQuery
            );
        }
        return false;
    }

    /**
     * public function addElement
     */
    public function addElement( $className ) {
        if( !isset( $this->classes[ $className ] ) ) {
            $this->classes[ $className ] = new sh_db_element( $className );
        }
        return true;
    }

    public function site_configure_form( $site = '' ) {
        if( !empty( $site ) && $this->isMaster() ) {
            $paramsFile = SH_SITES_FOLDER . $site . '/sh_params/' . __CLASS__ . '.params.php';
            return $this->linker->params->get( $paramsFile, '', array( ) );
        }
        return false;
    }

    public function site_configure_save( $site, $values = array( ), $isNew = false ) {
        if( empty( $values ) ) {
            return false;
        }
        if( $this->isMaster() ) {
            $paramsFile = SH_SITES_FOLDER . $site . '/sh_params/' . __CLASS__ . '.params.php';
        } else {
            $this->linker->html->addMessage( 'Only masters may change database parametters.' );
            return false;
        }
        // We first launch a test connection, because if the connection fails, the all site will stop working...
        $link = @mysql_connect( $values[ 'host' ], $values[ 'user' ], $values[ 'password' ] );
        if( !$link ) {
            $this->linker->html->addMessage( 'Could not connect to database.<br />Database settings not saved.' );
            return false;
        }
        $db_selected = mysql_select_db( $values[ 'database' ], $link );

        if( !$isNew ) {
            if( !$db_selected ) {
                $this->linker->html->addMessage( 'Could not access to database ' . $values[ 'database' ] . '.<br />Database settings not saved.' );
                return false;
            }
            // A query that should always return something on existing sites
            $qry = str_replace( '###', $values[ 'prefix' ], 'SELECT * FROM ###i18n LIMIT 1;' );
            $ret = mysql_query( $qry, $link );
            if( $ret === false ) {
                $this->linker->html->addMessage( 'Could not access the ' . $values[ 'prefix' ] . 'i18n table which should exist!<br />Database settings not saved.' );
                return false;
            }
        }

        $this->linker->params->addElement( $paramsFile, true );
        $this->linker->params->set( $paramsFile, 'host', $values[ 'host' ] );
        $this->linker->params->set( $paramsFile, 'user', $values[ 'user' ] );
        $this->linker->params->set( $paramsFile, 'password', $values[ 'password' ] );
        $this->linker->params->set( $paramsFile, 'database', $values[ 'database' ] );
        $this->linker->params->set( $paramsFile, 'prefix', $values[ 'prefix' ] );
        $this->linker->params->write( $paramsFile );
        // Done
    }

    /**
     * public function query
     *
     */
    public function execute( $element, $qryName, $replacements = array( ), &$qry = '', $dbShouldBeConstructed = false ) {
        $this->debug( 'Entering the method ' . __FUNCTION__, 2, __LINE__ );
        $this->classes[ $element ]->found_rows = false;
        // We get the query's datas
        $qryData = $this->classes[ $element ]->getQuery( $qryName );
        // And verify if it is set
        if( $qryData == sh_db_element::QUERY_DOES_NOT_EXIST ) {
            $this->debug( 'The query "' . $qryName . '" does not exist in the class "' . $element . '"', 0, __LINE__ );
            return false;
        }
        $this->activeElement = $element;
        $qry = $qryData[ 'query' ];
        $type = $qryData[ 'type' ];

        //We replace the elements into the query
        if( !is_array( $replacements ) ) {
            $replacements = array( );
        }
        if( count( $replacements ) > 0 ) {
            foreach( $replacements as $key => $value ) {
                $newReplacements[ '`{' . $key . '}`' ] = $value;
            }
            $qry = preg_replace( array_keys( $newReplacements ), array_values( $newReplacements ), $qry );
        }

        // We replace the prefix by its new value
        $qry = str_replace( '###', $this->prefix, $qry );
        $this->debug( 'Once replaced, the query is [' . $qry . ']', 3, __LINE__ );

        // We execute the query and return the results (depending of query's type)
        $link = $this->connect();
        if( $type == 'get' ) {
            $rep = $this->fetch_assoc( $link, $qry );
        } elseif( $type == 'get_foundRows' ) {
            $rep = $this->fetch_assoc( $link, $qry );
            $found_rows = mysql_query( 'SELECT FOUND_ROWS();', $link );
            $this->classes[ $element ]->found_rows = mysql_result( $found_rows, 0 );
        } else {
            $rep = $this->query( $link, $qry );
            if( $type == 'insert' ) {
                $this->classes[ $element ]->insert_id = mysql_insert_id( $link );
            } elseif( $type == 'count' ) {
                $this->classes[ $element ]->affected_rows = mysql_affected_rows( $link );
            }
        }
        $this->debug( 'Result: ' . print_r( $rep, true ), 3, __LINE__ );
        $this->disconnect( $link );
        if( $rep === false && $this->errorId == 1146 && !$dbShouldBeConstructed ) {
            // We have to ask the class to construct the db.
            if( $this->linker->method_exists( $shortClassName, 'constructDb' ) ) {
                $this->linker->$shortClassName->constructDb();
                $this->execute( $element, $qryName, $replacements, $qry, true );
            }
        }
        $qry = $element . '->query(' . $qryName . ') : ' . "\n" . $qry;
        $this->lastQuery = $qry;
        return $rep;
    }

    public function getFoundRows( $element ) {
        $this->debug( 'Entering the method ' . __FUNCTION__, 2, __LINE__ );
        return $this->classes[ $element ]->found_rows;
    }

    public function getRowCount( $element ) {
        $this->debug( 'Entering the method ' . __FUNCTION__, 2, __LINE__ );
        return $this->classes[ $element ]->affected_rows;
    }

    // Opens a connection to a MySQL server
    protected function connect() {
        $this->debug( 'Entering the method ' . __FUNCTION__, 2, __LINE__ );
        $link = @mysql_connect( $this->host, $this->user, $this->password );
        if( !$link ) {
            die(
                str_replace(
                    SH_ROOT_FOLDER, '/', __FILE__
                ) . ' : ' . __LINE__ . ' - Unable to connect to database...<br />Please contact an administrator.'
            );
            return false;
        }
        return $link;
    }

    /**
     * protected function disconnect
     *
     */
    protected function disconnect( $link ) {
        $this->debug( 'Entering the method ' . __FUNCTION__, 2, __LINE__ );
        return mysql_close( $link );
    }

    /**
     * public function addQuery
     *
     */
    public function addQuery( $element, $queryName, $query ) {
        $this->debug( 'Entering the method ' . __FUNCTION__, 2, __LINE__ );
        return $this->classes[ $element ]->addQuery(
                $queryName, array(
                'query' => $query,
                'type' => 'get'
                )
        );
    }

    // Set the active MySQL database
    protected function select_base( $link ) {
        $this->debug( 'Entering the method ' . __FUNCTION__ . ' for database ' . $this->db, 2, __LINE__ );
        $db_selected = mysql_select_db( $this->db, $link );
        if( !$db_selected ) {
            $this->error = mysql_error( $link );
            return false;
        }
        return $link;
    }

    protected function query( $link, $qry, $db = '', $dontClose = false ) {
        $this->debug( 'Entering the method ' . __FUNCTION__, 2, __LINE__ );
        if( !$this->select_base( $link, $db ) ) {
            return false;
        }
        $ret = mysql_query( $qry, $link );
        if( $ret !== false ) {
            return $ret;
        } else {
            $this->debug( 'Mysql error #' . mysql_errno( $link ) . ': ' . mysql_error( $link ), 0, __LINE__ );
            $this->classes[ $this->activeElement ]->error = mysql_error( $link );
            $this->classes[ $this->activeElement ]->errorId = mysql_errno( $link );
            $this->classes[ $this->activeElement ]->errorQuery = $qry;
            return false;
        }
    }

    public function insert_id( $element ) {
        $this->debug( 'Entering the method ' . __FUNCTION__, 2, __LINE__ );
        return $this->classes[ $element ]->insert_id;
    }

    protected function fetch_assoc( $link, $qry = null ) {
        $this->debug( 'Entering the method ' . __FUNCTION__, 2, __LINE__ );

        if( $qry && !is_resource( $qry ) ) {
            $this->debug( 'We launch the query [' . $qry . ']', 3, __LINE__ );
            $results = $this->query( $link, $qry );
        }
        if( is_resource( $results ) ) {
            $this->debug( 'Number of results: ' . mysql_num_rows( $results ), 3, __LINE__ );
            $ret = array( );
            while( $item = mysql_fetch_assoc( $results ) ) {
                $this->debug( '- ' . $item, 3, __LINE__ );
                $ret[ ] = $item;
            }
            return $ret;
        } else {
            $this->classes[ $this->activeElement ]->errorId = mysql_errno( $link );
            $this->classes[ $this->activeElement ]->error = mysql_error( $link );
            $this->classes[ $this->activeElement ]->errorQuery = $qry;
            return false;
        }
    }

    public function unicReturn( $element, $query ) {
        $this->debug( 'Entering the method ' . __FUNCTION__, 2, __LINE__ );
        return $this->execute( $element, $query );
    }

    public function __tostring() {
        return get_class();
    }

}

class sh_db_element {

    protected $className = '';
    protected $queries = array( );
    protected $queriesAreRead = false;
    public $insert_id = '';
    public $affected_rows = 0;
    public $found_rows = false;
    public $errorId = 0;
    public $error = '';
    public $errorQuery = '';

    const QUERY_DOES_NOT_EXIST = 'The query does not exist!';
    const QUERY_ALREADY_EXIST = 'The query could not be added, because it already exists!';

    /**
     * public function __construct
     *
     */
    public function __construct( $className ) {
        $this->linker = sh_linker::getInstance();
        $this->className = $className;
    }

    /**
     * public function getQuery
     *
     */
    public function getQuery( $queryName ) {
        if( !$this->queriesAreRead ) {
            $this->getQueries();
        }
        if( isset( $this->queries[ $queryName ] ) ) {
            return $this->queries[ $queryName ];
        }
        return self::QUERY_DOES_NOT_EXIST;
    }

    public function updateQueries() {
        $this->queriesAreRead = false;
        $this->queries = array( );
        $this->linker->params->updateQueries( $this->className );
        $this->getQueries();
    }

    /**
     * public function addQuery
     *
     */
    public function addQuery( $queryName, $query ) {
        if( !isset( $this->queries[ $queryName ] ) ) {
            $this->queries[ $queryName ] = $query;
            return true;
        }
        return self::QUERY_ALREADY_EXIST;
    }

    /**
     * public function load
     *
     */
    public function getQueries() {
        if( !$this->queriesAreRead ) {
            $this->queries = $this->linker->params->getQueries( $this->className );
            $this->queriesAreRead = true;
        }
        return $this->queries;
    }

}

