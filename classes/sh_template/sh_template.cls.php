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
 * Class that manages the params shared for all classes of a single website.
 */
class sh_template extends sh_core {

    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    protected $paramsFile = '';
    const ALL_VALUES = 'get_all_values';
    protected $renderingColors = array( );
    protected $sitesParamsFiles = array( );
    protected $template_i18n_file = '';
    protected $templateVersion = 1.0;
    public $callWithoutId = array(
        'createThumbnails', 'createThumbnailsFor', 'createThumbnails_progress', 'createThumbnails_start', 'manageColors'
    );
    public $minimal = array( 'createThumbnailsFor' => true, 'createThumbnails_progress' => true );

    /**
     * public function construct
     *
     */
    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            // The class datas are not in the same version as this file, or don't exist (installation)
            $this->helper->addClassesSharedMethods( 'sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__ );
            $this->helper->addClassesSharedMethods( 'sh_variation', 'change', __CLASS__ );
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
        $this->paramsFile = $this->linker->site->templateFolder . 'template.params.php';

        $this->path = $this->linker->site->templateFolder;
        $this->linker->params->addElement( $this->paramsFile );

        $this->template_i18n_file = $this->path . '/i18n/';

        $this->template = basename( $this->path );

        $this->templateVersion = $this->get( 'version', 1.0 );

        if( $_SESSION['this_is_a_temp_session'] ) {
            // The requested image is taken from the temp directory
            if( $_SESSION[__CLASS__]['active_temp_datas'] != $_SESSION['temp_session_form_datas'] ) {
                $_SESSION[__CLASS__]['active_temp_datas'] = $_SESSION['temp_session_form_datas'];
            }
        }

        return true;
    }

    protected function getI18n_template( $name ) {
        return $this->linker->i18n->get( $this->template_i18n_file, $name );
    }
    
    public function prepareTemporaryTemplate(){
        $this->cache_template_datas(true);
    }

    public function cache_template_datas($temporary = false) {
        if( version_compare( $this->templateVersion, 1.1, '<' ) ) {
            return false;
        }
        
        $dest = SH_SITE_FOLDER . __CLASS__ . '/images/';
        
        if($temporary){
            $dest .= 'temp/';
        }
        
        $this->linker->css->cache_css( );

        // We prepare the images in the right colors
        $images = $this->get( 'customizations>images' );

        foreach( $images as $imageName => $type ) {
            $srcImageName = $destImageName = $imageName;
            $color = $this->getParam(
                'customizations>' . $this->template . '>images>' . $type,
                $this->get( 'default_customizations>images>' . $type )
            );
            if($temporary){
                $color = $_SESSION['temp_session_form_datas']['images'][$type];
            }
            
            $prepare = true;
            
            if(!file_exists( $this->path . 'images/' . $imageName )){
                // This may mean that this image is part of a selection
                if($this->getParam('customizations>' . $this->template . '>images>' . $type,false)){
                    // The image is the one that has been selected
                    $srcImageName = $this->getParam('customizations>' . $this->template . '>selection>' . $type,'none');                    
                }
                if($temporary){
                    $srcImageName = $_SESSION['temp_session_form_datas']['selection'][$type];
                }
                if($srcImageName == 'none'){
                    $prepare = false;
                }
            }
            if($prepare){
                sh_colors::image_apply_color_filter(
                    $dest . $destImageName, $this->path . 'images/' . $srcImageName, $color
                );
            }elseif($srcImageName){
                copy(SH_SHAREDIMAGES_FOLDER.'icons/transparent.png', $dest.$destImageName);
            }
        }
    }

