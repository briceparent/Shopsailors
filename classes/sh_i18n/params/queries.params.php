<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    'getMax' => array(
        'query' => 'SELECT
            MAX(`id`) as id
            FROM
            ###i18n
            WHERE `class`="{class}"',
        'type' =>'get'),
    'get' => array(
        'query' => 'SELECT
            `text`
            FROM
            ###i18n
            WHERE `class`="{class}"
            AND `id`="{id}"
            AND `lang`="{lang}"
            LIMIT 1;',
        'type' =>'get'),
    'remove' => array(
        'query' => 'DELETE 
            FROM 
            ###i18n
            WHERE `class`="{class}"
            AND 
            `id` = "{id}" 
            AND 
            `lang` = "{lang}"
            LIMIT 1;',
        'type' =>'set'),
    'removeAll' => array(
        'query' => 'DELETE
            FROM
            ###i18n
            WHERE `class`="{class}"
            AND 
            `id` = "{id}";',
        'type' =>'set'),
    'set' => array(
        'query' => 'INSERT 
            INTO 
            ###i18n
            (`class`, `id`, `lang`, `text`)
            VALUES 
            ("{class}", "{id}", "{lang}", "{value}");',
        'type' =>'set')
);
