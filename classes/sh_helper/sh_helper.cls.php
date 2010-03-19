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
 * Class that serves all other classes
 */
class sh_helper extends sh_core{
    public $events = null;
    protected $needsHelper = false;
    protected $baseConstructed = false;

    /**
     * Constructor
     */
    public function construct(){
        mb_internal_encoding("UTF-8");
        mb_http_input( "UTF-8" );
        mb_http_output( "UTF-8" );

        $this->linker->events->afterMinimalConstruction();
        return true;
    }

    /**
     * Lists all the pages that are present in the sitemap file, eventually
     * with a marker on a special page, and excepting the pages that are given
     * using special regular expression
     * @param str $startSelection The page to put the marker in.<br />
     * This marker adds a "checked" value for the state key of the page, and
     * sets "unfolder" to true for the category
     * @param array $exceptions An array of pages. The pages may use * to create
     * patterns.<br />
     * Ex : <br/>
     * array(
     *  'contact/show/', //(the contact page)
     *  'content/showShortList/*', //(every short list)
     *  'shop/* /*', //(without the space, for every page created by sh_shop)
     *  '* /specialMethod/*', //(without the space, any page created by a method
     * named specialMethod)
     * );
     * @return array The pages.<br />
     * Ex : <br />
     * $ret[0][name] => General<br />
     * $ret[0][description] => Général<br />
     * $ret[0][unfolded] => 1<br />
     * $ret[0][elements][0][name] => contact/show/<br />
     * $ret[0][elements][0][value] => Page de contact<br />
     * $ret[0][elements][0][address] => http://dev.websailors.fr/contact/show.php<br />
     * $ret[0][elements][0][state] => checked<br />
     * $ret[0][elements][1][name] => index/show/<br />
     * $ret[0][elements][1][value] => Page d'accueil<br />
     * $ret[0][elements][1][address] => http://dev.websailors.fr/index.php<br />
     * $ret[0][elements][1][state] => <br />
     * $ret[1][name] => content<br />
     * $ret[1][description] => Pages de contenus et listes<br />
     * $ret[1][elements][0][name] => content/show/10<br />
     * $ret[1][elements][0][value] => Article "/content/show/10-Bienvenue...php"<br />
     * $ret[1][elements][0][address] => http://dev.websailors.fr/content/show/10-Bienvenue...php<br />
     * $ret[1][elements][0][state] =><br />
     */
    public function listLinks($startSelection = '', $exceptions = array()){
        $addresses = $this->linker->sitemap->getSitemapPagesList();
        if(!empty($exceptions)){
            $reg = preg_replace(
                array(
                    '`(\*\/)`',
                    '`(\*)`',
                    '`(\/)`'
                ),
                array(
                    '[^/]+/',
                    '.*',
                    '\/'
                ),
                implode('$)|(^',$exceptions)
            );
            $reg = '`(^'.$reg.'$)`';
            $thereAreExceptions = true;
        }
        if(is_array($addresses['PAGES'])){
            foreach($addresses['PAGES'] as $page=>$address){
                if(!$thereAreExceptions || !preg_match($reg,$page)){
                    $state = '';
                    list($class,$action,$id) = explode('/',$page);
                    $checked = '';
                    if($startSelection == $page){
                        $state='checked';
                    }
                    $value = $this->linker->$class->getPageName($action, $id);
                    $elements[$class][$action.$id] = array(
                        'name' => $page,
                        'value' => $value,
                        'address'=> $address['address'],
                        'state'=>$state
                    );
                }
            }
        }
        $datas = array();
        $classId = 1;
        foreach($elements as $class=>$parts){
            if(count($parts) == 1){
                $class = 'general';
                $generalClass = true;
                $oldClassId = $classId;
                $classId = 0;
                $className = $this->getI18n('singleEntry_title');
            }else{
                $className = $this->linker->i18n->get($class,'className');
            }
            if($className == ''){
                $className = $class;
            }
            $datas[$classId]['name'] = $class;
            $datas[$classId]['description'] = $className;
            $partsId = 0;
            if(is_array($parts)){
                ksort($parts);
                foreach($parts as $part){
                    //$datas['links'][] = $part;
                    if($part['state'] == 'checked'){
                        //$datas['class_unfolded'] = $class;
                        $datas[$classId]['unfolded'] = true;
                    }
                    $datas[$classId]['elements'][] = $part;
                    $partsId++;
                }
            }
            if($generalClass){
                $classId = $oldClassId;
                $generalClass = false;
            }else{
                $classId++;
            }
        }
        return $datas;
    }

