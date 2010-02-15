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
 * Class that manages the translations and internationalisation.
 */
class sh_i18n extends sh_core{
    protected $minimal = array('getSelector'=>true);

    protected $translations = array();
    protected $lang = '';

    public function construct(){
        $this->defaultLang = 'fr_FR';
        $this->setLang();
        $lang = $this->getLang();
        $this->renderer_addConstants(
            array(
                'lang'=>$lang,
                'xmlLang'=>array_shift(explode('_',$lang))
            ),
            false
        );
    }
    
    public function isEmpty($array = array()){
        if(is_array($array)){
            $atLeastOne = false;
            foreach($array as $lang => $value){
                $atLeastOne = $atLeastOne || (trim($value) != '');
                if($atLeastOne){
                    return false;
                }
            }
        }
        return true;
    }

    public function checkUnstranslated(){
        $this->onlyMaster();
        $classes = scandir(SH_CLASS_FOLDER);
        sort($classes);
        foreach($classes as $class){
            $languages = array();
            if(substr($class,0,3) == SH_PREFIX){
                if(is_dir(SH_CLASS_FOLDER.$class.'/i18n')){
                    $i18nDir = SH_CLASS_FOLDER.$class.'/i18n/';
                    $langs = scandir($i18nDir);
                    sort($langs);
                    foreach($langs as $lang){
                        if(is_dir($i18nDir.$lang) && substr($lang,0,1) != '.'){
                            $languages[] = $lang;
                            $files = scandir($i18nDir.$lang);
                            sort($files);
                            foreach($files as $file){
                                if(substr($file,-4) == '.php'){
                                    include($i18nDir.$lang.'/'.$file);
                                    ksort($i18n);
                                    foreach($i18n as $key=>$value){
                                        $entries[$class][$file][$key][$lang] = true;
                                        $entriesValues[$class.$file.$key] = $value;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if(is_array($entries[$class])){
                foreach($entries[$class] as $fileName=>$file){
                    $echoHr = false;
                    $toTheEnd = '';
                    foreach($file as $keyName=>$key){
                        foreach($languages as $language){
                            if(!$key[$language]){
                                echo '<b>'.$class.'</b> -> <b>'.$fileName.'</b> -> <b>'.$keyName.'</b> is not set in <b>'.$language.'</b><br />';
                                $toTheEnd .= '\''.$keyName.'\' => \''.htmlspecialchars($entriesValues[$class.$fileName.$keyName]).'\','."\n";
                                $echoHr = true;
                            }
                        }
                    }
                    if($echoHr){
                        echo '<textarea style="width:800px;height:200px;">'.$toTheEnd.'</textarea>';
                        echo '<hr />';
                    }
                }
            }
        }
    }

    /**
     * public function getLanguageSelector
     *
     */
    public function getLanguageSelector(){
        $site = $this->links->site;
        $langs = $site->langs;
        $lang = $this->getLang();
        if(is_array($langs)){
            foreach($langs as $oneLang){
                if($lang != $oneLang){
                    // We update the args list to create the url
                    $this->links->path->parsed_url['parsed_query']['lang'] = $oneLang;
                    $args = '';
                    $separator = '';
                    foreach($this->links->path->parsed_url['parsed_query'] as $argName=>$argValue){
                        $args .= $separator.$argName.'='.$argValue;
                        $separator = '&';
                    }
                    $destPage = $this->links->path->uri.'?'.$args;

                    $values['language'][] = array(
                        'image'=>'/images/shared/flags/'.$oneLang.'.png',
                        'desc'=>$this->getParam('show_in_'.$oneLang,'View in '.$oneLang),
                        'link'=>$destPage
                    );
                }
            }
            $this->renderer = $this->links->renderer;
            return $this->render('select_language',$values,false,false);
        }
        return false;
    }

    /**
     * public function getSelectorValues
     *
     */
    public function getSelectorValues($id){
        if(isset($_SESSION[__CLASS__.'changer'][$id])){
            return $_SESSION[__CLASS__.'changer'][$id]['selected'];
        }
        return array();
    }


    /**
     * public function getSelector
     *
     */
    public function getSelector(){
        $this->links->cache->disable();
        $this->renderer = $this->links->renderer;
        $id = $_GET['id'];

        if($_SESSION[__CLASS__.'changer'][$id]['onEnabledOnly']){
            $site = $this->links->site;
            $langs = $site->langs;
        }else{
            $availableLanguages = scandir(SH_SHAREDIMAGES_FOLDER.'flags');
            $languages = '';
            foreach($availableLanguages as $oneImage){
                list($lang,$ext) = explode('.',$oneImage);
                if(substr($lang,-(strlen('_small'))) != '_small'){
                    if(substr($oneImage,0,1) != '.' && strtolower($ext) == 'png'){
                        $langs[] = $lang;
                    }
                }
            }
        }

        $flagsRoot = SH_SHAREDIMAGES_PATH.'flags/';

        if($this->formSubmitted('i18n_changer')){
            $_SESSION[__CLASS__.'changer'][$id]['selected'] = array();

            foreach($_POST as $postElement){
                if(in_array($postElement,$langs)){
                    $html .= '<img src="'.$flagsRoot.$postElement.'.png" alt="'.$postElement.'" title="'.$postElement.'"/>';
                    $_SESSION[__CLASS__.'changer'][$id]['selected'][] = $postElement;
                }
            }
            $values['response']['container'] = $id;
            $values['response']['content'] = $html;
            echo $this->render('send_values', $values, false, false);
            return true;
        }

        $values['params']['type'] = $_SESSION[__CLASS__.'changer'][$id]['type'];


        foreach($langs as $oneLang){
            if(in_array($oneLang,$_SESSION[__CLASS__.'changer'][$id]['selected'])){
                $state = 'checked';
            }else{
                $state = '';
            }
            if($values['params']['type'] == 'checkbox'){
                $inputName = $oneLang;
            }else{
                $inputName = $id.'_value';
                $values['selector']['inputName'] = $inputName;
            }
            $values['langs'][] = array(
                'flag' => $flagsRoot.$oneLang.'.png',
                'language' => $oneLang,
                'languageName' => $this->get(__CLASS__,'lang_'.$oneLang),
                'state'=>$state,
                'inputName' => $inputName
            );
        }

        $values['selector']['id'] = $id;
        $values['flags']['root'] = $flagsRoot;

        echo $this->render('get_selector', $values, false, false);
        return true;
    }

    /**
     * public function createSelector
     *
     */
    public function createSelector($id,$default,$type = 'checkbox',$onEnabledOnly = true){
        $this->renderer = $this->links->renderer;

        $_SESSION[__CLASS__.'changer'][$id]['type'] = $type;
        $_SESSION[__CLASS__.'changer'][$id]['onEnabledOnly'] = $onEnabledOnly;
        $_SESSION[__CLASS__.'changer'][$id]['selected'] = array();
        if(is_array($default)){
            foreach($default as $lang){
                $flag = SH_SHAREDIMAGES_FOLDER.'flags/'.$lang.'.png';
                $values['langs'][] = array(
                    'flag' => $flag,
                    'language' => $lang,
                    'languageName' => $this->get(__CLASS__,'lang_'.$lang)
                );
                $_SESSION[__CLASS__.'changer'][$id]['selected'][] = $lang;
            }
        }

        $values['action']['container'] = $id;
        $values['action']['langsChanger'] = 'changeI18nValues(\''.$id.'\');';

        $this->links->html->addScript($this->getSinglePath().'changeContent.js');
        return $this->render('viewer',$values,false,false);
    }

    /**
     * public function db_get
     *
     */
    public function db_get($class, $id,$lang = null,$qry = false){
        if(is_null($lang)){
            $lang = $this->getLang();
        }
        if($lang != $this->links->site->lang){
            $tryThisNext = $this->links->site->lang;
        }
        list($text) = $this->db_execute('get',array('class'=>$class,'id'=>$id,'lang'=>$lang),$qry);

        if(trim($text['text']) == ''){
            if($tryThisNext){
                list($text) = $this->db_execute('get',array('class'=>$class,'id'=>$id,'lang'=>$tryThisNext),$qry);
            }
        }
        return $text['text'];
    }

   /**
     * public function db_set
     *
     */
    public function db_set($class, $id, $value, $lang = NULL, $force = false){
        if($id == 0){
            list($rep) = $this->db_execute('getMax',array('class'=>$class));
            $id = $rep['id'] + 1;
        }
        if(is_null($lang)){
            $lang = $this->getLang();
        }
        $this->db_execute('remove',array('class'=>$class,'id'=>$id,'lang'=>$lang));
        if(!empty($value) || $force){
            $this->db_execute('set',array('class'=>$class,'id'=>$id,'value'=>$value,'lang'=>$lang));
        }
        return $id;
    }

    /**
     * public function get
     * @param string $className Name of the class that calls this function
     * @param string $varName Name of the text we want to get in the actual language
     * @return string The text translated
     */
    public function get($className,$varName, $lang = null){
        if(is_null($lang)){
            $lang = $this->getLang();
        }
        if(is_numeric($varName)){
            return $this->db_get($className,$varName,$lang);
        }
        $varName = strtolower($varName);
        if($className == ''){
            $this->debug('The class name was not filled, we can\'t read the values in that case',0,__LINE__);
            $className = __CLASS__;
        }else{
            $className = $this->links->cleanObjectName($className);
        }
        $this->debug('We get the value for "'.$varName.'" in the class "'.$className.'"',2,__LINE__);
        if(!is_array($this->translations[$className][$lang])){
            $this->readLangFile($className,$lang);
        }
        if(isset($this->translations[$className][$lang][$varName])){
            $this->debug('This value is "'.$this->translations[$className][$lang][$varName].'"',2,__LINE__);
            return $this->translations[$className][$lang][$varName];
        }
        if($className != __CLASS__){
            $ret = $this->get(__CLASS__,$varName,$lang);
            if($ret !== false){
                return $ret;
            }
        }
        if(file_exists(SH_CLASS_FOLDER.$className.'/i18n/default.php')){
            include(SH_CLASS_FOLDER.$className.'/i18n/default.php');
            if($defaultLang != $lang){
                return $this->get($className, $varName, $defaultLang);
            }
        }

        return false;
    }

    /**
     * public function set
     *
     */
    public function set($className,$varName,$value,$lang = null,$force = false){
      if(is_null($lang)){
            $lang = $this->getLang();
      }
      if(is_numeric($varName)){
          // We set the value in the database
            if(is_array($value)){
                $old = $this->get($className, $varName, $this->links->site->lang);
                foreach($value as $entryName=>$entryValue){
                    $forceIt = $force;
                    if($entryName == $this->links->site->lang){
                        // If it is the default language, we have to write a
                        // value, even if it is an empty string
                        $forceIt = true;
                    }
                    if($entryValue == $value[$this->links->site->lang] || $entryValue == $old){
                        if($entryName != $this->links->site->lang){
                            $entryValue = '';
                        }
                    }
                    
                    $ret = $this->db_set($className,$varName,$entryValue,$entryName, $forceIt);
                    if($varName == 0){
                        $varName = $ret;
                    }
                }
                return $ret;
            }else{
                return $this->db_set($className,$varName, $value, $lang);
            }
        }
        // This method cannot write into the i18n files, but only into the i18n table of the database
        return false;
    }

    /**
     * public function set
     *
     */
    public function remove($className,$varName,$lang = 'all'){
        if(is_numeric($varName)){
            if(is_null($lang) || $lang == 'all'){
                $this->db_execute('removeAll', array('id'=>$varName,'class'=>$className),$qry);
                return true;
            }
            $this->db_execute('remove', array('id'=>$varName,'class'=>$className,'lang'=>$lang));
        }
        // This method cannot write into the i18n files, but only into the i18n table of the database
        return false;
    }

    /**
     * public function setLang
     *
     */
    public function setLang($moveEntries = false){
        $site = $this->links->site;
        $langs = $site->langs;
        
        if(isset($_GET['lang'])){
            $lang = $_GET['lang'];
        }elseif(!empty($_SESSION[__CLASS__]['lang'])){
            $lang = $_SESSION[__CLASS__]['lang'];
        }else{
            $lang = $site->lang;
        }
        if(!is_array($langs) || !in_array($lang,$langs)){
            $lang = $site->lang;
        }

        $_SESSION[__CLASS__]['lang'] = $lang;
        return true;
    }

    /**
     * protected function readLangFile
     *
     */
    protected function readLangFile($class, $lang = null){
        $this->debug('We look for the translation files for the class "'.$class.'"',2,__LINE__);
        if(is_null($lang)){
            $lang = $this->getLang();
            if(!is_dir(SH_CLASS_FOLDER.$class)){
                $this->debug('The folder that should contain the lang files for class "'.$class.'" was not found',0,__LINE__);
                return false;
            }
        }
        $folder = SH_CLASS_FOLDER.$class.'/i18n/'.$lang;
        $this->debug('Looking for "'.$folder.'" ',1,__LINE__);
        if(!is_dir($folder)){
            $this->debug('The "'.$class.'" class has no translation files for '.$lang,1,__LINE__);
            return false;
        }
        $scan =  scandir($folder);
        if(!is_array($scan)){
            $this->debug('The folder "'.$folder.'/'.$element.'" does not exist',1,__LINE__);
            return false;
        }
        $temp = array();
        foreach($scan as $element){
            if(substr($element,0,1) != '.'){
                include($folder.'/'.$element);
                $this->debug('We add the file "'.$folder.'/'.$element.'"',2,__LINE__);
                $temp = array_merge($temp,$i18n);
           }
        }
        $entries = array_change_key_case($temp);

        $this->translations[$class][$lang] = $entries;
        return true;
    }

    public function render_i18nInput($attributes = array()){
        if(!isset($attributes['class'])){
            return false;
        }
        if(!isset($attributes['i18n']) || !isset($attributes['name'])){
            return false;
        }
        $this->links->html->addScript($this->getSinglePath().'editorLangChooser.js');
        $class = $attributes['class'];
        $i18n = $attributes['i18n'];
        $name = $attributes['name'];

        $id = 'i18n_'.substr(md5(microtime()),0,8);

        $langs = $this->links->site->langs;
        
        if(is_array($langs) && count($langs)>0){
            foreach($langs as $oneLang){
                $value = $this->db_get($class,$i18n, $oneLang,&$qry);
                $thisArgs = ' name="'.$name.'['.$oneLang.']" value="'.$value.'"';
                $thisArgs .= ' style="background:#ffffff url(/images/shared/flags/'.$oneLang.'_small.png) no-repeat top left;padding-left:20px;"';
                $thisArgs .= ' class="'.$id.' form_i18n_input"';
                $values['langs'][] = array(
                    'name' => $oneLang,
                    'id' => $id.'_'.$oneLang,
                    'args' => $thisArgs.' id="'.$id.'_'.$oneLang.'"'
                );
            }
        }
        $values['lang']['firstDisplayed'] = $id.'_fr_FR';
        $values['lang']['id'] = $id;

        $ret = $this->render('input',$values,false,false);
        return $ret;
    }

    public function render_i18nTextarea($attributes = array()){
        if(!isset($attributes['class']) || !isset($attributes['class'])){
            return false;
        }
        if(!isset($attributes['i18n']) || !isset($attributes['name'])){
            return false;
        }
        $this->links->html->addScript($this->getSinglePath().'editorLangChooser.js');
        $classes = explode(' ',$attributes['class']);
        $class = array_shift($classes);
        $classes = ' '.implode($classes);
        $id = 'i18n_'.substr(md5(microtime()),0,8);
        $i18n = $attributes['i18n'];
        $name = $attributes['name'];


        $langs = $this->links->site->langs;

        if(is_array($langs) && count($langs)>0){
            foreach($langs as $oneLang){
                $value = $this->db_get($class,$i18n, $oneLang);
                $thisArgs = ' name="'.$name.'['.$oneLang.']"';
                $thisArgs .= ' style="background:#ffffff url(/images/shared/flags/'.$oneLang.'_small.png) no-repeat top left;padding-left:20px;"';
                $thisArgs .= ' class="'.$id.' form_i18n_textarea'.$classes.'"';
                $values['langs'][] = array(
                    'name' => $oneLang,
                    'id' => $id.'_'.$oneLang,
                    'args' => $thisArgs.' id="'.$id.'_'.$oneLang.'"',
                    'value' => $value
                );
            }
        }
        $values['lang']['firstDisplayed'] = $id.'_fr_FR';
        $values['lang']['id'] = $id;

        $ret = $this->render('textarea',$values,false,false);
        return $ret;
    }

    public function render_i18nWEditor($attributes = array()){
        if(!isset($attributes['i18nClass'])){
            return false;
        }
        if(!isset($attributes['i18n']) || !isset($attributes['name'])){
            return false;
        }
        if(!isset($attributes['type'])){
            $type = sh_wEditor::DEFAULT_TYPE;
        }else{
            $type = $attributes['type'];
        }
        $this->links->html->addScript($this->getSinglePath().'editorLangChooser.js');
        $class = $attributes['class'];
        $i18nClass = $attributes['i18nClass'];
        $i18n = $attributes['i18n'];
        $baseName = $attributes['name'];

        $id = 'i18n_'.substr(md5(microtime()),0,8);

        $langs = $this->links->site->langs;

        if(is_array($langs) && count($langs)>0){
            foreach($langs as $oneLang){
                $value = $this->db_get($i18nClass,$i18n, $oneLang);
                $values['langs'][] = array(
                    'class' => $class,
                    'i18nClass' => $i18nClass,
                    'content' => $value,
                    'name' => $baseName.'['.$oneLang.']',
                    'langName' => $oneLang,
                    'id' => $id.'_'.$oneLang
                );
            }
        }
        $values['lang']['firstDisplayed'] = $id.'_fr_FR';
        $values['lang']['id'] = $id;
        $values['lang']['type'] = $type;

        $ret = $this->render('wEditor',$values,false,false);
        return $ret;
    }

    /**
     * protected function lowerizeKeys
     *
     */
    protected function lowerizeKeys($array){
    }

    /**
     * public function getLang
     *
     */
    public function getLang(){
        return $_SESSION[__CLASS__]['lang'];
    }

    /**
     * public function getDefaultLang
     *
     */
    public function getDefaultLang(){
        return $this->links->site->lang;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        list($class, $method, $id) = explode('/',$page);
        if($method == 'checkUnstranslated'){
            return '/'.$this->shortClassName.'/checkUnstranslated.php';
        }
        if($method == 'getSelector'){
            return '/'.$this->shortClassName.'/getSelector.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        if($uri == '/'.$this->shortClassName.'/checkUnstranslated.php'){
            return $this->shortClassName.'/checkUnstranslated/';
        }
        if($uri == '/'.$this->shortClassName.'/getSelector.php'){
            return $this->shortClassName.'/getSelector/';
        }
        return false;
    }

    public function __tostring(){
        return get_class();
    }
}
