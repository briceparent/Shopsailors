<?php /* Smarty version 2.6.25, created on 2010-01-19 11:35:26
         compiled from Login/templates/header.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'Login/templates/header.tpl', 30, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	<title>Piwik &rsaquo; Login</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
	<link rel="shortcut icon" href="plugins/CoreHome/templates/images/favicon.ico" />

	<link rel="stylesheet" type="text/css" href="plugins/Login/templates/login.css" media="screen" />
	
	<?php echo '
	<script type="text/javascript">
		function focusit() {
			var formLogin = document.getElementById(\'form_login\');
			if(formLogin)
			{
				formLogin.focus();
			}
		}
		window.onload = focusit;
	</script>
	'; ?>

</head>

<body class="login">
<!-- shamelessly taken from wordpress 2.5 - thank you guys!!! -->

<div id="logo">
	<a href="http://piwik.org" title="<?php echo $this->_tpl_vars['linkTitle']; ?>
"><span class="h1"><span style="color: rgb(245, 223, 114);">P</span><span style="color: rgb(241, 175, 108);">i</span><span style="color: rgb(241, 117, 117);">w</span><span style="color: rgb(155, 106, 58);">i</span><span style="color: rgb(107, 50, 11);">k</span> <span class="description"># <?php echo ((is_array($_tmp='General_OpenSourceWebAnalytics')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</span></span></a>
</div>