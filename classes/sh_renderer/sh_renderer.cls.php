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
 * Class that renders the html from the templates files (.rf.xml files).
 * May be extended using plugins.
 * @todo Add a RENDER_CHECKBOX and a RENDER_RADIOBOX to allow the user to click
 * on the text instead of the input only.
 */
class sh_renderer extends sh_core {
    protected $actions;
    protected $captchasEnabled = true;
    protected $needsRenderer = false;
    protected $xml = array();
    protected $i18nClasses = array();
    protected $values = array();
    protected $methods = array();
    protected $plugins = array();
    protected $previousLoopName = null;
    protected $enableDb = false;
    protected $enableRenderer = false;
    protected $backupValues = array();
    protected $actualLoopName = false;
    protected $actualLoopId = null;

    /**
     * Initiates the object
     */
    public function construct() {
        // Prepares the methods for the special tags
        $this->methods = array(
            'RENDER_VALUE'=>        'replaceValue',
            'RENDER_IFSET'=>        'ifSet',
            'RENDER_IFNOTSET'=>     'ifNotSet',
            'RENDER_LOOP'=>         'createLoop',
            'RENDER_TABLE'=>        'createTable',
            'RENDER_TAG'=>          'createTag',
            'RENDER_LOADMODULE'=>   'loadModule',
            'RENDER_FORM'=>         'createFormVerifier',
            'RENDER_DEBUG'=>        'inPlaceDebugger',
            'RENDER_TRANSLATOR'=>   'createTranslator',
            'RENDER_TEST'=>         'test',
            'RENDER_DEBUGALL'=>     'debugAll',
            'RENDER_STRUCTURE'=>    'createStructure',
            'RENDER_NOTIF'=>        'createNotif',
            'RENDER_ADMINBOX'=>     'createAdminBox',
            'RENDER_ADMINBOXCONTENT'=>'createAdminBoxContent',
            'NORENDER'=>            'comment'
        );
        // Prepares the replacement of the plugins special tags
        // (plugins may not override this classe's methods, because they are
        // looked for only if none is found in this classes's methods).
        // They are stored in files named the same as the tag in the folder
        // $pluginsDir.
        // These files contain the class that will manage the content in the
        // first line, and the method to call in the second one.
        $pluginsDir = SH_CLASS_SHARED_FOLDER.__CLASS__.'/';
        if(is_dir($pluginsDir)){
            $files = scandir($pluginsDir);
            foreach($files as $file){
                if(substr($file,0,1) != '.'){
                    list($class,$method) = file($pluginsDir.$file);
                    $element = substr($file,0,-4);
                    $this->plugins[trim(strtoupper($element))] = array(
                        'class'=>trim($class),
                        'method'=>trim($method)
                    );
                }
            }
        }

        return true;
    }

    /**
     * Pre-loads the classe's i18n files (stores the name to be able to load
     * them if needed).
     * @param str $class The class name
     * @return bool Always returns true
     */
    public function loadI18n($class){
        $this->i18nClasses[$_SESSION['rendering']] = $class;
        return true;
    }

    public function array_change_key_case_recursive(&$array){
        if(is_array($array)){
            $array = array_change_key_case($array);
            foreach($array as $key=>$element){
                $array[$key] = $this->array_change_key_case_recursive($element);
            }
        }
        return $array;
    }

    /**
     * Main function - Renders the document
     * @param str $model The path and name of the .rf.xml file
     * @param array $values An array containing the values to replace.<br />
     * Defaults to an empty array.
     * @param bool|int $debug See sh_debug for informations about this.
     * @return str Returns the rendered contents.
     */
    public function render($model,$values = '',$debug = false){
        if(is_dir(dirname($debug))){
            $this->debugging(3,$debug);
        }else{
            $this->debugging($debug);
        }

        $browser = @get_browser();
        if(is_object($browser)){
            $values['userAgent'][$browser->browser] = true;
        }

        //Verifies that the file $model really exists
        if(file_exists($model)){
            // This part should not be used anymore, now that we get an xml string instead of an xml file
            $this->oldXmlDoc = new DOMDocument('1.0', 'UTF-8');
            $this->debug('We load the document "'.$model.'"',3,__LINE__);
            $this->oldXmlDoc->load($model);
            $enterLast = false;
        }else{
            $this->oldXmlDoc = new DOMDocument('1.0', 'UTF-8');
            $model = '<content>'.$model.'</content>';
            // If the content of $model is not an xml string, we exit with an error
            if(!$this->oldXmlDoc->loadXML($model)){
                $this->oldXmlDoc = null;
                $this->debug(
                    'The render file "'.$model.'" was not found.',
                    0,
                    __LINE__
                );
                return false;
            }
            $enterLast = true;
        }

        // We store the old replacement values
        $old_values = $this->values;
        // We set to lower case the keys from $values
        $this->values = $this->array_change_key_case_recursive($values);

        // We store the old rendering id, and creates a new one
        $previousRendering = $_SESSION['rendering'];
        $rendering = $_SESSION['rendering'] = md5(microtime());

        $this->debug(
            'We load the i18n file for the class '.$this->i18nClasses[$rendering],
            2,
            __LINE__
        );

        if(isset($this->values['i18n'])){
            $this->i18nClasses[$rendering] = $this->values['i18n'];
        }

        
        $this->debug('We start a render process',1,__LINE__);

        $this->xml[$rendering] = new DOMDocument('1.0', 'UTF-8');
        $this->xml[$rendering]->resolveExternals = true;
        $this->defaultXMLVersion = $this->xml[$rendering]->xmlVersion;
        $racine = $this->oldXmlDoc->documentElement;

        // Let's render
        $this->enterTag($racine,$this->xml[$rendering]);

        // Done

        // We don't want to send the xml declaration, so we don't send it
        if(!$enterLast){
            $xml = $this->xml[$rendering]->saveXML(
                $this->xml[$rendering]->documentElement
            );
        }else{
            //$xml = $this->xml[$rendering]->documentElement->firstChild->nodeValue;
            $xml = $this->xml[$rendering]->firstChild->nodeValue;
            if($this->xml[$rendering]->documentElement->hasChildNodes()){
                $xml = $this->xml[$rendering]->saveXML(
                    $this->xml[$rendering]->documentElement->firstChild
                );
            }else{
                $xml = '';
            }
        }
        // We replace some elements to create valid the xhtml
        $xhtml = preg_replace(
            array(
                // Auto-closed tags with parametters
                '`(<(style|head|select|a|link|script|div|span|p|ul|li|ol|table|tr|td|th|caption|textarea|h1|h2|h3|h4|h5|h6|h7|param|embed|meta) [^>]*)/>`',
                // Auto-closed tags without parametters
                '`(<(div|span|p|ul|li|ol|td|tr|th|caption|textarea|h1|h2|h3|h4|h5|h6|h7|strong))/>`',
                // New lines
                '`(<(br|hr) */>)`'/*,
                // Beautifulizing - These 2 lines may be commented to increase performances
                '`(</([^>]+)>)`',
                '`(/>)`'*/
            ),
            array(
                '$1></$2>',
                '$1></$2>',
                '<$2 />'/*,
                '</$2>'."\n",
                '/>'."\n"*/
            ),
            $xml
        );

        $this->debug('We have finished the render process',1,__LINE__);
        // Restores the previous unic rendering id
        $_SESSION['rendering'] = $previousRendering;

        $this->values = $old_values;
        return $xhtml;
    }