    public function manageColors() {
        $this->onlyAdmin(true);
        if( version_compare( $this->templateVersion, 1.1, '<' ) ) {
            $this->linker->path->error( 404 );
        }

        if( $this->formSubmitted( 'manageColors' ) ) {
            $this->setParam( 'customizations>' . $this->template, $_POST );
            $this->writeParams();

            $this->cache_template_datas();
        }

        $customizations = $this->get( 'customizations' );
        if( is_array( $customizations['images_groups'] ) ) {
            foreach( $customizations['images_groups'] as $imageKey=>$imageId) {
                if( !is_array( $imageId ) ) {
                    $color = $this->getParam(
                        'customizations>' . $this->template . '>images>' . $imageId,
                        $this->get( 'default_customizations>images>' . $imageId )
                    );
                    $values['images'][$imageId] = array(
                        'id' => $imageId,
                        'name' => $this->getI18n_template( $imageId ),
                        'color' => $color
                    );
                } else {
                    $color = $this->getParam(
                        'customizations>' . $this->template . '>images>' . $imageKey,
                        $this->get( 'default_customizations>images>' . $imageKey )
                    );
                    $values['images'][$imageKey] = array(
                        'id' => $imageKey,
                        'name' => $this->getI18n_template( $imageKey ),
                        'color' => $color,
                        'select' => true
                    );
                    $selection = $this->getParam(
                        'customizations>' . $this->template . '>selection>' . $imageKey,
                        $this->get( 'default_customizations>images>' . $imageKey )
                    );

                    // The image has to be chosen into the list $image
                    foreach( $imageId as $oneImageId=>$oneImageFile ) {
                        $state = '';
                        if($selection == $oneImageFile){
                            $state='selected';
                        }
                        $values['images'][$imageKey]['list'][$oneImageId] = array(
                            'image' => $oneImageFile,
                            'name' => $this->getI18n_template( $oneImageId ),
                            'state' => $state
                        );
                    }
                }
            }
        }
        if( is_array( $customizations['menus'] ) ) {
            foreach( $customizations['menus'] as $menuId => $menu ) {
                if( $menu != 'spacer' ) {
                    $color = $this->getParam(
                        'customizations>' . $this->template . '>menus>' . $menuId,
                        $this->get( 'default_customizations>menus>' . $menuId )
                    );
                    $values['menus'][$menuId] = array(
                        'id' => $menuId,
                        'name' => $this->getI18n_template( $menuId ),
                        'color' => $color
                    );
                } else {
                    $values['menus'][$menuId] = array(
                        'isSpacer' => true
                    );
                }
            }
        }
        if( is_array( $customizations['texts'] ) ) {
            foreach( $customizations['texts'] as $textId => $text ) {
                if( $text != 'spacer' ) {
                    $color = $this->getParam(
                        'customizations>' . $this->template . '>texts>' . $textId,
                        $this->get( 'default_customizations>texts>' . $textId )
                    );
                    $values['texts'][$textId] = array(
                        'id' => $textId,
                        'name' => $this->getI18n_template( $textId ),
                        'color' => $color
                    );
                } else {
                    $values['texts'][$textId] = array(
                        'isSpacer' => true
                    );
                }
            }
        }
        if( empty( $_SESSION['temp_session'] ) ) {
            $_SESSION['temp_session'] = substr( __CLASS__ . md5( microtime() ), 0, 8 );
        }
        $values['session']['temp'] = $_SESSION['temp_session'];
        $this->render( 'manageColors', $values );
        return true;
    }

    public function master_getMenuContent() {
        $masterMenu['Section Master'][] = array(
            'link' => 'template/addPalette/', 'text' => 'Importer une palette', 'icon' => 'picto_tool.png',
            'link' => 'template/createThumbnails/', 'text' => 'Créer les miniatures du template', 'icon' => 'picto_tool.png',
        );

        return $masterMenu;
    }

    public function admin_getMenuContent() {
        $adminMenu['Contenu']['top'] = array(
            'link' => 'template/select/', 'text' => 'Changer de modèle', 'icon' => 'picto_tool.png'
        );
        if( version_compare( $this->templateVersion, 1.1, '>=' ) ) {
            $adminMenu['Contenu']['top2'] = array(
                'link' => 'template/manageColors/', 'text' => 'Changer les couleurs', 'icon' => 'picto_tool.png'
            );
        }

        return $adminMenu;
    }

    public function getTemplateDescription( $templateName ) {
        if( file_exists( SH_TEMPLATE_FOLDER . $templateName . '/template.description.php' ) ) {
            list($id, $name) = explode( '-', $templateName );
            include(SH_TEMPLATE_FOLDER . $templateName . '/template.description.php');
            $lang = $this->linker->i18n->getLang();
            if( isset( $template['description'][$lang] ) ) {
                $template['description'] = $template['description'][$lang];
            } else {
                $template['description'] = array_shift( $template['description'] );
            }
            $template['name'] = $name;
            $template['longName'] = $templateName;
            $template['page'] = $this->shortClassName . '/show/' . $id;
            $template['link'] = $this->translatePageToUri( $this->shortClassName . '/show/' ) . '?template=' . $id;

            $imagesRoot = $this->getImagesRoot( $templateName );

            $template['variations'] = $imagesRoot . $template['variations'];
            $template['thumbnail'] = $imagesRoot . $template['thumbnail'];
            $template['background'] = $imagesRoot . $template['background'];
            if( is_array( $template['slides'] ) ) {
                foreach( $template['slides'] as &$slide ) {
                    $slide['src'] = $imagesRoot . $slide['src'];
                }
            }
            return $template;
        }
        return false;
    }

