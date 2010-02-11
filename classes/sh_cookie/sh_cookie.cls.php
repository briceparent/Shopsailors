<?php
//Derivated from:
// cookieClass
// Copyright (C) 2005 JRSofty Programming.
// http://jrsofty1.stinkbugonline.com
// Licensed under GNU/GPL

/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')){header('location: directCallForbidden.php');}

/**
 * This class manages the cookies
 */
class sh_cookie extends sh_core{

    protected $cName = '';
    protected $cTime = '';
    protected $cSerialize = false;
    protected $cPath = '';

    /**
     * public function add
     *
     */
    public function add($elements){
        if(!is_array($elements)){
            return false;
        }
        $ret = true;
        foreach($elements as $key=>$value){
            $ret = $ret && setcookie($key, serialize($value), time() + $this->getParam('timeOut'));
        }
        return $ret;
    }

    function destroyAll(){
        foreach($_COOKIE as $name=>$val){
            if(strpos($name,$this->cName) !== false){
                $_COOKIE[$name] = NULL;
                $this->destroy($name);
            }
        }
    }

    function destroy($cName){
        $tStamp = time() - 432000;
        setcookie($cName,"",$tStamp,$this->cPath);
    }

}