
function createThumbnailsFor(template){
    // 2 steps process
    // 1/ Launching the generation
    var post = "template=" + template;
    var uri = "/template/createThumbnailsFor.php";
    var uri_for_launching = "/template/createThumbnails_start.php";
    var uri_for_progress = "/template/createThumbnails_progress.php";
    new Ajax.Request(uri,{
        parameters : post ,
        method : "post",
        onSuccess: function(transport) {
            if(transport.responseText == 'ALREADY_DONE'){
                sh_popup.alert(i18n_already_done_content,{title:i18n_already_done_title});
            }else if(transport.responseText == 'OK'){
                // Launching
                new Ajax.Request(uri_for_launching,{});
                // 2/ Updating the status
                sh_popup.wait(i18n_generating);
                var cpt = 0;
                new PeriodicalExecuter(
                    function(pe) {
                        new Ajax.Request(uri_for_progress,{
                            onSuccess: function(transport) {
                                cpt++;
                                if(transport.responseText == 'COMPLETED'){
                                    pe.stop();
                                    sh_popup.hide();
                                    $('progressContent').innerHTML = i18n_completed;
                                    $('progress').appear();
                                }
                            }
                        });
                    }, 
                    1
                );
            }else{
                $('progress').appear();
                $('progressContent').innerHTML = transport.responseText;
            }
        }
    });
}