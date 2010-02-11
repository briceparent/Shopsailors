<?php
session_start();

$className = basename(dirname(dirname(__FILE__)));

$_SESSION[$className]['adminBoxPosX'] = $_GET['x'];
$_SESSION[$className]['adminBoxPosY'] = 0;
echo 'on a trouve '.$_SESSION[$className]['adminBoxPosX'].':'.$_SESSION[$className]['adminBoxPosY'];
