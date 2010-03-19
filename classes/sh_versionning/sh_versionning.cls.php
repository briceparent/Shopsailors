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
 * Class that manages the packages versions.
 */
class sh_versionning extends sh_core{
    protected $enableDb = false;
    static $actualMajorVersion = 1;

/**
 * Constructor
 */
    public function construct(){
        $this->actualMajorVersion = $this->getParam('actualMajorVersion');
    }

    public function isNewer($package1,$package2,$strict = false){
        list($p1_v,$p1_y,$p1_d,$p1_cpt) = explode('.',$this->getVersion($package1));
        list($p2_v,$p2_y,$p2_d,$p2_cpt) = explode('.',$this->getVersion($package2));

        if($p1_v > $p2_v){
            return true;
        }elseif($p2_v > $p1_v){
            return false;
        }
        if($p1_y > $p2_y){
            return true;
        }elseif($p2_y > $p1_y){
            return false;
        }
        if($p1_d > $p2_d){
            return true;
        }elseif($p2_d > $p1_d){
            return false;
        }
        if($p1_cpt > $p2_cpt){
            return true;
        }elseif($p2_cpt > $p1_cpt){
            return false;
        }
        return !$strict;
    }

    public function isOlder($package1,$package2,$strict){
        return !$this->isNewer($package1, $package2, $strict);
    }

    /**
     *
     * @param string|null $packageName
     * The name of the package we want to know the version, or null if we just ask
     * for the actual major version.
     * @return <type>
     */
    public function getVersion($packageName = null){
        $this->onlyAdmin();
        if(is_null($packageName)){
            return $this->actualMajorVersion;
        }
        $version = $this->linker->params->get($packageName,'actualMajorVersion','');
        return $version;
    }

    /**
     * protected function createVersionNumber
     *
     */
    public static function createVersionNumber($cpt,$date = null,$version=null){
        if(is_null($date)){
            $date = date('U');
        }
        if(is_null($version)){
            $version = self::$actualMajorVersion;
        }
        return $version.date('.y.z.').$cpt;
    }


    public function __tostring(){
        return get_class();
    }
}
