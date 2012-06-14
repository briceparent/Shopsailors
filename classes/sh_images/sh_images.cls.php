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
 * Class that serves images, and asks to build them if possible.
 */
class sh_images extends sh_core {

    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    protected $minimal = array( 'get' => true );
    const LANG_DIR = 'SH_LANG/';

    const PREVIEW_TEXT = 'Shopsailors';

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            // The class datas are not in the same version as this file, or don't exist (installation)

            if( version_compare( $installed_version, '1.1.11.03.28', '<' ) ) {
                $this->linker->db->updateQueries( __CLASS__ );
                $this->db_execute( 'create_table_1', array( ) );
                $this->db_execute( 'create_table_2', array( ) );
            }

            $this->helper->addClassesSharedMethods( 'sh_cron', '', __CLASS__ );
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
    }

    public function getFavicon() {
        $type = $_GET['favicon_type'];
        $favicon = SH_IMAGES_FOLDER . 'favicon' . $type;
        if( !file_exists( $favicon ) ) {
            $favicon = SH_SHAREDIMAGES_FOLDER . 'icons/favicon.png' . $type;
        }
        // It does so, so we send it with the apropriate header
        $contentType = mime_content_type( $favicon );
        header( 'Content-type: ' . $contentType );
        readfile( $favicon );
        return true;
    }

    public function toPng( $src, $destroyOriginal = false ) {
        $parts = explode( '.', $src );
        $fileExtension = strtolower( array_pop( $parts ) );
        if($fileExtension == 'png'){
            return $src;
        }
        if( $fileExtension != 'png' ) {
            $dest = implode( '.', $parts ) . '.png';
        } else {
            $dest = $src;
        }
        if( $fileExtension == "jpg" || $fileExtension == 'jpeg' ) {
            $newImage = ImageCreateFromJpeg( $src );
        } elseif( $fileExtension == 'gif' ) {
            $newImage = imageCreateFromGIF( $src );
        }
        imagepng( $newImage, $dest );
        imagedestroy( $newImage );
        if( $destroyOriginal ) {
            unlink( $src );
        }
        return $dest;
    }

    public function imageExists( $image ) {
        $realImage = $this->linker->path->changeToRealFolder(
            $image
        );
        return (file_exists( $realImage ) || file_exists( SH_ROOT_FOLDER . $realImage ));
    }

    public function cron_job( $time ) {
        if( $time == sh_cron::JOB_HOUR ) {
            // Cleaning old temporary images files (older than 1 hour)
            $tempImages = scandir( SH_TEMPIMAGES_FOLDER );
            foreach( $tempImages as $file ) {
                if( substr( $file, 0, 1 ) != '.' ) {
                    $fileTimestamp = floatval( date( 'YmdHi', filemtime( SH_TEMPIMAGES_FOLDER . $file ) ) );
                    $timestamp = floatval( date( 'YmdHi', mktime( date( 'H' ) - 1, 0, 0, date( 'm' ), date( 'd' ),
                                                                                                            date( 'Y' ) ) ) );
                    if( $fileTimestamp < $timestamp ) {
                        echo 'Removing old image file ' . $file . '<br />';
                        unlink( SH_TEMPIMAGES_FOLDER . $file );
                        $oldImagesFilesDeleted = true;
                    }
                }
            }
            if( !$oldImagesFilesDeleted ) {
                echo 'There was no old temporary images to delete<br />';
            }
        }
        return true;
    }

    /**
     * Gets an image, and send it with it's headers.
     * If it has to be built, launches the building process.
     * If the image isn't found, sends a replacement image.
     * @access Accessed directly from the url. This method shouldn't be called
     * by any other function, because it changes the headers and outputs the image.
     * @return bool true on success
     */
    public function get() {
        $this->linker->cache->disable();
        // we verify if the session exists.
        if( isset( $_SESSION['SH_BUILT'] ) ) {
            // It does, so we verify if the image file exists
            $askedFolder = $_GET['folder'];
            $file = $_GET['file'];
            $buttonType = $_GET['button_type'];
            if( $file == 'createPreview' ) {
                $this->createPreview( $_GET['font'], $_GET['height'] );
                return true;
            }
            // We translate path to folder
            $folder = $this->linker->path->changeToRealFolder( $askedFolder, $file, $buttonType );

            if( isset( $_GET['colorize'] ) && $this->isAdmin() ) {
                $colorize = true;
                $color = $_GET['colorize'];
                unset( $_GET['colorize'] );
            }


            if( $askedFolder == '/images/template/' ) {
                if( file_exists( SH_SITE_FOLDER . 'sh_template/images/' . $file ) ) {
                    $folder = SH_SITE_FOLDER . 'sh_template/images/';
                }
                if( $_SESSION['this_is_a_temp_session'] ) {
                    $folder = SH_SITE_FOLDER . 'sh_template/images/temp/';
                }
            }

            // we verify if the image file exists
            if( file_exists( $folder . $file ) && !$colorize ) {
                // It does, so we send it with the apropriate header
                $contentType = mime_content_type( $folder . $file );
                $this->sendImageHeader( $contentType );
                readfile( $folder . $file );
                return true;
            }
            if( preg_match( '`((.+)\.resized\.([0-9]+)\.([0-9]+))\.png`', $file, $matches ) ) {
                if( file_exists( $folder . '.shape.' . $matches[3] . '.' . $matches[4] . '.xml' ) ) {
                    // We should apply a shape after the image is resized
                    $shapeIt = true;
                    $shapeParams = $folder . '.shape.' . $matches[3] . '.' . $matches[4] . '.xml';
                }
                if( file_exists( $folder . $matches[2] ) ) {
                    $ext = array_pop( explode( '.', $matches[2] ) );
                    copy( $folder . $matches[2], $folder . $matches[1] . '.' . $ext );
                    $this->linker->browser->resize_image( $folder . $matches[1] . '.' . $ext, $matches[3], $matches[4],
                                                          true );
                    if( $shapeIt ) {
                        $this->shape_image( $folder . $file, $shapeParams );
                    }

                    if( !$colorize ) {
                        $contentType = mime_content_type( $folder . $file );
                        $this->sendImageHeader( $contentType );
                        readfile( $folder . $file );
                        return true;
                    }
                }
                if( $colorize ) {
                    $rgbColor = sh_colors::RGBStringToRGBArray( '#' . $color );
                    $baseImage = imagecreatefrompng( $folder . $file );
                    imagesavealpha( $baseImage, true );
                    imagefilter( $baseImage, IMG_FILTER_COLORIZE, $rgbColor['R'], $rgbColor['G'], $rgbColor['B'] );

                    $contentType = mime_content_type( $folder . $file );
                    $this->sendImageHeader( $contentType );
                    imagepng( $baseImage );
                    imagedestroy( $baseImage );
                    return true;
                }
            }
            if( preg_match( '`((.+)\.resizedX\.([0-9]+))\.png`', $file, $matches ) ) {
                if( file_exists( $folder . $matches[2] ) ) {
                    $ext = array_pop( explode( '.', $matches[2] ) );
                    copy( $folder . $matches[2], $folder . $matches[1] . '.' . $ext );
                    $this->linker->browser->resizeX_image( $folder . $matches[1] . '.' . $ext, $matches[3], true );

                    $contentType = mime_content_type( $folder . $file );
                    $this->sendImageHeader( $contentType );
                    readfile( $folder . $file );
                    return true;
                }
            }
            if( preg_match( '`((.+)\.resizedY\.([0-9]+))\.png`', $file, $matches ) ) {
                if( file_exists( $folder . $matches[2] ) ) {
                    $ext = array_pop( explode( '.', $matches[2] ) );
                    copy( $folder . $matches[2], $folder . $matches[1] . '.' . $ext );
                    $this->linker->browser->resizeY_image( $folder . $matches[1] . '.' . $ext, $matches[3], true );

                    $contentType = mime_content_type( $folder . $file );
                    $this->sendImageHeader( $contentType );
                    readfile( $folder . $file );
                    return true;
                }
            }
            // We check if the image is in the ###imagesGeneration table
            list($imageFromDb) = $this->db_execute(
                'getClass', array( 'folder' => SH_IMAGES_PATH . dirname( $file ), 'image' => basename( $file ) )
            );
            if( isset( $imageFromDb['image'] ) ) {
                if( $this->linker->method_exists( $imageFromDb['class'], 'buildImage' ) ) {
                    $ret = $this->linker->$imageFromDb['class']->buildImage( $imageFromDb['folder'],
                                                                             $imageFromDb['image'] );
                    if( !is_dir( $folder . $file ) ) {
                        $this->helper->createDir( dirname( $folder . $file ) );
                    }
                    imagepng( $ret, $folder . $file );
                    if( $ret ) {
                        $contentType = mime_content_type( $folder . $file );
                        $this->sendImageHeader( $contentType );
                        readfile( $folder . $file );
                        return true;
                    }
                }
            }

            // It doesn't, so we verify if the image has to be generated
            if(
                $folder == SH_TEMP_FOLDER
                || substr( $askedFolder . $file, 0, strlen( SH_GENERATEDIMAGES_PATH ) ) == SH_GENERATEDIMAGES_PATH
            ) {
                // We have to generate the image
                $newFile = $this->create( $askedFolder . $file );
                if( $newFile !== false ) {
                    $contentType = mime_content_type( $newFile );
                    $this->sendImageHeader( $contentType );
                    readfile( $newFile );
                    if( $folder == SH_TEMP_FOLDER ) {
                        unlink( $folder . $file );
                    }
                    return true;
                }
                echo basename( __FILE__ ) . ':' . __LINE__ . ' - There was an error!!!';
            }
        }

        // We send the replacement image (picture not found)
        $this->sendImageHeader();
        readfile( SH_SHAREDIMAGES_FOLDER . 'icons/picture_not_found.png' );
        return false;
    }

    protected function shape_image( $image, $params ) {
        // We read the params
        $shapeParams = new DOMDocument();
        $shapeParams->load( realpath( $params ) );

        $xpath = new DOMXpath( $paramsFile );
        $query = '//shape';
        list($entry) = $xpath->query( $query );
        if( $entry->hasAttributes() ) {
            $attributes = $entry->attributes;
            $type = $attributes->getNamedItem( 'type' )->nodeValue;
            $children = $entry->childNodes;
            if( $type == 'corners' ) {
                foreach( $children as $child ) {
                    $nodeName = $child->nodeName;
                    if( $nodeName == 'corner' ) {
                        $attributes = $child->attributes;
                        $position = $attributes->getNamedItem( 'position' );
                        $type = $attributes->getNamedItem( 'type' );
                        $layers[$zindex]['type'] = $attributes->getNamedItem( 'type' );
                    }
                }
            }
        }
    }

    protected function sendImageHeader( $contentType = 'image/png', $cacheDelay = 'oneDay' ) {
        if( $cacheDelay == 'oneDay' ) {
            $cacheDelay = 24 * 60 * 60;
        }
        header( 'Content-Type: ' . $contentType );
        header( "Pragma: Public" );
        header( "Expires: " . gmdate( "D, d M Y H:i:s", time() + $cacheDelay ) . " GMT" );
        header( 'Cache-Control: max-age=' . $cacheDelay . '' );
    }

    protected function createPreview( $font, $height ) {
        if( file_exists( SH_FONTS_FOLDER . $font ) ) {
            $font = SH_FONTS_FOLDER . $font;
        } elseif( file_exists( $this->linker->site->templateFolder . 'fonts/' . $font ) ) {
            $font = $this->linker->site->templateFolder . 'fonts/' . $font;
        } else {
            die( 'The font ' . $font . ' was not found!' );
        }
        $text = self::PREVIEW_TEXT;
        $image = SH_TEMPIMAGES_FOLDER . md5( microtime() );
        $imageBuilder = $this->linker->imagesBuilder;
        list($size) = $imageBuilder->getFontSizeByTextHeight(
            sh_fonts::FONT_THUMB_TEXT, $font, $height
        );
        $dims = $imageBuilder->getDimensions( $text, $size, $font );

        $imageBuilder->createImageWithBackground(
            $image, sh_colors::RGBStringToRGBArray( 'FFFFFF' ), $dims['width'], $dims['height']
        );

        $imageBuilder->addText(
            $text, $image, $dims['left'], $dims['top'], $font, $size, '000000'
        );

        $contentType = mime_content_type( $image );
        header( 'Content-type: ' . $contentType );
        readfile( $image );
        unlink( $image );
        return true;
    }

    public function getImageDimensions( $path ) {
        $oldPath = $path;
        $path = str_replace(
            self::LANG_DIR, $this->linker->i18n->getLang() . '/', $path
        );
        list($image) = $this->db_execute( 'getImage', array( 'path' => $path ) );
        return $image;
    }

    /**
     * Creates an image using the parametters read from the database
     * @param string $path The path of the image to create
     * @return string The name of the image that was created
     */
    public function create( $path ) {
        $oldPath = $path;
        $path = str_replace(
            self::LANG_DIR, $this->linker->i18n->getLang() . '/', $path
        );
        list($image) = $this->db_execute( 'getImage', array( 'path' => $path ) );
        if( !isset( $image['type'] ) && $oldPath != $path ) {
            $path = str_replace(
                self::LANG_DIR, $this->linker->i18n->getDefaultLang() . '/', $oldPath
            );
            return $this->create( $path );
        }

        $imageBuilder = $this->linker->imagesBuilder;
        $destImage = str_replace(
            array( SH_GENERATEDIMAGES_PATH, SH_TEMPIMAGES_PATH ), array( SH_GENERATEDIMAGES_FOLDER, SH_TEMPIMAGES_FOLDER ),
            $image['path']
        );



        $templatePath = $this->linker->site->templateFolder;
        if( file_exists( $templatePath . 'builder/' . $image['type'] . '/params.php' ) ) {
            include($templatePath . 'builder/' . $image['type'] . '/params.php');
            if( $params['creator_version'] == 2 ) {
                $image = $imageBuilder->createButton(
                    $image['type'], $image['position'], $image['state'], $destImage, $image['width'], $image['height'],
                    $image['text'], $image['font'], $image['fontsize'], $image['startX'], $image['startY']
                );
            }
            return $image;
        }


        $imageBuilder->stretchImage(
            $image['type'], $image['position'], $image['state'], $destImage, $image['width'], $image['height']
        );

        // In the case we have to add text
        if( trim( $image['text'] ) != '' ) {
            $imageBuilder->tagImage(
                $image['type'], $image['position'], $image['state'], $destImage, $image['text'], $image['font'],
                $image['fontsize'], $image['startX'], $image['startY']
            );
        }

        return $destImage; //false;
    }

    /**
     * Stores an images' datas into the database, for it to be built later, on its first use.
     * @param string $text Text of the button.<br />
     * Returns false if empty string
     * @param string $font The font that should be used to display $text.<br />
     * Returns false if empty string
     * @param string $fontsize The font size of $text.<br />
     * Returns false if empty string
     * @param string $path Path of the image to create (Will be used to call this image
     * from the html).<br />
     * Creates one if empty string (default).
     * @param string $type Name of the button's model to be used, or empty string ("") for
     * the default type taken from the "defaultBuilder" param of the template's params file.
     * @param string $position Position of the button in a buttons array.<br />
     * Can be set to the value of:
     * <ul><li>sh_imagesBuilder::NORMAL (default), for any image and for images that are neither first or last ones
     * of the array</li>
     * <li>sh_imagesBuilder::FIRST, for the first image of an array</li>
     * <li>sh_imagesBuilder::LAST, for the last image of an array</li>
     * </ul>
     * @param boolean $has3States
     * <ul><li>False (default) for images that don't need ACTIVE and SELECTED states</li>
     * <li>True for images that should change on focus and/or when active</li></ul>
     * @param integer $width The width of the image, in pixels, or 0 (default) for automatic
     * @param integer $height The height of the image, in pixels, or 0 (default) for automatic
     * @param integer $startX The text starting point on X, or null (default) for none
     * @param integer $startY The text starting point on Y, or null (default) for none
     * @return string The path of the image added to the database
     */
    public function prepare( $text, $font, $fontsize, $path='', $type='', $position=sh_imagesBuilder::NORMAL,
                             $has3States=false, $width=0, $height=0, $startX=null, $startY=null ) {
        if( $text == '' || $font == '' || $fontsize == '' ) {
            return false;
        }

        if( $type == '' ) {
            $type = $this->linker->template->defaultBuilder;
        }
        // Prepares a loop to build the states, if needed
        if( $has3States ) {
            $loops = array(
                sh_imagesBuilder::PASSIVE,
                sh_imagesBuilder::ACTIVE,
                sh_imagesBuilder::SELECTED
            );
            $imageReelState = $loops;

            $templatePath = $this->linker->site->templateFolder;
            include($templatePath . 'builder/' . $type . '/params.php');
            if( $params['creator_version'] < 2 ) {
                if( !file_exists( SH_BUILDER_FOLDER . $type . '/active.php' ) ) {
                    $imageReelState[1] = $loops[0];
                }
                if( !file_exists( SH_BUILDER_FOLDER . $type . '/selected.php' ) ) {
                    $imageReelState[2] = $imageReelState[1];
                }
            }
            $spacer = '_';
        } else {
            $loops = array( '' );
        }
        $explodedPath = explode( '|', $path );
        if( $explodedPath[0] == 'folder' || $path == '' ) {
            $path = SH_GENERATEDIMAGES_PATH . $explodedPath[1] . MD5(
                    $text . $font . $fontsize . $type . $element . $position . $width . $height . $startX . $startY
            );
        }
        // Formats the pathes to web accessible mode
        $path = str_replace( SH_ROOT_FOLDER, SH_ROOT_PATH, $path );
        $font = str_replace( SH_ROOT_FOLDER, SH_ROOT_PATH, $font );

        // Prepares the text starting point, if needed
        if( is_null( $startX ) ) {
            $startX = 'NULL';
        } else {
            $startX = '"' . $startX . '"';
        }
        if( is_null( $startY ) ) {
            $startY = 'NULL';
        } else {
            $startY = '"' . $startY . '"';
        }
        // Prepares the images
        foreach( $loops as $num => $element ) {
            $thisPath = $path . $spacer . $element . sh_imagesBuilder::DEFAULTEXT;
            $count++;
            $this->db_execute(
                'insertImage',
                array(
                'text' => $text,
                'font' => $font,
                'fontsize' => $fontsize,
                'path' => $thisPath,
                'type' => $type,
                'state' => $imageReelState[$num],
                'position' => $position,
                'width' => $width,
                'height' => $height,
                'startX' => $startX,
                'startY' => $startY
                ), $debug
            );
        }

        if( $count == 1 ) {
            return $thisPath;
        }
        return $path;
    }

    public function removeGeneratedByFolder( $folder ) {
        $folder = str_replace( SH_GENERATEDIMAGES_FOLDER, SH_GENERATEDIMAGES_PATH, $folder );
        if( substr( $folder, -1 ) == '/' ) {
            $folder = substr( $folder, 0, -1 );
        }
        $this->db_execute(
            'deleteByFolder', array( 'folder' => $folder )
        );
    }

    /**
     * Stores an images' datas into the database, for it to be built later, on its first use.
     * @param string $image The name of the image to be generated (with its path in SH_GENERATEDIMAGES_FOLDER)
     * @param string $class The class that will build the image (calling $class->buildImage($imageId).
     */
    public function prepareGeneration( $image, $class ) {
        $this->linker->db->updateQueries( __CLASS__ );
        $image = str_replace( SH_GENERATEDIMAGES_FOLDER, SH_GENERATEDIMAGES_PATH, $image );
        $folder = dirname( $image );
        $imageName = basename( $image );
        $rep = $this->db_execute(
            'add', array( 'folder' => $folder, 'image' => $imageName, 'class' => $class )
        );
    }

    /**
     * Deletes from the database all the images that should be created into the folder
     * given as parametter.<br />
     * Does nothing to the file system, so the images that had already been generated
     * will still exist and may be shown.
     * @param string $folder The folder name
     * @return true
     */
    public function removeOneFolder( $folder ) {
        $folder = str_replace( SH_ROOT_FOLDER, '/', $folder );
        $this->db_execute( 'deleteOneFolder', array( 'folder' => $folder ) );
        return true;
    }

    public function __tostring() {
        return get_class();
    }

}
