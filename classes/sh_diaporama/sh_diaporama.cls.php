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
 * Class that creates slideshows, using Millstream Web Software's javascript Crossfade,
 * and Andrew Tetlaw's javascript Fastinit.
 */
class sh_diaporama extends sh_core {

    const CLASS_VERSION = '1.1.11.11.11';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    protected $minimal = array( 'getList' => true );
    protected $acceptedTypes = array( );
    const LIST_FILENAME = '.images';
    const defaultType = 'default';
    protected static $previews = array( );
    protected $sizes = array( 100, 150, 200, 300, 400, 500 );
    protected $jsAdded = false;

    public function construct() {
        $this->diapoFolder = SH_IMAGES_FOLDER . 'diaporamas/';
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            $this->linker->db->updateQueries( __CLASS__ );
            if( version_compare( $installedVersion, '1.1.11.03.29', '<' ) ) {
                $this->helper->addClassesSharedMethods( 'sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__ );
                $this->linker->renderer->add_render_tag( 'render_diaporama', __CLASS__, 'render_diaporama' );
                $this->linker->renderer->add_render_tag( 'render_diaporamaFromList', __CLASS__,
                                                         'render_diaporamaFromList' );
                if( !is_dir( $this->diapoFolder ) ) {
                    sh_browser::createFolder( $this->diapoFolder, 1 );
                }
            }
            if( version_compare( $installedVersion, '1.1.11.11.11', '<' ) ) {
                // We add IDs to the existing diaporamas
                $diaporamas = scandir( $this->diapoFolder );
                $id = 1;
                foreach( $diaporamas as $diaporama ) {
                    if( substr( $diaporama, 0, 1 ) != '.' && is_dir( $this->diapoFolder . $diaporama ) ) {
                        $this->helper->writeInFile( $this->diapoFolder . $diaporama . '/.diaporama_id', $id );
                        $id++;
                        if( file_exists( $this->diapoFolder . $diaporama . '/' . sh_browser::DIMENSIONFILE ) ) {
                            rename(
                                $this->diapoFolder . $diaporama . '/' . sh_browser::DIMENSIONFILE,
                                $this->diapoFolder . $diaporama . '/.diaporama_dimensions'
                            );
                        }

                        $this->helper->writeInFile( $this->diapoFolder . $diaporama . '/' . sh_browser::MAXDIMENSIONFILE,
                                                    '1000x1000' );
                    }
                }
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
        $this->linker->html->addCSS( '/templates/global/diaporama.css' );
        $this->acceptedTypes = $this->getParam( 'acceptedTypes' );
    }

    public function master_getMenuContent() {
        $masterMenu = array( );
        return $masterMenu;
    }

    public function admin_getMenuContent() {
        $adminMenu = array( );
        $adminMenu[ 'Médias' ][ ] = array(
            'link' => 'diaporama/edit/',
            'text' => 'Modifier les diaporamas',
            'icon' => 'picto_browser.png'
        );
        return $adminMenu;
    }

    /**
     * Shows the form and save the result for the diaporamas editor.
     */
    public function edit() {
        $this->debug( __METHOD__, 2, __LINE__ );
        $this->onlyAdmin();
        $id = ( int ) $this->linker->path->page[ 'id' ];

        if( $this->formSubmitted( 'manage_diaporamas' ) ) {
            $datas = $this->getList( true );
            krsort( $_POST[ 'diapo' ] );
            $max = 1;
            foreach( $_POST[ 'diapo' ] as $id => $diapo ) {
                $max = max( $max, $id );
                if( $id == 0 ) {
                    //This is a new diapo
                    $id = $max + 1;
                    $name = sh_browser::modifyName( $diapo[ 'name' ] );
                    $folder = $this->diapoFolder . $name . '/';
                    mkdir( $folder );
                    $this->helper->writeInFile( $folder . '.diaporama_id', $id );
                    sh_browser::setOwner( $folder );

                    $this->helper->writeInFile( $folder . sh_browser::MAXDIMENSIONFILE, '1000x1000' );
                    $this->helper->writeInFile( $folder . '/.diaporama_dimensions',
                                                $diapo[ 'width' ] . 'x' . $diapo[ 'height' ] );
                    $this->helper->writeInFile( $folder . sh_browser::ONCHANGE, 'sh_diaporama|onChange|' );
                    sh_browser::setRights(
                        $folder,
                        sh_browser::READ + sh_browser::ADDFILE + sh_browser::DELETEFILE + sh_browser::RENAMEFILE
                    );
                } else {
                    $name = $_SESSION[ __CLASS__ ][ 'id_to_name' ][ $id ];
                    $this->helper->writeInFile( $this->diapoFolder . $name . '/.diaporama_dimensions',
                                                $diapo[ 'width' ] . 'x' . $diapo[ 'height' ] );
                }
            }
        }

        $datas = $this->getList( true );
        $max = 0;
        unset( $_SESSION[ __CLASS__ ][ 'id_to_name' ] );
        foreach( $datas[ 'diaporamas' ] as $oneData ) {
            $_SESSION[ __CLASS__ ][ 'id_to_name' ][ $oneData[ 'id' ] ] = $oneData[ 'name' ];
            $max++;
        }

        $this->render( 'add_edit', $datas );
        return true;
    }

    /**
     * Gets and eventually renders the list of diaporamas.
     * @param bool $inArray
     * <ul><li>false (default behaviour) for a rendering.</li>
     * <li>true to get the list in an array.</li></ul>
     * @return bool|array True if $inArray is "false", and the array containing
     * the list if $inArray is "true".
     */
    public function getList( $inArray = false ) {
        $this->debug( __METHOD__, 2, __LINE__ );
        $elements = scandir( $this->diapoFolder );
        $diapos[ 'diaporamas' ] = array( );
        foreach( $elements as $element ) {
            if( substr( $element, 0, 1 ) != '.' ) {
                if( file_exists( $this->diapoFolder . $element . '/' . sh_browser::OWNERFILE ) ) {
                    list($width, $height) = explode(
                        'x',
                        file_get_contents(
                            $this->diapoFolder . $element . '/.diaporama_dimensions'
                        )
                    );
                    $id = file_get_contents( $this->diapoFolder . $element . '/.diaporama_id' );
                    $diapos[ 'diaporamas' ][ ] = array(
                        'name' => $element,
                        'width' => $width,
                        'height' => $height,
                        'id' => $id
                    );
                }
            }
        }
        if( $inArray ) {
            return $diapos;
        }
        echo $this->render( 'diaporamaInserter', $diapos, false, false );
        return true;
    }

    /**
     * Method called by sh_browser when some change is done on a diaporama folder
     * (adding, renaming or removing of an directory).
     * @param str $event The event that braught us to here.
     * @param str $parentFolder The folder in which something changed.
     * @param str $folder The name of the folder that changed.
     * @param str $newName The new name of the forlder.<br />
     * Only used if the action was a renaming.
     * @param array $elements An array containing all the arguments that had to be
     * passed to the function (here, nothing...).
     */
    public function folderEvent( $event, $parentFolder, $folder, $newName, $elements ) {
        $this->debug( __METHOD__, 2, __LINE__ );
        if( $event == sh_browser::ONADDFOLDER ) {
            $this->debug( 'A folder named ' . basename( $folder ) . ' was created in ' . $parentFolder, 3, __LINE__ );
        }
        if( $event == sh_browser::ONRENAMEFOLDER ) {
            $this->debug( 'The folder ' . $folder . ' was renamed to ' . $newName, 3, __LINE__ );
        }
        if( $event == sh_browser::ONDELETEFOLDER ) {
            $this->debug( 'The folder ' . basename( $folder ) . ' was removed from ' . $parentFolder, 3, __LINE__ );
        }
    }

    /**
     * Method called by sh_browser when some change is done on the files of a diaporama
     * directory (adding, renaming or removing of an image).
     * @param str $event The event that braught us to here.
     * @param str $folder The name of the folder in which the change occured.
     * @return bool Always returns true.
     */
    public function onChange( $event, $folder ) {
        $this->debug( __METHOD__, 2, __LINE__ );
        $this->buildListFile( basename( $folder ) );
        return true;
    }

    /**
     * Method that creates a new diaporama folder, and sets the folder rigths.
     * @param str $name Name of the diaporama.
     * @return bool True for success, false for failure (the folder could not be created).
     */
    public function newDiaporama( $name, $num = 0 ) {
        $this->debug( __METHOD__, 2, __LINE__ );
        $name = $this->diapoFolder . sh_browser::modifyName( $name );
        if( !is_dir( $name ) ) {
            $name = sh_browser::createFolder( $name, 113 );
            if( !$name ) {
                return false;
            }
        }
        if( !file_exists( $name . '/' . sh_browser::DIMENSIONFILE ) ) {
            sh_browser::addDimension( $name, 500, 500 );
        }
        if( !file_exists( $name . '/' . sh_browser::ONCHANGE ) ) {
            sh_browser::addEvent( sh_browser::ONCHANGE, $name, __CLASS__, 'onChange' );
        }
        return true;
    }

    /**
     * Method that creates the images list file in a diaporama directory.<br />
     * If there is already one, it is replaced.
     * @param str $name Name of the diaporama.
     * @return array An array containing the images list.
     */
    protected function buildListFile( $name ) {
        $this->debug( __METHOD__, 2, __LINE__ );
        $folder = $this->diapoFolder . sh_browser::modifyName( $name );

        // We remove the old list file
        $file = $folder . '/' . self::LIST_FILENAME;
        if( file_exists( $file ) ) {
            unlink( $file );
        }

        list($width, $height) = explode(
            'x', file_get_contents(
                $folder . '/.diaporama_dimensions'
            )
        );

        $elements = scandir( $folder );
        foreach( $elements as $element ) {
            if( substr( $element, 0, 1 ) != '.' ) {
                // The file is neither ".", nor "..", nor any hidden file or folder
                $ext = array_pop( explode( '.', $element ) );
                if( in_array( strtolower( $ext ), $this->acceptedTypes ) ) {
                    // We don't take resized images neither
                    if( !preg_match( '`.*\.resized\.[0-9]+\.[0-9]+\.png`', $element ) ) {
                        $imagePath = $this->linker->path->changeToShortFolder( $folder ) . '/' . $element;
                        $values[ 'images' ][ ][ 'src' ] = $imagePath . '.resized.' . $width . '.' . $height . '.png';
                    }
                }
            }
        }
        $this->helper->writeArrayInFile( $file, 'values', $values );
        return $values;
    }

    public function shallWe_render_diaporama( $attributes = array( ) ) {
        $this->isRenderingWEditor = $this->isRenderingWEditor || $this->linker->wEditor->isRendering();
        $rep = !$this->isRenderingWEditor;
        return $rep;
    }

    /**
     * Method called by the sh_render class to render the tag RENDER_DIAPORAMA.
     * @param array $attributes An associative array containing all the tag's attributes.
     * @return str The rendered html for the diaporama.
     */
    public function render_diaporama( $attributes = array( ) ) {
        $name = $attributes[ 'name' ];
        $folder = $this->diapoFolder . sh_browser::modifyName( $name );
        if( !is_dir( $folder ) ) {
            return false;
        }
        if( isset( $attributes[ 'id' ] ) ) {
            $id = $attributes[ 'id' ];
            unset( $attributes[ 'id' ] );
        } else {
            $id = 'd_' . substr( md5( microtime() ), 0, 10 );
        }

        if( isset( $attributes[ 'first' ] ) ) {
            $first = $attributes[ 'first' ];
        } else {
            $first = 1;
        }
        $file = $folder . '/' . self::LIST_FILENAME;
        if( !file_exists( $file ) ) {
            $this->buildListFile( $name );
        }

        include($file);

        if( isset( $attributes[ 'manual' ] ) ) {
            $values[ 'diapo' ][ 'manual' ] = true;
            $attributes[ 'commands' ] = true;
        }
        if( isset( $attributes[ 'commands' ] ) ) {
            if( strtolower( $attributes[ 'commands' ] ) == 'commands' ) {
                $values[ 'diapo' ][ 'commands' ] = true;
            }
        } elseif( file_exists( $folder . '/.commands' ) ) {
            $values[ 'diapo' ][ 'commands' ] = true;
        }

        if( isset( $attributes[ 'shuffle' ] ) && is_array( $values[ 'images' ] ) ) {
            // 2 shuffles to really shuffle the array
            shuffle( $values[ 'images' ] );
            shuffle( $values[ 'images' ] );
        }

        $values[ 'diapo' ][ 'id' ] = $id;
        if( !empty( $attributes[ 'class' ] ) ) {
            $values[ 'diapo' ][ 'class' ] = $attributes[ 'class' ];
        } elseif( file_exists( $folder . '/.classes' ) ) {
            $values[ 'diapo' ][ 'class' ] = file_get_contents( $folder . '/.classes' );
        }

        if( isset( $values[ 'images' ][ $first ][ 'src' ] ) ) {
            $values[ 'defaultImage' ][ 'src' ] = $values[ 'images' ][ $first ][ 'src' ];
        } else {
            $values[ 'defaultImage' ][ 'src' ] = $values[ 'images' ][ 0 ][ 'src' ];
        }
        $values[ 'js' ][ 'dir' ] = $this->getSinglePath( false );

        $values[ 'diapo' ][ 'style' ] = '';
        if( isset( $attributes[ 'width' ] ) ) {
            $values[ 'diapo' ][ 'style' ] .= 'width:' . $attributes[ 'width' ] . 'px;';
        }
        if( isset( $attributes[ 'height' ] ) ) {
            $values[ 'diapo' ][ 'style' ] .= 'height:' . $attributes[ 'height' ] . 'px;';
        }
        if( isset( $attributes[ 'float' ] ) && $attributes[ 'float' ] != 'none' ) {
            $values[ 'diapo' ][ 'style' ] .= 'float:' . $attributes[ 'float' ] . ';';
        }

        if( $this->jsAdded ) {
            $values[ 'js' ][ 'added' ] = true;
        } elseif( sh_html::$willRender ) {
            $values[ 'js' ][ 'added' ] = true;
            $this->linker->javascript->get( sh_javascript::SCRIPTACULOUS );
            $this->linker->html->addScript( $this->getSinglePath() . 'fastinit.js' );
            $this->linker->html->addScript( $this->getSinglePath() . 'crossfade.js' );
            $this->linker->html->addScript( $this->getSinglePath() . 'actions.js' );
            $this->jsAdded = true;
        } else {
            $this->jsAdded = true;
        }

        return $this->render( 'diaporama', $values, false, false );
    }

    public function render_diaporamaFromList( $attributes, $content ) {
        $name = $attributes[ 'name' ];
        unset( $attributes[ 'name' ] );
        if( isset( $attributes[ 'id' ] ) ) {
            $id = $attributes[ 'id' ];
            unset( $attributes[ 'id' ] );
        }
        if( isset( $attributes[ 'background' ] ) ) {
            $values[ 'diapo' ][ 'background' ] = $attributes[ 'background' ];
        }

        $images = explode( '|', $content );

        if( isset( $attributes[ 'random' ] ) ) {
            shuffle( $images );
        }

        foreach( $images as $oneImage ) {
            if( trim( $oneImage ) != '' ) {
                $values[ 'images' ][ ][ 'src' ] = trim( $oneImage );
            }
        }

        if( !isset( $id ) ) {
            $id = 'd_' . substr( md5( microtime() ), 0, 10 );
        }

        if( isset( $attributes[ 'manual' ] ) ) {
            $values[ 'diapo' ][ 'manual' ] = true;
            $attributes[ 'commands' ] = true;
        }
        if( isset( $attributes[ 'commands' ] ) ) {
            $values[ 'diapo' ][ 'commands' ] = true;
        }

        if( isset( $attributes[ 'first' ] ) ) {
            $first = $attributes[ 'first' ];
            unset( $attributes[ 'first' ] );
        } else {
            $first = 1;
        }

        if( isset( $attributes[ 'shuffle' ] ) && is_array( $values[ 'images' ] ) ) {
            // 2 shuffles to really shuffle the array
            shuffle( $values[ 'images' ] );
            shuffle( $values[ 'images' ] );
        }

        $values[ 'diapo' ][ 'id' ] = $id;
        $values[ 'diapo' ][ 'class' ] = $attributes[ 'class' ];
        if( isset( $values[ 'images' ][ $first ][ 'src' ] ) ) {
            $values[ 'defaultImage' ][ 'src' ] = $values[ 'images' ][ $first ][ 'src' ];
        } else {
            $values[ 'defaultImage' ][ 'src' ] = $values[ 'images' ][ 0 ][ 'src' ];
        }

        if( $this->jsAdded ) {
            $values[ 'js' ][ 'added' ] = true;
        } elseif( sh_html::$willRender ) {
            $values[ 'js' ][ 'added' ] = true;
            $this->linker->html->addScript( $this->getSinglePath() . 'fastinit.js' );
            $this->linker->html->addScript( $this->getSinglePath() . 'crossfade.js' );
            $this->linker->html->addScript( $this->getSinglePath() . 'actions.js' );
            $this->jsAdded = true;
        } else {
            $this->jsAdded = true;
        }

        return $this->render( 'diaporama', $values, false, false );
    }

    /**
     * public function addToPreviews
     *
     */
    public static function addToPreviews( $id ) {
        self::$previews[ ] = $id;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri( $page ) {
        $this->debug( __METHOD__, 2, __LINE__ );
        if( $page == $this->shortClassName . '/edit/' ) {
            return '/' . $this->shortClassName . '/edit.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage( $uri ) {
        $this->debug( __METHOD__, 2, __LINE__ );
        if( $uri == '/' . $this->shortClassName . '/edit.php' ) {
            return $this->shortClassName . '/edit/';
        }
        return false;
    }

    public function __tostring() {
        return get_class();
    }

}
