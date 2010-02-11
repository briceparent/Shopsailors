<?php /* Smarty version 2.6.25, created on 2010-01-19 16:31:24
         compiled from /var/www/dev/shopsailors/classes/sh_analytics/singles/piwik/plugins/VisitFrequency/templates/index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'postEvent', '/var/www/dev/shopsailors/classes/sh_analytics/singles/piwik/plugins/VisitFrequency/templates/index.tpl', 1, false),array('modifier', 'translate', '/var/www/dev/shopsailors/classes/sh_analytics/singles/piwik/plugins/VisitFrequency/templates/index.tpl', 5, false),)), $this); ?>
<?php echo smarty_function_postEvent(array('name' => 'template_headerVisitsFrequency'), $this);?>

<script type="text/javascript" src="plugins/CoreHome/templates/sparkline.js"></script>

<a name="evolutionGraph" graphId="VisitFrequencygetEvolutionGraph"></a>
<h2><?php echo ((is_array($_tmp='VisitFrequency_Evolution')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h2>
<?php echo $this->_tpl_vars['graphEvolutionVisitFrequency']; ?>

<br />

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "VisitFrequency/templates/sparklines.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	
<?php echo smarty_function_postEvent(array('name' => 'template_footerVisitsFrequency'), $this);?>
