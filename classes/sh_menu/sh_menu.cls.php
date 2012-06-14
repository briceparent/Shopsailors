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
 * Class that creates and shows the menus.
 */
class sh_menu extends sh_core {

    const CLASS_VERSION = '1.1.11.07.25';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    protected $minimal = array(
        'verifyLength' => true, 'addEntry' => true, 'chooseLink' => true, 'createTextPreview' => true
    );
    private $items = array( );

    const TEMPTEXTIMAGE_FACTOR = 3;

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            // The class datas are not in the same version as this file, or don't exist (installation)
            $this->linker->db->updateQueries( __CLASS__ );

            if( version_compare( $installedVersion, '1.1.11.03.28', '<' ) ) {
                $this->helper->addClassesSharedMethods( 'sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_template', 'change', __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_variation', 'change', __CLASS__ );
                $this->db_execute( 'create_table', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.07.25', '<' ) ) {
                $this->db_execute( 'update_menus_table_1', array( ) );
            }
        }
        $this->uid = $this->getParam( 'uid' );
        $this->linker->html->addCSS( __CLASS__ . '.css', __CLASS__ );

        return true;
    }

    public function cache_prepareForCaching( $content ) {
        $content = preg_replace(
            '`/images/site/generated/sh_menu/menu_([0-9]+)/([a-zA-Z]+_[a-zA-Z]+)/`',
            '/images/site/generated/sh_menu/menu_$1/[SH_LANG]/',
            $content
        );
        return $content;
    }

    public function cache_prepareForUsing( $content ) {
        $content = preg_replace(
            '`(\[SH_LANG\])`',
            $this->linker->i18n->getLang(),
            $content
        );
        return $content;
    }

    public function master_getMenuContent() {
        $masterMenu[ 'Section Master' ][ ] = array(
            'link' => 'menu/unpack_button_type/', 'text' => 'Préparer un jeu de boutons de menu', 'icon' => 'picto_tool.png',
        );
        $masterMenu[ 'Section Master' ][ ] = array(
            'link' => 'menu/unpack_palettes/', 'text' => 'Préparer des palettes', 'icon' => 'picto_tool.png'
        );

        return $masterMenu;
    }

    public function admin_getMenuContent() {
        $myLinks = sh_linker::getInstance();

        $menusNumber = $myLinks->template->menusNumber;
        $menusDescription = $myLinks->template->menusDescription;

        $adminMenu = array( );
        for( $cpt = 0; $cpt < $menusNumber; $cpt++ ) {
            $adminMenu[ 'Contenu' ][ ] = array(
                'link' => 'menu/edit/' . ($cpt + 1),
                'text' => 'Modifier le menu ' . $menusDescription[ $cpt ],
                'icon' => 'picto_modify.png'
            );
        }

        return $adminMenu;
    }

    /**
     * Cleaning old images from disk and db
     */
    public function reset() {
        // So the folder in which the images are is :
        $this->helper->deleteDir( SH_GENERATEDIMAGES_FOLDER );
    }

    /**
     * Changes an element's sitemap priority.
     * @see sh_sitemap for more details
     * @param str $page The page name
     * @return int The sitemap level
     */
    public function changeSitemapPriority( $page ) {
        $ret = $this->db_execute( 'isInMenu', array( 'link' => $page ) );
        if( is_array( $ret ) && $this->getParam( 'sitemap>priority' ) != sh_params::VALUE_NOT_SET ) {
            return $this->getParam( 'sitemap>priority' );
        }
        return 0;
    }

    /**
     * Verifies if the menu is not too large for the template
     * @param bool $booleanReturn
     * <ul><li>if <b>true</b>, will return true if the length is good, false if not</li>
     * <li>if <b>false</b>, will echo "OK" if the length is good, and an error
     * message if not</li></ul>
     * @return bool|str See $booleanReturn
     */
    public function verifyLength( $booleanReturn = false ) {
        $id = $_POST[ 'id' ];

        if( !is_array( $_POST[ 'categories' ] ) ) {
            if( !$booleanReturn ) {
                echo 'OK';
            }
            return true;
        }

        $entriesIds = array_keys( $_POST[ 'categories' ] );
        $langs = array_keys( $_POST[ 'categories' ][ $entriesIds[ 0 ] ][ 'name' ] );

        $params = $this->linker->template->get( 'menuButtons' );

        $sectionsCount = $_POST[ 'sectionsCount' ];

        $maxMenuWidth = $this->linker->template->get(
            'menuButtons>' . $id . '>maxWidth', false
        );
        // If there is no maximum limit to the width, we always answer OK
        if( $maxMenuWidth === false ) {
            if( !$booleanReturn ) {
                echo 'OK';
            }
            return true;
        }

        // If there is no langs, we always answer OK
        if( !is_array( $_POST[ 'categories' ] ) ) {
            if( !$booleanReturn ) {
                echo 'OK';
            }
            return true;
        }

        $defaultLang = $this->linker->site->lang;

        foreach( $_POST[ 'categories' ] as $key => $value ) {
            foreach( $langs as $lang ) {
                if( !empty( $value[ 'name' ][ $lang ] ) ) {
                    $texts[ $lang ][ ] = $value[ 'name' ][ $lang ];
                } else {
                    $texts[ $lang ][ ] = $value[ 'name' ][ $defaultLang ];
                }
            }
        }


        $fonts = $this->linker->template->get(
            'menuButtons>' . $id . '>fonts', array( )
        );

        $font = $_POST[ 'font' ];
        $defaultFont = $this->linker->template->get(
            'menuButtons>' . $id . '>font', null
        );
        if( !$font || !isset( $fonts[ $font ] ) ) {
            $font = $defaultFont;
        }

        $font = $fonts[ $font ];

        // Reads all the parameters from the params file
        $type = $this->linker->template->get( 'menuButtons>' . $id . '>type' );
        $templateVariation = $this->linker->html->getVariation();
        $saturation = $this->linker->site->saturation;

        // do we have to expand the menu to fit the $menuWidth size?
        $expand = $this->linker->template->get(
            'menuButtons>' . $id . '>expand', 1
        );
        $menuWidth = $this->linker->template->get(
            'menuButtons>' . $id . '>totalWidth|width', 900
        );
        $textHeight = $_POST[ 'textHeight' ];

        $builder = $this->linker->imagesBuilder;

        $echo = 'OK';
        $addToEcho = '';

        foreach( $texts as $oneLang => $oneLangTexts ) {
            $box = $this->getMenuDims(
                $oneLangTexts, $font, $textHeight, $type
            );

            $calculatedWidth = $box[ 'width' ];

            // Verifies if the total generated width isn't too big
            if( $calculatedWidth > $menuWidth ) {
                $addToEcho .= '<div style="color:red">' . $this->getI18n( 'tooMuchText' ) . $oneLang . '.</div>';
                $echo = '';
                if( $booleanReturn ) {
                    return false;
                }
            }
        }
        if( !$booleanReturn ) {
            echo $echo . $addToEcho;
        }
        return true;
    }

    public function getMenuDims( $texts, $font, $textHeight, $type ) {
        $this->debug( 'function : ' . __FUNCTION__, 2, __LINE__ );

        $folder = $this->linker->imagesBuilder->builderFolder . $type . '/';

        if( file_exists( $folder . 'params.php' ) ) {
            include($folder . 'params.php');
            $version = $params[ 'creator_version' ];
        }
        if( $version < 2 ) {
            $box = $this->linker->imagesBuilder->getMultipleImagesBox(
                $texts, $font, $textHeight, $type
            );
            return $box;
        }
        $allText = implode( '', $texts );
        list($fontSize, $deltaY) = $this->linker->imagesBuilder->getFontSizeByTextHeight( $allText, $font, $textHeight );

        $valign = $params[ 50 ][ 'valign' ];

        $cpt = 0;
        $totalWidth = 0;
        $maxHeight = 0;
        $parts = array( );
        foreach( $texts as $text ) {
            $textDim = $this->linker->imagesBuilder->getDimensions( $text, $fontSize, $font );
            $parts[ $cpt ][ 'text' ] = $text;
            $parts[ $cpt ][ 'textDims' ] = $textDim;

            if( $cpt == 0 ) {
                // This is a "first" image
                $thisWidth = $params[ 50 ][ 'first' ][ 'minimalWidth' ] + $textDim[ 'width' ];
                if( $thisWidth < $params[ 'minimals' ][ 'first' ][ 'width' ] ) {
                    $parts[ $cpt ][ 'addToWidth' ] = $params[ 'minimals' ][ 'first' ][ 'width' ] - $thisWidth;
                    $thisWidth = $params[ 'minimals' ][ 'first' ][ 'width' ];
                } else {
                    $parts[ $cpt ][ 'addToWidth' ] = 0;
                }
                $parts[ $cpt ][ 'width' ] = $thisWidth;

                $thisHeight = $params[ 50 ][ 'first' ][ 'minimalHeight' ] + $textDim[ 'height' ];
                if( $thisHeight < $params[ 'minimals' ][ 'first' ][ 'height' ] ) {
                    $parts[ $cpt ][ 'addToHeight' ] = $params[ 'minimals' ][ 'first' ][ 'height' ] - $thisHeight;
                    $thisHeight = $params[ 'minimals' ][ 'first' ][ 'height' ];
                } else {
                    $parts[ $cpt ][ 'addToHeight' ] = 0;
                }
                $parts[ $cpt ][ 'height' ] = $thisHeight;
                $heights[ ] = $thisHeight;

                $parts[ $cpt ][ 'position' ] = 'first';
            } elseif( $cpt == count( $texts ) - 1 ) {
                // This is a "last" image
                $thisWidth = $params[ 50 ][ 'middle' ][ 'minimalWidth' ] + $textDim[ 'width' ];
                if( $thisWidth < $params[ 'minimals' ][ 'middle' ][ 'width' ] ) {
                    $parts[ $cpt ][ 'addToWidth' ] = $params[ 'minimals' ][ 'middle' ][ 'width' ] - $thisWidth;
                    $thisWidth = $params[ 'minimals' ][ 'middle' ][ 'width' ];
                } else {
                    $parts[ $cpt ][ 'addToWidth' ] = 0;
                }
                $parts[ $cpt ][ 'width' ] = $thisWidth;

                $thisHeight = $params[ 50 ][ 'middle' ][ 'minimalHeight' ] + $textDim[ 'height' ];
                if( $thisHeight < $params[ 'minimals' ][ 'middle' ][ 'height' ] ) {
                    $parts[ $cpt ][ 'addToHeight' ] = $params[ 'minimals' ][ 'middle' ][ 'height' ] - $thisHeight;
                    $thisHeight = $params[ 'minimals' ][ 'middle' ][ 'height' ];
                } else {
                    $parts[ $cpt ][ 'addToHeight' ] = 0;
                }
                $parts[ $cpt ][ 'height' ] = $thisHeight;
                $heights[ ] = $thisHeight;

                $parts[ $cpt ][ 'position' ] = 'last';
            } else {
                // This is a "middle" image
                $thisWidth = $params[ 50 ][ 'last' ][ 'minimalWidth' ] + $textDim[ 'width' ];
                if( $thisWidth < $params[ 'minimals' ][ 'last' ][ 'width' ] ) {
                    $parts[ $cpt ][ 'addToWidth' ] = $params[ 'minimals' ][ 'last' ][ 'width' ] - $thisWidth;
                    $thisWidth = $params[ 'minimals' ][ 'last' ][ 'width' ];
                } else {
                    $parts[ $cpt ][ 'addToWidth' ] = 0;
                }
                $parts[ $cpt ][ 'width' ] = $thisWidth;

                $thisHeight = $params[ 50 ][ 'last' ][ 'minimalHeight' ] + $textDim[ 'height' ];
                if( $thisHeight < $params[ 'minimals' ][ 'last' ][ 'height' ] ) {
                    $parts[ $cpt ][ 'addToHeight' ] = $params[ 'minimals' ][ 'last' ][ 'height' ] - $thisHeight;
                    $thisHeight = $params[ 'minimals' ][ 'last' ][ 'height' ];
                } else {
                    $parts[ $cpt ][ 'addToHeight' ] = 0;
                }
                $parts[ $cpt ][ 'height' ] = $thisHeight;
                $heights[ ] = $thisHeight;

                $parts[ $cpt ][ 'position' ] = 'middle';
            }

            $totalWidth += $thisWidth;

            $cpt++;
        }
        $maxHeight = max( $heights );
        // We then have to reajust the heights to the maximal height
        foreach( $parts as $cpt => $part ) {
            if( $part[ 'height' ] < $maxHeight ) {
                // We should add some height
                $parts[ $cpt ][ 'addToHeight' ] += $maxHeight - $part[ 'height' ];
                $parts[ $cpt ][ 'height' ] = $maxHeight;
            }
        }

        return array( 'width' => $totalWidth, 'height' => $maxHeight, 'parts' => $parts );
    }

