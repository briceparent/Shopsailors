<?php
/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')) {header('location: directCallForbidden.php');}

/**
 * Class that manages the params shared for all classes of a single website.
 */
class sh_variation extends sh_core{
    protected $paramsFile = '';
    const ALL_VALUES = 'get_all_values';

    /**
     * public function construct
     *
     */
    public function construct(){
        $templateFolder = $this->links->site->templateFolder;
        $variation = $this->links->site->variation;
        if(file_exists($templateFolder.'variations/params/'.$variation.'.params.php')){
            $this->paramsFile = $templateFolder.'variations/params/'.$variation.'.params.php';
        }elseif(file_exists()){
            $this->paramsFile = $templateFolder.'variations/params/default.params.php';
        }else{
            $this->paramsFile = $templateFolder.'variations/params/0.params.php';
        }
        $this->links->params->addElement($this->paramsFile);
        return true;
    }

    /**
     * public function get
     *
     */
    public function get($paramName = self::ALL_VALUES){
        if($paramName == self::ALL_VALUES){
            $paramName = '';
        }
        return $this->links->params->get(
            $this->paramsFile,
            $paramName
        );
    }

    /**
     * Returns a site's parametter.
     * @example
     * <code>
     * echo $sh_site->defaultTitle;<br />
     * // Will echo the containt of $this->defaultTitle,<br />
     * // even if it is protected or private.<br />
     * // In that cases, it allows to get the values, <br />
     * // but not to set them.
     * </code>
     * @param string $name The name of the param that should be read
     * @return mixed
     * Returns the value of the param, if found, or false
     */
    public function __get($paramName = self::ALL_VALUES){
        if($paramName == self::ALL_VALUES){
            $paramName = '';
        }
        return $this->get($paramName);
    }

    public function __tostring(){
        return get_class();
    }
}
