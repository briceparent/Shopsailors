<?php
echo '<div class="step">Step 1 : Checking system configuration</div>';
$error = false;

$php_version = phpversion();
echo 'PHP version is '.$php_version.' : ';
if(version_compare($php_version,'5.3') < 0){
    $error = true;
    echo 'Error !  PHP version >= 5.3 required!<br />';
}else{
    echo 'OK<br />';
}
echo '<br >';

$error_reporting = error_reporting();
echo 'Error reporting is '.$error_reporting.'<br />';
if($error_reporting & E_NOTICE){
    echo '<div class="notification">E_NOTICE is on. It will dynamically be disabled.</div>';
}
echo '<br />';

$display_errors = ini_get('display_errors');
echo 'display_errors is '.($display_errors?'ON':'OFF').'<br />';
if($display_errors){
    echo '<div class="notification">Remember display_errors should never be ON on production sites.</div>';
}
echo '<br />';

echo 'Rewrite mod is '.(isset($_SESSION['htaccess_didnt_work'])?'OFF':'ON').'<br />';
if(isset($_SESSION['htaccess_didnt_work'])){
    $error = true;
    echo '<div class="notification_error">Rewrite mode should be active AND the vhost should be abble to use it through .htaccess files</div>';
}
echo '<br />';

echo 'Extensions : <br />';
$exts = get_loaded_extensions();
$neededExtensions = array(
    'mysql','gd','json','session','dom','pcre','date'
);
$desiredExtension = array(
    'mcrypt','xdebug'
);
foreach($neededExtensions as $extension){
    if(in_array($extension,$exts)){
        echo $extension.' : OK<br />';
    }else{
        echo $extension.' : NOT INSTALLED!<br />';
        echo '<div class="notification_error">This extension is required to use Shopsailors</div>';
        $error = true;
    }
}
      

foreach($desiredExtension as $extension){
    if(in_array($extension,$exts)){
        echo $extension.' : OK<br />';
    }else{
        echo $extension.' : NO (not required, but may add some capabilities/improvements<br />';
    }
}
echo '<br />';

if($error){
    echo 'Configuration error. Please modify your configuration before continuing the installation.';
}else {
    echo '<hr />';
    echo '<input type="submit" name="Next" value="next"/>';
}