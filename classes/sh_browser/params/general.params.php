<?php
/**
 * Params file for the \"".$this->object->__tostring()."\" extension
 *
 * Params file version : 0.2
 * Licensed under LGPL
 */

if(!defined('SH_MARKER')) {
    header('location: directCallForbidden.php');
}

$this->general = array (
        'types' =>
        array (
                'any' => array(
                        'jpg','jpeg','png','bmp','gif','svg','dxf','tiff','tif',
                        'css','xml','csv','txt','htm','html',
                        'pdf','doc','docx','xls','odt','rtf',
                        'avi','mov','ogv','flv','mpeg','ogm',
                        'mp3','wav','ogg','oga','wma','au','aac','mp4','m4a',
                        'zip','rar','tar','gz','7z'

                ),
                'uploadFormats' =>
                array (
                        0 => 'jpg',
                        1 => 'jpeg',
                        2 => 'png',
                        3 => 'bmp',
                        4 => 'gif',
                        5 => 'css',
                        6 => 'xml',
                ),
                'images' =>
                array (
                        0 => 'jpg',
                        1 => 'jpeg',
                        2 => 'png',
                        3 => 'bmp',
                        4 => 'gif',
                        5 => 'tif',
                        6 => 'tiff',
                        7 => 'svg',
                ),
                'fileTypes' =>
                array (
                        'images' =>
                        array (
                                0 => '.jpg',
                                1 => 'jpeg',
                                2 => '.png',
                                3 => '.bmp',
                                4 => '.gif',
                        ),
                ),
                'folders' =>
                array (
                        'images' => '/images/site/',
                ),
        ),
        'shownAsImage' =>
        array (
                0 => 'jpg',
                1 => 'jpeg',
                2 => 'png',
                3 => 'bmp',
                4 => 'gif',
                5 => 'tif',
                6 => 'tiff',
        ),
        'version' => '1.09.118.1',
);
