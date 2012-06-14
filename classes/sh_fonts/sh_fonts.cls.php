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
 * Class that manages the fonts.
 */
class sh_fonts extends sh_core {

    const CLASS_VERSION = '1.1.11.07.23';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    protected $minimal = array( 'addThisFont' => true );
    protected $afterFlushValues = array( );
    const FONT_THUMB_TEXT = 'abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ 0123456789 áàâäãåçéèêëíìîïñóòôöõúùûü';

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            if( version_compare( $installedVersion, '1.1.11.03.28', '<' ) ) {
                if( !is_dir( SH_FONTS_FOLDER ) ) {
                    mkdir( SH_FONTS_FOLDER );
                }
                // The class datas are not in the same version as this file, or don't exist (installation)
                $this->helper->addClassesSharedMethods( 'sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__ );
                $this->linker->renderer->add_render_tag( 'render_fontSelector', __CLASS__, 'render_fontSelector' );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }

        // Use this method to rebuild all the fonts files.
        // $this->regenerate_all_fonts();
    }

    protected function regenerate_all_fonts() {
        // We first delete all generated fonts files and then re-create them all
        $folders = array( SH_ROOT_FOLDER . 'templates/fonts/' );
        $templates = scandir( SH_ROOT_FOLDER . 'templates/' );
        foreach( $templates as $template ) {
            if( $template != '.' && is_dir( SH_ROOT_FOLDER . 'templates/' . $template . '/fonts/' ) ) {
                $folders[ ] = SH_ROOT_FOLDER . 'templates/' . $template . '/fonts/';
            }
        }

        foreach( $folders as $folder ) {
            $files = scandir( $folder );
            foreach( $files as $file ) {
                if( in_array( substr( $file, -4 ), array( '.png', '.php' ) ) ) {
                    unlink( $folder . $file );
                }
            }
            foreach( $files as $file ) {
                if( substr( $file, -4 ) == '.ttf' ) {
                    $this->prepareFont( $folder . $file );
                }
            }
        }
    }

    protected function prepareFont( $fontFile ) {
        $baseFileName = substr( $fontFile, 0, -4 );
        $boxes = array( );
        for( $a = 6; $a <= 200; $a++ ) {
            if( $a > 30 ) {
                // every 2 (1 + 1)
                $a++;
            }
            if( $a > 50 ) {
                // every 5 (1 + 1 + 3)
                $a += 3;
            }
            if( $a > 90 ) {
                // every 10 (1 + 1 + 3 + 5)
                $a += 5;
            }
            list($fontSize, $tmp, $box) = $this->linker->imagesBuilder->getFontSizeByTextHeight(
                substr( basename( $fontFile ), 0, -4 ) . ' - ' . self::FONT_THUMB_TEXT, $fontFile, $a
            );

            $boxes[ $a ] = array(
                'fontSize' => $fontSize,
                'top' => $box[ 'top' ],
                'left' => $box[ 'left' ],
                'box' => $box[ 'box' ]
            );
        }

        $done++;
        $this->helper->writeArrayInFile(
            $baseFileName . '.php', 'boxes', $boxes
        );

        $this->linker->imagesBuilder->buildTextImage(
            $baseFileName . '.png', basename( $baseFileName ) . ' - ' . self::FONT_THUMB_TEXT, $fontFile, 16
        );
    }

    public function render_fontSelector( $attributes = array( ) ) {
        if( !isset( $attributes[ 'name' ] ) && !isset( $attributes[ 'id' ] ) ) {
            $attributes[ 'name' ] = $attributes[ 'id' ] = 'font';
        } elseif( !isset( $attributes[ 'name' ] ) ) {
            $attributes[ 'name' ] = $attributes[ 'id' ];
        } elseif( !isset( $attributes[ 'id' ] ) ) {
            $attributes[ 'id' ] = $attributes[ 'name' ];
        }

        $list = $this->getList();

        if( isset( $attributes[ 'csv' ] ) ) {
            $entries = explode( ',', $attributes[ 'csv' ] );
            foreach( $list as $key => $oneFont ) {
                if( !in_array( $oneFont[ 'filepath' ], $entries ) ) {
                    unset( $list[ $key ] );
                }
            }
            sort( $entries );
            unset( $attributes[ 'csvEntries' ] );
        }

        if( isset( $attributes[ 'value' ] ) ) {
            $defaultPath = $attributes[ 'value' ];
            $defaultName = basename( $defaultPath );
        }

        $defaultIsSet = false;
        foreach( $list as &$oneFont ) {
            if( $oneFont[ 'name' ] == $defaultName ) {
                $oneFont[ 'state' ] = 'selected';
                $defaultIsSet = true;
                break;
            }
        }
        $values[ 'font' ] = $attributes;
        if( !$defaultIsSet ) {
            $defaultName = $list[ 0 ][ 'name' ];
            $list[ 0 ][ 'state' ] = 'selected';
        }
        $values[ 'font' ][ 'selected' ] = $defaultName;

        $values[ 'fonts' ] = $list;

        return $this->render( 'fontSelector', $values, false, false );
    }