    /**
     * public function chooseLink
     *
     */
    public function chooseLink() {
        $datas[ 'classes' ] = $this->helper->listLinks(
            $_GET[ 'value' ]
        );
        $datas[ 'category' ][ 'id' ] = $_GET[ 'id' ];

        echo $this->render( 'chooseLink', $datas, false, false );
        return true;
    }

    public function buildImage( $folder, $image ) {
        $imagePath = $folder . '/' . $image;
        // We get the image datas
        $reg = '`.*/([0-9]+)/([a-zA-Z]+_[a-zA-Z]+)_([0-9]+)_(hover|passive|selected)\.png`';
        preg_match( $reg, $imagePath, $matches );
        if( empty( $matches ) ) {
            return false;
        }
        list(, $menuId, $lang, $entryId, $state) = $matches;
        $images = $this->linker->images;
        $site = $this->linker->site;
        $this->variation = $site->variation;
        $this->saturation = $site->saturation;
        $templatePath = $site->templateFolder;

        include(SH_SITE_FOLDER . __CLASS__ . '/' . $menuId . '.params.php');
        $this->menuParams = $this->values;
        $menuEntryParams = $this->values[ 'images' ][ $lang ][ $entryId ][ $state ];
        $type = $this->values[ 'buttonType' ];
        // We get the menu type params
        if( file_exists( $templatePath . 'builder/' . $type . '/params.php' ) ) {
            include($templatePath . 'builder/' . $type . '/params.php');
            if( $params[ 'creator_version' ] < 2 ) {
                return false;
            }
        }
        $this->builtImageRoot = $templatePath . 'builder/' . $type . '/';

        $width = $menuEntryParams[ 'width' ];
        $height = $menuEntryParams[ 'height' ];
        $imageHeight = $height;

        $destination = imageCreateTrueColor( $width, $imageHeight );
        imagealphablending( $destination, false );
        imagesavealpha( $destination, true );
        $transparentColor = imagecolorallocatealpha( $destination, 0, 0, 0, 127 );
        imagefill( $destination, 0, 0, $transparentColor );
        $position = $menuEntryParams[ 'position' ];

        $this->allParams = $params;
        foreach( $params as $layerId => $layer ) {
            if( is_int( $layerId ) ) {
                if( $layerId < 45 || $layerId > 55 ) {
                    // We should build the layer and add it to the image
                    $this->addLayer( $destination, $layer, $layer[ $position ][ $state ], $state, $width, $height, 0, 0 );
                } else {
                    // This is the text layer
                    $this->addText_old( $destination, $layer, $menuEntryParams, $position, $state, $width, $height, 0, 0 );
                }
            }
        }
        return $destination;
    }

    protected function addImageOnImage( $background, $foreground, $left = 0, $top = 0 ) {
        $width = imagesx( $foreground );
        $height = imagesy( $foreground );
        imagesavealpha( $background, true );
        imagealphablending( $background, true );

        for( $x = 0; $x < $width; $x++ ) {
            for( $y = 0; $y < $height; $y++ ) {
                $rgb = imagecolorat( $foreground, $x, $y );
                $pixel = imagecolorsforindex( $foreground, $rgb );
                $destX = $left + $x;
                $destY = $top + $y;
                $color = imagecolorallocatealpha( $background, $pixel[ 'red' ], $pixel[ 'green' ], $pixel[ 'blue' ],
                                                  $pixel[ 'alpha' ] );
                imagesetpixel( $background, $destX, $destY, $color );
            }
        }

        return $background;
    }

    protected function addLayer( $destination, $layerParams, $imageParams, $palette, $state, $width, $height, $destX,
                                 $destY ) {
        // we create a temp image
        $tempImage = imagecreatetruecolor( $width, $height );
        imagesavealpha( $tempImage, true );
        imagealphablending( $tempImage, false );
        $transp = imagecolorallocatealpha( $tempImage, 0, 0, 0, 127 );
        imagefill( $tempImage, 0, 0, $transp );

        $variationName = $this->linker->site->variation . '_' . $this->linker->site->saturation;
        $saturations = array( 'reallyDark' => -50, 'dark' => -25, 'normal' => 0, 'shiny' => 25, 'reallyShiny' => 50 );
        $hsvValue = $saturations[ $this->linker->site->saturation ];
        // we loop in all the images parts, to fill an array
        foreach( $imageParams as $partId => $part ) {
            if( preg_match( '`([0-9]+)_([0-9]+)(_x)?(_y)?`', $partId, $matches ) ) {
                $folder = dirname( $part );
                $image = baseName( $part );
                // We check if the variation exists
                $src = $folder . '/variations/' . $variationName . '/' . $image;

                if( !file_exists( $this->builtImageRoot . $src ) ) {
                    if( !is_dir( dirname( $this->builtImageRoot . $src ) ) ) {
                        $this->helper->createDir( dirname( $this->builtImageRoot . $src ) );
                    }

                    if( $this->linker->site->variation < 370 ) {
                        $hue = $this->linker->site->variation;
                        $opacity = 127;

                        if( $palette == 'none' ) {
                            copy( $this->builtImageRoot . $part, $this->builtImageRoot . $src );
                        } else {
                            // We check if we have to use a custom palette
                            if( $palette != 'default' ) {
                                // We do, so we will read the hue in it (present in $this->allParams)
                                //$palette = $this->allParams['palettes'][$layerParams['palette'][$state]];
                                $col = $palette[ $this->linker->site->variation ][ $this->linker->site->saturation ];
                                $hue = $col[ 'H' ];
                                $opacity = 127 - $col[ 'alpha' ];
                            }

                            sh_colors::setHueToImage( $this->builtImageRoot . $part, $this->builtImageRoot . $src, $hue,
                                                      0, $hsvValue, $opacity / 127 );
                        }
                    } else {
                        if( $palette == 'none' ) {
                            copy( $this->builtImageRoot . $src, $this->builtImageRoot . $part );
                        } else {
                            // Greyscale
                            sh_colors::setHueToImage( SH_TEMPIMAGES_FOLDER . 'greyscale.png',
                                                      $this->builtImageRoot . $src, 0, 0, $hsvValue, $opacity / 127
                            );
                            sh_colors::setGreyScaleToImage(
                                $this->builtImageRoot . $part, SH_TEMPIMAGES_FOLDER . 'greyscale.png'
                            );
                        }
                    }
                }
                $parts[ $matches[ 1 ] ][ $matches[ 2 ] ] = array(
                    'src' => $src,
                    'stretchX' => $matches[ 3 ] == '_x',
                    'stretchY' => $matches[ 4 ] == '_y'
                );
                if( $matches[ 3 ] == '_x' ) {
                    $columnsToStretch[ $matches[ 2 ] ] = true;
                }
                if( $matches[ 4 ] == '_y' ) {
                    $linesToStretch[ $matches[ 1 ] ] = true;
                }
            } else {
                if( $partId == 'minimalWidth' ) {
                    $minimimalWidth = $part;
                } elseif( $partId == 'minimalHeight' ) {
                    $minimimalHeight = $part;
                }
            }
        }

        // Calculating the number of pixels to add in width
        $numberOfStretchableX = count( $columnsToStretch );
        $stretchXSize = ( int ) (($width - $minimimalWidth) / $numberOfStretchableX);
        $numberOfPixelsToAddX = ($width - $minimimalWidth) % $numberOfStretchableX;

        $numberOfStretchableY = count( $linesToStretch );
        $stretchYSize = ( int ) (($height - $minimimalHeight) / $numberOfStretchableY);
        $numberOfPixelsToAddY = ($height - $minimimalHeight) % $numberOfStretchableY;


        $top = 0;
        $tempNumberOfPixelsToAddY = $numberOfPixelsToAddY;
        foreach( $parts as $lineNumber => $line ) {
            $left = 0;
            $tempNumberOfPixelsToAddX = $numberOfPixelsToAddX;
            foreach( $line as $cellNumber => $cell ) {
                // We get the image
                $srcImage = imagecreatefrompng( $this->builtImageRoot . $cell[ 'src' ] );
                // We get the image size
                $srcWidth = imagesx( $srcImage );
                $srcHeight = imagesy( $srcImage );
                if( $cell[ 'stretchX' ] ) {
                    if( $tempNumberOfPixelsToAddX > 0 ) {
                        $imageDestX = $stretchXSize + 1;
                        $tempNumberOfPixelsToAddX--;
                    } else {
                        $imageDestX = $stretchXSize;
                    }
                } else {
                    $imageDestX = $srcWidth;
                }
                $totalWidth += $imageDestX;
                if( $cell[ 'stretchY' ] ) {
                    if( $tempNumberOfPixelsToAddY > 0 ) {
                        $imageDestY = $stretchYSize + 1;
                        $tempNumberOfPixelsToAddY--;
                    } else {
                        $imageDestY = $stretchYSize;
                    }
                } else {
                    $imageDestY = $srcHeight;
                }
                // We copy the image
                imagecopyresampled(
                    $tempImage, $srcImage, $destX + $left, $destY + $top, 0, 0, $imageDestX, $imageDestY, $srcWidth,
                    $srcHeight
                );
                imagedestroy( $srcImage );
                $left += $imageDestX;
            }
            $totalWidth = 0;
            $top += $imageDestY;
        }
        // We then copy the content of the temp image on the destination image
        imagealphablending( $destination, true );
        imagecopy( $destination, $tempImage, 0, 0, 0, 0, $width, $height );
        imagealphablending( $destination, false );
        return $destination;
    }

