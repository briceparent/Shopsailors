<?php
/**
 * @author Brice PARENT (Websailors) for Shopsailors
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
    const CLASS_VERSION = '1.1.11.03.29';

    protected $paramsFile = '';
    const ALL_VALUES = 'get_all_values';
    public $shopsailors_dependencies = array(
        'sh_linker','sh_params','sh_db','sh_i18n','sh_renderer','sh_site'
    );

    const SATURATION_NOTSET = false;
    const SATURATION_REALLY_DARK = 'reallyDark';
    const SATURATION_DARK = 'dark';
    const SATURATION_NORMAL = 'normal';
    const SATURATION_SHINY = 'shiny';
    const SATURATION_REALLY_SHINY = 'reallyShiny';

    /**
     * public function construct
     *
     */
    public function construct(){
        $installedVersion = $this->getClassInstalledVersion();
        if($installedVersion != self::CLASS_VERSION){
            // The class datas are not in the same version as this file, or don't exist (installation)
            $this->helper->addClassesSharedMethods('sh_template', 'change',$this->className);
            $this->setClassInstalledVersion(self::CLASS_VERSION);
        }
        $templateFolder = $this->linker->site->templateFolder;
        $variation = $this->linker->site->variation;
        if(file_exists($templateFolder.'variations/'.$variation.'.params.php')){
            $this->paramsFile = $templateFolder.'variations/'.$variation.'.params.php';
        }elseif(file_exists($templateFolder.'variations/default.params.php')){
            $this->paramsFile = $templateFolder.'variations/default.params.php';
        }else{
            $this->paramsFile = $templateFolder.'variations/0.params.php';
        }
        $this->linker->params->addElement($this->paramsFile);
        return true;
    }
    
    public function template_can_set_variation($template){
        
    }
    
    /**
     * This method is automatically called by sh_template when the admin/master
     * changes the template he is using.<br />
     * It does everything that has to be done in this class when it occurs.
     * @param str $template The name of the template that will now be used.
     */
    public function template_change($template){
        $this->prepare(
            $template,
            $this->linker->site->variation,
            $this->linker->site->saturation
        );
    }

    public function prepare($template, $variation, $saturation = self::SATURATION_NOTSET){
        if($saturation == self::SATURATION_NOTSET){
            // Old variations, we do nothing
            return true;
        }
        if($template == ''){
            // We get the template that is in use right now
            $template = $this->linker->site->templateName;
        }
        // We list the classes that have something to do during this step
        $classes = $this->get_shared_methods('change');

        foreach($classes as $class){
            $this->linker->$class->variation_change($template, $variation,$saturation);
        }
    }

    /**
     * public function get
     *
     */
    public function get($paramName = self::ALL_VALUES){
        if($paramName == self::ALL_VALUES){
            $paramName = '';
        }
        return $this->linker->params->get(
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
