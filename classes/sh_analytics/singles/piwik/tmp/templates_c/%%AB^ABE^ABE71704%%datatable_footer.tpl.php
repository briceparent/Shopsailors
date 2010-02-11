<?php /* Smarty version 2.6.25, created on 2010-01-19 11:35:32
         compiled from CoreHome/templates/datatable_footer.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'CoreHome/templates/datatable_footer.tpl', 9, false),)), $this); ?>
<div id="dataTableFeatures">
<?php if ($this->_tpl_vars['properties']['show_exclude_low_population']): ?>
	<span id="dataTableExcludeLowPopulation"></span>
<?php endif; ?>

<?php if ($this->_tpl_vars['properties']['show_offset_information']): ?>
<div>
	<span id="dataTablePages"></span>
	<span id="dataTablePrevious">&lsaquo; <?php echo ((is_array($_tmp='General_Previous')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</span>
	<span id="dataTableNext"><?php echo ((is_array($_tmp='General_Next')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
 &rsaquo;</span>
</div>
<?php endif; ?>

<?php if ($this->_tpl_vars['properties']['show_search']): ?>
<span id="dataTableSearchPattern">
	<input id="keyword" type="text" length="15" />
	<input type="submit" value="<?php echo ((is_array($_tmp='General_Search')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" />
</span>
<?php endif; ?>

<?php if ($this->_tpl_vars['properties']['show_footer_icons']): ?>
	<div>
		<span id="dataTableFooterIcons">
			<span id="exportToFormat" style="display:none;padding-left:4px;">
				<?php if ($this->_tpl_vars['properties']['show_export_as_image_icon']): ?>
					<span id="dataTableFooterExportAsImageIcon">
						<a href="javascript:piwikHelper.OFC.jquery.popup('<?php echo $this->_tpl_vars['chartDivId']; ?>
');"><img title="<?php echo ((is_array($_tmp='General_ExportAsImage_js')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" src="themes/default/images/image.png" /></a>
					</span>
				<?php endif; ?>
				<img width="16" height="16" src="themes/default/images/export.png" title="<?php echo ((is_array($_tmp='General_Export')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" />
				<span id="linksExportToFormat" style="display:none"> 
					<a target="_blank" class="exportToFormat" methodToCall="<?php echo $this->_tpl_vars['properties']['apiMethodToRequestDataTable']; ?>
" format="CSV" filter_limit="100">CSV</a> | 
					<a target="_blank" class="exportToFormat" methodToCall="<?php echo $this->_tpl_vars['properties']['apiMethodToRequestDataTable']; ?>
" format="XML" filter_limit="100">XML</a> |
					<a target="_blank" class="exportToFormat" methodToCall="<?php echo $this->_tpl_vars['properties']['apiMethodToRequestDataTable']; ?>
" format="JSON" filter_limit="100">Json</a> |
					<a target="_blank" class="exportToFormat" methodToCall="<?php echo $this->_tpl_vars['properties']['apiMethodToRequestDataTable']; ?>
" format="PHP" filter_limit="100">Php</a> | 
					<a target="_blank" class="exportToFormat" methodToCall="<?php echo $this->_tpl_vars['properties']['apiMethodToRequestDataTable']; ?>
" format="RSS" filter_limit="100" date="last10"><img border="0" src="themes/default/images/feed.png"></a>
				</span>
			<?php if ($this->_tpl_vars['properties']['show_all_views_icons']): ?>
				<a class="viewDataTable" format="cloud"><img width="16" height="16" src="themes/default/images/tagcloud.png" title="<?php echo ((is_array($_tmp='General_TagCloud')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" /></a>
				<a class="viewDataTable" format="graphVerticalBar"><img width="16" height="16" src="themes/default/images/chart_bar.png" title="<?php echo ((is_array($_tmp='General_VBarGraph')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" /></a>
				<a class="viewDataTable" format="graphPie"><img width="16" height="16" src="themes/default/images/chart_pie.png" title="<?php echo ((is_array($_tmp='General_Piechart')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" /></a>
			<?php endif; ?>
			</span>
			<span id="dataTableFooterIconsShow" style="display:none;padding-left:4px;">
				<img src="plugins/CoreHome/templates/images/more.png" />
			</span>
			
			<?php if ($this->_tpl_vars['properties']['show_table']): ?>
				<span id="tableAllColumnsSwitch" style="display:none;float:right;padding-right:4px;border-right:1px solid #82A1D2;">
				<?php if ($this->_tpl_vars['javascriptVariablesToSet']['viewDataTable'] != 'table'): ?>
					<img title="<?php echo ((is_array($_tmp='General_DisplayNormalTable')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" src="themes/default/images/table.png" />
				<?php elseif ($this->_tpl_vars['properties']['show_table_all_columns']): ?>
					<img title="<?php echo ((is_array($_tmp='General_DisplayMoreData')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" src="themes/default/images/table_more.png" />
				<?php endif; ?>
				</span>
			<?php endif; ?>
			
			<?php if ($this->_tpl_vars['properties']['show_goals']): ?>
			<span id="tableGoals" style="display:none;float:right;padding-right:4px;">
				<?php if ($this->_tpl_vars['javascriptVariablesToSet']['viewDataTable'] != 'tableGoals'): ?>
					<img title="View Goals" src="themes/default/images/goal.png" />
				<?php endif; ?>
			</span>
			<?php endif; ?>
		</span>
	</div>
<?php endif; ?>

<span id="loadingDataTable"><img src="themes/default/images/loading-blue.gif" /> <?php echo ((is_array($_tmp='General_LoadingData')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</span>
</div>

<div class="dataTableSpacer" />