<?php

/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if( !defined( 'SH_MARKER' ) ) {
    header( 'location: directCallForbidden.php' );
}

/**
 * Class that renders the css files, replacing the colors by those that should
 * be used in the variation.
 */
class sh_css extends sh_core {

    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    protected $minimal = array( 'get' => true );

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
    }

    public function uncache_css() {
        $this->cachedPart_remove( '*', 'any' );
    }

    public function cache_css() {
        $lang = 'any';
        if( $_SESSION[ 'this_is_a_temp_session' ] ) {
            $lang = 'temp';
        }
        // We generate the css files
        $cssFiles = glob( $this->linker->template->path . 'css/*\.css' );

        foreach( $cssFiles as $cssFile ) {
            $css = $this->linker->renderer->render_css( $cssFile );

            // We check if we have things to add at the end
            $file = str_replace( '.css', '', basename( $cssFile ) );
            $method = 'addTo' . ucfirst( $file ) . 'CSS';
            $classes = $this->get_shared_methods( $method );
            if( !empty( $classes ) ) {
                foreach( $classes as $class ) {
                    $addToContent = "\n" . '/* Added contents from ' . $class . ' */' . "\n";
                    $addToContent .= trim( $this->linker->$class->$method() );
                    $css .= $this->linker->renderer->render_css( $addToContent );
                }
            } else {
                $css .= '/* No files to include */' . "\n";
            }
            // Saving it to the site's template folder
            $mobile = SH_MOBILE_DEVICE == false ? '' : '|' . SH_MOBILE_DEVICE;
            $this->cachedPart_cache( $css, basename( $cssFile ) . $mobile, $lang );
        }
    }

    /**
     * public function get
     *
     */
    public function get() {
        // Disables automatic caching
        $file = $_GET[ 'file' ];

        header( "Content-type: text/css" );

        $lang = 'any';
        if( $_SESSION[ 'this_is_a_temp_session' ] ) {
            $lang = 'temp';
        }

        $mobile = SH_MOBILE_DEVICE == false ? '' : '|' . SH_MOBILE_DEVICE;
        $cache = $this->cachedPart_get( $file . '.css' . $mobile, $lang );

        if( $cache ) {
            echo $cache;
            return true;
        }

        $templateFolder = $this->linker->site->templateFolder;

        // We check if we have things to add at the end
        $method = 'addTo' . ucfirst( $file ) . 'CSS';
        $addToContent = '';
        $classes = $this->get_shared_methods( $method );
        if( !empty( $classes ) ) {
            foreach( $classes as $class ) {
                $addToContent .= "\n" . '/* Added contents from ' . $class . ' */' . "\n";
                $ret = trim( $this->linker->$class->$method() );

                $addToContent .= $ret;
            }
        } else {
            $addToContent = '/* No files to include */' . "\n";
        }

        if( $_GET[ 'action' ] == 'replace' ) {

            $cssFile = $templateFolder . 'css/' . $file . '.css';
            if( file_exists( $cssFile ) ) {
                $content = $this->linker->renderer->render_css( $cssFile, false );
            }
            if( $content == '' ) {
                $content = '/* The CSS file "' . $cssFile . '" could not be found... */';
            }

            $addToContent = $this->linker->renderer->render_css( $addToContent, false );
        } elseif( $_GET[ 'action' ] == 'copy' ) {
            $cssFile = SH_TEMPLATE_FOLDER . 'global/' . $file . '.css';
            $content = '';
            if( file_exists( $cssFile ) ) {
                $content = $this->linker->renderer->render_css( $cssFile, false );
            }

            if( $content == '' ) {
                $content = '/* The CSS file "' . $cssFile . '" could not be found... */';
            }

            // We also add, if it exists, the contents of the same-named css file from the template
            if( file_exists( $templateFolder . 'css/' . $file . '.css' ) ) {
                $content .= "\n\n" . '/* The template wants to add some rules... */' . "\n";
                $content .= $this->linker->renderer->render_css( $templateFolder . 'css/' . $file . '.css', false );
            }
        }
        $content .= $addToContent;

        //sh_cache::startCache();
        echo $content;
        //echo sh_cache::stopCache();
        return true;
    }

    public function __tostring() {
        return get_class();
    }

}
