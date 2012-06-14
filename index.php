<?php
session_start();

if(file_exists(__DIR__.'/needs_installation.php')){
    include(__DIR__.'/installer/installer.php');
    exit;
}

// Verifies if there is some debug to do
if(file_exists('debug.php')){
    $debugEnvironment = true;
    include('debug.php');
    if(!defined('SH_IS_DEV')){
        define('SH_GLOBAL_DEBUG',false);
        define('SH_IS_DEV',false);
    }
}
// Gets the site name (using the server name)
include('sites/domains_resolver.php');

// Creates all the required constants
require('constants.php');

if(isset($_GET['robots']) && $_GET['robots'] == 'robots'){
    if(!SH_IS_DEV){
        include(SH_ROOT_FOLDER.'robots.txt');
    }else{
        include(SH_ROOT_FOLDER.'robots_dev.txt');
    }
    if(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on'){
        $protocol='https';
    }else{
        $protocol='http';
    }
    $domain = $_SERVER['SERVER_NAME'];
    $baseUri = $protocol.'://'.$domain;
    echo "\n\n".'Sitemap: '.$baseUri.'/sitemap.xml'."\n";
    exit;
}

// Creates the autoload function that will help finding the classes
function __autoload($className) {
    if(substr($className,0,strlen(SH_PREFIX)) == SH_PREFIX && file_exists(SH_CLASS_FOLDER.$className .'/'.$className.'.cls.php')){
        include_once (SH_CLASS_FOLDER.$className .'/'.$className.'.cls.php');
    }
    if(substr($className,0,strlen(SH_CUSTOM_PREFIX)) == SH_CUSTOM_PREFIX && file_exists(SH_CUSTOM_CLASS_FOLDER.$className .'/'.$className.'.cls.php')){
        include_once (SH_CUSTOM_CLASS_FOLDER.$className .'/'.$className.'.cls.php');
    }
    return true;
}

// Create the linker object
$linker = sh_linker::getInstance();

$updater = $linker->updater;

define('SH_MOBILE_DEVICE',$linker->session->checkIfIsMobileDevice());

// Verifies if the site can be accessed to any user
$linker->user->siteIsOpen();

// If the file is cached, we show it and exit
if(!$linker->admin->isAdmin()){
    /*if(!($linker->rights->getUserRights() & sh_rights::RIGHT_READ)){
        $linker->path->error(403);
        exit;
    }*/
    $cache = sh_cache::getCachedFile();
    if($cache){
        echo $cache;
        exit(1);
    }
}

// Gets some variables
$element = $linker->path->page['element'];
$action = $linker->path->page['action'];

if(!$linker->$element->isMinimal($action)){
    sh_html::$willRender = true;
}


// Redirect to the 404 error page if the required method doesn't exist.
if(!method_exists($linker->$element,$action)){
    $linker->path->error('404');
}

$linker->events->onAfterBaseConstruction();
// Calls the method
$linker->$element->$action();
$linker->events->onBeforeOutput();

// If the action is not minimal, we render the html
if(!$linker->$element->isMinimal($action)){
    // Renders the document
    $linker->html->render();
}
$linker->events->onAfterOutput();

// Ends the debug if it is on
if($debugEnvironment){
    endDebug();
}
