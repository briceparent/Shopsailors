<?php
session_start();
$_SESSION['htaccess_didnt_work'] = true;
header('location: installer.php');