<?php /* Smarty version 2.6.25, created on 2010-01-19 11:22:08
         compiled from Installation/templates/databaseCheck.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'Installation/templates/databaseCheck.tpl', 6, false),)), $this); ?>
<?php $this->assign('ok', "<img src='themes/default/images/ok.png' />"); ?>
<?php $this->assign('error', "<img src='themes/default/images/error.png' />"); ?>
<?php $this->assign('warning', "<img src='themes/default/images/warning.png' />"); ?>
<?php $this->assign('link', "<img src='themes/default/images/link.gif' />"); ?>

<h1><?php echo ((is_array($_tmp='Installation_DatabaseCheck')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h1>

<table class="infosServer">
	<tr>
		<td class="label"><?php echo ((is_array($_tmp='Installation_DatabaseServerVersion')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
		<td><?php if (isset ( $this->_tpl_vars['databaseVersionOk'] )): ?><?php echo $this->_tpl_vars['ok']; ?>
<?php else: ?><?php echo $this->_tpl_vars['error']; ?>
<?php endif; ?></td>
	</tr>
	<tr>
		<td class="label"><?php echo ((is_array($_tmp='Installation_DatabaseCreation')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
		<td><?php if (isset ( $this->_tpl_vars['databaseCreated'] )): ?><?php echo $this->_tpl_vars['ok']; ?>
<?php else: ?><?php echo $this->_tpl_vars['error']; ?>
<?php endif; ?></td>
	</tr>
	<tr>
		<td class="label"><?php echo ((is_array($_tmp='Installation_DatabaseClientCharset')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
		<td><?php if (isset ( $this->_tpl_vars['charsetWarning'] )): ?><?php echo $this->_tpl_vars['warning']; ?>
<?php else: ?>utf8 <?php echo $this->_tpl_vars['ok']; ?>
<?php endif; ?></td>
	</tr>
<?php if (isset ( $this->_tpl_vars['charsetWarning'] )): ?>
	<tr>
		<td colspan="2">
			<small>
				<span style="color:#FF7F00"><?php echo ((is_array($_tmp='Installation_ConnectionCharacterSetNotUtf8')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</span>
			</small>
		</td>
	</tr>
<?php endif; ?>
	<tr>
		<td class="label"><?php echo ((is_array($_tmp='Installation_DatabaseTimezone')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
		<td><?php if (isset ( $this->_tpl_vars['timezoneWarning'] )): ?><?php echo $this->_tpl_vars['warning']; ?>
<?php else: ?><?php echo $this->_tpl_vars['ok']; ?>
<?php endif; ?></td>
	</tr>
<?php if (isset ( $this->_tpl_vars['timezoneWarning'] )): ?>
	<tr>
		<td colspan="2">
			<small>
				<span style="color:#FF7F00"><?php echo ((is_array($_tmp='Installation_TimezoneMismatch')) ? $this->_run_mod_handler('translate', true, $_tmp, "<a href='misc/redirectToUrl.php?url=http://piwik.org/FAQ/troubleshooting/#faq_58' target='_blank'>FAQ</a>") : smarty_modifier_translate($_tmp, "<a href='misc/redirectToUrl.php?url=http://piwik.org/FAQ/troubleshooting/#faq_58' target='_blank'>FAQ</a>")); ?>
.</span>
			</small>
		</td>
	</tr>
<?php endif; ?>
</table>

<p>
<?php echo $this->_tpl_vars['link']; ?>
 <a href="http://piwik.org/docs/requirements/" target="_blank"><?php echo ((is_array($_tmp='Installation_Requirements')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a> 
</p>