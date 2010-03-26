tinymce.PluginManager.load(
'shopsailors',
'/sh_wEditor/singles/plugins/shopsailors/editor_plugin.js'
);
tinymce.PluginManager.load(
'advimagescale',
'/sh_wEditor/singles/plugins/advimagescale/editor_plugin.js'
);
<?php
// Gets the site name (using the server name)
$classFolder = dirname(dirname(__FILE__));
$class = basename($classFolder);
$classesFolder = dirname($classFolder);
$pluginsSharedFolder = $classesFolder.'/shared/'.$class.'/plugins/';
if(is_dir($pluginsSharedFolder)){
    $classes = scandir($pluginsSharedFolder);
    foreach($classes as $oneClass){
        if(substr($oneClass,-4) == '.php'){
            include($pluginsSharedFolder.$oneClass);
        }
    }
}
?>
/* tinyMCE_advanced */
tinyMCE.init({
mode : "textareas",
theme : "advanced",
browsers : "gecko",
entity_encoding : "raw",
editor_selector : "tinyMCE_advanced",
theme_advanced_layout_manager : "SimpleLayout",
theme_advanced_toolbar_location : "top",
inline_styles : true,
plugins : "style,paste,insertdatetime,diaporama,example,-shopsailors,"
    +"table,noneditable,-advimagescale",
paste_auto_cleanup_on_paste : true,
plugin_insertdate_dateFormat : "%d/%m/%Y",
theme_advanced_buttons1 : "fontselect,fontsizeselect,styleprops,|,bgcChanger,|,"
    +"bold,italic,|,imageInserter,image,|,diaporamaInserter,soundInserter,videoInserter,|,insertdate,|,hr,|,"
    +"removeformat,undo,redo",
theme_advanced_buttons2 : "tablecontrols,|,linkInserter,link,unlink,anchor,|,"
    +"charmap,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,"
    +"numlist,|,sub,sup,outdent,indent",
theme_advanced_buttons3 : "",
extended_valid_elements : "img[mce_noresize|longdesc|usemap|src|border|alt=|title|hspace|vspace|width|height|align|style],"
    +"object[width|height|classid|codebase],"
    +"param[name|value],"
    +"embed[src|type|width|height|flashvars|wmode|allowscriptaccess|allowfullscreen|],"
    +"render_diaporama[name|width|height|class],"
    +"render_sound[file],"
    +"render_video[file|width|height]",
custom_elements : '~diaporama',
content_css : "/CSS/" + (Math.floor(Math.random() * 1000)+1) + "/wEditor.css",
relative_urls : false,
theme_advanced_blockformats : "p,div,h3,h4,h5,h6,blockquote",
noneditable_leave_contenteditable : true,
language : "<?php echo $_GET['lang'];?>",
advimagescale_fix_border_glitch: false,
advimage_noresize_class: "mce_noresize"
});

/* tinyMCE_newsletter */
tinyMCE.init({
mode : "textareas",
theme : "advanced",
browsers : "gecko",
entity_encoding : "raw",
editor_selector : "tinyMCE_newsletter",
theme_advanced_layout_manager : "SimpleLayout",
theme_advanced_toolbar_location : "top",
plugins : "paste,-shopsailors,-advimagescale",
paste_auto_cleanup_on_paste : true,
theme_advanced_buttons1 : "fontsizeselect,forecolor,|,bold,italic,underline,|,"
+"removeformat,|,imageInserter,image,|,hr,|,undo,redo,|,linkInserter,link,"
+"unlink,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,blocksInserter",
theme_advanced_buttons2 : "",
theme_advanced_buttons3 : "",
imageInserter_specialFolder : 'newsletters',
noneditable_leave_contenteditable : true,
language : "<?php echo $_GET['lang'];?>",
relative_urls : false,
remove_script_host : false,
convert_fonts_to_spans : false,
gecko_spellcheck : true,
visual_table_class : "",
inline_styles : false,
force_br_newlines : true,
forced_root_block : '',
theme_advanced_resizing : true,
theme_advanced_resizing_min_height : 500,
theme_advanced_resizing_min_width : 600,
theme_advanced_resizing_max_width : 600,
auto_resize : true,
external_link_list_url : "/sh_wEditor/singles/myexternallist.js",

valid_elements : "@[title|dir<ltr?rtl|style],"
    + "a[rel|rev|charset|hreflang|tabindex|accesskey|type|name|href|target|title],"
    + "strong/b,em/i,strike,u,big,"
    + "-ol[type|compact],-ul[type|compact],-li,"
    + "img[longdesc|src|border|alt=|title|hspace|vspace|width|height|align],"
    + "-table[border=0|cellspacing=0|cellpadding=0|width|height|align|summary|"
        + "bgcolor|background|bordercolor],"
    + "-tr[rowspan|width|height|align|valign|bgcolor|background|bordercolor],"
    + "#td[colspan|rowspan|width|height|align|valign|bgcolor|background|"
        + "bordercolor],"
    + "#p,-span,#div[align|leftmargin|rightmargin|topmargin|bottommargin]"
    + "-h1,-h2,-h3,-h4,-h5,-h6,"
    + "br,hr[size|noshade],"
    + "-font[face|size|color]"
});

