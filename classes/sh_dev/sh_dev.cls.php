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
 * Class that manages some help to the developpers, like for debugging for example.
 */
class sh_dev extends sh_core{
    protected $admin = false;
    protected $master = false;
    protected $elements = array();
    /**
     * All the extensions that are used as text files (on which diff may be applied)
     * @var array
     */
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

    // Actions that happened to files
    const FILE_NEW = 'new';
    const FILE_DELETED = 'deleted';
    const FILE_NOTCHANGED = 'identical';
    const FILE_MODIFIED = 'modified';
    const FILE_INEXISTANT = 'inexistant';
    // And their icons
    const IMAGE_FILE_NEW = '/images/shared/icons/bank1/picto_add.png';
    const IMAGE_FILE_DELETED = '/images/shared/icons/bank1/picto_trash.png';
    const IMAGE_FILE_NOTCHANGED = '/images/shared/icons/bank1/picto_equal.png';
    const IMAGE_FILE_MODIFIED = '/images/shared/icons/bank1/picto_modify.png';
    const IMAGE_FILE_INEXISTANT = '/images/shared/icons/bank1/picto_delete.png';

    public function construct(){
        $this->master = $this->isMaster();
        define('SH_DEV_PATH',SH_ROOT_FOLDER.'../dev/');
        define('SH_DEVPARAMS_PATH',SH_CLASS_SHARED_FOLDER.__CLASS__.'/projects/');
        $this->renderer_addConstants(
            array(
                'FILE_NEW' => self::FILE_NEW,
                'FILE_DELETED' => self::FILE_DELETED,
                'FILE_NOTCHANGED' => self::FILE_NOTCHANGED,
                'FILE_MODIFIED' => self::FILE_MODIFIED,
                'FILE_INEXISTANT' => self::FILE_INEXISTANT,
                'IMAGE_FILE_NEW' => self::IMAGE_FILE_NEW,
                'IMAGE_FILE_DELETED' => self::IMAGE_FILE_DELETED,
                'IMAGE_FILE_NOTCHANGED' => self::IMAGE_FILE_NOTCHANGED,
                'IMAGE_FILE_MODIFIED' => self::IMAGE_FILE_MODIFIED,
                'IMAGE_FILE_INEXISTANT' => self::IMAGE_FILE_INEXISTANT
            )
        );
        return true;
    }

    public function showCheckList(){
        $this->onlyMaster();
        if(isset($_GET['type']) && $_GET['type'] == 'general'){
            $classes = $this->getClassesFromSharedFolder('checklist');
            
            if(is_array($classes)){
                foreach($classes as $class){
                    $general = array();
                    include($class['file']);
                    if(is_array($general)){
                        foreach($general as $entry){
                            if(trim($entry) != ''){
                                $values['general'][] = array(
                                    'name'=>$entry,
                                    'cb'=>true
                                );
                            }else{
                                $values['general'][] = array(
                                    'name'=>'&#160;'
                                );
                            }
                        }
                    }
                }
            }
            header('Content-disposition: attachment; filename="checklist_general.htm"');
            header('Content-Type: application/force-download');
            header('Content-Transfer-Encoding: binary');
            header('Pragma: no-cache');
            header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');
            echo $this->render('showCheckList_general',$values,false,false);
            exit;
        }elseif(isset($_GET['type']) && $_GET['type'] == 'templates'){
            $values['templatesList'] = $this->links->templatesLister->getList(true);
            $classes = $this->getClassesFromSharedFolder('checklist');

            if(is_array($classes)){
                $values['templates'] = array();
                foreach($classes as $class){
                    $general = array();
                    $templates = array();
                    include($class['file']);
                    if(is_array($general)){
                        foreach($general as $entry){
                            if(trim($entry) != ''){
                                $values['templates'][] = array(
                                    'name'=>$entry,
                                    'cb'=>true
                                );
                            }else{
                                $values['templates'][] = array(
                                    'name'=>'&#160;'
                                );
                            }
                        }
                    }
                    if(is_array($templates)){
                        $values['templates'] = array_merge($values['templates'],$templates);
                    }
                }
            }
        }

        $this->render('showCheckList', $values);
    }
    

