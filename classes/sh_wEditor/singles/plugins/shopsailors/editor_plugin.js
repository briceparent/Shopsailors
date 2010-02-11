/**
 * TinyMce Shopsailors' Plugin
 *
 * @author Shopsailors
 * @copyright Copyright � 2009-2010, Shopsailors.
 * @licence Distributed under the CeCILL licence
 */
var myPrivateCounter = 0;
(function() {
    tinymce.create('tinymce.plugins.shopsailorsPlugin', {
        init : function(ed, url) {
	    this.editor = ed;
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
                popupBrowser.parameters.set('folder',folder);
                popupBrowser.parameters.set('action','selectImage');
                popupBrowser.parameters.set('element','0');
                popupBrowser.open();
            });
            ed.addCommand('mcediaporamaInserter', function() {
                ed.windowManager.open({
                    file : '/diaporama/get_list.php',
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

            ed.onNodeChange.add(function(ed, cm, e, co) {
                // Activates the link button when the caret is placed in a anchor element
                cm.setActive('linkInserter', (e.nodeName == 'A'));
                /*if(e.nodeName == 'A'){
                    alert('co = '+co)
                }*/
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

function tinymce_insertDiaporama(diapo,img){
    tinyMCE.execCommand('mceInsertContent',false,'<img src="' + img + '" class="diaporama_inserted ' + diapo + '" />');
}



