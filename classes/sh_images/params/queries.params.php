<?php
/**
 * Params file for the images extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
        'isInMenu' => array(
            'query' => 'SELECT
                `link`
                FROM ###menus
                WHERE `link`="{link}" LIMIT 1;',
             'type' => 'get'),
        'updateCategoryAndPosition' => array(
            'query' => 'UPDATE ###menus
                SET
                `category`="0",
                `position`="{newPosition}"
                WHERE
                `menu`="{menuId}",
                `category`="{category}",
                `position`="{position}",
                `link`="{link}";',
            'type' => 'get'),
        'insertImage' => array(
            'query' => 'INSERT INTO ###images
                (`text`, `font`,`fontsize`, `path`, `position`, `type`,`state`, `width`,`height`,`startX`,`startY`)
                VALUES
                ("{text}","{font}","{fontsize}","{path}","{position}","{type}","{state}","{width}","{height}",{startX},{startY})',
            'type' => 'get'),
        'deleteOneFolder' => array(
            'query' => 'DELETE FROM ###images
                WHERE LEFT(`path`,CHAR_LENGTH(\'{folder}\'))="{folder}";',
            'type' => 'set'),
        'getImage' => array(
            'query' => 'SELECT
                `path`,
                `text`,
                `font`,
                `fontsize`,
                `position`,
                `type`,
                `state`,
                `width`,
                `height`,
                `startX`,
                `startY`
                FROM ###images
                WHERE
                `path` = "{path}"
                LIMIT 1;',
            'type' => 'get'),

        'add' => array(
            'query' => 'INSERT INTO ###imagesGeneration
                (`folder`,`image`,`class`)
                VALUES
                ("{folder}","{image}","{class}");',
            'type' => 'insert'),
        'getClass' => array(
            'query' => 'SELECT
                `folder`,
                `image`,
                `class`
                FROM ###imagesGeneration
                WHERE
                `folder` = "{folder}"
                AND `image` = "{image}"
                LIMIT 1;',
            'type' => 'get'),
        'deleteByFolder' => array(
            'query' => 'DELETE FROM ###imagesGeneration
                WHERE `folder` = "{folder}";',
            'type' => 'set'),
        'deleteByName' => array(
            'query' => 'DELETE FROM ###imagesGeneration
                WHERE `folder` = "{folder}" AND `image` = "{image}" LIMIT 1;',
            'type' => 'set'),

        'create_table_1' => array(
            'query' => 'CREATE TABLE IF NOT EXISTS `###images` (
  `path` varchar(150) COLLATE utf8_bin NOT NULL,
  `type` varchar(30) COLLATE utf8_bin NOT NULL,
  `text` varchar(100) COLLATE utf8_bin NOT NULL,
  `state` enum(\'passive\',\'active\',\'selected\') COLLATE utf8_bin NOT NULL DEFAULT \'passive\',
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `fontsize` smallint(6) NOT NULL,
  `font` varchar(150) COLLATE utf8_bin NOT NULL,
  `position` enum(\'normal\',\'first\',\'last\') COLLATE utf8_bin NOT NULL DEFAULT \'normal\',
  `startX` smallint(6) DEFAULT NULL,
  `startY` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`path`,`state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;',
            'type' => 'insert'),
        'create_table_2' => array(
            'query' => 'CREATE TABLE IF NOT EXISTS `###imagesGeneration` (
  `folder` varchar(128) NOT NULL,
  `image` varchar(128) NOT NULL,
  `class` varchar(32) NOT NULL,
  UNIQUE KEY `folder` (`folder`,`image`,`class`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
            'type' => 'insert'),
);
