<?php /* Smarty version 2.6.25, created on 2010-01-19 11:11:37
         compiled from default/genericForm.tpl */ ?>

<?php if ($this->_tpl_vars['form_data']['errors']): ?>
	<div class="warning">
		<img src="themes/default/images/warning_medium.png">
	<strong>Please fix the following errors:</strong>
	<ul>
	<?php $_from = $this->_tpl_vars['form_data']['errors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['data']):
?>
	<li><?php echo $this->_tpl_vars['data']; ?>
</li>
	<?php endforeach; endif; unset($_from); ?>
	</ul>	
	</div>
<?php endif; ?>


<form <?php echo $this->_tpl_vars['form_data']['attributes']; ?>
>
<!-- Output hidden fields -->

<!-- Display the fields -->
<?php $_from = $this->_tpl_vars['element_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['title'] => $this->_tpl_vars['data']):
?>
	<h3><?php echo $this->_tpl_vars['title']; ?>
</h3>
	<div class="centrer">
	<table class="centrer">
	<?php $_from = $this->_tpl_vars['data']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['fieldname']):
?>
				<?php if ($this->_tpl_vars['form_data'][$this->_tpl_vars['fieldname']]['type'] == 'checkbox'): ?>
		<tr>
			<td colspan=2><?php echo $this->_tpl_vars['form_data'][$this->_tpl_vars['fieldname']]['html']; ?>
</td>
		</tr>
		<?php elseif ($this->_tpl_vars['form_data'][$this->_tpl_vars['fieldname']]['label']): ?>
		<tr>
			<td><?php echo $this->_tpl_vars['form_data'][$this->_tpl_vars['fieldname']]['label']; ?>
</td>
			<td><?php echo $this->_tpl_vars['form_data'][$this->_tpl_vars['fieldname']]['html']; ?>
</td>
		</tr>
		<?php elseif ($this->_tpl_vars['form_data'][$this->_tpl_vars['fieldname']]['type'] == 'hidden'): ?>
			<tr><td colspan=2><?php echo $this->_tpl_vars['form_data'][$this->_tpl_vars['fieldname']]['html']; ?>
</td></tr>
				<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>
	</table>
	</div>
<?php endforeach; endif; unset($_from); ?>
<div class="submit">

<?php echo $this->_tpl_vars['form_data']['submit']['html']; ?>

</div>
</form> 