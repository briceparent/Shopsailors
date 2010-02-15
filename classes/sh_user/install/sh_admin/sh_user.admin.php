<?php
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$adminMenu['Contenu'][] = array(
    'link'=>'user/manage/','text'=>'Restrictions d\'accès','icon'=>'picto_security.png'
);
