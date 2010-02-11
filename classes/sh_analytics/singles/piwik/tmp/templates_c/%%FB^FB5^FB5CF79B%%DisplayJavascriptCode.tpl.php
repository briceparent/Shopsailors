<?php /* Smarty version 2.6.25, created on 2010-01-19 16:20:20
         compiled from /var/www/dev/shopsailors/classes/sh_analytics/singles/piwik/plugins/SitesManager/templates/DisplayJavascriptCode.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'loadJavascriptTranslations', '/var/www/dev/shopsailors/classes/sh_analytics/singles/piwik/plugins/SitesManager/templates/DisplayJavascriptCode.tpl', 4, false),array('modifier', 'translate', '/var/www/dev/shopsailors/classes/sh_analytics/singles/piwik/plugins/SitesManager/templates/DisplayJavascriptCode.tpl', 26, false),)), $this); ?>
<?php $this->assign('showSitesSelection', false); ?>
<?php $this->assign('showPeriodSelection', false); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreAdminHome/templates/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php echo smarty_function_loadJavascriptTranslations(array('plugins' => 'SitesManager'), $this);?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreAdminHome/templates/menu.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php echo '
<style>
code {
	background-color:#F0F7FF;
	border-color:#00008B;
	border-style:dashed dashed dashed solid;
	border-width:1px 1px 1px 5px;
	direction:ltr;
	display:block;
	font-size:80%;
	margin:2px 2px 20px;
	padding:4px;
	text-align:left;
	font-family: "Courier New" Courier monospace;
}
</style>
'; ?>


<h2><?php echo $this->_tpl_vars['siteName']; ?>
</h2>
<p><?php echo ((is_array($_tmp='SitesManager_JsTrackingTagHelp')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
:</p>

<code><?php echo $this->_tpl_vars['jsTag']; ?>
</code>

<ul style="list-style-type:disc; padding-left:20px">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "SitesManager/templates/JavascriptTagHelp.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</ul>