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
            `###shop_categories` (`name`)
            VALUES ("")',
        'type' => 'set'),
    'updateCategory' => array(
        'query' => 'UPDATE ###shop_categories
            SET `name`="{name}"
            WHERE `id` = "{id}"',
        'type' =>'set'),
    'getLevelParent' => array(
        'query' => 'SELECT
            `parent`
            FROM
            ###shop_categories
            WHERE `id`="{id}"
            LIMIT 1;',
        'type' =>'get'),
    'getLevelSons' => array(
        'query' => 'SELECT
            `id`,
            `name`
            FROM
            ###shop_categories
            WHERE `parent`="{id}"
            LIMIT 20;',
        'type' =>'get'),
    'getLevelBrothers' => array(
        'query' => 'SELECT
            `name`,
            `id`
            FROM
            ###shop_categories
            WHERE `parent` = "{parent}"
            AND `id` != "{id}"
            LIMIT 20;',
        'type' =>'get'),
     'getLevelInfos' => array(
        'query' => 'SELECT
            `name`,
            `id`
            FROM
            ###shop_categories
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'),
    'getCategoryProducts' => array(
        'query' => 'SELECT
            `id`,
            `name`,
            `image`,
            `shortDescription`,
            `price`,
            `ref`,
            `keywords`,
            `stock`
            FROM
            ###shop_products
            WHERE `category` = "{id}"
            AND `onsale` = "1"
            LIMIT 21;',
        'type' =>'get'),
     'getProduct' => array(
        'query' => 'SELECT
            `id`,
            `name`,
            `image`,
            `shortDescription`,
            `description`,
            `category`,
            `price`,
            `ref`,
            `keywords`,
            `stock`
            FROM
            ###shop_products
            WHERE `id` = "{id}"
            AND `onsale` = "1"
            LIMIT 1;',
        'type' =>'get'),
);
