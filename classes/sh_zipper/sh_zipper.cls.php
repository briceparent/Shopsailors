<?php
/**
 * @author Brice PARENT (Websailors) for Shopsailors
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
    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker','sh_params','sh_db'
    );
    const SYMLINK_FILENAME = 'shopsailors_symlinks.txt';

    public function construct(){
        $installedVersion = $this->getClassInstalledVersion();
        if($installedVersion != self::CLASS_VERSION){
            // The class datas are not in the same version as this file, or don't exist (installation)
            $this->setClassInstalledVersion(self::CLASS_VERSION);
        }
        require_once(dirname(__FILE__).'/libphp-pclzip/pclzip.lib.php');
    }

    /**
     * Extracts the zip file $zipFile into the folder $folder. Some validation on content types may be given in $types.
     * @param str $zipFile The path to the zip file
     * @param str $folder The path of the folder to extract the zip to
     * @param str|array $types A string or an array containing either :
     * <ul><li>"*" (default behaviour) to extract any kind of contents</li>
     * <li>An array of the extensions that are allowed to be axtracted</li>
     * </ul>
     * @return ZipArchive The archive object
     */
    public function extract($zipFile, $folder, $types = '*'){
        if($types == '*' || $types == array('*') || empty($types)){
            $allTypes = true;
        }else{
            $allTypes = false;
        }
        $symlinksFiles = array();
        $zip = new ZipArchive;;
        if ( $zip->open( $zipFile ) ){
            for ( $i=0; $i < $zip->numFiles; $i++ ){
                $entry = $zip->getNameIndex($i);
                if(substr($entry,-1) == '/'){
                    $isFolder = true;
                }else{
                    $ext = array_pop(explode('.',$entry));
                    $isFolder = false;
                }
                if($allTypes || $isFolder || in_array($ext,$types)){
                    // We verify if the file is a symlink
                    $fileName = basename($entry);
                    if($fileName == self::SYMLINK_FILENAME){
                        // after the dezipping process, we will add the symlinks
                        $symlinksFiles[] = $entry;
                    }
                    $zip->extractTo($folder,$entry);
                }
            }
            foreach($symlinksFiles as $symlinksFile){
                $symlinks = file($folder.'/'.$symlinksFile);
                foreach($symlinks as $symlink){
                    list($source,$destination) = explode(' ',$symlink,2);
                    $this->helper->create_symLink(
                        $source,
                        realPath(dirname($source).'/'.$destination)
                    );
                }
                //echo 'On a un symlink de '.$source.' vers '.$destination.'<br />';
            }
            $zip->close();
        }else{
            return false;
        }

        return $zip;
    }

    public function compress($zipFile,$element){
        $zip = new ZipArchive;

        if ($zip->open($zipFile, ZIPARCHIVE::OVERWRITE) === true) {
            if(is_dir($element)){
                // We zip a directory
                $this->rootDir = str_replace('//','/',$element.'/');
                $this->addDir($zip,$element);
            }else{
                // We zip a single file
                $zip->addFile($element, basename($element));
            }
            $zip->close();
            return true;
        }
        return false;
    }

    protected function addDir($zip,$path) {
        $localPath = str_replace($this->rootDir,'/',$localPath);
        $zip->addEmptyDir($localPath);
        $nodes = glob($path . '/*');
        $symlinks = array();
        foreach ($nodes as $node) {
            $localNode = str_replace($this->rootDir,'/',$node);
            if(is_link($node)){
                $symlinks[] = array(
                    'src'=>$node,
                    'dest'=>readlink($node)
                );
            }elseif(is_dir($node)) {
                $this->addDir($zip,$node);
            }elseif(is_file($node))  {
                $zip->addFile($node,$localNode);
            }
        }
        if(!empty($symlinks)){
            $symLinksFileContent = '';
            foreach($symlinks as $symlink){
                $symLinksFileContent .= $symlink['src'].' '.$symlink['dest']."\n";
            }
            $zip->addFromString(
                dirname($localNode).'/'.self::SYMLINK_FILENAME,
                $symLinksFileContent
            );
        }
    } 
    
    public function __tostring(){
        return get_class();
    }
}
