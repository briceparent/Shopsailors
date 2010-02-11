<?php /* Smarty version 2.6.25, created on 2010-01-19 11:35:31
         compiled from /var/www/dev/shopsailors/classes/sh_analytics/singles/piwik/plugins/Dashboard/templates/index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'loadJavascriptTranslations', '/var/www/dev/shopsailors/classes/sh_analytics/singles/piwik/plugins/Dashboard/templates/index.tpl', 1, false),array('modifier', 'translate', '/var/www/dev/shopsailors/classes/sh_analytics/singles/piwik/plugins/Dashboard/templates/index.tpl', 27, false),)), $this); ?>
<?php echo smarty_function_loadJavascriptTranslations(array('plugins' => 'CoreHome Dashboard'), $this);?>


<script type="text/javascript">
	piwik.dashboardLayout = <?php echo $this->_tpl_vars['layout']; ?>
;
		piwik.availableWidgets = <?php echo $this->_tpl_vars['availableWidgets']; ?>
;
</script>

<?php echo '
<script type="text/javascript">
$(document).ready( function() {
		var dashboardObject = new dashboard();
		var widgetMenuObject = new widgetMenu(dashboardObject);
		dashboardObject.init(piwik.dashboardLayout);
		widgetMenuObject.init();
		$(\'.button#addWidget\').click(function(){widgetMenuObject.show();});
});
</script>
'; ?>

<div id="dashboard">
 
	<div class="dialog" id="confirm"> 
	        <img src="themes/default/images/delete.png" style="padding: 10px; position: relative; margin-top: 10%; float: left;"/>
	        <p><?php echo ((is_array($_tmp='Dashboard_DeleteWidgetConfirm')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</p>
			<input id="yes" type="button" value="<?php echo ((is_array($_tmp='General_Yes')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
"/>
			<input id="no" type="button" value="<?php echo ((is_array($_tmp='General_No')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
"/>
	</div> 

	<div class="button" id="addWidget">
		<?php echo ((is_array($_tmp='Dashboard_AddWidget')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

	</div>
	
	<div class="menu" id="widgetChooser">
		<div id="closeMenuIcon"><img src="themes/default/images/close_medium.png" title="<?php echo ((is_array($_tmp='General_Close')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
"/></div>
		<div id="menuTitleBar"><?php echo ((is_array($_tmp='Dashboard_SelectWidget')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</div>

		<div class="subMenu" id="sub1"></div>
		<div class="subMenu" id="sub2"></div>
		<div class="subMenu" id="sub3"></div>
		<div class="menuClear"> </div>
	</div>	

	<div id="dashboardWidgetsArea">
		<div class="col" id="1"></div>
		<div class="col" id="2"></div>
		<div class="col" id="3"></div>
	</div>
</div>