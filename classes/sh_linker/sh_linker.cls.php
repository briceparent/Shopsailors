<?php
/**
 * @author Brice PARENT (Websailors) for Shopsailors
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
class sh_linker{
    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array();
    /**
     * sh_linker Instance of the class
     */
    public static $instance;

    protected $objects = array();

    /**
     * Constructs the class.<br />
     * As a singleton, this method is protected, to defend from external creating.
     */
    protected function __construct(){
        // This class shouldn't depend on any other, so there is no need for any updating process (replacing this file
        //by a newer version should just work directly
        self::$instance = $this;
        // We construct the classes that need to be built at the beginning
        $this->__get('session');
        $this->__get('site');
    }

    public function getLoadedObjects($onlyNames = true){
        if($onlyNames){
            return array_keys($this->objects);
        }
        return $this->objects;
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
        if(method_exists($objectName, 'getInstance')){
            $object = $objectName::getInstance($objectName);
            $this->$objectName = $object;
            $object->construct();
            $this->objects[$objectName] = $object;
            return $object;
        }
        echo __CLASS__.'::'.__LINE__.' : The class '.$objectName.' does not exist or doesn\'t have a getInstance() static method.<br />';
        
        if($this->admin->isAdmin(true)){
            $debugger = new sh_debugger();
            $debugger->xdebug(__FILE__.' : '.__LINE__);
        }
        return null;
    }

    public function construct(){
        // this method is only usefull to be able to call $this->linker from the other classes,
        //if they want to.
    }

    /**
     * This method returns the short class name of the class $className (without the prefix).
     * @param str $className The long or short class name
     * @return str The short class name
     */
    public function getShortClassName($className){
        if(preg_match('`^('.SH_PREFIX.'|'.SH_CUSTOM_PREFIX.')?(.+)`',$className,$matches)){
            $className = $matches[2];
        }
        return $className;
    }

    /**
     * Creates the real class names with their prefixes (content → sh_content).
     * @param str $objectName The class name we want, short or long.
     * @return str The long class name.
     */
    public function cleanObjectName($objectName){
        if(trim($objectName) == ''){
            echo __CLASS__.'::'.__LINE__.' : No object name was given!<br/>';
            return false;
        }
        // Shopsailors' classes
        $class = trim(preg_replace('`^('.SH_PREFIX.')?(.+)$`',SH_PREFIX.'$2',$objectName));
        if(is_dir(SH_CLASS_FOLDER.$class) || class_exists($class)){
            return $class;
        }
        // Custom classes
        $class = trim(preg_replace('`^('.SH_CUSTOM_PREFIX.')?(.+)$`',SH_CUSTOM_PREFIX.'$2',$objectName));
        if(is_dir(SH_CUSTOM_CLASS_FOLDER.$class) || class_exists($class)){
            return $class;
        }
        echo __CLASS__.'::'.__LINE__.' : No object named "'.$objectName.'" was found!<br/>';
        return $objectName;
    }

    /**
     * public static function getInstance
     * gets the unic instance of this class, and returns it
     * @return sh_linker The instance of sh_linker
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            $calledClass = get_class();
            new $calledClass();
        }
        return self::$instance;
    }

    /**
     * Tests if the method $method exists in the $object class.
     * @param str $object The object name, in the short or long way
     * @param str $method The name of the method we want to try
     * @return bool true if it does, false if not
     */
    public function method_exists($object,$method){
        if(!empty($object)){
            $objectName = $this->cleanObjectName($object);
            if(class_exists($objectName)){
                if(isset($objectName::$privateClass) && $objectName::$privateClass){
                    // We should check if this site may use this class
                    if(!in_array(SH_SITENAME,$objectName::$private_allowedSites)){
                        return false;
                    }
                }
                return method_exists($objectName,$method);
            }
        }
        return false;
    }

    public function __tostring(){
        return 'Linker object ('.__CLASS__.')';
    }
}
