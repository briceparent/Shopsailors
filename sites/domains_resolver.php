<?php
/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @package Shopsailors Core
 */

include('list.php');
unset($_SESSION['SH_SITE']);
unset($_SESSION['SH_SITENAME']);
if(isset($_SESSION['SH_SITE'])){
    define('SH_SITE',$_SESSION['SH_SITE']);
    define('SH_SITENAME',$_SESSION['SH_SITENAME']);
}else{
    $actualSite = $_SERVER['SERVER_NAME'];
    foreach($sites as $siteAddress=>$siteName){
        if(!defined('SH_SITE') && preg_match($siteAddress,$actualSite)){
            /* Constants that contain the name of the site. */
            define('SH_SITE',$siteName.'/');
            define('SH_SITENAME',$siteName);
            $_SESSION['SH_SITE'] = SH_SITE;
            $_SESSION['SH_SITENAME'] = SH_SITENAME;
            break;
        }
    }
    if(!defined('SH_SITE') && is_array($redirections)){
        if($_SERVER['https']){
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
        // We have found no redirection, so we will show the default site
        define('SH_SITE',$default.'/');
        define('SH_SITENAME',$default);
        $_SESSION['SH_SITE'] = SH_SITE;
        $_SESSION['SH_SITENAME'] = SH_SITENAME;
    }
}