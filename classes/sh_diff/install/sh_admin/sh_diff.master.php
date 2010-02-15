<?php
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

if(is_dir(SH_CLASS_FOLDER.'sh_diff/')){
    $masterMenu['Section Développeur'][] = array(
        'link'=>'diff/showDiff/','text'=>'Différences','icon'=>'picto_tool.png'
    );
}
