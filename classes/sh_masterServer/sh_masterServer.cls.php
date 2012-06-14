<?php
/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')) {header('location: directCallForbidden.php');}

class sh_masterServer extends sh_core{
    const CLASS_VERSION = '1.1.11.04.03';
    const LINE_SEPARATOR = '__NEW_LINE__';

    public $shopsailors_dependencies = array(
        'sh_linker','sh_params','sh_db'
    );
    public $minimal = array(
        'linkToServer'=>true,'setCryptingCode'=>true,'addClient'=>true,'getAllowedSiteCode_master'=>true
    );
    public $callWithoutId = array(
        'addClient','linkToServer','setCryptingCode','nothing','getAllowedSiteCode',
        'getAllowedSiteCode_master'
    );
    protected $siteCode = null;

    public function nothing(){
        sh_cache::disable();
        echo __METHOD__;
    }

    public function construct(){
        $this->masterSite = SH_MASTERSERVER_SITE;
        $this->masterUrl = $this->getMasterServerUrl();
        $installedVersion = $this->getClassInstalledVersion();
        if($installedVersion != self::CLASS_VERSION){
            // The class datas are not in the same version as this file, or don't exist (installation)

            if(version_compare($installedVersion,'1.1.11.04.03','<') ){
                if(!SH_MASTERSERVER){
                    // We prepare the site code
                    $this->siteCode = $this->getParam('siteCode',null);
                    if(is_null($this->siteCode)){
                        $this->siteCode = md5(microtime().mt_rand()).md5(microtime().mt_rand());
                        $this->setParam('siteCode', $this->siteCode);
                    }
                    $site = $this->siteCode;

                    $passToMasterServer = $this->getParams('master_site_code',null);
                    if(!is_null($passToMasterServer)){
                        // Crypts it
                        $key = $this->createUnicCryptingKey();

                        $class_with_more = __CLASS__.' '.md5(mt_rand());
                        $crypted_class = $this->linker->crypter->crypt($class_with_more,$passToMasterServer);
                        $crypted_key = $this->linker->crypter->crypt($key,$passToMasterServer);
                        $crypted_site = $this->linker->crypter->crypt($site,$passToMasterServer);
                        $this->setParam('crypting_key',$key);
                        $this->writeParams();
                        $written = $this->getParam('crypting_key',array());

                        // Saving the new user on the master server
                        $uri = 'sh_masterServer/addClient.php';
                        $connectionPage = $this->masterUrl.$uri;
                        $requestId = $this->linker->postRequest->create($connectionPage);
                        $this->linker->postRequest->setData($requestId,'class',urlencode($crypted_class));
                        $this->linker->postRequest->setData($requestId,'site',urlencode($crypted_site));
                        $this->linker->postRequest->setData($requestId,'crypting_key',urlencode($crypted_key));
                        $response = $this->linker->postRequest->send($requestId);
                        if($response == 'NOT ALLOWED'){
                            echo 'Error connecting to masterServer!';
                        }
                    }else{
                        echo 'ERROR! NO LINK TO MASTERSERVER!!';
                    }
                }
                // We prepare the site code
                $this->siteCode = $this->getParam('siteCode',null);
                if(is_null($this->siteCode)){
                    $this->siteCode = md5(microtime().mt_rand()).md5(microtime().mt_rand());
                    $this->setParam('siteCode', $this->siteCode);
                    $this->writeParams();
                }
            }
            $this->setClassInstalledVersion(self::CLASS_VERSION);
        }
        $this->siteCode = $this->getParam('siteCode',null);

        $this->masterUrl = 'http://'.SH_MASTERSERVER_DOMAIN;
    }
    public function getMasterServerUrl(){
        return 'http://'.$this->getParam('master_domain','').'/';
    }

