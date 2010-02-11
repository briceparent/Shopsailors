<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Controller.php 1420 2009-08-22 13:23:16Z vipsoft $
 * 
 * @category Piwik_Plugins
 * @package Piwik_CoreAdminHome
 */

/**
 *
 * @package Piwik_CoreAdminHome
 */
class Piwik_CoreAdminHome_Controller extends Piwik_Controller
{
	function getDefaultAction()
	{
		return 'redirectToIndex';
	}
	
	function redirectToIndex()
	{
		if(Piwik::isUserIsSuperUser()) 
		{
			$module = 'CorePluginsAdmin';
		} 
		else 
		{
			$module = 'SitesManager';
		}
		header("Location:index.php?module=" . $module);
	}

	public function index()
	{
		Piwik::checkUserIsSuperUser();
		$view = $this->getDefaultIndexView();
		echo $view->render();
	}
	
	protected function getDefaultIndexView()
	{
		$view = Piwik_View::factory('index');
		$view->content = '';
		$this->setGeneralVariablesView($view);
		$view->menu = Piwik_GetAdminMenu();
		return $view;
	}
}
