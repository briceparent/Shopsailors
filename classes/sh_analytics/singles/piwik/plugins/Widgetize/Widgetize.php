<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Widgetize.php 1665 2009-12-11 21:25:57Z vipsoft $
 * 
 * @category Piwik_Plugins
 * @package Piwik_Widgetize
 */

/**
 * 
 * @package Piwik_Widgetize
 */
class Piwik_Widgetize extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'name' => 'Widgetize your data!',
			'description' => 'The plugin makes it very easy to export any Piwik Widget in your Blog, Website or on Igoogle and Netvibes!',
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
	}
}
