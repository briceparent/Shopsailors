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
 * Class that renders the css files, replacing the colors by those that should
 * be used in the variation.
 */
class sh_cron extends sh_core{
    protected $minimal = array('job' => true);

    const JOB_FROM = 1;
    const JOB_YEAR = 1;
    const JOB_HALFYEAR = 2;
    const JOB_QUARTERYEAR = 3;
    const JOB_MONTH = 4;
    const JOB_WEEK = 5;
    const JOB_DAY = 6;
    const JOB_HALFDAY = 7;
    const JOB_HOUR = 8;
    const JOB_HALFHOUR = 9;
    const JOB_QUARTERHOUR = 10;
    const JOB_TO = 10;
    
    public function construct(){
        if(!is_dir(SH_CLASS_SHARED_FOLDER.__CLASS__)){
            mkdir(SH_CLASS_SHARED_FOLDER.__CLASS__);
        }
    }

    /**
     * public function get
     *
     */
    public function job(){
        if(
            in_array(
                $_SERVER['REMOTE_ADDR'],
                $this->getParam('launchers',array())
            )
        ){
            $id = (int) $this->linker->path->page['id'];
            $this->linker->helper->writeInFile(
                SH_TEMP_FOLDER.'cron.php',
                $id.' - '.date('Y-m-d H:i:s')."\n",
                true
            );
            $ret = true;
            $classes = $this->getClassesFromSharedFolder();
            foreach($classes as $class){
                $method = 'cron_job';
                if(method_exists($class['long'],$method )){
                    $shortClassName = $class['short'];
                    $ret = $this->linker->$shortClassName->$method($id) && $ret;
                }
            }
            
            return $ret;
        }
        echo 'NOT ALLOWED TO LAUNCH CRON JOBS!';
        return false;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        list($class,$method,$id) = explode('/',$page);

        if($method == 'job' && $id >= self::JOB_FROM && $id <= self::JOB_TO){
            return '/'.$this->shortClassName.'/job/'.$id.'.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        if(preg_match('`/'.$this->shortClassName.'/([^/]+)(/([0-9]+)(-[^/]+)?)?\.php`',$uri,$matches)){
            $id = $matches[1];
            if($matches[1] == 'job' && $matches[3] >= self::JOB_FROM && $matches[3] <= self::JOB_TO){
                return $this->shortClassName.'/job/'.$matches[3];
            }
        }
        return false;
    }
    
    public function __tostring(){
        return get_class();
    }
}