    /**
     * Saves the menu
     */
    protected function updateDB( $id = '' ) {
        if( empty( $id ) ) {
            $id = ( int ) $this->linker->path->page[ 'id' ];
        }
        $this->cachedPart_remove( '.*' );

        $this->helper->emptyDir( SH_GENERATEDIMAGES_FOLDER . __CLASS__ );

        $menuParamsFile = SH_SITE_FOLDER . __CLASS__ . '/' . $id . '.params.php';
        // We remove the old file
        if( file_exists( $menuParamsFile ) ) {
            unlink( $menuParamsFile );
        }
        $this->linker->params->addElement( $menuParamsFile, true, true );
        $this->linker->params->set( $menuParamsFile, 'activated', isset( $_POST[ 'menuState' ] ) );

        $this->setParam( 'activated>' . $id, isset( $_POST[ 'menuState' ] ) );

        $sectionsCount = $_POST[ 'sectionsCount' ];

        $maxMenuWidth = $this->linker->template->get(
            'menuButtons>' . $id . '>maxWidth', false
        );
        if( is_array( $_POST[ 'categories' ] ) ) {
            $oneCategory = current( $_POST[ 'categories' ] );
            $langs = array_keys( $oneCategory[ 'name' ] );
        } else {
            $langs = array( );
        }

        $defaultLang = $this->linker->site->lang;

        // Reads all the parameters from the params file and POST datas
        $textHeight = $_POST[ 'textHeight' ];

        $this->linker->params->set( $menuParamsFile, 'textHeight', $textHeight );

        $type = $this->linker->template->get( 'menuButtons>' . $id . '>type' );
        $menuButtons = $this->linker->template->get( 'menuButtons' );

        $templateVariation = $this->linker->html->getVariation();

        $images = $this->linker->images;
        $site = $this->linker->site;
        $variation = $site->variation;
        $saturation = $site->saturation;

        // Sets the font to use
        $fonts = $this->linker->template->get(
            'menuButtons>' . $id . '>fonts', array( )
        );

        $font = $_POST[ 'font' ];
        $defaultFont = $this->linker->template->get(
            'menuButtons>' . $id . '>font', null
        );
        if( !$font || !isset( $fonts[ $font ] ) ) {
            $font = $defaultFont;
        }
        $this->linker->params->set( $menuParamsFile, 'font', $font );

        $font = $fonts[ $font ];

        $this->writeParams();

        $path = 'menu_' . SH_SITENAME . '_' . $id . '/';

        // Removes all images from a folder on the disk and in the db
        $this->helper->deleteDir( SH_GENERATEDIMAGES_FOLDER . $path );
        $images->removeOneFolder( SH_GENERATEDIMAGES_PATH . $path );

        // Creates the images builder
        $imagesBuilder = $this->linker->imagesBuilder;

        // We remove from the i18n the old i18n menu entries
        $images = $this->getParamForMenu( $id, 'entries', array( ) );
        foreach( $images as $entryId => $entry ) {
            $oldI18nEntries[ ] = $entry[ 'title' ];
        }

        $this->setParamForMenu( $id, 'entries', array( ) );

        if( is_array( $oldI18nEntries ) ) {
            foreach( $oldI18nEntries as $oldI18nEntry ) {
                $this->removeI18n( $oldI18nEntry );
            }
        }

        // We get the builder params file
        $templatePath = $this->linker->site->templateFolder;
        $this->builtImageRoot = $templatePath . 'builder/' . $type . '/';
        include($this->builtImageRoot . 'params.php');

        // We should save the menu
        if( is_array( $_POST[ 'categories' ] ) ) {
            $cpt = 0;
            foreach( $_POST[ 'categories' ] as $key => $value ) {
                // Saves the texts in the i18n db
                $langs = array_keys( $value[ 'name' ] );
                $i18nId = $this->setI18n( 0, $value[ 'name' ] );
                $menuLink = $value[ 'link' ];

                $image = SH_GENERATEDIMAGES_PATH . __CLASS__ . '/menu_' . $id . '/' . sh_images::LANG_DIR . 'image_' . $cpt;

                // Adds the menu entry
                $this->setParamForMenu(
                    $id, 'entries>' . $cpt,
                    array(
                    'menuId' => $id,
                    'category' => $cpt,
                    'link' => $menuLink,
                    'title' => $i18nId,
                    'position' => 0,
                    'image' => $image
                    )
                );
                $cpt++;
            }
        }
        $this->linker->params->set( $menuParamsFile, 'langs', $langs );
        $this->linker->params->write( $menuParamsFile );
        $this->rebuildMenuImages( $id );
        return 'OK';
    }

    public function rebuildMenuImages( $id = null ) {
        if( is_null( $id ) ) {
            // We should rebuild every menus
            $menus = $this->linker->template->get( 'menuButtons' );
            foreach( $menus as $id => $menu ) {
                $this->rebuildMenuImages( $id );
            }
        }

        // We get the builder for this menu
        $type = $this->linker->template->get( 'menuButtons>' . $id . '>type' );
        $templatePath = $this->linker->site->templateFolder;
        $builder = $templatePath . 'builder/' . $type . '/';
        include($builder . 'params.php');
        $this->builtImageRoot = $builder;

        $menuParamsFile = SH_SITE_FOLDER . __CLASS__ . '/' . $id . '.params.php';
        $this->linker->params->addElement( $menuParamsFile, true, true );
        $menuParams = $this->linker->params->get( $menuParamsFile, '', array( ) );

        $textHeight = $menuParams[ 'textHeight' ];

        // Sets the font to use
        $fonts = $this->linker->template->get(
            'menuButtons>' . $id . '>fonts', array( )
        );
        $font = $menuParams[ 'font' ];
        $defaultFont = $this->linker->template->get(
            'menuButtons>' . $id . '>font', null
        );
        if( !isset( $fonts[ $font ] ) ) {
            $font = $defaultFont;
        }

        $font = $fonts[ $font ];

        list($fontSize, $vDelta, $box) = $this->linker->imagesBuilder->getFontSizeByHeight( $font, $textHeight );
        $insertionPointY = abs( $box[ 5 ] );

        $expand = $this->linker->template->get( 'menuButtons>' . $id . '>expand' );
        $totalWidth = $this->linker->template->get( 'menuButtons>' . $id . '>totalWidth', 900 );
        $maxWidth = $this->linker->template->get( 'menuButtons>' . $id . '>maxWidth', false );

        foreach( $menuParams[ 'langs' ] as $lang ) {
            // We should calculate the total length
            $totalLength = 0;
            foreach( $menuParams[ 'entries' ] as $positionNumber => $entry ) {
                $text = $this->getI18n( $entry[ 'title' ], $lang );
                $menuParams[ 'entries' ][ $positionNumber ][ 'text' ] = $text;
                if( $positionNumber == 0 ) {
                    $position = 'first';
                } elseif( $positionNumber == count( $menuParams[ 'entries' ] ) - 1 ) {
                    $position = 'last';
                } else {
                    $position = 'middle';
                }
                $width = $this->getTextWidth( $font, $fontSize, $text );
                $width += $params[ 'minimals' ][ $position ][ 'width' ];
                $menuParams[ 'entries' ][ $positionNumber ][ 'position' ] = $position;
                $totalLength += $width;
                $menuParams[ 'entries' ][ $positionNumber ][ 'width' ] = $width;
            }

            $widthToAdd = 0;
            if( $expand ) {
                $widthToSplit = $totalWidth - $totalLength;
                $widthToAdd = floor( $widthToSplit / count( $menuParams[ 'entries' ] ) );
                $numberOfElementsToAdd1 = $widthToSplit % count( $menuParams[ 'entries' ] );
            }

            foreach( $menuParams[ 'entries' ] as $positionNumber => $entry ) {
                $addThisToWidth = 0;
                if( $numberOfElementsToAdd1 > 0 ) {
                    $addThisToWidth = 1;
                    $numberOfElementsToAdd1--;
                }
                $position = $entry[ 'position' ];
                $text = $entry[ 'text' ];
                $width = $entry[ 'width' ] + $widthToAdd + $addThisToWidth;
                $height = $textHeight + $params[ 'minimals' ][ $position ][ 'height' ];
                foreach( array( 'hover', 'passive', 'selected' ) as $state ) {

                    // Creation of the image with a transparent background
                    $destination = imageCreateTrueColor( $width, $height );
                    imagealphablending( $destination, false );
                    imagesavealpha( $destination, true );
                    $transparentColor = imagecolorallocatealpha( $destination, 0, 0, 0, 127 );
                    imagefill( $destination, 0, 0, $transparentColor );

                    // Adding every layers
                    foreach( $params as $layerId => $layer ) {
                        if( is_int( $layerId ) ) {
                            if( isset( $layer[ 'palettes' ][ $state ] ) ) {
                                $palette = $params[ 'palettes' ][ $layer[ 'palettes' ][ $state ] ];
                            } elseif( $layer[ 'palette' ] == 'none' ) {
                                $palette = 'none';
                            } elseif( isset( $layer[ 'palette' ] ) ) {
                                $palette = $params[ 'palettes' ][ $layer[ 'palette' ] ];
                            } else {
                                $palette = 'default';
                            }
                            if( $layerId < 45 || $layerId > 55 ) {
                                // We should build the layer and add it to the image
                                $this->addLayer(
                                    $destination, $layer, $layer[ $position ][ $state ], $palette, $state, $width,
                                    $height, 0, 0
                                );
                            } else {
                                // margins :
                                $vMargins = $layer[ 'margins' ][ $state ];
                                $hMargins = $layer[ 'margins' ][ $position . 's' ];

                                // Alignments
                                $hAlign = $layer[ 'align' ];
                                $vAlign = $layer[ 'valign' ];

                                // This is the text layer
                                $destination = $this->addText( $destination, $text, $font, $fontSize, $textHeight,
                                                               $insertionPointY, $palette, $vMargins, $hMargins,
                                                               $hAlign, $vAlign );
                            }
                        }
                    }
                    $this->helper->createDir( SH_GENERATEDIMAGES_FOLDER . __CLASS__ . '/menu_' . $id . '/' . $lang . '/' );
                    imagepng( $destination,
                              SH_GENERATEDIMAGES_FOLDER . __CLASS__ . '/menu_' . $id . '/' . $lang . '/image_' . $positionNumber . '_' . $state . '.png' );

                    imagedestroy( $destination );
                }
            }
        }
    }

    protected function addText( $destination, $text, $font, $fontSize, $textHeight, $insertionPointY, $palette,
                                $vMargins, $hMargins, $hAlign, $vAlign ) {
        //$destination, $layer, $menuEntryParams, $position, $state, $width, $height, $top, $left) {
        // We check if we have to use a custom palette        
        $destWidth = imagesx( $destination );
        $destHeight = imagesy( $destination );

        $textImage = imagecreatetruecolor( $destWidth, $destHeight );

        $rgbCol = $palette[ $this->linker->site->variation ][ $this->linker->site->saturation ];

        // Creating the text color
        $textColor = imagecolorallocatealpha(
            $destination, $rgbCol[ 'R' ], $rgbCol[ 'G' ], $rgbCol[ 'B' ], $rgbCol[ 'alpha' ]
        );

        // Calculating the margins
        $textHSpace = $destWidth - $hMargins[ 'min' ]; // outer horizontal space
        $realTextWidth = $this->getTextWidth( $font, $fontSize, $text );
        $leftInsertionPoint = $hMargins[ 'left' ];
        if( $textHSpace > $realTextWidth ) {
            // We should add some space
            if( $hAlign == 'center' ) {
                $leftInsertionPoint += ( $textHSpace - $realTextWidth) / 2;
            } elseif( $hAlign == 'right' ) {
                $leftInsertionPoint += $textHSpace - $realTextWidth;
            }
        }
        $textVSpace = $destHeight - $vMargins[ 'min' ]; // outer vertical space
        $topInsertionPoint = $vMargins[ 'top' ] + $insertionPointY;
        if( $textVSpace > $textHeight ) {
            if( $vAlign == 'middle' ) {
                $topInsertionPoint += ( $textVSpace - $textHeight) / 2;
            } elseif( $vAlign == 'bottom' ) {
                $topInsertionPoint += $textVSpace - $textHeight;
            }
        }

        // Making sure the image is prepared
        imagesavealpha( $destination, true );

        $transp = imagecolorallocatealpha( $textImage, 0, 0, 0, 127 );
        imagefill( $textImage, 0, 0, $transp );

        imagesavealpha( $textImage, true );
        imagealphablending( $textImage, false );
        imagettftext(
            $textImage, $fontSize, 0, $leftInsertionPoint, $topInsertionPoint, $textColor, $font, $text
        );

        imagealphablending( $destination, true );
        imagecopy( $destination, $textImage, 0, 0, 0, 0, $destWidth, $destHeight );
        imagealphablending( $destination, false );

        return $destination;
    }

