<?php

/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Websailors 2012
 * @license All rights reserved
 * @version See version in sh_coupons::CLASS_VERSION.
 * @package Websailors' custom classes
 */
if( !defined( 'SH_MARKER' ) ) {
    header( 'location: directCallForbidden.php' );
}

/**
 * This class allows the creation of coupons for the shop
 */
class sh_coupons extends sh_core {

    const CLASS_VERSION = '1.1.12.04.03';

    protected static $allowed_sites = array( );
    public $minimal = array( );
    public $callWithoutId = array( );
    public $callWithId = array( 'edit' );
    protected $may_be_used = false;

    public function construct() {
        $this->may_be_used = $this->getParams( 'site_allowed', false );
        if( !$this->may_be_used ) {
            return false;
        }

        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            $this->linker->db->updateQueries( __CLASS__ );
            if( version_compare( $installedVersion, '1.1.12.04.03', '<' ) ) {
                $this->helper->addClassesSharedMethods( 'sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__ );
                $this->db_execute( 'create_table_coupons', array( ) );
            }

            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
    }

    public function edit() {
        $this->onlyAdmin();
        $id = ( int ) $this->linker->path->page[ 'id' ];
        
        if($this->formSubmitted( 'coupon_editor')){
            //if()
        }
        
        list($values['coupon']) = $this->db_execute('coupon_get_with_inactive', array('id'=>$id));
        $values['coupon']['type_'.$values['coupon']['reduction_type']] =  'selected';

        $this->render( 'edit', $values );
    }

    public function master_getMenuContent() {
        $masterMenu = array( );
        return $masterMenu;
    }

    public function admin_getMenuContent() {
        $adminMenu = array( );
        $adminMenu[ 'Boutique' ][ ] = array(
            'link' => 'coupons/list/',
            'text' => 'Liste des coupons',
            'icon' => 'picto_modify.png'
        );
        return $adminMenu;
    }
    
    public function sitemap_renew() {
        $this->addToSitemap( $this->shortClassName . '/homePage/', 1 );
        return true;
    }

    public function getPageName( $action, $id, $forUrl = false ) {
        if( $action == 'homePage' ) {
            return 'Page d\'accueil personnalisée de Sceau Numérique';
        }
        return false;
    }
    
    public function __tostring() {
        return get_class();
    }

}