    public function isAdmin(){
        return parent::isAdmin();
    }

    /**
     * public function getRealClassName
     *
     */
    public function getRealClassName($class){
        if(class_exists(SH_PREFIX.$class)){
            return SH_PREFIX.$class;
        }elseif(class_exists($class)){
            return $class;
        }elseif(class_exists(SH_CUSTOM_PREFIX.$class)){
            return SH_CUSTOM_PREFIX.$class;
        }
        return false;
    }

   /**
     * Writes some text into a file.<br />
     * If the file already exists, it is replaced, unless $appends = true.
     * @param string $file contains the path and name of the file we want to create
     * @param string $content contains the text that we want to write into the file
     * @param boolean $append
     * If false (default), writes into a new file (deletes the old one if it already exists).<br />
     * If true, appends the text at the end of the document if it already exists, or creates it.
     * @return string This function resturns the return of fwrite
     */
    public static function writeInFile($file,$content,$append = false){
        $folder = dirname($file);
        if(!is_dir($folder)){
            mkdir($folder,0770,true);
        }
        if($append && file_exists($file)){
            $content = file_get_contents($file).$content;
        }
        if(file_exists($file)){
            unlink($file);
        }
        $f = fopen($file,'w+');
        //$ret = fwrite($f,"\xEF\xBB\xBF".$content);
        $ret = fwrite($f,$content);
        fclose($f);
        return $ret;
    }

    /**
     * Writes the contents of an array into a file so that it can be include()ed.<br />
     * If the file already exists, it is replaced.<br />
     * The file is generated using the function var_export.
     * Can only be called by this class and its dependencies
     * @param string $file contains the path and name of the file we want to create
     * @param string $name contains the name of the variable that will be generated into the file
     * @param array $array is the contents of the file
     * @return string This function resturns the return of writeInFile
     */
    public static function writeArrayInFile($file,$name,$array,$insertLicense = true){
        $content = '<?php'."\n";
        // inserts the copyrights from the file license_header.php, which is in
        // the same folder that this file.
        if($insertLicense){
            $content .= file_get_contents(dirname(__FILE__).'/license_header.php');
        }

        $content .= file_get_contents(dirname(__FILE__).'/generated_with_shopsailors.php');

        $content .= 'if(!defined(\'SH_MARKER\')){'."\n";
        $content .= '    header(\'location: directCallForbidden.php\');'."\n";
        $content .= '}'."\n\n";
        $content .= '$'.$name.' = ';
        $content .= var_export($array,true).';';
        return self::writeInFile($file, $content);
    }

    function createDir($path){
        if(is_dir($path)){
            return true;
        }
        return mkdir($path,0777,true);
    }

    /**
     * Deletes a directory, and all it's contents (recursively)
     * @param string $path The directory's path
     * @return boolean The status of the operation
     */
    function deleteDir($path){
        $rep = true;
        if(is_dir($path)){
            $dir = scandir($path);
            foreach($dir as $file){
                if($file != "." && $file != ".." ){
                    $fullpath= $path.'/'.$file;
                    if( is_dir($fullpath) ){
                        $rep = $rep && $this->deleteDir($fullpath);
                    }else{
                        $rep = $rep && unlink($fullpath);
                    }
                }
            }
            $rep = $rep && rmdir($path);
        }else{
            $rep = true;
        }
        return $rep;
    }

