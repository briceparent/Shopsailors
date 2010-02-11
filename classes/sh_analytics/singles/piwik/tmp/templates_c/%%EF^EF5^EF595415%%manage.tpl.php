<?php /* Smarty version 2.6.25, created on 2010-01-19 12:01:37
         compiled from /var/www/dev/shopsailors/classes/sh_analytics/singles/piwik/plugins/CorePluginsAdmin/templates/manage.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', '/var/www/dev/shopsailors/classes/sh_analytics/singles/piwik/plugins/CorePluginsAdmin/templates/manage.tpl', 8, false),array('modifier', 'nl2br', '/var/www/dev/shopsailors/classes/sh_analytics/singles/piwik/plugins/CorePluginsAdmin/templates/manage.tpl', 31, false),)), $this); ?>
<?php $this->assign('showSitesSelection', false); ?>
<?php $this->assign('showPeriodSelection', false); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreAdminHome/templates/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreAdminHome/templates/menu.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<div style="max-width:980px;">

<h2><?php echo ((is_array($_tmp='CorePluginsAdmin_PluginsManagement')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h2>
<p><?php echo ((is_array($_tmp='CorePluginsAdmin_MainDescription')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</p>
<table class="adminTable">
	<thead>
	<tr>
		<th><?php echo ((is_array($_tmp='CorePluginsAdmin_Plugin')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</th>
		<th class="num"><?php echo ((is_array($_tmp='CorePluginsAdmin_Version')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</th>
		<th><?php echo ((is_array($_tmp='CorePluginsAdmin_Description')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</th>
		<th class="status"><?php echo ((is_array($_tmp='CorePluginsAdmin_Status')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</th>
		<th class="action-links"><?php echo ((is_array($_tmp='CorePluginsAdmin_Action')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</th>
	</tr>
	</thead>
	<tbody id="plugins">
	<?php $_from = $this->_tpl_vars['pluginsName']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['name'] => $this->_tpl_vars['plugin']):
?>
	<?php if (! $this->_tpl_vars['plugin']['alwaysActivated']): ?>
		<tr class=<?php if ($this->_tpl_vars['plugin']['activated']): ?>"active"<?php else: ?>class="deactivate"<?php endif; ?>>
			<td class="name">
				<?php if (isset ( $this->_tpl_vars['plugin']['info']['homepage'] )): ?><a title="<?php echo ((is_array($_tmp='CorePluginsAdmin_PluginHomepage')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" href="<?php echo $this->_tpl_vars['plugin']['info']['homepage']; ?>
"><?php endif; ?>
				<?php echo $this->_tpl_vars['name']; ?>

				<?php if (isset ( $this->_tpl_vars['plugin']['info']['homepage'] )): ?></a><?php endif; ?>
			</td>
			<td class="vers"><?php echo $this->_tpl_vars['plugin']['info']['version']; ?>
</td>
			<td class="desc">
				<?php echo ((is_array($_tmp=$this->_tpl_vars['plugin']['info']['description'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>

				&nbsp;<cite>By 
					<?php if (isset ( $this->_tpl_vars['plugin']['info']['author_homepage'] )): ?><a title="Author Homepage" href="misc/redirectToUrl.php?url=<?php echo $this->_tpl_vars['plugin']['info']['author_homepage']; ?>
"><?php endif; ?>
					<?php echo $this->_tpl_vars['plugin']['info']['author']; ?>
<?php if (isset ( $this->_tpl_vars['plugin']['info']['author_homepage'] )): ?></a><?php endif; ?>.</cite>
			</td>
			<td class="status">
				<?php if ($this->_tpl_vars['plugin']['alwaysActivated']): ?><span title="<?php echo ((is_array($_tmp='CorePluginsAdmin_ActivatedHelp')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" class="active"><?php echo ((is_array($_tmp='CorePluginsAdmin_Active')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</span>
				<?php elseif ($this->_tpl_vars['plugin']['activated']): ?><?php echo ((is_array($_tmp='CorePluginsAdmin_Active')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

				<?php else: ?><?php echo ((is_array($_tmp='CorePluginsAdmin_Inactive')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
<?php endif; ?>
			</td>
			
			<td class="togl action-links" <?php if ($this->_tpl_vars['plugin']['alwaysActivated']): ?>title="<?php echo ((is_array($_tmp='CorePluginsAdmin_ActivatedHelp')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
"<?php endif; ?>>
				<?php if ($this->_tpl_vars['plugin']['alwaysActivated']): ?> <center>-</center>  
				<?php elseif ($this->_tpl_vars['plugin']['activated']): ?><a href=index.php?module=CorePluginsAdmin&action=deactivate&pluginName=<?php echo $this->_tpl_vars['name']; ?>
><?php echo ((is_array($_tmp='CorePluginsAdmin_Deactivate')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a>
				<?php else: ?><a href=index.php?module=CorePluginsAdmin&action=activate&pluginName=<?php echo $this->_tpl_vars['name']; ?>
><?php echo ((is_array($_tmp='CorePluginsAdmin_Activate')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a><?php endif; ?>
			</td> 
		</tr>
	<?php endif; ?>
<?php endforeach; endif; unset($_from); ?>
</tbody>
</table>

</div>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreAdminHome/templates/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>