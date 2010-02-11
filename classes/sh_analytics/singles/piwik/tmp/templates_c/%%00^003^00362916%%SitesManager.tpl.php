<?php /* Smarty version 2.6.25, created on 2010-01-19 12:01:55
         compiled from /var/www/dev/shopsailors/classes/sh_analytics/singles/piwik/plugins/SitesManager/templates/SitesManager.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'loadJavascriptTranslations', '/var/www/dev/shopsailors/classes/sh_analytics/singles/piwik/plugins/SitesManager/templates/SitesManager.tpl', 4, false),array('function', 'url', '/var/www/dev/shopsailors/classes/sh_analytics/singles/piwik/plugins/SitesManager/templates/SitesManager.tpl', 55, false),array('modifier', 'translate', '/var/www/dev/shopsailors/classes/sh_analytics/singles/piwik/plugins/SitesManager/templates/SitesManager.tpl', 27, false),array('modifier', 'count', '/var/www/dev/shopsailors/classes/sh_analytics/singles/piwik/plugins/SitesManager/templates/SitesManager.tpl', 33, false),)), $this); ?>
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

<script type="text/javascript" src="plugins/SitesManager/templates/SitesManager.js"></script>
<?php echo '
<style>
.addRowSite:hover, .editableSite:hover, .addsite:hover, .cancel:hover, .deleteSite:hover, .editSite:hover, .updateSite:hover{
	cursor: pointer;
}
.addRowSite a {
	text-decoration: none;
}
.addRowSite {
	padding:1em;
	font-color:#3A477B;
	padding:1em;
	font-weight:bold;
}
#editSites {
	valign: top;
}
</style>
'; ?>

<h2><?php echo ((is_array($_tmp='SitesManager_WebsitesManagement')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h2>
<p><?php echo ((is_array($_tmp='SitesManager_MainDescription')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</p>

<div id="ajaxError" style="display:none"></div>
<div id="ajaxLoading" style="display:none"><div id="loadingPiwik"><img src="themes/default/images/loading-blue.gif" alt="" /> <?php echo ((is_array($_tmp='General_LoadingData')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
 </div></div>

<?php if (count($this->_tpl_vars['adminSites']) == 0): ?>
	<?php echo ((is_array($_tmp='SitesManager_NoWebsites')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

<?php else: ?>
	<table class="admin" id="editSites" border=1 cellpadding="10">
		<thead>
			<tr>
			<th><?php echo ((is_array($_tmp='SitesManager_Id')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</th>
			<th><?php echo ((is_array($_tmp='SitesManager_Name')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</th>
			<th><?php echo ((is_array($_tmp='SitesManager_Urls')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</th>
			<th> </th>
			<th> </th>
			<th> <?php echo ((is_array($_tmp='SitesManager_JsTrackingTag')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
 </th>
			</tr>
		</thead>
		<tbody>
			<?php $_from = $this->_tpl_vars['adminSites']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i'] => $this->_tpl_vars['site']):
?>
			<tr id="row<?php echo $this->_tpl_vars['i']; ?>
">
				<td id="idSite"><?php echo $this->_tpl_vars['site']['idsite']; ?>
</td>
				<td id="siteName" class="editableSite"><?php echo $this->_tpl_vars['site']['name']; ?>
</td>
				<td id="urls" class="editableSite"><?php $_from = $this->_tpl_vars['site']['alias_urls']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['url']):
?><?php echo $this->_tpl_vars['url']; ?>
<br /><?php endforeach; endif; unset($_from); ?></td>       
				<td><img src='plugins/UsersManager/images/edit.png' class="editSite" id="row<?php echo $this->_tpl_vars['i']; ?>
" href='#' alt="" /></td>
				<td><img src='plugins/UsersManager/images/remove.png' class="deleteSite" id="row<?php echo $this->_tpl_vars['i']; ?>
" value="<?php echo ((is_array($_tmp='General_Delete')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" alt="" /></td>
				<td><a href='<?php echo smarty_function_url(array('action' => 'displayJavascriptCode','idsite' => $this->_tpl_vars['site']['idsite']), $this);?>
'><?php echo ((is_array($_tmp='SitesManager_ShowTrackingTag')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a></td>
			</tr>
			<?php endforeach; endif; unset($_from); ?>
			
		</tbody>
	</table>
	<?php if ($this->_tpl_vars['isSuperUser']): ?>	
	<div class="addRowSite"><a href="#"><img src='plugins/UsersManager/images/add.png' alt="" /> <?php echo ((is_array($_tmp='SitesManager_AddSite')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a></div>
	<?php endif; ?>
	<div class="ui-widget">
		<div class="ui-state-highlight ui-corner-all" style="margin-top:20px; padding:0 .7em; width:400px">
			<p style="font-size:62.5%;"><span class="ui-icon ui-icon-info" style="float:left;margin-right:.3em;"></span>
			<?php echo ((is_array($_tmp='SitesManager_AliasUrlHelp')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</p>
		</div>
	</div>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreAdminHome/templates/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>