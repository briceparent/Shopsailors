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
 * Class that display and manages html contents, like company presentation for example.
 */
class sh_legacy extends sh_core {

    public function getLegacyLine(){
        $values['links'][] = array(
            'link'=>'http://www.websailors.fr',
            'textBefore'=>$this->getI18n('hosterBeforeText'),
            'text'=>$this->getI18n('hosterText'),
            'textAfter'=>$this->getI18n('hosterAfterText')
        );
        $separator = $this->getI18n('separatorInLegacyLine');
        $values['links'][] = array(
            'link'=>$this->links->path->getLink($this->shortClassName.'/show/'),
            'text'=>$this->getI18n('title'),
            'separator'=>$separator
        );
        $cpt = count($values['links']);
        $classes = scandir(SH_CLASS_SHARED_FOLDER.__CLASS__);
        foreach($classes as $class){
            if(substr($class,0,1) != '.' && strtolower(substr($class,-4)) == '.php'){
                $class = substr($class,0,-4);
                if($this->links->method_exists($class,'getLegacyEntry')){
                    $elements = file(SH_CLASS_SHARED_FOLDER.__CLASS__.'/'.$class.'.php');
                    foreach($elements as $element){
                        $val = $this->links->$class->getLegacyEntry(trim($element));
                        if($val){
                            $values['links'][$cpt] = $val;
                            $values['links'][$cpt]['separator'] = $separator;
                            $cpt++;
                        }
                    }
                }
            }
        }
        return $this->render('legacyLine', $values, false, false);
    }

    /**
     * public function show
     */
    public function show(){
        $this->links->html->setTitle($this->getI18n('title'));
        $values['legacy']['content'] = $this->getParam('content', '');
        $this->render('show', $values);
        return true;
    }

    /**
     * public function edit
     */
    public function edit(){
        $this->onlyAdmin();
        $this->links->html->setTitle($this->getI18n('title'));

        if($this->formSubmitted('legacy_edit')){
            $this->setParam('content', stripslashes($_POST['legacy']));
            $this->writeParams();
        }
        $values['legacy']['content'] = $this->getParam('content', '');
        $this->render('edit',$values);
    }

    /**
     * public function sitemap_renew
     *
     */
    public function sitemap_renew(){
        $this->addToSitemap($this->shortClassName.'/show/', 0.2);
        return true;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        if($page == $this->shortClassName.'/edit/'){
            return '/'.$this->shortClassName.'/edit.php';
        }
        if($page == $this->shortClassName.'/show/'){
            return '/'.$this->shortClassName.'/show.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        if($uri == '/'.$this->shortClassName.'/edit.php'){
            return $this->shortClassName.'/edit/';
        }
        if($uri == '/'.$this->shortClassName.'/show.php'){
            return $this->shortClassName.'/show/';
        }
        return false;
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring(){
        return get_class();
    }
}