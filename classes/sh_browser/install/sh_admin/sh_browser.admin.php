<?php
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$adminMenu['Médias'] = array(
    array(
        'type'=>'popup',
        'width'=>750,
        'height'=>410,
        'link'=>'browser/show/',
        'text'=>'Accéder à l\'explorateur',
        'icon'=>'picto_browser.png'
    )
);
