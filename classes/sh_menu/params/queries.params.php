<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
        'deleteMenuItems' => array(
            'query' => 'DELETE FROM ###menus
                WHERE `menu`="{menuId}"
                AND `link`!="UNAFFECTED";',
            'type' => 'set'),
        'addMenuItem' => array(
            'query' => 'INSERT INTO ###menus
                (`menu`, `link`,`category`, `position`,`title`,`image`)
                VALUES
                ("{menuId}","{link}","{category}","{position}","{title}","{image}")',
            'type' => 'get'),
        'isInMenu' => array(
            'query' => 'SELECT
                `link`
                FROM ###menus
                WHERE `link`="{link}" LIMIT 1;',
             'type' => 'get'),
        'getMenusByMenuId' => array(
            'query' => 'SELECT
                `category`, `title`, `link`, `position`, `image`
                FROM ###menus
                WHERE `menu`="{menuId}" 
                AND `category` != "0"
                AND `position` = "0"
                ORDER BY `category`, `position`',
            'type' => 'get'),
        'getMenusI18nsByMenuId' => array(
            'query' => 'SELECT
                `title`
                FROM `###menus`
                WHERE `menu`="{menuId}"
                AND `category` != "0"',
            'type' => 'get'),
        'getMenuLink' => array(
            'query' => 'SELECT
                `link`
                FROM ###menus
                WHERE `menu`="{menuId}" 
                AND `category` = "{category}"
                AND `position` = "0";',
            'type' => 'get'),
       'getForRenderer' => array(
            'query' => 'SELECT
                `category`, `title`, `link`, `position`, `image`
                FROM ###menus
                WHERE `menu`="{menuId}" 
                AND `category` != "0"
                AND `position` = "0"
                ORDER BY `category`, `position`',
            'type' => 'get'),
        'deleteMenuByMenuId' => array(
            'query' => 'DELETE FROM ###menus
                WHERE `menu`="{menuId}"
                AND `position`="0";',
            'type' => 'set'),
        'getElementsByMenuId' => array(
            'query' => 'SELECT
                `category`, `link`, `position`
                FROM ###menus
                WHERE `menu`="{menuId}"',
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
        'insertElement' => array(
            'query' => 'INSERT INTO ###menus
                (`menu`, `link`,`category`, `position`,`title`,`image`)
                VALUES
                ("{menuId}","{link}","{category}","{position}","{title}","{image}")',
            'type' => 'get'),
        'getNewElementPosition' => array(
            'query' => 'SELECT
                MAX(`category`) as max
                FROM
                ###menus
                WHERE
                `menu`="{menuId}" ;',
            'type' => 'get'),
        'updateElement' => array(
            'query' => 'UPDATE ###menus
                SET
                `menu`="{menuId}",
                `category`="{category}", 
                `position`="{position}"
                WHERE
                `link`="{link}"
                AND
                `position`!="0"
                LIMIT 1;',
            'type' => 'get'),
        'getCategories' => array(
            'query' => 'SELECT
                `id`,
                `name`
                FROM
                ###book_categories
                WHERE
                `id`>"0" ;',
            'type' => 'get')

        );
