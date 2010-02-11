<?php /* Smarty version 2.6.25, created on 2010-01-19 11:35:30
         compiled from CoreHome/templates/period_select.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'loadJavascriptTranslations', 'CoreHome/templates/period_select.tpl', 1, false),array('function', 'url', 'CoreHome/templates/period_select.tpl', 10, false),)), $this); ?>
<?php echo smarty_function_loadJavascriptTranslations(array('plugins' => 'CoreHome'), $this);?>

<script type="text/javascript" src="plugins/CoreHome/templates/calendar.js"></script>
<script type="text/javascript" src="plugins/CoreHome/templates/date.js"></script>

<span id="periodString">
	<span id="date"><img src='themes/default/images/icon-calendar.gif' style="vertical-align:middle" alt="" /> <?php echo $this->_tpl_vars['prettyDate']; ?>
</span> -&nbsp;
	<span id="periods"> 
		<span id="currentPeriod"><?php echo $this->_tpl_vars['periodsNames'][$this->_tpl_vars['period']]['singular']; ?>
</span> 
		<span id="otherPeriods">
			<?php $_from = $this->_tpl_vars['otherPeriods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['thisPeriod']):
?> | <a href='<?php echo smarty_function_url(array('period' => $this->_tpl_vars['thisPeriod']), $this);?>
'><?php echo $this->_tpl_vars['periodsNames'][$this->_tpl_vars['thisPeriod']]['singular']; ?>
</a><?php endforeach; endif; unset($_from); ?>
		</span>
	</span>
	<br/>
	<span id="datepicker"></span>
</span>

<?php echo '<script language="javascript">
$(document).ready(function() {
     // this will trigger to change only the period value on search query and hash string.
     $("#otherPeriods a").bind(\'click\',function(e) {
        e.preventDefault();                            
        var request_URL = $(e.target).attr("href");
        var new_period = broadcast.getValueFromUrl(\'period\',request_URL);
        broadcast.propagateNewPage(\'period=\'+new_period);
    });
});</script>
'; ?>


<div style="clear:both"></div>
