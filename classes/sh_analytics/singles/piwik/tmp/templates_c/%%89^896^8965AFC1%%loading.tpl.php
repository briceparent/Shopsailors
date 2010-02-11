<?php /* Smarty version 2.6.25, created on 2010-01-19 11:35:30
         compiled from CoreHome/templates/loading.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'CoreHome/templates/loading.tpl', 2, false),)), $this); ?>
<div id="loadingPiwik" <?php if (isset ( $this->_tpl_vars['basicHtmlView'] ) && $this->_tpl_vars['basicHtmlView']): ?>style="display:none;"<?php endif; ?>>
<img src="themes/default/images/loading-blue.gif" alt="" /> <?php echo ((is_array($_tmp='General_LoadingData')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

</div>
<div id="loadingError"><?php echo ((is_array($_tmp='General_ErrorRequest')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</div>