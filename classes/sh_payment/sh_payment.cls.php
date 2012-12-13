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
 * This class manages the payment modes.
 *
 * How it works :
 * Let's say the class that is asking for a payment in the shop class.
 * 1/ shop asks for the payment modes objects
 * 2/ shop asks each payment modes (called banks, here) for its payment form
 *      (which contains links or form targetting to the payment method), giving also
 *      the cart contents, prices, callback methods, etc.
 * 3/ the bank prepares the payment, and creates a form redirecting to the
 *      payment page (or to the "payment successfull" page, if it needs so).
 * 4/ shop shows the different payment modes, and the user clicks the links or
 *      validate the forms of the bank he wants to use.
 * 5/ the user is redirected to the payment mode's page (enabling him to enter
 *      datas if needed), which may be on any webserver
 * 6/ the bank calls the shop's success or failure page. The shop does what it
 *      needs to, like removing products from the stock, or saying that the payment was 
 *      refused and asking for retry.
 *      If failure, it stops here. If success, let's continue a little bit
 * 7/ the bank, or the external payment server, redirects then either to success
 *      or failure page of the bank (eg. good credit card datas).
 * 8/ once the payment has really been made (which isn't the same as the first
 *      success, as for example payments by checks are always successes,
 *      because we can't wait for the payment to arrive to remove the objects from
 *      the stocks, but the check may never be sent or be refused), the bank
 *      calls the shop's paymentValidated or paymentUnauthorized pages.
 *      The first asks the vendor to prepare and send the command, to edit and send a bill,
 *      the second refills the stocks, and tells the vendor that the sale is out.
 * 
 * Note : if the Success and Validate steps are made at the same time (like for the payment by
 * credit card through ATOS), both methods HAVE to be called in the right order : 
 * 1 - success
 * 2 - validated
 * If it is not made, there could be a serious problem as validated doesn't call success.
 */
class sh_payment extends sh_core {

    const CLASS_VERSION = '1.1.11.12.06';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    public $minimal = array( 'cron_job' => true );
    protected $activatedBanks = array( );
    protected $defaultBank = null;
    protected $bankName = null;
    public $callWithoutId = array(
        'manual_collector'
    );

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            if( version_compare( $installedVersion, '1.1.11.03.29' ) < 0 ) {
                $this->helper->addClassesSharedMethods( 'sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_cron', '', __CLASS__ );
                if( !is_dir( SH_IMAGES_FOLDER . 'banks' ) ) {
                    mkdir( SH_IMAGES_FOLDER . 'banks' );
                    sh_browser::setRights(
                        SH_IMAGES_FOLDER . 'banks',
                        sh_browser::READ + sh_browser::ADDFILE + sh_browser::DELETEFILE + sh_browser::RENAMEFILE
                    );
                    sh_browser::setOwner( SH_IMAGES_FOLDER . 'banks' );
                    sh_browser::addDimension( SH_IMAGES_FOLDER . 'banks', 100, 100 );
                }
            }
            if( version_compare( $installedVersion, '1.1.11.11.15' ) < 0 ) {
                $this->getBanksList( true );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
        return false;
    }

    public function master_getMenuContent() {
        return array( );
    }

    public function admin_getMenuContent() {
        $adminMenu = array();
        if($this->linker->shop->isActivated()){
            $adminMenu[ 'Boutique' ][ ] = array(
                'link' => 'payment/manual_collector/',
                'text' => 'Encaissements manuels',
                'icon' => 'picto_money.png'
            );
        }
        return $adminMenu;
    }

    public function manual_collector() {
        $this->onlyAdmin();
        if( $this->formSubmitted( 'manually_collected' ) ) {
            if( isset( $_POST[ 'collector_validated' ] ) ) {
                $lookingInto = 'collector_validated';
                $accepted = true;
            } else {
                $lookingInto = 'collector_unAuthorized';
                $accepeted = false;
            }
            $class = array_pop( array_keys( $_POST[ $lookingInto ] ) );
            $payment_id = array_pop( array_keys( $_POST[ $lookingInto ][ $class ] ) );
            $this->linker->$class->payment_manually_collected( $payment_id, $accepted );
        }
        $this->linker->html->setTitle( $this->getI18n( 'manual_collector_title' ) );

        $classes = $this->get_shared_methods( 'require_manual_collection' );
        foreach( $classes as $class ) {
            $values[ 'collectors' ][ $class ] = $this->linker->$class->payment_manual_collector();
        }
        $this->render( 'manual_collector', $values );
    }