    public function showFileHistory(){
        $this->links->cache->disable();
        $this->links->html->setTitle('File history');
        if(isset($_GET['project'])){
            if(isset($_GET['file'])){
                $projectName = $_GET['project'];
                $md5Name = $_GET['file'];
                $file = $this->getFileName($projectName, $md5Name);
                $fileEvents = file(
                    SH_DEV_PATH.$projectName.'/revisions/'.$md5Name.'.php'
                );
                $fileName = array_shift($fileEvents);
                $first = true;
                foreach($fileEvents as $event){
                    list($revision,$md5,$reason) = explode(' ',$event,3);
                    if(file_exists(SH_DEV_PATH.$projectName.'/'.$revision.'/description.php')){
                        $description = trim(
                            stripslashes(
                                file_get_contents(
                                    SH_DEV_PATH.$projectName.'/'.$revision.'/description.php'
                                )
                            )
                        );
                    }
                    if($first){
                        if($description == ''){
                            $description = 'Insertion dans le projet';
                        }
                        $first = false;
                    }
                    $values['revisions'][] = array(
                        'number'=>$revision,
                        'reason'=>$reason,
                        'description'=>nl2br($description),
                        $this->whatHappenedTo($projectName, $md5Name, $revision) => true
                    );
                }
                $values['file']['name'] = $file;
                $values['project']['name'] = $projectName;
                $this->render('showFileHistory', $values);
                return true;
            }
        }
        echo 'NADA!';
    }

    /**
     * Gets a string telling if at the revision $revision, the file $file of the
     * project $project has been changed, created, removed, if nothing happened,
     * or if the file didn't exist (whether it had already existed or not)
     * @param str $project The project name
     * @param str $file The path and filename (relative path to the websailors' root)
     * @param int $revision The revision number, or null.
     * <ul><li>If $revision is an integer, looks what was the modification on this revision</li>
     * <li>If null, looks at the last modification (and not the last global's revision modification)</li></ul>
     * @return str
     * <ul><li>self::FILE_NOTCHANGED if nothing happened to the file</li>
     * <li>self::FILE_NEW if the file was created</li>
     * <li>self::FILE_MODIFIED if the file was modified</li>
     * <li>self::FILE_DELETED if the file was deleted</li>
     * <li>self::FILE_INEXISTANT if the file didn't exist</li></ul>
     */
    public function whatHappenedTo($projectName,$md5Name,$revision = null){
        if(file_exists(SH_DEV_PATH.$projectName.'/revisions/deletedFiles.php')){
            $deletedFiles = file(SH_DEV_PATH.$projectName.'/revisions/deletedFiles.php');
            foreach($deletedFiles as $deletedFile) {
                list($deleteRevision,$oldMd5Name,$newMd5Name) = explode(
                    ' ',
                    $deletedFile
                );
                if($revision == $deleteRevision && $oldMd5Name == $md5Name){
                    return self::FILE_DELETED;
                }
            }
        }

        if(file_exists(SH_DEV_PATH.$projectName.'/revisions/'.$md5Name.'.php')){
            $file = file(SH_DEV_PATH.$projectName.'/revisions/'.$md5Name.'.php');
            $realName = array_shift($file);

            list($firstRevision) = explode(' ',array_shift($file));
            if($firstRevision == $revision){
                return self::FILE_NEW;
            }
            if($firstRevision > $revision){
                return self::FILE_INEXISTANT;
            }
            foreach($file as $revisionData){
                list($thisRevision,$md5Name) = explode(' ',array_pop($file));
                if($revision == $thisRevision){
                    if($md5Name == 'deleted'){
                        return self::FILE_DELETED;
                    }
                    return self::FILE_MODIFIED;
                }
            }
        }
        return self::FILE_NOTCHANGED;
    }

