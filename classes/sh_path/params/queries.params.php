<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    'getParent' => array(
        'query' => 'INSERT INTO
            `###book_categories` (`name`)
            VALUES ("")',
        'type' => 'set'),
    'getCategoryByLink' => array(
        'query' => 'SELECT
            `category`
            FROM
            `###menus`
            WHERE
            `link`="{link}"
            LIMIT 1;',
        'type' =>'get'),
    'getCategoryInformations' => array(
        'query' => 'SELECT `title`,`link`
            FROM `###menus`
            WHERE `category`="{category}"
            AND `position`="0" ;',
        'type' => 'get'),
    'getUriByPage' => array(
        'query' => 'SELECT
            `uri`, `reverse`
            FROM
            `###uri`
            WHERE
            `page`="{page}"
            LIMIT 1;',
        'type' =>'get'),
    'getPageByUri' => array(
        'query' => 'SELECT
            `page`
            FROM
            `###uri`
            WHERE
            `uri`="{uri}"
            LIMIT 1;',
        'type' =>'get'),
    'getUriByPage' => array(
        'query' => 'SELECT
            `uri`, `reverse`
            FROM
            `###uri`
            WHERE
            `page`="{page}"
            LIMIT 1;',
        'type' =>'get'),
    'getUriByReverse' => array(
        'query' => 'SELECT
            `{field}`
            FROM ###{table}
            WHERE `{id}`="{value}";',
        'type' =>'get')
);
