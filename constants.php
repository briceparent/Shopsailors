<?php
// Taken from the php documentation at php.net
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300) {
    die(
        'The minimum php version required to use Shopsailors is PHP5.3.<br />
        You need to update your version if you want to use Shopsailors.'
    );
}

// We use FOLDER for global pathes, from /var/www/...
// and PATH for internet pathes, like everything after the domain name

/** Marker that enables to verify if the site was really built.<br />
 * Only works with files that include this one.
 */
define('SH_MARKER',true);
/** Prefix for the long class names */
define('SH_PREFIX','sh_');
define('SH_CUSTOM_PREFIX','cm_'); /* If you change this, make sure you update the .htaccess the same way */

//----------------------------- MAIN FOLDERS ---------------------------------\\

/**
 * Constant for the root
 */
define('SH_ROOT_FOLDER',dirname(__FILE__).'/');
/**
 * Constant for the root - Without the path to shopsailors
 */
define('SH_ROOT_PATH','/');

/**
 * Constant for the shared and custom classes folders
 */
define('SH_CLASS_FOLDER',SH_ROOT_FOLDER.'classes/');
define('SH_CUSTOM_CLASS_FOLDER',SH_CLASS_FOLDER.'custom/'); /* If you change this, make sure you update the .htaccess the same way */
/**
 * Constant for the shared and custom classes folders - Without the path to shopsailors
 */
define('SH_CLASS_PATH',SH_ROOT_PATH.'classes/');
define('SH_CUSTOM_CLASS_PATH',SH_CLASS_PATH.'custom/');

/**
 * Constant for the include/ folder
 */
define('SH_INCLUDE_FOLDER',SH_ROOT_FOLDER.'include/');
/**
 * Constant for the include/ folder - Without the path to shopsailors
 */
define('SH_INCLUDE_PATH',SH_ROOT_PATH.'include/');

/**
 * Constants for the shared and custom templates' folders
 */
define('SH_TEMPLATE_FOLDER',SH_ROOT_FOLDER.'templates/');
define('SH_CUSTOM_TEMPLATE_FOLDER',SH_TEMPLATE_FOLDER);
/**
 * Constant for the shared and custom templates' folders - Without the path to shopsailors
 */
define('SH_TEMPLATE_PATH',SH_ROOT_PATH.'templates/');
define('SH_CUSTOM_TEMPLATE_PATH',SH_TEMPLATE_PATH);

/**
 * Constant for the site/ folder
 */
define('SH_SITES_FOLDER',SH_ROOT_FOLDER.'sites/');
/**
 * Constant for the site/ folder - Without the path to shopsailors
 */
define('SH_SITES_PATH',SH_ROOT_PATH.'sites/');

/**
 * Constant for the site/[site_name]/ folder
 */
define('SH_SITE_FOLDER',SH_SITES_FOLDER.SH_SITE);
/**
 * Constant for the site/[site_name]/ folder - Without the path to shopsailors
 */
define('SH_SITE_PATH',SH_SITES_PATH.SH_SITE);

/**
 * Constant for the user's sh_params/ folder
 */
define('SH_SITEPARAMS_FOLDER',SH_SITE_FOLDER.'sh_params/');

/**
 * Constant for the cache/[site]/ folder
 */
define('SH_CACHE_FOLDER',SH_ROOT_FOLDER.'cache/'.SH_SITE);

/**
 * Constant for the fonts/ folder (into the templates/ folder)
 */
define('SH_FONTS_FOLDER',SH_TEMPLATE_FOLDER.'fonts/');
/**
 * Constant for the fonts/ folder (into the templates/ folder) - Without the path to shopsailors
 */
define('SH_FONTS_PATH',SH_TEMPLATE_PATH.'fonts/');

/**
 * Constant for the temp/ folder
 */
define('SH_TEMP_FOLDER',SH_ROOT_FOLDER.'temporary/');

//------------------------------- IMAGES -------------------------------------\\

/**
 * Constant for the shared images folder.<br />
 * Shared images are seen as if they were in /images/shared, but are in
 * /images/ instead.
 */
define('SH_SHAREDIMAGES_FOLDER',SH_ROOT_FOLDER.'images/');
/**
 * Constant for the shared images folder - Without the path to shopsailors.<br />
 * Shared images are seen as if they were in /images/shared, but are in
 * /images/ instead.
 */
define('SH_SHAREDIMAGES_PATH',SH_ROOT_PATH.'images/shared/');

/**
 * Constant for the user's images folder.<br />
 * The user's images are seen to be in the /images/site/ folder, but are in
 * SH_SITE_FOLDER/sh_images/ instead.
 */
define('SH_IMAGES_FOLDER',SH_SITE_FOLDER.'sh_images/');
/**
 * Constant for the user's images folder - Without the path to shopsailors.<br />
 * The user's images are seen to be in the /images/site/ folder, but are in
 * SH_SITE_FOLDER/sh_images/ instead.
 */
define('SH_IMAGES_PATH',SH_ROOT_PATH.'images/site/');

/**
 * Constant for the user's logo files.
 */
define('SH_LOGO_FOLDER',SH_IMAGES_FOLDER.'logo/');
/**
 * Constant for the user's logo files - Without the path to shopsailors.
 */
define('SH_LOGO_PATH',SH_IMAGES_PATH.'logo/');


/**
 * Path translation constant to /images/shared/
 */
define('SH_IMAGES_SHARED','/images/shared/');
/**
 * Path translation constant to /images/site/
 */
define('SH_IMAGES_SITE','/images/site/');
/**
 * Path translation constant to /images/template/global/
 */
define('SH_IMAGES_TEMPLATE_GLOBAL','/images/template/global/');
/**
 * Path translation constant to /images/template/variation/
 */
define('SH_IMAGES_TEMPLATE_VARIATION','/images/template/variation/');

/**
 * Constant for the generated/ folder (images that were generated automatically)
 */
define('SH_GENERATEDIMAGES_FOLDER',SH_IMAGES_FOLDER.'generated/');
/**
 * Constant for the generated/ folder (images that were generated automatically)
 *  - Without the path to shopsailors
 */
define('SH_GENERATEDIMAGES_PATH',SH_IMAGES_PATH.'generated/');

/**
 * Constant for the folder in which are put the temporary images
 */
define('SH_TEMPIMAGES_FOLDER',SH_IMAGES_FOLDER.'temp/');
/**
 * Constant for the folder in which are put the temporary images
 */
define('SH_TEMPIMAGES_PATH','/images/temp/');
