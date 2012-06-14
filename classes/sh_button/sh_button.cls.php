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
 * Class that creates and manages buttons
 */
class sh_button extends sh_core {

    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );

    public function construct() {
        $this->builderFolder = $this->linker->site->templateFolder . 'builder/';
        if( !is_dir( $this->builderFolder ) ) {
            mkdir( $this->builderFolder );
        }
    }

    /**
     * public function getButtonParams
     * Gets the params from a button's variation, in one state
     */
    public function getButtonParams( $type, $variation, $state ) {
        $path = $this->builderFolder . $type . '/';
        $ret = $this->linker->params->get(
            $path . 'params.php', 'variations>' . $variation . '>' . $state
        );
        return $ret;
    }

    /**
     * protected function buildVariations
     *
     */
    protected function buildVariations( $buttonName ) {
        $path = $this->builderFolder . $buttonName . '/';
        $files = scandir( $path . 'model/' );

        if( file_exists( $path . 'passive.php' ) ) {
            $addToPassive = '.' . sh_imagesBuilder::PASSIVE;
        }
        if( file_exists( $path . 'selected.php' ) ) {
            $isThereSelected = true;
            $addToPassive = '.' . sh_imagesBuilder::PASSIVE;
        }
        if( file_exists( $path . 'active.php' ) ) {
            $isThereActive = true;
            $addToPassive = '.' . sh_imagesBuilder::PASSIVE;
        }

        echo 'Successfully built variations : ';
        flush();
        set_time_limit( 0 );
        $mtime = explode( " ", microtime() );
        $starttime = array_sum( $mtime );

        for( $degree = 0; $degree <= 360; $degree+=20 ) {
            if( !is_dir( $path . 'variations/' . $degree ) ) {
                mkdir( $path . 'variations/' . $degree );
            }
            foreach( $files as $file ) {
                $forcedSaturation = 0;
                if( array_pop( explode( '.', $file ) ) == 'png' ) {
                    $srcImage = $path . 'model/' . $file;
                    $shortName = str_replace( '.png', '', $file );
                    $destImage = $path . 'variations/' . $degree . '/' . $shortName . $addToPassive . '.png';

                    if( $degree == 360 ) {
                        $forcedSaturation = -100;
                    }
                    sh_colors::setHueToImage( $srcImage, $destImage, $degree, $forcedSaturation );
                    if( $isThereSelected ) {
                        sh_colors::setHueToImage( $srcImage,
                                                  $path . 'variations/' . $degree . '/' . $shortName . '.' . sh_imagesBuilder::SELECTED . '.png',
                                                  $degree, $forcedSaturation, -10 );
                    }
                    if( $isThereActive ) {
                        sh_colors::setHueToImage( $srcImage,
                                                  $path . 'variations/' . $degree . '/' . $shortName . '.' . sh_imagesBuilder::ACTIVE . '.png',
                                                  $degree, $forcedSaturation, 10 );
                    }
                }
            }
            echo $separator . $degree;
            $separator = ' - ';
            flush();
        }
        $mtime = explode( " ", microtime() );
        echo '<br />Build time : ' . (array_sum( $mtime ) - $starttime) . 's.<br />';
        echo 'Press F5 to reload the form properly.<hr />';
        return true;
    }

    /**
     * public function add
     *
     */
    public function add() {
        $this->onlyMaster();
        if( $this->formSubmitted( 'addButton' ) ) {
            $buttonName = $this->loadZip();
            if( $buttonName !== false ) {
                $rep = $this->linker->imagesBuilder->prepareButtons( $buttonName, $_POST[ 'variation' ] );
                $_GET[ 'name' ] = $buttonName;
                $this->buildVariations( $buttonName );
                $this->linker->html->insert( 'toutes les variations ont été créées avec succès!<br /> ' );
                return true;
            }
        }
        $this->linker->html->setTitle( 'Ajouter un bouton' );
        $this->render( 'add', $values );
        return true;
    }

    /**
     * loadZip
     * Load the zip, unzip the php and png files, deletes the zip, and copy the
     * default params file, if needed
     */
    protected function loadZip() {
        $table = explode( '.', $_FILES[ "button" ][ 'name' ] );
        $last = strtolower( $table[ count( $table ) - 1 ] );
        if( $_FILES[ "button" ][ 'size' ] == 0 ) {
            $this->linker->html->insert( 'Soit le fichier est trop volumineux (>' . ($_POST[ 'MAX_FILE_SIZE' ] / 1024) . ' ko), soit aucun fichier n\'a été selectionné.' );
            return false;
        } elseif( $last == 'zip' ) {
            // Cleans the file name.
            $fileName = strtr( $_FILES[ "button" ][ 'name' ], 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ',
                               'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy' );
            $fileName = preg_replace( '/([^.a-z0-9]+)/i', '_', $fileName );
            if( trim( $fileName ) != '' ) {
                // Copy the file
                if( move_uploaded_file( $_FILES[ "button" ][ 'tmp_name' ], $this->builderFolder . $fileName ) ) {
                    // Deletes the folder if it already exists
                    $buttonName = substr( $fileName, 0, -4 );
                    $mainFolder = $this->builderFolder . $buttonName;
                    if( is_dir( $mainFolder ) ) {
                        $deleted = $this->helper->deleteDir( $mainFolder );
                        if( !$deleted ) {
                            $this->linker->html->insert( 'Droits insuffisants pour supprimer le dossier actuellement en place.<br />' );
                            return false;
                        }
                    }
                    mkdir( $mainFolder );

                    // Unzips the archive
                    $this->linker->zipper->extract(
                        $this->builderFolder . $fileName, $mainFolder, array( 'png', 'php' )
                    );
                    // TODO: Add an "empty php files" routine, to prevent from hacking
                    mkdir( $mainFolder . '/model' );
                    mkdir( $mainFolder . '/variations' );
                    // Deletes the archive
                    unlink( $this->builderFolder . $fileName );
                    return $buttonName;
                }
                $this->linker->html->insert( 'Il y a eu une erreur lors de l\'envoi du fichier. Si le problème persiste, contactez l\'administrateur du site.<br />' );
                return false;
            }
            $this->linker->html->insert( 'Aucun nom n\'a été trouvé!<br />' );
            return false;
        }
        $this->linker->html->insert( $last . ' n\'est pas un format accepté.<br />' );
        return false;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri( $page ) {
        list($class, $method, $id) = explode( '/', $page );
        if( $method == 'add' ) {
            $uri = '/' . $this->shortClassName . '/add.php';
            return $uri;
        }

        return parent::translatePageToUri( $page );
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage( $uri ) {
        if( $uri == '/' . $this->shortClassName . '/add.php' ) {
            $page = $this->shortClassName . '/add/';
            return $page;
        }

        return parent::translateUriToPage( $uri );
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

}
