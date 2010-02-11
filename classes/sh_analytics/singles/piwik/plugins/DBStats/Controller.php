<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Controller.php 1420 2009-08-22 13:23:16Z vipsoft $
 * 
 * @category Piwik_Plugins
 * @package Piwik_DBStats
 */

/**
 *
 * @package Piwik_DBStats
 */
class Piwik_DBStats_Controller extends Piwik_Controller
{
	function index()
	{
		$view = Piwik_View::factory('DBStats');
		$view->tablesStatus = Piwik_DBStats_API::getAllTablesStatus();
		$this->setGeneralVariablesView($view);
		$view->menu = Piwik_GetAdminMenu();
		echo $view->render();		
	}
}