    public function addBankLogo_auto( $bankLogoFile, $subFolder ) {
        if( !is_dir( SH_IMAGES_FOLDER . 'banks/' . $subFolder . '/' ) ) {
            mkdir( SH_IMAGES_FOLDER . 'banks/' . $subFolder );
            sh_browser::setRights(
                SH_IMAGES_FOLDER . 'banks/' . $subFolder, sh_browser::READ
            );
        }
        if( !file_exists( SH_IMAGES_FOLDER . 'banks/' . $subFolder . '/' . basename( $bankLogoFile ) ) ) {
            copy( $bankLogoFile, SH_IMAGES_FOLDER . 'banks/' . $subFolder . '/' . basename( $bankLogoFile ) );
        }
    }

    /**
     * Declares a public address to a page in a specific bank.
     * That page may be accessed for a certain time only.
     * @param int $session The reference to the page to call.
     * @param int $bank The id of the bank to make the call to.
     * @param str $method The method to call on the bank $bank.
     * @param int $id Optional. The id of the page to call (like class/method/id).
     * Defaults to 0.
     * @param int $eraseDelay The amount of time the page may be called, in seconds.
     * Defaults to 1209600 seconds, which is 2 weeks (the time for any kind of payment should
     * be smaller than that, even for cheques).
     */
    public function setCallPage( $session, $bank, $method, $id = 0, $eraseDelay = 1209600 ) {
        $this->debug( 'Creating call page for ' . $bank . '->' . $method . '(' . $id . ') in session ' . $session, 3,
                      __LINE__ );
        if( !$session ) {
            // Getting a new session number between 1 000 and 1 000 000
            $session = rand( 1000000, 1000000000 );
            while( $this->getParam( 'callPage>' . $session, false ) ) {
                $session = rand( 1000000, 1000000000 );
            }
        }
        $eraseDelay = date(
            'U',
            mktime(
                date( 'h' ), date( 'i' ), date( 's' ) + $eraseDelay, date( 'm' ), date( 'd' ), date( 'Y' )
            )
        );

        $this->setParam(
            'callPage>' . $session,
            array(
            'bank' => $bank,
            'method' => $method,
            'id' => $id,
            'eraseDate' => $eraseDelay
            )
        );
        $this->writeParams();
        return $session;
    }

    public function callPage() {
        $this->debug( __FUNCTION__, 3, __LINE__ );
        sh_cache::disable();
        $id = $this->linker->path->page[ 'id' ];
        if( $this->getParam( 'callPage>' . $id, false ) ) {
            $session = $this->getParam( 'callPage>' . $id );
            $bank = $this->get( $session[ 'bank' ] );
            $method = $session[ 'method' ];
            if( $this->linker->method_exists( $bank, $method ) ) {
                return $bank->$method( $session[ 'id' ], $id );
            }
        }
        return false;
    }

    public function resetCallPage() {
        
    }

    protected function loadClass( $bankName ) {
        $this->debug( __FUNCTION__ . '(' . $bankName . ')', 3, __LINE__ );
        $bankFile = dirname( __FILE__ ) . '/banks/' . $bankName . '.cls.php';
        if( file_exists( $bankFile ) ) {
            include_once($bankFile);
            return true;
        }
        $this->debug( 'Bank class for ' . $bankName . ' was not found!', 0, __LINE__ );
        return false;
    }

    public function getActivePaymentModesCount() {
        $list = $this->getBanksList();
        $cpt = 0;
        if( is_array( $list ) ) {
            foreach( $list as $key => $element ) {
                if( $element[ 'active' ] ) {
                    $cpt++;
                }
            }
        }
        return $cpt;
    }

