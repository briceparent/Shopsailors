/**
 * TinyMce Shopsailors' Plugin
 *
 * @author Shopsailors
 * @copyright Copyright � 2009-2010, Shopsailors.
 * @licence Distributed under the CeCILL licence
 */
var myPrivateCounter = 0;
var tinymce_baseurl = '';
var tinymce_old_selection = null;
var tinymce_externalVideos = new Array();
(function() {
    var each = tinymce.each;
    tinymce.create('tinymce.plugins.shopsailorsPlugin', {
        init : function(ed, url) {
	    this.editor = ed;
            tinymce_baseurl = url;
            // Register commands
            ed.addCommand('mcebgcChanger', function() {
                ed.windowManager.open({
                    file : url + '/bgcChanger.php',
                    width : 250,
                    height : 220,
                    inline : 1
                }, {
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
                folder = 'SH_IMAGES_FOLDER';
                /*Launches the browser*/
                popupBrowser.clearParameters();
                popupBrowser.parameters.set('type','url');
                popupBrowser.parameters.set('types','sounds');
                popupBrowser.parameters.set('folder',folder);
                popupBrowser.parameters.set('action','tinymce_insertSound');
                popupBrowser.parameters.set('element','0');
                popupBrowser.open();
            });
            ed.addCommand('mcevideoInserter', function() {
                var folder = 'SH_IMAGES_FOLDER';
                /*Launches the browser*/
                popupBrowser.clearParameters();
                popupBrowser.parameters.set('type','url');
                popupBrowser.parameters.set('types','videos');
                popupBrowser.parameters.set('folder',folder);
                popupBrowser.parameters.set('action','tinymce_insertVideo');
                popupBrowser.parameters.set('element','0');
                popupBrowser.open();
            });
            ed.addCommand('mcediaporamaInserter', function() {
                ed.windowManager.open({
                    file : '/wEditor/insertDiaporama.php',
                    width : 250,
                    height : 220,
                    inline : 1
                }, {
                    plugin_url : url
                });
            });
            ed.addCommand('mceblocksInserter', function() {
                alert('1');
                tinyMCE.util.XHR.send({
                    url : '/wEditor/addBlock.php',
                    success : function(text) {
                    /*alert(text);*/
                    }

                });
                alert('2');

                ed.execCommand(
                    'mceInsertContent',
                    false,
                    '<table width="100"><tr><td>Block</td></tr><tr><td>Lorem ipsum...</td></tr></table>'
                    );
            });

            // Register buttons
            ed.addButton('bgcChanger', {
                title : 'Background color changer',
                cmd : 'mcebgcChanger',
                image : url + '/img/bcgChanger.gif'
            });
            ed.addButton('linkInserter', {
                title : 'Link inserter',
                cmd : 'mcelinkInserter',
                image : url + '/img/linkInserter.gif'
            });
            ed.addButton('imageInserter', {
                title : 'Image inserter',
                cmd : 'mceimageInserter',
                image : url + '/img/imageInserter.gif'
            });
            ed.addButton('soundInserter', {
                title : 'Image inserter',
                cmd : 'mcesoundInserter',
                image : url + '/img/soundInserter.gif'
            });
            ed.addButton('videoInserter', {
                title : 'Video inserter',
                cmd : 'mcevideoInserter',
                image : url + '/img/videoInserter.gif'
            });
            ed.addButton('diaporamaInserter', {
                title : 'Diaporama inserter',
                cmd : 'mcediaporamaInserter',
                image : url + '/img/diaporamaInserter.gif'
            });
            ed.addButton('blocksInserter', {
                title : 'Blocks inserter',
                cmd : 'mceblocksInserter',
                image : url + '/img/blocksInserter.gif'
            });

            ed.onPreProcess.add(function(ed,o) {
                // Called right before the validating of the form
                var dom = ed.dom;
                each(dom.select('IMG', o.node), function(n) {
                    var sound, video, diaporama, el, width, height;
                    if(dom.getAttrib(n, 'src') == tinymce_soundImage()){
                        sound = dom.getAttrib(n, 'title');
                        el = dom.create('RENDER_SOUND', {'file' : sound},'Un render sound');

                        dom.replace(el,n);
                    }else if(dom.getAttrib(n, 'src') == tinymce_videoImage()){
                        video = dom.getAttrib(n, 'title');
                        width = dom.getAttrib(n, 'width');
                        height = dom.getAttrib(n, 'height');
                        el = dom.create('RENDER_VIDEO', {'file' : video,'width':width,'height':height},'Un render video');

                        dom.replace(el,n);
                    }else if(dom.getAttrib(n, 'src') == tinymce_diaporamaImage()){
                        diaporama = dom.getAttrib(n, 'title');
                        width = dom.getAttrib(n, 'width');
                        height = dom.getAttrib(n, 'height');
                        el = dom.create('RENDER_DIAPORAMA', {'name' : diaporama,'width':width,'height':height},'Un render diaporama');

                        dom.replace(el,n);
                    }else if(dom.getAttrib(n, 'src') == tinymce_flashImage()){
                        diaporama = dom.getAttrib(n, 'title');
                        width = dom.getAttrib(n, 'width');
                        height = dom.getAttrib(n, 'height');
                        el = dom.create('RENDER_FLASH', {'id' : diaporama,'width':width,'height':height},'Un render flash');

                        dom.replace(el,n);
                    }
                });
            });

            ed.onSetContent.add(function(ed, o) {
                // Called when the form is loading
                var dom = ed.dom;
                var sound, video, diaporama, flashFile, el, width, height;
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
                                'height':height
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
                        el = dom.create(
                            'img',
                            {
                                'src' : tinymce_diaporamaImage(),
                                'title' : diaporama,
                                'width':width,
                                'height':height,
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


function tinymce_insertLink(link){
    var oldContent = tinyMCE.activeEditor.selection.getContent();
    tinyMCE.activeEditor.selection.setContent('<a href="'+link+'">'+oldContent+'</a>');
}

function tinymce_insert(img,id){
    tinyMCE.execCommand('mceInsertContent',false,'<img src="' + img + '" />');
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
function tinymce_createVideoTag(video){
    return '<img src="'+tinymce_videoImage()+'" title="'+video+'" width="320" height="240"/>';
}
function tinymce_insertVideo(video,id){
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