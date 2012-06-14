function returnElement(name,width,height) {
    if(returnNeeded){
        window.opener.browser_return(returnMethod,name,returnParam,width,height);
        window.close ();
    }
}
function showContent(folder){
    lastOpenedFolder = folder;
    new Ajax.Updater('images',showContentLink + '?browserSession='+browserSession+'&folder='+folder,{
        evalScripts:true
    });
}
function showLastOpenedFolder(){
    showContent(lastOpenedFolder);
}
function setTitleToFile(image,tag){
    var element = tag.up('td').previous().down();
    var title = element.title;
    sh_popup.prompt(fileNewTitleI18n, title,
    {
        title:'Titre de la photo',
        textarea:true,
        onpromptok: function(value){
            value = value.replace('\n',' \n');
            if(!(value == null || value == '')){
                element.title = value;
                url = setTitleLink;
                var params = {element:image,title:value};
                new Ajax.Request(url,{        
                    method: 'post', 
                    parameters: params, 
                    onSuccess: function(transport){
                        var response = transport.responseText;
                        if(response.substring(0,5) == 'ERROR'){
                            sh_popup.alert(response.substring(5,response.length));
                        }
                    }
                });
            }
        }
    });
}
function inPlaceRename(element){
    previousContents[element] = $(element + "_form").innerHTML;
    $(element + "_form").innerHTML = '<input id="' + element + '_element" value="' + $(element).innerHTML + '"/>';
    $(element + "_form").innerHTML += '<img style="cursor:pointer;" src="/templates/global/admin/sh_browser/validate.png" onclick="inPlaceRenameUpdate(\'' + element + '\')"/>';
    $(element + "_form").innerHTML += '<img style="cursor:pointer;" src="/templates/global/admin/sh_browser/cancel.png" onclick="inPlaceRenameCancel(\'' + element + '\')"/>';
}
function inPlaceRenameUpdate(element){
    url = renameLink + '?element=' + element + '|' + $(element + '_element').value;
    $(element + "_form").innerHTML = previousContents[element];
    $(element).innerHTML = updatingI18n;
    new Ajax.Updater(element,url,{
        evalScripts:true
    });
}

function inPlaceRenameCancel(element){
    $(element + "_form").innerHTML = previousContents[element];
}
function deleteFile(element){
    sh_popup.confirm(reallyDeleteI18n,
    {
        title:'Confirmation de la suppression',
        onconfirmok:function(){
            url = deleteLink + '?element=' + element;
            new Ajax.Request(url,{
                onSuccess: function(transport){
                    var response = transport.responseText;
                    if(response.substring(0,5) == 'ERROR'){
                        sh_popup.alert(couldNotDeleteFileI18n + response);
                    }else{
                        showLastOpenedFolder();
                    }
                }
            });
        }
    });

}
function addFolder(element){
    value = sh_popup.prompt(
        newFolderNameI18n, folderNameI18n,
        {
            title:'Création d\'un dossier',
            onpromptok: function(value){
                if(!(value == null || value == '')){
                    url = addFolderLink + '?element=' + element + '|' + value;
                    new Ajax.Request(url,{
                        onSuccess: function(transport){
                            var response = transport.responseText;
                            if(response.substring(0,5) == 'ERROR'){
                                sh_popup.alert(response.substring(5,response.length));
                            }else{
                                document.location.reload();
                            }
                        }
                    });
                }
            }
        }
        );
}
function renameFolder(element, oldName){
    sh_popup.prompt(folderNewNameI18n, oldName,
    {
        title:'Renommage de dossier',
        onpromptok: function(value){
            if(!(value == null || value == '')){
                url = renameFolderLink + '?element=' + element + '|' + value;
                new Ajax.Request(url,{
                    onSuccess: function(transport){
                        var response = transport.responseText;
                        if(response.substring(0,5) == 'ERROR'){
                            sh_popup.alert(response.substring(5,response.length));
                        }else{
                            document.location.reload();
                        }
                    }
                });
            }
        }
    });
}
function deleteFolder(folder){
    sh_popup.confirm(confirmDeleteI18n,
    {
        title:'Confirmation de la suppression',
        onconfirmok:function(){
            url = deleteFolderLink + '?element=' + folder;
            new Ajax.Request(url,{
                onSuccess: function(transport){
                    var response = transport.responseText;
                    if(response.substring(0,5) == 'ERROR'){
                        sh_popup.alert(response.substring(5,response.length));
                    }else{
                        document.location.reload();
                    }
                }
            });
        }
    });
}