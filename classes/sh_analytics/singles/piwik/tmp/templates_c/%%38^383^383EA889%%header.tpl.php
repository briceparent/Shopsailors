<?php /* Smarty version 2.6.25, created on 2010-01-19 12:01:37
         compiled from CoreAdminHome/templates/header.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'postEvent', 'CoreAdminHome/templates/header.tpl', 16, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Piwik &rsaquo; Administration</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="generator" content="Piwik <?php echo $this->_tpl_vars['piwik_version']; ?>
" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<link rel="shortcut icon" href="plugins/CoreHome/templates/images/favicon.ico" />

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreHome/templates/js_global_variables.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<link rel="stylesheet" type="text/css" href="themes/default/common.css" />
<link rel="stylesheet" type="text/css" href="libs/jquery/themes/base/jquery-ui.css" class="ui-theme" />
<link rel="stylesheet" type="text/css" href="plugins/CoreAdminHome/templates/styles.css" />
<?php echo smarty_function_postEvent(array('name' => 'template_css_import'), $this);?>


<script type="text/javascript" src="themes/default/common.js"></script>
<script type="text/javascript" src="libs/jquery/jquery.js"></script>
<script type="text/javascript" src="libs/jquery/jquery-ui.js"></script>
<script type="text/javascript" src="libs/jquery/jquery.bgiframe.js"></script>
<script type="text/javascript" src="libs/jquery/jquery.blockUI.js"></script>
<script type="text/javascript" src="libs/jquery/fdd2div-modified.js"></script>
<script type="text/javascript" src="libs/javascript/sprintf.js"></script>
<?php echo smarty_function_postEvent(array('name' => 'template_js_import'), $this);?>


</head>
<body>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreHome/templates/top_bar.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<div id="header">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreHome/templates/header_message.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreHome/templates/logo.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php if ($this->_tpl_vars['showPeriodSelection']): ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreHome/templates/period_select.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php endif; ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreHome/templates/js_disabled_notice.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</div>

<br clear="all"/>

<div id="content">