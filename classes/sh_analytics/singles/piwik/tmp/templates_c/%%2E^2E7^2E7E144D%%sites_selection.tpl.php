<?php /* Smarty version 2.6.25, created on 2010-01-19 11:35:30
         compiled from CoreHome/templates/sites_selection.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'CoreHome/templates/sites_selection.tpl', 2, false),array('function', 'url', 'CoreHome/templates/sites_selection.tpl', 4, false),array('function', 'hiddenurl', 'CoreHome/templates/sites_selection.tpl', 10, false),)), $this); ?>
<span id="sitesSelectionWrapper" style="display:none;" >
	<?php echo ((is_array($_tmp='General_Website')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
 <span id="selectedSiteName" style="display:none"><?php echo $this->_tpl_vars['siteName']; ?>
</span>
	<span id="sitesSelection" style="position:absolute">Site 
		<form action="<?php echo smarty_function_url(array('idSite' => null), $this);?>
" method="get">
		<select name="idSite">
		   <?php $_from = $this->_tpl_vars['sites']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['info']):
?>
		   		<option value="<?php echo $this->_tpl_vars['info']['idsite']; ?>
" <?php if ($this->_tpl_vars['idSite'] == $this->_tpl_vars['info']['idsite']): ?> selected="selected"<?php endif; ?>><?php echo $this->_tpl_vars['info']['name']; ?>
</option>
		   <?php endforeach; endif; unset($_from); ?>
		</select>
		<?php echo smarty_function_hiddenurl(array('idSite' => null), $this);?>

		<input type="submit" value="go"/>
		</form>
	</span>

	<?php echo '<script language="javascript">
	$(document).ready(function() {
		var extraPadding = 0;
		// if there is only one website, we dont show the arrows image, so no need to add the extra padding
		if( $(\'#sitesSelection\').find(\'option\').size() > 1) {
			extraPadding = 21;
		}
		$("#sitesSelectionWrapper").show();
		var widthSitesSelection = $("#selectedSiteName").width() + 4 + extraPadding;
		$("#sitesSelectionWrapper").css(\'padding-right\', widthSitesSelection);
		$("#sitesSelection").fdd2div({CssClassName:"formDiv"});

		// this will put the anchor after the url before proceed to different site.
		$("#sitesSelection ul li").bind(\'click\',function (e) {
			e.preventDefault();               
			var request_URL = $(e.target).attr("href");
		        var new_idSite = broadcast.getValueFromUrl(\'idSite\',request_URL);
		        broadcast.propagateNewPage( \'idSite=\'+new_idSite );
		});
	});</script>
	'; ?>

</span>