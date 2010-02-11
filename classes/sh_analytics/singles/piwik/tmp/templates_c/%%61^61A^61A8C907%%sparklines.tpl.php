<?php /* Smarty version 2.6.25, created on 2010-01-19 16:31:24
         compiled from VisitFrequency/templates/sparklines.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'sparkline', 'VisitFrequency/templates/sparklines.tpl', 1, false),array('modifier', 'translate', 'VisitFrequency/templates/sparklines.tpl', 2, false),array('modifier', 'sumtime', 'VisitFrequency/templates/sparklines.tpl', 8, false),)), $this); ?>
<div class="sparkline"><?php echo smarty_function_sparkline(array('src' => $this->_tpl_vars['urlSparklineNbVisitsReturning']), $this);?>

<?php echo ((is_array($_tmp='VisitFrequency_ReturnVisits')) ? $this->_run_mod_handler('translate', true, $_tmp, "<strong>".($this->_tpl_vars['nbVisitsReturning'])."</strong>") : smarty_modifier_translate($_tmp, "<strong>".($this->_tpl_vars['nbVisitsReturning'])."</strong>")); ?>
</div>
<div class="sparkline"><?php echo smarty_function_sparkline(array('src' => $this->_tpl_vars['urlSparklineNbActionsReturning']), $this);?>

<?php echo ((is_array($_tmp='VisitFrequency_ReturnActions')) ? $this->_run_mod_handler('translate', true, $_tmp, "<strong>".($this->_tpl_vars['nbActionsReturning'])."</strong>") : smarty_modifier_translate($_tmp, "<strong>".($this->_tpl_vars['nbActionsReturning'])."</strong>")); ?>
</div>
<div class="sparkline"><?php echo smarty_function_sparkline(array('src' => $this->_tpl_vars['urlSparklineMaxActionsReturning']), $this);?>

 <?php echo ((is_array($_tmp='VisitFrequency_ReturnMaxActions')) ? $this->_run_mod_handler('translate', true, $_tmp, "<strong>".($this->_tpl_vars['maxActionsReturning'])."</strong>") : smarty_modifier_translate($_tmp, "<strong>".($this->_tpl_vars['maxActionsReturning'])."</strong>")); ?>
</div>
<div class="sparkline"><?php echo smarty_function_sparkline(array('src' => $this->_tpl_vars['urlSparklineSumVisitLengthReturning']), $this);?>

 <?php $this->assign('sumtimeVisitLengthReturning', ((is_array($_tmp=$this->_tpl_vars['sumVisitLengthReturning'])) ? $this->_run_mod_handler('sumtime', true, $_tmp) : smarty_modifier_sumtime($_tmp))); ?>
 <?php echo ((is_array($_tmp='VisitFrequency_ReturnTotalTime')) ? $this->_run_mod_handler('translate', true, $_tmp, "<strong>".($this->_tpl_vars['sumtimeVisitLengthReturning'])."</strong>") : smarty_modifier_translate($_tmp, "<strong>".($this->_tpl_vars['sumtimeVisitLengthReturning'])."</strong>")); ?>
</div>
<div class="sparkline"><?php echo smarty_function_sparkline(array('src' => $this->_tpl_vars['urlSparklineBounceRateReturning']), $this);?>

 <?php echo ((is_array($_tmp='VisitFrequency_ReturnBounceRate')) ? $this->_run_mod_handler('translate', true, $_tmp, "<strong>".($this->_tpl_vars['bounceRateReturning'])."%</strong>") : smarty_modifier_translate($_tmp, "<strong>".($this->_tpl_vars['bounceRateReturning'])."%</strong>")); ?>
 </div>