    public function showProject(){
        if(isset($_GET['project'])){
            $this->links->html->setTitle('Projet : '.$_GET['project']);

            $projectName = $_GET['project'];
            if(is_dir(SH_DEVPARAMS_PATH.$projectName)){
                include(SH_DEVPARAMS_PATH.$projectName.'/description.params.php');
                $values['project'] = array(
                    'id' => $projectName,
                    'name' => $project['name'],
                    'showHistoryLink' => $this->translatePageToUri('/showFileHistory/')
                );
                if(!is_dir(SH_DEV_PATH.$projectName)){
                    $this->render('projectNotInitialized');
                }else{
                    $revisions = file_get_contents(SH_DEV_PATH.$projectName.'/actualRevision.txt');
                    for($a=0;$a<=$revisions;$a++){
                        $elements = array_diff(
                            scandir(SH_DEV_PATH.$projectName.'/'.$a),
                            array('.','..','description.php')
                        );
                        $values['revisions'][$a]['number'] = $a;
                        if(file_exists(SH_DEV_PATH.$projectName.'/'.$a.'/description.php')){
                            $values['revisions'][$a]['description'] = nl2br(
                                file_get_contents(
                                    SH_DEV_PATH.$projectName.'/'.$a.'/description.php'
                                )
                            );
                        }
                        foreach($elements as $element){
                            if(substr($element,-4) == '.php'){
                                $values['revisions'][$a]['added']++;
                                $values['revisions'][$a]['details'][] = array(
                                    'file' => $this->getFileName($projectName, $element, true),
                                    'fileId' => $this->removeExtension($element),
                                    'added' => true
                                );
                            }elseif(substr($element,-5) == '.diff'){
                                $values['revisions'][$a]['modified']++;
                                $values['revisions'][$a]['details'][] = array(
                                    'file' =>$this->getFileName($projectName, $element, true),
                                    'fileId' => $this->removeExtension($element),
                                    'modified' => true
                                );
                            }
                        }
                    }
                    // The changes (and addings) are listed in the revision directory,
                    // and the removed files in the revisions/deletedFiles.php file
                    if(file_exists(SH_DEV_PATH.$projectName.'/revisions/deletedFiles.php')){
                        $removed = file(SH_DEV_PATH.$projectName.'/revisions/deletedFiles.php');
                        foreach($removed as $file){
                            list($revision,$oldName,$newName) = explode(' ',$file,4);
                            $values['revisions'][$revision]['deleted']++;
                            $values['revisions'][$revision]['details'][] = array(
                                'file' => $this->getFileName($projectName, trim($newName), true),
                                'fileId' => $newName,
                                'deleted' => true
                            );
                        }
                    }else{
                        $removed = array();
                    }
                    $this->render('showProject', $values);
                }
            }
        }else{
            $this->links->html->setTitle('Choix du projet');
            $this->chooseProject(__FUNCTION__);
        }
        return true;
    }

    protected function removeExtension($fileName){
        $parts = explode('.',$fileName);
        if(isset($parts[1])){
            // If there is only 1 element, there is no extension
            array_pop($parts);
        }
        return implode('.',$parts);
    }

    protected function getFileName($project,$md5Name,$removeExtension = false){
        if($removeExtension){
            $md5Name = $this->removeExtension($md5Name);
        }
        if(!file_exists(SH_DEV_PATH.$project.'/revisions/'.$md5Name.'.php')){
            // The file must have been deleted, so we look in the deleted files
            if(file_exists(SH_DEV_PATH.$project.'/revisions/deletedFiles.php')){
                $removed = file(SH_DEV_PATH.$project.'/revisions/deletedFiles.php');
                foreach($removed as $file){
                    list($revision,$oldName,$newName) = explode(' ',$file,4);
                    if($oldName == $md5Name){
                        return $this->getFileName($project,trim($newName));
                    }
                }
            }
        }
        $name = array_shift(
            file(
                SH_DEV_PATH.$project.'/revisions/'.$md5Name.'.php'
            )
        );
        return $name;
    }

    protected function chooseProject($function){
        $projects = array_diff(scandir(SH_DEVPARAMS_PATH),array('.','..'));
        foreach($projects as $projectId){
            if(file_exists(SH_DEVPARAMS_PATH.$projectId.'/description.params.php')){
                include(SH_DEVPARAMS_PATH.$projectId.'/description.params.php');
                $values['projects'][] = array(
                    'id' => $projectId,
                    'name' => $project['name'],
                    'folder' => $project['folder']
                );
            }
        }
        $values['links']['toHere'] = $this->translatePageToUri('/'.$function.'/');
        $this->render('chooseProject', $values);
    }

