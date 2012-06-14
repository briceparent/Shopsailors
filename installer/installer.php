<?php
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=UTF-8"></meta>
        <title>Shopsailors Installer</title>
        <link rel="shortcut icon" href="/favicon.ico"></link>
        <link rel="stylesheet" media="screen" type="text/css" href="style.css"></link>
        <script type="text/javascript" src="prototype.js"></script>
    </head>
    <body>
        <fieldset>
            <legend>Shopsailors - Installation</legend>
            <?php
            $page = $_SERVER['REQUEST_URI'];
            echo '<form action="' . $page . '" method="POST">';
            
            if( !isset( $_POST['step'] ) ) {
                $step = 1;
            } else {
                $step = $_POST['step'];
                include(dirname( __FILE__ ) . '/installer_' . $step . '_verif.php');
            }
            if(!isset($ended)){
                include(dirname( __FILE__ ) . '/installer_' . $step . '.php');
            }

            echo '<input type="hidden" name="step" value="' . $step . '"/>';
            echo '</form>';
            ?>
        </fieldset>
    </body>
</html>