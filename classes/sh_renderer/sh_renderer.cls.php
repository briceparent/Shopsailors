<?php

/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if( !defined( 'SH_MARKER' ) ) {
    header( 'location: directCallForbidden.php' );
}

/**
 * Class that renders the html from the templates files (.rf.xml files).
 * May be extended using plugins.
 */
class sh_renderer extends sh_core {

    const CLASS_VERSION = '1.1.11.04.18';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    protected $actions;
    protected $captchasEnabled = true;
    protected $needsRenderer = false;
    protected $xml = array( );
    protected $i18nClasses = array( );
    protected $values = array( );
    protected $methods = array( );
    protected $plugins = array( );
    protected $previousLoopName = null;
    //protected $enableDb = false;
    protected $enableRenderer = false;
    protected $backupValues = array( );
    protected $actualLoopName = false;
    protected $actualLoopId = null;
    protected $renderFilesCache = array( );
    protected $timeDebugStatus = false;
    public $renderFiles = array( );
    protected $onlyRenderFiles = false;
    protected $auto_values = array( );

    /**
     * Initiates the object
     */
    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            if( version_compare( $installedVersion, '1.1.11.03.28', '<' ) ) {
                // Construction of the table
                $this->db_execute( 'render_tags_create_table', array( ) );
            }

            // We construct the classes we know that may need to be updated
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
        // Prepares the methods for the special tags
        $this->methods = array(
            'RENDER_VALUE' => 'replaceValue',
            'RENDER_IFSET' => 'ifSet',
            'RENDER_IFNOTSET' => 'ifNotSet',
            'RENDER_LOOP' => 'createLoop',
            'RENDER_TABLE' => 'createTable',
            'RENDER_TAG' => 'createTag',
            'RENDER_LOADMODULE' => 'loadModule',
            'RENDER_FORM' => 'createFormVerifier',
            'RENDER_DEBUG' => 'inPlaceDebugger',
            'RENDER_TRANSLATOR' => 'createTranslator',
            'RENDER_TEST' => 'test',
            'RENDER_DEBUGALL' => 'debugAll',
            'RENDER_STRUCTURE' => 'createStructure',
            'RENDER_TABGROUP' => 'createTabGroup',
            'RENDER_TAB' => 'createTab',
            'RENDER_NOTIF' => 'createNotif',
            'RENDER_ADMINBOX' => 'createAdminBox',
            'RENDER_ADMINBOXCONTENT' => 'createAdminBoxContent',
            'NORENDER' => 'comment',
            'RENDERED' => 'alreadyRendered',
            'RENDER_ADD_VALUES' => 'addValues',
            'RENDER_CACHE' => 'cache',
            'RENDER_PREPAREVALUES' => 'prepareValues',
            'RENDER_MODIFYVALUE' => 'modifyValue',
        );

        $this->getPlugins();

        $variation = $this->linker->site->variation;
        define( 'VARIATION', $variation );

        $this->renderFiles = $this->linker->template->getRenderFiles();

        $this->auto_values[ 'user_connected' ] = $this->isConnected();
        $this->auto_values[ 'user_isAdmin' ] = $this->isAdmin();
        $this->auto_values[ 'user_isMaster' ] = $this->isMaster();
        if( $this->auto_values[ 'user_connected' ] ) {
            $this->auto_values[ 'user_id' ] = $this->linker->user->getUserId();
        }