    public function getImagesRoot( $templateName ) {
        return '/images/templates/' . $templateName . '/';
    }

    protected function getSiteParamsFile( $site ) {
        if( !isset( $this->sitesParamsFiles[$site] ) || true ) {
            if( $this->isMaster() ) {
                if( empty( $site ) || !is_dir( SH_SITES_FOLDER . $site ) ) {
                    $this->sitesParamsFiles[$site] = false;
                } else {
                    $file = SH_SITES_FOLDER . $site . '/sh_params/' . __CLASS__ . '.params.php';

                    $this->linker->params->addElement( $file, true );

                    $this->sitesParamsFiles[$site] = $file;
                }
            } else {
                $this->sitesParamsFiles[$site] = false;
            }
        }
        return $this->sitesParamsFiles[$site];
    }

    public function restricted_setAll( $templates, $site = '' ) {
        $this->restricted_unAuthorizeAll( $site );
        if( is_array( $templates ) ) {
            foreach( $templates as $template ) {
                $this->restricted_authorize( $template, $site );
            }
        }
    }

    public function restricted_getAll( $site = '' ) {
        $elements = scandir( SH_TEMPLATE_FOLDER );
        $templates = array( );
        foreach( $elements as $element ) {
            if( preg_match( '`(cm_[1-9][0-9]*)-.+`', $element, $matches ) ) {
                if( file_exists( SH_TEMPLATE_FOLDER . $element . '/restricted.php' ) ) {
                    // This is a template folder
                    $template = $this->getTemplateDescription( $element );
                    if( $template ) {
                        $templates[$matches[1]] = $template;
                        // We will also check if this site is allowed to use this template
                        if( $this->restricted_isAuthorized( $element, $site ) ) {
                            $templates[$matches[1]]['state'] = 'checked';
                        }
                    }
                }
            }
        }
        return $templates;
    }

    public function restricted_getAuthorized( $site = '' ) {
        if( empty( $site ) ) {
            return $this->getParam( 'authorized', array( ) );
        } else {
            $paramsFile = $this->getSiteParamsFile( $site );
            if( $paramsFile ) {
                return $this->linker->params->get( $paramsFile, 'authorized', array( ) );
            }
        }
        return array( );
    }

    public function restricted_unAuthorizeAll( $site = '' ) {
        if( empty( $site ) ) {
            $this->setParam( 'authorized', array( ) );
            $this->writeParams();
        } else {
            $paramsFile = $this->getSiteParamsFile( $site );
            if( $paramsFile ) {
                $this->linker->params->set( $paramsFile, 'authorized', array( ) );
                $this->linker->params->write( $paramsFile );
            }
        }
    }

    public function restricted_unAuthorize( $template, $site = '' ) {
        if( empty( $site ) ) {
            $this->setParam( 'authorized>' . $template, false );
            $this->writeParams();
        } else {
            $paramsFile = $this->getSiteParamsFile( $site );
            if( $paramsFile ) {
                $this->linker->params->set( $paramsFile, 'authorized>' . $template, false );
                $this->linker->params->write( $paramsFile );
            }
        }
    }

    public function restricted_authorize( $template, $site = '' ) {
        if( empty( $site ) ) {
            $this->setParam( 'authorized>' . $template, true );
            $this->writeParams();
        } else {
            $paramsFile = $this->getSiteParamsFile( $site );
            if( $paramsFile ) {
                $this->linker->params->set( $paramsFile, 'authorized>' . $template, true );
                $this->linker->params->write( $paramsFile );
            }
        }
    }

    public static function restricted_isAuthorized( $template, $site = '' ) {
        if( !file_exists( SH_TEMPLATE_FOLDER . $template . '/restricted.php' ) ) {
            return true;
        }
        $allowed = array( );
        include(SH_TEMPLATE_FOLDER . $template . '/restricted.php');
        if( empty( $site ) ) {
            $site = SH_SITENAME;
        }
        return in_array( $site, $allowed );
    }

