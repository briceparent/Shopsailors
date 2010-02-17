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
            `summary`,
            `image`,
            DATE_FORMAT(`date`,"%d/%m/%Y") as date,
            `content`,
            showDate,
            showTitle
            FROM
            ###content
            WHERE `active` = "1"
            AND `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'getWithInactive' => array(
        'query' => 'SELECT
            `id`,
            `title`,
            `summary`,
            `image`,
            DATE_FORMAT(`date`,"%d/%m/%Y") as date,
            `content`,
            `showDate`,
            `showTitle`,
            `active`
            FROM
            ###content
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'delete' => array(
        'query' => 'DELETE
            FROM
            ###content
            WHERE
            `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'getTitle' => array(
        'query' => 'SELECT
            `title`
            FROM
            ###content
            WHERE `active` = "1"
            AND `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'getTitleWithInactive' => array(
        'query' => 'SELECT
            `title`
            FROM
            ###content
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'getShort' => array(
        'query' => 'SELECT
            `id`,
            `title`,
            `summary`,
            `image`,
            DATE_FORMAT(`date`,"%d/%m/%Y") as date,
            showDate,
            showTitle
            FROM
            ###content
            WHERE `active` = "1"
            AND `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'getNewId' => array(
        'query' => 'SELECT
            (MAX(`id`) + 1) as new
            FROM
            ###content
            WHERE 1;',
        'type' =>'get'
    ),
    'create' => array(
        'query' => 'INSERT INTO ###content
            (`active`)
            VALUES
            (1)',
        'type' =>'insert'
    ),
    'save' => array(
        'query' => 'UPDATE ###content
            SET
            `image`="{image}",
            `active`="{active}",
            `showDate`="{showDate}",
            `showTitle`="{showTitle}",
            `title`="{title}",
            `content`="{content}",
            `summary`="{summary}"
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'exists' => array(
        'query' => '
            SELECT 1 FROM 
            ###content
            WHERE
            exists(
            SELECT * FROM ###content WHERE `id`="{id}"
            );',
        'type' => 'get'
    )
);