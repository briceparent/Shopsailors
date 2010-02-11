<?php /* Smarty version 2.6.25, created on 2010-01-19 11:35:32
         compiled from CoreHome/templates/datatable_js.tpl */ ?>

<script type="text/javascript" defer="defer">
$(document).ready(function()<?php echo '{'; ?>
 
	dataTables['<?php echo $this->_tpl_vars['properties']['uniqueId']; ?>
'] = new dataTable();
	dataTables['<?php echo $this->_tpl_vars['properties']['uniqueId']; ?>
'].param = <?php echo '{'; ?>
 
	<?php $_from = $this->_tpl_vars['javascriptVariablesToSet']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['loop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['loop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['name'] => $this->_tpl_vars['value']):
        $this->_foreach['loop']['iteration']++;
?>
		<?php echo $this->_tpl_vars['name']; ?>
: '<?php echo $this->_tpl_vars['value']; ?>
'<?php if (! ($this->_foreach['loop']['iteration'] == $this->_foreach['loop']['total'])): ?>,<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>
	<?php echo '};'; ?>

	dataTables['<?php echo $this->_tpl_vars['properties']['uniqueId']; ?>
'].init('<?php echo $this->_tpl_vars['properties']['uniqueId']; ?>
');
<?php echo '}'; ?>
);
</script>