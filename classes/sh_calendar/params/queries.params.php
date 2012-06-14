<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    'delete' => array(
        'query' => 'DELETE
            FROM
            ###content
            WHERE
            `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'getNewId' => array(
        'query' => 'SELECT
            (MAX(`id`) + 1) as new
            FROM
            ###content
            WHERE 1;',
        'type' =>'get'
    ),
    'create_calendar' => array(
        'query' => 'INSERT INTO ###calendar
            (`name`,`description`)
            VALUES
            ("{name}","{description}")',
        'type' =>'insert'
    ),
    'get' => array(
        'query' => 'SELECT
            `id`,
            `name`,
            `description`
            FROM
            ###calendar
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'get_list' => array(
        'query' => 'SELECT
            `id`,
            `name`,
            `description`
            FROM
            ###calendar;',
        'type' =>'get'
    ),

    
    'type_get_max' => array(
        'query' => 'SELECT
            MAX(`id`) as max
            FROM
            ###calendar_entries_type
            WHERE `calendar` = "{calendar}";',
        'type' =>'get'
    ),
    'type_create' => array(
        'query' => 'INSERT INTO ###calendar_entries_type
            (`calendar`,`id`,`color`,`name`)
            VALUES
            ("{calendar}","{id}","{color}","{name}")',
        'type' =>'insert'
    ),
    'type_set_color' => array(
        'query' => 'UPDATE ###calendar_entries_type SET
            `color` = "{color}"
            WHERE
            `calendar`="{calendar}" 
            AND `id`="{id}"',
        'type' =>'insert'
    ),
    'type_get' => array(
        'query' => 'SELECT
            `calendar`,`id`,`color`,`name`
            FROM
            ###calendar_entries_type
            WHERE `calendar` = "{calendar}" AND `id`="{id}";',
        'type' =>'get'
    ),
    'types_get' => array(
        'query' => 'SELECT
            `calendar`,`id`,`color`,`name`
            FROM
            ###calendar_entries_type
            WHERE `calendar` = "{calendar}";',
        'type' =>'get'
    ),
    
    'date_get_max' => array(
        'query' => 'SELECT
            MAX(`id`) as max
            FROM
            ###calendar_entry
            WHERE `calendar` = "{calendar}";',
        'type' =>'get'
    ),
    'date_create' => array(
        'query' => 'INSERT INTO ###calendar_entry
            (`calendar`,`id`,`date`,`title`, `content`,`type`)
            VALUES
            ("{calendar}","{id}","{date}","{title}","{content}","{type}")',
        'type' =>'insert'
    ),
    'date_set_state' => array(
        'query' => 'UPDATE ###calendar_entry SET
            `active` = "{active}"
            WHERE
            `calendar` = "{calendar}"
            AND `id` = "{id}" 
            LIMIT 1;',
        'type' =>'insert'
    ),
    'date_set_date' => array(
        'query' => 'UPDATE ###calendar_entry SET
            `date` = "{date}"
            WHERE
            `calendar` = "{calendar}"
            AND `id` = "{id}" 
            LIMIT 1;',
        'type' =>'insert'
    ),
    'date_set_type' => array(
        'query' => 'UPDATE ###calendar_entry SET
            `type` = "{type}"
            WHERE
            `calendar` = "{calendar}"
            AND `id` = "{id}" 
            LIMIT 1;',
        'type' =>'insert'
    ),
    'date_get' => array(
        'query' => 'SELECT
            `calendar`,`id`,`active`,`date`,`title`, `content`,`type`
            FROM
            ###calendar_entry
            WHERE `calendar` = "{calendar}" AND `id` = "{id}";',
        'type' =>'get'
    ),
    'date_get_active' => array(
        'query' => 'SELECT
            `calendar`,`id`,`date`,`title`, `content`,`type`
            FROM
            ###calendar_entry
            WHERE `calendar` = "{calendar}" AND `id` = "{id}" AND `active`=1;',
        'type' =>'get'
    ),
    'dates_get' => array(
        'query' => 'SELECT
            `calendar`,`id`,`active`,`date`,`title`, `content`,`type`
            FROM
            ###calendar_entry
            WHERE `calendar` = "{calendar}" AND `date` = "{date}";',
        'type' =>'get'
    ),
    'dates_get_active' => array(
        'query' => 'SELECT
            `calendar`,`id`,`date`,`title`, `content`,`type`
            FROM
            ###calendar_entry
            WHERE `calendar` = "{calendar}" AND `date` = "{date}" AND `active`=1;',
        'type' =>'get'
    ),

    'create_table_1' => array(
        'query' => '
CREATE TABLE IF NOT EXISTS `###calendar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` bigint(20) NOT NULL,
  `description` bigint(20) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;',
        'type' =>'insert'
    ),
    'create_table_2' => array(
        'query' => '
CREATE TABLE IF NOT EXISTS `###calendar_entries_type` (
  `calendar` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `color` varchar(6) NOT NULL,
  `name` bigint(20) NOT NULL,
  UNIQUE KEY `unique` (`calendar`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' =>'insert'
    ),
    'create_table_3' => array(
        'query' => '
CREATE TABLE IF NOT EXISTS `###calendar_entry` (
  `calendar` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `title` bigint(20) NOT NULL,
  `content` bigint(20) NOT NULL,
  `type` tinyint(4) NOT NULL,
  UNIQUE KEY `unique` (`calendar`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' =>'insert'
    ),
    'add_state_field' => array(
        'query' => 'ALTER TABLE `###calendar_entry` ADD `active` BOOLEAN NOT NULL DEFAULT \'1\' AFTER `id` ;',
        'type' =>'insert'
    ),
);



