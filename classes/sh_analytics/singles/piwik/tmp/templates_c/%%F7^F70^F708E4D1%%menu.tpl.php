<?php /* Smarty version 2.6.25, created on 2010-01-19 12:01:37
         compiled from CoreAdminHome/templates/menu.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'urlRewriteWithParameters', 'CoreAdminHome/templates/menu.tpl', 4, false),array('modifier', 'translate', 'CoreAdminHome/templates/menu.tpl', 4, false),)), $this); ?>
<div id="menu">
<ul id="tablist">
<?php $_from = $this->_tpl_vars['menu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['menu'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['menu']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['name'] => $this->_tpl_vars['url']):
        $this->_foreach['menu']['iteration']++;
?>
	<li><a href='index.php<?php echo smarty_modifier_urlRewriteWithParameters($this->_tpl_vars['url']); ?>
'><?php echo ((is_array($_tmp=$this->_tpl_vars['name'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a></li>
<?php endforeach; endif; unset($_from); ?>
</ul>
</div>