    protected function imagecopymerge_alpha( $dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct,
                                             $trans = NULL ) {
        $dst_w = imagesx( $dst_im );
        $dst_h = imagesy( $dst_im );

        // bounds checking
        $src_x = max( $src_x, 0 );
        $src_y = max( $src_y, 0 );
        $dst_x = max( $dst_x, 0 );
        $dst_y = max( $dst_y, 0 );
        if( $dst_x + $src_w > $dst_w )
            $src_w = $dst_w - $dst_x;
        if( $dst_y + $src_h > $dst_h )
            $src_h = $dst_h - $dst_y;

        for( $x_offset = 0; $x_offset < $src_w; $x_offset++ )
            for( $y_offset = 0; $y_offset < $src_h; $y_offset++ ) {
                // get source & dest color
                $srccolor = imagecolorsforindex( $src_im,
                                                 imagecolorat( $src_im, $src_x + $x_offset, $src_y + $y_offset ) );
                $dstcolor = imagecolorsforindex( $dst_im,
                                                 imagecolorat( $dst_im, $dst_x + $x_offset, $dst_y + $y_offset ) );

                // apply transparency
                if( is_null( $trans ) || ($srccolor !== $trans) ) {
                    $src_a = $srccolor[ 'alpha' ] * $pct / 100;
                    // blend
                    $src_a = 127 - $src_a;
                    $dst_a = 127 - $dstcolor[ 'alpha' ];
                    $dst_r = ($srccolor[ 'red' ] * $src_a + $dstcolor[ 'red' ] * $dst_a * (127 - $src_a) / 127) / 127;
                    $dst_g = ($srccolor[ 'green' ] * $src_a + $dstcolor[ 'green' ] * $dst_a * (127 - $src_a) / 127) / 127;
                    $dst_b = ($srccolor[ 'blue' ] * $src_a + $dstcolor[ 'blue' ] * $dst_a * (127 - $src_a) / 127) / 127;
                    $dst_a = 127 - ($src_a + $dst_a * (127 - $src_a) / 127);
                    $color = imagecolorallocatealpha( $dst_im, $dst_r, $dst_g, $dst_b, $dst_a );
                    // paint
                    if( !imagesetpixel( $dst_im, $dst_x + $x_offset, $dst_y + $y_offset, $color ) )
                        return false;
                    imagecolordeallocate( $dst_im, $color );
                }
            }
        return true;
    }

    protected function addText_old( $destImage, $layerParams, $menuEntryParams, $position, $state, $width, $height,
                                    $destX, $destY ) {
        // We check if we have to use a custom palette
        if( isset( $layerParams[ 'palettes' ][ $state ] ) ) {
            // We do, so we will read the hue in it (present in $this->allParams)
            $palette = $this->allParams[ 'palettes' ][ $layerParams[ 'palettes' ][ $state ] ];
            $rgbCol = $palette[ $this->variation ][ $this->saturation ];
        } else {
            // We can't write without a palette
            return false;
        }
        // Top and bottom margins :
        $vmargins = $layerParams[ 'margins' ][ $state ];

        // Left and right margins :
        $hmargins = $layerParams[ 'margins' ][ $position . 's' ];

        $align = $layerParams[ 'align' ];
        $valign = $layerParams[ 'valign' ];

        // We need to know the text size
        if( !isset( $this->menuParams[ 'font' ] ) ) {
            $font = $this->linker->fonts->getFilePath( $this->menuParams[ 'font' ] );
        } else {
            $font = $this->menuParams[ 'font' ];
        }

        if( !isset( $this->menuParams[ 'text' ] ) ) {
            $text = $menuEntryParams[ 'text' ];
        } else {
            $text = $this->menuParams[ 'text' ];
        }

        $textHeight = $this->menuParams[ 'textHeight' ];
        $size = $this->menuParams[ 'fontSize' ];

        $box = $this->linker->imagesBuilder->getDimensions( $text, $size, $font );

        // We first check if we have to add extra margins
        if( $valign == 'bottom' ) {
            $vPos = $height - $vmargins[ 'bottom' ] - $textHeight - $menuEntryParams[ 'addToHeight' ];
        } elseif( $valign == 'top' ) {
            $vPos = $vmargins[ 'top' ] + $menuEntryParams[ 'addToHeight' ];
        } else {
            $vPos = $vmargins[ 'top' ] + $box[ 'height' ] + $menuEntryParams[ 'addToHeight' ];
        }

        if( $align != 'left' ) {
            $delta = $width - $box[ 'width' ] - $hmargins[ 'min' ];
            if( $delta > 0 ) {
                if( $align == 'right' ) {
                    $hmargins[ 'left' ] += $delta;
                } else {
                    $hmargins[ 'left' ] += $delta / 2;
                }
            }
        }

        $textColor = imagecolorallocatealpha(
            $destImage, $rgbCol[ 'R' ], $rgbCol[ 'G' ], $rgbCol[ 'B' ], $rgbCol[ 'alpha' ]
        );
        $destX = $hmargins[ 'left' ] + $menuEntryParams[ 'addToWidth' ] + $destX;
        $destY += $vPos;


        imagesavealpha( $destImage, true );
        imagealphablending( $destImage, false );


        $zoomFactor = 1;

        $tempTextHeight = $textHeight * $zoomFactor;
        $tempSizeDatas = $this->linker->imagesBuilder->getFontSizeByTextHeight(
            $text, $font, $tempTextHeight
        );
        $tempTextSize = $tempSizeDatas[ 0 ];

        $tempLeft = $tempSizeDatas[ 2 ][ 'left' ];
        $tempTop = $tempSizeDatas[ 2 ][ 'top' ];
        $tempWidth = $tempSizeDatas[ 2 ][ 'width' ];
        $tempHeight = $tempSizeDatas[ 2 ][ 'height' ];

        $realFactor = 1; //$box['width'] / $tempWidth;
        $leftPosition = $destX + $menuEntryParams[ 'addToWidth' ] + $menuEntryParams[ 'addToWidth' ];
        $topPosition = round( $vmargins[ 'top' ] + ($tempHeight - $tempTop) * $realFactor ) + $menuEntryParams[ 'addToHeight' ];
        $destWidth = $box[ 'width' ];
        $destHeight = $box[ 'height' ];

        imagettftext(
            $destImage, $textHeight, 0, $leftPosition, $topPosition, $textColor, $font, $text
        );

        // test
        $tests = array( 'Agenda', 'Contact', 'aeruiosmwxcvn', 'fzypqgj', 'tdfhkl' );
        $imgtest = imagecreatetruecolor( 750, $height );
        $white = imagecolorallocate( $imgtest, 255, 255, 255 );
        $black = imagecolorallocate( $imgtest, 0, 0, 0 );
        imagefill( $imgtest, 0, 0, $white );
        foreach( $tests as $id => $test ) {
            imagettftext( $imgtest, 15, 0, $id * 150, $topPosition, $black, $font, $test );
        }
        imagepng( $imgtest, SH_TEMP_FOLDER . 'test_sizes.png' );
        /*
          $im = imagecreatetruecolor($tempWidth, $tempHeight);

          // Create colors and draw transparent background
          imagealphablending($destImage, false);
          imagesavealpha($destImage,true);
          imagealphablending($im, false);
          imagesavealpha($im,true);
          $trans = imagecolorallocatealpha($im, 0, 0, 0, 127);
          imagefilledrectangle($im, 0, 0, $tempWidth, $tempHeight, $trans);
          imagettftext(
          $im, $tempTextSize, 0,
          $tempLeft, $tempTop,
          $textColor, $font, $text
          );
          //imagealphablending($im, true);

          // We create another temp image with the good size
          $text = imagecreatetruecolor($destWidth, $destHeight);
          imagealphablending($text, false);
          imagesavealpha($text,true);

          imagecopyresampled(
          $text,
          $im,
          0,
          0,
          0,
          0,
          $destWidth,
          $destHeight,
          $tempWidth,
          $tempHeight
          );

          $destImage = $this->addImageOnImage($destImage, $text, $leftPosition, $topPosition);

          imagedestroy($im);
         */
    }

    protected function getTextWidth( $font, $fontSize, $text ) {
        $boundingBox = imagettfbbox( $fontSize, 0, $font, $text );
        $width = $boundingBox[ 2 ] - $boundingBox[ 0 ];
        return $width;
    }

    /**
     * Edits a menu
     * @return bool always returns true
     */
    public function edit() {
        $this->onlyAdmin( true );
        $id = ( int ) $this->linker->path->page[ 'id' ];
        if( $this->formSubmitted( 'menuEditor' ) ) {
            // We verify another time that all is good in the form
            if( $this->verifyLength( true ) ) {
                $this->uid = substr( md5( microtime() ), 0, 6 );
                $this->setParam( 'uid', $this->uid );
                $this->writeParams();
                $this->updateDB();
            }
        }

        //loads the external files (JS & CSS)
        $this->linker->html->addScript( '/sh_menu/singles/menuEditor.js' );
        $this->linker->html->addToBody( 'onLoad', 'createSortables();' );

        // Reads button type's params to get the available fonts
        $type = $this->linker->template->get( 'menuButtons>' . $id . '>type' );

        $_SESSION[ __CLASS__ ][ 'links' ] = array( );
        $_SESSION[ __CLASS__ ][ 'links' ][ 'menuId' ] = $id;

        //Removes the cache
        $menuForCache = array( 'module' => get_class(), 'type' => $id );
        sh_cache::removeCache();
        //Gets the menus in the db
        $values[ 'sections' ] = $this->getForRenderer( $id );
        $values[ 'menu' ][ 'id' ] = $id;
        $values[ 'menu' ][ 'type' ] = $type;
        $values[ 'menu' ][ 'modifylink' ] = $this->linker->path->getUri(
            'menu/modifyLink/'
        );

        $fonts = $this->linker->template->get( 'menuButtons>' . $id . '>fonts', array( ) );

        $values[ 'fonts' ][ 'allowed' ] = implode( ',', $fonts );

        $font = $this->linker->fonts->getFilePath( $this->getParamForMenu( $id, 'font', false ) );
        $defaultFont = $this->linker->template->get(
            'menuButtons>' . $id . '>font', null
        );

        if( !$font || !in_array( $font, $fonts ) ) {
            $values[ 'font' ][ 'actual' ] = $this->linker->fonts->getFilePath( $defaultFont );
        } else {
            $values[ 'font' ][ 'actual' ] = $font;
        }

        $values[ 'menu' ][ 'id' ] = $id;

        $values[ 'menu' ][ 'state' ] = $this->helper->addChecked(
            $this->getParamForMenu( $id, 'activated', true )
        );

        // Gets the actual text height from the user's params or the template's params
        $actualTextHeight = $this->getParamForMenu( $id, 'textHeight', false );
        if( !$actualTextHeight ) {
            $actualTextHeight = $this->linker->template->get(
                'menuButtons>' . $id . '>textHeight', 20
            );
        }
        $textHeights = array( 12, 14, 16, 18, 20, 25, 30, 35, 40, 50, 60, 70, 80 );
        foreach( $textHeights as $textHeight ) {
            if( $textHeight == $actualTextHeight ) {
                $values[ 'textHeights' ][ ] = array(
                    'height' => $textHeight,
                    'state' => 'selected'
                );
            } else {
                $values[ 'textHeights' ][ ][ 'height' ] = $textHeight;
            }
        }

        $this->render( 'edit', $values );
        return true;
    }

