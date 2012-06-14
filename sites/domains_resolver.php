<?php
/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @package Shopsailors Core
 */

if(file_exists(dirname(__FILE__).'/list.php')){
    include('list.php');
}else{
    echo 'An error occured. An important file (list.php) was not found.<br />Please contact an administrator.';
    exit(0);
}

if(isset($_SESSION['SH_SITE']) && !SH_IS_DEV){
    define('SH_SITE',$_SESSION['constants']['SH_SITE']);
    define('SH_SITENAME',$_SESSION['constants']['SH_SITENAME']);
    define('SH_MASTERSERVER',$_SESSION['constants']['SH_MASTERSERVER']);
    define('SH_MASTERSERVER_SITE',$_SESSION['constants']['SH_MASTERSERVER_SITE']);
    define('SH_MASTERSERVER_DOMAIN',$_SESSION['constants']['SH_MASTERSERVER_DOMAIN']);
}else{  
    $siteFound = false;
    $actualSite = $_SERVER['SERVER_NAME'];
    foreach($sites as $siteAddress=>$siteName){
        $siteAddress = stripslashes($siteAddress);
        if(preg_match($siteAddress,$actualSite)){
            $siteName = preg_replace($siteAddress,$siteName,$actualSite);
            /* Constants that contain the name of the site. */
            define('SH_SITE',$siteName.'/');
            define('SH_SITENAME',$siteName);
            $_SESSION['constants']['SH_SITE'] = SH_SITE;
            $_SESSION['constants']['SH_SITENAME'] = SH_SITENAME;
            /* Constant that tells if we are on a masterServer site */
            setMasterServerConstants();
            $siteFound = true;
            break;
        }
    }

    if(!$siteFound && is_array($redirections)){
        if(isset($_SERVER['https']) && $_SERVER['https']){
            $protocol='https://';
        }else{
            $protocol='http://';
        }
        $request = $_SERVER['REQUEST_URI'];
        // We check for redirections
        foreach($redirections as $siteAddress=>$siteName){
            if(preg_match($siteAddress,$actualSite)){
                $newDomain = preg_replace($siteAddress,$siteName,$actualSite);
                header('Status: 301 Moved Permanently', false, 301);
                header('Location: '.$protocol.$newDomain.$request);
                break;
            }
        }
    }
    if(!$siteFound){
        // We have found no redirection, so we will show the default site
        define('SH_SITE',$default.'/');
        define('SH_SITENAME',$default);
        $_SESSION['constants']['SH_SITE'] = SH_SITE;
        $_SESSION['constants']['SH_SITENAME'] = SH_SITENAME;
        define('SH_MASTERSERVER',false);
        $_SESSION['constants']['SH_MASTERSERVER'] = SH_MASTERSERVER;
    }
}

function setMasterServerConstants(){
    global $masterSite;
    global $masterServer;
    global $devMasterSite;
    global $devMasterServer;
    global $localMasterServers;
    
    define('SH_MASTERSERVER',in_array(SH_SITENAME,$localMasterServers));
    
    if(!SH_MASTERSERVER){
        if(!SH_GLOBAL_DEBUG){
            define('SH_MASTERSERVER_SITE',$masterSite);
            define('SH_MASTERSERVER_DOMAIN',$masterServer);
        }else{
            define('SH_MASTERSERVER_SITE',$devMasterSite);
            define('SH_MASTERSERVER_DOMAIN',$devMasterServer);
        }
    }else{
        define('SH_MASTERSERVER_DOMAIN','');
        define('SH_MASTERSERVER_SITE','');
    }
    
    $_SESSION['constants']['SH_MASTERSERVER'] = SH_MASTERSERVER;
    $_SESSION['constants']['SH_MASTERSERVER_SITE'] = SH_MASTERSERVER_SITE;
    $_SESSION['constants']['SH_MASTERSERVER_DOMAIN'] = SH_MASTERSERVER_DOMAIN;
}