    /**
     * Enables a web-designer to create .rf.xml before the developper has
     * created the script that generates the $values.
     * @param DOMElement $tag The element to enter
     * @param DOMElement $dest The element in which the changes will be made
     * @return bool Always returns true
     */
    protected function test($tag,$dest){
        $this->debug('We launch a rf tester',3,__LINE__);
        foreach ($tag->attributes as $attribute){
            if($attribute->name == 'rf'){
                $rf = $attribute->value;
            }elseif($attribute->name == 'values'){
                $valuesFile = $attribute->value;
            }
        }
        if(!file_exists(SH_ROOT_FOLDER.'tests/'.$rf)){
            $this->debug('Render file ('.$rf.') does not exist',1,__LINE__);
            return false;
        }

        if(file_exists(SH_ROOT_FOLDER.'tests/'.$valuesFile)){
            include(SH_ROOT_FOLDER.'tests/'.$valuesFile);
        }

        $rendered = $this->links->renderer->render(
            SH_ROOT_FOLDER.'tests/'.$rf,$values,$this->debugging()
        );
        $tempNode = new domDocument('1.0', 'UTF-8');
        $tempNode->loadXML($rendered);
        $content = $tempNode->firstChild;
        $hiddenNode = $this->xml[$_SESSION['rendering']]->importNode(
            $content,
            true
        );
        $dest->appendChild($hiddenNode);
        return true;
    }

    /**
     * Do nothing with the contents of the tag. Is just here to enable the
     * .xf.xml to be commented.
     * @param DOMElement $tag Not used
     * @param DOMElement $dest Not used
     * @return bool Always returns true
     */
    protected function comment($tag,$dest){
        $this->debug('We add a comment, so we do nothing',3,__LINE__);
        return true;
    }

    /**
     * Displays in the debug the list of all the values and their keys, that
     * could be replaced.
     * @param DOMElement $tag The element to enter
     * @param DOMElement $dest The element in which the changes will be made
     * @return bool Always returns true
     */
    protected function debugAll($tag,$dest){
        $debugging = $this->debugging();
        if($debugging != 3){
            $this->debugging(3);
        }
        $this->debug('We show all variables',3,__LINE__);
        $this->debug('-------BEGINNING-----',3,__LINE__);

        if(is_array($this->values)){
            foreach($this->values as $key=>$value){
                $this->debugAll_element($key,$value);
            }
        }

        $this->debug('---------ENDING------',3,__LINE__);
        if($debugging != 3){
            $this->debugging($debugging);
        }
        return true;
    }

    /**
     * Method used by debugAll to debug the content in lines.
     * @param str $key The name of the key.
     * @param str $element The content of the value.
     */
    protected function debugAll_element($key,$element){
        if(!is_array($element)){
            $this->debug($key.' = '.$element,3,0,false);
        }else{
            foreach($element as $newKey=>$value){
                $this->debugAll_element($key.'>'.$newKey,$value);
            }
        }
    }

