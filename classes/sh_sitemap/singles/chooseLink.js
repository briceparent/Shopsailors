
function inPlaceChangeLink(linkInput){
    var link = $(linkInput).value;
    new Ajax.Request(
        '/sitemap/chooseLink.php?value=' + link + '&id=' + linkInput,
        {
            onSuccess: function (transport){
                sh_popup.prompt(
                    transport.responseText,
                    link,
                    {
                        title:i18n_chooseLinkTitle,
                        width: 620,
                        onpromptok: function(value){
                            changeLink(linkInput,value);
                        }
                    }
                );
            }
        }
    );
}

function toggleCategory(category){
    /* We first close all of them */
    $$('.index_class').each(function(el){
        el.style.display = 'none';
    });
    $(category).style.display = '';
    sh_popup.resizeToContent();
}

function link_chosen(){
    $$('input.page').each(function(s) {
        if(s.checked){
            chosenValue=s.value;
        }
    });
    if(chosenValue == "hardLink"){
        chosenValue = $('hardLink_value').value;
    }
    textValue = $('hardLink_value').innerHTML;
    window.opener.changeLink('<RENDER_VALUE what="category:id"/>',chosenValue);
    window.close ();
}

function changeLink(id,value){
    $(id).value = value;
}

