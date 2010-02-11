<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: iUpdate.php 1432 2009-08-23 15:13:40Z vipsoft $
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Interface to be implemented by update scripts
 *
 * @example core/Updates/0.4.2.php
 * @package Piwik
 */
interface Piwik_iUpdate
{
	/**
	 * Incremental version update
	 */
	static function update();
}