    /**
     * Creates an xml tree containing RENDER_VALUE and RENDER_LOOP tags for all
     * the values.<br />
     * Exists in order to help at the beginning of the creation of a .rf.xml file.
     * @todo Finish this function
     * @param DOMElement $tag The element to enter
     * @param DOMElement $dest The element in which the changes will be made
     * @return bool Always returns true
     */
    protected function createStructure($tag,$dest){
        $this->debug('We show the structure of the file',3,__LINE__);
        $showContent = false;
        foreach ($tag->attributes as $attribute){
            if($attribute->name == 'showContent'){
                if(
                    strtolower($attribute->value) == 'true'
                    || strtolower($attribute->value) == 'showcontent'
                ){
                    $showContent = true;
                }
            }
        }

        if(is_array($this->values)){
            echo '<div style="font-family:Courier New;">';
            foreach($this->values as $key=>$values){
                if(is_array($values)){
                    echo $key.'<br />';
                    $this->createStructure_element(
                        $this->values,
                        $key,
                        '',
                        '',
                        $showContent
                    );
                }
            }
            echo '</div>';
        }

        return true;
    }

    /**
     * The same as createStructure.
     * @todo Finish this function
     */
    protected function createStructure_element($element,$key = '',$parentKey = '',$indent = '',$showContent = false,$onlyOnce = false){
        if(is_array($element) && $key != 'i18n' &&  $parentKey != 'i18n'){
            $onlyOnceCounter = 0;
            if(isset($element[0])){
                $onlyOnce = (isset($values[0]));
            }
            foreach($element as $newKey=>$value){
                if($onlyOnce && $onlyOnceCounter > 0){
                    return true;
                }
                $onlyOnceCounter++;

                if(!is_array($value)){
                    if($newKey != 'i18n'){
                        echo $indent.'&#60;RENDER_VALUE what="'.$key.'&#62;'.$newKey.'"/&#62<br />';
                        if($showContent){
                            echo $indent.'<span style="border:1px solid blue">'.$this->createStructure_cleanOutput($value).'</span><br />';
                        }
                    }
                }else{
                    $newIndent = $indent.'&#160;&#160;&#160;&#160;';
                    foreach($value as $oneKey=>$oneValue){
                        echo $indent.'&#60;RENDER_LOOP what="'.$key.'"&#62;<br />';
                        $this->createStructure_element($oneValue,$key,$key,$newIndent,$showContent);
                        echo $indent.'&#60;/RENDER_LOOP&#62;<br />';
                   }
                }
            }
        }elseif($key != 'i18n' &&  $parentKey != 'i18n'){
            echo $indent.'&#60;RENDER_VALUE what="'.$key.'&#62;'.$parentKey.'"/&#62<br />';
            if($showContent){
                echo $indent.'<span style="border:1px solid blue">'.$this->createStructure_cleanOutput($element).'</span><br />';
            }
        }
    }

    /**
     * The same as createStructure.
     * @todo Finish this function
     */
    protected function createStructure_cleanOutput($output){
        $search = array('`<`','`>`');
        $replace = array('&#60;','&#62');
        return preg_replace($search,$replace,$output);
    }

    protected function createNotif($tag, $dest){
        $this->debug('We create a notification',3,__LINE__);

        foreach ($tag->attributes as $attribute){
            if($attribute->name == 'id'){
                $id = $this->changeValue($attribute->value);
                $hasId = true;
            }elseif($attribute->name == 'title'){
                $title = $this->changeValue($attribute->value);
                $hasTitle = true;
            }elseif($attribute->name == 'size'){
                $size = $this->changeValue($attribute->value);
            }elseif($attribute->name == 'type'){
                $type = $this->changeValue($attribute->value);
                if(strtolower($type) == 'alert'){
                    $alert = true;
                }
            }
        }
        if(empty($size)){
            $size = 'L';
        }
        
        // Creating the container
        $node = $this->xml[$_SESSION['rendering']]->createElement('div');
        $mainNode = $dest->appendChild($node);
        if(!$alert){
            $mainNode->setAttribute('class','notif_container');
        }else{
            $mainNode->setAttribute('class','notif_container_alert');
        }
        if($hasId){
            $mainNode->setAttribute('id',$id);
        }
        // Creating the top, and the title if necessary
        $node = $this->xml[$_SESSION['rendering']]->createElement('div');
        $topNode = $mainNode->appendChild($node);
        $topNode->setAttribute('class','notif'.$size.'_top');
        if($hasTitle){
            $h3Content = '<h3>'.$title.'</h3>';
            $tempNode = new domDocument('1.0', 'UTF-8');
            $tempNode->loadXML($h3Content);
            $content = $tempNode->firstChild;
            $hiddenNode = $this->xml[$_SESSION['rendering']]->importNode($content,true);
            $topNode->appendChild($hiddenNode);
        }
        // Creating the middle and the content
        $node = $this->xml[$_SESSION['rendering']]->createElement('div');
        $contentNode = $mainNode->appendChild($node);
        $contentNode->setAttribute('class','notif'.$size.'_middle');
        $node = $this->xml[$_SESSION['rendering']]->createElement('div');
        $textContentNode = $contentNode->appendChild($node);
        $textContentNode->setAttribute('class','notif'.$size.'_content');
        if($tag->hasChildNodes()){
            $this->enterChildren($tag, $textContentNode);
        }
        // Creating the bottom
        $node = $this->xml[$_SESSION['rendering']]->createElement('div');
        $contentNode = $mainNode->appendChild($node);
        $contentNode->setAttribute('class','notif'.$size.'_bottom');

    }
    protected function createAdminBox($tag, $dest){
        $this->debug('We create an admin box',3,__LINE__);

        foreach ($tag->attributes as $attribute){
            if($attribute->name == 'title'){
                $title = $this->changeValue($attribute->value);
                $hasTitle = true;
            }
        }

        // Creating the container
        $node = $this->xml[$_SESSION['rendering']]->createElement('div');
        $mainNode = $dest->appendChild($node);
        $mainNode->setAttribute('class','form_box_container');

        // Creating the top, and the title if necessary
        $node = $this->xml[$_SESSION['rendering']]->createElement('div');
        $topNode = $mainNode->appendChild($node);
        $topNode->setAttribute('class','form_box_top');
        if($hasTitle){
            $h3Content = '<h3 class="box_title">'.$title.'</h3>';
            $tempNode = new domDocument('1.0', 'UTF-8');
            $tempNode->loadXML($h3Content);
            $content = $tempNode->firstChild;
            $hiddenNode = $this->xml[$_SESSION['rendering']]->importNode($content,true);
            $topNode->appendChild($hiddenNode);
        }

        // Creating the middle and the content
        $node = $this->xml[$_SESSION['rendering']]->createElement('div');
        $contentNode = $mainNode->appendChild($node);
        $contentNode->setAttribute('class','form_box_middle');
        if($tag->hasChildNodes()){
            $this->enterChildren($tag, $contentNode);
        }
        // Creating the bottom
        $node = $this->xml[$_SESSION['rendering']]->createElement('div');
        $contentNode = $mainNode->appendChild($node);
        $contentNode->setAttribute('class','form_box_bottom');
    }
    protected function createAdminBoxContent($tag, $dest){
        $this->debug('We create an admin box',3,__LINE__);

        // Creating the container
        $node = $this->xml[$_SESSION['rendering']]->createElement('div');
        $mainNode = $dest->appendChild($node);
        $mainNode->setAttribute('class','formContent');
        if($tag->hasChildNodes()){
            $this->enterChildren($tag, $mainNode);
        }
    }

