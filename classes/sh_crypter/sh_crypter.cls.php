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
 * This is just used to crypt and uncrypt datas.
 */
class sh_crypter extends sh_core {
    
    /**
     * public function crypt
     *
     */
    public function crypt($content,$key = '',$cipher = MCRYPT_RIJNDAEL_256){
        $this->debug(__FUNCTION__, 2, __LINE__);
        $iv_size = mcrypt_get_iv_size($cipher, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size,MCRYPT_RAND);
        if($key == ''){
            $key = MD5(__CLASS__.'defaultpass');
            $this->debug('No key given. We will use '.$key, 3, __LINE__);
        }else{
            $key = MD5(__CLASS__.$key);
            $this->debug('A key was given. We will use '.$key, 3, __LINE__);
        }
        return $iv.mcrypt_encrypt($cipher,$key,$content, MCRYPT_MODE_CBC,$iv);
    }

    /**
     * public function uncrypt
     *
     */
    public function uncrypt($content,$key = '',$cipher = MCRYPT_RIJNDAEL_256){
        $this->debug(__FUNCTION__, 2, __LINE__);
        $iv_size = mcrypt_get_iv_size($cipher, MCRYPT_MODE_CBC);
        $iv = substr($content,0,$iv_size);
        $content = substr($content,$iv_size);
        if($key == ''){
            $key = MD5(__CLASS__.'defaultpass');
            $this->debug('No key given. We will use '.$key, 3, __LINE__);
        }else{
            $key = MD5(__CLASS__.$key);
            $this->debug('A key was given. We will use '.$key, 3, __LINE__);
        }
        $uncrypted = mcrypt_decrypt($cipher, $key,$content, MCRYPT_MODE_CBC,$iv);
        return rtrim($uncrypted, "\0\4");
    }
    
    public function __tostring(){
        return get_class();
    }
}