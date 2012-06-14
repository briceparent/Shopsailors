<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    'shared_methods_get' => array(
        'query' => 'SELECT
            `loaded_class`
            FROM
            ###shared_methods
            WHERE `loader_class` = "{loader_class}"
            AND `type` = "{type}";',
        'type' =>'get'
    ),
    'shared_methods_delete_one' => array(
        'query' => 'DELETE
            FROM
            ###shared_methods
            WHERE `loader_class` = "{loader_class}"
            AND `type` = "{type}"
            AND `loaded_class` = "{loaded_class}";',
        'type' =>'set'
    ),
    'shared_methods_delete_type' => array(
        'query' => 'DELETE
            FROM
            ###shared_methods
            WHERE `loader_class` = "{loader_class}"
            AND `type` = "{type}";',
        'type' =>'set'
    ),
    'shared_methods_delete_loader' => array(
        'query' => 'DELETE
            FROM
            ###shared_methods
            WHERE `loader_class` = "{loader_class}";',
        'type' =>'set'
    ),
    'shared_methods_delete_loaded' => array(
        'query' => 'DELETE
            FROM
            ###shared_methods
            WHERE `loaded_class` = "{loaded_class}";',
        'type' =>'set'
    ),
    'shared_methods_add' => array(
        'query' => 'INSERT INTO ###shared_methods
            (`loader_class`,`type`,`loaded_class`)
            VALUES
            ("{loader_class}","{type}","{loaded_class}");',
        'type' =>'insert'
    ),

    'installed_version_get' => array(
        'query' => 'SELECT
            `version`
            FROM
            ###classes_installed_version
            WHERE `class` = "{class}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'installed_version_add' => array(
        'query' => 'INSERT INTO ###classes_installed_version
            (`class`)
            VALUES
            ("{class}");',
        'type' =>'insert'
    ),
    'installed_version_update' => array(
        'query' => 'UPDATE ###classes_installed_version
            SET
            `version`="{version}"
            WHERE `class` = "{class}"
            LIMIT 1;',
        'type' =>'insert'
    ),
    
    'store_256' => array(
        'query' => 'INSERT INTO ###datas_256
            (`class`,`data_id`,`data`)
            VALUES
            ("{class}","{data_id}","{data}");',
        'type' => 'insert'),
    
    'get_256' => array(
        'query' => 'SELECT
            `data`,
            `data_id`,
            `class`
            FROM ###datas_256
            WHERE
            `class` = "{class}"
            AND `data_id` = "{data_id}"
            LIMIT 1;',
        'type' => 'get'),
    

    'classes_installed_version_add_table'=> array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###classes_installed_version` (
  `class` varchar(32) NOT NULL,
  `version` varchar(32) NOT NULL,
  PRIMARY KEY (`class`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' =>'get'
    ),
    'shared_methods_add_table'=> array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###shared_methods` (
  `loader_class` varchar(32) NOT NULL,
  `type` varchar(32) NOT NULL,
  `loaded_class` varchar(32) NOT NULL,
  UNIQUE KEY `loader_class` (`loader_class`,`type`,`loaded_class`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' =>'get'
    ),
    'create_table_datas_256' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `datas_256` (
  `class` VARCHAR( 32 ) NOT NULL,
  `data_id` varchar(256) NOT NULL,
  `data` varchar(256) NOT NULL,
  UNIQUE KEY `unique` (`class`,`data_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
',
            'type' => 'insert'),

);