    /**
     * protected function createFormVerifier
     *
     */
    protected function createFormVerifier($tag,$dest){
        $this->debug('We create a form verifier',3,__LINE__);
        foreach ($tag->attributes as $attribute){
            $attributeValue = $this->changeValue($attribute->value);
            if($attribute->name == 'id'){
                $formIdIsSet = true;
                $formId = $attributeValue;
                $attributes .= ' id="'.$attributeValue.'"';
            }elseif($attribute->name == 'method'){
                $methodIsSet = true;
                if(!empty($attributeValue)){
                    $attributes .= ' '.$attribute->name.'="'.$attributeValue.'"';
                }
            }elseif($attribute->name != 'name'){
                // We don't copy any "name" attribute because there is not any in xhtml
                $attributes .= ' '.$attribute->name.'="'.$attributeValue.'"';
            }
        }
        if(!$methodIsSet){
            $attributes .= ' method="POST"';
        }
        if(!$formIdIsSet){
            $this->debug('We have no "id" attribute in the "RENDER_FORM" tag so no verifier can be used!', 0, __LINE__);
            $formId = 'error';
        }
        $xmlTag = '<form'.$attributes.'><div></div></form>';
        $tempNode = new domDocument('1.0', 'UTF-8');

        $tempNode->loadXML($xmlTag);
        $content = $tempNode->firstChild;
        $formNode = $this->xml[$_SESSION['rendering']]->importNode($content,true);
        $newTag = $dest->appendChild($formNode);
        $newContentTag = $newTag->firstChild;

        $value = self::$form_verifier->create($formId);
        $hidden = '<input type="hidden" name="verif" value="'.$value.'" />';
        $tempNode = new domDocument('1.0', 'UTF-8');
        $tempNode->loadXML($hidden);
        $content = $tempNode->firstChild;
        $hiddenNode = $this->xml[$_SESSION['rendering']]->importNode($content,true);
        $newContentTag->appendChild($hiddenNode);

        if($tag->hasChildNodes()){
            $this->enterChildren($tag, $newContentTag);
        }
    }

    /**
     * protected function inPlaceDebugger
     *
     */
    protected function inPlaceDebugger($tag,$dest){
        $this->debug('We apply the function '.__FUNCTION__. ' on the tag "'.$tag->nodeName.'"',3,__LINE__);
        foreach ($tag->attributes as $attribute){
            if($attribute->name == 'level'){
                $level = $attribute->value;
            }
            if($attribute->name == 'debugTime'){
                $setTimeDebug = true;
                $oldTimeDebugStatus = $this->timeDebugStatus;
                $this->timeDebugStatus = true;
            }
        }
        if(!isset($level)){
            $this->debug('We could\'nt find the debug level',1,__LINE__);
            return false;
        }
        $previousDebugLevel = $this->debugging();
        $this->debugging($level);
        $this->debug('We change the debug level temporarly to  "'.$level.'"',1,__LINE__);
        $this->enterChildren($tag, $dest);

        if($setTimeDebug){
            $this->timeDebugStatus = $oldTimeDebugStatus;
        }

        $this->debug('We put back the debug level to  "'.$previousDebugLevel.'"',1,__LINE__);        
        $this->debugging($previousDebugLevel);
        return true;
    }
    
    protected function debug(
        $text,
        $level = sh_debugger::LEVEL,
        $line = sh_debugger::LINE,
        $showClassName = sh_debugger::SHOWCLASS
    ){
        if($this->timeDebugStatus){
            $text = '('.substr(microtime(true),-5).') '.$text;
        }
        return $this->debugger->debug($text,$level,$line,$showClassName);
    }