    protected function commit(){
        $project = $_POST['project'];
        if(is_array($_POST['files'])){
            $actualRevision = file_get_contents(SH_DEV_PATH.$project.'/actualRevision.txt');
            $newRevision = $actualRevision + 1;
            $this->links->helper->writeInFile(SH_DEV_PATH.$project.'/actualRevision.txt',$newRevision);
            $revisionFolder = SH_DEV_PATH.$project.'/'.$newRevision.'/';
            mkdir($revisionFolder);
            $this->links->helper->writeInFile(
                $revisionFolder.'description.php',
                $_POST['text']['global']
            );
            foreach(array_keys($_POST['files']) as $file){
                $revisionFile = SH_DEV_PATH.$project.'/revisions/'.$file.'.php';
                if(file_exists($revisionFile)){
                    $revisions = file(
                        $revisionFile,
                        FILE_IGNORE_NEW_LINES
                    );
                    $fileName = array_shift($revisions);
                    $longFileName = SH_ROOT_FOLDER.$fileName;
                    $noMoreCommitHere = false;
                    while($longFileName != SH_ROOT_FOLDER){
                        // The folder may have been transformed to noCommit
                        if(file_exists($longFileName.'noCommit')){
                            $noMoreCommitHere = true;
                            break;
                        }
                        $longFileName = dirname($longFileName).'/';
                    }
                    if(!file_exists(SH_ROOT_FOLDER.$fileName) || $noMoreCommitHere){
                        // We've deleted the file
                        $filemd5 = self::FILE_DELETED;
                        $newFileName = md5($file.$newRevision);
                        $newFile = SH_DEV_PATH.$project.'/revisions/'.$newFileName.'.php';
                        rename(
                            $revisionFile,
                            $newFile
                        );
                        $revisionFile = $newFile;
                        $this->links->helper->writeInFile(
                            SH_DEV_PATH.$project.'/revisions/deletedFiles.php',
                            $newRevision.' '.$file.' '.$newFileName."\n",
                            true
                        );
                    }else{
                        // We've modified the file
                        if($this->isTextFile($fileName)){
                            // We create the patch from its first revision to now
                            list($firstRevision, $md5) = explode(' ',array_shift($revisions),2);
                            $this->links->diff->createPatch(
                                SH_DEV_PATH.$project.'/'.$firstRevision.'/'.$file.'.php',
                                SH_ROOT_FOLDER.$fileName,
                                $revisionFolder.'/'.$file.'.diff'
                            );
                        }else{
                            // We copy the binary file
                            copy(
                                SH_ROOT_FOLDER.$fileName,
                                SH_DEV_PATH.$project.'/'.$firstRevision.'/'.$file.'.bin'
                            );
                        }
                        $filemd5 = md5_file(SH_ROOT_FOLDER.$fileName);
                    }
                }else{
                    // It is a new file, so we add it
                    $fileName = $_POST['newFiles'][$file];
                    $filemd5 = md5_file(SH_ROOT_FOLDER.$fileName);
                    if($this->isTextFile($fileName)){
                        // We copy the text file
                        copy(
                            SH_ROOT_FOLDER.$fileName,
                            $revisionFolder.$file.'.php'
                        );
                    }else{
                        // We copy the binary file
                        copy(
                            SH_ROOT_FOLDER.$fileName,
                            $revisionFolder.$file.'.bin'
                        );
                    }
                    // We add a line for this revision
                    $this->links->helper->writeInFile(
                        $revisionFile,
                        $fileName."\n"
                    );
                }
                // We add a line for this revision
                $this->links->helper->writeInFile(
                    $revisionFile,
                    $newRevision.' '.$filemd5.' '.$_POST['text'][$file]."\n",
                    true
                );
            }
        }

    }

