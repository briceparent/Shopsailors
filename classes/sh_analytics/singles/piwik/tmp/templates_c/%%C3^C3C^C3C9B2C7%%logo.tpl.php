<?php /* Smarty version 2.6.25, created on 2010-01-19 11:35:30
         compiled from CoreHome/templates/logo.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'CoreHome/templates/logo.tpl', 2, false),)), $this); ?>
<span id="logo">
<a href="index.php" title="Piwik # <?php echo ((is_array($_tmp='General_OpenSourceWebAnalytics')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" style="text-decoration: none;">
	<span style="color: rgb(245, 223, 114);">P</span><span style="color: rgb(241, 175, 108);">i</span><span style="color: rgb(241, 117, 117);">w</span><span style="color: rgb(155, 106, 58);">i</span><span style="color: rgb(107, 50, 11);">k</span>
    <?php if ($this->_tpl_vars['currentModule'] != 'CoreHome'): ?><span style="padding-left:1em;font-size: 20pt; letter-spacing: -1pt; color: rgb(107, 50, 11);">&rsaquo; <?php echo $this->_tpl_vars['currentPluginName']; ?>
</span><?php endif; ?>
</a>
</span>