    public function manageBank() {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        $this->onlyAdmin();
        $id = ( int ) $this->linker->path->page[ 'id' ];
        $list = $this->getBanksList();
        $message = '';
        if( isset( $list[ $id ] ) ) {
            $cpt = 0;
            foreach( $list as $key => $element ) {
                if( $key == $id && $element[ 'active' ] ) {
                    $itIsActive = true;
                } elseif( $element[ 'active' ] ) {
                    $cpt++;
                }
            }
            if( $cpt == 0 && $itIsActive ) {
                $classes = $this->get_shared_methods( 'beforeNoPaymentModes' );
                foreach( $classes as $class ) {
                    $message.=$this->linker->$class->payment_beforeNoPaymentModes();
                }
                $_SESSION[ __CLASS__ ][ 'payment_beforeNoPaymentModes' ] = 'said';
            } elseif( $cpt == 0 ) {
                if( $_SESSION[ __CLASS__ ][ 'payment_beforeNoPaymentModes' ] == 'said' ) {
                    unset( $_SESSION[ __CLASS__ ][ 'payment_beforeNoPaymentModes' ] );
                    $classes = $this->get_shared_methods( 'onNoPaymentModes' );
                    foreach( $classes as $class ) {
                        $message.=$this->linker->$class->payment_onNoPaymentModes();
                    }
                }
            }
            $class = $list[ $id ][ 'class' ];
            $this->linker->html->setTitle( 'Paramètres du paiement "' . $list[ $id ][ 'name' ] . '"' );
            $this->linker->$class->manage( $message );
        }
    }

    public function getBanksList( $eraseBefore = false ) {
        static $bankList = array( );
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( $eraseBefore ) {
            $bankList = array( );
        }
        if( empty( $bankList ) ) {
            // We first get the classes that are properly declared
            $classes = $this->get_shared_methods( 'banks' );
            foreach( $classes as $class ) {
                $bankObject = $this->linker->$class;
                $bankCode = $bankObject->bank_getCode();
                $bankList[ $bankObject->bank_getCode() ] = array(
                    'class' => $bankObject->shortClassName,
                    'longClass' => $class,
                    'name' => $bankObject->bank_getName(),
                    'id' => $bankCode,
                    'logo' => $bankObject->bank_getLogo(),
                    'description' => $bankObject->bank_getDescription(),
                    'ready' => $bankObject->isReady(),
                    'active' => $bankObject->isActive(),
                    'edit' => $this->linker->path->getLink( __CLASS__ . '/manageBank/' . $bankCode )
                );
            }

            /*
              $classes = scandir(dirname(__FILE__).'/banks');
              if(is_array($classes)) {
              foreach ($classes as $class) {
              if(substr($class,-8) == '.cls.php') {
              $shortName = substr($class,3,-8);
              $className = substr($class,0,-8);
              if(!$this->loadClass($className)) {
              continue;
              }
              $bankObject = $this->linker->$shortName;
              $bankCode = $bankObject->bank_getCode();
              $this->debug('Found bank '.$bankObject->bank_getCode().' : '.$bankObject->bank_getName(), 3, __LINE__);
              $bankList[$bankObject->bank_getCode()] = array(
              'class' =>$shortName,
              'longClass' =>$className,
              'name'  =>$bankObject->bank_getName(),
              'id'    =>$bankCode,
              'logo'  =>$bankObject->bank_getLogo(),
              'description'  =>$bankObject->bank_getDescription(),
              'ready' =>$bankObject->isReady(),
              'active' =>$bankObject->isActive(),
              'edit' => $this->linker->path->getLink(__CLASS__.'/manageBank/'.$bankCode)
              );
              }
              }
              } */
        } else {
            $this->debug( 'Banklist already created', 3, __LINE__ );
        }
        return $bankList;
    }

    public function getAvailablePaymentModes( $alsoGetInactive = false ) {
        $list = $this->getBanksList();
        if( is_array( $list ) ) {
            foreach( $list as $key => $value ) {
                if( !$alsoGetInactive && !$value[ 'active' ] ) {
                    unset( $list[ $key ] );
                }
            }
        }
        return $list;
    }

    /**
     * This method gets and returns an instance of the mailer class. It may
     * return the internal mailer (which is phpMailer, for now), or the external,
     * which may be any mail senders (used for example for newsletters)
     * @param bool $externalMailer <b>false (default)</b> to get the internal
     * mailer<br />
     * <b>true</b> to get the external mailer
     * @return sh_mailsenders The mailer object
     */
    public function get( $bank = '' ) {
        if( !empty( $bank ) ) {
            $banks = $this->getBanksList();
            if( isset( $banks[ $bank ] ) ) {
                $class = $banks[ $bank ][ 'class' ];
                return $this->linker->$class;
            } else {
                return false;
            }
        }
        return $this->defaultBank;
    }