    public function createThumbnails() {
        $this->onlyMaster();
        $scan = scandir( SH_TEMPLATE_FOLDER );
        $areNotTemplates = array( 'builder', 'fonts', 'global', 'preview', 'variations' );
        if( is_array( $scan ) ) {
            foreach( $scan as $element ) {
                if( !in_array( $element, $areNotTemplates ) ) {
                    $folder = SH_TEMPLATE_FOLDER . $element . '/';

                    if( substr( $element, 0, 1 ) != '.' && is_dir( $folder ) ) {
                        if( preg_match( '`((sh_|cm_)([1-9][0-9]*))-(.+)`', $element, $matches ) ) {
                            list($all, $id, $type, $num, $name) = $matches;

                            $longId = str_pad( $num, 6, "0", STR_PAD_LEFT );
                            $values['templates'][$longId] = array(
                                'name' => $id . ' - ' . $name,
                                'id' => $element
                            );
                        }
                    }
                }
            }
        }
        $this->linker->html->addScript( $this->getSinglePath() . 'createThumbnails.js' );
        ksort( $values['templates'] );
        $this->render( 'createThumbnails', $values );
    }

    public function createThumbnailsFor() {
        $_SESSION[__CLASS__]['template'] = $_POST['template'];
        $miniaturesFolder = SH_TEMPLATE_FOLDER . $_POST['template'] . '/images/miniatures/';
        $defaultFolder = $miniaturesFolder . 'default/';
        if( !is_dir( $defaultFolder ) ) {
            echo 'The folder ' . SH_TEMPLATE_PATH . $_POST['template'] . '/images/miniatures/default/ does not exist!!';
            return true;
        }
        if( file_exists( $miniaturesFolder . '370_reallyShiny.png' ) ) {
            echo 'ALREADY_DONE';
            return true;
        }
        $_SESSION[__CLASS__]['cpt'] = 0;
        // Listing the images to creation the variations for
        if( file_exists( $defaultFolder . 'params.xml' ) ) {
            // We read the params
            $paramsFile = new DOMDocument();
            $paramsFile->load( realpath( $defaultFolder . 'params.xml' ) );
            $xpath = new DOMXpath( $paramsFile );

            // We get the layers
            $layers = array( );
            $query = '//miniatures/layers/layer';
            $entries = $xpath->query( $query );
            foreach( $entries as $entry ) {
                if( $entry->hasAttributes() ) {
                    $attributes = $entry->attributes;
                    $zindex = $attributes->getNamedItem( 'zindex' )->nodeValue;
                    $children = $entry->childNodes;
                    foreach( $children as $child ) {
                        $nodeName = $child->nodeName;
                        if( $nodeName == 'image' ) {
                            $attributes = $child->attributes;
                            $src = $attributes->getNamedItem( 'src' )->nodeValue;
                            $layers[$zindex]['imageSrc'] = $defaultFolder . $src;
                            $layers[$zindex]['name'] = basename( $src );
                        } elseif( $nodeName == 'palette' ) {
                            $attributes = $child->attributes;
                            $layers[$zindex]['type'] = $attributes->getNamedItem( 'type' )->nodeValue;
                            if( $layers[$zindex]['type'] == 'file' ) {
                                $src = $attributes->getNamedItem( 'src' )->nodeValue;
                                flush();
                                $layers[$zindex]['palette'] = sh_colors::getPalette( $defaultFolder . $src );
                            }
                        }
                    }
                }
            }
        } else {
            echo 'There was no params file.';
            exit;
        }
        ksort( $layers );
        $_SESSION[__CLASS__]['layersForThumbnails'] = $layers;
        echo 'OK';
    }

