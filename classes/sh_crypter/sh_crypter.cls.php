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
 * This is just used to crypt and uncrypt datas.<br />
 * Take care that the mcrypt extension should either be installed on both the server
 * and the master server, or on none of them. Any other combination would create
 * corruption of datas.
 */
class sh_crypter extends sh_core {

    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    protected $mcrypt_active = true;

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
        $allowed = $this->getParam( 'allowed_to_use_mcrypt', true );
        $this->mcrypt_active = function_exists( 'mcrypt_encrypt' ) && $allowed;
        if( !$this->mcrypt_active ) {
            // We will use the blowfish class instead of mcrypt
            include(dirname( __FILE__ ) . '/blowfish.php');
        }
    }

    /**
     * This method encrypts $content using the password $key.<br />
     * If the mcrypt extension is installed, it will use $cipher as cipher and
     * $mode as mode.<br />
     * If not, no matter what is given in $cipher and $mode, this method will use
     * blowfish in CBC mode, with RFC padding style. In this case, it uses Matt Harris'
     * blowfish class instead of the mcrypt extension.
     * @param str $content The text to encrypt.
     * @param str $key The secret key. Defaults to an empty string.
     * @param str $cipher One of the MCRYPT_ciphername PHP constants. Defaults to MCRYPT_RIJNDAEL_256.
     * @param str $mode One of the MCRYPT_MODE_modename PHP constants. Defaults to MCRYPT_MODE_CBC.
     * @return str The encrypted text.
     */
    public function crypt( $content, $key = '', $cipher = MCRYPT_RIJNDAEL_256, $mode = MCRYPT_MODE_CBC ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        $this->debug( 'Crypting with key ' . $key, 3, __LINE__ );

        if( $this->mcrypt_active ) {
            $iv_size = mcrypt_get_iv_size( $cipher, $mode );
            $iv = substr( md5( 'hskjdh kjqsdnqndqs; sqnd;qskjdhkjha knd;n;za jkah' ), 0, $iv_size );
            if( $key == '' ) {
                $key = MD5( __CLASS__ . 'defaultpass' );
                $this->debug( 'No key given. We will use ' . $key, 3, __LINE__ );
            } else {
                $key = MD5( __CLASS__ . $key );
                $this->debug( 'A key was given. We will use ' . $key, 3, __LINE__ );
            }
            $ret = $iv . mcrypt_encrypt( $cipher, $key, $content, $mode, $iv );
        } else {
            $iv = md5( 'jdqlkj ,ql dqd45dq454 ù;:sqmqdqsdd1216qq2s sqqsd!' );
            // We will use the blowfish class instead of mcrypt
            $ret = Blowfish::encrypt(
                    $content, $key,
                    Blowfish::BLOWFISH_MODE_CBC,
                    Blowfish::BLOWFISH_PADDING_RFC,
                    $iv
            );
        }
        return $ret;
    }

    /**
     * This method decrypts $content using the password $key.<br />
     * If the mcrypt extension is installed, it will use $cipher as cipher and 
     * $mode as mode.<br />
     * If not, no matter what is given in $cipher and $mode, this method will use
     * blowfish in CBC mode, with RFC padding style. In this case, it uses Matt Harris'
     * blowfish class instead of the mcrypt extension.
     * @param str $content The encrypted text to decrypt.
     * @param str $key The secret key. Defaults to an empty string.
     * @param str $cipher One of the MCRYPT_ciphername PHP constants. Defaults to MCRYPT_RIJNDAEL_256.
     * @param str $mode One of the MCRYPT_MODE_modename PHP constants. Defaults to MCRYPT_MODE_CBC.
     * @return str The decrypted text.
     */
    public function uncrypt( $content, $key = '', $cipher = MCRYPT_RIJNDAEL_256, $mode = MCRYPT_MODE_CBC ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        $this->debug( 'Uncrypting with key ' . $key, 3, __LINE__ );
        if( !empty( $content ) ) {
            if( $this->mcrypt_active ) {
                $iv_size = mcrypt_get_iv_size( $cipher, $mode );
                $iv = substr( md5( 'hskjdh kjqsdnqndqs; sqnd;qskjdhkjha knd;n;za jkah' ), 0, $iv_size );
                $content = substr( $content, $iv_size );
                if( $key == '' ) {
                    $key = MD5( __CLASS__ . 'defaultpass' );
                    $this->debug( 'No key given. We will use ' . $key, 3, __LINE__ );
                } else {
                    $key = MD5( __CLASS__ . $key );
                    $this->debug( 'A key was given. We will use ' . $key, 3, __LINE__ );
                }
                $uncrypted = mcrypt_decrypt( $cipher, $key, $content, $mode, $iv );
                $ret = rtrim( $uncrypted, "\0\4" );
            } else {
                $iv = md5( 'jdqlkj ,ql dqd45dq454 ù;:sqmqdqsdd1216qq2s sqqsd!' );
                // We will use the blowfish class instead of mcrypt
                $ret = Blowfish::decrypt(
                        $content, 
                        $key, 
                        Blowfish::BLOWFISH_MODE_CBC,
                        Blowfish::BLOWFISH_PADDING_RFC, 
                        $iv
                );
            }
            return $ret;
        }
        return 'Nothing to uncrypt...';
    }

    public function __tostring() {
        return get_class();
    }

}