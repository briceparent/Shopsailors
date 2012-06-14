<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    'count_db_entries' => array(
        'query' => 'SELECT
            COUNT(*) AS count
            FROM
            ###i18n
            ;',
        'type' =>'get'),
    'get_20_entries' => array(
        'query' => 'SELECT
            `text`,`class`,`id`,`lang`
            FROM
            ###i18n
            LIMIT {from},20;',
        'type' =>'get'),
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
    'get_star' => array(
        'query' => 'SELECT
            `text`,
            `lang`
            FROM
            ###i18n
            WHERE `class`="{class}"
            AND `id`="{id}";',
        'type' =>'get'),
    'export' => array(
        'query' => 'SELECT
            *
            FROM
            ###i18n
            WHERE `class`="{class}";',
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
        'type' =>'set'),

    'create_table' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###i18n` (
  `class` varchar(30) COLLATE utf8_bin NOT NULL,
  `id` bigint(20) NOT NULL,
  `lang` varchar(8) COLLATE utf8_bin NOT NULL,
  `text` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `class` (`class`,`id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;',
        'type' =>'set'),
);
