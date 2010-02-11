<?php /* Smarty version 2.6.25, created on 2010-01-19 11:11:18
         compiled from Installation/templates/systemCheck.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'Installation/templates/systemCheck.tpl', 6, false),array('modifier', 'nl2br', 'Installation/templates/systemCheck.tpl', 32, false),array('function', 'url', 'Installation/templates/systemCheck.tpl', 201, false),)), $this); ?>
<?php $this->assign('ok', "<img src='themes/default/images/ok.png' />"); ?>
<?php $this->assign('error', "<img src='themes/default/images/error.png' />"); ?>
<?php $this->assign('warning', "<img src='themes/default/images/warning.png' />"); ?>
<?php $this->assign('link', "<img src='themes/default/images/link.gif' />"); ?>

<h1><?php echo ((is_array($_tmp='Installation_SystemCheck')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h1>

<table class="infosServer">
	<tr>
		<td class="label"><?php echo ((is_array($_tmp='Installation_SystemCheckPhp')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
 &gt; <?php echo $this->_tpl_vars['infos']['phpVersion_minimum']; ?>
</td>
		<td><?php if ($this->_tpl_vars['infos']['phpVersion_ok']): ?><?php echo $this->_tpl_vars['ok']; ?>
<?php else: ?><?php echo $this->_tpl_vars['error']; ?>
<?php endif; ?></td>
	</tr>
	<tr>
		<td class="label">PDO <?php echo ((is_array($_tmp='Installation_Extension')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
		<td><?php if ($this->_tpl_vars['infos']['pdo_ok']): ?><?php echo $this->_tpl_vars['ok']; ?>

			<?php else: ?>-<?php endif; ?>
		</td>
	</tr>
	<?php $_from = $this->_tpl_vars['infos']['adapters']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['adapter'] => $this->_tpl_vars['port']):
?>
	<tr>
		<td class="label"><?php echo $this->_tpl_vars['adapter']; ?>
 <?php echo ((is_array($_tmp='Installation_Extension')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
		<td><?php echo $this->_tpl_vars['ok']; ?>
</td>
	</tr>
	<?php endforeach; endif; unset($_from); ?>
	<?php if (! count ( $this->_tpl_vars['infos']['adapters'] )): ?>
	<tr>
		<td colspan="2" class="error">
			<small>
				<?php echo ((is_array($_tmp='Installation_SystemCheckDatabaseHelp')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

				<p>
				<?php if ($this->_tpl_vars['infos']['isWindows']): ?>
					<?php echo ((is_array($_tmp=((is_array($_tmp='Installation_SystemCheckWinPdoAndMysqliHelp')) ? $this->_run_mod_handler('translate', true, $_tmp, "<br /><br /><code>extension=php_mysqli.dll</code><br /><code>extension=php_pdo.dll</code><br /><code>extension=php_pdo_mysql.dll</code><br />") : smarty_modifier_translate($_tmp, "<br /><br /><code>extension=php_mysqli.dll</code><br /><code>extension=php_pdo.dll</code><br /><code>extension=php_pdo_mysql.dll</code><br />")))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>

				<?php else: ?>
					<?php echo ((is_array($_tmp=((is_array($_tmp='Installation_SystemCheckPdoAndMysqliHelp')) ? $this->_run_mod_handler('translate', true, $_tmp, "<br /><br /><code>--with-mysqli</code><br /><code>--with-pdo-mysql</code><br />", "<br /><br /><code>extension=mysqli.so</code><br /><code>extension=pdo.so</code><br /><code>extension=pdo_mysql.so</code><br />") : smarty_modifier_translate($_tmp, "<br /><br /><code>--with-mysqli</code><br /><code>--with-pdo-mysql</code><br />", "<br /><br /><code>extension=mysqli.so</code><br /><code>extension=pdo.so</code><br /><code>extension=pdo_mysql.so</code><br />")))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>

				<?php endif; ?>
				<br />
				<?php echo ((is_array($_tmp='Installation_SystemCheckPhpPdoAndMysqliSite')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

				</p>
			</small>
		</td>
	</tr>
	<?php endif; ?>
	<tr>
		<td valign="top">
			<?php echo ((is_array($_tmp='Installation_SystemCheckJson')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

		</td>
		<td><?php if ($this->_tpl_vars['infos']['json'] || $this->_tpl_vars['infos']['xml']): ?><?php echo $this->_tpl_vars['ok']; ?>

			<?php else: ?><?php echo $this->_tpl_vars['error']; ?>
<?php endif; ?>
		</td>
	</tr>
	<?php if (! $this->_tpl_vars['infos']['json'] && ! $this->_tpl_vars['infos']['xml']): ?>
	<tr>
		<td colspan="2" class="error">
			<small>
				<?php echo ((is_array($_tmp='Installation_SystemCheckJsonHelp')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

				<br />
				<?php if (version_compare ( $this->_tpl_vars['infos']['phpVersion'] , '5.2.0' ) >= 0): ?>
					<?php echo ((is_array($_tmp='Installation_SystemCheckJsonSite')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

				<?php else: ?>
					<?php echo ((is_array($_tmp='Installation_SystemCheckXmlSite')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

				<?php endif; ?>
			</small>
		</td>
	</tr>
	<?php endif; ?>
	<tr>
		<td class="label"><?php echo ((is_array($_tmp='Installation_SystemCheckExtensions')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
		<td><?php $_from = $this->_tpl_vars['infos']['needed_extensions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['needed_extension']):
?>
				<?php echo $this->_tpl_vars['needed_extension']; ?>

				<?php if (in_array ( $this->_tpl_vars['needed_extension'] , $this->_tpl_vars['infos']['missing_extensions'] )): ?>
					<?php echo $this->_tpl_vars['error']; ?>

				<?php else: ?>
					<?php echo $this->_tpl_vars['ok']; ?>

				<?php endif; ?>
				<br />
			<?php endforeach; endif; unset($_from); ?>
		</td>
	</tr>
	<?php if (count ( $this->_tpl_vars['infos']['missing_extensions'] ) > 0): ?>
	<tr>
		<td colspan="2" class="error">
			<small>
				<?php $_from = $this->_tpl_vars['infos']['missing_extensions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['missing_extension']):
?>
					<p>
					<?php echo ((is_array($_tmp=$this->_tpl_vars['helpMessages'][$this->_tpl_vars['missing_extension']])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

					</p>
				<?php endforeach; endif; unset($_from); ?>
			</small>
		</td>
	</tr>
	<?php endif; ?>
	<tr>
		<td valign="top">
			<?php echo ((is_array($_tmp='Installation_SystemCheckWriteDirs')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

		</td>
		<td>
			<small>
				<?php $_from = $this->_tpl_vars['infos']['directories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['dir'] => $this->_tpl_vars['bool']):
?>
					<?php if ($this->_tpl_vars['bool']): ?><?php echo $this->_tpl_vars['ok']; ?>
<?php else: ?>
					<span style="color:red"><?php echo $this->_tpl_vars['error']; ?>
</span><?php endif; ?> 
					<?php echo $this->_tpl_vars['dir']; ?>

					<br />				
				<?php endforeach; endif; unset($_from); ?>
			</small>
		</td>
	</tr>
</table>
<?php if ($this->_tpl_vars['problemWithSomeDirectories']): ?>
	<br />
	<div class="error">
		<?php echo ((is_array($_tmp='Installation_SystemCheckWriteDirsHelp')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
:
		<?php $_from = $this->_tpl_vars['infos']['directories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['dir'] => $this->_tpl_vars['bool']):
?>
			<ul><?php if (! $this->_tpl_vars['bool']): ?>
					<li><pre>chmod a+w <?php echo $this->_tpl_vars['dir']; ?>
</pre></li>
				<?php endif; ?>
			</ul>
		<?php endforeach; endif; unset($_from); ?>
	</div>
	<br />
<?php endif; ?>
<h1><?php echo ((is_array($_tmp='Optional')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h1>
<table class="infos">
	<tr>
		<td class="label"><?php echo ((is_array($_tmp='Installation_SystemCheckMemoryLimit')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
		<td>
			<?php echo $this->_tpl_vars['infos']['memoryCurrent']; ?>

			<?php if ($this->_tpl_vars['infos']['memory_ok']): ?><?php echo $this->_tpl_vars['ok']; ?>
<?php else: ?><?php echo $this->_tpl_vars['warning']; ?>
 
				<br /><i><?php echo ((is_array($_tmp='Installation_SystemCheckMemoryLimitHelp')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</i><?php endif; ?>	
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo ((is_array($_tmp='Installation_SystemCheckOpenURL')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
		<td>
			<?php if ($this->_tpl_vars['infos']['openurl']): ?><?php echo $this->_tpl_vars['infos']['openurl']; ?>
 <?php echo $this->_tpl_vars['ok']; ?>
<?php else: ?><?php echo $this->_tpl_vars['warning']; ?>
 <br /><i><?php echo ((is_array($_tmp='Installation_SystemCheckOpenURLHelp')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</i><?php endif; ?>
		</td>
	</tr>
	<?php if ($this->_tpl_vars['infos']['json']): ?>
	<tr>
		<td class="label"><?php echo ((is_array($_tmp='Installation_SystemCheckXml')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
		<td>
			<?php if ($this->_tpl_vars['infos']['xml']): ?><?php echo $this->_tpl_vars['ok']; ?>
<?php else: ?><?php echo $this->_tpl_vars['warning']; ?>
<br /><i><?php echo ((is_array($_tmp='Installation_SystemCheckXmlHelp')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</i><?php endif; ?>
		</td>
	</tr>
	<?php endif; ?>
	<tr>
		<td class="label"><?php echo ((is_array($_tmp='Installation_SystemCheckGD')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
		<td>
			<?php if ($this->_tpl_vars['infos']['gd_ok']): ?><?php echo $this->_tpl_vars['ok']; ?>
<?php else: ?><?php echo $this->_tpl_vars['warning']; ?>
 <br /><i><?php echo ((is_array($_tmp='Installation_SystemCheckGDHelp')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</i><?php endif; ?>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo ((is_array($_tmp='Installation_SystemCheckFunctions')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
		<td><?php $_from = $this->_tpl_vars['infos']['needed_functions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['needed_function']):
?>
				<?php echo $this->_tpl_vars['needed_function']; ?>

				<?php if (in_array ( $this->_tpl_vars['needed_function'] , $this->_tpl_vars['infos']['missing_functions'] )): ?>
					<?php echo $this->_tpl_vars['warning']; ?>

					<p>
					<small>
					<?php echo ((is_array($_tmp=$this->_tpl_vars['helpMessages'][$this->_tpl_vars['needed_function']])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

					</small>
					</p>
				<?php else: ?>
					<?php echo $this->_tpl_vars['ok']; ?>
<br />
				<?php endif; ?>
			<?php endforeach; endif; unset($_from); ?>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo ((is_array($_tmp='Installation_SystemCheckProtocol')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
		<td>
			<?php if ($this->_tpl_vars['infos']['protocol_ok']): ?><?php echo $this->_tpl_vars['ok']; ?>
<?php else: ?><?php echo $this->_tpl_vars['warning']; ?>
 <?php echo $this->_tpl_vars['infos']['protocol']; ?>
<br /><i><?php echo ((is_array($_tmp='Installation_SystemCheckProtocolHelp')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</i><br /><br /><code>[General]</code><br /><code>reverse_proxy = 1</code><br /><?php endif; ?>
		</td>
	</tr>
</table>

<p>
<?php echo $this->_tpl_vars['link']; ?>
 <a href="http://piwik.org/docs/requirements/" target="_blank"><?php echo ((is_array($_tmp='Installation_Requirements')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a> 
</p>

<?php if (! $this->_tpl_vars['showNextStep']): ?>
<?php echo '
<style>
#legend {
	border:1px solid #A5A5A5;
	padding:5px;
	color:#727272;
	margin-top:30px;
}
</style>
'; ?>

<div id="legend"><small>
<b><?php echo ((is_array($_tmp='Installation_Legend')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</b>
<br />
<?php echo $this->_tpl_vars['ok']; ?>
 <?php echo ((is_array($_tmp='General_Ok')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
<br />
<?php echo $this->_tpl_vars['error']; ?>
 <?php echo ((is_array($_tmp='General_Error')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
: <?php echo ((is_array($_tmp='Installation_SystemCheckError')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
 <br />
<?php echo $this->_tpl_vars['warning']; ?>
 <?php echo ((is_array($_tmp='General_Warning')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
: <?php echo ((is_array($_tmp='Installation_SystemCheckWarning')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
 <br />
</small></div>


<p class="nextStep">
	<a href="<?php echo smarty_function_url(array(), $this);?>
"><?php echo ((is_array($_tmp='General_Refresh')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
 &raquo;</a>
</p>
<?php endif; ?>