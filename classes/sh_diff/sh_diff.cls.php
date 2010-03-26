<?php
/**
 *
 * Original class :
 * Ross Scrivener http://scrivna.com
 * PHP file diff implementation
 * Much credit goes to...
 *
 * Paul's Simple Diff Algorithm v 0.1
 * (C) Paul Butler 2007 <http://www.paulbutler.org/>
 * May be used and distributed under the zlib/libpng license.
 *
 * ... for the actual diff code, i changed a few things and implemented a pretty interface to it.
 *
 *
 * This version (improved to fit the Shopsailors needs)
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license zlib/libpng (as the original)
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')){header('location: directCallForbidden.php');}

/**
 * Class that creates diff files and upgrading patches
 *
 * In every revision folders (named like the number of the revision) :
 * modifications.txt contains one line per modification/adding/deletion like this:
 * [+|-|*] [text|bin] [file path] [[file md5]]
 * [+|-|*] whether the file is added, deleted, or modified
 * [text|bin] whether the engines constructs diff files for it
 * [file path] the original file path (in the Shopsailors' directories)
 * [file md5] the md5 of the complete file in the current revision. Is not necessary for deletion
 *
 * In the "revision" folder :
 * The files are named this way :
 * [file name md5].txt where [file name md5] is the md5 from the complete path to the file
 * And contain a first line only containing the file path and name in the Shopsailors' directories
 * and one line per revision in which this file changed like this :
 * [revision number] [md5 sum of the file] [[Description of the change]]
 * where [md5 sum of the file] is the md5sum of the file in its revision [revision number]
 * [Description of the change] is the text that is given for this file's modification.
 * It is not mandatory to have one, and it may, of course, contain spaces.
 */
class sh_diff extends sh_core {
    protected $minimal = array('showChanges'=>true);
    protected $changes = array();
    protected $diff = array();
    protected $linepadding = null;
    protected $extensions = array(
        'php',
        'xml',
        'html',
        'htm',
        'css',
        'js',
        'txt',
        'htaccess'
    );
    protected $images = array(
        'png',
        'jpg',
        'jpeg',
        'tif',
        'tiff',
        'bmp',
        'gif'
    );
    protected $flash = array(
        'flv',
        'swf'
    );
    protected $videos = array(
        'avi',
        'divx',
        'mov',
        'mpeg',
        'mp4',
        'wmv'
    );
    protected $audio = array(
        'mp3',
        'aac',
        'ac3',
        'ogg',
        'wma'
    );
    protected $folders = array(
        SH_CLASS_FOLDER,
        SH_TEMPLATE_FOLDER
    );

    public function construct(){
        // On big files, creating a diff may consume more memory than other treatments.
        ini_set("memory_limit","128M");
    }

    /**
     * Checks if the file is known as a text file (on which we can apply patches).
     * The checking is done on the extension, and not on the file's headers.
     * @param str $fileName The file name
     * @return bool True if it is a text file, false otherwise.
     */
    protected function isTextFile($fileName, $asBoolean = true){
        if($asBoolean){
            return in_array(
                array_pop( // the file's extension
                    explode('.',$fileName)
                ),
                $this->extensions // the text extensions
            );
        }
        if($this->isTextFile($fileName)){
            return true;
        }
        return array_pop( // the file's extension
            explode('.',$fileName)
        );
    }