    /**
     * protected function loadModule
     *
     */
    protected function loadModule($tag,$dest){
        $this->debug('We apply the function '.__FUNCTION__. ' on the tag "'.$tag->nodeName.'"',3,__LINE__);
        foreach ($tag->attributes as $attribute){
            ${$attribute->name} = $attribute->value;
        }
        if(!isset($module)){
            $this->debug('We could\'nt find the module to load',1,__LINE__);
            return false;
        }

        $this->debug('We load the module "'.$module.'"',1,__LINE__);

        $this->links->$module->loadModule($params);
        return true;
    }

    /**
     * protected function createHelp
     *
     */
    protected function createHelp($tag,$dest){
        $this->debug('We apply the function '.__FUNCTION__. ' on the tag "'.$tag->nodeName.'"',3,__LINE__);
        foreach ($tag->attributes as $attribute){
            if($attribute->name == 'id'){
                $id = $attribute->value;
                $this->debug('An old version of RENDER_HELP was used for a div named "'.$attribute->value.'".',1,__LINE__);
                return false;
            }
            if($attribute->name == 'what'){
                $what = $attribute->value;
            }


        }
        if(!isset($what)){
            $this->debug('We could\'nt find the id of the destination div',1,__LINE__);
            return false;
        }
        $content = $this->changeValue('{'.$what.'}');

        $id=substr(MD5(__CLASS__.microtime()),0,12);
        $tempNode = new domDocument('1.0', 'UTF-8');
        $xml = '
<help>
    <img src="/images/shared/icons/help.png" class="aLink" onclick="UnTip();TagToTip(\''.$id.'\',BORDERCOLOR,\'#003366\',TITLEBGCOLOR,\'#336699\', BALLOON, true, ABOVE, true)"/>
    <div id="'.$id.'">
        <div class="render_help_explanation">
        '.$content.'
        </div>
    </div>
</help>
';
        $tempNode->loadXML($xml);
        $content = $tempNode->firstChild;
        $this->enterChildren($content, $dest);
        return true;
    }

    /**
     * protected function createTranslator
     *
     */
    protected function createTranslator($tag,$dest){
        $this->debug('We apply the function '.__FUNCTION__. ' on the tag "'.$tag->nodeName.'"',3,__LINE__);
        foreach ($tag->attributes as $attribute){
            if($attribute->name == 'id'){
                $id = $attribute->value;
            }
        }
        if(!isset($id)){
            $this->debug('We could\'nt find the id of the destination div',1,__LINE__);
            return false;
        }
        $this->debug('We build a help element for the div "'.$id.'"',2,__LINE__);
        $tempNode = new domDocument('1.0', 'UTF-8');
        $tempNode->loadXML(
            '<help><img src="/images/shared/icons/help.png" class="aLink" onclick="UnTip();TagToTip(\''.$id.'\',BORDERCOLOR,\'#003366\',TITLEBGCOLOR,\'#336699\', BALLOON, true, ABOVE, true)"/></help>'
        );
        $content = $tempNode->firstChild;
        $this->enterChildren($content, $dest);

        return true;
    }

    /**
     * public function getXmlVersionTag
     *
     */
    public function getXmlVersionTag(){
        return '<?xml version="'.$this->xml[$_SESSION['rendering']]->xmlVersion.'"?>';
    }

    /**
     * protected function createLoop
     *
     */
    protected function createLoop($tag,$dest){
        $this->debug('We apply the function '.__FUNCTION__. ' on the tag "'.$tag->nodeName.'"',3,__LINE__);
        foreach ($tag->attributes as $attribute){
            if($attribute->name == 'what'){
                $what = strtolower($attribute->value);
            }
        }
        if($what){
            if(is_array($this->values[$what])){
                $enter = true;
                $workingArray = $this->values[$what];
                $oldLoopName = $this->actualLoopName;
                $this->actualLoopName = $what;
            }elseif(
                    !is_null($this->previousLoopName)
                    && is_array($this->values[$this->previousLoopName][$what])
                ){
                $enter = true;
                $workingArray = $this->values[$this->previousLoopName][$what];
            }
            if($enter){
                $this->debug('We enter the "'.$what.'" loop',3,__LINE__);
                $backupValues = $this->values;
                $backupLoopName = $this->previousLoopName;

                foreach($workingArray as $key=>$entry){
                    $oldLoopId = $this->actualLoopId;
                    $this->actualLoopId = $key;
                    $this->previousLoopName = $what;
                    $this->values = array_merge(
                        $backupValues,
                        array($what => $entry)
                    );
                    $this->enterChildren($tag,$dest,$debugTime);
                    $this->actualLoopId = $oldLoopId;
                }
                $this->previousLoopName = $backupLoopName;
                $this->values = $backupValues;
                $this->actualLoopName = $oldLoopName;
                $this->debug('We exit the "'.$what.'" loop',3,__LINE__);
            }else{
                $this->debug('"'.$what.'" is not an array, so we can\'t loop in',0,__LINE__);
            }
            return true;
        }
        $this->debug('There was not "what" attribute to loop in',0,__LINE__);
        return false;
    }