    public function prepareCommit(){
        $this->onlyMaster();
        if($this->formSubmitted('commitForm')){
            // We are committing
            $this->commit();
        }
        if(isset($_GET['project'])){
            // The project has been chosen. We have to find out which files have
            // been modified/created/deleted
            $projectName = $_GET['project'];
            if(is_dir(SH_DEVPARAMS_PATH.$projectName)){
                include(SH_DEVPARAMS_PATH.$projectName.'/description.params.php');
                $values['project'] = array(
                    'id' => $projectName,
                    'name' => $project['name']
                );
                if(is_dir(SH_DEV_PATH.$projectName)){
                    $this->links->html->setTitle($project['name'].' - Soumission de mise à jour');

                    $files = $this->getProjectElements($projectName);

                    $existing = array_flip(
                        scandir(SH_DEV_PATH.$projectName.'/revisions/')
                    );

                    unset($existing['.']);
                    unset($existing['..']);
                    unset($existing['deletedFiles.php']);
                    $values['files'] = array();
                    foreach($files as $file){
                        if(file_exists(SH_DEV_PATH.$projectName.'/revisions/'.$file['md5Name'].'.php')){
                            $revisions = file(
                                SH_DEV_PATH.$projectName.'/revisions/'.$file['md5Name'].'.php',
                                FILE_IGNORE_NEW_LINES
                            );
                            $fileName = array_shift($revisions);
                            list($firstRevision, $md5) = explode(' ',array_pop($revisions),3);
                            if($md5 != $file['md5Sum']){
                                $values['files'][] = array(
                                    'name' => $file['name'],
                                    'md5' => $file['md5Name'],
                                    'changed'=>true
                                );
                            }
                            unset($existing[$file['md5Name'].'.php']);
                        }else{
                            $values['files'][] = array(
                                'name' => $file['name'],
                                'md5' => $file['md5Name'],
                                'new'=>true
                            );
                        }
                    }
                    foreach(array_keys($existing) as $missing){
                        if(!file_exists(SH_DEV_PATH.$projectName.'/revisions/'.$missing.'.php')){
                            $revisions = file(
                                SH_DEV_PATH.$projectName.'/revisions/'.$missing,
                                FILE_IGNORE_NEW_LINES
                            );
                            $name = array_shift($revisions);

                            // @todo Remove this test for next versions
                            if(array_pop(explode('/',$name)) != 'Thumbs.db'){
                                list($lastRev,$md5) = explode(' ',array_pop($revisions));
                                if($md5 != self::FILE_DELETED){
                                    // We check if it hasn't already been deleted
                                    if($this->isTextFile($missing)){
                                        $type = 'text';
                                    }else{
                                        $type = 'bin';
                                    }
                                    $values['files'][] = array(
                                        'name' => $name,
                                        'type' => $type,
                                        'md5' => md5($name),
                                        'removed'=>true
                                    );
                                }
                            }
                        }
                    }

                    $values['showChangesLink']['base'] = $this->links->path->getLink(
                        'diff/showChanges/'
                    );
                    if($values['files'] == array()){
                        $values['files'] = 'noChanges';
                    }

                    $actualRevision = file_get_contents(SH_DEV_PATH.$projectName.'/actualRevision.txt');
                    $values['revisionNumber']['actual'] = $actualRevision;
                    $values['revisionNumber']['next'] = $actualRevision + 1;

                    $this->render('prepareCommit',$values);
                    return true;
                }
                if($this->formSubmitted('projectInitializer')){
                   $this->initializeProject($projectName);
                    $this->render('projectInitialized',$values);
                    return true;
                }
                $this->render('initializeProject',$values);
                return true;
            }
        }
        $this->chooseProject(__FUNCTION__);
    }

    /**
     * Creates the architecture of the project in the dev folder, and inserts the
     * first revision (#0)
     * @param str $projectName The name of the project, as it may be found in the
     * dev folder
     */
    protected function initializeProject($projectName){
        include(SH_DEVPARAMS_PATH.$projectName.'/description.params.php');
        
        $this->links->helper->deleteDir(SH_DEV_PATH.$projectName.'/revisions/');
        $this->links->helper->writeInFile(
            SH_DEV_PATH.$projectName.'/actualRevision.txt',
            '0'
        );
        mkdir(SH_DEV_PATH.$projectName.'/revisions/');
        mkdir(SH_DEV_PATH.$projectName.'/0/');
        
        $this->links->helper->writeInFile(
            SH_DEV_PATH.$projectName.'/0/description.php',
            $_POST['description']
        );

        $files = $this->getProjectElements($projectName);

        foreach($files as $file){
            $fileName = $file['name'];
            if($file['type'] == 'text'){
                copy($file['name'],SH_DEV_PATH.$projectName.'/0/'.md5($fileName).'.php');
                $this->links->helper->writeInFile(
                    SH_DEV_PATH.$projectName.'/revisions/'.md5($fileName).'.php',
                    $fileName."\n0 ".md5_file(SH_ROOT_FOLDER.$fileName)."\n"
                );
            }elseif($file['type'] == 'bin'){
                copy($file['name'],SH_DEV_PATH.$projectName.'/0/'.md5($fileName).'.bin');
                $this->links->helper->writeInFile(
                    SH_DEV_PATH.$projectName.'/revisions/'.md5($fileName).'.php',
                    $fileName."\n0 ".md5_file(SH_ROOT_FOLDER.$fileName)."\n"
                );
            }
        }
    }

