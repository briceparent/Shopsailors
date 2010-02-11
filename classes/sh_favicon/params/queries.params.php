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
);