    public function createThumbnails_start() {
        set_time_limit( 600 );
        $miniaturesFolder = SH_TEMPLATE_FOLDER . $_SESSION[__CLASS__]['template'] . '/images/miniatures/';
        $defaultFolder = $miniaturesFolder . 'default/';
        $createdMiniatures = 0;
        $layers = $_SESSION[__CLASS__]['layersForThumbnails'];
        foreach( $layers as $id => $layer ) {
            $layers[$id]['image'] = imagecreatefrompng( $layer['imageSrc'] );
        }
        $totalNumberOfImages = 37 * 5;
        foreach( array( 'reallyDark', 'dark', 'normal', 'shiny', 'reallyShiny' ) as $value ) {
            for( $a = 0; $a < 360; $a+=10 ) {
                $createdMiniatures++;
                $dest = imagecreatetruecolor( 150, 150 );
                foreach( $layers as $layer ) {
                    $_SESSION[__CLASS__]['progress'] = 'createThumbnails_addImage(' . $dest . ',$layer,$a,$value)';
                    $dest = $this->createThumbnails_addImage( $dest, $layer, $a, $value );
                }
                imagepng( $dest, $miniaturesFolder . $a . '_' . $value . '.png' );
                imagedestroy( $dest );
                $_SESSION[__CLASS__]['progress'] = 'Step ' . $createdMiniatures . ' on ' . $createdMiniatures . '.';
            }
            $createdMiniatures++;
            $dest = imagecreatetruecolor( 150, 150 );
            foreach( $layers as $layer ) {
                $_SESSION[__CLASS__]['progress'] = 'createThumbnails_addImage(' . $dest . ',$layer,$a,$value)';
                $dest = $this->createThumbnails_addImage( $dest, $layer, 370, $value );
            }
            imagepng( $dest, $miniaturesFolder . '370_' . $value . '.png' );
            imagedestroy( $dest );
            $_SESSION[__CLASS__]['progress'] = 'Step ' . $createdMiniatures . ' on ' . $createdMiniatures . '.';
        }

        $images = imagecreatetruecolor( 40 * 37, 200 );
        for( $a = 370; $a >= 0; $a -= 10 ) {
            if( $a < 360 ) {
                foreach( array( 'reallyDark', 'dark', 'normal', 'shiny', 'reallyShiny' ) as $top => $value ) {
                    if( file_exists( $miniaturesFolder . $a . '_' . $value . '.png' ) ) {
                        $tempImage = imagecreatefrompng( $miniaturesFolder . $a . '_' . $value . '.png' );
                        imagecopyresized( $images, $tempImage, $a * 4, $top * 40 + $addToTop, 0, 0, 37.5, 37.5, 150, 150 );
                        imagedestroy( $tempImage );
                    }
                }
            } elseif( $a == 370 ) {
                foreach( array( 'reallyDark', 'dark', 'normal', 'shiny', 'reallyShiny' ) as $top => $value ) {
                    if( file_exists( $miniaturesFolder . $a . '_' . $value . '.png' ) ) {
                        $tempImage = imagecreatefrompng( $miniaturesFolder . $a . '_' . $value . '.png' );
                        imagecopyresized( $images, $tempImage, ($a - 10) * 4, $top * 40 + $addToTop, 0, 0, 37.5, 37.5,
                                          80, 150 );
                        imagedestroy( $tempImage );
                    }
                }
            }
        }
        imagepng( $images, $miniaturesFolder . 'all_variations.png' );
        imagedestroy( $images );
        $_SESSION[__CLASS__]['progress'] = 'COMPLETED';
    }

    protected function createThumbnails_addImage( $dest, $layer, $hue, $value ) {
        $image = $layer['image'];
        $type = $layer['type'];
        $tempFile = SH_TEMPIMAGES_FOLDER . 'temp_' . date( 'U' ) . '.png';
        // We create the variation of the source file, if needed
        if( $type == 'default' ) {
            $translation = array( 'reallyDark' => -50, 'dark' => -25, 'normal' => 0, 'shiny' => 25, 'reallyShiny' => 50 );
            // We use $variation and $value
            $saturation = 0;
            if( $hue == 370 ) {
                $hue = 0;
                $saturation = -255;
            }
            $source = sh_colors::setHueToImage( $image, '', $hue, $saturation, $translation[$value], null, true );
            $destroy = true;
        } elseif( $type == 'none' ) {
            // We use the image as it is
            $source = $image;
            $destroy = false;
        } else {
            $palette = $layer['palette'];
            // We use the palette
            $hue = $palette[$hue][$value]['H'];
            $value = $palette[$hue][$value]['V'];
            $source = sh_colors::setHueToImage( $image, '', $hue, 0, $value, null, true );
            $destroy = true;
        }
        imagecopy( $dest, $source, 0, 0, 0, 0, 150, 150 );
        if( $destroy ) {
            imagedestroy( $source );
        }
        return $dest;
    }