    /**
     * Creates a table of n columns.
     */
    protected function createTable($tag,$dest){
        $this->debug('We apply the function '.__FUNCTION__. ' on the tag "'.$tag->nodeName.'"',3,__LINE__);
        $tableParams = '';
        foreach ($tag->attributes as $attribute){
            if($attribute->name == 'what'){
                $what = strtolower($attribute->value);
            }elseif($attribute->name == 'cols'){
                $cols = $attribute->value;
            }elseif($attribute->name == 'opened'){
                $opened = $attribute->value;
            }else{
                $tableParams .= ' '.$separator.$attribute->name.'="'.$attribute->value.'"';
            }
        }

        if($what && $cols){
            if(is_array($this->values[$what]) && !empty($this->values[$what])){
                $enter = true;
                $workingArray = $this->values[$what];
                $oldLoopName = $this->actualLoopName;
                $this->actualLoopName = $what;
            }elseif(
                    !is_null($this->previousLoopName)
                    && is_array($this->values[$this->previousLoopName][$what])
                    && !empty($this->values[$this->previousLoopName][$what])
                ){
                $enter = true;
                $workingArray = $this->values[$this->previousLoopName][$what];
            }
            if($enter){
                $this->debug('We enter the "'.$what.'" loop',3,__LINE__);
                $backupValues = $this->values;
                $backupLoopName = $this->previousLoopName;

                if($opened){
                    $tableTag = $dest;
                }else{
                    $tableTag = $this->insertTag($dest,'<table'.$tableParams.'></table>');
                }

                $cpt = 0;
                foreach($workingArray as $key=>$entry){
                    $cpt++;
                    if($cpt > $cols){
                        $cpt = 1;
                    }
                    if($cpt == 1){
                        $rowTag = $this->insertTag($tableTag,'<tr></tr>');
                    }
                    $oldLoopId = $this->actualLoopId;
                    $this->actualLoopId = $key;
                    $this->previousLoopName = $what;
                    $this->values = array_merge(
                        $backupValues,
                        array($what => $entry)
                    );
                    $cellTag = $this->insertTag($rowTag,'<td></td>');
                    $this->enterChildren($tag,$cellTag);
                    $this->actualLoopId = $oldLoopId;
                }
                $content .= '</table>';
                $this->previousLoopName = $backupLoopName;
                $this->values = $backupValues;
                $this->actualLoopName = $oldLoopName;
                $this->debug('We exit the "'.$what.'" loop',3,__LINE__);
            }else{
                $this->debug('"'.$what.'" is not an array, or is empty, so we can\'t loop in',0,__LINE__);
            }
            return true;
        }
        $this->debug('There was no "what" attribute to loop in',0,__LINE__);
        return false;
    }

    protected function insertTag($dest,$content){
        $tempNode = new domDocument('1.0', 'UTF-8');
        $tempNode->loadXML($content);
        $content = $tempNode->firstChild;
        $formNode = $this->xml[$_SESSION['rendering']]->importNode($content,true);
        $newTag = $dest->appendChild($formNode);
        return $newTag;
    }

    /**
     * protected function ifset
     *
     */
    protected function ifSet($tag,$dest){
        foreach ($tag->attributes as $attribute){
            if($attribute->name == 'what'){
                $what = strtolower($attribute->value);
                $what = $this->changeValue($what);
            }
        }
        $this->debug('We apply the function '.__FUNCTION__. ' on "'.$what.'"',3,__LINE__);
        if($what){
            $exploded = explode('>',$what);
            if(count($exploded) == 1){
                if(is_array($this->values[$what])){
                    $this->debug($what.' is an array',1,__LINE__);
                    $this->enterChildren($tag,$dest);
                }else{
                    $this->debug($what.' is not set, so we don\'t display the content',1,__LINE__);
                }
            }else{
                if(
                    isset($this->values[$exploded[0]][$exploded[1]])
                    && trim($this->values[$exploded[0]][$exploded[1]]) != ''
                ){
                    $this->debug($what.' is set to '.$this->values[$exploded[0]][$exploded[1]],1,__LINE__);
                    $this->enterChildren($tag,$dest);
                }else{
                    if($exploded[0] == 'session'){
                        if(($exploded[1] == 'admin' && $this->isAdmin()) ||
                           ($exploded[1] == 'master' && $this->isMaster())){
                            $this->debug('We are allowed to show this part',1,__LINE__);
                            $this->enterChildren($tag,$dest);
                        }
                    }
                    $this->debug($what.' is not set, so we don\'t display the content',1,__LINE__);
                }
            }
            return true;
        }
        $this->debug('We didn\'t find the class name (in attribute "what")',0,__LINE__);
        return false;
    }

    /**
     * protected function ifNotSet
     *
     */
    protected function ifNotSet($tag,$dest){
        $this->debug('We apply the function '.__FUNCTION__. ' on the tag "'.$tag->nodeName.'"',3,__LINE__);
        foreach ($tag->attributes as $attribute){
            if($attribute->name == 'what'){
                $what = strtolower($attribute->value);
            }
        }
        $this->debug('We verify that "'.$what.'" is not set',3,__LINE__);
        if($what){
            $exploded = explode('>',$what);
            if(count($exploded) == 1){
                if(is_array($this->values[$what])){
                    $this->debug($what.' is an array, so we don\'t display the content',1,__LINE__);
                }else{
                    $this->debug($what.' is not set',1,__LINE__);
                    $this->enterChildren($tag,$dest);
                }
            }else{
                if(
                    isset($this->values[$exploded[0]][$exploded[1]])
                    && trim($this->values[$exploded[0]][$exploded[1]]) != ''
                ){
                    $this->debug($what.' is set, so we don\'t display the content',1,__LINE__);
                }else{
                    $this->debug($what.' is not set, so we display the content',1,__LINE__);
                    $this->enterChildren($tag,$dest);
                }
            }
            return true;
        }
        $this->debug('We didn\'t find the class name (in attribute "what")',0,__LINE__);
        return false;
    }

