<?php
session_start();
// Verifies if there is some debug to do
if(file_exists('debug.php')){
    $debugEnvironment = true;
    include('debug.php');
}
// Gets the site name (using the server name)
include('sites/domains_resolver.php');

// Creates all the required constants
require('constants.php');

// Creates the autoload function that will help finding the classes
function __autoload($className) {
    if(file_exists(SH_CLASS_FOLDER.$className .'/'.$className.'.cls.php')){
        include_once (SH_CLASS_FOLDER.$className .'/'.$className.'.cls.php');
    }
    return true;
}

// Create the linker object
$linker = sh_linker::getInstance();

// Verifies if the site can be accessed to any user
$linker->user->siteIsOpen();

// If the file is cached, we show it and exit
$cache = sh_cache::getCachedFile();
if($cache){
    echo $cache;
    exit(1);
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

// Calls the method
$linker->$element->$action();

// If the action is not minimal, we render the html
if(!$linker->$element->isMinimal($action)){
    // Renders the document
    $cache = $linker->html->render();
}

// Ends the debug if it is on
if($debugEnvironment){
    endDebug();
}
