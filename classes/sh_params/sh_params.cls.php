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
 * This class allows the other classes to store and restore php datas into files.
 *
 * Use of these classes
 *
 * For a class's param:
 * $this->setParam('truc>bidule', array('machin','chose'));
 * $this->writeParams();
 * print_r($this->getParam());
 *
 * For a file's param:
 * $fileParams = SH_ROOT_FOLDER.'file.php';
 * $this->links->params->addElement($fileParams);
 * $this->links->params->set($fileParams,'truc','bidule');
 * $this->links->params->write($fileParams);
 * $this->links->params->get($fileParams,'truc');
 *
 * In this last case, we may want to write the modifications into the original
 * file or into an other.
 * The default behaviour is to write them into another file.
 * If you want to edit the original file, you should call addElement with the
 * second parametter set to "true".
 */
class sh_params extends sh_core{
    protected $classes = array();

    const VALUE_NOT_SET = 'Value not set';

    /**
     * public function getFileToWrite
     */
    public function getFileToWrite($element){
        return $this->classes[$element]->getFileToWrite();
    }

    /**
     * public function addElement
     */
    public function addElement($className, $forceFile=false){
        if(!isset($this->classes[$className])){
            $this->classes[$className] = new sh_params_element($className,$forceFile);
        }elseif($forceFile != $this->classes[$className]->forceFile){
            // The forcing needs to be changed
            $this->classes[$className] = new sh_params_element($className,$forceFile);
        }
        return true;
    }

    /**
     * public function set
     *
     */
    public function remove($element,$param){
        return $this->classes[$element]->remove($param);
    }

    /**
     * public function set
     *
     */
    public function set($element,$param,$value = ''){
        return $this->classes[$element]->set($param,$value);
    }

    /**
     * public function write
     *
     */
    public function write($element){
        return $this->classes[$element]->write();
    }

    /**
     * public function get
     *
     */
    public function get($element,$param = '',$defaultValue = self::VALUE_NOT_SET){
        if(!isset($this->classes[$element])){
            $this->addElement($element);
        }

        // By ref. to save some memory (does it really do?)
        $elements =& $this->classes[$element]->allParams;

        if($param == ''){
            return $elements;
        }

        // If there is a choice (separated with | in $param),
        // we test the different values one by one, in the order.
        if(strpos($param,'|')){
            $optNames = explode('|',$param);
            foreach($optNames as $param){
                $rep = $this->get($element,$param,$defaultValue);
                if($rep !== $defaultValue){
                    return $rep;
                }
            }
            return $defaultValue;
        }

        // We separate the path
        $tab = explode('>',$param);

        $found = true;
        if(is_array($tab)){
            foreach($tab as $element){
                if(!is_array($elements) || !isset($elements[$element])){
                    $found = false;
                    break;
                }
                $elements = $elements[$element];
            }
        }else{
            echo __FILE__.':'.__LINE__.' - ON PASSE PAR LA<br />';
            if(isset($elements[$param])){
                return $elements[$param];
            }
        }
        if($found){
            return $elements;
        }
        // No answer was found, so we send back the default value
        return $defaultValue;
    }

    /**
     * public function getQueries
     */
    public function getQueries($element){
        return $this->classes[$element]->queries;
    }

    public function __tostring(){
        return get_class();
    }//
}

class sh_params_element{
    /**
     * @var boolean True if $class is a class, false if not
     */
    protected $objectIsAClass = false;

    public $forceFile = false;

    protected $fileToWrite = '';
    protected $onlyWriteDifferences = false;

    protected $values = array();
    protected $queries = array();
    protected $default = array();
    protected $general = array();
    protected $allParams = array();

    public $needsDb = false;

    protected $version = '0.2';

    // Params files names
    const VERSION_PARAMS_FILE = 'version.params.php';
    const GENERAL_PARAMS_FILE = 'general.params.php';
    const DEFAULT_PARAMS_FILE = 'default.params.php';
    const QUERIES_PARAMS_FILE = 'queries.params.php';

    /**
     * public function __construct
     *
     */
    public function __construct($className = null,$forceFile = false){
        $this->className = $className;
        $this->links = sh_links::getInstance();
        $this->load($forceFile);
    }


    /**
     * protected function getFileToWrite
     *
     */
     public function getFileToWrite(){
        return $this->fileToWrite;
    }

    function array_merge_replace(){
        // Holds all the arrays passed
        $params =& func_get_args();
        foreach($params as &$param){
            if(!is_array($param)){
                $param = array();
            }
        }
        if(count($params) == 1){
            return $params[0];
        }
        
        $ret = $params[1];
        foreach($params[0] as $key=>$value){
            if($value == $ret[$key]){
                $ret[$key] = $value;
            }elseif(!isset($ret[$key])){
                $ret[$key] = $value;
            }elseif(is_array($value) && is_array($ret[$key])){
                $ret[$key] = $this->array_merge_replace(
                    $value,
                    $ret[$key]
                );
            }
            // Else, we keep the value
        }

        unset($params[0]);
        unset($params[1]);
        foreach($params as $paramEntry){
            $ret = $this->array_merge_replace(
                $ret,
                $paramEntry
            );
        }

        return $ret;
        
    }

