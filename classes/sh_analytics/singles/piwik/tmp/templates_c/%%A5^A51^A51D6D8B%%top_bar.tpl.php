<?php /* Smarty version 2.6.25, created on 2010-01-19 11:35:30
         compiled from CoreHome/templates/top_bar.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'assignTopBar', 'CoreHome/templates/top_bar.tpl', 1, false),array('function', 'postEvent', 'CoreHome/templates/top_bar.tpl', 9, false),array('modifier', 'urlRewriteWithParameters', 'CoreHome/templates/top_bar.tpl', 7, false),array('modifier', 'translate', 'CoreHome/templates/top_bar.tpl', 15, false),)), $this); ?>
<?php echo smarty_function_assignTopBar(array(), $this);?>


<div id="topBars">

<div id="topLeftBar">
<?php $_from = $this->_tpl_vars['topBarElements']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['element']):
?>
	<span class="topBarElem"><?php if ($this->_tpl_vars['element']['0'] == $this->_tpl_vars['currentModule']): ?><b><?php else: ?><a href="index.php<?php echo smarty_modifier_urlRewriteWithParameters($this->_tpl_vars['element']['2']); ?>
" <?php if (isset ( $this->_tpl_vars['element']['3'] )): ?><?php echo $this->_tpl_vars['element']['3']; ?>
<?php endif; ?>><?php endif; ?><?php echo $this->_tpl_vars['element']['1']; ?>
<?php if ($this->_tpl_vars['element']['0'] == $this->_tpl_vars['currentModule']): ?></b><?php else: ?></a><?php endif; ?></span>
<?php endforeach; endif; unset($_from); ?>
<?php echo smarty_function_postEvent(array('name' => 'template_topBar'), $this);?>
 
</div>

<div id="topRightBar">
<nobr>
<small>
<?php echo ((is_array($_tmp='General_HelloUser')) ? $this->_run_mod_handler('translate', true, $_tmp, "<strong>".($this->_tpl_vars['userLogin'])."</strong>") : smarty_modifier_translate($_tmp, "<strong>".($this->_tpl_vars['userLogin'])."</strong>")); ?>

<?php if (isset ( $this->_tpl_vars['userHasSomeAdminAccess'] ) && $this->_tpl_vars['userHasSomeAdminAccess']): ?>| <a href='index.php?module=CoreAdminHome'><?php echo ((is_array($_tmp='General_Settings')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a><?php endif; ?> 
 <?php if ($this->_tpl_vars['showSitesSelection'] && $this->_tpl_vars['showWebsiteSelectorInUserInterface']): ?>| <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreHome/templates/sites_selection.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php endif; ?>
| <?php if ($this->_tpl_vars['userLogin'] == 'anonymous'): ?><a href='index.php?module=<?php echo $this->_tpl_vars['loginModule']; ?>
'><?php echo ((is_array($_tmp='Login_LogIn')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a><?php else: ?><a href='index.php?module=<?php echo $this->_tpl_vars['loginModule']; ?>
&amp;action=logout'><?php echo ((is_array($_tmp='Login_Logout')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a><?php endif; ?>
</small>

</nobr>
</div>

<br clear="all" />

</div>