    public function createThumbnails_progress() {
        echo $_SESSION[__CLASS__]['progress'];
        exit;
    }

    public function getPalettesColors( $forCss = false ) {
        if( version_compare( $this->templateVersion, 1.1, '>=' ) ) {
            static $cache = null;
            if( is_null( $cache ) ) {
                $ret = array( );
                $customizations = $this->get( 'customizations' );
                if( is_array( $customizations['images'] ) ) {
                    foreach( $customizations['images'] as $image => $imageId ) {
                        $color = $this->getParam(
                            'customizations>' . $this->template . '>images>' . $imageId,
                            $this->get( 'default_customizations>images>' . $imageId )
                        );
                        if($_SESSION['this_is_a_temp_session']){
                            if(isset($_SESSION['temp_session_form_datas']['images'][$imageId])){
                                $color = $_SESSION['temp_session_form_datas']['images'][$imageId];
                            }
                        }
                        $ret['hex@' . strtolower( $imageId )] = '#' . $color;
                    }
                }
                if( is_array( $customizations['menus'] ) ) {
                    foreach( $customizations['menus'] as $menuId => $menu ) {
                        if( $menu != 'spacer' ) {
                            $color = $this->getParam(
                                'customizations>' . $this->template . '>menus>' . $menuId,
                                $this->get( 'default_customizations>menus>' . $menuId )
                            );
                            if($_SESSION['this_is_a_temp_session']){
                                if(isset($_SESSION['temp_session_form_datas']['menus'][$menuId])){
                                    $color = $_SESSION['temp_session_form_datas']['menus'][$menuId];
                                }
                            }
                            $ret['hex@' . strtolower( $menuId )] = '#' . $color;
                        }
                    }
                }
                if( is_array( $customizations['texts'] ) ) {
                    foreach( $customizations['texts'] as $textId => $text ) {
                        if( $text != 'spacer' ) {
                            $color = $this->getParam(
                                'customizations>' . $this->template . '>texts>' . $textId,
                                $this->get( 'default_customizations>texts>' . $textId )
                            );
                            if($_SESSION['this_is_a_temp_session']){
                                if(isset($_SESSION['temp_session_form_datas']['texts'][$textId])){
                                    $color = $_SESSION['temp_session_form_datas']['texts'][$textId];
                                }
                            }
                            $ret['hex@' . strtolower( $textId )] = '#' . $color;
                        }
                    }
                }
                
                if($_SESSION['this_is_a_temp_session']){
                    $ret['suffix@image'] = '&temp_session='.$_SESSION['temp_session'].'&suf='.substr(md5(microtime()),0,8);
                }else{
                    $ret['suffix@image'] = 'suf='.'&suf='.substr(md5(microtime()),0,8);
                }
                
                $callback =
                    function ($a, $b) {
                        return strlen( $b ) - strlen( $a );
                    };

                uksort( $ret, $callback );
                $cache = $ret;
            }
            return $cache;
        }
        if( !$forCss ) {
            $renderer = 'xml';
        } else {
            $renderer = 'css';
        }
        if( empty( $this->renderingColors[$renderer] ) ) {
            $this->renderingColors[$renderer] = array( );
            $palettes = $this->get( 'palettes', array( ) );
            $variation = $this->linker->site->variation;
            $value = $this->linker->site->saturation;
            $templateFolder = $this->linker->site->templateFolder;
            foreach( $palettes as $paletteName => $paletteFile ) {
                if( file_exists( $templateFolder . 'palettes/' . $paletteFile ) ) {
                    include($templateFolder . 'palettes/' . $paletteFile);
                    if( !$forCss ) {
                        $this->renderingColors[$renderer]['palette_' . $paletteName] = $palette[$variation][$value];
                        $this->renderingColors[$renderer]['variation']['hue'] = $variation;
                        $this->renderingColors[$renderer]['variation']['value'] = $value;
                    } else {
                        foreach( $palette[$variation][$value] as $key => $entryValue ) {
                            $this->renderingColors[$renderer][strtolower( $key . '@' . $paletteName )] = $entryValue;
                        }
                    }
                }
            }
        }

        return $this->renderingColors[$renderer];
    }