    public function showChanges($project = null, $file = null, $from = null, $to = null){
        $this->onlyMaster();
        $dev = $this->linker->dev;
        if(is_null($project)){
            list($project,$file) = explode('/',$_GET['file'],2);
        }
        if(file_exists(SH_DEV_PATH.$project.'/revisions/'.$file.'.php')){
            $this->onlyDiff = false;
            $lines = file(
                SH_DEV_PATH.$project.'/revisions/'.$file.'.php',
                FILE_IGNORE_NEW_LINES
            );
            $filename = array_shift($lines);
            $values['titles']['h1'] = $filename;

            $textFile = $this->isTextFile($filename);

            if(!is_null($from)){
                
            }else{
                if(count($lines) == 1){
                    list($lastRevision,$md5) = explode(' ',$lines[0]);
                    if($textFile){
                        $ext = '.php';
                    }else{
                        $ext = '.bin';
                    }
                    $old = SH_DEV_PATH.$project.'/'.$lastRevision.'/'.$file.$ext;
                }else{
                    if($textFile){
                        list($firstRevision,$md5) = explode(' ',array_shift($lines));
                        list($lastRevision,$md5) = explode(' ',array_pop($lines));
                        $ext = '.php';

                        $old = $this->applyPatch(
                            SH_DEV_PATH.$project.'/'.$firstRevision.'/'.$file.$ext,
                            SH_DEV_PATH.$project.'/'.$lastRevision.'/'.$file.'.diff',
                            true
                        );
                    }else{
                        $old = SH_DEV_PATH.$project.'/'.$lastRevision.'/'.$file.'.bin';
                        $new = SH_ROOT_FOLDER.$filename;
                        echo $this->renderBinDiff($old,$new,false);
                        return true;
                    }
                }
                $values['titles']['h3'] = 'Différences dans le fichier depuis la révision '.$lastRevision;
            }

            echo $this->renderDiff(
                $old,
                SH_ROOT_FOLDER.$filename,
                false,
                4,
                $values
            );
        }else{
            echo SH_DEV_PATH.$project.'/revisions/'.$file.'.php';
        }
    }

    public function renderDiff($old,$new,$direct = true,$onlyDiff = false,$values = array()){
        $this->onlyDiff = $onlyDiff;
        // If $old and $new are not arrays, it means they are files, so we get their contents
        if(!is_array($old)){
            $old = file($old);
        }else{
            $old = array_values($old);
        }
        if(!is_array($new)){
            $new = file($new);
        }else{
            $new = array_values($new);
        }
        $values['lines'] = $this->inline($old,$new);
        return $this->render('showDiff', $values, false, $direct);
    }

    public function createPatch($old,$new,$patchFile,$onlyDiff = true){
        $this->onlyDiff = $onlyDiff;
        $this->inFile = true;
        if(!is_array($old)){
            $old = file($old);
        }
        if(!is_array($new)){
            $new = file($new);
        }
        $values = $this->inline($old,$new);
        $lines[0] = 'Shopsailors\' patch file'."\n";
        
        if(is_array($values)){
            $lastOld = 0;
            foreach($values as $value){
                if(isset($value['old']) && !isset($value['new'])){
                    $lines[] = '-l'.$this->pad($value['old']).' '.$value['content'];
                    $lastOld = $value['old'];
                    $cpt = 0;
                }elseif(isset($value['new']) && !isset($value['noChange'])){
                    $lines[] = '+l'.$this->pad($value['old'])
                        .'.'
                        .$this->pad($cpt++)
                        .' '
                        .$value['content'];
                }
                if(!$this->onlyDiff && isset($value['new']) && isset($value['old'])){
                    $lines[] = 'l'.$this->pad($lastOld)
                        .'.'
                        .$this->pad($cpt++)
                        .' '
                        .$value['content'];
                }
            }
        }

        $this->linker->helper->writeInFile(
            $patchFile,
            implode("",$lines)
        );
    }
    
