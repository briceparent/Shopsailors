<?php
// We prepare the users names and pass for both databases
$_SESSION['masterUser'] = 'master_'.substr(md5(microtime()),0,6);
$_SESSION['masterPassword'] = substr(md5(microtime()),0,18);

$_SESSION['user'] = 'master_'.substr(md5(microtime()),0,6);
$_SESSION['userPassword'] = substr(md5(microtime()),0,18);

$tests = array();
if($_POST['install'] == 'both'){
    $tests[] = $_POST['both_ms'];
    $tests[] = $_POST['both_site'];
}elseif($_POST['install'] == 'ms'){
    $tests[] = $_POST['both_ms'];
}else{
    $tests[] = $_POST['both_site'];
}
$error = '';
foreach($tests as $test){
    // We first try to connect :
    $link = @mysql_connect ($test['server'] , $test['user'], $test['password']);
    
    if($link){
        var_dump($link);
        $db_selected = mysql_select_db($test['db'], $link);
        if($db_selected){
            // We check if the database is empty
            $contents = mysql_query('SHOW TABLES',$link);
            if(mysql_num_rows($contents) > 0){
                $error .= 'The database "'.$test['db'].'" already exists, and is not empty...<br />';
            }
        }else{
            // We try to create the database
            $contents = mysql_query('CREATE DATABASE `'.$test['db'].'` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;',$link);
            $err = mysql_error($link);
            if($err){
                $error .= 'Could not create the database "'.$test['db'].'". The message is "'.$err.'"<br />';
            }
        }
        mysql_close($link);
    }else{
        $error .= 'Could not connect to server "'.$test['server'].'" using the user "'.$test['user'].'" and the given password<br />';
    }
}
if($error){
    $step = 2;
}else{
    $step = 3;
}