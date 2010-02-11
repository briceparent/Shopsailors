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
 * Class creating and sending the cache of entire html pages
 */
class sh_cache extends sh_core{
    protected static $enabled = true;

    /**
     * public static function disable
     */
    public static function disable(){
        self::$enabled = false;
    }

    public static function removeCacheFile($path = ''){
        if($path ==  ''){
            $path = self::getCacheName();
        }
        if(file_exists($path)){
            unlink($path);
        }
    }

    /**
     * protected static function staticGetPath
     *
     */
    protected static function staticGetPath(){
        return SH_CACHE_FOLDER;
    }

    /**
     * This function should only be called from this file and from
     * sh_core
     */
    public static function removeCache(){
        $path = self::staticGetPath();
        $links = sh_links::getInstance();
        $links->helper->deleteDir($path);
        return true;
    }


    /**
     * protected static function staticRecursiveRm
     *
     */
    protected static function staticRecursiveRm($element){
        if (is_dir($element)){
            $files = scandir($element);
            foreach($files as $file){
                if($file != '.' && $file != '..'){
                    if(is_dir($file)){
                        self::staticRecursiveRm($element.'/'.$file);
                    }else{
                        unlink($element.'/'.$file);
                    }
                }
            }
            rmdir($element);
        }elseif(file_exists($element)){
            unlink($element);
        }
    }


    public static function getCachedFile(){
        if(sh_session::staticIsAdmin()){
            return false;
        }
        $cacheName = self::getCacheName();
        if(file_exists($cacheName)){
            return file_get_contents($cacheName);
        }
        return false;
    }

    public static function startCache(){
        ob_start();
    }

    public static function stopCache(){
        $src['cache'] = '<!-- ALL THIS PAGE WAS TAKEN FROM CACHE - (the cache was built on '.date('Y/m/d').') -->';
        $buffer     = ob_get_contents() ;
        ob_end_clean();
        if(self::$enabled){
            $writtenBuffer = str_replace('</body',$src['cache'].'</body',$buffer);
            $cacheFile = self::getCacheName();
            $cacheName  = fopen($cacheFile,'w+') ;
            $rep        = fwrite($cacheName,$writtenBuffer) ;
            fclose($cacheName) ;
        }
        return $buffer;
    }

    public static function getCacheName(){
        $path = self::staticGetPath();
        if(!is_dir($path)){
            mkdir($path,0777,true);
        }
        $file = $path.sh_i18n::getLang().sh_path::staticGetUnicId().'.php';

        return $file;
    }

    public function __tostring(){
        return get_class();
    }

}