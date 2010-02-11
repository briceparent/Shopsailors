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
 * Linker object.
 * Will contain the links to all objects extendinging the sh_core class
 */
class sh_links{
    /**
     * @var object Instance of the class
     */
    public static $instance;
    /**
     * @var Array Array of all the singletons
     */
    protected $allObjects = array();

    /**
     * Constructs the class.<br />
     * As a singleton, this method is protected, to defend from external creating.
     */
    protected function __construct(){
        self::$instance = $this;
        // We construct the classes that need to be built at the beginning
        $this->__get('session');
        $this->__get('site');
    }

    /**
     * Gets the object named as in the arguments.<br />
     * The name of the object is stranslated to start with SH_PREFIX.
     * @param string $objectName The name of the object, starting with SH_PREFIX or not
     * @return object|boolean
     * <ul><li>The object that was asked for.</li>
     * <li>False if none was found.</li></ul>
     */
    public function __get($objectName){
        $objectName = $this->cleanObjectName($objectName);
        if(isset($this->$objectName) && is_object($this->$objectName)){
            return $this->$objectName;
        }
        if(file_exists(SH_CLASS_SHARED_FOLDER.__CLASS__.'/'.$objectName.'.builder.php')){
           include(SH_CLASS_SHARED_FOLDER.__CLASS__.'/'.$objectName.'.builder.php');
            if(function_exists('get_'.$objectName)){
                $func = 'get_'.$objectName;
                $object = $func();
                $this->$objectName = $object;
                $object->construct();
                return $object;
            }else{
                echo __CLASS__.'::'.__LINE__.': Error in the function get_'.$objectName.'() that should create the object<br />';
                return null;
            }
        }
        echo 'The file '.SH_CLASS_SHARED_FOLDER.__CLASS__.'/'.$objectName.'.builder.php does not exist<br />';
        // We create a new object, and return it
        echo __CLASS__.'::'.__LINE__.': The object "'.$objectName.'" may not be constructed clearly, so we don\'t construct it.<br />';
        return null;
    }

    public function cleanObjectName($objectName){
        if(trim($objectName) == ''){
            echo 'ERROR: No object name was given!<br/>';
            return false;
        }
        // Shopsailors' classes
        $class = trim(preg_replace('`^('.SH_PREFIX.')?(.+)$`',SH_PREFIX.'$2',$objectName));
        if(is_dir(SH_CLASS_FOLDER.$class) || class_exists($class)){
            return $class;
        }
        // Custom classes
        $class = trim(preg_replace('`^('.SH_CUSTOM_PREFIX.')?(.+)$`',SH_CUSTOM_PREFIX.'$2',$objectName));
        if(is_dir(SH_CLASS_FOLDER.$class) || class_exists($class)){
            return $class;
        }
        echo 'ERROR: No object named "'.$objectName.'" was found!<br/>';
        return $objectName;
    }

    /**
     * public function getAllObjects
     *
     */
    public function getAllObjects(){
        return $this->allObjects;
    }

    /**
     * public static function getInstance
     * gets the unic instance of this class, and returns it
     * @return object The instance of sh_l
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            $calledClass = get_class();
            new $calledClass();
        }
        return self::$instance;
    }

    public function method_exists($object,$method){
        $objectName = $this->cleanObjectName($object);
        return method_exists($objectName,$method);
    }

    public function __tostring(){
        return 'Linker object ('.__CLASS__.')';
    }
}
