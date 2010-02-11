<?php
/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')){header('location: directCallForbidden.php');}

/**
 * class that manages the queries and the database.
 */
class sh_db extends sh_core{
    protected $classes = array();

    protected $host = '';
    protected $user = '';
    protected $password = '';
    protected $db = '';
    protected $prefix;

    /**
     * public function construction
     *
     */
    public function construct(){
        $domain = $_SERVER['SERVER_NAME'];

        $this->host = $this->links->params->get('sh_db','host');
        $this->user = $this->links->params->get('sh_db','user');
        $this->password = $this->links->params->get('sh_db','password');
        $this->db = $this->links->params->get('sh_db','database');
        $this->prefix = $this->links->params->get('sh_db','prefix');
        return true;
    }

    /**
     * public function addElement
     */
    public function addElement($className){
        if(!isset($this->classes[$className])){
            $this->classes[$className] = new sh_db_element($className);
        }
        return true;
    }

    /**
     * public function query
     *
     */
    public function execute($element,$qryName,$replacements = array(),&$qry = '', $dbShouldBeConstructed = false){
        $this->debug('Entering the method '.__FUNCTION__, 2, __LINE__);
        // We get the query's datas
        $qryData = $this->classes[$element]->getQuery($qryName);
        // And verify if it is set
        if($qryData == sh_db_element::QUERY_DOES_NOT_EXIST){
            $this->debug('The query "'.$qryName.'" does not exist in the class "'.$element.'"', 0, __LINE__);
            return false;
        }
        $qry = $qryData['query'];
        $type = $qryData['type'];

        //We replace the elements into the query
        if(!is_array($replacements)){
            $replacements = array();
        }
        if(count($replacements) > 0){
            foreach($replacements as $key=>$value){
                $newReplacements['`{'.$key.'}`'] = $value;
            }
            $qry = preg_replace(array_keys($newReplacements),array_values($newReplacements),$qry);
        }

        // We replace the prefix by its new value
        $qry = str_replace('###',$this->prefix,$qry);
        $this->debug('Once replaced, the query is ['.$qry.']', 3, __LINE__);

        // We execute the query and return the results (depending of query's type)
        $link = $this->connect();
        if($type == 'get'){
            $rep = $this->fetch_assoc($link,$qry);
        }else{
            $rep = $this->query($link,$qry);
            if($type == 'insert'){
                $this->classes[$element]->insert_id = mysql_insert_id($link);
            }
        }
        $this->debug('Result: '.print_r($rep,true), 3, __LINE__);
        $this->disconnect($link);
        if($rep === false && $this->errorId == 1146 && !$dbShouldBeConstructed){
            // We have to ask the class to construct the db.
            if(method_exists($this->links->$shortClassName,'constructDb')){
                $this->links->$shortClassName->constructDb();
                $this->execute($element,$qryName,$replacements,&$qry,true);
            }
        }
        return $rep;
    }

    // Opens a connection to a MySQL server
    protected function connect(){
        $this->debug('Entering the method '.__FUNCTION__, 2, __LINE__);
        $link = mysql_connect($this->host,$this->user,$this->password);
        if (!$link) {
            $this->error= mysql_error($link);
            return false;
        }
        return $link;
    }

    /**
     * protected function disconnect
     *
     */
    protected function disconnect($link){
        $this->debug('Entering the method '.__FUNCTION__, 2, __LINE__);
        return mysql_close($link);
    }

    /**
     * public function addQuery
     *
     */
    public function addQuery($element,$queryName,$query){
        $this->debug('Entering the method '.__FUNCTION__, 2, __LINE__);
        return $this->classes[$element]->addQuery(
            $queryName,
            array(
                'query' => $query,
                'type'=>'get'
            )
        );
    }

    // Set the active MySQL database
    protected function select_base($link){
        $this->debug('Entering the method '.__FUNCTION__, 2, __LINE__);
        $this->debug('Selecting database '.$this->db, 3, __LINE__);
        $db_selected = mysql_select_db($this->db, $link);
        if (!$db_selected) {
            $this->error= mysql_error($link);
            return false;
        }
        return $link;
    }

    protected function query($link,$qry,$db = '',$dontClose = false) {
        $this->debug('Entering the method '.__FUNCTION__, 2, __LINE__);
        if(!$this->select_base($link,$db)){
            return false;
        }
        $ret = mysql_query($qry, $link);
        if($ret !== false){
            return $ret;
        }else{
            $this->debug('Mysql error #'.mysql_errno($link).': '.mysql_error($link), 0, __LINE__);
            $this->error = mysql_error($link);
            $this->errorId = mysql_errno($link);
            return false;
        }
    }

    public function insert_id($element){
        $this->debug('Entering the method '.__FUNCTION__, 2, __LINE__);
        return $this->classes[$element]->insert_id;
    }

    protected function fetch_assoc($link,$qry = null) {
        $this->debug('Entering the method '.__FUNCTION__, 2, __LINE__);

        if($qry && ! is_resource($qry)){
            $this->debug('We launch the query ['.$qry.']', 3, __LINE__);
            $results = $this->query($link,$qry);
        }
        if(is_resource($results)){
            $this->debug('Number of results: '.mysql_num_rows($results), 3, __LINE__);
            while($item = mysql_fetch_assoc($results)){
                $this->debug('- '.$item, 3, __LINE__);
                $ret[] = $item;
            }
            return  $ret;
        }else {
            $this->error = mysql_error($link);
            return false;
        }
    }

    public function unicReturn($element,$query){
        $this->debug('Entering the method '.__FUNCTION__, 2, __LINE__);
        return $this->execute($element,$query);
    }

    public function __tostring(){
        return get_class();
    }
}


class sh_db_element{
    protected $className = '';
    protected $queries = array();
    protected $queriesAreRead = false;

    public $insert_id = '';

    const QUERY_DOES_NOT_EXIST = 'The query does not exist!';
    const QUERY_ALREADY_EXIST = 'The query could not be added, because it already exists!';

    /**
     * public function __construct
     *
     */
    public function __construct($className){
        $this->links = sh_links::getInstance();
        $this->className = $className;
    }

    /**
     * public function getQuery
     *
     */
    public function getQuery($queryName){
        if(!$this->queriesAreRead){
            $this->getQueries();
        }
        if(isset($this->queries[$queryName])){
            return $this->queries[$queryName];
        }
        return self::QUERY_DOES_NOT_EXIST;
    }

    /**
     * public function addQuery
     *
     */
    public function addQuery($queryName,$query){
        if(!isset($this->queries[$queryName])){
            $this->queries[$queryName] = $query;
            return true;
        }
        return self::QUERY_ALREADY_EXIST;
    }

    /**
     * public function load
     *
     */
    public function getQueries(){
        if(!$this->queriesAreRead){
            $this->queries = $this->links->params->getQueries($this->className);
            $this->queriesAreRead = true;
        }
        return $this->queries;
    }
}

