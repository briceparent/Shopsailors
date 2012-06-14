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

include_once(SH_CLASS_FOLDER . '/sh_payment_atos/sh_payment_atos.cls.php');

/**
 * This class implements the e-transactions functionnalities from Crédit Agricole.
 * It is using the CIPS/ATOS module, so it extends cm_atos
 */
class sh_payment_elysnet extends sh_payment_atos {

    const CLASS_VERSION = '1.1.11.12.05';

    protected $parmcom_bankExtension = 'elysnet';
    protected $lastErrorId = 0;
    protected $ready = false;
    const DEV_NAME = 'Elys Net / Shopsailors';
    const DEV_WEBSITE = 'http://wiki.websailors.fr';
    const DEV_EMAIL = 'brice.parent@websailors.fr';
    protected $bank_code = 30056;
    protected $bankName = 'Elys Net - HSBC';
    protected $countries = array(
        'fr', 'ge', 'en', 'sp', 'it'
    );

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            if( version_compare( $installedVersion, '1.1.11.12.05' ) < 0 ) {
                $this->helper->addClassesSharedMethods( 'sh_payment', 'banks', __CLASS__ );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }

        parent::construct();
    }

    public function bank_getName() {
        return $this->bankName;
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

}