    public function addPalette() {
        if( $this->formSubmitted( 'addPalette' ) ) {
            if( !is_dir( SH_TEMP_FOLDER . __CLASS__ ) ) {
                mkdir( SH_TEMP_FOLDER . __CLASS__ );
            }
            $file = $this->linker->form_elements->getFile( 'palette', SH_TEMP_FOLDER . __CLASS__ );
            if( $file['response'] != sh_form_elements::SUCCESS ) {
                $this->linker->html->addMessage( $this->getI18n( 'errorSendingFile' ) );
                $error = true;
            } else {
                if( substr( strtolower( $file['fileName'] ), -4 ) != '.png' ) {
                    $this->linker->html->addMessage( $this->getI18n( 'wrongFileType' ) );
                    $error = true;
                }
            }
            if( empty( $_POST['name'] ) || strtolower( $_POST['name'] ) == 'default' ) {
                $this->linker->html->addMessage( $this->getI18n( 'thereShouldBeAFilename' ) );
                $error = true;
            }
            if( !$error ) {
                // We have a palette, so we have to explode it
                $colors = sh_colors::explodePalette( $file['completeFileName'] );
                $file = dirname( $file['completeFileName'] ) . '/' . $_POST['name'] . '.php';
                $this->helper->writeArrayInFile(
                    $file, 'palette', $colors
                );
                $this->linker->html->addMessage( $this->getI18n( 'paletteSuccessfullySent' ) . $file );
                $this->linker->html->addMessage( $this->getI18n( 'paletteSuccessfullySentHowTo' ) );
            }
            $file['completeFileName'];
        }
        $this->render( 'addPalette', $values );
    }

    public function variation_change( $template, $variation, $value ) {
        $templateFolder = SH_TEMPLATE_FOLDER . $template . '/';
        $variationFolder = $templateFolder . 'images/variations/' . $variation . '_' . $value . '/';
        // We check if the variation has already been generated
        if( is_dir( $variationFolder ) ) {
            // It has, so we have nothing else to do...
            return true;
        }
        $defaultVariationFolder = $templateFolder . 'images/variations/default/';
        // We list the base files
        if( !is_dir( $defaultVariationFolder ) ) {
            // Nothing to do, there is no base file
            return true;
        }

        $transposition = array(
            sh_variation::SATURATION_REALLY_DARK => -50,
            sh_variation::SATURATION_DARK => -25,
            sh_variation::SATURATION_NORMAL => 0,
            sh_variation::SATURATION_SHINY => 25,
            sh_variation::SATURATION_REALLY_SHINY => 50
        );
        $transposedValue = $transposition[$value];
        set_time_limit( 300 );

        if( $variation == 370 ) {
            $variation = 0;
            $transposedValue = -260;
        }
        $this->variation_change_folder( $defaultVariationFolder, $variationFolder, $variation, $transposedValue );
        return true;
    }

    protected function variation_change_folder( $sourceFolder, $destinationFolder, $variation, $transposedValue ) {
        if( !is_dir( $destinationFolder ) ) {
            mkdir( $destinationFolder );
        }
        $files = scandir( $sourceFolder );
        foreach( $files as $file ) {
            if( substr( $file, -4 ) == '.png' ) {
                sh_colors::setHueToImage(
                    $sourceFolder . $file, $destinationFolder . $file, $variation, 0, $transposedValue
                );
            } elseif( substr( $file, 0, 1 ) != '.' && is_dir( $sourceFolder . $file ) ) {
                $this->variation_change_folder( $sourceFolder . $file . '/', $destinationFolder . $file . '/',
                                                $variation, $transposedValue );
            }
        }
    }