        return true;
    }

    public function showRenderTagHelp( $name, $desc, $args = array( ), $samples = array( ) ) {
        $style = '.renderTagHelp{border:1px solid red;width:600px;margin:10px;padding:5px;}';
        $style .= '.renderTagHelp .name{font-weight:bold;}';
        $style .= '.renderTagHelp .desc{margin-left:5px;margin-bottom:8px;}';
        $style .= '.renderTagHelp .argName{font-weight:bold;}';
        $style .= '.renderTagHelp .argDesc{margin-left:5px;margin-bottom:8px;}';



        $ret = '<style>' . $style . '</style>';
        $ret .= '<div class="renderTagHelp">';

        $ret .= '<div class="name">' . $name . '</div>';
        $ret .= '<div class="desc">' . $desc . '</div>';

        $ret .= '<div class="arg">';
        $ret .= '<div class="argName">help_me</div>';
        $ret .= '<div class="argDesc">Shows this help. Cancels every other arguments.</div>';
        $ret .= '</div>';

        foreach( $args as $argName => $desc ) {
            $ret .= '<div class="arg">';
            $ret .= '<div class="argName">' . $argName . '</div>';
            $ret .= '<div class="argDesc">' . $desc . '</div>';
            $ret .= '</div>';
        }

        foreach( $samples as $sampleCode => $desc ) {
            $ret .= '<div class="arg">';
            $ret .= '<div class="argName">' . $sampleCode . '</div>';
            $ret .= '<div class="argDesc">' . $desc . '</div>';
            $ret .= '</div>';
        }

        $ret .= '</div>';
        return $ret;
    }

    public function add_render_tag( $tag, $class, $method ) {
        if( !empty( $tag ) ) {
            $this->db_execute(
                'add_render_tag',
                array(
                'tag' => trim( strtoupper( $tag ) ),
                'class' => trim( $class ),
                'method' => trim( $method )
                )
            );
        }
        $this->getPlugins( true );
        return false;
    }

    protected function getPlugins( $forceReload = false ) {
        // Prepares the replacement of the plugins special tags
        // (plugins may not override this classe's methods, because they are
        // looked for only if none is found in this classes's methods).
        // They are declared using the add_render_tag() method.
        if( $forceReload || empty( $_SESSION[ __CLASS__ ][ 'plugins' ] ) ) {
            $_SESSION[ __CLASS__ ][ 'plugins' ] = array( );
            $plugins = $this->db_execute( 'get_render_tags', array( ) );
            if( is_array( $plugins ) ) {
                foreach( $plugins as $plugin ) {
                    $_SESSION[ __CLASS__ ][ 'plugins' ][ strtoupper( $plugin[ 'tag' ] ) ] = array(
                        'class' => $plugin[ 'class' ],
                        'method' => $plugin[ 'method' ]
                    );
                }
            }
        }
        $this->plugins = $_SESSION[ __CLASS__ ][ 'plugins' ];
    }

    /**
     * Pre-loads the classe's i18n files (stores the name to be able to load
     * them if needed).
     * @param str $class The class name
     * @return bool Always returns true
     */
    public function loadI18n( $class ) {
        $this->i18nClasses[ $_SESSION[ 'rendering' ] ] = $class;
        return true;
    }

    public function array_change_key_case_recursive( &$array ) {
        if( is_array( $array ) ) {
            $array = array_change_key_case( $array );
            foreach( $array as $key => $element ) {
                $array[ $key ] = $this->array_change_key_case_recursive( $element );
            }
        }
        return $array;
    }

    public function render_css( $file, $debug = false ) {
        if( is_dir( dirname( $debug ) ) ) {
            $this->debugging( 3, $debug );
        } else {
            $this->debugging( $debug );
        }
        $ret = '';
        $fromFile = false;
        if( file_exists( $file ) ) {
            if( SH_MOBILE_DEVICE ) {
                $newFileName = str_replace( '.css', '.' . SH_MOBILE_DEVICE . '.css', $file );
                if( file_exists( $newFileName ) ) {
                    $file = $newFileName;
                }
                if( SH_MOBILE_DEVICE != 'mobile' ) {
                    $newFileName = str_replace( '.css', '.mobile.css', $file );
                    if( file_exists( $newFileName ) ) {
                        $file = $newFileName;
                    }
                }
            }
            $fromFile = true;
            $fileContents = file_get_contents( $file );
        } else {
            $fileContents = $file;
        }

        $replacements = $this->linker->template->getPalettesColors( true );

        // We prepare the replacements
        $replacements[ 'suffix@images' ] = $this->linker->site->images_suffix;
        $replacements[ 'name@template' ] = $this->linker->site->templateName;
        $replacements[ 'name@variation' ] = $this->linker->site->variation;
        $replacements[ 'name@value' ] = $this->linker->site->saturation;
        $replacements[ 'name@site' ] = $this->linker->site->siteName;

        $newContents = str_replace(
            array_keys( $replacements ), array_values( $replacements ), $fileContents
        );

        $ret .= preg_replace(
            array( "`\n+`", "`\r`", '` +`', '`\*\/`', '`\}`' ), array( '', '', ' ', '*/' . "\n", '}' . "\n" ),
            $newContents
        );
        if( $fromFile ) {
            $ret .= '/* Rendered file : ' . basename( $file ) . ' - Date : ' . date( 'Y-m-d H:i:s' ) . ' */' . "\n\n";
        } else {
            $ret .= '/* Rendered string on ' . date( 'Y-m-d H:i:s' ) . ' */' . "\n\n";
        }
        return $ret;
    }

    /**
     * Main function - Renders the document
     * @param str $model The path and name of the .rf.xml file
     * @param array $values An array containing the values to replace.<br />
     * Defaults to an empty array.
     * @param bool|int $debug See sh_debug for informations about this.
     * @return str Returns the rendered contents.
     */
    public function render( $model, $values = '', $debug = false ) {
        if( is_dir( dirname( $debug ) ) ) {
            $this->debugging( 3, $debug );
        } else {
            $this->debugging( $debug );
        }

        $values = array_merge( $values, $this->linker->template->getPalettesColors() );
        $values[ 'auto' ] = $this->auto_values;
        $values[ 'auto' ][ 'page_short' ] = $this->linker->path->uri;
        $values[ 'auto' ][ 'page' ] = $this->linker->path->url;

        //Verifies that the file $model really exists
        if( file_exists( $model ) ) {
            // We check if a mobile version exists, and if this session is done on a mobile
            if( SH_MOBILE_DEVICE ) {
                $newFileName = str_replace( '.rf.xml', '.' . SH_MOBILE_DEVICE . '.rf.xml', $model );
                if( file_exists( $newFileName ) ) {
                    $model = $newFileName;
                }
                if( SH_MOBILE_DEVICE != 'mobile' ) {
                    $newFileName = str_replace( '.rf.xml', '.mobile.rf.xml', $model );
                    if( file_exists( $newFileName ) ) {
                        $model = $newFileName;
                    }
                }
            }

            if( !isset( $this->renderFilesCache[ $model ] ) ) {
                $this->renderFilesCache[ $model ] = new DOMDocument( '1.0', 'UTF-8' );
                $this->debug( 'We load the document "' . $model . '"', 3, __LINE__ );
                $this->renderFilesCache[ $model ]->load( $model );
            } else {
                $this->debug( 'We take the document "' . $model . '" from cache', 3, __LINE__ );
            }
            $this->oldXmlDoc = $this->renderFilesCache[ $model ];
            $enterLast = false;
        } else {
            $text = $model;
            $model = md5( microtime() );
            if( !isset( $this->renderFilesCache[ $model ] ) ) {
                $this->renderFilesCache[ $model ] = new DOMDocument( '1.0', 'UTF-8' );
                $this->debug( 'We load some xml contents from a string', 3, __LINE__ );
                $text = '<content>' . $text . '</content>';
                // If the content of $model is not an xml string, we exit with an error
                if( is_null( $this->renderFilesCache[ $model ] ) || !$this->renderFilesCache[ $model ]->loadXML( $text ) ) {
                    $this->oldXmlDoc = null;
                    $this->renderFilesCache[ $model ] = null;
                    $this->debug(
                        'The render file "' . $model . '" was not found.', 0, __LINE__
                    );
                    return false;
                }
            }
            $this->oldXmlDoc = $this->renderFilesCache[ $model ];
            $enterLast = true;
        }

        // We store the old replacement values
        $old_values = $this->values;
        // We set to lower case the keys from $values
        $this->values = $this->array_change_key_case_recursive( $values );

        // We store the old rendering id, and creates a new one
        $previousRendering = $_SESSION[ 'rendering' ];
        $rendering = $_SESSION[ 'rendering' ] = md5( microtime() );

        $this->debug(
            'We load the i18n file for the class ' . $this->i18nClasses[ $rendering ], 2, __LINE__
        );

        if( isset( $this->values[ 'i18n' ] ) ) {
            $this->i18nClasses[ $rendering ] = $this->values[ 'i18n' ];
        }


        $this->debug( 'We start a render process', 1, __LINE__ );

        $this->xml[ $rendering ] = new DOMDocument( '1.0', 'UTF-8' );
        $this->xml[ $rendering ]->resolveExternals = true;
        $this->defaultXMLVersion = $this->xml[ $rendering ]->xmlVersion;
        $racine = $this->oldXmlDoc->documentElement;

        // Let's render
        $this->enterTag( $racine, $this->xml[ $rendering ] );

        // Done
        // We don't want to send the xml declaration, so we don't send it
        if( !$enterLast ) {
            $xml = $this->xml[ $rendering ]->saveXML(
                $this->xml[ $rendering ]->documentElement
            );
        } else {
            //$xml = $this->xml[$rendering]->documentElement->firstChild->nodeValue;
            $xml = $this->xml[ $rendering ]->firstChild->nodeValue;
            if( $this->xml[ $rendering ]->documentElement->hasChildNodes() ) {
                $xml = $this->xml[ $rendering ]->saveXML(
                    $this->xml[ $rendering ]->documentElement->firstChild
                );
            } else {
                $xml = '';
            }
        }

        $this->debug( 'We have finished the render process', 1, __LINE__ );
        // Restores the previous unic rendering id
        $_SESSION[ 'rendering' ] = $previousRendering;

        $this->values = $old_values;
        return '<RENDERED>' . $xml . '</RENDERED>';
    }

    /**
     * protected function alreadyRendered
     * This is usefull to avoid to treat more than once some contents     *
     */
    protected function alreadyRendered( $tag, $dest ) {
        $this->debug( 'We copy the content of a RENDERED', 3, __LINE__ );
        $this->enterChildren( $tag, $dest );
    }

    public function toHtml( $renderedContent ) {
        // We replace some elements to create valid the xhtml
        $xhtml = preg_replace(
            array(
            // Auto-closed tags with parametters
            '`(<(style|head|select|a|link|script|div|span|p|ul|li|ol|table|tr|td|th|caption|textarea|h1|h2|h3|h4|h5|h6|h7|param|embed|meta|iframe) [^>]*)/>`',
            // Auto-closed tags without parametters
            '`(<(div|span|p|ul|li|ol|td|tr|th|caption|textarea|h1|h2|h3|h4|h5|h6|h7|strong))/>`',
            // New lines
            '`(<(br|hr) */>)`',
            // RENDERED tags (removal)
            '`(</?RENDERED>)`'/* ,
              // Beautifulizing - These 2 lines may be commented to increase performances
              '`(</([^>]+)>)`',
              '`(/>)`' */
            ),
            array(
            '$1></$2>',
            '$1></$2>',
            '<$2 />',
            ''/* ,
              '</$2>'."\n",
              '/>'."\n" */
            ), $renderedContent
        );
        return $xhtml;
    }

    /**
     * Enables a web-designer to create .rf.xml before the developper has
     * created the script that generates the $values.
     * @param DOMElement $tag The element to enter
     * @param DOMElement $dest The element in which the changes will be made
     * @return bool Always returns true
     */
    protected function test( $tag, $dest ) {
        $this->debug( 'We launch a rf tester', 3, __LINE__ );
        foreach( $tag->attributes as $attribute ) {
            if( $attribute->name == 'rf' ) {
                $rf = $attribute->value;
            } elseif( $attribute->name == 'values' ) {
                $valuesFile = $attribute->value;
            }
        }
        if( !file_exists( SH_ROOT_FOLDER . 'tests/' . $rf ) ) {
            $this->debug( 'Render file (' . $rf . ') does not exist', 1, __LINE__ );
            return false;
        }

        if( file_exists( SH_ROOT_FOLDER . 'tests/' . $valuesFile ) ) {
            include(SH_ROOT_FOLDER . 'tests/' . $valuesFile);
        }

        $rendered = $this->linker->renderer->render(
            SH_ROOT_FOLDER . 'tests/' . $rf, $values, $this->debugging()
        );
        $tempNode = new domDocument( '1.0', 'UTF-8' );
        $tempNode->loadXML( $rendered );
        $content = $tempNode->firstChild;
        $hiddenNode = $this->xml[ $_SESSION[ 'rendering' ] ]->importNode(
            $content, true
        );
        $dest->appendChild( $hiddenNode );
        return true;
    }

    /**
     * Do nothing with the contents of the tag. Is just here to enable the
     * .xf.xml to be commented.
     * @param DOMElement $tag Not used
     * @param DOMElement $dest Not used
     * @return bool Always returns true
     */
    protected function comment( $tag, $dest ) {
        $this->debug( 'We add a comment, so we do nothing', 3, __LINE__ );
        return true;
    }

    /**
     * Displays in the debug the list of all the values and their keys, that
     * could be replaced.
     * @param DOMElement $tag The element to enter
     * @param DOMElement $dest The element in which the changes will be made
     * @return bool Always returns true
     */
    protected function debugAll( $tag, $dest ) {
        if( $this->isAdmin() ) {
            $debugging = $this->debugging();
            $direct = false;
            foreach( $tag->attributes as $attribute ) {
                if( $attribute->name == 'direct' ) {
                    $direct = true;
                    if( $debugging != 3 ) {
                        $this->debugging( 3 );
                    }
                    $this->debug( 'We show all variables', 3, __LINE__ );
                    $this->debug( '-------BEGINNING-----', 3, __LINE__ );
                }
            }
            if( !$direct ) {
                // Creating the container
                $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'table' );
                $mainNode = $dest->appendChild( $node );
                $mainNode->setAttribute( 'class', 'debugTable' );

                $addedContent = 'RENDER_DEBUGALL';
                $tempNode = new domDocument( '1.0', 'UTF-8' );
                $tempNode->loadXML( '<tr><th colspan="2">' . $addedContent . '</th></tr>' );
                $content = $tempNode->firstChild;
                $newNode = $this->xml[ $_SESSION[ 'rendering' ] ]->importNode( $content, true );
                $addedTag = $mainNode->appendChild( $newNode );
            }
            if( is_array( $this->values ) ) {
                foreach( $this->values as $key => $value ) {
                    $this->debugAll_element( $key, $value, $mainNode, $direct );
                }
            }

            if( $direct ) {
                $this->debug( '---------ENDING------', 3, __LINE__ );
                if( $debugging != 3 ) {
                    $this->debugging( $debugging );
                }
            }
        }
        return true;
    }

    /**
     * Method used by debugAll to debug the content in lines.
     * @param str $key The name of the key.
     * @param str $element The content of the value.
     */
    protected function debugAll_element( $key, $element, $dest, $direct = false ) {
        if( !is_array( $element ) ) {
            if( $direct ) {
                $this->debug( $key . ' = ' . $element, 3, 0, false );
            } else {
                // Creating the container
                $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'tr' );
                $mainNode = $dest->appendChild( $node );

                $tempNode = new domDocument( '1.0', 'UTF-8' );
                $tempNode->loadXML( '<td>' . htmlentities( $key ) . '</td>' );
                $content = $tempNode->firstChild;
                $newNode = $this->xml[ $_SESSION[ 'rendering' ] ]->importNode( $content, true );
                $addedTag = $mainNode->appendChild( $newNode );

                $tempNode = new domDocument( '1.0', 'UTF-8' );
                $tempNode->loadXML( '<td> = ' . preg_replace( '`&(\#?[a-zA-Z0-9]{3,5}[^;]{1})`', '&#38;$1', $element ) . '</td>' );
                $content = $tempNode->firstChild;
                $newNode = $this->xml[ $_SESSION[ 'rendering' ] ]->importNode( $content, true );
                $addedTag = $mainNode->appendChild( $newNode );
            }
        } else {
            foreach( $element as $newKey => $value ) {
                $this->debugAll_element( $key . ':' . $newKey, $value, $dest, $direct );
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
    protected function createStructure( $tag, $dest ) {
        $this->debug( 'We show the structure of the file', 3, __LINE__ );
        $showContent = false;
        foreach( $tag->attributes as $attribute ) {
            if( $attribute->name == 'showContent' ) {
                if(
                    strtolower( $attribute->value ) == 'true'
                    || strtolower( $attribute->value ) == 'showcontent'
                ) {
                    $showContent = true;
                }
            }
        }

        if( is_array( $this->values ) ) {
            echo '<div style="font-family:Courier New;">';
            foreach( $this->values as $key => $values ) {
                if( is_array( $values ) ) {
                    echo $key . '<br />';
                    $this->createStructure_element(
                        $this->values, $key, '', '', $showContent
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
    protected function createStructure_element( $element, $key = '', $parentKey = '', $indent = '',
                                                $showContent = false, $onlyOnce = false ) {
        if( is_array( $element ) && $key != 'i18n' && $parentKey != 'i18n' ) {
            $onlyOnceCounter = 0;
            if( isset( $element[ 0 ] ) ) {
                $onlyOnce = (isset( $values[ 0 ] ));
            }
            foreach( $element as $newKey => $value ) {
                if( $onlyOnce && $onlyOnceCounter > 0 ) {
                    return true;
                }
                $onlyOnceCounter++;

                if( !is_array( $value ) ) {
                    if( $newKey != 'i18n' ) {
                        echo $indent . '&#60;RENDER_VALUE what="' . $key . '&#62;' . $newKey . '"/&#62<br />';
                        if( $showContent ) {
                            echo $indent . '<span style="border:1px solid blue">' . $this->createStructure_cleanOutput( $value ) . '</span><br />';
                        }
                    }
                } else {
                    $newIndent = $indent . '&#160;&#160;&#160;&#160;';
                    foreach( $value as $oneKey => $oneValue ) {
                        echo $indent . '&#60;RENDER_LOOP what="' . $key . '"&#62;<br />';
                        $this->createStructure_element( $oneValue, $key, $key, $newIndent, $showContent );
                        echo $indent . '&#60;/RENDER_LOOP&#62;<br />';
                    }
                }
            }
        } elseif( $key != 'i18n' && $parentKey != 'i18n' ) {
            echo $indent . '&#60;RENDER_VALUE what="' . $key . '&#62;' . $parentKey . '"/&#62<br />';
            if( $showContent ) {
                echo $indent . '<span style="border:1px solid blue">' . $this->createStructure_cleanOutput( $element ) . '</span><br />';
            }
        }
    }

    /**
     * The same as createStructure.
     * @todo Finish this function
     */
    protected function createStructure_cleanOutput( $output ) {
        $search = array( '`<`', '`>`' );
        $replace = array( '&#60;', '&#62' );
        return preg_replace( $search, $replace, $output );
    }

    protected function createTabGroup( $tag, $dest ) {
        $this->debug( 'We create a tab group', 3, __LINE__ );
        $addToClass = '';

        foreach( $tag->attributes as $attribute ) {
            if( $attribute->name == 'size' ) {
                $size = $this->changeValue( strtoupper( $attribute->value ) );
            }
            if( $attribute->name == 'validate' ) {
                $validateText = $this->changeValue( $attribute->value );
                $needsButtons = true;
            }
            if( $attribute->name == 'onvalidate' ) {
                $validateAction = $this->changeValue( $attribute->value );
            }
            if( $attribute->name == 'cancel' ) {
                $cancelText = $this->changeValue( $attribute->value );
                $needsButtons = true;
            }
            if( $attribute->name == 'oncancel' ) {
                $cancelAction = $this->changeValue( $attribute->value );
            }
            if( $attribute->name == 'previous' ) {
                $previousText = $this->changeValue( $attribute->value );
                $needsButtons = true;
            }
            if( $attribute->name == 'onprevious' ) {
                $previousAction = $this->changeValue( $attribute->value );
            }
            if( $attribute->name == 'next' ) {
                $nextText = $this->changeValue( $attribute->value );
                $needsButtons = true;
            }
            if( $attribute->name == 'onnext' ) {
                $nextAction = $this->changeValue( $attribute->value );
            }
            if( $attribute->name == 'admin' && $attribute->value == 'admin' ) {
                $isAdminBox = true;
                $addToClass .= ' tabGroup_admin';
            }
            if( $attribute->name == 'wiki' ) {
                $hasWikiPage = true;
                $wiki = $attribute->value;
            }
            if( $attribute->name == 'type' && $attribute->value == 'false' ) {
                // This is a false tab group (there are links on the tabs, instead of javascript
                $isFalse = true;
            }
        }
        if( empty( $size ) ) {
            $size = 'L';
        }
        $_SESSION[ __CLASS__ ][ 'temp' ][ 'entered_tabGroup' ] = 'tabGroup_' . substr( md5( microtime() ), 0, 8 );

        // Main container (div)
        $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
        $mainContainer = $dest->appendChild( $node );
        $mainContainer->setAttribute( 'class', 'tabGroup tabGroup_' . $size . $addToClass );

        // tabs container (div)
        $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
        $_SESSION[ __CLASS__ ][ 'temp' ][ 'titlesNode' ] = $mainContainer->appendChild( $node );
        $_SESSION[ __CLASS__ ][ 'temp' ][ 'titlesNode' ]->setAttribute( 'id',
                                                                        $_SESSION[ __CLASS__ ][ 'temp' ][ 'entered_tabGroup' ] );
        $_SESSION[ __CLASS__ ][ 'temp' ][ 'titlesNode' ]->setAttribute( 'class', 'tabGroup_title' );

        if( $isAdminBox ) {
            $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
            $adminTitleNode = $_SESSION[ __CLASS__ ][ 'temp' ][ 'titlesNode' ]->appendChild( $node );
            $adminTitleNode->setAttribute( 'class', 'tabGroup_adminTitle' );
            $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'img' );
            $adminTitleImage = $adminTitleNode->appendChild( $node );
            $adminTitleImage->setAttribute( 'src', '/templates/global/admin/shopsailors_tab.png' );
            $adminTitleImage->setAttribute( 'alt', ' ' );
            $adminTitleImage->setAttribute( 'title', $this->getI18n( 'admin_page' ) );
            $adminTitleImage->setAttribute( 'onclick', 'adminBoxShowHide();' );
        }

        // contents container (div)
        $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
        $_SESSION[ __CLASS__ ][ 'temp' ][ 'contentsNode' ] = $mainContainer->appendChild( $node );
        $_SESSION[ __CLASS__ ][ 'temp' ][ 'contentsNode' ]->setAttribute( 'class', 'tabGroup_container' );

        $oldTabGroupIsFalse = $this->tabGroupIsFalse;
        $oldTabsCount = $this->tabsCount;
        $this->tabsCount = 0;
        $this->tabGroupIsFalse = $isFalse;
        if( $tag->hasChildNodes() ) {
            $this->enterChildren( $tag, $mainContainer );
        }
        $this->tabsCount = $oldTabsCount;
        $this->tabGroupIsFalse = $oldTabGroupIsFalse;

        if( $hasWikiPage ) {
            $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
            $adminTitleNode = $_SESSION[ __CLASS__ ][ 'temp' ][ 'titlesNode' ]->appendChild( $node );
            $adminTitleNode->setAttribute( 'class', 'tabGroup_adminTitle' );

            $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'a' );
            $linkNode = $adminTitleNode->appendChild( $node );
            $linkNode->setAttribute( 'href', $wiki );
            $linkNode->setAttribute( 'target', '_blank' );

            $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'img' );
            $adminTitleImage = $linkNode->appendChild( $node );
            $adminTitleImage->setAttribute( 'src', '/templates/global/admin/icons/picto_wiki_link.png' );
            $adminTitleImage->setAttribute( 'alt', 'Shopsailors Wiki' );
            $adminTitleImage->setAttribute( 'title', 'Shopsailors Wiki' );
        }

        if( $needsButtons ) {
            // buttons container (div)
            $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
            $buttonsNode = $mainContainer->appendChild( $node );
            $buttonsNode->setAttribute( 'class', 'tab_buttons' );

            if( $validateText ) {
                $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'input' );
                $btnElNode = $buttonsNode->appendChild( $node );
                $btnElNode->setAttribute( 'class', 'tab_button btn_validate' );
                $btnElNode->setAttribute( 'name', 'submit' );
                $btnElNode->setAttribute( 'type', 'submit' );
                $btnElNode->setAttribute( 'value', $validateText );
                $btnElNode->setAttribute( 'onclick', $validateAction );
            }
            if( $cancelText ) {
                $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'input' );
                $btnElNode = $buttonsNode->appendChild( $node );
                $btnElNode->setAttribute( 'class', 'tab_button btn_cancel' );
                $btnElNode->setAttribute( 'name', 'cancel' );
                $btnElNode->setAttribute( 'type', 'submit' );
                $btnElNode->setAttribute( 'value', $cancelText );
                $btnElNode->setAttribute( 'onclick', $cancelAction );
            }
            if( $previousText ) {
                $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'input' );
                $btnElNode = $buttonsNode->appendChild( $node );
                $btnElNode->setAttribute( 'class', 'tab_button btn_previous' );
                $btnElNode->setAttribute( 'name', 'previous' );
                $btnElNode->setAttribute( 'type', 'submit' );
                $btnElNode->setAttribute( 'value', $previousText );
                $btnElNode->setAttribute( 'onclick', $previousAction );
            }
            if( $nextText ) {
                $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'input' );
                $btnElNode = $buttonsNode->appendChild( $node );
                $btnElNode->setAttribute( 'class', 'tab_button btn_next' );
                $btnElNode->setAttribute( 'name', 'next' );
                $btnElNode->setAttribute( 'type', 'submit' );
                $btnElNode->setAttribute( 'value', $nextText );
                $btnElNode->setAttribute( 'onclick', $nextAction );
            }
        }

        // spacer
        $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
        $mainNode = $_SESSION[ __CLASS__ ][ 'temp' ][ 'titlesNode' ]->appendChild( $node );
        $mainNode->setAttribute( 'class', 'noFloat' );

        $_SESSION[ __CLASS__ ][ 'temp' ][ 'entered_tabGroup' ] = false;
        $_SESSION[ __CLASS__ ][ 'temp' ][ 'selected_tabGroup' ] = false;
    }

    protected function createTab( $tag, $dest ) {
        $this->debug( 'We create a tab', 3, __LINE__ );

        $isImage = false;
        foreach( $tag->attributes as $attribute ) {
            if( $attribute->name == 'title' ) {
                $title = $this->changeValue( $attribute->value );
                $hasTitle = true;
            }
            if( $attribute->name == 'type' && strtolower( $attribute->value ) == 'image' ) {
                $isImage = true;
            }
            if( $attribute->name == 'name' ) {
                $name = $this->changeValue( $attribute->value );
            }
            if( $attribute->name == 'value' ) {
                $value = $this->changeValue( $attribute->value );
            }
            if( $attribute->name == 'src' ) {
                $imageSrc = $this->changeValue( $attribute->value );
            }
            if( $attribute->name == 'icon' ) {
                $icon = $this->changeValue( $attribute->value );
            }
            if( $attribute->name == 'link' ) {
                // This is a link tab, when we click on it, it loads the page in this attribute
                $link = $this->changeValue( $attribute->value );
            }
            if( $attribute->name == 'inactive' ) {
                // The text or image is shown, but no action occurs when clicked
                $inactive = true;
            }
            if( $attribute->name == 'id' ) {
                $thisUID = $this->changeValue( $attribute->value );
            } else {
                $thisUID = 'tab_' . substr( md5( microtime() ), 0, 8 );
            }
        }

        if( $inactive ) {
            $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
            $mainNode = $_SESSION[ __CLASS__ ][ 'temp' ][ 'titlesNode' ]->appendChild( $node );

            $mainNode->setAttribute( 'class', 'tabGroup_containerTitleInactive ' . $tabGroup . '_titles' );
            $mainNode->setAttribute( 'id', $thisUID );
            if( !empty( $icon ) ) {
                $subNode = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'img' );
                $iconNode = $node->appendChild( $subNode );
                $iconNode->setAttribute( 'src', $icon );
                $iconNode->setAttribute( 'class', 'tabGroup_containerTitleIcon' );
            }
            $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createTextNode( $title );
            $mainNode->appendChild( $node );
            return true;
        }
        if( $link ) {
            $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
            $mainNode = $_SESSION[ __CLASS__ ][ 'temp' ][ 'titlesNode' ]->appendChild( $node );
            $mainNode->setAttribute( 'class', 'tabGroup_containerTitleButton' );
            $mainNode->setAttribute( 'id', $thisUID );

            $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'input' );
            $subNode = $mainNode->appendChild( $node );
            $subNode->setAttribute( 'type', 'submit' );
            $subNode->setAttribute( 'class', 'tabEntry_hideButton' );
            $subNode->setAttribute( 'name', $name );
            $subNode->setAttribute( 'value', $title );
            $subNode->setAttribute( 'title', $title );
            return true;
        }
        if( $this->tabGroupIsFalse && $this->tabsCount > 0 ) {
            // Error, there may only be one tab in this tab group
            $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
            $mainNode = $_SESSION[ __CLASS__ ][ 'temp' ][ 'titlesNode' ]->appendChild( $node );
            $mainNode->setAttribute( 'class', 'tabGroup_adminTitle' );
            $mainNode->setAttribute( 'id', $thisUID );

            $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'input' );
            $subNode = $mainNode->appendChild( $node );
            $subNode->setAttribute( 'type', 'image' );
            $subNode->setAttribute( 'name', $name );
            $subNode->setAttribute( 'value', $value );
            $subNode->setAttribute( 'src', '/images/shared/icons/picto_caution.png' );
            $subNode->setAttribute( 'title', $title );
            return false;
        }
        $this->tabsCount++;
        if( $isImage ) {
            $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
            $mainNode = $_SESSION[ __CLASS__ ][ 'temp' ][ 'titlesNode' ]->appendChild( $node );
            $mainNode->setAttribute( 'class', 'tabGroup_adminTitle' );
            $mainNode->setAttribute( 'id', $thisUID );

            $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'input' );
            $subNode = $mainNode->appendChild( $node );
            $subNode->setAttribute( 'type', 'image' );
            $subNode->setAttribute( 'name', $name );
            $subNode->setAttribute( 'value', $value );
            $subNode->setAttribute( 'src', $imageSrc );
            $subNode->setAttribute( 'title', $title );
            return true;
        }
        if( $hasTitle ) {
            // Without a title, nothing can be selected, so we only take care of the ones with title
            $tabGroup = $_SESSION[ __CLASS__ ][ 'temp' ][ 'entered_tabGroup' ];
            // titles
            $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
            $mainNode = $_SESSION[ __CLASS__ ][ 'temp' ][ 'titlesNode' ]->appendChild( $node );
            if( !$_SESSION[ __CLASS__ ][ 'temp' ][ 'selected_tabGroup' ] ) {
                $mainNode->setAttribute( 'class', 'tabGroup_containerTitle selected ' . $tabGroup . '_titles' );
            } else {
                $mainNode->setAttribute( 'class', 'tabGroup_containerTitle ' . $tabGroup . '_titles' );
            }

            $mainNode->setAttribute( 'id', $thisUID );
            $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createTextNode( $title );
            $mainNode->appendChild( $node );

            // contents
            $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
            $mainNode = $_SESSION[ __CLASS__ ][ 'temp' ][ 'contentsNode' ]->appendChild( $node );
            if( !$_SESSION[ __CLASS__ ][ 'temp' ][ 'selected_tabGroup' ] ) {
                $mainNode->setAttribute( 'class', 'tabGroup_content ' . $tabGroup . ' selected' );
            } else {
                $mainNode->setAttribute( 'class', 'tabGroup_content ' . $tabGroup );
            }
            $mainNode->setAttribute( 'id', $thisUID . '_content' );

            if( $tag->hasChildNodes() ) {
                $this->enterChildren( $tag, $mainNode );
            }
            $_SESSION[ __CLASS__ ][ 'temp' ][ 'selected_tabGroup' ] = true;
        }
    }

    protected function createNotif( $tag, $dest ) {
        $this->debug( 'We create a notification', 3, __LINE__ );

        foreach( $tag->attributes as $attribute ) {
            if( $attribute->name == 'id' ) {
                $id = $this->changeValue( $attribute->value );
                $hasId = true;
            } elseif( $attribute->name == 'title' ) {
                $title = $this->changeValue( $attribute->value );
                $hasTitle = true;
            } elseif( $attribute->name == 'size' ) {
                $size = $this->changeValue( $attribute->value );
            } elseif( $attribute->name == 'type' ) {
                $type = $this->changeValue( $attribute->value );
                if( strtolower( $type ) == 'alert' ) {
                    $alert = true;
                }
            }
        }
        if( empty( $size ) ) {
            $size = 'L';
        }
        // Creating the container
        $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
        $mainNode = $dest->appendChild( $node );
        if( !$alert ) {
            $mainNode->setAttribute( 'class', 'notif_container' );
        } else {
            $mainNode->setAttribute( 'class', 'notif_container_alert' );
        }
        if( $hasId ) {
            $mainNode->setAttribute( 'id', $id );
        }
        // Creating the top, and the title if necessary
        $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
        $topNode = $mainNode->appendChild( $node );
        $topNode->setAttribute( 'class', 'notif' . $size . '_top' );
        if( $hasTitle ) {
            $h3Content = '<h3>' . $title . '</h3>';
            $tempNode = new domDocument( '1.0', 'UTF-8' );
            $tempNode->loadXML( $h3Content );
            $content = $tempNode->firstChild;
            $hiddenNode = $this->xml[ $_SESSION[ 'rendering' ] ]->importNode( $content, true );
            $topNode->appendChild( $hiddenNode );
        }
        // Creating the middle and the content
        $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
        $contentNode = $mainNode->appendChild( $node );
        $contentNode->setAttribute( 'class', 'notif' . $size . '_middle' );
        $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
        $textContentNode = $contentNode->appendChild( $node );
        $textContentNode->setAttribute( 'class', 'notif' . $size . '_content' );
        if( $tag->hasChildNodes() ) {
            $this->enterChildren( $tag, $textContentNode );
        }
        // Creating the bottom
        $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
        $contentNode = $mainNode->appendChild( $node );
        $contentNode->setAttribute( 'class', 'notif' . $size . '_bottom' );
    }

    protected function createAdminBox( $tag, $dest ) {
        $this->debug( 'We create an admin box', 3, __LINE__ );

        foreach( $tag->attributes as $attribute ) {
            if( $attribute->name == 'title' ) {
                $title = $this->changeValue( $attribute->value );
                $hasTitle = true;
            }
            if( $attribute->name == 'folded' && $attribute->value == 'folded' ) {
                $folded = true;
            }
            if( $attribute->name == 'size' ) {
                $size = strtolower( $attribute->value );
            }
        }
        if( !in_array( $size, array( 's', 'm', 'l', 'xl' ) ) ) {
            $size = 'm';
        }

        // Creating the container
        $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
        $mainNode = $dest->appendChild( $node );
        $mainNode->setAttribute( 'class', 'form_box_container form_box_container_' . $size );

        // Creating the top, and the title if necessary
        $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
        $uid = md5( microtime() );
        $topNode = $mainNode->appendChild( $node );
        $topNode->setAttribute( 'class', 'form_box_top' );

        if( $hasTitle ) {
            $h3Content = '<h3 class="box_title">' . $title . '</h3>';
            $tempNode = new domDocument( '1.0', 'UTF-8' );
            $tempNode->loadXML( $h3Content );
            $content = $tempNode->firstChild;
            $hiddenNode = $this->xml[ $_SESSION[ 'rendering' ] ]->importNode( $content, true );
            $topNode->appendChild( $hiddenNode );
        }

        // Creating the middle and the content
        $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
        $contentNode = $mainNode->appendChild( $node );
        $contentNode->setAttribute( 'class', 'form_box_middle' );
        $contentNode->setAttribute( 'id', $uid );
        if( $folded ) {
            $contentNode->setAttribute( 'style', 'display:none;' );
        }
        if( $tag->hasChildNodes() ) {
            $this->enterChildren( $tag, $contentNode );
        }
        // Creating the bottom
        $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
        $contentNode = $mainNode->appendChild( $node );
        $contentNode->setAttribute( 'class', 'form_box_bottom' );
    }

    protected function createAdminBoxContent( $tag, $dest ) {
        $this->debug( 'We create an admin box', 3, __LINE__ );

        // Creating the container
        $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'div' );
        $mainNode = $dest->appendChild( $node );
        $mainNode->setAttribute( 'class', 'formContent' );
        if( $tag->hasChildNodes() ) {
            $this->enterChildren( $tag, $mainNode );
        }
    }

    /**
     * protected function createFormVerifier
     *
     */
    protected function createFormVerifier( $tag, $dest ) {
        $this->debug( 'We create a form verifier', 3, __LINE__ );
        foreach( $tag->attributes as $attribute ) {
            $attributeValue = $this->changeValue( $attribute->value );
            if( $attribute->name == 'id' ) {
                $formIdIsSet = true;
                $formId = $attributeValue;
                $attributes .= ' id="' . $attributeValue . '"';
            } elseif( $attribute->name == 'method' ) {
                $methodIsSet = true;
                if( !empty( $attributeValue ) ) {
                    $attributes .= ' ' . $attribute->name . '="' . $attributeValue . '"';
                }
            } elseif( $attribute->name == 'type' ) {
                if( strtolower( $attribute->value ) == 'file' ) {
                    $attributes .= ' enctype="multipart/form-data"';
                }
            } elseif( $attribute->name != 'name' ) {
                // We don't copy any "name" attribute because there is not any in xhtml
                $attributes .= ' ' . $attribute->name . '="' . $attributeValue . '"';
            }
        }
        if( !$methodIsSet ) {
            $attributes .= ' method="POST"';
        }
        if( !$formIdIsSet ) {
            $this->debug( 'We have no "id" attribute in the "RENDER_FORM" tag so no verifier can be used!', 0, __LINE__ );
            $formId = 'error';
        }
        $xmlTag = '<form' . $attributes . '><div></div></form>';
        $tempNode = new domDocument( '1.0', 'UTF-8' );

        $tempNode->loadXML( $xmlTag );
        $content = $tempNode->firstChild;
        $formNode = $this->xml[ $_SESSION[ 'rendering' ] ]->importNode( $content, true );
        $newTag = $dest->appendChild( $formNode );
        $newContentTag = $newTag->firstChild;

        $value = self::$form_verifier->create( $formId );
        $hidden = '<input type="hidden" name="verif" value="' . $value . '" />';
        $tempNode = new domDocument( '1.0', 'UTF-8' );
        $tempNode->loadXML( $hidden );
        $content = $tempNode->firstChild;
        $hiddenNode = $this->xml[ $_SESSION[ 'rendering' ] ]->importNode( $content, true );
        $newContentTag->appendChild( $hiddenNode );

        if( $tag->hasChildNodes() ) {
            $this->enterChildren( $tag, $newContentTag );
        }
    }

    /**
     * protected function inPlaceDebugger
     *
     */
    protected function inPlaceDebugger( $tag, $dest ) {
        $this->debug( 'We apply the function ' . __FUNCTION__ . ' on the tag "' . $tag->nodeName . '"', 3, __LINE__ );
        foreach( $tag->attributes as $attribute ) {
            if( $attribute->name == 'level' ) {
                $level = $attribute->value;
            }
            if( $attribute->name == 'debugTime' ) {
                $setTimeDebug = true;
                $oldTimeDebugStatus = $this->timeDebugStatus;
                $this->timeDebugStatus = true;
            }
        }
        if( !isset( $level ) ) {
            $this->debug( 'We could\'nt find the debug level', 1, __LINE__ );
            return false;
        }
        $previousDebugLevel = $this->debugging();
        $this->debugging( $level );
        $this->debug( 'We change the debug level temporarly to  "' . $level . '"', 1, __LINE__ );
        $this->enterChildren( $tag, $dest );

        if( $setTimeDebug ) {
            $this->timeDebugStatus = $oldTimeDebugStatus;
        }

        $this->debug( 'We put back the debug level to  "' . $previousDebugLevel . '"', 1, __LINE__ );
        $this->debugging( $previousDebugLevel );
        return true;
    }

    /**
     * protected function addValues
     *
     */
    protected function addValues( $tag, $dest ) {
        foreach( $tag->attributes as $attribute ) {
            if( $attribute->name == 'file' ) {
                $file = $attribute->value;
            }
        }
        if( !isset( $file ) || (!file_exists( $file ) && !file_exists( SH_ROOT_FOLDER . $file )) ) {
            $this->debug( 'The "file" argument must be set to an existing php file!', 0, __LINE__ );
            return false;
        }
        $this->debug( 'We add the values from the file "' . $file . '"', 3, __LINE__ );
        if( file_exists( $file ) ) {
            include($file);
        } else {
            include(SH_ROOT_FOLDER . $file);
        }

        $values = $this->array_change_key_case_recursive( $values );
        $this->values = array_merge( $this->values, $values );
        return true;
    }

    protected function debug(
    $text, $level = sh_debugger::LEVEL, $line = sh_debugger::LINE, $showClassName = sh_debugger::SHOWCLASS
    ) {
        if( $this->timeDebugStatus ) {
            $text = '(' . substr( microtime( true ), -5 ) . ') ' . $text;
        }
        return $this->debugger->debug( $text, $level, $line, $showClassName );
    }

    /**
     * protected function loadModule
     *
     */
    protected function loadModule( $tag, $dest ) {
        $this->debug( 'We apply the function ' . __FUNCTION__ . ' on the tag "' . $tag->nodeName . '"', 3, __LINE__ );
        foreach( $tag->attributes as $attribute ) {
            ${$attribute->name} = $attribute->value;
        }
        if( !isset( $module ) ) {
            $this->debug( 'We could\'nt find the module to load', 1, __LINE__ );
            return false;
        }

        $this->debug( 'We load the module "' . $module . '"', 1, __LINE__ );

        $this->linker->$module->loadModule( $params );
        return true;
    }

    /**
     * protected function createHelp
     *
     */
    protected function createHelp( $tag, $dest ) {
        $this->debug( 'We apply the function ' . __FUNCTION__ . ' on the tag "' . $tag->nodeName . '"', 3, __LINE__ );
        foreach( $tag->attributes as $attribute ) {
            if( $attribute->name == 'id' ) {
                $id = $attribute->value;
                $this->debug( 'An old version of RENDER_HELP was used for a div named "' . $attribute->value . '".', 1,
                              __LINE__ );
                return false;
            }
            if( $attribute->name == 'what' ) {
                $what = $attribute->value;
            }
        }
        if( !isset( $what ) ) {
            $this->debug( 'We could\'nt find the id of the destination div', 1, __LINE__ );
            return false;
        }
        $content = $this->changeValue( '{' . $what . '}' );

        $id = substr( MD5( __CLASS__ . microtime() ), 0, 12 );
        $tempNode = new domDocument( '1.0', 'UTF-8' );
        $xml = '
<help>
    <img src="/images/shared/icons/help.png" class="aLink" onclick="UnTip();TagToTip(\'' . $id . '\',BORDERCOLOR,\'#003366\',TITLEBGCOLOR,\'#336699\', BALLOON, true, ABOVE, true)"/>
    <div id="' . $id . '">
        <div class="render_help_explanation">
        ' . $content . '
        </div>
    </div>
</help>
';
        $tempNode->loadXML( $xml );
        $content = $tempNode->firstChild;
        $this->enterChildren( $content, $dest );
        return true;
    }

    /**
     * protected function createTranslator
     *
     */
    protected function createTranslator( $tag, $dest ) {
        $this->debug( 'We apply the function ' . __FUNCTION__ . ' on the tag "' . $tag->nodeName . '"', 3, __LINE__ );
        foreach( $tag->attributes as $attribute ) {
            if( $attribute->name == 'id' ) {
                $id = $attribute->value;
            }
        }
        if( !isset( $id ) ) {
            $this->debug( 'We could\'nt find the id of the destination div', 1, __LINE__ );
            return false;
        }
        $this->debug( 'We build a help element for the div "' . $id . '"', 2, __LINE__ );
        $tempNode = new domDocument( '1.0', 'UTF-8' );
        $tempNode->loadXML(
            '<help><img src="/images/shared/icons/help.png" class="aLink" onclick="UnTip();TagToTip(\'' . $id . '\',BORDERCOLOR,\'#003366\',TITLEBGCOLOR,\'#336699\', BALLOON, true, ABOVE, true)"/></help>'
        );
        $content = $tempNode->firstChild;
        $this->enterChildren( $content, $dest );

        return true;
    }

    /**
     * public function getXmlVersionTag
     *
     */
    public function getXmlVersionTag() {
        return '<?xml version="' . $this->xml[ $_SESSION[ 'rendering' ] ]->xmlVersion . '"?>';
    }

    /**
     * protected function createLoop
     *
     */
    protected function createLoop( $tag, $dest ) {
        $this->debug( 'We apply the function ' . __FUNCTION__ . ' on the tag "' . $tag->nodeName . '"', 3, __LINE__ );
        foreach( $tag->attributes as $attribute ) {
            if( $attribute->name == 'what' ) {
                $what = strtolower( $attribute->value );
            } elseif( $attribute->name == 'from' ) {
                $from = ( int ) $attribute->value;
            } elseif( $attribute->name == 'to' ) {
                $to = ( int ) $attribute->value;
            }
        }
        if( $what ) {
            if( is_array( $this->values[ $what ] ) ) {
                $enter = true;
                $workingArray = $this->values[ $what ];
                $oldLoopName = $this->actualLoopName;
                $this->actualLoopName = $what;
            } elseif(
                !is_null( $this->previousLoopName )
                && is_array( $this->values[ $this->previousLoopName ][ $what ] )
            ) {
                $enter = true;
                $workingArray = $this->values[ $this->previousLoopName ][ $what ];
            }
            if( $enter ) {
                $this->debug( 'We enter the "' . $what . '" loop', 3, __LINE__ );
                $backupValues = $this->values;
                $backupLoopName = $this->previousLoopName;

                foreach( $workingArray as $key => $entry ) {
                    $oldLoopId = $this->actualLoopId;
                    $this->actualLoopId = $key;
                    $this->previousLoopName = $what;
                    $this->values = array_merge(
                        $backupValues, array( $what => $entry )
                    );

                    if( is_array( $this->values[ $what ] ) ) {
                        $this->values[ $what ][ 'rlid' ] = $key;
                    }
                    $this->enterChildren( $tag, $dest );
                    $this->actualLoopId = $oldLoopId;
                }
                $this->previousLoopName = $backupLoopName;
                $this->values = $backupValues;
                $this->actualLoopName = $oldLoopName;
                $this->debug( 'We exit the "' . $what . '" loop', 3, __LINE__ );
            } else {
                $this->debug( '"' . $what . '" is not an array, so we can\'t loop in', 0, __LINE__ );
            }
            return true;
        } elseif( $to > 0 ) {
            $this->debug( 'We enter the loop', 3, __LINE__ );
            if( !isset( $from ) ) {
                $from = 0;
            }
            $previousLoopId = $this->values[ 'loop' ][ 'rlid' ];
            for( $loop = $from; $loop <= $to; $loop++ ) {
                $this->debug( 'We create the loop entry n°' . $loop, 3, __LINE__ );
                $this->values[ 'loop' ][ 'id' ] = $loop;
                $this->enterChildren( $tag, $dest );
            }
            $this->values[ 'loop' ][ 'rlid' ] = $previousLoopId;
            $this->debug( 'We exit the loop', 3, __LINE__ );
            return true;
        }
        $this->debug( 'There was neither "what" nor "to" attributes to loop in', 0, __LINE__ );
        return false;
    }

    /**
     * Prepares datas to be used as if they were given through php, for integration purpose.
     * @param DOMElement $tag
     * @param DOMElement $dest 
     */
    protected function prepareValues( $tag, $dest ) {
        $this->debug( 'We apply the function ' . __FUNCTION__ . ' on the tag "' . $tag->nodeName . '"', 3, __LINE__ );
        // We should transform the xml structure taken from $tag, to create a php array structure
        $content = $tag->nodeValue;
        $php = json_decode( $content, true );
        if( is_array( $php ) ) {
            $this->values = array_merge( $this->values, $php );
        }
    }

    /**
     * Creates a table of n columns.
     */
    protected function createTable( $tag, $dest ) {
        $this->debug( 'We apply the function ' . __FUNCTION__ . ' on the tag "' . $tag->nodeName . '"', 3, __LINE__ );
        $tableParams = '';
        foreach( $tag->attributes as $attribute ) {
            if( $attribute->name == 'what' ) {
                $what = strtolower( $attribute->value );
            } elseif( $attribute->name == 'cols' ) {
                $cols = $attribute->value;
            } elseif( $attribute->name == 'opened' ) {
                $opened = $attribute->value;
            } else {
                $tableParams .= ' ' . $separator . $attribute->name . '="' . $attribute->value . '"';
            }
        }

        if( $what && $cols ) {
            if( is_array( $this->values[ $what ] ) && !empty( $this->values[ $what ] ) ) {
                $enter = true;
                $workingArray = $this->values[ $what ];
                $oldLoopName = $this->actualLoopName;
                $this->actualLoopName = $what;
            } elseif(
                !is_null( $this->previousLoopName )
                && is_array( $this->values[ $this->previousLoopName ][ $what ] )
                && !empty( $this->values[ $this->previousLoopName ][ $what ] )
            ) {
                $enter = true;
                $workingArray = $this->values[ $this->previousLoopName ][ $what ];
            }
            if( $enter ) {
                $this->debug( 'We enter the "' . $what . '" loop', 3, __LINE__ );
                $backupValues = $this->values;
                $backupLoopName = $this->previousLoopName;

                if( $opened ) {
                    $tableTag = $dest;
                } else {
                    $tableTag = $this->insertTag( $dest, '<table' . $tableParams . '></table>' );
                }

                $cpt = 0;
                foreach( $workingArray as $key => $entry ) {
                    $cpt++;
                    if( $cpt > $cols ) {
                        $cpt = 1;
                    }
                    if( $cpt == 1 ) {
                        $rowTag = $this->insertTag( $tableTag, '<tr></tr>' );
                    }
                    $oldLoopId = $this->actualLoopId;
                    $this->actualLoopId = $key;
                    $this->previousLoopName = $what;
                    $this->values = array_merge(
                        $backupValues, array( $what => $entry )
                    );
                    $cellTag = $this->insertTag( $rowTag, '<td></td>' );
                    $this->enterChildren( $tag, $cellTag );
                    $this->actualLoopId = $oldLoopId;
                }
                $content .= '</table>';
                $this->previousLoopName = $backupLoopName;
                $this->values = $backupValues;
                $this->actualLoopName = $oldLoopName;
                $this->debug( 'We exit the "' . $what . '" loop', 3, __LINE__ );
            } else {
                $this->debug( '"' . $what . '" is not an array, or is empty, so we can\'t loop in', 0, __LINE__ );
            }
            return true;
        }
        $this->debug( 'There was no "what" attribute to loop in', 0, __LINE__ );
        return false;
    }

    protected function insertTag( $dest, $content ) {
        $tempNode = new domDocument( '1.0', 'UTF-8' );
        $tempNode->loadXML( $content );
        $content = $tempNode->firstChild;
        $formNode = $this->xml[ $_SESSION[ 'rendering' ] ]->importNode( $content, true );
        $newTag = $dest->appendChild( $formNode );
        return $newTag;
    }

    /**
     *
     * @param DOMElement $tag
     * @param DOMElement $dest
     * @return bool 
     */
    protected function cache( $tag, $dest ) {
        foreach( $tag->attributes as $attribute ) {
            if( $attribute->name == 'class' ) {
                $class = $this->changeValue( $attribute->value );
            } elseif( $attribute->name == 'part' ) {
                $part = $this->changeValue( $attribute->value );
            } elseif( $attribute->name == 'disabled' ) {
                // If the value is neither empty nor "false", it means that we shouldn't use the cached version
                $disabled = strtolower( $this->changeValue( $attribute->value ) );
                if( $disabled && $disabled != 'false' ) {
                    $newNode = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'rendered' );
                    $newDest = $dest->appendChild( $newNode );
                    $this->enterChildren( $tag, $newDest );
                    $content = $this->xml[ $_SESSION[ 'rendering' ] ]->saveXML(
                        $newDest
                    );
                    return true;
                }
            }
        }
        $lang = $this->linker->i18n->getLang();
        $contentToInsert = $this->linker->cache->part_get( $class, $part, $lang );
        if( !$contentToInsert ) {
            $newNode = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( 'rendered' );
            $newDest = $dest->appendChild( $newNode );
            $this->enterChildren( $tag, $newDest );
            $content = $this->xml[ $_SESSION[ 'rendering' ] ]->saveXML(
                $newDest
            );
            $this->linker->cache->part_cache( $class, $part, $content, $lang );
        } else {
            $tempDomDoc = new domDocument( '1.0', 'UTF-8' );
            $tempDomDoc->loadXML( '<div><RENDERED>' . $contentToInsert . '</RENDERED></div>' );
            $content = $tempDomDoc->firstChild;
            $newNode = $this->xml[ $_SESSION[ 'rendering' ] ]->importNode(
                $content, true
            );
            $addedTag = $dest->appendChild( $newNode );
        }
        return true;
    }

    /**
     * protected function ifset
     *
     */
    protected function ifSet( $tag, $dest ) {
        foreach( $tag->attributes as $attribute ) {
            if( $attribute->name == 'what' ) {
                $what = strtolower( $attribute->value );
                $what = $this->changeValue( $what );
            }
        }
        $this->debug( 'We apply the function ' . __FUNCTION__ . ' on "' . $what . '"', 3, __LINE__ );
        if( $what ) {
            $this->separateArgs( $what, $class, $var );
            if( empty( $var ) && $var !== '0' ) {
                if( is_array( $this->values[ $what ] ) ) {
                    $this->debug( $what . ' is an array', 1, __LINE__ );
                    $this->enterChildren( $tag, $dest );
                } else {
                    $this->debug( $what . ' is not set, so we don\'t display the content', 1, __LINE__ );
                }
            } else {
                if( isset( $this->values[ $class ][ $var ] ) && !empty( $this->values[ $class ][ $var ] ) ) {
                    $this->debug( $what . ' is set to ' . $this->values[ $class ][ $var ], 1, __LINE__ );
                    $this->enterChildren( $tag, $dest );
                } else {
                    if( $class == 'session' ) {
                        if( ($var == 'admin' && $this->isAdmin()) ||
                            ($var == 'master' && $this->isMaster()) ) {
                            $this->debug( 'We are allowed to show this part', 1, __LINE__ );
                            $this->enterChildren( $tag, $dest );
                        }
                    }
                    $this->debug( $what . ' is not set, so we don\'t display the content', 1, __LINE__ );
                }
            }
            return true;
        }
        $this->debug( 'We didn\'t find the class name (in attribute "what")', 0, __LINE__ );
        return false;
    }

    /**
     * protected function ifNotSet
     *
     */
    protected function ifNotSet( $tag, $dest ) {
        $this->debug( 'We apply the function ' . __FUNCTION__ . ' on the tag "' . $tag->nodeName . '"', 3, __LINE__ );
        foreach( $tag->attributes as $attribute ) {
            if( $attribute->name == 'what' ) {
                $what = strtolower( $attribute->value );
            }
        }
        $this->debug( 'We verify that "' . $what . '" is not set', 3, __LINE__ );
        if( $what ) {
            $this->separateArgs( $what, $class, $var );
            if( empty( $var ) && $var !== '0' ) {
                if( is_array( $this->values[ $what ] ) ) {
                    $this->debug( $what . ' is an array, so we don\'t display the content', 1, __LINE__ );
                } else {
                    $this->debug( $what . ' is not set', 1, __LINE__ );
                    $this->enterChildren( $tag, $dest );
                }
            } else {
                if( isset( $this->values[ $class ][ $var ] ) && !empty( $this->values[ $class ][ $var ] ) ) {
                    $this->debug( $what . ' is set, so we don\'t display the content', 1, __LINE__ );
                } else {
                    $this->debug( $what . ' is not set, so we display the content', 1, __LINE__ );
                    $this->enterChildren( $tag, $dest );
                }
            }
            return true;
        }
        $this->debug( 'We didn\'t find the class name (in attribute "what")', 0, __LINE__ );
        return false;
    }

    /**
     * This method separates the arguments like class>element or class:element.
     * @param str $text The text to separate
     * @param str $class The class that is found (should be passed by ref)
     * @param str $var The variable in the class $class (should be passed by ref)
     * @return bool True if class and var were found, false if not
     */
    protected function separateArgs( $text, &$class, &$var ) {
        $text = strtolower( $text );
        if( strpos( $text, ':' ) > 0 ) {
            list($class, $var) = explode( ':', $text );
        } else {
            return false;
        }
        return true;
    }

    /**
     * protected function enterTag
     *
     */
    protected function enterTag( $tag, $dest ) {
        $this->debug( 'We enter the "' . $tag->nodeName . '" tag', 3, __LINE__ );
        if( method_exists( $tag, 'hasAttribute' ) ) {
            // We check if the tag hasn't the attribute skip_tag set to "skip_tag"
            if( $tag->hasAttribute( 'skip_tag' ) && $tag->getAttribute( 'skip_tag' ) == 'skip_tag' ) {
                echo 'We should skip a tag!';
            }

            $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( $tag->nodeName );
            $dest->appendChild( $node );
            foreach( $tag->attributes as $attribute ) {
                $this->debug( 'We add the "' . $attribute->name . '" attribute', 3, __LINE__ );
                $changedValue = $this->changeValue( $attribute->value );
                $attributeName = $attribute->name;
                if( $attribute->name == 'state' ) {
                    $this->debug( 'We set a state', 3, __LINE__ );
                    if( trim( $changedValue ) == '' ) {
                        continue;
                    }
                    $elements = explode( '+', $changedValue );
                    foreach( $elements as $element ) {
                        $element = trim( $element );
                        $root_attr1 = $this->xml[ $_SESSION[ 'rendering' ] ]->createAttribute(
                            $element
                        );
                        $node->appendChild( $root_attr1 );

                        $root_text = $this->xml[ $_SESSION[ 'rendering' ] ]->createTextNode(
                            $element
                        );
                        $root_attr1->appendChild( $root_text );
                    }
                    continue;
                }

                $root_attr1 = $this->xml[ $_SESSION[ 'rendering' ] ]->createAttribute(
                    $attributeName
                );
                $node->appendChild( $root_attr1 );

                $root_text = $this->xml[ $_SESSION[ 'rendering' ] ]->createTextNode(
                    $changedValue
                );
                $root_attr1->appendChild( $root_text );
            }
            if( $tag->hasChildNodes() ) {
                $this->enterChildren( $tag, $node );
            } else {
                $this->debug( 'The "' . $tag->nodeName . '" tag has no children', 3, __LINE__ );
            }
        }
        return true;
    }

    /**
     * Renders the contents of the tag $tag at the position $dest in the $this->xml[$_SESSION['rendering']] DOM object.
     * @param DOMNode $tag The node containing the contents to add.
     * @param DOMElement $dest The destination node in the $this->xml[$_SESSION['rendering']] DOM object.
     * @param bool $debugTime Used to only debug the first loop when debugging is active and we are treating a loop.
     */
    protected function enterChildren( $tag, $dest, $debugTime = false ) {
        $this->debug( 'We look for the children of "' . $tag->nodeName . '"', 3, __LINE__ );
        foreach( $tag->childNodes as $item ) {
            if( $debugTime ) {
                $time = microtime( true );
                $this->debug( 'Loop debug time : ' . $time, 1, __LINE__ );
            }
            if( $item->nodeName != '#text' ) {
                $upperNodeName = strtoupper( $item->nodeName );
                if( isset( $this->methods[ $upperNodeName ] ) ) {
                    $method = $this->methods[ $upperNodeName ];
                    if( method_exists( $this, $method ) ) {
                        $this->$method( $item, $dest );
                    } else {
                        $this->debug( 'The "' . $method . '" method doesn\'t exist in the "' . $item->nodeName . '" class',
                                      0, __LINE__ );
                    }
                } elseif( isset( $this->plugins[ $upperNodeName ] ) ) {
                    $class = $this->plugins[ $upperNodeName ][ 'class' ];
                    $method = $this->plugins[ $upperNodeName ][ 'method' ];
                    $className = $this->linker->$class->getClassName();
                    if( method_exists( $className, $method ) ) {
                        $shallWeMethod = 'shallWe_' . $method;
                        if( method_exists( $className, $shallWeMethod ) && !$this->linker->$class->$shallWeMethod() ) {
                            $this->enterTag( $item, $dest );
                        } else {
                            $attributes = array( );
                            foreach( $item->attributes as $attribute ) {
                                $attributes[ $attribute->name ] = $this->changeValue(
                                    $attribute->value
                                );
                            }

                            // Changes the values that are in the content of the tag
                            $tempXML = $this->xml[ $_SESSION[ 'rendering' ] ];
                            $this->debug( 'Switching to temp document', 3, __LINE__ );
                            $this->xml[ $_SESSION[ 'rendering' ] ] = new domDocument(
                                    '1.0',
                                    'UTF-8'
                            );
                            $this->xml[ $_SESSION[ 'rendering' ] ]->loadXML( '<RF></RF>' );
                            $content = $this->xml[ $_SESSION[ 'rendering' ] ]->firstChild;
                            $this->enterChildren( $item, $content );
                            $oldContents = $content->nodeValue;

                            $oldContents = $this->xml[ $_SESSION[ 'rendering' ] ]->saveXML(
                                $this->xml[ $_SESSION[ 'rendering' ] ]->documentElement
                            );
                            $oldContents = substr( $oldContents, 4, -5 );
                            $this->debug( 'Switching back from temp document', 3, __LINE__ );
                            $this->xml[ $_SESSION[ 'rendering' ] ] = $tempXML;
                            $newContent = $this->linker->$class->$method(
                                $attributes, $oldContents, $this->values
                            );
                            // If the function returns true, we don't have to loop in it.
                            if( $newContent && $newContent !== true ) {
                                $tempNode = new domDocument( '1.0', 'UTF-8' );
                                $tempNode->loadXML( '<RF>' . $newContent . '</RF>' );
                                $content = $tempNode->firstChild;
                                $this->enterChildren( $content, $dest );
                            }
                        }
                    } else {
                        $this->debug( 'The "' . $method . '" method doesn\'t exist in the "' . $this->linker->$class->getClassName() . '" class',
                                      0, __LINE__ );
                    }
                } else {
                    $this->enterTag( $item, $dest );
                }
            } else {
                if( trim( $item->nodeValue ) != '' ) {
                    $this->debug( 'We add the text value "' . $item->nodeValue . '"', 3, __LINE__ );
                    $textNode = $this->xml[ $_SESSION[ 'rendering' ] ]->createTextNode(
                        $item->nodeValue
                    );
                    $dest->appendChild( $textNode );
                }
            }
        }
        $this->debug( 'End of the loop on the children of "' . $tag->nodeName . '"', 3, __LINE__ );
    }

    /**
     * protected function createTag
     *
     */
    protected function createTag( $tag, $dest ) {
        $this->debug( 'We create the tag "' . $tag->nodeName . '"', 2, __LINE__ );
        $args = '';
        foreach( $tag->attributes as $attribute ) {
            $this->debug( 'RENDER_TAG has attribute ' . $attribute->name . ' = "' . $attribute->value . '"', 2, __LINE__ );
            if( $attribute->name == 'what' ) {
                $this->separateArgs( $attribute->value, $class, $element );
            } elseif( $attribute->name == 'type' ) {
                $type = strtolower( $this->changeValue( $attribute->value ) );
            } else {
                $changedValue = $this->changeValue( $attribute->value );
                $args .= ' ' . $attribute->name . '="' . $changedValue . '"';
            }
        }
        if( !isset( $type ) ) {
            $this->debug( 'We didn\'t find the type', 0, __LINE__ );
            return false;
        }
        if( isset( $class ) && isset( $element ) ) {
            if( isset( $this->values[ $class ][ $element ] ) ) {
                $tempNode = new domDocument( '1.0', 'UTF-8' );
                $tempNode->loadXML(
                    '<' . $type . ' ' . $this->values[ $class ][ $element ] . $args . '/>'
                );
                $content = $tempNode->firstChild;
                $newNode = $this->xml[ $_SESSION[ 'rendering' ] ]->importNode(
                    $content, true
                );
                $newTag = $dest->appendChild( $newNode );
            } else {
                $node = $this->xml[ $_SESSION[ 'rendering' ] ]->createElement( $type );
                $newTag = $dest->appendChild( $node );
            }
        }
        // Enters the source node, if not empty
        if( $tag->hasChildNodes() ) {
            $this->enterChildren( $tag, $newTag );
        }
    }

    /**
     * protected function changeValue
     *
     */
    protected function changeValue( $valueToChange, $secondLevel = false ) {
        $old = $valueToChange;
        $found = false;
        // Looking for the {class:element} or {class>element} format
        if( preg_match( '`(.*)\{([^ >]+)\:([^\}]+)\}(.*)`', $valueToChange, $matches ) ) {
            $found = true;
        }
        if( $found ) {
            $class = strtolower( $matches[ 2 ] );
            $element = strtolower( $matches[ 3 ] );
            if( $class == 'i18n' ) {
                $ret = $matches[ 1 ] . $this->linker->i18n->get(
                        $this->i18nClasses[ $_SESSION[ 'rendering' ] ], $matches[ 3 ]
                    ) . $matches[ 4 ];
                $valueToChange = $this->changeValue( $ret, true );
            } else {
                if( $class == 'constants' && defined( strtoupper( $matches[ 3 ] ) ) ) {
                    $value = constant( strtoupper( $matches[ 3 ] ) );
                } else {
                    $value = trim( $this->values[ $class ][ $element ] );
                }
                $ret = $matches[ 1 ] . $value . $matches[ 4 ];
                $valueToChange = $this->changeValue( $ret, true );
            }
        } elseif(
            $this->actualLoopName
            && preg_match( '`(.*)\{' . $this->actualLoopName . '\}(.*)`', $valueToChange, $matches )
        ) {
            $ret = $matches[ 1 ] . $this->actualLoopId . $matches[ 2 ];
            $valueToChange = $this->changeValue( $ret, true );
        }
        if( !$secondLevel ) {
            if( $old != $valueToChange ) {
                $this->debug( __FUNCTION__ . ' - We replace "' . $old . '" with "' . $valueToChange . '"', 2, __LINE__ );
            } else {
                $this->debug( __FUNCTION__ . ' - We don\'t replace "' . $old . '"', 2, __LINE__ );
            }
        }
        return $valueToChange;
    }

    protected function modifyValue( $tag ) {
        $help_me = $tag->getAttribute( 'help_me' );

        if( $help_me != '' ) {
            echo $this->showRenderTagHelp(
                    'RENDER_MODIFYVALUE', 'Changes the value of some variables, using external routines.',
                    array(
                    'what' => 'The variable to modify (format : "[class]:[var]")',
                    'new' => 'Optional : if set (format : "[class]:[var]"), will not replace the 
                        "what" var, but will instead create a new one based on it.',
                    'class' => 'Optional : If not set, will use common modifiers, if not, will
                        pass the value to class->method, and use the returned string.',
                    'method' => 'Optional : If not set, will use common modifiers, if not, will
                        pass the value to class->method, and use the returned string.'
                    )
            );
        }
        $what = $tag->getAttribute( 'what' );
        $new = $tag->getAttribute( 'new' );
        if( isset( $this->values[ $what ] ) ) {
            $oldContent = $this->values[ $what ];
            $array = true;
            if( empty( $new ) ) {
                $new = $what;
            }
        } else {
            $oldContent = $this->changeValue( '{' . $what . '}' );
            $array = false;
            if( !empty( $new ) ) {
                $this->separateArgs( $new, $outputClass, $outputVar );
            } else {
                $this->separateArgs( $what, $outputClass, $outputVar );
            }
        }
        $class = $tag->getAttribute( 'class' );
        $method = $tag->getAttribute( 'method' );
        list($method, $args) = explode( '|', $method, 2 );
        if( empty( $class ) && $this->linker->method_exists( __CLASS__, 'mod_' . $method ) ) {
            $class = __CLASS__;
            $method = 'mod_' . $method;
        }
        if( empty( $class ) ) {
            echo 'class is empty<br />';
            return false;
        }
        if( $this->linker->method_exists( $class, $method ) ) {
            $newContent = $this->linker->$class->$method( $oldContent, $args );
        } else {
            echo 'The method ' . $method . ' does not exist!<br />';
            return false;
        }
        if( !$array ) {
            $this->values[ $outputClass ][ $outputVar ] = $newContent;
        } else {
            $this->values[ $new ] = $newContent;
        }
        return true;
    }

    protected function mod_toUpperCase( $text ) {
        return strtoupper( $text );
    }

    protected function mod_toLowerCase( $text ) {
        return strtolower( $text );
    }

    protected function mod_toUpperCase_firstLetter( $text ) {
        return ucfirst( $text );
    }

    protected function mod_toUpperCase_firstLetters( $text ) {
        return ucwords( $text );
    }

    protected function mod_removeTags( $text ) {
        return strip_tags( $text );
    }

    protected function mod_cut( $text, $params ) {
        list($length, $toWord, $ellipsis) = explode( '|', $params );
        if( $toWord == 'word' ) {
            if( $ellipsis == 'ellipsis' ) {
                $length -= 3;
            }
            $words = explode( ' ', $text );
            $beginning = '';
            foreach( $words as $oneWord ) {
                if( strlen( $beginning ) + strlen( $oneWord ) + 1 < $length ) {
                    if( $beginning != '' ) {
                        $beginning .= ' ';
                    }
                    $beginning .= $oneWord;
                } else {
                    if( $ellipsis == 'ellipsis' ) {
                        $beginning .= '...';
                    }
                    break;
                }
            }
            return $beginning;
        }
        return substr( $text, 0, $length );
    }

    /**
     * protected function replaceValue
     * Called with the tag :
     * <RENDER_VALUE what="[class]:[varName]" />
     * eg. : <RENDER_VALUE what="body:beginning" />
     */
    protected function replaceValue( $tag, $dest ) {
        // We only take care of the "what" attribute
        $what = $tag->getAttribute( 'what' );
        $quotes = '';
        if( $tag->hasAttribute( 'quotes' ) ) {
            $quotes = $tag->getAttribute( 'quotes' );
        }
        if( trim( $what ) != '' ) {
            $this->debug( 'We want to replace the value of "' . $what . '"', 3, __LINE__ );
            $content = $this->changeValue( $what );

            $this->separateArgs( $content, $class, $element );
            if( $element == '' ) {
                $textContent = trim( $this->values[ $class ] );
            } else {
                if( $class == 'i18n' ) {
                    $value = $this->linker->i18n->get(
                        $this->i18nClasses[ $_SESSION[ 'rendering' ] ], $element
                    );
                    $value = '<content>' . $quotes . $value . $quotes . '</content>';
                    $tempNode = new domDocument( '1.0', 'UTF-8' );

                    $status = @$tempNode->loadXML( $value );
                    if( $status ) {
                        $content = $tempNode->firstChild;
                        $this->enterChildren( $content, $dest );
                    } else {
                        $this->debug( 'Error loading XML (' . $element . '", "' . $class . '")', 0, __LINE__ );
                    }
                    return true;
                } elseif( $class == 'constants' && defined( strtoupper( $element ) ) ) {
                    $textContent = constant( strtoupper( $element ) );
                } else {
                    $textContent = trim( $this->values[ $class ][ $element ] );
                }
            }
            $textContent = $quotes . $textContent . $quotes;
            if( $textContent != '' ) {
                // We take the text as an xml part, even if it is not
                $value = '<content>' . $textContent . '</content>';
                $tempNode = new domDocument( '1.0', 'UTF-8' );
                $status = @$tempNode->loadXML( $value );
                if( $status ) {
                    $content = $tempNode->firstChild;
                    $this->enterChildren( $content, $dest );
                } else {
                    $this->debug( 'Error loading XML (' . $element . '", "' . $class . '")', 0, __LINE__ );
                }
                return true;
            } else {
                // Error : [varName] does not exist in [class]
                $this->debug( 'We didn\'t find any value "' . $element . '" in the "' . $class . '" class', 1, __LINE__ );
                return false;
            }
        }
        // Error : There was no "what" attribute
        $this->debug( 'We didn\'t find any "what" attribute for a ' . __FUNCTION__, 0, __LINE__ );

        return false;
    }

    /**
     * protected function getModel
     * Gets the model from the file given as a parameter
     */
    protected function getModel( $model ) {
        $model = $this->linker->html->replaceTemplateDir( $model );
        if( file_exists( $model ) ) {
            $ret = file_get_contents( $model );
        }
        if( file_exists( '.' . $model ) ) {
            $ret = file_get_contents( '.' . $model );
        }
        if( file_exists( SH_CLASS_FOLDER . '/' . $model ) ) {
            $ret = file_get_contents( SH_CLASS_FOLDER . '/' . $model );
        }
        $ret = preg_replace( array( "`\n`", '` +`' ), array( '', ' ' ), $ret );
        return $ret;
    }

    /**
     * public function enableCaptchas
     */
    public function enableCaptchas( $status ) {
        $this->captchasEnabled = $status;
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

}