/* tinyMCE_readonly */
tinyMCE.init({
mode : "textareas",
theme : "advanced",
browsers : "gecko",
entity_encoding : "raw",
editor_selector : "tinyMCE_readonly",
theme_advanced_layout_manager : "SimpleLayout",
theme_advanced_toolbar_location : "top",
inline_styles : true,
theme_advanced_buttons1 : "",
theme_advanced_buttons2 : "",
theme_advanced_buttons3 : "",
readonly : 1,
auto_resize : true,
language : "<?php echo $_GET['lang'];?>"
});

/* tinyMCE_simple */
tinyMCE.init({
mode : "textareas",
theme : "advanced",
theme_advanced_layout_manager : "SimpleLayout",
inline_styles : true,
entity_encoding : "raw",
content_css : "/CSS/<?php echo $_GET['variation'];?>/wEditor.css",
plugins : "paste,insertdatetime,advlink,advimage",
paste_auto_cleanup_on_paste : true,
relative_urls : false,
plugin_insertdate_dateFormat : "%d/%m/%Y",
plugin_preview_width : "600",
plugin_preview_height : "600",
theme_advanced_buttons1 : "backcolor,forecolor,fontsizeselect,|,linkInserter,"
+"link,unlink,|,image,|,insertdate,|,hr,|,removeformat,|,sub,sup,|,charmap",
theme_advanced_buttons2 : "justifyleft,justifycenter,justifyright,justifyfull,|,"
+"bullist,numlist,|,outdent,indent,|,undo,redo,|,visualaid,cleanup",
theme_advanced_buttons3 : "",
editor_selector : "tinyMCE_simple",
language : "<?php echo $_GET['lang'];?>"
});

/* tinyMCE_minimal */
tinyMCE.init({
mode : "textareas",
theme : "advanced",
theme_advanced_layout_manager : "SimpleLayout",
inline_styles : true,
entity_encoding : "raw",
plugins : "paste",
paste_auto_cleanup_on_paste : true,
theme_advanced_buttons1 : "bold,italic,underline,|,linkInserter,link,unlink,"
+"justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist",
theme_advanced_buttons2 : "",
theme_advanced_buttons3 : "",
relative_urls : false,
editor_selector : "tinyMCE_minimal",
language : "<?php echo $_GET['lang'];?>"
});

/* tinyMCE_forum */
tinyMCE.init({
mode : "textareas",
theme : "advanced",
theme_advanced_layout_manager : "SimpleLayout",
inline_styles : true,
content_css : "/CSS/<?php echo $_GET['variation'];?>/forum.css,/sh_wEditor/singles/forum.css",
entity_encoding : "raw",
plugins : "emotions",
paste_auto_cleanup_on_paste : true,
theme_advanced_buttons1 : "emotions,|,blockquote,|,bold,italic,underline,|,link,"
+"unlink,|,bullist,numlist",
theme_advanced_buttons2 : "",
theme_advanced_buttons3 : "",
relative_urls : false,
editor_selector : "tinyMCE_forum",
language : "<?php echo $_GET['lang'];?>",
valid_elements : ""
+"a[accesskey|charset|class|dir<ltr?rtl|coords|href|hreflang|id|lang|name"
  +"|rel|rev|style|tabindex|title|target|type],"
+"blockquote[cite|class|dir<ltr?rtl|id|lang|style|title],"
+"em/i[class|dir<ltr?rtl|id|lang|title],"
+"img[align<bottom?left?middle?right?top|alt|border|class|dir<ltr?rtl|height"
  +"|hspace|id|ismap<ismap|lang|longdesc|name|src|style|title|usemap|vspace|width],"
+"li[class|dir<ltr?rtl|id|lang|style|title|type|value],"
+"ol[class|compact<compact|dir<ltr?rtl|id|lang|start|style|title|type],"
+"p[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|style|title],"
+"strong/b[class|dir<ltr?rtl|id|lang|style|title],"
+"ul[class|compact<compact|dir<ltr?rtl|id|lang|style|title|type]"
});