    function array_merge_replace_recursive() {
        // Holds all the arrays passed
        $params =& func_get_args();

        // First array is used as the base, everything else overwrites on it
        $return = array_shift($params);

        // Merge all arrays on the first array
        foreach($params as $array){
            foreach($array as $key => $value){
                // Numeric keyed values are added (unless already there)
                if(is_numeric($key) && (!in_array($value,$return))){
                    if(is_array($value)){
                        $return [] = $this->array_merge_replace_recursive ( $return [$$key], $value );
                    }else{
                        $return [] = $value;
                    }

                // String keyed values are replaced
                }else{
                    if(isset($return[$key]) && is_array($value) && is_array($return[$key])){
                        $return[$key] = $this->array_merge_replace_recursive($return[$$key], $value);
                    }else{
                        $return[$key] = $value;
                    }
                }
            }
        }

        return $return;
    }

    /**
     * protected function load
     *
     */
    protected function load($forceFile = false){
         if(is_dir(SH_CLASS_FOLDER.$this->className)){
            $this->classPath = SH_CLASS_FOLDER.$this->className.'/params/';

            if(file_exists($this->classPath.self::VERSION_PARAMS_FILE)){
                include($this->classPath.self::VERSION_PARAMS_FILE);
            }
            if(file_exists($this->classPath.self::GENERAL_PARAMS_FILE)){
                include($this->classPath.self::GENERAL_PARAMS_FILE);
            }
            if(file_exists($this->classPath.self::DEFAULT_PARAMS_FILE)){
                include($this->classPath.self::DEFAULT_PARAMS_FILE);
            }
            if(file_exists($this->classPath.self::QUERIES_PARAMS_FILE)){
                include($this->classPath.self::QUERIES_PARAMS_FILE);
                $this->needsDb = true;
            }
            $valuesPath = SH_SITEPARAMS_FOLDER.$this->className.'.params.php';
            if(file_exists($valuesPath)){
                include($valuesPath);
            }

            $this->allParams = $this->array_merge_replace(
                $this->default,
                $this->values,
                $this->general
            );
            /*echo '<table width="1200px;"><tr>';
            echo '<td style="width:300px;overflow:auto;">'.nl2br(str_replace(' ','',print_r($this->default,true))).'</td>';
            echo '<td style="width:300px;overflow:auto;">'.nl2br(str_replace(' ','',print_r($this->values,true))).'</td>';
            echo '<td style="width:300px;overflow:auto;">'.nl2br(str_replace(' ','',print_r($this->general,true))).'</td>';
            echo '<td style="width:300px;overflow:auto;">'.nl2br(str_replace(' ','',print_r($this->allParams,true))).'</td>';
            echo '</tr></table><hr/>';*/
            //$this->allParams = array_merge_recursive($this->default,$this->values,$this->general);
            $this->fileToWrite = $valuesPath;
            return true;
        }else{
            if(!$forceFile){
                // We write the differences into another file if we want to edit it
                $this->fileToWrite = $this->getUserFile($this->className);
                $this->onlyWriteDifferences = true;
                if(file_exists($this->className)){
                    include($this->className);
                }
                $this->fileValues = $this->values;
                if(file_exists($this->fileToWrite)){
                    include($this->fileToWrite);
                    if(is_array($this->values)){
                        $this->values = $this->links->helper->array_merge_recursive_replace(
                            $this->fileValues,
                            $this->values
                        );
                    }else{
                        $this->values = $this->fileValues;
                    }
                }
                $this->forceFile = false;
            }else{
                // We edit the original file if we want to edit it
                $this->fileToWrite = $this->className;
                $this->onlyWriteDifferences = false;
                $this->forceFile = true;
                if(file_exists($this->className)){
                    include($this->className);
                }
                $this->fileValues = $this->values;
            }

            $this->allParams = $this->values;
            return true;
        }
    }

    /**
     * protected function getUserFile
     *
     */
    protected function getUserFile($class){
        if(substr($class,0,strlen(SH_SITEPARAMS_FOLDER)) == SH_SITEPARAMS_FOLDER){
            return $class;
        }
        return SH_SITEPARAMS_FOLDER.substr(md5($class),0,10).'.params.php';
    }

    /**
     * public function reload
     *
     */
    public function reload(){
        return $this->load();
    }

    /**
     * public function write
     *
     */
    public function write(){
        if(!$this->onlyWriteDifferences){
            $valuesToWrite = $this->values;
        }else{
            $valuesToWrite = $this->links->helper->array_diff_assoc_recursive(
                $this->values,
                $this->fileValues
            );
        }
        $ret = sh_helper::writeArrayInFile(
            $this->fileToWrite,
            'this->values',
            $valuesToWrite,
            false
        );
        $this->reload();
        return $ret;
    }

    /**
     * public function set
     *
     */
    public function set($param,$value = ''){
        $rep =& $this->values;

        $tab = explode('>',$param);
        if(is_array($tab)){
            foreach($tab as $element){
                if(!is_array($rep[$element])){
                    $rep[$element] = array();
                }
                $rep = &$rep[$element];
            }
        }
        return ($rep = $value);
    }

    /**
     * public function remove
     *
     */
    public function remove($param){
        $rep =& $this->values;

        $tab = explode('>',$param);
        if(is_array($tab)){
            $lastLevel = array_pop($tab);
            foreach($tab as $element){
                if(!is_array($rep[$element])){
                    return true;
                }
                $rep = &$rep[$element];
            }
            unset($rep[$lastLevel]);
        }else{
            unset($rep);
        }
        return true;
    }

    /**
     * public function __get
     *
     */
    public function __get($property){
        if(isset($this->$property)){
            return $this->$property;
        }
        return false;
    }

}
