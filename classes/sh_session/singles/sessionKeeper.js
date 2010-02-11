/* Calls a page to restart the session time to leave from 0 */
/* Can also be used to send some js to the user (using the $_SESSION['adminSendJsOnSessionKeeper'] variable) */
new PeriodicalExecuter(function(pe) {
    new Ajax.Request('/sh_session/singles/sessionKeeper.php', {
        // No need to tell anything, we only have to call the file to have the sessions renewed
    });
}, 120);