<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    'copy' => array(
        'query' => '
            SELECT
            `id`
            FROM
            ###users
            WHERE
            `{field}` = "{value}"
            ;',
        'type' =>'get'
    ),
    'connectOneUser' => array(
        'query' => '
            SELECT
            `id`
            FROM
            ###users
            WHERE
            `login` = "{userName}"
            AND
            `password` = "{password}"
            AND
            `verification` = "{verification}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'connectOneUserWithNewPassword' => array(
        'query' => '
            SELECT
            `id`
            FROM
            ###users
            WHERE
            `login` = "{userName}"
            AND
            `verification` = "{verification}"
            AND
            `temporaryPassword` = "{temporaryPassword}"
            AND
            TIMESTAMPDIFF(HOUR,`temporaryPasswordTimestamp`,NOW()) <= 48
            LIMIT 1;',
        'type' =>'get'
    ),
    'getOneUserVerification' => array(
        'query' => '
            SELECT
            `verification`
            FROM
            ###users
            WHERE
            `login` = "{userName}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'getAllowedUsers' => array(
        'query' => '
            SELECT
            `login`,
            `name`,
            `lastName`
            FROM
            ###users
            WHERE
            1;',
        'type' =>'get'
    ),
    'verify' => array(
        'query' => '
            SELECT
            `id`,
            `active`
            FROM
            ###users
            WHERE
            `login` = "{name}"
            AND 
            `password` = "{password}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'changePassword' => array(
        'query' => '
            UPDATE ###users SET
            `password` = "{newPassword}",
            `temporaryPasswordTimestamp` = ""
            WHERE
            `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'addTemporaryPassword' => array(
        'query' => '
            UPDATE ###users SET
            `temporaryPassword` = "{temporaryPassword}",
            `temporaryPasswordTimestamp` = NOW()
            WHERE
            `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'getUserData' => array(
        'query' => '
            SELECT
            *
            FROM
            ###users
            WHERE
            `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'activateAccount' => array(
        'query' => 'UPDATE
            ###users
            SET
            `active`="1"
            WHERE
            `mail` = "{mail}"
             LIMIT 1;',
        'type' =>'set'
    ),
    'createAccount' => array(
        'query' => '
            INSERT INTO 
            ###users
            (`login`,`active`,`name`,`lastName`,`mail`,`phone`,`password`,`address`)
            VALUES
            ("{login}","0","{name}","{lastName}","{mail}","{phone}","{password}","{address}");',
        'type' =>'get'
    ),
);