    /**
     * addEntry adds an entry to the menu
     * @return str status
     * Echoes 'OK' if the entry was added successfully
     */
    public function addEntry() {
        $menuId = $_POST[ 'menuId' ];
        $_SESSION[ __CLASS__ ][ 'addEntry' ][ $menuId ] = true;
        echo 'OK';
        return true;
    }

    /**
     * public function getForRenderer
     */
    public function getForRenderer( $menuId ) {
        $elements = $this->getParamForMenu( $menuId, 'entries', array( ) );
        if( $_SESSION[ __CLASS__ ][ 'addEntry' ][ $menuId ] ) {
            $entryId = $this->getMaxMenuEntryId( $menuId ) + 1;
            $elements[ $entryId ] = array(
                'menuId' => $menuId,
                'category' => 1000,
                'link' => '',
                'title' => 0,
                'position' => 0,
                'image' => ''
            );
            unset( $_SESSION[ __CLASS__ ][ 'addEntry' ][ $menuId ] );
        }

        if( is_array( $elements ) ) {
            foreach( $elements as &$element ) {
                $sections .= ',\'category_' . $element[ 'category' ] . '\'';
            }
            $this->linker->html->addTextScript(
                'sections = [\'absent\'' . $sections . '];
    function createSortables(){
        Sortable.create(\'container\',{tag:\'div\',only:\'section\',handle:\'handle\'});
    }'
            );
        }

        return $elements;
    }

    public function getInArray( $menuId ) {
        $elements[ 'sections' ] = $this->getParamForMenu( $menuId, 'entries', array( ) );

        foreach( $elements as $element ) {
            $cat = $element[ 'category' ];
            $pos = $element[ 'position' ];
            $ret[ $cat ][ $pos ][ 'title' ] = $element[ 'title' ];
            $ret[ $cat ][ $pos ][ 'image' ] = $element[ 'image' ];
            $ret[ $cat ][ $pos ][ 'link' ] = $element[ 'link' ];
        }
        return $ret;
    }

    /** function  insert
     * Description:  Inserts an element to the breadcrumbs' list
     * if no target is given, there will be no link...
     */
    public function insert( $name, $target = '' ) {
        $this->items[ ] = array( 'name' => $name, 'target' => $target );
    }

    public function getForMobile() {
        $menu = $this->get( 1, 'array' );
        foreach( $menu[ 'sections' ] as $id => $menuEntry ) {
            $data[ $id ] = array(
                'text' => $this->linker->i18n->get( 'sh_menu', $menuEntry[ 0 ][ 'title' ] ),
                'link' => $this->linker->path->getLink( $menuEntry[ 0 ][ 'link' ] )
            );
        }
        return $data;
    }

    protected function getMaxMenuEntryId( $menuId ) {
        $entries = array_keys( $this->getParamForMenu( $menuId, 'entries', array( ) ) );
        if( empty( $entries ) ) {
            return 0;
        }
        return max( $entries );
    }

    protected function getParamForMenu( $menuId, $paramName, $defaultValue = sh_params::VALUE_NOT_SET ) {
        $menuParamsFile = SH_SITE_FOLDER . __CLASS__ . '/' . $menuId . '.params.php';
        $this->linker->params->addElement( $menuParamsFile, true );
        $params = $this->linker->params->get( $menuParamsFile, '', 'empty' );
        return $this->linker->params->get( $menuParamsFile, $paramName, $defaultValue );
    }

    protected function setParamForMenu( $menuId, $paramName, $paramValue ) {
        $menuParamsFile = SH_SITE_FOLDER . __CLASS__ . '/' . $menuId . '.params.php';
        $this->linker->params->addElement( $menuParamsFile, true );
        return $this->linker->params->set( $menuParamsFile, $paramName, $paramValue );
    }

    protected function writeParamsForMenu( $menuId ) {
        $menuParamsFile = SH_SITE_FOLDER . __CLASS__ . '/' . $menuId . '.params.php';
        $this->linker->params->addElement( $menuParamsFile, true );
        return $this->linker->params->write( $menuParamsFile );
    }

    public function get( $menuId, $menuType = '' ) {
        $activated = $this->getParamForMenu( $menuId, 'activated', true );
        $this->linker->html->addScript( $this->getSinglePath() . 'rolling_submenus.js' );

        $page = $this->linker->path->getPage();
        if( strtolower( $menuType ) != 'array' ) {
            $fromCache = $this->cachedPart_get( 'menu_' . $menuId . '_' . $page );
            if( $fromCache ) {
                return $fromCache;
            }
        }

        if( false && strtolower( $menuType ) == 'array' ) {
            if( !$activated ) {
                $elements[ 'sections' ] = array( );
                return $elements[ 'sections' ];
            }
            $elements[ 'sections' ] = $this->getInArray( $menuId );
            return $elements;
        }

        $elements[ 'menu' ][ 'container' ] = 'myMenu_' . $menuId;
        
        $lang = $this->linker->i18n->getLang();
        if( $activated ) {
            $elements[ 'sections' ] = $this->getParamForMenu( $menuId, 'entries', array( ) );

            $elements[ 'menu' ][ 'id' ] = $menuId;
            if( is_array( $elements[ 'sections' ] ) ) {
                if( $this->linker->template->get( 'menuButtons>' . $menuId . '>hasSubmenus', false ) ) {
                    $elements[ 'menu' ][ 'hasSubmenus' ] = true;
                }
                if( $this->getParam( 'menu_' . $menuId . '>hasSubmenus', false ) ) {
                    $elements[ 'menu' ][ 'hasSubmenus' ] = true;
                }
                $thisPage = $this->linker->path->getPage();
                $thisLink = $this->linker->path->getLink();
                $cpt = 1;
                $leftPosition = 0;
                $topPosition = 0;
                $maxWidth = 0;

                $hasSelected = false;
                foreach( $elements[ 'sections' ] as $sectionId => &$section ) {
                    if( strtolower( $menuType ) != 'array' ) {
                        $elements[ 'sections' ][ $sectionId ][ 'uid' ] = 'm_e_' . substr( md5( microtime() ), 0, 3 );
                        // We have to check if the page is the index page
                        $rewrittenLink = $this->linker->index->rewritePage( $section[ 'link' ] );
                        if( $section[ 'link' ] == $thisPage || $rewrittenLink == $thisPage ) {
                            $selected = true;
                        } else {
                            $selected = false;
                        }

                        $state = 'passive';
                        $imagesRoot = $this->getParamForMenu(
                            $menuId, 'entries>' . $sectionId . '>image', ''
                        );

                        $imagesRoot = str_replace( sh_images::LANG_DIR, $lang . '/', $imagesRoot );
                        if( !$selected ) {
                            $passiveState = 'passive';
                            $hoverState = 'hover';
                            $section[ 'class' ] = '';
                        } else {
                            $passiveState = 'selected';
                            $hoverState = $passiveState;
                            $section[ 'class' ] = 'menu_entry_selected';
                        }
                        $section[ 'image' ] = $imagesRoot . '_' . $passiveState . '.png';
                        $section[ 'imageHover' ] = $imagesRoot . '_' . $hoverState . '.png';
                        $section[ 'width' ] = $imageParams[ 'width' ];
                        $section[ 'height' ] = $imageParams[ 'height' ];
                        $section[ 'leftPosition' ] = $leftPosition;
                        $leftPosition += $imageParams[ 'width' ];
                        $section[ 'topPosition' ] = $topPosition;
                        $topPosition += $imageParams[ 'height' ];
                        $maxWidth = max( $imageParams[ 'width' ], $maxWidth );
                        $elements[ 'menu' ][ 'oneLineHeight' ] = $imageParams[ 'height' ];
                    }
                    $section[ 'title' ] = ' ' . $this->getI18n( $section[ 'title' ] ) . ' ';
                    $section[ 'id' ] = $cpt++;
                    $selected = false;
                    if( strtolower( substr( $section[ 'link' ], 0, 4 ) ) != 'http' ) {
                        $section[ 'href' ] = $this->linker->path->getUri(
                            $section[ 'link' ]
                        );
                        if( $elements[ 'menu' ][ 'hasSubmenus' ] ) {
                            list($class, $method, $id) = explode( '/', $section[ 'link' ] );
                            if( $class && $this->linker->method_exists( $class, 'getSubmenus' ) ) {
                                $subMenus = $this->linker->$class->getSubmenus( $method, $id );
                                if( $subMenus ) {
                                    if( is_array( $subMenus ) ) {
                                        foreach( $subMenus as &$subMenu ) {
                                            if( $subMenu[ 'link' ] == $thisLink ) {
                                                $selected = true;
                                                $subMenu[ 'class' ] = ' submenu_selected';
                                            }
                                        }
                                    }
                                    $section[ 'subMenus' ] = $subMenus;
                                }
                            }
                        }
                    } else {
                        $section[ 'href' ] = $section[ 'link' ];
                    }
                }
                if( strtolower( $menuType ) != 'array' ) {
                    $elements[ 'menu' ][ 'width' ] = $leftPosition;
                    $elements[ 'menu' ][ 'height' ] = $topPosition;
                    $elements[ 'menu' ][ 'oneLineWidth' ] = $maxWidth;
                    foreach( $elements[ 'sections' ] as &$section ) {
                        $section[ 'diffWidth' ] = $maxWidth - $section[ 'width' ];
                        $section[ 'halfDiffWidth' ] = round( $section[ 'diffWidth' ] / 2 );
                    }
                }
            }
        } else {
            return '';
        }
        $elements[ 'suffix' ][ 'images' ] = $this->uid;

        if( strtolower( $menuType ) == 'array' ) {
            return $elements;
        }
        $rf = $this->linker->template->get( 'menuButtons>' . $menuId . '>renderFile', 'withSubmenus' );
        $rendered = $this->render( $rf, $elements, false, false );

        $this->cachedPart_cache( $rendered, 'menu_' . $menuId . '_' . $page );
        return $rendered;
    }

    /**
     * This method checks if the page $mainMenuClass/$mainMenuMethod/$mainMenuId is used in any menu, and if
     * so, removes these menus from cache because they have to be re-generated
     * @param str $mainMenuClass The class that manages the page (its shortClassName actually)
     * @param str $mainMenuMethod The method that creates the page
     * @param str $mainMenuId The id of the page (if any) in the method $mainMenuMethod
     */
    public function submenuMayHaveChanged( $mainMenuClass, $mainMenuMethod, $mainMenuId = '' ) {
        $menus = array_keys( $this->linker->template->get( 'menuButtons' ) );
        foreach( $menus as $menu ) {
            $sections = $this->getParamForMenu( $menu, 'entries', array( ) );
            $page = $mainMenuClass . '/' . $mainMenuMethod . '/' . $mainMenuId;
            foreach( $sections as $section ) {
                if( $section[ 'link' ] == $page ) {
                    // We remove the menu from the cache
                    $this->cachedPart_remove( 'menu_' . $menu . '_.*/.*' );
                    return true;
                }
            }
        }
    }

    public function hasMenuBeenDeactivated() {
        $ret = isset( $_SESSION[ __CLASS__ ][ 'template_change_deactivated_menu' ] );
        unset( $_SESSION[ __CLASS__ ][ 'template_change_deactivated_menu' ] );
        return $ret;
    }

    /**
     * This method is automatically called by sh_template when the admin/master
     * changes the template he is using.<br />
     * It does everything that has to be done in this class when it occurs.
     * @param str $template The name of the template that will now be used.
     */
    public function template_change( $template ) {
        // We remove the generated images from the disk
        $this->reset();

        $this->uid = substr( md5( microtime() ), 0, 6 );
        $this->setParam( 'uid', $this->uid );
        $this->writeParams();

        //Removes the cache
        sh_cache::removeCache();

        // We check if we can rebuild the menus
        $menus = array_keys( $this->linker->template->get( 'menuButtons' ) );
        foreach( $menus as $menu ) {
            $activated = $this->getParamForMenu( $menu, 'activated', true );
            if( $activated ) {
                // checking if the menu may be rebuilt
                // We should get the texts from the database and the datas from the params file
                $textHeight = $this->getParamForMenu( $menu, 'textHeight', 16 );
                $font = $this->getParamForMenu( $menu, 'font' );
                $entries = $this->getParamForMenu( $menu, 'entries' );

                foreach( $entries as $entryId => $entry ) {
                    $categories[ $entryId ][ 'name' ] = $this->getI18n( $entry[ 'title' ], '*' );
                    $categories[ $entryId ][ 'link' ] = $entry[ 'link' ];
                }
                $_POST = array(
                    'real' => true,
                    'id' => $menu,
                    'font' => $font,
                    'textHeight' => $textHeight,
                    'sectionsCount' => count( $entries ),
                    'categories' => $categories,
                    'menuState' => true
                );

                if( !$this->verifyLength( true, $menu ) ) {
                    if( is_int( $menu ) ) {
                        $this->setParamForMenu( $menu, 'activated', false );
                        $this->writeParamsForMenu( $menu );
                        $_SESSION[ __CLASS__ ][ 'template_change_deactivated_menu' ] = true;
                    }
                } else {
                    $this->updateDB( $menu );
                }
            }
        }
        return true;
    }

    public function unpack_palettes() {
        $this->onlyMaster();
        if( $this->formSubmitted( 'addPalettes' ) ) {
            set_time_limit( 360 );
            // We should download the button pack in the temp folder
            if( !is_dir( SH_TEMP_FOLDER . __CLASS__ ) ) {
                mkdir( SH_TEMP_FOLDER . __CLASS__ );
            }
            $file = $this->linker->form_elements->getFile( 'zipFile', SH_TEMP_FOLDER . __CLASS__ );
            // We verify if the file is a zip
            if( substr( $file[ 'fileName' ], -4 ) == '.zip' ) {
                // we extract it in the same folder
                $folder = dirname( $file[ 'completeFileName' ] ) . '/' . substr( $file[ 'fileName' ], 0, -4 ) . '/';
                if( is_dir( $folder ) ) {
                    $this->helper->deleteDir( $folder );
                }
                $zipObject = $this->linker->zipper->extract(
                    $file[ 'completeFileName' ], $folder, array( 'png' )
                );
                $palettes = glob( $folder . '*\.png' );
                foreach( $palettes as $palette ) {
                    sh_colors::explodePalette(
                        $palette,
                        dirname( dirname( $palette ) ) . '/' . str_replace( '.png', '.php', basename( $palette ) )
                    );
                    $this->linker->html->addMessage( 'Le fichier de palette ' . dirname( dirname( $palette ) ) . '/' . str_replace( '.png',
                                                                                                                                    '.php',
                                                                                                                                    basename( $palette ) ) . ' a été généré' );
                }
            }
        }

        $this->render( __FUNCTION__, array( ) );
    }

    public function unpack_button_type() {
        $this->onlyMaster();
        $message = '';
        if( $this->formSubmitted( 'addButton' ) ) {
            set_time_limit( 360 );
            // We should download the button pack in the temp folder
            if( !is_dir( SH_TEMP_FOLDER . __CLASS__ ) ) {
                mkdir( SH_TEMP_FOLDER . __CLASS__ );
            }
            $file = $this->linker->form_elements->getFile( 'zipFile', SH_TEMP_FOLDER . __CLASS__ );
            // We verify if the file is a zip
            if( substr( $file[ 'fileName' ], -4 ) == '.zip' ) {
                // we extract it in the same folder
                $folder = dirname( $file[ 'completeFileName' ] ) . '/' . substr( $file[ 'fileName' ], 0, -4 ) . '/';
                if( is_dir( $folder ) ) {
                    $this->helper->deleteDir( $folder );
                }
                $zipObject = $this->linker->zipper->extract(
                    $file[ 'completeFileName' ], $folder, array( 'xml', 'png' )
                );
                // We check if we have at least the params file
                if( file_exists( $folder . 'params.xml' ) ) {
                    $params = array( 'creator_version' => 2 );

                    // We read the params
                    $paramsFile = new DOMDocument();
                    $paramsFile->load( realpath( $folder . 'params.xml' ) );
                    $xpath = new DOMXpath( $paramsFile );

                    $this->unpackFolder = $folder;

                    // We get the layers
                    $layers = array( );
                    $query = '//menubutton/layers/layer';
                    $entries = $xpath->query( $query );
                    foreach( $entries as $entry ) {
                        if( $entry->hasAttributes() ) {
                            $attributes = $entry->attributes;
                            $type = $attributes->getNamedItem( 'type' )->nodeValue;
                            $zindex = $attributes->getNamedItem( 'zindex' )->nodeValue;
                            $children = $entry->childNodes;
                            if( $type == 'text' ) {
                                $layers[ $zindex ][ 'type' ] = 'text';
                                foreach( $children as $child ) {
                                    $nodeName = $child->nodeName;
                                    if( $nodeName == 'position' ) {
                                        $attributes = $child->attributes;
                                        $layers[ $zindex ][ 'position' ] = $attributes->getNamedItem( 'src' )->nodeValue;
                                        $layers[ $zindex ][ 'align' ] = strtolower( $attributes->getNamedItem( 'align' )->nodeValue );
                                        if( !in_array( $layers[ $zindex ][ 'align' ], array( 'left', 'right' ) ) ) {
                                            $layers[ $zindex ][ 'align' ] = 'center';
                                        }
                                        $layers[ $zindex ][ 'valign' ] = strtolower( $attributes->getNamedItem( 'valign' )->nodeValue );
                                        if( !in_array( $layers[ $zindex ][ 'valign' ], array( 'top', 'bottom' ) ) ) {
                                            $layers[ $zindex ][ 'valign' ] = 'middle';
                                        }
                                    } elseif( $nodeName == 'palette' ) {
                                        $attributes = $child->attributes;
                                        $palette = $attributes->getNamedItem( 'class' )->nodeValue;
                                        $src = $attributes->getNamedItem( 'src' )->nodeValue;
                                        $states = explode( ',', $attributes->getNamedItem( 'state' )->nodeValue );
                                        if( empty( $states ) ) {
                                            // It means that it should be used for all states
                                            $states = array( 'passive', 'hover', 'selected' );
                                        }
                                        if( $palette != 'default' ) {
                                            foreach( $states as $state ) {
                                                $layers[ $zindex ][ 'palettes' ][ $state ] = $this->getPalette( $src );
                                            }
                                        }
                                    }
                                }
                                // We should get the borders sizes
                                $ret = $this->getTextBorders(
                                    $layers[ $zindex ][ 'position' ]
                                );
                                $layers[ $zindex ][ 'first' ] = $ret[ 'first' ];
                                $layers[ $zindex ][ 'middle' ] = $ret[ 'middle' ];
                                $layers[ $zindex ][ 'last' ] = $ret[ 'last' ];
                                $layers[ $zindex ][ 'margins' ] = $ret[ 'margins' ];
                            } else {
                                $layers[ $zindex ][ 'type' ] = 'decoration';
                                foreach( $children as $child ) {
                                    $nodeName = $child->nodeName;
                                    if( $nodeName == 'image' ) {
                                        $attributes = $child->attributes;
                                        $layers[ $zindex ][ 'image' ] = $attributes->getNamedItem( 'src' )->nodeValue;
                                    } elseif( $nodeName == 'stretch' ) {
                                        $attributes = $child->attributes;
                                        $layers[ $zindex ][ 'stretch' ] = $attributes->getNamedItem( 'src' )->nodeValue;
                                    }
                                    if( $nodeName == 'palette' ) {
                                        $attributes = $child->attributes;
                                        $palette = $attributes->getNamedItem( 'type' )->nodeValue;
                                        $src = $attributes->getNamedItem( 'src' )->nodeValue;
                                        $states = explode( ',', $attributes->getNamedItem( 'state' )->nodeValue );
                                        if( empty( $states ) ) {
                                            // It means that it should be used for all states
                                            $states = array( 'passive', 'hover', 'selected' );
                                        }
                                        if( $palette == 'file' ) {
                                            foreach( $states as $state ) {
                                                $layers[ $zindex ][ 'palette' ] = $this->getPalette( $src );
                                            }
                                        } elseif( $palette == 'none' ) {
                                            $layers[ $zindex ][ 'palette' ] = 'none';
                                        }
                                    }
                                }
                                // We should explode "image" using "stretch"
                                $destFolder = $this->unpackFolder . '/' . substr( basename( $layers[ $zindex ][ 'image' ] ),
                                                                                            0, -4 ) . '/';
                                $images = $this->splitImage( $layers[ $zindex ][ 'image' ], $destFolder, true );
                                $stretches = $this->splitImage( $layers[ $zindex ][ 'stretch' ], $destFolder, true );

                                foreach( $images as $positionName => $position ) {
                                    foreach( $position as $imageType => $imagePath ) {
                                        mkdir( $destFolder . 'exploded_' . $zindex . '_' . $positionName . '_' . $imageType . '/' );
                                        // We create the gitignore file for the variations
                                        $this->helper->writeInFile( $destFolder . 'exploded_' . $zindex . '_' . $positionName . '_' . $imageType . '/' . 'variations/.gitignore',
                                                                    '*/' . "\n" );

                                        $layers[ $zindex ][ $positionName ][ $imageType ] = $this->explodeImage(
                                            $imagePath, $stretches[ $positionName ][ $imageType ],
                                            $destFolder . 'exploded_' . $zindex . '_' . $positionName . '_' . $imageType . '/'
                                        );
                                    }
                                }
                            }
                        }
                    }
                    ksort( $layers );
                    $layers[ 'palettes' ] = $this->getPalettes();
                    $layers[ 'creator_version' ] = 2;
                    // We also have to define the minimal widths and heights (which are the bigger minimals of all layers)
                    foreach( $layers as $layerId => $oneLayer ) {
                        if( is_int( $layerId ) ) {
                            // We are reading a layer
                            $positions = array( 'first', 'middle', 'last' );
                            $states = array( 'passive', 'selected', 'hover' );
                            foreach( $positions as $position ) {
                                foreach( $states as $state ) {
                                    if( $oneLayer[ $position ][ $state ][ 'minimalWidth' ] > $layers[ 'minimals' ][ $position ][ 'width' ] ) {
                                        $layers[ 'minimals' ][ $position ][ 'width' ] = $oneLayer[ $position ][ $state ][ 'minimalWidth' ];
                                    }
                                    if( $oneLayer[ $position ][ $state ][ 'minimalHeight' ] > $layers[ 'minimals' ][ $position ][ 'height' ] ) {
                                        $layers[ 'minimals' ][ $position ][ 'height' ] = $oneLayer[ $position ][ $state ][ 'minimalHeight' ];
                                    }
                                }
                            }
                        }
                    }
                    $this->helper->writeArrayInFile(
                        $folder . 'params.php', 'params', $layers
                    );
                } else {
                    $message .= 'The file params.xml wasn\'t found in the archive.<br />';
                    $this->helper->deleteDir( $folder );
                }
            }
            $this->linker->html->addMessage( 'L\'action a été menée avec succès' );
        }

        $this->render( __FUNCTION__, array( ) );
    }

    protected function getPalettes() {
        return $this->palettes;
    }

    protected function getPalette( $source ) {
        if( isset( $this->palettes[ basename( $source ) ] ) ) {
            return basename( $source );
        }
        if( file_exists( $this->unpackFolder . $source ) ) {
            $source = $this->unpackFolder . $source;
        }

        $this->palettes[ basename( $source ) ] = sh_colors::getPalette( $source );
        return basename( $source );
    }

    protected function explodeImage( $image, $mask, $destFolder = '' ) {
        $width = imagesx( $mask );
        $height = imagesy( $mask );
        $open = false;
        $cpt = 0;
        $cut = false;
        $lastCut = 0;
        for( $x = 0; $x < $width; $x++ ) {
            $rgba = imagecolorat( $mask, $x, 0 );
            $alpha = ($rgba & 0x7F000000) >> 24;
            if( !$open && $alpha != 127 ) {
                $open = true;
                $cut = true;
            }
            if( $open && $alpha == 127 ) {
                $open = false;
                $cut = true;
                $xStretchSuffixes[ $cpt ] = $cpt;
            }
            if( $x == $width - 1 ) {
                $x++;
                $cut = true;
                if( $open ) {
                    $xStretchSuffixes[ $cpt ] = $cpt;
                }
            }
            if( $x > 0 && $cut ) {
                $columns[ $cpt ] = imagecreatetruecolor( $x - $lastCut, $height );
                imagealphablending( $columns[ $cpt ], false );
                imagesavealpha( $columns[ $cpt ], true );
                $trans_colour = imagecolorallocatealpha( $columns[ $cpt ], 50, 60, 70, 127 );
                imagefill( $columns[ $cpt ], 0, 0, $trans_colour );
                imagecopy( $columns[ $cpt ], $image, 0, 0, $lastCut, 0, $x - $lastCut, $height );
                imagepng( $columns[ $cpt ], $destFolder . 'col_' . $x . '.png' );

                $lastCut = $x;
                $cut = false;
                $cpt++;
            }
        }

        $open = false;
        $cpt = 0;
        $cut = false;
        $lastCut = 0;
        $minimalHeight = 0;
        for( $y = 0; $y < $height; $y++ ) {
            $rgba = imagecolorat( $mask, 0, $y );
            $alpha = ($rgba & 0x7F000000) >> 24;
            if( !$open && $alpha != 127 ) {
                $open = true;
                $cut = true;
                $yStretchSuffix = '';
                $addToMinimalHeight = true;
            }
            if( $open && $alpha == 127 ) {
                $open = false;
                $cut = true;
                $yStretchSuffix = '_y';
                $addToMinimalHeight = false;
            }
            if( $y == $height - 1 ) {
                $y++;
                $cut = true;
                if( !$open ) {
                    $yStretchSuffix = '';
                    $addToMinimalHeight = true;
                } else {
                    $yStretchSuffix = '_y';
                }
            }
            $minimalWidth = 0;
            if( $y > 0 && $cut ) {
                $cpt2 = 0;
                if( $addToMinimalHeight ) {
                    $minimalHeight += $y - $lastCut;
                }
                foreach( $columns as $columnId => $column ) {
                    $stretchSuffix = '';
                    $width = imagesx( $column );
                    if( isset( $xStretchSuffixes[ $columnId ] ) ) {
                        $stretchSuffix = '_x';
                    } else {
                        $minimalWidth += $width;
                    }
                    $stretchSuffix .= $yStretchSuffix;
                    $images[ $cpt . '_' . $cpt2 . $stretchSuffix ] = imagecreatetruecolor( $width, $y - $lastCut );
                    imagealphablending( $images[ $cpt . '_' . $cpt2 . $stretchSuffix ], false );
                    imagesavealpha( $images[ $cpt . '_' . $cpt2 . $stretchSuffix ], true );
                    $trans_colour = imagecolorallocatealpha( $images[ $cpt . '_' . $cpt2 . $stretchSuffix ], 0, 0, 0,
                                                             127 );
                    imagefill( $images[ $cpt . '_' . $cpt2 . $stretchSuffix ], 0, 0, $trans_colour );

                    imagecopy( $images[ $cpt . '_' . $cpt2 . $stretchSuffix ], $column, 0, 0, 0, $lastCut, $width,
                               $y - $lastCut );
                    if( !empty( $destFolder ) && is_dir( $destFolder ) ) {
                        $name = $destFolder . 'splitted_' . $cpt . '_' . $cpt2 . $stretchSuffix . '.png';
                        imagepng( $images[ $cpt . '_' . $cpt2 . $stretchSuffix ], $name );
                        imagedestroy( $images[ $cpt . '_' . $cpt2 . $stretchSuffix ] );
                        $images[ $cpt . '_' . $cpt2 . $stretchSuffix ] = str_replace( $this->unpackFolder, '', $name );
                    }
                    $cpt2++;
                }
                $lastCut = $y;
                $cut = false;
                $cpt++;
            }
        }
        $images[ 'minimalWidth' ] = $minimalWidth;
        $images[ 'minimalHeight' ] = $minimalHeight;
        return $images;
    }

    protected function getTextBorders( $image ) {
        if( file_exists( $this->unpackFolder . $image ) ) {
            $image = $this->unpackFolder . $image;
        }
        $model = imagecreatefrompng( $image );
        $width = imagesx( $model );
        $height = imagesy( $model );
        $oneWidth = $width / 3;
        $oneHeight = $height / 3;

        // We have to find the two first points of the first line
        for( $x == 0; $x < $width; $x++ ) {
            $rgba = imagecolorat( $model, $x, 0 );
            $alpha = ($rgba & 0x7F000000) >> 24;
            if( $alpha != 127 ) {
                $value = $x % $oneWidth;
                if( $x < $oneWidth ) {
                    $position = 'firsts';
                } elseif( $x < 2 * $oneWidth ) {
                    $position = 'middles';
                } else {
                    $position = 'lasts';
                }
                if( !isset( $margins[ $position ][ 'left' ] ) ) {
                    $margins[ $position ][ 'left' ] = $value;
                } else {
                    $margins[ $position ][ 'right' ] = $oneWidth - $value;
                }
            }
        }
        for( $y == 0; $y < $height; $y++ ) {
            $rgba = imagecolorat( $model, 0, $y );
            $alpha = ($rgba & 0x7F000000) >> 24;
            if( $alpha != 127 ) {
                $value = $y % $oneHeight;
                if( $y < $oneHeight ) {
                    // First line
                    $position = 'passive';
                } elseif( $y < 2 * $oneHeight ) {
                    // Second line
                    $position = 'selected';
                } else {
                    // Third line
                    $position = 'hover';
                }
                if( !isset( $margins[ $position ][ 'top' ] ) ) {
                    $margins[ $position ][ 'top' ] = $value;
                } else {
                    $margins[ $position ][ 'bottom' ] = $oneHeight - $value;
                }
            }
        }
        imagedestroy( $model );
        foreach( $margins as $positionName => $position ) {
            if( count( $position ) != 2 ) {
                echo 'Error : One or more text margin(s) are missing, or there are too much of them.';
                return false;
            }
            $margins[ $positionName ][ 'min' ] = array_sum( $position );
        }
        // We then organize them to be the same (more or less) that the result of explodeImage
        $ret[ 'first' ][ 'passive' ][ 'minimalWidth' ] = $margins[ 'firsts' ][ 'min' ];
        $ret[ 'first' ][ 'hover' ][ 'minimalWidth' ] = $margins[ 'firsts' ][ 'min' ];
        $ret[ 'first' ][ 'selected' ][ 'minimalWidth' ] = $margins[ 'firsts' ][ 'min' ];
        $ret[ 'middle' ][ 'passive' ][ 'minimalWidth' ] = $margins[ 'middles' ][ 'min' ];
        $ret[ 'middle' ][ 'hover' ][ 'minimalWidth' ] = $margins[ 'middles' ][ 'min' ];
        $ret[ 'middle' ][ 'selected' ][ 'minimalWidth' ] = $margins[ 'middles' ][ 'min' ];
        $ret[ 'last' ][ 'passive' ][ 'minimalWidth' ] = $margins[ 'lasts' ][ 'min' ];
        $ret[ 'last' ][ 'hover' ][ 'minimalWidth' ] = $margins[ 'lasts' ][ 'min' ];
        $ret[ 'last' ][ 'selected' ][ 'minimalWidth' ] = $margins[ 'lasts' ][ 'min' ];

        $ret[ 'first' ][ 'passive' ][ 'minimalHeight' ] = $margins[ 'passive' ][ 'min' ];
        $ret[ 'first' ][ 'hover' ][ 'minimalHeight' ] = $margins[ 'hover' ][ 'min' ];
        $ret[ 'first' ][ 'selected' ][ 'minimalHeight' ] = $margins[ 'selected' ][ 'min' ];
        $ret[ 'middle' ][ 'passive' ][ 'minimalHeight' ] = $margins[ 'passive' ][ 'min' ];
        $ret[ 'middle' ][ 'hover' ][ 'minimalHeight' ] = $margins[ 'hover' ][ 'min' ];
        $ret[ 'middle' ][ 'selected' ][ 'minimalHeight' ] = $margins[ 'selected' ][ 'min' ];
        $ret[ 'last' ][ 'passive' ][ 'minimalHeight' ] = $margins[ 'passive' ][ 'min' ];
        $ret[ 'last' ][ 'hover' ][ 'minimalHeight' ] = $margins[ 'hover' ][ 'min' ];
        $ret[ 'last' ][ 'selected' ][ 'minimalHeight' ] = $margins[ 'selected' ][ 'min' ];


        $ret[ 'first' ][ 'minimalWidth' ] = $margins[ 'firsts' ][ 'min' ];
        $ret[ 'middle' ][ 'minimalWidth' ] = $margins[ 'middles' ][ 'min' ];
        $ret[ 'last' ][ 'minimalWidth' ] = $margins[ 'lasts' ][ 'min' ];
        $ret[ 'first' ][ 'minimalHeight' ] = max( $margins[ 'passive' ][ 'min' ], $margins[ 'hover' ][ 'min' ],
                                                  $margins[ 'selected' ][ 'min' ] );
        $ret[ 'middle' ][ 'minimalHeight' ] = $ret[ 'first' ][ 'minimalHeight' ];
        $ret[ 'last' ][ 'minimalHeight' ] = $ret[ 'first' ][ 'minimalHeight' ];

        $ret[ 'margins' ] = $margins;
        return $ret;
    }

    protected function splitImage( $image, $destFolder = '', $returnGdImages = false ) {
        if( file_exists( $this->unpackFolder . $image ) ) {
            $image = $this->unpackFolder . $image;
        }
        if( empty( $destFolder ) ) {
            $destFolder = dirname( $image ) . '/' . substr( basename( $image ), 0, -4 ) . '/';
        }
        if( !is_dir( $destFolder ) ) {
            mkdir( $destFolder );
        }
        $model = imagecreatefrompng( $image );
        $width = imagesx( $model );
        $height = imagesy( $model );
        $oneWidth = $width / 3;
        $oneHeight = $height / 3;
        $positions = array( 'first', 'middle', 'last' );
        $states = array( 'passive', 'selected', 'hover' );
        for( $x = 0; $x < 3; $x++ ) {
            for( $y = 0; $y < 3; $y++ ) {
                // We are in every image
                $newImage = imagecreatetruecolor( $oneWidth, $oneHeight );
                imagealphablending( $newImage, false );
                imagesavealpha( $newImage, true );
                $trans_colour = imagecolorallocatealpha( $newImage, 0, 0, 0, 127 );
                imagefill( $newImage, 0, 0, $trans_colour );

                imagecopy( $newImage, $model, 0, 0, $x * $oneWidth, $y * $oneHeight, $oneWidth, $oneHeight );
                if( !$returnGdImages ) {
                    $name = $destFolder . substr( md5( microtime() ), 0, 8 ) . '.png';
                    imagepng( $newImage, $name );
                    imagedestroy( $newImage );
                    $ret[ $positions[ $x ] ][ $states[ $y ] ] = $name;
                } else {
                    $ret[ $positions[ $x ] ][ $states[ $y ] ] = $newImage;
                }
            }
        }
        return $ret;
    }

    protected function buttons_explode( $image, $lines, $columns ) {
        $lineHeight = $this->destHeight;
        if( !is_dir( $this->destPartFolder ) ) {
            mkdir( $this->destPartFolder );
            foreach( $lines as $lineDesc ) {
                mkdir( $this->destPartFolder . $lineDesc );
            }
        }
        $model = imagecreatefrompng( $image );
        $width = imagesx( $model );
        $lastLine = count( $lines ) - 1;

        foreach( $lines as $lineNum => $lineDesc ) {
            $destination = imageCreateTrueColor( $width, $lineHeight );
            imagealphablending( $destination, false );
            imagesavealpha( $destination, true );
            $transparentColor = imagecolorallocatealpha( $destination, 0, 0, 0, 127 );
            imagefill( $destination, 0, 0, $transparentColor );
            imagecopy( $destination, $model, 0, 0, 0, $lineNum * $lineHeight, $width, $lineHeight );

            if( $lineNum != $lastLine ) {
                $this->isMask = false;
            } else {
                $this->isMask = true;
            }
            imagepng( $destination, $this->destPartFolder . $lineDesc . '.png' );
            $previousFolder = $this->destPartFolder;
            // we explode the cols
            $this->destPartFolder .= $lineDesc . '/';
            $this->buttons_explode_cols( $destination, $columns );
            $this->destPartFolder = $previousFolder;
        }
    }

    public function variation_change( $template, $variation, $value ) {
        $this->rebuildMenuImages();
        return true;
    }

    protected function buttons_explode_cols( $image, $cols ) {
        $folder = $this->destPartFolder;
        $colWidths = $this->destWidth;
        $height = $this->destHeight;

        foreach( $cols as $colNum => $colDesc ) {
            $destination = imageCreateTrueColor( $colWidths, $height );
            imagealphablending( $destination, false );
            imagesavealpha( $destination, true );
            $transparentColor = imagecolorallocatealpha( $destination, 0, 0, 0, 127 );
            imagefill( $destination, 0, 0, $transparentColor );
            imagecopy( $destination, $image, 0, 0, $colWidths * $colNum, 0, $colWidths, $height );
            if( !$this->isMask ) {
                mkdir( $folder . 'palette_' . $colNum . '/' );
                imagepng( $destination, $folder . 'palette_' . $colNum . '.png' );
            } else {
                if( $colNum == 0 ) {
                    imagepng( $destination, $folder . 'textMask.png' );
                    $this->buttons_readTextMask( $folder . 'textMask.png' );
                } elseif( $colNum == 1 ) {
                    imagepng( $destination, $folder . 'stretchMask.png' );
                    $this->buttons_readStretchMask( $folder . 'stretchMask.png' );
                }
            }
        }
        return $ret;
    }

    protected function buttons_readStretchMask( $mask ) {
        $this->debug( 'function : ' . __FUNCTION__, 2, __LINE__ );

        $original = imagecreatefrompng( $mask );

        // Sets the text mask file name and include if it already exists
        $textFile = str_replace( '.png', '.php', $mask );
        if( file_exists( $textFile ) ) {
            include($textFile);
            return $image;
        }

        // Defines the size of the image
        $ret[ 'width' ] = imagesx( $original );
        $ret[ 'height' ] = imagesy( $original );

        $cpt = 0;
        $open = false;
        // Gets the pixels that are colored in the first line
        for( $a = 0; $a < $ret[ 'width' ]; $a++ ) {
            $rgb = imagecolorat( $original, $a, 0 );
            $alpha = ($rgb & 0x7F000000) >> 24;
            if( $alpha < 110 ) {
                if( $open == false ) {
                    $ret[ 'stretchLeft' ][ $cpt ][ 'start' ] = $a;
                    $open = true;
                }
            } elseif( $open ) {
                $ret[ 'stretchLeft' ][ $cpt ][ 'stop' ] = $a;
                $open = false;
                $cpt++;
            }
        }
        $cpt = 0;
        $open = false;
        // Gets the pixels that are colored in the first column
        for( $a = 0; $a < $ret[ 'height' ]; $a++ ) {
            $rgb = imagecolorat( $original, 0, $a );
            $alpha = ($rgb & 0x7F000000) >> 24;
            if( $alpha < 110 ) {
                if( $open == false ) {
                    $ret[ 'stretchTop' ][ $cpt ][ 'start' ] = $a;
                    $open = true;
                }
            } elseif( $open ) {
                $ret[ 'stretchTop' ][ $cpt ][ 'stop' ] = $a;
                $open = false;
                $cpt++;
            }
        }
        // Writes the params file for the image
        $this->helper->writeArrayInFile( $textFile, 'image', $ret );

        return true;
    }

    protected function buttons_readTextMask( $mask ) {
        $this->debug( 'function : ' . __FUNCTION__, 2, __LINE__ );

        $original = imagecreatefrompng( $mask );

        // Sets the text mask file name and include if it already exists
        $textFile = str_replace( '.png', '.php', $mask );
        if( file_exists( $textFile ) ) {
            include($textFile);
            return $image;
        }

        // Defines the size of the image
        $ret[ 'width' ] = imagesx( $original );
        $ret[ 'height' ] = imagesy( $original );

        $cpt = 0;
        $open = false;

        $open = false;
        // Gets the pixels that are colored in the last line
        for( $a = 0; $a < $ret[ 'width' ]; $a++ ) {
            $rgb = imagecolorat( $original, $a, 0 );
            $alpha = ($rgb & 0x7F000000) >> 24;
            if( $alpha < 110 ) {
                if( $open == false ) {
                    $ret[ 'startLeft' ] = $a;
                    $open = true;
                } else {
                    $ret[ 'stopLeft' ] = $a + 1;
                    $open = false;
                }
            }
        }
        $open = false;
        // Gets the pixels that are colored in the last column
        for( $a = 0; $a < $ret[ 'height' ]; $a++ ) {
            $rgb = imagecolorat( $original, 0, $a );
            $alpha = ($rgb & 0x7F000000) >> 24;
            if( $alpha < 110 ) {
                if( $open == false ) {
                    $ret[ 'startTop' ] = $a;
                    $open = true;
                } else {
                    $ret[ 'stopTop' ] = $a + 1;
                    $open = false;
                }
            }
        }

        $ret[ 'fixWidth' ] = $ret[ 'width' ] - $ret[ 'stopLeft' ] + $ret[ 'startLeft' ];
        $ret[ 'fixHeight' ] = $ret[ 'height' ] - $ret[ 'stopTop' ] + $ret[ 'startTop' ];

        // Writes the params file for the image
        $this->helper->writeArrayInFile( $textFile, 'image', $ret );


        //building temporary images
        $image = Array
            (
            'path' => SH_TEMP_FOLDER . 'temp1.png',
            'text' => 'This is just a test',
            'font' => SH_FONTS_FOLDER . '/FreeFontBold.ttf',
            'fontsize' => 10,
            'position' => 'normal',
            'type' => 'test_2',
            'state' => 'selected',
            'startX' => $ret[ 'startLeft' ],
            'startY' => $ret[ 'startTop' ]
        );


        echo '<div>$ret = ' . nl2br( str_replace( ' ', '&#160;', htmlspecialchars( print_r( $ret, true ) ) ) ) . '</div>';
        $size = $this->linker->imagesBuilder->getDimensions( $image[ 'text' ], $image[ 'fontsize' ], $image[ 'font' ] );

        echo '<div>$size = ' . nl2br( str_replace( ' ', '&#160;', htmlspecialchars( print_r( $size, true ) ) ) ) . '</div>';

        $image[ 'width' ] = $ret[ 'fixWidth' ] + $size[ 'width' ];
        $image[ 'height' ] = $ret[ 'fixHeight' ] + $size[ 'height' ];
        echo '<div>$image = ' . nl2br( str_replace( ' ', '&#160;', htmlspecialchars( print_r( $image, true ) ) ) ) . '</div>';


        $this->linker->imagesBuilder->createButton(
            $image[ 'type' ], $image[ 'position' ], $image[ 'state' ], $image[ 'path' ], $image[ 'width' ],
            $image[ 'height' ], $image[ 'text' ], $image[ 'font' ], $image[ 'fontsize' ], $image[ 'startX' ],
            $image[ 'startY' ]
        );

        return true;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri( $page ) {
        list($class, $method, $id) = explode( '/', $page );
        if( $method == 'edit' ) {
            return '/menu/edit/' . $id . '.php';
        }
        if( $method == 'verifyLength' ) {
            // To change this, we also have to do it in menuEditor.js
            return '/menu/verifyLength.php';
        }
        if( $method == 'addEntry' ) {
            // To change this, we also have to do it in menuEditor.js
            return '/menu/addEntry.php';
        }
        if( $method == 'chooseLink' ) {
            // To change this, we also have to do it in menuEditor.js
            return '/menu/chooseLink.php';
        }
        if( $method == 'unpack_button_type' ) {
            // To change this, we also have to do it in menuEditor.js
            return '/menu/unpack_button_type.php';
        }

        return parent::translatePageToUri( $page );
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage( $uri ) {
        if( preg_match( '`/menu/edit/([0-9]+)\.php`', $uri, $matches ) ) {
            return 'menu/edit/' . $matches[ 1 ];
        }
        if( $uri == '/menu/verifyLength.php' ) {
            // To change this, we also have to do it in menuEditor.js
            return 'menu/verifyLength/';
        }
        if( $uri == '/menu/addEntry.php' ) {
            // To change this, we also have to do it in menuEditor.js
            return 'menu/addEntry/';
        }
        if( $uri == '/menu/chooseLink.php' ) {
            // To change this, we also have to do it in menuEditor.js
            return 'menu/chooseLink/';
        }
        if( $uri == '/menu/unpack_button_type.php' ) {
            // To change this, we also have to do it in menuEditor.js
            return 'menu/unpack_button_type/';
        }

        return parent::translateUriToPage( $uri );
    }

    public function __tostring() {
        return get_class();
    }

}
