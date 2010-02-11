<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: 0.4.4.php 1620 2009-12-02 19:41:36Z vipsoft $
 *
 * @category Piwik
 * @package Updates
 */

/**
 * @package Updates
 */
class Piwik_Updates_0_4_4 implements Piwik_iUpdate
{
	static function update()
	{
		$obsoleteFile = PIWIK_DOCUMENT_ROOT . '/libs/open-flash-chart/php-ofc-library/ofc_upload_image.php';
		if(file_exists($obsoleteFile))
		{
			$rc = @unlink($obsoleteFile);
			if(!$rc)
			{
				throw new Piwik_Updater_UpdateErrorException("Unable to delete $obsoleteFile");
			}
		}
	}
}
