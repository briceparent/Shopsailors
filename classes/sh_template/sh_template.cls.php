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
class sh_template extends sh_core{
    protected $paramsFile = '';
    const ALL_VALUES = 'get_all_values';

    /**
     * public function construct
     *
     */
    public function construct(){
        $this->paramsFile = $this->links->site->templateFolder.'template.params.php';
        $this->links->params->addElement($this->paramsFile);
        return true;
    }

    public function select(){
        $this->links->javascript->get(sh_javascript::LIGHTWINDOW);
        $this->onlyAdmin();
        if($this->formSubmitted('templateChooser')){
            $this->changeTemplate($_POST['template']);
        }
        if($_SESSION[__CLASS__]['templateHasChanged'] == true){
            $values['template']['changed'] = 'true';
            unset($_SESSION[__CLASS__]['templateHasChanged']);
        }
        $scan =  scandir(SH_TEMPLATE_FOLDER);
        if(is_array($scan)){
            foreach($scan as $element){
                if(preg_match('`(((sh_|cm_)[0-9]*)-(.+))`', $element, $matches)){
                    if($this->links->site->templateIsAuthorized($matches[1])){
                        if($matches[1] == $this->links->site->templateName){
                            $state = 'checked';
                            $values['template']['original'] = $matches[1];
                        }else{
                            $state = '';
                        }

                        $imagesRoot = $this->links->templatesLister->getImagesRoot($element);
                        
                        if(file_exists(SH_TEMPLATE_FOLDER.$matches[1].'/template.description.php')){
                            list($id, $name) = explode('-',$matches[1]);
                            include(SH_TEMPLATE_FOLDER.$matches[1].'/template.description.php');
                            $firstSlide = $template['variations'];
                            $slides = array();
                            if(is_array($template['slides'])){
                                foreach($template['slides'] as $slide){
                                    $slides[]['src'] = $imagesRoot.$slide['src'];
                                }
                            }
                        }

                        $values['templates'][$matches[2]] = array(
                            'name' => $matches[4],
                            'thumbnail' => $imagesRoot.$template['thumbnail'],
                            'completeName' => $matches[1],
                            'state'=>$state,
                            'firstSlide'=>$imagesRoot.$firstSlide,
                            'slides'=>$slides
                        );
                    }
                }
            }
        }
        ksort($values['templates']);
        $this->render('select',$values);
    }

    public function changeTemplate($template){
        $this->onlyAdmin();
        $this->links->site->changeTemplate($template);
        $directory = SH_CLASS_SHARED_FOLDER.__CLASS__.'/change/';
        if(is_dir($directory)){
            $classes = scandir($directory);
            foreach($classes as $class){
                if(substr($class,0,1) != '.'){
                    $className = substr($class,0,-4);
                    // We have found a class on which to call template_change();
                    $this->links->$className->template_change($template);
                }
            }
        }
        $_SESSION[__CLASS__]['templateHasChanged'] = true;
        $this->links->path->refresh();
        return true;
    }

    /**
     * public function get
     *
     */
    public function get($paramName = self::ALL_VALUES, $onNotSet = sh_params::VALUE_NOT_SET){
        if($paramName == self::ALL_VALUES){
            $paramName = '';
        }
        return $this->links->params->get(
            $this->paramsFile,
            $paramName,
            $onNotSet
        );
    }

    /**
     * public function get
     *
     */
    public function setMenuFont($menuId, $font){
        $this->links->params->set(
            $this->paramsFile,
            'menuButtons>'.$menuId.'>font',
            $font
        );
        return $this->links->params->write($this->paramsFile);
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
    public function __get($paramName){
        return $this->get($paramName);
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        list($class,$method,$id) = explode('/',$page);
        if($method == 'select'){
            return '/'.$this->shortClassName.'/select.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        if($uri == '/'.$this->shortClassName.'/select.php'){
            return $this->shortClassName.'/select/';
        }
        return false;
    }

    public function __tostring(){
        return get_class();
    }
}