    /**
     * Deletes all the contents of a directory, but the hidden files
     * @param string $path The directory's path
     * @return boolean The status of the operation
     */
    function emptyDir($path){
        $rep = true;
        if(is_dir($path)){
            $dir = scandir($path);
            foreach($dir as $file){
                if(substr($file,0,1) != '.'){
                    $fullpath= $path.'/'.$file;
                    if( is_dir($fullpath) ){
                        $rep = $rep && $this->deleteDir($fullpath);
                    }else{
                        $rep = $rep && unlink($fullpath);
                    }
                }
            }
        }else{
            $rep = true;
        }
        return $rep;
    }

    /**
     * Moves all the contents of a directory into another one
     * @param string $from The directory's path from which to copy
     * @param string $to The directory's path to copy to
     * @return boolean The status of the operation
     */
    function moveDirContent($from,$to,$alsoHidden = false){
        $rep = true;
        if(is_dir($from)){
            $dir = scandir($from);
            foreach($dir as $file){
                if($file != '.' && $file != '..'){
                    if($alsoHidden || substr($file,0,1) != '.'){
                        $fromFullpath= $from.'/'.$file;
                        $toFullpath= $to.'/'.$file;
                        $rep = $rep && rename($fromFullpath,$toFullpath);
                    }
                }
            }
        }else{
            $rep = true;
        }
        return $rep;
    }

    /**
     * Simply changes a boolean "true" to "checked" or a "false" to an empty string
     * @param bool $condition The condition to evaluate (in fact, when here, it has already
     * been evaluated...)
     * @return str "checked" if $condition is true, "" if $condition is false
     */
    public function addChecked($condition){
        if($condition == 1){
            return 'checked';
        }
        return '';
    }


    /**
     * Method that does the same as array_merge_recursive, excepted that if
     * an integer key already exists in $original, it is replaced.
     * @param array $original The array of which we want to add some values
     * @param array $added An array containing the values we want to add to $original
     * @return array The elements that are present in $original, in which we have added
     * those of $added. The keys that were existing in both are now those of $added.
     */
    public function array_merge_recursive_replace($original, $added){
        $ret = $original;

        if(!is_array($original) && !is_array($added)){
            return false;
        }

        if(!is_array($original)){
            return $added;
        }
        if(!is_array($added)){
            return $original;
        }

        foreach($added as $key => $value){
            if(is_array($value) && is_array($original[$key])){
                // Both of them being array, we have do recurse
                $thisRet = $this->array_merge_recursive_replace($value,$original[$key]);
                $ret[$key] = $thisRet;
            }elseif(is_array($value) || $value != $original[$key]){
                // Adding the array/value
                $ret[$key] = $value;
            }
        }
        return $ret;
    }

    /**
     * This method makes as if the array_diff_assoc_recursive was a pre built method.
     * It works as the array_diff_assoc, but does it recursively.
     * @param array $original The array of which we want to remove some values
     * @param array $compared An array containing the values we want to remove from $original
     * @return array The elements that are present in $original, but missing in $compared
     */
    public function array_diff_assoc_recursive($original, $compared){
        $ret = array();

        if(!is_array($original)){
            // We can only make test on array
            return false;
        }
        if(!is_array($compared)){
            // We return the original, bacause nothing is the same...
            return $original;
        }
        foreach($original as $key => $value){
            if(is_array($value) && is_array($compared[$key])){
                // Both of them being array, we have do recurse
                $thisRet = $this->array_diff_assoc_recursive($value,$compared[$key]);
                if($thisRet !== false){
                    // Adding the return of the recursion
                    $ret[$key] = $thisRet;
                }
            }elseif(is_array($value) || $value != $compared[$key]){
                // Adding the array/value
                $ret[$key] = $value;
            }
        }
        if($ret == array()){
            return false;
        }
        return $ret;
    }

    public function __tostring(){
        return __CLASS__;
    }
}
