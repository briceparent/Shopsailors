<?php
/**
 * This class implements Ludovic PATEY's postrequest class which may be found
 * in the postrequest subfolder, which is distributed under a BSD license
 */

/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')){header('location: directCallForbidden.php');}

/**
 * This is a class used to communicate with others serveurs trough POST.
 */
include('postrequest/postrequest.class.php');

class sh_postRequest extends sh_core {
    protected $posters = array();

    public function create($url){
        $this->debug(__CLASS__.'->'.__FUNCTION__.'('.$url.')', 2, __LINE__);
        $counter = count($this->posters);
        $this->posters[$counter] = new PostRequest($url);
        $this->debug('We create the poster #'.$counter, 3, __LINE__);
        return $counter;
    }
    
    public function setCookies($poster,$cookies){
        $this->debug(__CLASS__.'->'.__FUNCTION__.'('.$poster.')', 2, __LINE__);
        return $this->posters[$poster]->setCookies($cookies);
    }
    
    public function setData($poster,$name,$value){
        $this->debug(__CLASS__.'->'.__FUNCTION__.'('.$poster.')', 2, __LINE__);
        return $this->posters[$poster]->setData($name,$value);
    }

    public function setFile($poster,$name,$path,$mime){
        $this->debug(__CLASS__.'->'.__FUNCTION__.'('.$poster.')', 2, __LINE__);
        return $this->posters[$poster]->setFile($name,$path,$mime);
    }

    public function getCookies($poster) {
        $this->debug(__CLASS__.'->'.__FUNCTION__.'('.$poster.')', 2, __LINE__);
        return $this->posters[$poster]->getCookies();
    }

    public function setHeader($poster,$name,$value){
        $this->debug(__CLASS__.'->'.__FUNCTION__.'('.$poster.')', 2, __LINE__);
        return $this->posters[$poster]->setHeader($name,$value);
    }

    public function send($poster){
        $this->debug(__CLASS__.'->'.__FUNCTION__.'('.$poster.')', 2, __LINE__);
        return $this->posters[$poster]->send();
    }
    
    public function __tostring(){
        return get_class();
    }
}