    /**
     * public function addThisFont
     *
     */
    public function addThisFont() {
        $this->onlyMaster();
        if( $this->formSubmitted( 'addFont' ) ) {
            $fileName = $_FILES[ "font" ][ 'name' ];
            if( !is_dir( SH_TEMP_FOLDER . __CLASS__ . '/' ) ) {
                $this->helper->createDir( SH_TEMP_FOLDER . __CLASS__ . '/' );
            }
            if( move_uploaded_file( $_FILES[ "font" ][ 'tmp_name' ], SH_TEMP_FOLDER . __CLASS__ . '/' . $fileName ) ) {
                $sentFile = substr( $fileName, 0, -4 );
                $last = array_pop( explode( '.', $_FILES[ "font" ][ 'name' ] ) );
                if( strtolower( $last ) != 'ttf' ) {
                    $this->linker->html->addMessage( 'Unrecognized file type... <br />Only *.ttf files (for TTF fonts) are accepted.' );
                    $error = true;
                }
            } else {
                $this->linker->html->addMessage( 'There was an error during the file transfert.<br />' );
                $error = true;
            }
            if( !$error ) {
                $this->prepareFont( SH_TEMP_FOLDER . __CLASS__ . '/' . $fileName );
                $this->linker->html->addMessage( $this->getI18n( 'fontAddedSuccessfully' ) );
            }
        }
        header( 'location: ' . $this->linker->path->getLink( 'fonts/add/' ) );
    }

    /**
     * public function add
     *
     */
    public function add() {
        $this->onlyMaster();
        $this->linker->html->setTitle( $this->getI18n( 'title_add' ) );
        $vars[ 'font' ][ 'addlink' ] = $this->linker->path->getLink( 'fonts/addThisFont/' );
        $this->render( 'add', $vars );
        return true;
    }

    /**
     * public function showList
     */
    public function showList() {
        if( !$this->isMaster() ) {
            header( 'location: access_forbiden.php' );
        }
        $this->linker->html->setTitle( 'Polices' );
        $loop[ 'fonts' ] = $this->getList();
        $loop[ 'add' ][ 'link' ] = $this->linker->path->getLink( 'fonts/add/' );
        $this->render( 'list', $loop );
    }

    /**
     * public function getList
     *
     */
    public function getList() {
        // Adding the shared fonts
        $scan = scandir( SH_FONTS_FOLDER );
        if( is_array( $scan ) ) {
            foreach( $scan as $element ) {
                if( substr( $element, 0, 1 ) != '.' && substr( $element, -4 ) == '.ttf' ) {
                    $ret[ $element ] = array(
                        'path' => SH_FONTS_PATH,
                        'filepath' => SH_FONTS_FOLDER . $element,
                        'name' => $element,
                        'preview' => SH_FONTS_PATH . substr( $element, 0, -4 ) . '.png'
                    );
                }
            }
        }
        // Adding the template's specific fonts (if any)
        $specificPath = $this->linker->template->path . 'fonts/';
        if( is_dir( $specificPath ) ) {
            $specificFolder = $this->linker->path->changeToShortFolder( $specificPath );
            $scan = scandir( $this->linker->template->path . 'fonts' );
            if( is_array( $scan ) ) {
                foreach( $scan as $element ) {
                    if( substr( $element, 0, 1 ) != '.' && substr( $element, -4 ) == '.ttf' ) {
                        $ret[ $element ] = array(
                            'path' => $specificFolder,
                            'filepath' => $specificPath . $element,
                            'name' => $element,
                            'preview' => $specificFolder . substr( $element, 0, -4 ) . '.png'
                        );
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * public function modify
     *
     */
    public function modify() {
        if( !$this->isMaster() ) {
            header( 'location: access_forbiden.php' );
        }
        $buttonName = $_GET[ 'name' ];
        $this->linker->html->setTitle( 'Modification du bouton "' . $buttonName . '"' );
        $values = new sh_params( SH_TEMPLATE_FOLDER . 'builder/' . $buttonName . '/params.php' );
        $template[ 'GET' ][ 0 ] = array(
            'width' => $values->get( 'width' ),
            'menuWidth' => $values->get( 'menuButtons>totalWidth' ),
            'expandYes' => ($values->get( 'menuButtons>expand' ) ? 'selected="selected"' : ''),
            'expandNo' => ($values->get( 'menuButtons>expand' ) ? '' : 'selected="selected"')
        );
        foreach( $values->get( 'variations' ) as $variationName => $variation ) {
            $cpt++;
            $loops[ 'VARIATIONS' ][ $cpt ][ 'name' ] = $variationName;
            $loops[ 'VARIATIONS' ][ $cpt ][ 'color' ] = str_replace( '#', '', $variation[ 'color' ] );
            $loops[ 'VARIATIONS' ][ $cpt ][ 'selectedColor' ] = str_replace( '#', '', $variation[ 'selectedColor' ] );
            $loops[ 'VARIATIONS' ][ $cpt ][ 'activeColor' ] = str_replace( '#', '', $variation[ 'activeColor' ] );
        }
        $cpt = 0;
        foreach( array_keys( $values->get( 'fonts' ) ) as $fontName ) {
            $cpt++;
            $loops[ 'FONTS' ][ $cpt ][ 'name' ] = $fontName;
        }
        $this->linker->html->insert( $this->renderer->render( dirname( __FILE__ ) . '/modify.php', $template, $loops ) );
    }

    public function getFilePath( $fontName ) {
        // We first look for it in the template's folder
        if( file_exists( $this->linker->template->path . 'fonts/' . $fontName ) ) {
            return $this->linker->template->path . 'fonts/' . $fontName;
        }
        if( file_exists( SH_FONTS_FOLDER . $fontName ) ) {
            return SH_FONTS_FOLDER . $fontName;
        }
        return false;
    }

    public function master_getMenuContent() {
        $masterMenu[ 'Section Master' ][ ] = array(
            'link' => 'fonts/add/', 'text' => 'Ajouter des polices', 'icon' => 'picto_tool.png'
        );
        return $masterMenu;
    }

    public function admin_getMenuContent() {
        return array( );
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

}