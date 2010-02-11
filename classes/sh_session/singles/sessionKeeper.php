<?php
// To send some javascript, we have to put it in the $_SESSION['adminSendJsOnSessionKeeper'] variable,
// and it will be sent only once
session_start();

if(!isset($_SESSION['adminSendJsOnSessionKeeper'])){
    // Sends the xml response, just telling the microtime, and exits
    echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    echo '<ret>'.microtime().'</ret>';
    exit;
}
// Sends the javascript that should be evaluated
$js = $_SESSION['adminSendJsOnSessionKeeper'];
unset($_SESSION['adminSendJsOnSessionKeeper']);
header('content-type: text/javascript');
echo $js;