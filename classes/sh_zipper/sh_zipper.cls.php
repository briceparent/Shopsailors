<?php
/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')){header('location: directCallForbidden.php');}

/**
 * Class manages the zip files (using lbphp-pclzip)
 */
class sh_zipper extends sh_core{
    public function construct(){
        require_once(dirname(__FILE__).'/libphp-pclzip/pclzip.lib.php');
    }

    public function extract($zipFile, $folder, $types = array('.*')){
        $typesReg = '('.implode('|',$types).')';
        // Unzips the archive
        $archive = new PclZip($zipFile);
        if(
            $archive->extract(
                PCLZIP_OPT_PATH, $folder,
                PCLZIP_OPT_BY_PREG, $typesReg
            ) == 0
        ){
            die("Error : ".$archive->errorInfo(true));
        }
    }

    public function __tostring(){
        return get_class();
    }
}