    public function request($page,$params,$splitReturn = true){
        // We get the site code
        $site = $this->getSiteCode();

        $destinationPageName = $this->linker->path->getLink($page);
        $connectionPage = $this->masterUrl.$destinationPageName;
        $requestId = $this->linker->postRequest->create($connectionPage);

        // We prepare the POST params
        foreach($params as $paramName=>$paramValue){
            $crypted = $this->linker->masterServer->crypt($paramValue);
            $this->linker->postRequest->setData($requestId,$paramName,urlencode($crypted));

        }
        $this->linker->postRequest->setData($requestId,'site',urlencode($site));

        // Sending the request
        $response = $this->linker->postRequest->send($requestId);

        if($splitReturn){
            $response = $this->splitReturn($response);
        }
        return $response;
    }

    public function getSiteCode(){
        return $this->siteCode;
    }

    public function getAllowedSiteCode(){
        sh_cache::disable();
        $site = $this->getFromAnyServer('site');
        $rep = $this->request(
            __CLASS__.'/getAllowedSiteCode/',
            array(
                'verification' => md5(date('Y-m-d').$site)
            )
        );
        if(!isset($rep['forbidden'])){
            return $rep['allowedSiteCode'];
        }
        return false;
    }

    public function getAllowedSiteCode_master(){
        sh_cache::disable();
        if(SH_MASTERSERVER){
            $site = $this->getFromAnyServer('site');
            $verification = $this->linker->masterServer->uncrypt($this->getFromAnyServer('verification'),$site);

            // We verify that the server has been able to send a cryted md5 of the date
            //salted with its name
            $tests[] = md5(date('Y-m-d').$site);
            // Added a test 5 minutes earlier, and a test 5 minutes later, in case the 2
            // servers don't share the exact same time,  and the date is not the same
            // because of it
            $tests[] = md5(date('Y-m-d',time() - 5 * 60).$site);
            $tests[] = md5(date('Y-m-d',time() + 5 * 60).$site);
            
            foreach( $tests as $value ) {
                if($value == $verification){
                    $checked = true;
                    break;
                }
            }
            if(!$checked){
                echo 'forbidden'."\n".'forbidden'."\n".self::LINE_SEPARATOR."\n";
                return true;
            }

            $passwords = $this->getParams('allowed_sites_codes',array());

            $password = '';
            for($a = 0;$a < 10;$a++){
                $rand = mt_rand(0,100000);
                $password .= md5(__FILE__.md5(microtime()).$rand);
            }
            $passwords[] = $password;
            echo 'allowedSiteCode'."\n".$password."\n".self::LINE_SEPARATOR."\n";
            $this->setParams('allowed_sites_codes',$passwords);
            $this->writeParams();
        }
    }

    /**
     * protected function getFromAnyServer
     *
     */
    protected function getFromAnyServer($argName) {
        return urldecode(stripslashes($_POST[$argName]));
    }

    public function splitReturn($return) {
        $this->debug(__FUNCTION__, 2, __LINE__);
        $entries = explode("\n".self::LINE_SEPARATOR."\n", $return);
        if(is_array($entries)) {
            foreach($entries as $entry) {
                list($fieldName,$fieldValue) = explode("\n",$entry,2);
                if($fieldName != '') {
                    $ret[$fieldName] = $fieldValue;
                }
            }
            return $ret;
        }
        return array();
    }

    public function addClient(){
        sh_cache::disable();
        // Testing if the client is allowed to connect to this masterServer

        $passwords = $this->getParams('allowed_sites_codes',array());
        $allowed = false;
        $remaining = array();
        $class = urldecode(stripslashes($_POST['class']));
        $site = urldecode(stripslashes($_POST['site']));
        $crypting_key = urldecode(stripslashes($_POST['crypting_key']));
        foreach($passwords as $code){
            $decrypted_class = $this->linker->crypter->uncrypt($class,$code);

            if(substr($decrypted_class,0,strlen(__CLASS__)) == __CLASS__){
                echo $decrypted_class.'<br />';
                $allowed = true;
                $decrypted_site = $this->linker->crypter->uncrypt($site,$code);
                $decrypted_crypting_key = $this->linker->crypter->uncrypt($crypting_key,$code);
                echo $decrypted_crypting_key.' -> '.$crypted_key.'<br />';
            }else{
                $remaining[] = $code;
            }
        }
        if($allowed){
            $this->setParams('allowed_sites_codes',$remaining);
            if(!$this->getParam('allowedOrigins>'.$_SERVER['REMOTE_ADDR'],false)){
                $this->setParam(
                    'allowedOrigins>'.$_SERVER['REMOTE_ADDR'],$_SERVER['REMOTE_ADDR']
                );
            }
            $allowed_sites = $this->getParams('allowed_sites',array());
            $allowed_sites[$decrypted_site] = array(
                'site' => $decrypted_site,
                'crypting_key'=>$decrypted_crypting_key
            );
            $this->setParams('allowed_sites',$allowed_sites);

            $this->writeParams();
            echo 'OK';
        }
        echo 'NOT ALLOWED';
    }

