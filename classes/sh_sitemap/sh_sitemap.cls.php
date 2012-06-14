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
 * class sh_contact
 *
 */
class sh_sitemap extends sh_core {
    const CLASS_VERSION = '1.1.11.04.01';

    public $shopsailors_dependencies = array(
        'sh_linker','sh_params','sh_db'
    );
    protected $minimal = array('show' => true,'chooseLink'=>true,'renew' => true);
    protected $addresses = array();
    protected $file = '';

    const FREQUENCY_ALWAYS = 0;
    const FREQUENCY_HOURLY = 1;
    const FREQUENCY_DAILY = 2;
    const FREQUENCY_WEEKLY = 3;
    const FREQUENCY_MONTHLY = 4;
    const FREQUENCY_YEARLY = 5;
    const FREQUENCY_NEVER = 6;

    public function construct(){
        $installedVersion = $this->getClassInstalledVersion();
        if($installedVersion != self::CLASS_VERSION){
            // The class datas are not in the same version as this file, or don't exist (installation)
            
            if(version_compare($installedVersion,'1.1.11.04.01','<=')){
                if(!is_dir(SH_SITE_FOLDER.__CLASS__.'/')){
                    mkdir(SH_SITE_FOLDER.__CLASS__.'/');
                }
                $this->linker->renderer->add_render_tag('render_chooseLink',__CLASS__,'render_chooseLink');
            }
            $this->setClassInstalledVersion(self::CLASS_VERSION);
        }
        $this->file = SH_SITE_FOLDER. __CLASS__.'/sitemap.php';
        return true;
    }

    public function renew(){
        $this->removeFromSitemap('*');
        $classes = $this->helper->getClassesSharedMethods(__CLASS__);
        foreach($classes as $class){
            // We have found a class on which to call sitemap_renew();
            $this->linker->$class->sitemap_renew();
        }
        return true;
    }

    public function render_chooseLink($attributes = array()){
        if(empty($attributes['name'])){
            return false;
        }
        $this->linker->html->addScript($this->getSinglePath().'chooseLink.js');

        $values['link']['name'] = $attributes['name'];
        $values['link']['value'] = $attributes['value'];
        $values['input']['class'] = $attributes['class'];
        if(!empty($attributes['id'])){
            $values['input']['id'] = $attributes['id'];
        }else{
            $values['input']['id'] = 'link_'.substr(md5(microtime()),0,8);
        }

        return $this->render('render_chooseLink',$values,false,false);
    }

    /**
     * public function chooseLink
     *
     */
    public function chooseLink() {
        $datas['classes'] = $this->helper->listLinks(
            $_GET['value']
        );
        
        $datas['category']['id'] = $_GET['id'];

        echo $this->render('chooseLink',$datas,false,false);
        return true;
    }

    public function getSitemapPagesList(){
        if(!file_exists($this->file)){
            $this->renew();
        }
        include($this->file);
        return $this->addresses;
    }

    /**
     * public function create
     */
    public function create(){
        if(file_exists($this->file)){
            include($this->file);
        }
        $content = $this->render('show',$this->addresses,false,false);

        return $content;
    }

    /**
     * public function show
     *
     */
    public function show(){
        $this->linker->cache->disable();
        $xml = $this->create();
        header('content-type: text/xml');
        echo $xml;
        return true;
    }

    /**
     * protected function addToSitemap
     *
     */
    public function addToSitemap($page,$priority,$date,$frequency){
        if(file_exists($this->file)){
            include($this->file);
        }
        $uri = $this->linker->path->getLink($page);
        $address = 'http://'.$_SERVER['HTTP_HOST'].$uri;
        $this->addresses['PAGES'][$page] = array(
            'address'=>$address,
            'priority'=>$priority,
            'date'=>$date,
            'frequency'=>$frequency
        );
        return $this->buildFile();
    }

    /**
     * protected function buildFile
     *
     */
    protected function buildFile(){
        $this->helper->writeArrayInFile(
            $this->file,
            'this->addresses',
            $this->addresses,
            false
        );
        return $rep;
    }

    /**
     * public function removeFromSitemap
     * Can be used with or without a mask : eg.
     * className/action/1 will only delete the selected entry.
     * className/action/* will delete any entry begining with className/action
     * className/* will delete all entries from the class className
     * className/* /1 (without the space) will delete elements #1 from
     * any method from the class className
     */
    public function removeFromSitemap($elements){
        if(file_exists($this->file)){
            include($this->file);
        }
        if($elements == '*'){
            $this->addresses['PAGES'] = array();
        }elseif(is_array($this->addresses['PAGES'])){
            list($class,$method,$id) = explode('/',$elements);
            foreach($this->addresses['PAGES'] as $page=>$values){
                list($readClass,$readMethod,$readValue) = explode('/',$page);
                if($class == $readClass){
                    if($method == '*'){
                        if($id == '' || $id == '*' || $id == $readValue){
                            continue;
                        }
                    }
                    if($method == $readMethod){
                        if($id == '*' || $id == $readValue){
                            continue;
                        }
                    }
                }
                $newAddresses[$page] = $values;
            }
        }
        $this->addresses['PAGES'] = $newAddresses;
        return $this->buildFile();
    }

    /**
     * public function setDate
     *
     */
    public function setDate($date = ''){
        if($date != '' && preg_match('`([0-9]{4}-[0-9]{2}-[0-9]{2}).+`',$date,$match)){
            $date = $match[1];
        }elseif($date != '' && preg_match('`([0-9]{2})-([0-9]{2})-([0-9]{4}).+`',$date,$match)){
            $date = $match[3].'-'.$match[2].'-'.$match[1];
        }else{
            $date = date('Y-m-d');
        }
        $page = MD5($this->linker->path->getPage());
        $file = SH_SITE_FOLDER.__CLASS__.'/'.$page;

        echo $page;
    }

    /**
     * public function getFrequencies
     *
     */
    public function getFrequencies(){
        return $this->getParam('frequencies');
    }

    /**
     * public function getDefaultFrequency
     *
     */
    public function getDefaultFrequency(){
        return $this->getParam('defaultFrequency');
    }

    /**
     * public function getDefaultPriority
     *
     */
    public function getDefaultPriority(){
        return $this->getParam('defaultPriority');
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        if($page == $this->shortClassName.'/create/'){
            return '/'.$this->shortClassName.'/create.php';
        }
        if($page == $this->shortClassName.'/show/'){
            return '/'.$this->shortClassName.'show.php';
        }
        if($page == $this->shortClassName.'/renew/'){
            return '/'.$this->shortClassName.'renew.php';
        }
        if($page == $this->shortClassName.'/chooseLink/'){
            return '/'.$this->shortClassName.'chooseLink.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        if($uri == '/'.$this->shortClassName.'/create.php'){
            return $this->shortClassName.'/create/';
        }
        if($uri == '/'.$this->shortClassName.'/show.php'){
            return $this->shortClassName.'/show/';
        }
        if($uri == '/'.$this->shortClassName.'/renew.php'){
            return $this->shortClassName.'/renew/';
        }
        if($uri == '/'.$this->shortClassName.'/chooseLink.php'){
            return $this->shortClassName.'/chooseLink/';
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