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
 * class sh_contact
 *
 */
class sh_sitemap extends sh_core {
    protected $minimal = array('show' => true);
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
        $this->file = SH_SITE_FOLDER. __CLASS__.'/sitemap.php';
        return true;
    }

    public function renew(){
        $this->removeFromSitemap('*');
        $directory = SH_CLASS_SHARED_FOLDER.__CLASS__.'/';
        if(is_dir($directory)){
            $classes = scandir($directory);
            foreach($classes as $class){
                if(substr($class,0,1) != '.'){
                    $class = substr($class,0,-4);
                    // We have found a class on which to call sitemap_renew();
                    $this->links->$class->sitemap_renew();
                }
            }
        }
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
        if(!is_dir(SH_SITE_FOLDER.__CLASS__.'/')){
            mkdir(SH_SITE_FOLDER.__CLASS__.'/');
        }
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
        $this->links->cache->disable();
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
        $uri = $this->links->path->getLink($page);
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
        $this->links->helper->writeArrayInFile(
            $this->file,
            'this->addresses',
            $this->addresses
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
        $page = MD5($this->links->path->getPage());
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