<?php /* Smarty version 2.6.25, created on 2010-01-19 11:11:12
         compiled from Installation/templates/welcome.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'Installation/templates/welcome.tpl', 1, false),)), $this); ?>
<h1><?php echo ((is_array($_tmp='Installation_Welcome')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h1>

<?php echo ((is_array($_tmp='Installation_WelcomeHelp')) ? $this->_run_mod_handler('translate', true, $_tmp, $this->_tpl_vars['totalNumberOfSteps']) : smarty_modifier_translate($_tmp, $this->_tpl_vars['totalNumberOfSteps'])); ?>


<?php echo '
<script type="text/javascript">
<!--
$(function() {
if (document.location.protocol === \'https:\') {
	$(\'p.nextStep a\').attr(\'href\', $(\'p.nextStep a\').attr(\'href\') + \'&clientProtocol=https\');
}
});
//-->
</script>
'; ?>
