<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    'getList' => array(
        'query' => 'SELECT
            `id`,
            `title`,
            DATE_FORMAT(`date`,"%d/%m/%Y") as date,
            `active`
            FROM
            ###content
            WHERE (`active` = "1"){orAny};',
        'type' =>'get'
        
    ),
    'get' => array(
        'query' => 'SELECT
            `id`,
            `title`,
            DATE_FORMAT(`date`,"%d/%m/%Y") as date,
            `content`,
            showDate,
            showTitle
            FROM
            ###content
            WHERE `active` = "1"
            AND `id` = "{id}" LIMIT 1;',
        'type' =>'get'),
    'getNewId' => array(
        'query' => 'SELECT
            (MAX(`id`) + 1) as new
            FROM
            ###content
            WHERE 1;',
        'type' =>'get'),
    'getWithInactive' => array(
        'query' => 'SELECT
            `id`,
            `title`,
            DATE_FORMAT(`date`,"%d/%m/%Y") as date,
            `content`,
            `showDate`,
            `showTitle`,
            `active`
            FROM
            ###content
            WHERE `id` = "{id}" LIMIT 1;',
        'type' =>'get'),
    'create' => array(
        'query' => 'INSERT INTO ###content
            (`id`,`title`,`content`)
            VALUES
            ("{newId}","NEWTITLE","NEWCONTENT")',
        'type' =>'set'),
    'save' => array(
        'query' => 'UPDATE ###content
            SET
            `title`="{title}",
            `active`="{active}",
            `content`="{content}",
            `showDate`="{showDate}",
            `showTitle`="{showTitle}"
            WHERE `id` = "{id}" LIMIT 1;',
        'type' =>'get'),
    );