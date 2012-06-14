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
class sh_gallery extends sh_core {

    const CLASS_VERSION = '1.1.12.03.16';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    protected $minimal = array( );
    protected $acceptedTypes = array( );
    const LIST_FILENAME = '.images';
    const defaultType = 'default';
    protected static $previews = array( );
    protected $jsAdded = false;
    public $callWithoutId = array( 'getList' );
    public $callWithId = array( 'edit', 'show' );

    public function construct() {
        $this->galleryFolder = SH_IMAGES_FOLDER . 'galleries/';
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            $this->linker->db->updateQueries( __CLASS__ );
            if( version_compare( $installedVersion, '1.1.12.03.16', '<' ) ) {
                $this->helper->addClassesSharedMethods( 'sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__ );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
        $this->acceptedTypes = $this->getParam( 'acceptedTypes' );
    }

    public function master_getMenuContent() {
        $masterMenu = array( );
        return $masterMenu;
    }

    public function admin_getMenuContent() {
        $adminMenu = array( );
        $adminMenu[ 'Médias' ][ ] = array(
            'link' => 'gallery/getList/',
            'text' => 'Modifier les galeries',
            'icon' => 'picto_browser.png'
        );
        return $adminMenu;
    }

    /**
     * Shows the form and save the result for the gallerys editor.
     */
    public function edit() {
        $this->onlyAdmin();
        $id = ( int ) $this->linker->path->page[ 'id' ];
        if( $this->formSubmitted( 'edit_gallery' ) ) {
            if( $id == 0 ) {
                // This is a new gallery
                $max = $this->getParam( 'max', 0 ) + 1;
                $this->setParam( 'max', $max );
                $id = $max;
                $redirect = true;
            }
            $this->addToSitemap( $this->shortClassName . '/show/' . $id, 0.3 );
            $title = $this->getParam( 'list>' . $id . '>title', 0 );
            $title = $this->setI18n( $title, $_POST[ 'title' ] );
            $this->setParam( 'list>' . $id . '>title', $title );
            $this->setParam( 'list>' . $id . '>images', explode( '|', $_POST[ 'images' ] ) );
            $this->writeParams();
            $this->linker->path->redirect( __CLASS__, 'show', $id );
        }
        $values[ 'gallery' ] = $this->getParam( 'list>' . $id, array( ) );
        if( is_array( $values[ 'gallery' ][ 'images' ] ) ) {
            $values[ 'gallery' ][ 'images' ] = implode( '|', $values[ 'gallery' ][ 'images' ] );
        }
        $this->render( 'edit', $values );
    }

    public function show() {
        $this->linker->javascript->get( sh_javascript::LIGHTBOX );
        $id = ( int ) $this->linker->path->page[ 'id' ];
        $datas = $this->getParam( 'list>' . $id, array( ) );
        $this->linker->html->setTitle( $this->getI18n( $datas[ 'title' ] ) );
        if( is_array( $datas[ 'images' ] ) ) {
            foreach( $datas[ 'images' ] as $image ) {
                $values[ 'images' ][ ][ 'src' ] = $image;
                if( $_GET[ 'image' ] == $image ) {
                    $values[ 'main_image' ][ 'src' ] = $image;
                    $default_found = true;
                }
            }
        }
        if( !$default_found ) {
            $values[ 'main_image' ] = $values[ 'images' ][ 0 ];
        }
        
        $datas = $this->getParam( 'list', array( ) );
        foreach( $datas as $galId => $gal ) {
            if($galId != $id){
                $values[ 'other_galleries' ][ $galId ] = array(
                    'name' => $this->getI18n( $gal[ 'title' ] ),
                    'link' => $this->linker->path->getLink( __CLASS__ . '/show/' . $galId ),
                    'images_count' => count( $gal[ 'images' ] )
                );
            }
        }
        
        $this->render( 'show', $values );
    }

    /**
     * Shows the form and save the result for the gallerys editor.
     */
    public function getList() {
        $this->onlyAdmin();
        $datas = $this->getParam( 'list', array( ) );

        foreach( $datas as $galId => $gal ) {
            $values[ 'galleries' ][ $galId ] = array(
                'name' => $this->getI18n( $gal[ 'title' ] ),
                'link' => $this->linker->path->getLink( __CLASS__ . '/edit/' . $galId ),
                'images_count' => count( $gal[ 'images' ] )
            );
        }

        $values[ 'new' ][ 'action' ] = $this->linker->path->getLink( __CLASS__ . '/edit/0' );

        $this->render( 'getList', $values );
        return true;
    }

    /**
     * Method called by sh_browser when some change is done on a gallery folder
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
     * Method called by sh_browser when some change is done on the files of a gallery
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
     * Method that creates a new gallery folder, and sets the folder rigths.
     * @param str $name Name of the gallery.
     * @return bool True for success, false for failure (the folder could not be created).
     */
    public function newGallery( $name, $num = 0 ) {
        $this->debug( __METHOD__, 2, __LINE__ );
        $name = $this->galleryFolder . sh_browser::modifyName( $name );
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
     * Method that creates the images list file in a gallery directory.<br />
     * If there is already one, it is replaced.
     * @param str $name Name of the gallery.
     * @return array An array containing the images list.
     */
    protected function buildListFile( $name ) {
        $this->debug( __METHOD__, 2, __LINE__ );
        $folder = $this->galleryFolder . sh_browser::modifyName( $name );

        // We remove the old list file
        $file = $folder . '/' . self::LIST_FILENAME;
        if( file_exists( $file ) ) {
            unlink( $file );
        }

        list($width, $height) = explode(
            'x', file_get_contents(
                $folder . '/.images_dimensions'
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

    public function getPageName( $action, $id, $forUrl = false ) {
        if( $action == 'show' ) {
            return 'Galerie n°' . $id.' - '.$this->getI18n( $this->getParam( 'list>'.$id.'>title', 0));
        }
        if( $action == 'edit' ) {
            return 'Galerie n°' . $id;
        }
        return false;
    }

    public function __tostring() {
        return get_class();
    }

}
