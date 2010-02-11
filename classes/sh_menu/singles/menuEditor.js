/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function createTextPreview(){
    var height = $('textHeight').value;
    var font = $('font').value;
    uri = 'index.php?path_type=image&file=createPreview&font='+font+'&height='+height;
    $('textPreview').src=uri;
    $('textPreview_div').appear();
}

function removeSection(name) {
    if(confirm('Voulez-vous vraiment supprimer la section?') == true){
        $(name).fade();
        window.setTimeout(function(){
            $(name).innerHTML='';
            menus_submit_now();
        },1000);
    }
}

function createLineItemSortables() {
    for(var i = 0; i < sections.length; i++) {
        Sortable.create(sections[i],{
            tag:'div',
            dropOnEmpty: true,
            containment: sections,
            only:'lineitem'
        });
    }
}

function destroyLineItemSortables() {
    for(var i = 0; i < sections.length; i++) {
        Sortable.destroy(sections[i]);
    }
}

function createGroupSortable() {
    Sortable.create('container',{
        tag:'div',
        only:'section',
        handle:'handle'
    });
}

function menus_addEntry(){
    post = "menuId=" + $F("menuId");
    uri = "/menu/addEntry.php";
    new Ajax.Request(uri,{
        parameters : post ,
        method : "post",
        onSuccess: function(transport) {
            if(transport.responseText == 'OK'){
                menus_submit();
            }else{
                alert(transport.responseText);
            }
        }
    });
}

function menus_submit() {
    /* We first verify the total line length */
    $('editMenu_waiting').show();
    uri = "/menu/verifyLength.php";
    post = "real=true&id=" + $('menuId').value;

    tab = $$("input.form_i18n_input");
    if(tab.length > 0){
        tab.each(
            function(s, index) {
                post += "&" + s.name + '=' + s.value;
            }
        );
    }
    sectionsCount = $$(".section").length;
    font = $("font").value;
    post += "&sectionsCount=" + sectionsCount + "&font=" + font;
    textHeight = $('textHeight').value;
    post += "&textHeight=" + textHeight;
    new Ajax.Request(
        uri,
        {
            parameters : post ,
            method : "post",
            onSuccess: function(transport) {
                if(transport.responseText == 'OK'){
                    menus_submit_now();
                }else{
                    $('editMenu_waiting').hide();
                    $('menu_imagesUpdater').innerHTML = transport.responseText;
                    $('menu_imagesUpdater2').innerHTML = transport.responseText;
                }
            }
        }
    );
}
function menus_submit_now(){
    document.forms["menuEditor"].submit();
}


function inPlaceChangeLink(category,link){
    window.open (
        '/menu/chooseLink.php?value=' + link + '&id=' + category,
        'Websailors',
        config = 'width=600, height=400, toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, directories=no, status=no'
    )
}

function changeLink(id,value){
    $('visibleLink_' + id).innerHTML = value;
    $('link_' + id).value = value;
}