    public function setCryptingCode(){
        sh_cache::disable();
        $site = urldecode(stripslashes($_POST['site']));
        $crypting_key = urldecode(stripslashes($_POST['crypting_key']));

        $site = $this->linker->crypter->uncrypt($site, md5(__CLASS__));
        $crypting_key = $this->linker->crypter->uncrypt($crypting_key, md5(__CLASS__));

        // We save those datas in the params file
        $this->setParam('sites>'.$site,$crypting_key);
        $this->writeParams();

        $ok = $this->linker->crypter->crypt('OK', $crypting_key);
        echo $ok;
        exit;
    }

    public function getKey($siteCode = null){
        static $key = null;
        if(is_null($key)){
            if(SH_MASTERSERVER){
                /*
                $this->linker->db->updateQueries(__CLASS__);
                list($result) = $this->db_execute('get_key',array('siteCode'=>$siteCode));
                $key = $result['key'];*/
                if(is_null($siteCode)){
                    $siteCode = $this->siteCode;
                }
                $key = $this->getParam('sites>'.$siteCode,null);
            }else{
                // We don't use $siteCode, because we are on a client, so the siteCode and the key are already known
                $key = $this->getParam('key',null);
            }
        }
        return $key;
    }

    /**
     * Gets the name of the classes and methods that may be called on master servers
     * @return array An array of the classes and methods with the format "sh_classname/methodname"
     */
    protected function getAllowedPathes(){
        $classes = $this->get_shared_methods();
        $ret = array();
        foreach($classes as $class){
            foreach($this->linker->$class->masterServer_getMethods() as $method){
                $ret[] = $class.'/'.$method;
            }
        }
        $ret[] = __CLASS__.'/addClient';
        $ret[] = __CLASS__.'/getAllowedSiteCode_master';
        $ret[] = __CLASS__.'/nothing';
        return $ret;
    }

    public function linkToServer(){
        // This method should be called during the installation of the server

        // We first check if the server is not already paired to a master server
        $actualKey = $this->getParam('key',null);
        if(is_null($actualKey)){
            $key = $this->createUnicCryptingKey();

            $this->setParam('key', $key);
            $this->writeParams();

            // We ask the master server $server if it is ok to be linked with this site
            header('Content-disposition:filename='.$this->siteCode.'.txt');
            header('Content-type:application/octetstream');
            echo SH_SITENAME."\n";
            echo $this->siteCode."\n";
            echo $key;
        }else{
            echo 'Already done! Can\'t do it again...';
        }
    }

    protected function createUnicCryptingKey(){
        $key = '';
        for($a = 0;$a<3;$a++){
            // we add 3 times a 32 unic string
            $rand = mt_rand(0,100000);
            $key .= md5(__FILE__.microtime().$rand);
        }
        return $key;
    }

    public function isPathAllowed($class,$method){
        $pageName = $this->linker->cleanObjectName($class).'/'.$method;

        $allowedPages = $this->getAllowedPathes();
        if(!in_array($pageName,$allowedPages)){
            return false;
        }
        return true;
    }

    public function crypt($content,$client = null){
        $key = $this->getKey($client);
        $ret = $this->linker->crypter->crypt($content,$key);
        return $ret;
    }

    public function uncrypt($content,$client = null){
        $key = $this->getKey($client);
        $ret = $this->linker->crypter->uncrypt($content,$key);
        return $ret;
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring(){
        return get_class();
    }

}