    public function createPaymentId() {
        $actual = $this->getParam( 'max_payment_id', rand( 0, 1000 ) );
        $cpt = $actual + 1;
        $this->setParam( 'max_payment_id', $cpt );
        $this->writeParams();
        $this->debug( 'Created payment ' . $cpt, 3, __LINE__ );
        return $cpt;
    }

    public function cron_job( $time ) {
        if( $time == sh_cron::JOB_DAY ) {
            // We check if there is any cleaning to make
            $callPages = $this->getParam(
                'callPage', array( )
            );
            $remainingCallPages = array( );
            foreach( $callPages as $id => $callPage ) {
                if( $callPage[ 'eraseDate' ] >= date( 'U' ) ) {
                    $remainingCallPages[ $id ] = $callPage;
                }
            }

            $this->setParam(
                'callPage', $remainingCallPages
            );
            $this->writeParams();
        }
        // We call the job on each bank
        $banks = $this->getBanksList();
        if( is_array( $banks ) ) {
            foreach( $banks as $oneBank ) {
                $bankName = $oneBank[ 'class' ];
                $rep = $this->linker->$bankName->cron_job( $time );
            }
        }

        return $rep;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri( $page ) {
        list($class, $method, $id) = explode( '/', $page );
        $withoutId = array(
        );
        $withId = array(
            'callPage', 'manageBank'
        );
        if( $id === '' && in_array( $method, $withoutId ) ) {
            return '/' . $this->shortClassName . '/' . $method . '.php';
        } elseif( in_array( $method, $withId ) ) {
            return '/' . $this->shortClassName . '/' . $method . '/' . $id . '.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage( $uri ) {
        if( preg_match( '`/' . $this->shortClassName . '/([^/]+)(/([0-9]+)(-[^/]+)?)?\.php`', $uri, $matches ) ) {
            $method = $matches[ 1 ];
            $id = $matches[ 3 ];
            $methods = array(
                'manageBank', 'callPage'
            );
            if( in_array( $method, $methods ) ) {
                return $this->shortClassName . '/' . $method . '/' . $id;
            }
        }
        return false;
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

}

/**
 * @abstract Class to be extended by most of all classes in the Shopsailors' engine.
 *
 */
abstract class sh_banks extends sh_core {

    protected $lastErrorId = 0;
    protected $active = false;
    protected $ready = false;
    protected $needsManagementPage = false;
    const DEV_NAME = 'Shopsailors';
    const DEV_WEBSITE = 'http://wiki.websailors.fr';
    const DEV_EMAIL = 'brice.parent@websailors.fr';

    const SUCCESS = 100;
    const FAILURE = 101;
    const SUCCESS_REQUIRES_VALIDATION = 102;


    // Currencies codes (ISO 4217)
    const CUR_ALL = 8, CUR_DZD = 12, CUR_ARS = 32, CUR_AUD = 36, CUR_BSD = 44, CUR_BHD = 48;
    const CUR_BDT = 50, CUR_AMD = 51, CUR_BBD = 52, CUR_BMD = 60, CUR_BTN = 64, CUR_BOB = 68;
    const CUR_BWP = 72, CUR_BZD = 84, CUR_SBD = 90, CUR_BND = 96, CUR_MMK = 104, CUR_BIF = 108;
    const CUR_KHR = 116, CUR_CAD = 124, CUR_CVE = 132, CUR_KYD = 136, CUR_LKR = 144, CUR_CLP = 152;
    const CUR_CNY = 156, CUR_COP = 170, CUR_KMF = 174, CUR_CRC = 188, CUR_HRK = 191, CUR_CUP = 192;
    const CUR_CZK = 203, CUR_DKK = 208, CUR_DOP = 214, CUR_SVC = 222, CUR_ETB = 230, CUR_ERN = 232;
    const CUR_EEK = 233, CUR_FKP = 238, CUR_FJD = 242, CUR_DJF = 262, CUR_GMD = 270, CUR_GIP = 292;
    const CUR_GTQ = 320, CUR_GNF = 324, CUR_GYD = 328, CUR_HTG = 332, CUR_HNL = 340, CUR_HKD = 344;
    const CUR_HUF = 348, CUR_ISK = 352, CUR_INR = 356, CUR_eNR = 356, CUR_IDR = 360, CUR_IRR = 364;
    const CUR_IQD = 368, CUR_ILS = 376, CUR_JMD = 388, CUR_JPY = 392, CUR_KZT = 398, CUR_JOD = 400;
    const CUR_KES = 404, CUR_KPW = 408, CUR_KRW = 410, CUR_KWD = 414, CUR_KGS = 417, CUR_LAK = 418;
    const CUR_LBP = 422, CUR_LSL = 426, CUR_LVL = 428, CUR_LRD = 430, CUR_LYD = 434, CUR_LTL = 440;
    const CUR_MOP = 446, CUR_MWK = 454, CUR_MYR = 458, CUR_MVR = 462, CUR_MRO = 478, CUR_MUR = 480;
    const CUR_MXN = 484, CUR_MNT = 496, CUR_MDL = 498, CUR_MAD = 504, CUR_OMR = 512, CUR_NAD = 516;
    const CUR_NPR = 524, CUR_ANG = 532, CUR_AWG = 533, CUR_VUV = 548, CUR_NZD = 554, CUR_NIO = 558;
    const CUR_NGN = 566, CUR_NOK = 578, CUR_PKR = 586, CUR_PAB = 590, CUR_PGK = 598, CUR_PYG = 600;
    const CUR_PEN = 604, CUR_PHP = 608, CUR_GWP = 624, CUR_QAR = 634, CUR_RUB = 643, CUR_RWF = 646;
    const CUR_SHP = 654, CUR_STD = 678, CUR_SAR = 682, CUR_SCR = 690, CUR_SLL = 694, CUR_SGD = 702;
    const CUR_VND = 704, CUR_SOS = 706, CUR_ZAR = 710, CUR_SZL = 748, CUR_SEK = 752, CUR_CHF = 756;
    const CUR_SYP = 760, CUR_THB = 764, CUR_TOP = 776, CUR_TTD = 780, CUR_AED = 784, CUR_TND = 788;
    const CUR_UGX = 800, CUR_MKD = 807, CUR_EGP = 818, CUR_GBP = 826, CUR_TZS = 834, CUR_USD = 840;
    const CUR_UYU = 858, CUR_UZS = 860, CUR_WST = 882, CUR_YER = 886, CUR_ZMK = 894, CUR_TWD = 901;
    const CUR_CUC = 931, CUR_ZWL = 932, CUR_TMT = 934, CUR_GHS = 936, CUR_VEF = 937, CUR_SDG = 938;
    const CUR_UYI = 940, CUR_RSD = 941, CUR_MZN = 943, CUR_AZN = 944, CUR_RON = 946, CUR_CHE = 947;
    const CUR_CHW = 948, CUR_TRY = 949, CUR_XAF = 950, CUR_XCD = 951, CUR_XOF = 952, CUR_XPF = 953;
    const CUR_XBA = 955, CUR_XBB = 956, CUR_XBC = 957, CUR_XBD = 958, CUR_XAU = 959, CUR_XDR = 960;
    const CUR_XAG = 961, CUR_XPT = 962, CUR_XTS = 963, CUR_XPD = 964, CUR_SRD = 968, CUR_MGA = 969;
    const CUR_COU = 970, CUR_AFN = 971, CUR_TJS = 972, CUR_AOA = 973, CUR_BYR = 974, CUR_BGN = 975;
    const CUR_CDF = 976, CUR_BAM = 977, CUR_EUR = 978, CUR_MXV = 979, CUR_UAH = 980, CUR_GEL = 981;
    const CUR_BOV = 984, CUR_PLN = 985, CUR_BRL = 986, CUR_CLF = 990, CUR_USN = 997, CUR_USS = 998;
    const CUR_XXX = 999, CUR_NONE = 0;

    const CUR_8 = 'ALL', CUR_12 = 'DZD', CUR_32 = 'ARS', CUR_36 = 'AUD', CUR_44 = 'BSD', CUR_48 = 'BHD', CUR_50 = 'BDT';
    const CUR_51 = 'AMD', CUR_52 = 'BBD', CUR_60 = 'BMD', CUR_64 = 'BTN', CUR_68 = 'BOB', CUR_72 = 'BWP', CUR_84 = 'BZD';
    const CUR_90 = 'SBD', CUR_96 = 'BND', CUR_104 = 'MMK', CUR_108 = 'BIF', CUR_116 = 'KHR', CUR_124 = 'CAD', CUR_132 = 'CVE';
    const CUR_136 = 'KYD', CUR_144 = 'LKR', CUR_152 = 'CLP', CUR_156 = 'CNY', CUR_170 = 'COP', CUR_174 = 'KMF', CUR_188 = 'CRC';
    const CUR_191 = 'HRK', CUR_192 = 'CUP', CUR_203 = 'CZK', CUR_208 = 'DKK', CUR_214 = 'DOP', CUR_222 = 'SVC', CUR_230 = 'ETB';
    const CUR_232 = 'ERN', CUR_233 = 'EEK', CUR_238 = 'FKP', CUR_242 = 'FJD', CUR_262 = 'DJF', CUR_270 = 'GMD', CUR_292 = 'GIP';
    const CUR_320 = 'GTQ', CUR_324 = 'GNF', CUR_328 = 'GYD', CUR_332 = 'HTG', CUR_340 = 'HNL', CUR_344 = 'HKD', CUR_348 = 'HUF';
    const CUR_352 = 'ISK', CUR_356 = 'INR', CUR_360 = 'IDR', CUR_364 = 'IRR', CUR_368 = 'IQD', CUR_376 = 'ILS';
    const CUR_388 = 'JMD', CUR_392 = 'JPY', CUR_398 = 'KZT', CUR_400 = 'JOD', CUR_404 = 'KES', CUR_408 = 'KPW', CUR_410 = 'KRW';
    const CUR_414 = 'KWD', CUR_417 = 'KGS', CUR_418 = 'LAK', CUR_422 = 'LBP', CUR_426 = 'LSL', CUR_428 = 'LVL', CUR_430 = 'LRD';
    const CUR_434 = 'LYD', CUR_440 = 'LTL', CUR_446 = 'MOP', CUR_454 = 'MWK', CUR_458 = 'MYR', CUR_462 = 'MVR', CUR_478 = 'MRO';
    const CUR_480 = 'MUR', CUR_484 = 'MXN', CUR_496 = 'MNT', CUR_498 = 'MDL', CUR_504 = 'MAD', CUR_512 = 'OMR', CUR_516 = 'NAD';
    const CUR_524 = 'NPR', CUR_532 = 'ANG', CUR_533 = 'AWG', CUR_548 = 'VUV', CUR_554 = 'NZD', CUR_558 = 'NIO', CUR_566 = 'NGN';
    const CUR_578 = 'NOK', CUR_586 = 'PKR', CUR_590 = 'PAB', CUR_598 = 'PGK', CUR_600 = 'PYG', CUR_604 = 'PEN', CUR_608 = 'PHP';
    const CUR_624 = 'GWP', CUR_634 = 'QAR', CUR_643 = 'RUB', CUR_646 = 'RWF', CUR_654 = 'SHP', CUR_678 = 'STD', CUR_682 = 'SAR';
    const CUR_690 = 'SCR', CUR_694 = 'SLL', CUR_702 = 'SGD', CUR_704 = 'VND', CUR_706 = 'SOS', CUR_710 = 'ZAR', CUR_748 = 'SZL';
    const CUR_752 = 'SEK', CUR_756 = 'CHF', CUR_760 = 'SYP', CUR_764 = 'THB', CUR_776 = 'TOP', CUR_780 = 'TTD', CUR_784 = 'AED';
    const CUR_788 = 'TND', CUR_800 = 'UGX', CUR_807 = 'MKD', CUR_818 = 'EGP', CUR_826 = 'GBP', CUR_834 = 'TZS', CUR_840 = 'USD';
    const CUR_858 = 'UYU', CUR_860 = 'UZS', CUR_882 = 'WST', CUR_886 = 'YER', CUR_894 = 'ZMK', CUR_901 = 'TWD', CUR_931 = 'CUC';
    const CUR_932 = 'ZWL', CUR_934 = 'TMT', CUR_936 = 'GHS', CUR_937 = 'VEF', CUR_938 = 'SDG', CUR_940 = 'UYI', CUR_941 = 'RSD';
    const CUR_943 = 'MZN', CUR_944 = 'AZN', CUR_946 = 'RON', CUR_947 = 'CHE', CUR_948 = 'CHW', CUR_949 = 'TRY', CUR_950 = 'XAF';
    const CUR_951 = 'XCD', CUR_952 = 'XOF', CUR_953 = 'XPF', CUR_955 = 'XBA', CUR_956 = 'XBB', CUR_957 = 'XBC', CUR_958 = 'XBD';
    const CUR_959 = 'XAU', CUR_960 = 'XDR', CUR_961 = 'XAG', CUR_962 = 'XPT', CUR_963 = 'XTS', CUR_964 = 'XPD', CUR_968 = 'SRD';
    const CUR_969 = 'MGA', CUR_970 = 'COU', CUR_971 = 'AFN', CUR_972 = 'TJS', CUR_973 = 'AOA', CUR_974 = 'BYR', CUR_975 = 'BGN';
    const CUR_976 = 'CDF', CUR_977 = 'BAM', CUR_978 = 'EUR', CUR_979 = 'MXV', CUR_980 = 'UAH', CUR_981 = 'GEL', CUR_984 = 'BOV';
    const CUR_985 = 'PLN', CUR_986 = 'BRL', CUR_990 = 'CLF', CUR_997 = 'USN', CUR_998 = 'USS', CUR_999 = 'XXX', CUR_0 = 'NONE';

    protected $bank_code = self::CUR_NONE;

    // ERRORS
    const ERROR_CUR_NOT_SUPPORTED = 1000;
    const ERROR_COUNTRY_NOT_SUPPORTED = 1001;

    const ERROR_NEGATIVE_PRICES_FORBIDDEN = 1500;

    const ERROR_BANK_PARAMETTERS = 1600;

    protected function setError( $id, $details = '' ) {
        $this->lastError = $this->getI18n( 'error_beginning' ) . $id . $this->getI18n( 'error_ending' );
        if( trim( $details ) != '' ) {
            $details = ' (' . $details . ')';
        }
        $this->lastError .= $this->getI18n( 'error_' . $id ) . $details;
        $this->debug( $this->lastError, 0 );
        return $id;
    }

    /**
     * Returns true if the payment mode is ready to be used, false if there are
     * things that should be set up using manageBank().
     */
    public function isReady() {
        return $this->ready === true;
    }

    /**
     * Returns true if the payment mode is set as useable (activated).
     */
    public function isActive() {
        return $this->active === true;
    }

    public function adminPanelCreation( $returnAdminContentArray = true ) {
        if( !$returnAdminContentArray ) {
            // We tell that we have an entry to add to the admin panel
            return $this->needsManagementPage;
        }
        return array(
            'link' => 'payment/manageBank/' . $this->bank_code,
            'text' => 'Paramètres de ' . $this->bank_getName(),
            'icon' => 'picto_money.png'
        );
    }

    public function reinitError() {
        $this->lastError = '';
        return true;
    }

    public function getErrorMessage( $id = 0 ) {
        if( $id == 0 ) {
            return $this->lastError;
        }
        return $this->getI18n( 'error_' . $id );
    }

    public function cron_job( $time ) {
        return true;
    }

    public function bank_getLogo() {
        $logoFile = SH_CLASS_FOLDER . $this->__tostring() . '/' . $this->__tostring() . '.png';
        if( file_exists( $logoFile ) ) {
            return $this->fromRoot( $logoFile );
        }
        return false;
    }

    final public function bank_getCode() {
        return $this->bank_code;
    }

    abstract public function bank_getDescription();

    abstract public function bank_getName();

    final public function payment_prepare( $id = 0 ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( $id == 0 ) {
            $id = $this->linker->payment->createPaymentId();
        }
        $this->debug( 'Payment id is ' . $id, 3, __LINE__ );
        return $id;
    }

    /**
     * Sets the price of the payment
     * @param int $payment Id of the payment
     * @param int $price The price should be given using the smaller part of
     * the currency (like in cents for euros or dollars).
     * @return True, or an error number
     */
    abstract public function payment_setPrice( $payment, $price, $decimalPrice );

    abstract public function payment_setCurrency( $payment, $currency );

    abstract public function payment_setCountry( $payment, $country );
    
    public function payment_setCustomerMail( $payment, $mail ) {}

    abstract public function payment_setSuccessPage( $payment, $page );

    abstract public function payment_setFailurePage( $payment, $page );

    abstract public function payment_setValidatedPage( $payment, $page );

    abstract public function payment_setUnauthorizedPage( $payment, $page );

    abstract public function payment_action( $payment );

    abstract public function payment_getTicket( $payment );
    
}
