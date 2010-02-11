<?php
/**
 * Params file for the sh_votes extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    'addVote' => array(
        'query' => 'INSERT INTO
            `###votes` (`id`, `type`, `vote`, `ip`)
            VALUES ("{id}","{type}","{vote}","{ip}")',
        'type' => 'set'),
    'changeVote' => array(
        'query' => 'UPDATE `###votes`
            SET
            `vote` = "{vote}"
            WHERE `id`="{id}" AND `type`="{type}" AND `ip` = "{ip}"',
        'type' => 'set'),
    'insertVotesCounts' => array(
        'query' => 'INSERT INTO `###votes_counts`
            (`type`,`id`,`count`, `vote`)
            VALUES
            ("{type}","{id}",0,5);',
        'type' => 'set'),
    'updateVotesCounts' => array(
        'query' => ' UPDATE `###votes_counts` SET
            `count` = (
                SELECT count( * )
                FROM `###votes`
                WHERE `type` = "{type}"
                AND `id` = "{id}"
                AND `vote` >= "0" ),
            `vote` = (
                SELECT SUM( `vote` ) / count( * )
                FROM `###votes`
                WHERE `type` = "{type}"
                AND `id` = "{id}"
                AND `vote` >= "0" )
            WHERE `type` = "{type}" AND`id` = "{id}"',
        'type' => 'set'),
    'getMyVote' => array(
        'query' => 'SELECT
            `vote`
            FROM `###votes`
            WHERE `id`="{id}" AND `type`="{type}" AND `ip`="{ip}" ',
        'type' => 'get'),
    'getVoteCount' =>array(
        'query' =>'SELECT
            `count`
            FROM `###votes_counts`
            WHERE `type`="{type}" AND `id`="{id}"',
        'type' => 'get'),
    'getPublicVote' => array(
        'query' => 'SELECT
            `vote`
            FROM `###votes_counts`
            WHERE `type`="{type}" AND `id`="{id}"',
        'type' => 'get'),
    'getDataFromReader' =>array(
        'query' =>'SELECT
            `table`, `idKey`, `searchedKey`
            FROM `###votes_reader`
            WHERE `searchType`="{searchType}"',
        'type' => 'get'),
    'getMasterVote' =>array(
        'query' =>'SELECT
            `{searchedKey}`
            FROM `###{table}`
            WHERE `{idKey}`="{value}"',
        'type' => 'get')
);
