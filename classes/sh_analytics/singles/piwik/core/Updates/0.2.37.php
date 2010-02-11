<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: 0.2.37.php 1433 2009-08-23 15:29:12Z vipsoft $
 *
 * @category Piwik
 * @package Updates
 */

/**
 * @package Updates
 */
class Piwik_Updates_0_2_37 implements Piwik_iUpdate
{
	static function update()
	{
		Piwik_Updater::updateDatabase(__FILE__, array(
			'DELETE FROM `'.  Piwik::prefixTable('user_dashboard') ."`
				WHERE layout LIKE '%.getLastVisitsGraph%'
				OR layout LIKE '%.getLastVisitsReturningGraph%'" => false,
		));
	}
}
