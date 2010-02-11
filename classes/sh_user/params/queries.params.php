<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    'getOneUserId' => array(
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
    'connectOneUserWithChallenge' => array(
        'query' => '
            SELECT
            `id`
            FROM
            ###users
            WHERE
            `login` = "{userName}"
            AND
            MD5("{challenge}-".`password`) = "{challengePassword}"
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
            `verification`,
            `active`,
            `id`
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
    'createAccount_setIncrement' => array(
        'query' => '
            ALTER TABLE `users`
            AUTO_INCREMENT = {increment};',
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
            (`login`,`active`,`name`,`lastName`,`mail`,`phone`,`password`,`address`,`verification`)
            VALUES
            ("{login}","0","{name}","{lastName}","{mail}","{phone}","{password}","{address}","{verification}");',
        'type' =>'insert'
    ),
    // Connections status logger
    'update_connection_successfull' => array(
        'query' => '
            UPDATE ###connections_successes
            SET
            `date` = CURRENT_TIMESTAMP(),
            `site` = "{site}"
            WHERE
            `user` = "{user}";',
        'type' =>'insert'
    ),
    'add_connection_successfull' => array(
        'query' => '
            INSERT INTO
            ###connections_successes
            (`user`,`site`,`date`)
            VALUES
            ("{user}","{site}",CURRENT_TIMESTAMP());',
        'type' =>'insert'
    ),
    'get_connection_successfull' => array(
        'query' => '
            SELECT
            `date`,`site`
            FROM
            ###connections_successes
            WHERE
            `user` = "{user}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'add_connection_failure' => array(
        'query' => '
            INSERT INTO
            ###connections_failures
            (`user`,`site`,`date`,`ip`)
            VALUES
            ("{user}","{site}",CURRENT_TIMESTAMP(),"{ip}");',
        'type' =>'insert'
    ),
    'get_connection_failures_number' => array(
        'query' => '
            SELECT
            COUNT(*) as count
            FROM
            ###connections_failures
            WHERE
            `user` = "{user}" AND
            `shown` = "0";',
        'type' =>'get'
    ),
    'get_connection_failures' => array(
        'query' => '
            SELECT
            `date`,`site`,`ip`
            FROM
            ###connections_failures
            WHERE
            `user` = "{user}" AND
            `shown` = "0"
            ORDER BY `date` DESC
            LIMIT 10;',
        'type' =>'get'
    ),
    'clear_connections_failures' => array(
        'query' => '
            UPDATE ###connections_failures
            SET
            `shown` = 1
            WHERE
            `user` = "{user}";',
        'type' =>'set'
    ),
    'clear_older_connections_failures' => array(
        'query' => '
            DELETE
            FROM
            ###connections_failures
            WHERE
            DATEDIFF(CURDATE() , `date`) > 30;',
        'type' =>'set'
    ),
);