    public function select() {
        $this->linker->javascript->get( sh_javascript::LIGHTBOX );
        $this->onlyAdmin(true);
        if( $this->formSubmitted( 'templateChooser' ) ) {
            $this->changeTemplate( $_POST['template'] );
        }
        if( $_SESSION[__CLASS__]['templateHasChanged'] == true ) {
            $values['template']['changed'] = 'true';
            unset( $_SESSION[__CLASS__]['templateHasChanged'] );
            if( $this->linker->menu->hasMenuBeenDeactivated() ) {
                $values['template']['menuDeactivated'] = 'true';
                unset( $_SESSION[__CLASS__]['template_change_deactivated_menu'] );
            }
        }
        $scan = scandir( SH_TEMPLATE_FOLDER );
        if( is_array( $scan ) ) {
            foreach( $scan as $element ) {
                if( preg_match( '`(((sh_|cm_)[0-9]*)-(.+))`', $element, $matches ) ) {
                    if( $this->linker->site->templateIsAuthorized( $matches[1] ) ) {
                        if( $matches[1] == $this->linker->site->templateName ) {
                            $state = 'checked';
                            $values['template']['original'] = $matches[1];
                        } else {
                            $state = '';
                        }

                        $imagesRoot = $this->getImagesRoot( $element );

                        if( file_exists( SH_TEMPLATE_FOLDER . $matches[1] . '/template.description.php' ) ) {
                            list($id, $name) = explode( '-', $matches[1] );
                            include(SH_TEMPLATE_FOLDER . $matches[1] . '/template.description.php');
                            $firstSlide = $template['variations'];
                            $slides = array( );
                            if( is_array( $template['slides'] ) ) {
                                foreach( $template['slides'] as $slide ) {
                                    $slides[]['src'] = $imagesRoot . $slide['src'];
                                }
                            }
                        }

                        $values['templates'][$matches[2]] = array(
                            'name' => $matches[4],
                            'thumbnail' => $imagesRoot . $template['thumbnail'],
                            'completeName' => $matches[1],
                            'state' => $state,
                            'firstSlide' => $imagesRoot . $firstSlide,
                            'slides' => $slides
                        );
                    }
                }
            }
        }
        ksort( $values['templates'] );
        $this->render( 'select', $values );
    }

    public function changeTemplate( $template ) {
        $this->onlyAdmin();

        // We remove the cached datas
        $this->linker->css->uncache_css( );
        if( version_compare( $this->templateVersion, 1.1, '<' ) ) {
            // We should rebuild a cache
            $this->cache_template_datas();
        }
        
        $this->linker->site->changeTemplate( $template );

        $templateParams = $this->linker->params->get( $this->paramsFile, '' );

        $this->paramsFile = $this->linker->site->templateFolder . 'template.params.php';
        $this->linker->params->addElement( $this->paramsFile );

        $templateParams = $this->linker->params->get( $this->paramsFile, '' );

        
        $classes = $this->get_shared_methods( 'change' );

        foreach( $classes as $class ) {
            // We have found a class on which to call template_change();
            $this->linker->$class->template_change( $template );
        }
        $_SESSION[__CLASS__]['templateHasChanged'] = true;
        $this->linker->path->refresh();
        return true;
    }

    /**
     * public function get
     *
     */
    public function get( $paramName = self::ALL_VALUES, $onNotSet = sh_params::VALUE_NOT_SET ) {
        if( $paramName == self::ALL_VALUES ) {
            $paramName = '';
        }
        return $this->linker->params->get(
                $this->paramsFile, $paramName, $onNotSet
        );
    }

    public function getRenderFiles() {
        return $this->linker->params->get(
                $this->paramsFile, 'renderFiles', array( )
        );
    }

    public function getCSSFile( $file, $default = '' ) {
        return $this->linker->params->get(
                $this->paramsFile, 'css>' . $file, $default
        );
    }

    /**
     * public function get
     *
      public function setMenuFont($menuId, $font){
      $this->linker->params->set(
      $this->paramsFile,
      'menuButtons>'.$menuId.'>font',
      $font
      );
      return $this->linker->params->write($this->paramsFile);
      }
     */

    /**
     * Returns a site's parametter.
     * @example
     * <code>
     * echo $sh_site->defaultTitle;<br />
     * // Will echo the containt of $this->defaultTitle,<br />
     * // even if it is protected or private.<br />
     * // In that cases, it allows to get the values, <br />
     * // but not to set them.
     * </code>
     * @param string $name The name of the param that should be read
     * @return mixed
     * Returns the value of the param, if found, or false
     */
    public function __get( $paramName ) {
        return $this->get( $paramName );
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri( $page ) {
        list($class, $method, $id) = explode( '/', $page );
        if( $method == 'select' ) {
            return '/' . $this->shortClassName . '/select.php';
        }
        if( $method == 'addPalette' ) {
            return '/' . $this->shortClassName . '/addPalette.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage( $uri ) {
        if( $uri == '/' . $this->shortClassName . '/select.php' ) {
            return $this->shortClassName . '/select/';
        }
        if( $uri == '/' . $this->shortClassName . '/addPalette.php' ) {
            return $this->shortClassName . '/addPalette/';
        }
        return false;
    }

    public function __tostring() {
        return get_class();
    }

}
