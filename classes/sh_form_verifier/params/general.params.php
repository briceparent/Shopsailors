<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->general = array(
    'types' => array(
        'uploadFormats'=>array('jpg','jpeg','png','bmp','gif','css','xml'),
        'fileTypes' => array(
            'images'=> array('.jpg','jpeg','.png','.bmp','.gif')
        ),
        'folders' => array(
            'images' => SH_IMAGES_PATH
        ),
        'images'=>array('jpg','jpeg','png','bmp','gif','tif','tiff'),
    ),
    'shownAsImage'=>array('jpg','jpeg','png','bmp','gif','tif','tiff')
);
