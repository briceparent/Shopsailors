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
 * Class that creates and manages forms elements, such as checkboxes and options
 */
class sh_form_elements extends sh_core {

    const CLASS_VERSION = '1.1.12.02.09';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    protected $minimal = array( 'change' => true );

    const SUCCESS = 'success';
    const ERROR = 'error';
    const ERROR_NOT_FOUND = 'not found';
    const ERROR_INEXISTANT_DIRECTORY = 'inexistant directory';
    const ERROR_FILE_ALREADY_EXISTS = 'file already exists';
    const ERROR_UNDOCUMENTED = 'error not documented';

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            if( version_compare( $installedVersion, '1.1.11.03.28', '<' ) ) {
                // The class datas are not in the same version as this file, or don't exist (installation)
                $this->linker->renderer->add_render_tag( 'render_checkbox', __CLASS__, 'render_checkbox' );
                $this->linker->renderer->add_render_tag( 'render_radiobox', __CLASS__, 'render_radiobox' );
            }
            if( version_compare( $installedVersion, '1.1.12.02.09', '<' ) ) {
                $this->helper->addClassesSharedMethods('sh_events', 'onAfterBaseConstruction', __CLASS__);
            }
            
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
    }
    
    public function onAfterBaseConstruction(){
        // We may have to do special actions on the post datas, like cleaning wEditors' generated texts
        if( isset( $_POST[ 'entries_verifier' ] ) ) {
            foreach( $_POST[ 'entries_verifier' ] as $class => $editors ) {
                if( method_exists( $class, 'form_verifier_content' ) ) {
                    foreach( $editors as $field ) {
                        $element = & $_POST;
                        while( preg_match( '`([a-zA-Z0-9_-]+)\[(.+)\]`', $field, $matches ) ) {
                            $element = & $element[ $matches[ 1 ] ];
                            $field = $matches[ 2 ];
                        }
                        $element[ $field ] = $this->linker->$class->form_verifier_content( $element[ $field ] );
                    }
                }
            }
        }
    }

    public function render_checkbox( $attributes = array( ) ) {
        if( isset( $attributes[ 'text' ] ) ) {
            $values[ 'element' ][ 'text' ] = $attributes[ 'text' ];
            unset( $attributes[ 'text' ] );
        }
        if( isset( $attributes[ 'disabled' ] ) && $attributes[ 'disabled' ] == 'disabled' ) {
            $values[ 'element' ][ 'disabled' ] = true;
        }
        if( isset( $attributes[ 'readonly' ] ) && $attributes[ 'readonly' ] == 'readonly' ) {
            $values[ 'element' ][ 'disabled' ] = true;
        }
        if(
            isset( $attributes[ 'state' ] ) &&
            $attributes[ 'state' ] == 'disabled' ||
            $attributes[ 'state' ] == 'readonly'
        ) {
            $values[ 'element' ][ 'disabled' ] = true;
        }
        if( isset( $attributes[ 'help' ] ) ) {
            $values[ 'help' ][ 'content' ] = $attributes[ 'help' ];
            unset( $attributes[ 'help' ] );
        }

        $attributeString = $separator = '';
        foreach( $attributes as $attributeName => $attributeValue ) {
            if( strpos( $attributeValue, '"' ) === false ) {
                $attributeString .= $separator . $attributeName . '="' . $attributeValue . '"';
            } else {
                $attributeString .= $separator . $attributeName . "='" . $attributeValue . "'";
            }
            if( $attributeName == 'id' ) {
                $uid = $attributeValue;
            }
            $separator = ' ';
        }
        if( !isset( $uid ) ) {
            $uid = 'cb_' . substr( md5( __CLASS__ . microtime() ), 0, 8 );
            $attributeString .= ' id="' . $uid . '"';
        }
        $attributeString .= ' type="checkbox"';

        $values[ 'element' ][ 'uid' ] = $uid;
        $values[ 'element' ][ 'attributes' ] = $attributeString;
        $values[ 'element' ][ 'jsmethod' ] = 'cbox_' . $uid;
        $values[ 'element' ][ 'checkUncheck' ] = true;
        $values[ 'element' ][ 'class' ] = 'betterCheckbox';

        //@todo Replace the following lines by <label for="..."> in rf.xml
        //$browser = get_browser();
        //$values['userAgent'][$browser->browser] = true;

        return $this->render( 'form_element', $values, false, false );
    }

    public function render_radiobox( $attributes = array( ) ) {
        if( isset( $attributes[ 'text' ] ) ) {
            $values[ 'element' ][ 'text' ] = $attributes[ 'text' ];
            unset( $attributes[ 'text' ] );
        }
        if( isset( $attributes[ 'disabled' ] ) && $attributes[ 'disabled' ] == 'disabled' ) {
            $values[ 'element' ][ 'disabled' ] = true;
        }
        if( isset( $attributes[ 'readonly' ] ) && $attributes[ 'readonly' ] == 'readonly' ) {
            $values[ 'element' ][ 'disabled' ] = true;
        }
        if(
            isset( $attributes[ 'state' ] ) &&
            $attributes[ 'state' ] == 'disabled' ||
            $attributes[ 'state' ] == 'readonly'
        ) {
            $values[ 'element' ][ 'disabled' ] = true;
        }
        if( isset( $attributes[ 'help' ] ) ) {
            $values[ 'help' ][ 'content' ] = $attributes[ 'help' ];
            unset( $attributes[ 'help' ] );
        }

        $attributeString = $separator = '';
        foreach( $attributes as $attributeName => $attributeValue ) {
            if( strpos( $attributeValue, '"' ) === false ) {
                $attributeString .= $separator . $attributeName . '="' . $attributeValue . '"';
            } else {
                $attributeString .= $separator . $attributeName . "='" . $attributeValue . "'";
            }
            if( $attributeName == 'id' ) {
                $uid = $attributeValue;
            }
            $separator = ' ';
        }
        if( !isset( $uid ) ) {
            $uid = 'rb_' . substr( md5( __CLASS__ . microtime() ), 0, 8 );
            $attributeString .= ' id="' . $uid . '"';
        }
        $attributeString .= ' type="radio"';

        $values[ 'element' ][ 'uid' ] = $uid;
        $values[ 'element' ][ 'attributes' ] = $attributeString;
        $values[ 'element' ][ 'jsmethod' ] = 'rbox_' . $uid;

        //@todo Replace the following lines by <label for="..."> in rf.xml
        //$browser = get_browser();
        //$values['userAgent'][$browser->browser] = true;

        return $this->render( 'form_element', $values, false, false );
    }

    /**
     * Stores a file that was sent using the form input $formElementName in the folder $destinationFolder.
     * @param str $formElementName The name of the form element.
     * @param str $destinationFolder The path in which to store the file. The folder
     * must already exist.<br />
     * Defaults to SH_TEMP_FOLDER
     * @param bool $replace True if the new file should replace an existing one, false if not.
     * @return bool True for success, false for failure
     */
    public function getFile( $formElementName, $destinationFolder = SH_TEMP_FOLDER, $newName = false,
                             $addSentFileExtension = true ) {
        if( !isset( $formElementName[ $formElementName ] ) ) {
            return array( 'response' => self::ERROR, 'error' => self::ERROR_NOT_FOUND );
        }
        if( substr( $destinationFolder, -1 ) != '/' ) {
            $destinationFolder .= '/';
        }
        if( !is_dir( $destinationFolder ) ) {
            return array( 'response' => self::ERROR, 'error' => self::ERROR_INEXISTANT_DIRECTORY );
        }
        if( $newName ) {
            $fileName = $newName;
            if( $addSentFileExtension ) {
                $sentFileExtension = array_pop( explode( '.', $_FILES[ $formElementName ][ 'name' ] ) );
                $fileName .= '.' . $sentFileExtension;
            }
        } else {
            $fileName = $_FILES[ $formElementName ][ 'name' ];
        }
        if( file_exists( $destinationFolder . $fileName ) ) {
            unlink( $destinationFolder . $fileName );
        }
        if( move_uploaded_file( $_FILES[ $formElementName ][ 'tmp_name' ], $destinationFolder . $fileName ) ) {
            return array(
                'response' => self::SUCCESS,
                'fileName' => $fileName,
                'completeFileName' => $destinationFolder . $fileName
            );
        }
        return array( 'response' => self::ERROR, 'error' => self::ERROR_UNDOCUMENTED );
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

}