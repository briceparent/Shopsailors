<?php

// Taken from php.net
define('DS', DIRECTORY_SEPARATOR);

function copy_r( $path, $dest ){
    if( is_dir($path) ){
        @mkdir( $dest );
        $objects = scandir($path);
        if( sizeof($objects) > 0 ){
            foreach( $objects as $file ){
                if( $file == "." || $file == ".." ){
                    continue;
                }
                // go on
                if( is_dir( $path.DS.$file ) ){
                    copy_r( $path.DS.$file, $dest.DS.$file );
                } else {
                    copy( $path.DS.$file, $dest.DS.$file );
                }
            }
        }
        return true;
    } elseif( is_file($path) ) {
        return copy($path, $dest);
    } else {
        return false;
    }
}
// end

$thisFolder = __DIR__.DS;
$filesFolder = realpath($thisFolder.'..').DS;
    
// We prepare the users names and pass for both databases
$_SESSION['masterUser'] = 'master_'.substr(md5(microtime()),0,6);
$_SESSION['masterPassword'] = substr(md5(microtime()),0,18);

$_SESSION['userUser'] = 'user_'.substr(md5(microtime()),0,6);
$_SESSION['userPassword'] = substr(md5(microtime()),0,18);


$sitesArray = array();
if($_POST['install'] == 'both'){
    $sitesArray[0] = $_POST['both_ms'];
    $sitesArray[0]['created_user'] = $_SESSION['masterUser'];
    $sitesArray[0]['created_password'] = $_SESSION['masterPassword'];
    $sitesArray[0]['type'] = 'master';
    $sitesArray[0]['site_name'] = str_replace('.','_',$_POST['both_ms']['domain']);
    for($a = 0;$a<20;$a++){
        $key = '';
        for($b = 0;$b < 10;$b++){
            $rand = mt_rand(0,100000);
            $key .= md5(__FILE__.md5(microtime()).$rand);
        }
        $keys[] = $key;
    }
    $sitesArray[0]['master_client_codes'] = $keys;
    
    $sitesArray[1] = $_POST['both_site']; 
    $sitesArray[1]['created_user'] = $_SESSION['userUser'];
    $sitesArray[1]['created_password'] = $_SESSION['userPassword'];
    $sitesArray[1]['type'] = 'site'; 
    $sitesArray[1]['site_name'] = str_replace('.','_',$_POST['both_site']['domain']); 
    $sitesArray[1]['master_client_code'] = $keys[0];
    $sitesArray[1]['masterServer_domain'] = $_POST['both_ms']['domain'];
}elseif($_POST['install'] == 'ms'){
    $sitesArray[0] = $_POST['both_ms']; 
    $sitesArray[0]['created_user'] = $_SESSION['masterUser'];
    $sitesArray[0]['created_password'] = $_SESSION['masterPassword']; 
    $sitesArray[0]['type'] = 'master';    
    $sitesArray[0]['site_name'] = str_replace('.','_',$_POST['both_ms']['domain']);
}else{
    $sitesArray[0] = $_POST['both_site']; 
    $sitesArray[0]['created_user'] = $_SESSION['userUser'];
    $sitesArray[0]['created_password'] = $_SESSION['userPassword']; 
    $sitesArray[0]['type'] = 'site';    
    $sitesArray[0]['site_name'] = str_replace('.','_',$_POST['both_site']['domain']);
}
// Setting master to the account corresponding to the email
setParamsFile(
    $filesFolder.'classes/sh_admin/params/general.params.php',
    array(
        'master_by_mail' => $_POST['mail']
    ),
    'general'
);

$default = '';
$siteNames = array();
$mainDomains = array();
$sites = array();
$masterServer = '';