    protected function pad($number){
        return str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    public function applyPatch($old,$patchFile,$newFile){
        $this->onlyDiff = true;
        $this->inFile = true;
        if(!is_array($old)){
            $old = file($old);
        }
        if(!is_array($patchFile)){
            $patchEntries = file($patchFile);
        }
        if($newFile === true){
            $this->inFile = false;
            $returnResult = true;
        }

        foreach($old as $line=>$content){
            $new['l'.str_pad($line+1, 5, '0', STR_PAD_LEFT)] = $content;
        }
        // We remove the first line which is not a diff entry
        array_shift($patchEntries);
        foreach($patchEntries as $key=>$patchEntry) {
            list($line,$content) = explode(' ',$patchEntry,2);
            if(substr($line,0,1) == '+'){
                $new[substr($line,1)] = $content;
            }elseif(substr($line,0,1) == '-'){
                unset($new[substr($line,1)]);
            }
        }
        ksort($new);

        if($returnResult){
            return $new;
        }

        $this->linker->helper->writeInFile(
            $newFile,
            implode("",$new)
        );
    }

    public function applyPatches($old,$patchFiles,$newFile){
        foreach($patchFile as $patchFile){
            $newFile = $this->applyPatches($old,$patchFile,$newFile);
        }
        return $newFile;
    }

    protected function makeDiff($old,$new){
        // We loop on the old entries
        foreach($old as $oldIndex => $oldValue){
            // We get the keys that contain $oldValue
            $newKeys = array_keys($new, $oldValue);
            // We loop on them
            foreach($newKeys as $newIndex){
                if(isset($matrix[$oldIndex - 1][$newIndex - 1]) ){
                    $matrix[$oldIndex][$newIndex] = $matrix[$oldIndex - 1][$newIndex - 1] + 1;
                }else{
                    $matrix[$oldIndex][$newIndex] = 1;
                }
                if($matrix[$oldIndex][$newIndex] > $maxlen){
                    $maxlen = $matrix[$oldIndex][$newIndex];
                    $oldMax = $oldIndex + 1 - $maxlen;
                    $newMax = $newIndex + 1 - $maxlen;
                }
            }
        }

        if($maxlen == 0){
            return array(array('delete'=>$old, 'insert'=>$new));
        }

        return array_merge(
            $this->makeDiff(
                array_slice($old, 0, $oldMax),
                array_slice($new, 0, $newMax)
            ),
            array_slice($new, $newMax, $maxlen),
            $this->makeDiff(
                array_slice($old, $oldMax + $maxlen),
                array_slice($new, $newMax + $maxlen)
            )
        );

    }

    function diffWrap($old, $new){
        $this->diff = $this->makeDiff($old, $new);
        $this->changes = array();
        $ndiff = array();
        foreach ($this->diff as $line => $k){
            if(is_array($k)){
                if(isset($k['delete'][0]) || isset($k['insert'][0])){
                    $this->changes[] = $line;
                    $ndiff[$line] = $k;
                }
            } else {
                $ndiff[$line] = $k;
            }
        }
        $this->diff = $ndiff;
        return $this->diff;
    }

    function formatcode($code){
        if(!$this->inFile){
            // Replaced htmlentities to preserve from errors due to dom and the "&" character
            $code = htmlspecialchars($code);
            $code = str_replace(' ','&#160;',$code);
            $code = str_replace("\t",'&#160;&#160;&#160;&#160;',$code);
        }
        return $code;
    }

    function showline($line){
        if($this->linepadding === 0){
            if(in_array($line,$this->changes)) return true;
            return false;
        }
        if(is_null($this->linepadding)){
            return true;
        }

        if(($line - $this->linepadding) > 0){
            $start = $line - $this->linepadding;
        }else{
            $start = 0;
        }
        $end = ($line + $this->linepadding);
        $search = range($start,$end);
        foreach($search as $k){
            if(in_array($k,$this->changes)){
                return true;
            }
        }
        return false;
    }

    function inline($old, $new, $linepadding=null){
        $this->linepadding = $linepadding;

        $count_old = 1;
        $count_new = 1;

        $insert = false;
        $delete = false;
        $truncate = false;

        $diff = $this->diffWrap($old, $new);

        foreach($diff as $line => $k){
            if($this->showline($line)){
                $truncate = false;
                if(is_array($k)){
                    if(is_array($previousLines)){
                        if($cut){
                            $lines[] =  array(
                                'content' => '...'
                            );
                            $cut = false;
                        }
                        while(!empty($previousLines)){
                            $lines[] = array_shift($previousLines);
                        }
                    }
                    // Counter to know how many line have been shown after
                    $diffLineCreated = $this->onlyDiff;
                    foreach ($k['delete'] as $val){
                        $class = '';
                        if(!$delete){
                            $delete = true;
                            $class = 'first';
                            if($insert){
                                $class = '';
                            }
                            $insert = false;
                        }
                        $lines[] = array(
                            'old' => $count_old,
                            'class' => 'del '.$class,
                            'content' => $this->formatcode($val)
                        );
                        $count_old++;
                    }
                    foreach ($k['insert'] as $val){
                        $class = '';
                        if(!$insert){
                            $insert = true;
                            $class = 'first';
                            if($delete){
                                $class = '';
                            }
                            $delete = false;
                        }
                        $lines[] = array(
                            'new' => $count_new,
                            'old' => ($count_old - 1),
                            'class' => 'ins '.$class,
                            'content' => $this->formatcode($val)
                        );
                        $count_new++;
                    }
                }else{
                    $class = '';
                    if($delete){
                        $class = 'del_end';
                    }
                    if($insert){
                        $class = 'ins_end';
                    }
                    $delete = false;
                    $insert = false;
                    if($this->onlyDiff === false || ($this->onlyDiff !== true && $diffLineCreated > 0)){
                        $lines[] = array(
                            'old' => $count_old,
                            'new' => $count_new,
                            'noChange' => true,
                            'class' => $class,
                            'content' => $this->formatcode($k)
                        );
                        $diffLineCreated--;
                    }elseif($this->onlyDiff !== true && $this->onlyDiff>0){
                        if(count($previousLines) == $this->onlyDiff){
                            array_shift($previousLines);
                            $cut = true;
                        }
                        $previousLines[] = array(
                            'old' => $count_old,
                            'new' => $count_new,
                            'class' => $class,
                            'content' => $this->formatcode($k)
                        );
                    }
                    $linesHaveBeenShawn = false;
                    $count_old++;
                    $count_new++;
                }
            }else{
                $class = '';
                if($delete){
                    $class = 'del_end';
                }
                if($insert){
                    $class = 'ins_end';
                }
                $delete = false;
                $insert = false;

                if(!$truncate){
                    $truncate = true;
                    $lines[] = array(
                        'old' => '...',
                        'new' => '...',
                        'class' => $class,
                        'content' => '&#160;'
                    );
                }
                $count_old++;
                $count_new++;
            }
        }
        if(is_array($previousLines)){
            if($cut){
                $lines[] =  array(
                    'content' => '...'
                );
                $cut = false;
            }
            while(!empty($previousLines)){
                $lines[] = array_shift($previousLines);
            }
        }
        return $lines;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        list($class,$method,$id) = explode('/',$page);
        if($method == 'create'){
            $uri = '/'.$this->shortClassName.'/create.php';
            return $uri;
        }
        if($method == 'showChanges'){
            $uri = '/'.$this->shortClassName.'/showChanges.php';
            return $uri;
        }
        if($method == 'prepareCommit'){
            $uri = '/'.$this->shortClassName.'/prepareCommit.php';
            return $uri;
        }

        return parent::translatePageToUri($page);
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        if($uri == '/'.$this->shortClassName.'/create.php'){
            $page = $this->shortClassName.'/create/';
            return $page;
        }
        if($uri == '/'.$this->shortClassName.'/showChanges.php'){
            $page = $this->shortClassName.'/showChanges/';
            return $page;
        }
        if($uri == '/'.$this->shortClassName.'/prepareCommit.php'){
            $page = $this->shortClassName.'/prepareCommit/';
            return $page;
        }

        return parent::translateUriToPage($uri);
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring(){
        return get_class();
    }
}