    /**
     * protected function enterTag
     *
     */
    protected function enterTag($tag, $dest){
        $this->debug('We enter the "'.$tag->nodeName.'" tag',3,__LINE__);
        $node = $this->xml[$_SESSION['rendering']]->createElement($tag->nodeName);
        $dest->appendChild($node);
        foreach ($tag->attributes as $attribute){
            $this->debug('We add the "'.$attribute->name.'" attribute',3,__LINE__);
            $changedValue = $this->changeValue($attribute->value);
            $attributeName = $attribute->name;
            if($attribute->name == 'state'){
                $this->debug('We set a state',3,__LINE__);
                if($changedValue == ''){
                    continue;
                }
                $attributeName = $changedValue;
            }

            $root_attr1 = $this->xml[$_SESSION['rendering']]->createAttribute(
                $attributeName
            );
            $node->appendChild($root_attr1);

            $root_text = $this->xml[$_SESSION['rendering']]->createTextNode(
                $changedValue
            );
            $root_attr1->appendChild($root_text);
        }

        $this->enterChildren($tag,$node);
        return $text;
    }

    /**
     * protected function enterChildren
     *
     */
    protected function enterChildren($tag, $dest,$debugTime = false){
        $this->debug('We look for the children of "'.$tag->nodeName.'"',3,__LINE__);
        foreach ($tag->childNodes as $item){
            if($debugTime){
                $time = microtime(true);
                $this->debug('Loop debug time : '.$time,1,__LINE__);
            }
            if($item->nodeName != '#text'){
                $upperNodeName = strtoupper($item->nodeName);
                if(isset($this->methods[$upperNodeName])){
                    $method = $this->methods[$upperNodeName];
                    if(method_exists($this, $method)){
                        $this->$method($item,$dest);
                    }else{
                        $this->debug('The "'.$method.'" method doesn\'t exist in the "'.$item->nodeName.'" class',0,__LINE__);
                    }
                }elseif(isset($this->plugins[$upperNodeName])){
                    $class = $this->plugins[$upperNodeName]['class'];
                    $method = $this->plugins[$upperNodeName]['method'];
                    if(method_exists($this->links->$class->getClassName(),$method)){
                        $attributes = array();
                        foreach ($item->attributes as $attribute){
                            $attributes[$attribute->name] = $this->changeValue(
                                $attribute->value
                            );
                        }

                        // Changes the values that are in the content of the tag
                        $tempXML = $this->xml[$_SESSION['rendering']];
                        $this->xml[$_SESSION['rendering']] = new domDocument(
                            '1.0',
                            'UTF-8'
                        );
                        $this->xml[$_SESSION['rendering']]->loadXML('<RF></RF>');
                        $content = $this->xml[$_SESSION['rendering']]->firstChild;
                        $this->enterChildren($item,$content);
                        $oldContents = $content->nodeValue;
                        $this->xml[$_SESSION['rendering']] = $tempXML;
                        $newContent = $this->links->$class->$method(
                            $attributes,
                            $oldContents,
                            $this->values
                        );
                        // If the function returns true, we don't have to loop in it.
                        if($newContent && $newContent !== true){
                            $tempNode = new domDocument('1.0', 'UTF-8');
                            $tempNode->loadXML('<RF>'.$newContent.'</RF>');
                            $content = $tempNode->firstChild;
                            $this->enterChildren($content, $dest);
                        }
                    }else{
                        $this->debug('The "'.$method.'" method doesn\'t exist in the "'.$this->links->$class->getClassName().'" class',0,__LINE__);
                    }

                }else{
                    $this->enterTag($item,$dest);
                }
            }else{
                if(trim($item->nodeValue) != ''){
                    $this->debug('We add the text value "'.$item->nodeValue.'"',3,__LINE__);
                    $textNode = $this->xml[$_SESSION['rendering']]->createTextNode(
                        $item->nodeValue
                    );
                    $child = $dest->appendChild($textNode);
                }
            }
        }
        $this->debug('End of the loop on the children of "'.$tag->nodeName.'"',3,__LINE__);
    }

    /**
     * protected function createTag
     *
     */
    protected function createTag($tag,$dest){
        $this->debug('We create the tag "'.$tag->nodeName.'"',3,__LINE__);
        $args = '';
        foreach ($tag->attributes as $attribute){
            if($attribute->name == 'what'){
                list($class, $element) = explode(
                    '>',
                    strtolower($attribute->value)
                );
            }elseif($attribute->name == 'type'){
                $type = strtolower($this->changeValue($attribute->value));
            }else{
                $changedValue = $this->changeValue($attribute->value);
                $args .= ' '.$attribute->name.'="'.$changedValue.'"';
            }
        }
        if(!isset($type)){
            $this->debug('We didn\'t find the type',0,__LINE__);
            return false;
        }
        if(isset($class) && isset($element)){
            if(isset($this->values[$class][$element])){
                $tempNode = new domDocument('1.0', 'UTF-8');
                $tempNode->loadXML(
                    '<'.$type.' '.$this->values[$class][$element].$args.'/>'
                );
                $content = $tempNode->firstChild;
                $newNode = $this->xml[$_SESSION['rendering']]->importNode(
                    $content,
                    true
                );
                $newTag = $dest->appendChild($newNode);
            }else{
                $node = $this->xml[$_SESSION['rendering']]->createElement($type);
                $newTag = $dest->appendChild($node);
            }
        }
        // Adds analytics scripts and admin panel to the body tag
        if($type == 'body'){
            $addedContent = '
<RENDER_IFSET what="body>adminPanel"><RENDER_VALUE what="body>adminPanel"/></RENDER_IFSET>
<RENDER_IFSET what="body>analytics"><RENDER_VALUE what="body>analytics"/></RENDER_IFSET>';
            $tempNode = new domDocument('1.0', 'UTF-8');
            $tempNode->loadXML('<div>'.$addedContent.'</div>');
            $content = $tempNode->firstChild;
            $newNode = $this->oldXmlDoc->importNode($content,true);
            $addedTag = $tag->appendChild($newNode);
        }
        // Enters the source node, if not empty
        if($tag->hasChildNodes()){
            $this->enterChildren($tag, $newTag);
        }

    }

