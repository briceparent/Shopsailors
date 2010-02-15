<?php
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$masterMenu['Section Master'][] = array(
    'link'=>'templatesLister/build/','text'=>'Variations des templates','icon'=>'picto_tool.png'
);
