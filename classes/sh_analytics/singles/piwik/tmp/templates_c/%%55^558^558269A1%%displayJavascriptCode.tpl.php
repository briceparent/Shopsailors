<?php /* Smarty version 2.6.25, created on 2010-01-19 11:23:21
         compiled from Installation/templates/displayJavascriptCode.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'Installation/templates/displayJavascriptCode.tpl', 29, false),)), $this); ?>

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
}
</style>

<script>
$(document).ready( function(){
	$(\'code\').click( function(){ $(this).select(); });
});
</script>

'; ?>


<?php if (isset ( $this->_tpl_vars['displayfirstWebsiteSetupSuccess'] )): ?>

<span id="toFade" class="success">
	<?php echo ((is_array($_tmp='Installation_SetupWebsiteSetupSuccess')) ? $this->_run_mod_handler('translate', true, $_tmp, $this->_tpl_vars['websiteName']) : smarty_modifier_translate($_tmp, $this->_tpl_vars['websiteName'])); ?>

	<img src="themes/default/images/success_medium.png">
</span>
<?php endif; ?>
<h1><?php echo ((is_array($_tmp='Installation_JsTag')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h1>
<?php echo ((is_array($_tmp='Installation_JsTagHelp')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

<code>
<?php echo $this->_tpl_vars['javascriptTag']; ?>

</code>

<h1><?php echo ((is_array($_tmp='Installation_JsTagHelpTitle')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h1>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "SitesManager/templates/JavascriptTagHelp.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<br/>
<h1><?php echo ((is_array($_tmp='Installation_LargePiwikInstances')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h1>
<?php echo ((is_array($_tmp='Installation_JsTagArchivingHelp')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

<!-- <li>Link to help with the main blog engines wordpress/drupal/myspace/blogspot</li> -->