    protected function getProjectElements($projectName){
        include(SH_DEVPARAMS_PATH.$projectName.'/elements.php');
        $files = array();
        if(is_array($elements['folders'])){
            foreach($elements['folders'] as $folder){
                $files = array_merge($files,$this->getAllFiles(SH_ROOT_FOLDER.$folder.'/'));
            }
        }
        if(is_array($elements['files'])){
            foreach($elements['files'] as $singleFile){
                $singleFiles[] = SH_ROOT_FOLDER.$singleFile;
            }
            $files = array_merge($files,$this->getAllFiles($singleFiles));
        }
        return $files;
    }

    /**
     * Checks if the file is known as a text file (on which we can apply patches).
     * The checking is done on the extension, and not on the file's headers.
     * @param str $fileName The file name
     * @return bool True if it is a text file, false otherwise.
     */
    protected function isTextFile($fileName){
        return in_array(
            array_pop( // the file's extension
                explode('.',$fileName)
            ),
            $this->extensions // the text extensions
        );
    }

    protected function getAllFiles($folder){
        $return = array();
        if(is_array($folder)){
            $elements = $folder;
            $folder = '';
        }else{
            if(!is_dir($folder) || file_exists($folder.'noCommit')){
                return $return;
            }
            $elements = scandir($folder);
        }
        foreach($elements as $element){
            if(substr($element,0,1) == '.' || $element=='Thumbs.db'){
                continue;
            }

            // To authorize copies on any systems, we cut the root folder
            $shortPath = str_replace(SH_ROOT_FOLDER,'',$folder.$element);
            if(is_dir($shortPath)){
                $return = array_merge($return,$this->getAllFiles($shortPath.'/'));
            }elseif($this->isTextFile($element)){
                $return[] = array(
                    'type'=>'text',
                    'name'=>$shortPath,
                    'md5Name'=>md5($shortPath),
                    'md5Sum'=>md5_file($shortPath),
                    'md5'=>md5($shortPath)
                );
            }else{
                $return[] = array(
                    'type'=>'bin',
                    'name'=>$shortPath,
                    'md5Name'=>md5($shortPath),
                    'md5Sum'=>md5_file($shortPath)
                );
            }
        }
        return $return;
    }

/**
 * Configures the debug, code coverage, etc...
 * @return string (xml)
 */
    public function configure(){
        // Only the masters can do that actions
        $this->onlyMaster();
        if($this->formSubmitted('dev_configurer')){
            $activatedElements = '';
            $endActivatedElements = '';
            $constants = '';

            $debugFilesPath = SH_CLASS_FOLDER.__CLASS__.'/debugFiles/';
            if(!is_dir($debugFilesPath)){
                mkdir($debugFilesPath);
            }
            $this->setParam('debug_path>path', $_POST['debug_path']);
            $debugFile = SH_ROOT_FOLDER.'debug.php';
            if(file_exists($debugFile)){
                unlink($debugFile);
            }
            // Code Coverage
            if(isset($_POST['codeCoverage_activated'])){
                $this->setParam('codeCoverage>activated', 'checked');
                $constants .= 'define("SH_DEBUG_COVERAGE_PAGE","'.$debugFilesPath.'coverage.php");'."\n";
            }else{
                $this->setParam('codeCoverage>activated', '');
            }
            // Trace
            if(isset($_POST['trace_activated'])){
                $this->setParam('trace>activated', 'checked');
                $constants .= 'define("SH_DEBUG_TRACE_PATH","'.$debugFilesPath.'traces/".microtime(true));'."\n";
                if(!is_dir($debugFilesPath.'traces')){
                    mkdir($debugFilesPath.'traces');
                }
            }else{
                $this->setParam('trace>activated', '');
            }
            // Errors displaying
            $errors = '';
            if(isset($_POST['E_ALL_activated'])){
                $this->setParam('errors>e_all', 'checked');
                $errors = E_ALL;
                $operator = 'remove';
            }else{
                $this->setParam('errors>e_all', '');
            }
            if(isset($_POST['E_STRICT_activated'])){
                $this->setParam('errors>e_strict', 'checked');
                if($operator == 'remove'){
                    $errors = $errors ^ E_STRICT;
                }else{
                    $errors = $errors & E_STRICT;
                }
            }else{
                $this->setParam('errors>e_strict', '');
            }
            if(isset($_POST['E_WARNING_activated'])){
                $this->setParam('errors>e_warning', 'checked');
                if($operator == 'remove'){
                    $errors = $errors ^ E_WARNING;
                }else{
                    $errors = $errors & E_WARNING;
                }
            }else{
                $this->setParam('errors>e_warning', '');
            }
            if(isset($_POST['E_NOTICE_activated'])){
                $this->setParam('errors>e_notice', 'checked');
                if($operator == 'remove'){
                    $errors = $errors ^ E_NOTICE;
                }else{
                    $errors = $errors & E_NOTICE;
                }
            }else{
                $this->setParam('errors>e_notice', '');
            }
            if(!empty($errors)){
                $constants .= 'define("SH_DEBUG_ERROR_REPORTING","'.$errors.'");'."\n";
            }

            // Debug
            if(isset($_POST['debug_activated'])){
                $this->setParam('debug>activated', 'checked');
                $content = '<?php'."\n";
                $constants .= 'define("SH_DEBUG_VERIFY_FOLDER","'.$_POST['debug_path'].'");'."\n";

                $f = fopen($debugFile,'w+');
                fwrite($f,
'<?php
'.$constants.'
include("'.dirname(__FILE__).'/debug_functions.php");
'
                );
                fclose($f);
            }else{
                $this->setParam('debug>activated', '');
            }
            $this->writeParams();
            $this->links->path->refresh();
        }
        $az++;
        $values['default']['debug_activated'] = $this->getParam('debug>activated','');
        $values['default']['codeCoverage_activated'] = $this->getParam('codeCoverage>activated','');
        $values['default']['trace_activated'] = $this->getParam('trace>activated','');
        $values['default']['debug_path'] = $this->getParam('debug_path>path','');
        $values['default']['E_ALL_activated'] = $this->getParam('errors>e_all','');
        $values['default']['E_STRICT_activated'] = $this->getParam('errors>e_strict','');
        $values['default']['E_WARNING_activated'] = $this->getParam('errors>e_warning','');
        $values['default']['E_NOTICE_activated'] = $this->getParam('errors>e_notice','');

        $this->render('configure',$values);
        return $ret;
    }
    
