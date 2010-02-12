function returnElement(name) {
    if(returnNeeded){
        window.opener.browser_return(returnMethod,name,returnParam);
        window.close ();
    }
}
function showContent(folder){
    lastOpenedFolder = folder;
    new Ajax.Updater('images',showContentLink + '?browserSession='+browserSession+'&folder='+folder,{evalScripts:true});
}
function showLastOpenedFolder(){
    showContent(lastOpenedFolder);
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
    new Ajax.Updater(element,url,{evalScripts:true});
}

function inPlaceRenameCancel(element){
    $(element + "_form").innerHTML = previousContents[element];
}
function deleteFile(element){
    rep = confirm(reallyDeleteI18n);

    if(rep == true){
        url = deleteLink + '?element=' + element;
        new Ajax.Request(url,{
                onSuccess: function(transport){
                    var response = transport.responseText;
                    if(response.substring(0,5) == 'ERROR'){
                        alert(couldNotDeleteFileI18n + response);
                    }else{
                        showLastOpenedFolder();
                    }
                }
            });
    }
}
function addFolder(element){
    var value = prompt(newFolderNameI18n, folderNameI18n);
    if(!(value == null || value == '')){
        url = addFolderLink + '?element=' + element + '|' + value;
        new Ajax.Request(url,{
            onSuccess: function(transport){
                var response = transport.responseText;
                if(response.substring(0,5) == 'ERROR'){
                    alert(response.substring(5,response.length));
                }else{
                    document.location.reload();
                }
            }
        });
    }
}
function renameFolder(element, oldName){
    var value = prompt(folderNewNameI18n, oldName);
    if(!(value == null || value == '')){
        url = renameFolderLink + '?element=' + element + '|' + value;
        new Ajax.Request(url,{
            onSuccess: function(transport){
                var response = transport.responseText;
                if(response.substring(0,5) == 'ERROR'){
                    alert(response.substring(5,response.length));
                }else{
                    document.location.reload();
                }
            }
        });
    }
}
function deleteFolder(folder){
    rep = confirm(confirmDeleteI18n);
    if(rep == true){
        url = deleteFolderLink + '?element=' + folder;
        new Ajax.Request(url,{
            onSuccess: function(transport){
                var response = transport.responseText;
                if(response.substring(0,5) == 'ERROR'){
                    alert(response.substring(5,response.length));
                }else{
                    document.location.reload();
                }
            }
        });
    }
}