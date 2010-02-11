<?php /* Smarty version 2.6.25, created on 2010-01-19 12:00:02
         compiled from MultiSites/templates/index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'fetch', 'MultiSites/templates/index.tpl', 8, false),array('function', 'postEvent', 'MultiSites/templates/index.tpl', 37, false),array('modifier', 'replace', 'MultiSites/templates/index.tpl', 19, false),array('modifier', 'translate', 'MultiSites/templates/index.tpl', 30, false),array('modifier', 'escape', 'MultiSites/templates/index.tpl', 32, false),)), $this); ?>

<?php $this->assign('showSitesSelection', false); ?>
<?php $this->assign('showPeriodSelection', true); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreHome/templates/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<script type="text/javascript" src="plugins/MultiSites/templates/common.js"></script>
<style>
<?php echo smarty_function_fetch(array('file' => "plugins/MultiSites/templates/styles.css"), $this);?>

</style>

<div id="multisites" style="margin: auto">
<div id="main">
<?php ob_start();
$_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "MultiSites/templates/row.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
$this->assign('row', ob_get_contents()); ob_end_clean();
 ?>

<script type="text/javascript">
	var allSites = new Array();
	var params = new Array();
	<?php $_from = $this->_tpl_vars['mySites']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i'] => $this->_tpl_vars['site']):
?>
		allSites[<?php echo $this->_tpl_vars['i']; ?>
] = new setRowData(<?php echo $this->_tpl_vars['site']['idsite']; ?>
, <?php echo $this->_tpl_vars['site']['visits']; ?>
, <?php echo $this->_tpl_vars['site']['actions']; ?>
, <?php echo $this->_tpl_vars['site']['unique']; ?>
, '<?php echo $this->_tpl_vars['site']['name']; ?>
', '<?php echo $this->_tpl_vars['site']['main_url']; ?>
', '<?php echo ((is_array($_tmp=$this->_tpl_vars['site']['visitsSummaryValue'])) ? $this->_run_mod_handler('replace', true, $_tmp, ",", ".") : smarty_modifier_replace($_tmp, ",", ".")); ?>
', '<?php echo ((is_array($_tmp=$this->_tpl_vars['site']['actionsSummaryValue'])) ? $this->_run_mod_handler('replace', true, $_tmp, ",", ".") : smarty_modifier_replace($_tmp, ",", ".")); ?>
', '<?php echo ((is_array($_tmp=$this->_tpl_vars['site']['uniqueSummaryValue'])) ? $this->_run_mod_handler('replace', true, $_tmp, ",", ".") : smarty_modifier_replace($_tmp, ",", ".")); ?>
');
	<?php endforeach; endif; unset($_from); ?>
    params['period'] = '<?php echo $this->_tpl_vars['period']; ?>
';
	params['date'] = '<?php echo $this->_tpl_vars['date']; ?>
';
	params['dateToStr'] = '<?php echo $this->_tpl_vars['dateToStr']; ?>
';
	params['evolutionBy'] = '<?php echo $this->_tpl_vars['evolutionBy']; ?>
';
	params['mOrderBy'] = '<?php echo $this->_tpl_vars['orderBy']; ?>
';
	params['order'] = '<?php echo $this->_tpl_vars['order']; ?>
';
	params['site'] = '<?php echo $this->_tpl_vars['site']; ?>
';
	params['limit'] = '<?php echo $this->_tpl_vars['limit']; ?>
';
	params['page'] = 1;
	params['prev'] = "<?php echo ((is_array($_tmp='General_Previous')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
";
	params['next'] = "<?php echo ((is_array($_tmp='General_Next')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
";
	params['row'] = '<?php echo ((is_array($_tmp=$this->_tpl_vars['row'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : smarty_modifier_escape($_tmp, 'javascript')); ?>
';
	params['arrow_desc'] = '<span id="arrow_desc" class="desc"><?php echo $this->_tpl_vars['arrowDesc']; ?>
</span>';
	params['arrow_asc'] = '<span id="arrow_asc" class="asc"><?php echo $this->_tpl_vars['arrowAsc']; ?>
</span>';
</script>

<?php echo smarty_function_postEvent(array('name' => 'template_headerMultiSites'), $this);?>

<table id="mt" class="dataTable" cellspacing="0" style="width:850px;margin: auto">
	<thead>
		<th class="label" style="text-align:center">
			<span style="cursor:pointer;" onClick="params = setOrderBy(this,allSites, params, 'names');"><?php echo ((is_array($_tmp='General_Website')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</span>
		</th>
		<th class="multisites-column">
			<span style="cursor:pointer;" onClick="params = setOrderBy(this,allSites, params, 'visits');"><?php echo ((is_array($_tmp='General_ColumnNbVisits')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</span>
		</th>
		<th class="multisites-column">
			<span style="cursor:pointer;" onClick="params = setOrderBy(this,allSites, params, 'actions');"><?php echo ((is_array($_tmp='General_ColumnPageviews')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</span>
		</th>
		<th class="multisites-column">
			<span style="cursor:pointer;" onClick="params = setOrderBy(this,allSites, params, 'unique');"><?php echo ((is_array($_tmp='General_ColumnNbUniqVisitors')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</span>
		</th>
		<th style="text-align:center;width:350px" colspan="2">
			<span style="cursor:pointer;" onClick="params = setOrderBy(this,allSites, params, $('#evolution_selector').val() + 'Summary');"> Evolution</span>
			<select class="selector" id="evolution_selector" onchange="params['evolutionBy'] = $('#evolution_selector').val(); switchEvolution(params);">
				<option value="visits" <?php if ($this->_tpl_vars['evolutionBy'] == 'visits'): ?> selected <?php endif; ?>><?php echo ((is_array($_tmp='General_ColumnNbVisits')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</option>
				<option value="actions" <?php if ($this->_tpl_vars['evolutionBy'] == 'actions'): ?> selected <?php endif; ?>><?php echo ((is_array($_tmp='General_ColumnPageviews')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</option>
				<option value="unique"<?php if ($this->_tpl_vars['evolutionBy'] == 'unique'): ?> selected <?php endif; ?>><?php echo ((is_array($_tmp='General_ColumnNbUniqVisitors')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</option>
			</select>
		</th>
	</thead>
	
	<tbody id="tb">
	</tbody>
	
	<tfoot>
	<tr row_id="last">
		<td colspan="8" class="clean">
		<span id="prev" class="pager"  style="padding-right: 20px;"></span>
		<div id="dataTablePages">
			<span id="counter">
			</span>
		</div>
		<span id="next" class="clean" style="padding-left: 20px;"></span>
	</td>
	</tr>
	</tfoot>
</table>

<script type="text/javascript">
prepareRows(allSites, params, '<?php echo $this->_tpl_vars['orderBy']; ?>
');
</script>
</div>
</div>

</body>
</html>