$error = '';
foreach($sitesArray as $oneSite){
    // We first try to connect :
    $link = @mysql_connect ($oneSite['server'] , $oneSite['user'], $oneSite['password']);
    
    if($link){
        $db_selected = mysql_select_db($oneSite['db'], $link);
        if($db_selected){
            // We check if the database is empty
            $contents = mysql_query('SHOW TABLES',$link);
            if(mysql_num_rows($contents) > 0){
                if(trim($oneSite['db']) == ''){
                    $error .= 'The database "'.$oneSite['db'].'" already exists, and is not empty...<br />';
                }
            }
        }else{
            // We try to create the database
            $contents = mysql_query('CREATE DATABASE `'.$oneSite['db'].'` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;',$link);
            $err = mysql_error($link);
            if($err){
                $error .= 'Could not create the database "'.$oneSite['db'].'". The message is "'.$err.'"<br />';
            }
        }       
        // We create the user
        list(,$host) = explode('@',mysql_result(mysql_query('SELECT CURRENT_USER();',$link),0));
        
        $qry = 'GRANT ALL PRIVILEGES ON `'.$oneSite['db'].'`.* TO '.$oneSite['created_user'].'@'.$host.' IDENTIFIED BY "'.$oneSite['created_password'].'";';
        mysql_query($qry,$link);                                                                        
        $err = mysql_error($link);
        if($err){
            $error .= 'Could not create the user "'.$oneSite['user'].'". The message is "'.$err.'"<br />';
        }
        mysql_close($link);  
        
        $siteFolder = $filesFolder.'/sites/'.$oneSite['site_name'];
        
        if($oneSite['type'] == 'master'){
            $modelFolderName = 'masterServer_model';  
        }else{
            $modelFolderName = 'site_model'; 
        }
        // Copying the site model folder
        copy_r(
            $filesFolder.'/sites/'.$modelFolderName,   
            $siteFolder
        );
	echo 'We copied '.$filesFolder.'/sites/'.$modelFolderName. ' in '.$siteFolder.'<br />';;
           
        if($oneSite['type'] == 'master'){
            // We should add the available keys to allow the client to connect
            setParamsFile( 
                $siteFolder.'/sh_params/sh_masterServer.params.php',
                array(
                    'allowed_sites_codes'=>$sitesArray[0]['master_client_codes']
                ),
                'values'
            );
        }else{
            // We should add the key that will allow the site to connect to the masterServer
            setParamsFile( 
                $siteFolder.'/sh_params/sh_masterServer.params.php',
                array(
                    'master_site_code' => $sitesArray[1]['master_client_code'],
                    'master_domain' => $sitesArray[1]['masterServer_domain']
                ),
                'values'
            );
        }
        
        // Setting the db params for this site
        setParamsFile(
            $siteFolder.'/sh_params/sh_db.params.php',
            array(
                'host' => $oneSite['server'], 
                'user' => $oneSite['created_user'], 
                'password' => $oneSite['created_password'],
                'database' => $oneSite['db'],
                'prefix' =>  $oneSite['prefix']
            ),
            'values'
        );
        $default = $oneSite['site_name'];
        $defaultDomain = $oneSite['domain'];
        $sitesNames[$default] = $oneSite['domain'];
        $mainDomains[$default] = $oneSite['domain'];
        $domainRegExp = '`'.str_replace(
            '.',
            '\\.',
            $oneSite['domain']
        ).'`';
        $sites[$domainRegExp] = $default;
        
        if($oneSite['type'] == 'master'){
            $masterServer = $default;
            $masterServerDomain = $oneSite['domain'];
        }
    }else{
        $error .= 'Could not connect to server "'.$oneSite['server'].'" using the user "'.$oneSite['user'].'" and the given password<br />';
    }
}

if($error){
    $step = 2;
}else{
    // Creating the list.php file
    $f = fopen($filesFolder.'sites/list.php','w+');
    fwrite($f,"<?php\n\n");
    fwrite($f,'$default = "'.$default.'";'."\n\n");
    fwrite($f,'$sitesNames = '.var_export($sitesNames,true).';'."\n\n");
    fwrite($f,'$mainDomains = '.var_export($mainDomains,true).';'."\n\n");
    fwrite($f,'$sites = '.var_export($sites,true).';'."\n\n"); 
    fwrite($f,'$redirections = array();'."\n\n"); 
    fwrite($f,'$masterServer = "'.$masterServer.'";'."\n\n");
    fwrite($f,'$localMasterServers = array("'.$masterServer.'");'."\n\n");
    fwrite($f,'$devMasterServer = "'.$masterServerDomain.'";'."\n\n");
    fwrite($f,'$prodMasterServer = "'.$masterServerDomain.'";'."\n\n");
    
    unlink($filesFolder.'needs_installation.php');
    
    // We load a page on every server we installed, in order to make them create their database
    // We load a page that is short, in order not to have a memory limit error
    if(isset($sitesArray[1]['domain'])){              
        echo 'Preparing server for '.'http://'.$sitesArray[1]['domain'].'/ (creating the databse structure and updating the site\'s folder contents)<br />';
        echo file_get_contents('http://'.$sitesArray[1]['domain'].'/sh_updater/ajax_echo_ok.php').'<br />';
    }
    echo 'Preparing server for '.'http://'.$sitesArray[0]['domain'].'/ (creating the databse structure and updating the site\'s folder contents)<br />';
    echo file_get_contents('http://'.$sitesArray[0]['domain'].'/sh_updater/ajax_echo_ok.php').'<br />';
    
    
    echo '<a href="http://'.$defaultDomain.'/user/createAccount.php?mail='.$_POST['mail'].'">Create your account</a><br />';
    echo 'Ended!';
    
    $ended = true;
}


function setParamsFile($file,$content,$type = 'general'){
    $f = fopen($file,'w+');
    fwrite($f,'<?php
/**
 * Params file for a class in the Shopsailors\' project
 * Licensed under CeCILL
 */

if(!defined(\'SH_MARKER\')){header(\'location: directCallForbidden.php\');}

$this->'.$type.' = ');
    fwrite($f,var_export($content,true));
    fwrite($f,';');
    fclose($f);
}
