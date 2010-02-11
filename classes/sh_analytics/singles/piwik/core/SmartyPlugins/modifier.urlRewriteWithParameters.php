<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: modifier.urlRewriteWithParameters.php 1420 2009-08-22 13:23:16Z vipsoft $
 * 
 * @category Piwik
 * @package SmartyPlugins
 */

/**
 * Rewrites the given URL and modify the given parameters.
 * @see Piwik_Url::getCurrentQueryStringWithParametersModified()
 * 
 * @return string
 */
function smarty_modifier_urlRewriteWithParameters($parameters)
{
	$url = Piwik_Url::getCurrentQueryStringWithParametersModified($parameters);
	return htmlspecialchars($url);
}
