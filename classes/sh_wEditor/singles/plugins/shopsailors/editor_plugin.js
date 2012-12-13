/**
 * TinyMce Shopsailors' Plugin
 *
 * @author Shopsailors
 * @copyright Copyright 2009-2010, Shopsailors.
 * @licence Distributed under the CeCILL licence
 */
var myPrivateCounter = 0;
var tinymce_baseurl = '';
var tinymce_old_selection = null;
var tinymce_externalVideos = new Array();

if(actualOpenListBox == undefined){
    var actualOpenListBox = null;
}

function updateCustomStyles(){
    var ed = tinyMCE.activeEditor;
    var cm = ed.controlManager;
    cm.get('sh_style').remove();
}


(function() {
    tinymce.PluginManager.requireLangPack('shopsailors');
    
    var each = tinymce.each;
    tinymce.create('tinymce.plugins.shopsailorsPlugin', {
        editor : null,
        listBoxStyleElement : null,
        createControl : function(n,cm){
            if (n=='myListBox'){
                listBoxStyleElement = cm.createListBox('sh_style',{
                    title:'Style',
                    onselect: function(v){
                        var ed = tinyMCE.activeEditor;
                        var url = ed.URI;
                        if(v == 'edit'){
                            actualOpenListBox = listBoxStyleElement;
                            listBoxStyleElement.hideMenu();
                            ed.windowManager.open(
                                {
                                    file :'/wEditor/chooseStyle.php',
                                    width : 650,
                                    height : 500,
                                    inline : 1
                                }, {
                                    plugin_url : url
                                }
                            );
                        }else{
                            var oldContent = tinyMCE.activeEditor.selection.getContent();
                            tinyMCE.activeEditor.selection.setContent('<span class="'+v+'">'+oldContent+'</span>');
                        }
                    }
                },tinymce.ui.NativeListBox);
                var textStyle = '';
                for(var oneStyle in tinymce_styles_json){
                    var style = tinymce_styles_json[oneStyle];
                    listBoxStyleElement.add(
                        style.name,
                        style.cssName
                    );
                    textStyle += '.'+style.cssName+'{'+style.css+'}\n';
                }
                listBoxStyleElement.add(tinyMCE.activeEditor.getLang('shopsailors.modifyStyles'),'edit');
                return listBoxStyleElement;
                 
            }
            return false;
        },
        init : function(ed, url) {
            var me  = this;
            this.editor = ed;
            tinymce_baseurl = url;
            
            ed.onNodeChange.add(function(ed, cm, e) {
                // Activates the link button when the caret is placed in a anchor element
                var weHaveSetTheStyle = false;
                if (e.nodeName == 'SPAN'){
                    var spanClass = e.getAttribute('class','');
                    if(spanClass != ''){
                        weHaveSetTheStyle = true;
                        listBoxStyleElement.select(spanClass);
                    }
                }
                if(!weHaveSetTheStyle){
                    listBoxStyleElement.selectByIndex();
                }
                return true;
            });
            // Register commands
            ed.addCommand('mcebgcChanger', function() {
                shopsailorsListBoxStyle.add('val4','val4');
                ed.windowManager.open({
                    file : url + '/bgcChanger.php',
                    width : 250,
                    height : 220,
                    inline : 1
                }, {
                    scrollbars : "yes",
                    plugin_url : url
                });
            });
            ed.addCommand('mcelinkInserter', function() {
                ed.windowManager.open({
                    file :'/wEditor/chooseLink.php',
                    width : 500,
                    height : 400,
                    inline : 1
                }, {
                    plugin_url : url
                });
            });
            ed.addCommand('mcecalendarInserter', function() {
                ed.windowManager.open({
                    file :'/wEditor/chooseCalendar.php',
                    width : 500,
                    height : 400,
                    inline : 1
                }, {
                    plugin_url : url
                });
            });
            ed.addCommand('mceshopsailorsStyle', function() {
                ed.windowManager.open({
                    file :'/wEditor/chooseStyle.php',
                    width : 650,
                    height : 500,
                    inline : 1
                }, {
                    plugin_url : url
                });
            });
            ed.addCommand('mceimageInserter', function() {
                var folder = tinyMCE.activeEditor.getParam('imageInserter_specialFolder');
                if(folder == '' || folder == undefined){
                    folder = 'SH_IMAGES_FOLDER';
                }
                /*Launches the browser*/
                popupBrowser.clearParameters();
                popupBrowser.parameters.set('type','url');
                popupBrowser.parameters.set('types','images');
                popupBrowser.parameters.set('folder',folder);
                popupBrowser.parameters.set('action','tinymce_insert');
                popupBrowser.parameters.set('element','0');
                popupBrowser.open();
            });
            ed.addCommand('mcesoundInserter', function() {
                var folder = 'SH_IMAGES_FOLDER';
                /*Launches the browser*/
                popupBrowser.clearParameters();
                popupBrowser.parameters.set('type','url');
                popupBrowser.parameters.set('types','sounds');
                popupBrowser.parameters.set('folder',folder);
                popupBrowser.parameters.set('action','tinymce_insertSound');
                popupBrowser.parameters.set('element','0');
                popupBrowser.open();
            });
            ed.addCommand('mceshopProductInserter', function() {
                ed.windowManager.open({
                    file :'/wEditor/insert_shop_product.php',
                    width : 650,
                    height : 500,
                    inline : 1
                }, {
                    plugin_url : url
                });
            });
            ed.addCommand('mcevideoInserter', function() {/*Launches the browser*/
                video = prompt(tinyMCE.activeEditor.getLang('shopsailors.youtube_video_id'));
                if(video){
                    tinymce_insertVideo(video);
                }
            });
            ed.addCommand('mcediaporamaInserter', function() {
                ed.windowManager.open({
                    file : '/wEditor/insertDiaporama.php',
                    width : 250,
                    height : 300,
                    inline : 1
                }, {
                    plugin_url : url
                });
            });
            ed.addCommand('mceblocksInserter', function() {
                tinyMCE.util.XHR.send({
                    url : '/wEditor/addBlock.php',
                    success : function(text) {
                    }

                });

                ed.execCommand(
                    'mceInsertContent',
                    false,
                    '<table width="100"><tr><td>Block</td></tr><tr><td>Lorem ipsum...</td></tr></table>'
                    );
            });
            ed.addCommand('mcehyphenInserter', function() {
                ed.execCommand(
                    'mceInsertContent',
                    false,
                    '&#173;'
                    );
            });

            // Register buttons
            ed.addButton('bgcChanger', {
                title : tinyMCE.activeEditor.getLang('shopsailors.bgcChanger_title'),
                cmd : 'mcebgcChanger',
                image : url + '/img/bcgChanger.gif'
            });
            ed.addButton('linkInserter', {
                title : tinyMCE.activeEditor.getLang('shopsailors.linkInserter_title'),
                cmd : 'mcelinkInserter',
                image : url + '/img/linkInserter.gif'
            });
            ed.addButton('calendarInserter', {
                title : tinyMCE.activeEditor.getLang('shopsailors.calendarInserter_title'),
                cmd : 'mcecalendarInserter',
                image : url + '/img/calendarInserter.gif'
            });
            ed.addButton('styleInserter', {
                title : tinyMCE.activeEditor.getLang('shopsailors.style_title'),
                cmd : 'mceshopsailorsStyle',
                image : url + '/img/styleInserter.gif'
            });
            ed.addButton('shopProductInserter', {
                title : tinyMCE.activeEditor.getLang('shopsailors.shopProduct_title'),
                cmd : 'mceshopProductInserter',
                image : url + '/img/shopProductInserter.gif'
            });
            ed.addButton('imageInserter', {
                title : tinyMCE.activeEditor.getLang('shopsailors.imageInserter_title'),
                cmd : 'mceimageInserter',
                image : url + '/img/imageInserter.gif'
            });
            ed.addButton('soundInserter', {
                title : tinyMCE.activeEditor.getLang('shopsailors.soundInserter_title'),
                cmd : 'mcesoundInserter',
                image : url + '/img/soundInserter.gif'
            });
            ed.addButton('videoInserter', {
                title : tinyMCE.activeEditor.getLang('shopsailors.videoInserter_title'),
                cmd : 'mcevideoInserter',
                image : url + '/img/videoInserter.gif'
            });
            ed.addButton('diaporamaInserter', {
                title : tinyMCE.activeEditor.getLang('shopsailors.diaporamaInserter_title'),
                cmd : 'mcediaporamaInserter',
                image : url + '/img/diaporamaInserter.gif'
            });
            ed.addButton('blocksInserter', {
                title : 'Blocks inserter',
                cmd : 'mceblocksInserter',
                image : url + '/img/blocksInserter.gif'
            });
            ed.addButton('hyphenInserter', {
                title : 'Hyphens inserter',
                cmd : 'mcehyphenInserter',
                image : url + '/img/hyphenInserter.gif'
            });

            ed.onPreProcess.add(function(ed,o) {
                // Called right before the validating of the form
                var dom = ed.dom;
                each(dom.select('IMG', o.node), function(n) {
                    var sound, video, diaporama, el, width, height, source, id, date;
                    if(dom.getAttrib(n, 'src') == tinymce_soundImage()){
                        sound = dom.getAttrib(n, 'title');
                        el = dom.create('RENDER_SOUND', {
                            'file' : sound
                        },'Un render sound');

                        dom.replace(el,n);
                    }else if(tinymce_isCalendarImage(dom.getAttrib(n, 'src'))){
                        source = dom.getAttrib(n, 'src');
                        id = getQueryParam(source,'id');
                        date = getQueryParam(source,'date');
                        el = dom.create('RENDER_CALENDARBOX', {
                            'id' : id,
                            'date' : date
                        },'Un render calendar');

                        dom.replace(el,n);
                    }else if(dom.getAttrib(n, 'src') == tinymce_videoImage()){
                        video = dom.getAttrib(n, 'title');
                        width = dom.getAttrib(n, 'width');
                        height = dom.getAttrib(n, 'height');
                        el = dom.create('RENDER_VIDEO', {
                            'file' : video,
                            'width':width,
                            'height':height
                        },'Un render video');

                        dom.replace(el,n);
                    }else if(dom.getAttrib(n, 'src') == tinymce_diaporamaImage()){
                        diaporama = dom.getAttrib(n, 'title');
                        width = dom.getAttrib(n, 'width');
                        height = dom.getAttrib(n, 'height');
                        var style = dom.getAttrib(n, 'style');
                        var floatVal = 'none';
                        if(style.search(/float: left;/) >= 0){
                            floatVal = 'left';
                        }else if(style.search(/float: right;/) >= 0){
                            floatVal = 'right';
                        }
                        el = dom.create('RENDER_DIAPORAMA', {
                            'name' : diaporama,
                            'width':width,
                            'height':height,
                            'class':'tinymce',
                            'float' : floatVal
                        },'Un render diaporama');

                        dom.replace(el,n);
                    }else if(dom.getAttrib(n, 'src') == tinymce_flashImage()){
                        diaporama = dom.getAttrib(n, 'title');
                        width = dom.getAttrib(n, 'width');
                        height = dom.getAttrib(n, 'height');
                        el = dom.create('RENDER_FLASH', {
                            'id' : diaporama,
                            'width':width,
                            'height':height
                        },'Un render flash');

                        dom.replace(el,n);
                    }else if(dom.getAttrib(n, 'src') == tinymce_ShopProductImage()){
                        product = dom.getAttrib(n, 'title');
                        el = dom.create('RENDER_SHOPPRODUCT', {
                            'id' : product
                        },'Un render shopProduct');

                        dom.replace(el,n);
                    }
                });
            });



            ed.onSetContent.add(function(ed, o) {
                // Called when the form is loading
                var dom = ed.dom;
                var id, sound, calendar, date, video, diaporama, flashFile, el, width, height;
                each(dom.select('RENDER_SHOPPRODUCT', o.node), function(n) {
                    if(dom.getAttrib(n, 'id')){
                        id = dom.getAttrib(n, 'id');
                        el = dom.create(
                            'img',
                            {
                                'src' : tinymce_ShopProductImage(),
                                'title' : id,
                                'mce_noresize' : "1"
                            }
                            );
                        dom.replace(el,n);
                    }
                });
                each(dom.select('RENDER_SOUND', o.node), function(n) {
                    if(dom.getAttrib(n, 'file')){
                        sound = dom.getAttrib(n, 'file');
                        el = dom.create(
                            'img',
                            {
                                'src' : tinymce_soundImage(),
                                'title' : sound,
                                'mce_noresize' : "1"
                            }
                            );
                        dom.replace(el,n);
                    }
                });
                each(dom.select('RENDER_CALENDARBOX', o.node), function(n) {
                    if(dom.getAttrib(n, 'id')){
                        calendar = dom.getAttrib(n, 'id');
                        date = dom.getAttrib(n, 'date');
                        el = dom.create(
                            'img',
                            {
                                'src' : tinymce_calendarImage()+'?id='+calendar+'&date='+date,
                                'title' : 'calendar',
                                'mce_noresize' : "1"
                            }
                            );
                        dom.replace(el,n);
                    }
                });
                each(dom.select('RENDER_VIDEO', o.node), function(n) {
                    if(dom.getAttrib(n, 'file')){
                        video = dom.getAttrib(n, 'file');
                        width = dom.getAttrib(n, 'width');
                        height = dom.getAttrib(n, 'height');
                        el = dom.create(
                            'img',
                            {
                                'src' : tinymce_videoImage(),
                                'title' : video,
                                'width':width,
                                'height':height,
                                'class':'noRatio'
                            }
                            );
                        dom.replace(el,n);
                    }
                });
                each(dom.select('RENDER_DIAPORAMA', o.node), function(n) {
                    if(dom.getAttrib(n, 'name')){
                        diaporama = dom.getAttrib(n, 'name');
                        width = dom.getAttrib(n, 'width');
                        height = dom.getAttrib(n, 'height');
                        floatValue = dom.getAttrib(n, 'float');
                        var style = '';
                        if(floatValue == 'left'){
                            style = 'float: left;';
                        }else if(floatValue == 'right'){
                            style = 'float: right;';
                        }
                        el = dom.create(
                            'img',
                            {
                                'src' : tinymce_diaporamaImage(),
                                'title' : diaporama,
                                'width':width,
                                'height':height,
                                'style':style,
                                'mce_noresize' : "1"
                            }
                            );
                        dom.replace(el,n);
                    }
                });
                each(dom.select('RENDER_FLASH', o.node), function(n) {
                    if(dom.getAttrib(n, 'file')){
                        flashFile = dom.getAttrib(n, 'id');
                        width = dom.getAttrib(n, 'width');
                        height = dom.getAttrib(n, 'height');
                        el = dom.create(
                            'img',
                            {
                                'src' : tinymce_flashImage(),
                                'title' : flashFile,
                                'width':width,
                                'height':height,
                                'mce_noresize' : "1"
                            }
                            );
                        dom.replace(el,n);
                    }
                });
            });
        },
        
        getInfo : function() {
            return {
                longname : 'Shopsailor\'s addons',
                author : 'Shopsailors',
                authorurl : 'http://www.shopsailors.fr',
                infourl : 'http://www.shopsailors.fr',
                version : "1.0"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add('shopsailors', tinymce.plugins.shopsailorsPlugin);
})();

function getQueryParam(query, param){
    query = query.split('?')[1];
    
    params = query.split("&");

    for(i=0;i<params.length;i++){
        var thisParam = params[i].split("=");
        if(thisParam[0] == param){
            return thisParam[1];
        }
    }
    return false;
}

function updateStyles(){
    var uri = "/sh_wEditor/getStyles.php";
    new Ajax.Request(uri,{
        method : "post",
        parameters: {},
        onSuccess: function(transport) {
            eval('tinymce_styles_json = '+transport.responseText);
            
            updateStylesNow();
            return true
        },
        onFailure: function(){
            alert('Erreur lors de la réception des données de style');
        }
    });
}

function updateStylesNow(){
    var insertsElem = document.getElementById(actualOpenListBox['id']);
    
    // Remove all but the first option in the list box
    insertsElem.options.length = 1;  
    // Remove the first style tags in the content
    var ed = tinyMCE.activeEditor;
    var oldContent = ed.getContent();
    var newContent = oldContent.replace(/<style[^>]*>[\S\s]*?<\/style>/gi, '');
    
    var optElem = null;
    var cpt = 0;
    var allCss = '';
    for(var oneStyle in tinymce_styles_json){
        var style = tinymce_styles_json[oneStyle];
        optElem = document.createElement("option");
        optElem.value = style.cssName;
        optElem.text = style.name;
        allCss += '.'+style.cssName+'\n{'+style.css+'}\n';
        insertsElem.add(optElem, null);
        actualOpenListBox.items[cpt].title = style.name;
        actualOpenListBox.items[cpt].value = style.cssName;
        actualOpenListBox.items[cpt].attribs.value = style.name;
        cpt++;
    }

    actualOpenListBox.add(tinyMCE.activeEditor.getLang('shopsailors.modifyStyles'),"edit");
    
    // Adding the styles
    newContent = '<style type="text/css">'+allCss+'</style>'+newContent;
    ed.setContent(newContent);
}

function tinymce_calendarImage(){
    return tinymce_baseurl+'/img/falseCalendar.png';
}
function tinymce_isCalendarImage(image){
    var base = tinymce_calendarImage();
    if(image.substr(0,base.length) == '/sh_wEditor/singles/plugins/shopsailors/img/falseCalendar.png'){
        return true;
    }
    return false;
}

function tinymce_insertCalendar(calendar, shownDate){
    if(calendar != 'none'){
        tinyMCE.activeEditor.selection.setContent('<img width="200" height="200" mce_noresize="1" src="/sh_wEditor/singles/plugins/shopsailors/img/falseCalendar.png?id='+calendar+'&date='+shownDate+'" class="one_calendar"/>');
    }
}

function tinymce_insertLink(link){
    var oldContent = tinyMCE.activeEditor.selection.getContent();
    tinyMCE.activeEditor.selection.setContent('<a href="'+link+'">'+oldContent+'</a>');
}

function tinymce_insert(img, id, width, height){
    tinyMCE.execCommand('mceInsertContent',false,'<img src="' + img + '" width="'+width+'" height="'+height+'" />');
}

function tinymce_ShopProductImage(){
    return '/images/shared/icons/picto_cart.png';
}
function tinymce_createShopProductTag(product){
    return '<img mce_noresize="1" src="'+tinymce_ShopProductImage()+'" title="'+product+'"/>';
}
function tinymce_insertShopProduct(id){
    tinyMCE.execCommand(
        'mceInsertContent',
        false,
        tinymce_createShopProductTag(id)
        );
}

function tinymce_soundImage(){
    return tinymce_baseurl+'/img/falseSoundPlayer.png';
}
function tinymce_createSoundTag(sound){
    return '<img mce_noresize="1" src="'+tinymce_soundImage()+'" title="'+sound+'"/>';
}
function tinymce_insertSound(sound,id){
    tinyMCE.execCommand(
        'mceInsertContent',
        false,
        tinymce_createSoundTag(sound)
        );
}

function tinymce_videoImage(){
    return tinymce_baseurl+'/img/falseVideoPlayer.png';
}
function splitQueryArgus(url){
    //get the parameters
    url.match(/\?(.+)$/);
    var params = RegExp.$1;
    // split up the query string and store in an
    // associative array
    var params = params.split("&");
    var queryStringList = {};

    for(var i=0;i<params.length;i++){
        var tmp = params[i].split("=");
        queryStringList[tmp[0]] = unescape(tmp[1]);
    }

    return queryStringList;
}
function tinymce_createVideoTag(video){
    //http://www.youtube.com/watch?v=-WNH-LcCpH0&feature=hp_SLN&list=SL
    video = splitQueryArgus(video)['v'];
    
    return '<img src="'+tinymce_videoImage()+'" title="'+video+'" width="320" height="240"/>';
}
function tinymce_insertVideo(video){
    tinyMCE.execCommand(
        'mceInsertContent',
        false,
        tinymce_createVideoTag(video)
        );
}

function tinymce_flashImage(){
    return tinymce_baseurl+'/img/falseFlashPlayer.png';
}
function tinymce_createFlashTag(flash){
    return '<img src="'+tinymce_flashImage()+'" title="'+flash+'" width="320" height="240"/>';
}
function tinymce_insertFlash(flash,id){
    tinyMCE.execCommand(
        'mceInsertContent',
        false,
        tinymce_createVideoTag(flash)
        );
}

function tinymce_diaporamaImage(){
    return tinymce_baseurl+'/img/falseDiaporamaPlayer.png';
}
function tinymce_createDiaporamaTag(diaporama,width,height){
    return '<img mce_noresize="1" src="'+tinymce_diaporamaImage()+'" title="'+diaporama+'" width="'+width+'" height="'+height+'"/>';
}
function tinymce_insertDiaporama(diaporama,width,height){
    tinyMCE.execCommand(
        'mceInsertContent',
        false,
        tinymce_createDiaporamaTag(diaporama,width,height)
        );
}