    public function test(){
        $this->onlyMaster();
        sh_cache::disable();
        if(isset($_GET['rf'])){
            $rf = $_GET['rf'];
            $values = array();
            if(file_exists(dirname(__FILE__).'/renderFiles/tests/'.$rf.'.values.php')){
                include(dirname(__FILE__).'/renderFiles/tests/'.$rf.'.values.php');
            }
            $this->render('tests/'.$rf,$values);
            return true;
        }
        $this->links->html->insert('L\'url doit se finir par ?rf=nom_du_rf_sans_extension');
        return false;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        list($class,$method,$id) = explode('/',$page);
        if($method == 'configure'){
            $uri = '/'.$this->shortClassName.'/configure.php';
            return $uri;
        }
        if($method == 'prepareCommit'){
            $uri = '/'.$this->shortClassName.'/prepareCommit.php';
            return $uri;
        }
        if($method == 'showProject'){
            $uri = '/'.$this->shortClassName.'/showProject.php';
            return $uri;
        }
        if($method == 'showFileHistory'){
            $uri = '/'.$this->shortClassName.'/showFileHistory.php';
            return $uri;
        }
        if($method == 'test'){
            $uri = '/'.$this->shortClassName.'/test.php';
            return $uri;
        }
        if($method == 'showCheckList'){
            $uri = '/'.$this->shortClassName.'/showCheckList.php';
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
        if(preg_match('`/'.$this->shortClassName.'/configure\.php`',$uri)){
            $page = $this->shortClassName.'/configure/';
            return $page;
        }
        if($uri == '/'.$this->shortClassName.'/prepareCommit.php'){
            $page = $this->shortClassName.'/prepareCommit/';
            return $page;
        }
        if($uri == '/'.$this->shortClassName.'/showProject.php'){
            $page = $this->shortClassName.'/showProject/';
            return $page;
        }
        if($uri == '/'.$this->shortClassName.'/showFileHistory.php'){
            $page = $this->shortClassName.'/showFileHistory/';
            return $page;
        }
        if($uri == '/'.$this->shortClassName.'/test.php'){
            $page = $this->shortClassName.'/test/';
            return $page;
        }
        if($uri == '/'.$this->shortClassName.'/showCheckList.php'){
            $page = $this->shortClassName.'/showCheckList/';
            return $page;
        }

        return parent::translateUriToPage($uri);
    }

    public function __tostring(){
        return get_class();
    }
}