    /**
     * protected function changeValue
     *
     */
    protected function changeValue($valueToChange,$secondLevel = false){
        $old = $valueToChange;
        if(preg_match('`(.*)\{([^>]+)\}(.*)`',$valueToChange,$matches)){
            $element = strtolower($matches[2]);
            $value = trim($this->values[$element]);
            $ret = $matches[1].$value.$matches[3];
            $valueToChange = $this->changeValue($ret,true);
        }
        
        if(preg_match('`(.*)\{([^>]+)>([^\}]+)\}(.*)`',$valueToChange,$matches)){
            $class = strtolower($matches[2]);
            $element = strtolower($matches[3]);
            if($class == 'i18n'){
                return $this->links->i18n->get(
                    $this->i18nClasses[$_SESSION['rendering']],$matches[3]
                );
            }
            if($class == 'constants' && defined(strtoupper($matches[3]))){
                $value = constant(strtoupper($matches[3]));
            }
            if($class == 'constants' && defined(strtoupper($matches[3]))){
                $value = constant(strtoupper($matches[3]));
            }else{
                $value = trim($this->values[$class][$element]);
            }
            $ret = $matches[1].$value.$matches[4];
            $valueToChange = $this->changeValue($ret,true);
        }elseif(
            $this->actualLoopName
            && preg_match('`(.*)\{'.$this->actualLoopName.'\}(.*)`',$valueToChange,$matches)
        ){
            $ret = $matches[1].$this->actualLoopId.$matches[2];
            $valueToChange = $this->changeValue($ret,true);
        }
        if(!$secondLevel){
            if($old != $valueToChange){
                $this->debug(__FUNCTION__.' - We replace "'.$old.'" with "'.$valueToChange.'"',2,__LINE__);
            }else{
                $this->debug(__FUNCTION__.' - We don\'t replace "'.$old.'"',2,__LINE__);
            }
        }
        return $valueToChange;
    }

    /**
     * protected function replaceValue
     * Called with the tag :
     * <RENDER_VALUE what="[class]>[varName]" />
     * eg. : <RENDER_VALUE what="body>beginning" />
     */
    protected function replaceValue($tag,$dest){
        // We only take care of the "what" attribute
        $what = $tag->getAttribute('what');
        if(trim($what) != ''){
            $this->debug('We want to replace the value of "'.$what.'"',3,__LINE__);
            $content = $this->changeValue($what);
            list($class, $element) = explode('>',strtolower($content));
            if($element == ''){
                $textContent = trim($this->values[$class]);
            }else{
                if($class == 'i18n'){
                    $value = '<content>'.$this->links->i18n->get(
                        $this->i18nClasses[$_SESSION['rendering']],
                        $element
                    ).'</content>';
                    $tempNode = new domDocument('1.0', 'UTF-8');
                    $tempNode->loadXML($value);
                    $content = $tempNode->firstChild;

                    $this->enterChildren($content, $dest);
                    return true;
                }elseif($class == 'constants' && defined(strtoupper($element))){
                    $textContent = constant(strtoupper($element));
                }else{
                    $textContent = trim($this->values[$class][$element]);
                }
            }
            if($textContent != ''){
                // We take the text as an xml part, even if it is not
                $value = '<content>'.$textContent.'</content>';
                $tempNode = new domDocument('1.0', 'UTF-8');
                $tempNode->loadXML($value);
                $content = $tempNode->firstChild;
                $this->enterChildren($content, $dest);
                return true;
            }else{
                // Error : [varName] does not exist in [class]
                $this->debug('We didn\'t find any value "'.$element.'" in the "'.$class.'" class',1,__LINE__);
                return false;
            }
        }
        // Error : There was no "what" attribute
        $this->debug('We didn\'t find any "what" attribute for a '.__FUNCTION__,0,__LINE__);

        return false;
    }

    /**
     * protected function getModel
     * Gets the model from the file given as a parameter
     */
    protected function getModel($model){
        $model = $this->links->html->replaceTemplateDir($model);
        if(file_exists($model)){
            $ret = file_get_contents($model);
        }
        if(file_exists('.'.$model)){
            $ret = file_get_contents('.'.$model);
        }
        if(file_exists(SH_CLASS_FOLDER.'/'.$model)){
            $ret = file_get_contents(SH_CLASS_FOLDER.'/'.$model);
        }
        $ret = preg_replace(array("`\n`",'` +`'),array('',' '),$ret);
        return $ret;
    }

    /**
     * public function enableCaptchas
     */
    public function enableCaptchas($status){
        $this->captchasEnabled = $status;
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring(){
        return get_class();
    }
}