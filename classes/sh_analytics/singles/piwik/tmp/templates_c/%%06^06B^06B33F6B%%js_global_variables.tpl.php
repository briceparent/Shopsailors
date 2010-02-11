<?php /* Smarty version 2.6.25, created on 2010-01-19 11:35:30
         compiled from CoreHome/templates/js_global_variables.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'urlencode', 'CoreHome/templates/js_global_variables.tpl', 4, false),)), $this); ?>
<script type="text/javascript">
	var piwik = <?php echo '{}'; ?>
;
	piwik.token_auth = "<?php echo $this->_tpl_vars['token_auth']; ?>
";
	piwik.piwik_url = "<?php echo ((is_array($_tmp=$this->_tpl_vars['piwikUrl'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
";
	<?php if (isset ( $this->_tpl_vars['idSite'] )): ?>piwik.idSite = "<?php echo $this->_tpl_vars['idSite']; ?>
";<?php endif; ?>
	<?php if (isset ( $this->_tpl_vars['siteName'] )): ?>piwik.siteName = "<?php echo $this->_tpl_vars['siteName']; ?>
";<?php endif; ?>
	<?php if (isset ( $this->_tpl_vars['siteMainUrl'] )): ?>piwik.siteMainUrl = "<?php echo $this->_tpl_vars['siteMainUrl']; ?>
";<?php endif; ?>
	<?php if (isset ( $this->_tpl_vars['period'] )): ?>piwik.period = "<?php echo $this->_tpl_vars['period']; ?>
";<?php endif; ?>
	<?php if (isset ( $this->_tpl_vars['date'] )): ?>piwik.currentDateString = "<?php echo $this->_tpl_vars['date']; ?>
";<?php endif; ?>
	<?php if (isset ( $this->_tpl_vars['minDateYear'] )): ?>piwik.minDateYear = <?php echo $this->_tpl_vars['minDateYear']; ?>
;<?php endif; ?>
	<?php if (isset ( $this->_tpl_vars['minDateMonth'] )): ?>piwik.minDateMonth = parseInt("<?php echo $this->_tpl_vars['minDateMonth']; ?>
", 10);<?php endif; ?>
	<?php if (isset ( $this->_tpl_vars['minDateDay'] )): ?>piwik.minDateDay = parseInt("<?php echo $this->_tpl_vars['minDateDay']; ?>
", 10);<?php endif; ?>
	<?php if (isset ( $this->_tpl_vars['maxDateYear'] )): ?>piwik.maxDateYear = <?php echo $this->_tpl_vars['maxDateYear']; ?>
;<?php endif; ?>
	<?php if (isset ( $this->_tpl_vars['maxDateMonth'] )): ?>piwik.maxDateMonth = parseInt("<?php echo $this->_tpl_vars['maxDateMonth']; ?>
", 10);<?php endif; ?>
	<?php if (isset ( $this->_tpl_vars['maxDateDay'] )): ?>piwik.maxDateDay = parseInt("<?php echo $this->_tpl_vars['maxDateDay']; ?>
", 10);<?php endif; ?>
</script>