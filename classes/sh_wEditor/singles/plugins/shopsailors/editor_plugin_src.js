/**
 * $Id: editor_plugin_src.js 520 2008-01-07 16:30:32Z spocke $
 *
 * @author Moxiecode
 * @copyright Copyright � 2004-2008, Moxiecode Systems AB, All rights reserved.
 */

(function() {
	tinymce.create('tinymce.plugins.websailorsPlugin', {
		init : function(ed, url) {
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
			ed.addCommand('mceimageInserter', function() {
				ed.windowManager.open({
					file :'/browser/SH_IMAGES_FOLDER/images/tinymce_insert/0/show.php',
					width : 750,
					height : 410,
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('bgcChanger', {title : 'Background color changer', cmd : 'mcebgcChanger', image : url + '/img/bcgChanger.gif'});
			ed.addButton('imageInserter', {title : 'Image inserter', cmd : 'mceimageInserter', image : url + '/img/imageInserter.gif'});
		},

		getInfo : function() {
			return {
				longname : 'Background color changer',
				author : 'Websailors',
				authorurl : 'http://websailors.fr',
				infourl : 'http://websailors.fr',
				version : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('websailors', tinymce.plugins.websailorsPlugin);
})();


function tinymce_insert(img,id){
tinyMCE.execCommand('mceInsertContent',false,'<img src="' + img + '" />');
}




