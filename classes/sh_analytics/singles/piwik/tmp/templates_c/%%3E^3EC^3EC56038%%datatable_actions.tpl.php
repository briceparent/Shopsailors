<?php /* Smarty version 2.6.25, created on 2010-01-19 16:27:28
         compiled from CoreHome/templates/datatable_actions.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'CoreHome/templates/datatable_actions.tpl', 7, false),)), $this); ?>
<div id="<?php echo $this->_tpl_vars['properties']['uniqueId']; ?>
">
	<div class="dataTableActionsWrapper">
	<?php if (isset ( $this->_tpl_vars['arrayDataTable']['result'] ) && $this->_tpl_vars['arrayDataTable']['result'] == 'error'): ?>
		<?php echo $this->_tpl_vars['arrayDataTable']['message']; ?>
 
	<?php else: ?>
		<?php if (count ( $this->_tpl_vars['arrayDataTable'] ) == 0): ?>
			<div id="emptyDatatable"><?php echo ((is_array($_tmp='CoreHome_TableNoData')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</div>
		<?php else: ?>
			<table cellspacing="0" class="dataTable dataTableActions"> 
			<thead>
			<tr>
			<?php $_from = $this->_tpl_vars['dataTableColumns']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['column']):
?>
				<th class="sortable" id="<?php echo $this->_tpl_vars['column']; ?>
"><?php echo $this->_tpl_vars['columnTranslations'][$this->_tpl_vars['column']]; ?>
</td>
			<?php endforeach; endif; unset($_from); ?>
			</tr>
			</thead>
			
			<tbody>
			<?php $_from = $this->_tpl_vars['arrayDataTable']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row']):
?>
			<tr <?php if ($this->_tpl_vars['row']['idsubdatatable']): ?>class="rowToProcess subActionsDataTable" id="<?php echo $this->_tpl_vars['row']['idsubdatatable']; ?>
"<?php else: ?> class="actionsDataTable rowToProcess"<?php endif; ?>>
				<?php $_from = $this->_tpl_vars['dataTableColumns']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['column']):
?>
				<td>
				<?php if (isset ( $this->_tpl_vars['row']['columns'][$this->_tpl_vars['column']] )): ?><?php echo $this->_tpl_vars['row']['columns'][$this->_tpl_vars['column']]; ?>
<?php else: ?><?php echo $this->_tpl_vars['defaultWhenColumnValueNotDefined']; ?>
<?php endif; ?>
				</td>
				<?php endforeach; endif; unset($_from); ?>
			</tr>
			<?php endforeach; endif; unset($_from); ?>
			</tbody>
		</table>
		<?php endif; ?>
	
		<?php if ($this->_tpl_vars['properties']['show_footer']): ?>
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreHome/templates/datatable_footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php endif; ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreHome/templates/datatable_actions_js.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php endif; ?>
	</div>
</div>