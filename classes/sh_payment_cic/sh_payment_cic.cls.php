<?php

/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See sh_payment_cic::CLASS_VERSION
 * @package Shopsailors Core Classes
 */
if( !defined( 'SH_MARKER' ) ) {
    header( 'location: directCallForbidden.php' );
}

$paymentObject = sh_linker::getInstance()->payment;

class sh_payment_cic extends sh_banks {

    const CLASS_VERSION = '1.1.11.05.10';

    protected $ready = false;
    protected $bank_code = 0;
    protected $payments = array( );
    protected $paymentConfirmPagesParams = null;
    protected $paymentsLogParams = null;
    protected $price = false;
    protected $successUrl = '';
    protected $failureUrl = '';
    protected $autoresponseUrl = '';
    protected $pathFolder = '';
    protected $currency = 0;
    protected $currencies = array(
        self::CUR_EUR, self::CUR_USD, self::CUR_CHF, self::CUR_GBP, self::CUR_CAD, self::CUR_JPY
    );
    protected $languages = array(
        'FR', 'EN', 'DE', 'IT', 'ES', 'NL', 'PT', 'SV'
    );
    protected $lang = 'EN';
    protected $customer_mail = '';

    public function GetClassConstants() {
        $oClass = new ReflectionClass( $this );
        return $oClass->getConstants();
    }

    public function construct() {
        define ("CMCIC_CLE", "12345678901234567890123456789012345678P0");
        define ("CMCIC_TPE", "0000001");
        define ("CMCIC_VERSION", "3.0");
        define ("CMCIC_SERVEUR", "https://ssl.paiement.cic-banques.fr/test/");
        define ("CMCIC_CODESOCIETE", "71af122290dd4a29a033");
        define ("CMCIC_URLOK", "http://www.websailors.fr/index.php?payment=ok");
        define ("CMCIC_URLKO", "http://www.websailors.fr/index.php?payment=error");
        
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {

            if( version_compare( $installedVersion, '1.1.11.05.10', '<' ) ) {
                if( !is_dir( SH_SITE_FOLDER . __CLASS__ ) ) {
                    mkdir( SH_SITE_FOLDER . __CLASS__ );
                }
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
    }

    public function payment_setLang( $lang ) {
        if( in_array( $lang, $this->languages ) ) {
            $this->lang = $lang;
        }
        $this->lang = 'EN';
    }

    public function payment_setCustomerMail( $mail ) {
        $this->customer_mail = $mail;
    }

    public function manage( $message = '' ) {
    }

    public function cron_job( $time ) {
        return true;
    }

    public function bank_getName() {
        return $this->getI18n( 'bank_name' );
    }

    public function bank_getDescription() {
        return $this->getI18n( 'bank_description' );
    }

    public function payment_setPrice( $payment, $price, $decimalPrice ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( $price > 0 ) {
            $this->price = $price;
            return true;
        }
        return $this->setError( self::ERROR_NEGATIVE_PRICES_FORBIDDEN );
    }

    public function payment_setCurrency( $payment, $currency ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( !in_array( $currency, $this->currencies ) ) {
            return $this->setError( self::ERROR_CUR_NOT_SUPPORTED );
        }
        // Convert from currency id to currency name (3 chars)
        $curCode = 'CUR_' . $currency;
        $this->currency = self::$curCode;
        return true;
    }

    public function payment_setCountry( $payment, $merchant_country ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( !in_array( $merchant_country, $this->countries ) ) {
            return $this->setError( self::ERROR_COUNTRY_NOT_SUPPORTED );
        }
        $this->merchant_country = $merchant_country;
    }

    public function payment_setFailurePage( $payment, $page ) {
        $this->debug( __FUNCTION__ . '(' . $payment . ', ' . $page . ')', 2, __LINE__ );
        $this->linker->params->addElement( $this->paymentConfirmPagesParams, true );
        $this->linker->params->set( $this->paymentConfirmPagesParams, 'payments>' . $payment . '>failurePages', $page );
        $this->linker->params->write( $this->paymentConfirmPagesParams );
    }

    public function payment_setSuccessPage( $payment, $page ) {
        $this->debug( __FUNCTION__ . '(' . $payment . ', ' . $page . ')', 2, __LINE__ );
        $this->linker->params->addElement( $this->paymentConfirmPagesParams, true );
        $this->linker->params->set( $this->paymentConfirmPagesParams, 'payments>' . $payment . '>successPages', $page );
        $this->linker->params->write( $this->paymentConfirmPagesParams );
    }

    public function payment_setValidatedPage( $payment, $page ) {
        $this->debug( __FUNCTION__ . '(' . $payment . ', ' . $page . ')', 2, __LINE__ );
        $this->linker->params->addElement( $this->paymentConfirmPagesParams, true );
        $this->linker->params->set( $this->paymentConfirmPagesParams, 'payments>' . $payment . '>validatedPages', $page );
        $this->linker->params->write( $this->paymentConfirmPagesParams );
    }

    public function payment_setUnauthorizedPage( $payment, $page ) {
        $this->debug( __FUNCTION__ . '(' . $payment . ', ' . $page . ')', 2, __LINE__ );
        $this->linker->params->addElement( $this->paymentConfirmPagesParams, true );
        $this->linker->params->set( $this->paymentConfirmPagesParams, 'payments>' . $payment . '>unauthorizedPage',
                                    $page );
        $this->linker->params->write( $this->paymentConfirmPagesParams );
    }

    /**
     * This method is called by the user when he clicks "cancel" on the bank's atos server.
     * @return * The return of the method called
     */
    public function payment_failure( $payment ) {
        return true;
    }

    /**
     * This method is called by the user when he clicks "back to the shop (after payment success)" on the bank's atos server.
     * @return * The return of the method called
     */
    public function payment_success( $payment ) {
        return true;
    }

    /**
     * This method may be called automatically by the bank's atos server, or manually by the user whan he clicks on the
     * "cancel" or "back to the shop (after payment success)" buttons
     * @return bool always returns true
     */
    public function payment_autoresponse() {
        return true;
    }

    public function payment_action( $payment ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );

        $sOptions = "";
        $sReference = substr( md5( microtime() ), 0, 12 );
        $sMontant = $this->price;
        $sDevise = $this->currency;
        $sTexteLibre = $payment;
        $sDate = date( "d/m/Y:H:i:s" );
        $sLangue = $this->lang;
        $sEmail = $this->customer_mail;

        $sNbrEch = "";
        $sDateEcheance1 = "";$sMontantEcheance1 = "";
        $sDateEcheance2 = "";$sMontantEcheance2 = "";
        $sDateEcheance3 = "";$sMontantEcheance3 = "";
        $sDateEcheance4 = "";$sMontantEcheance4 = "";

        $oTpe = new CMCIC_Tpe( $sLangue );
        $oHmac = new CMCIC_Hmac( $oTpe );

        // Control String for support
        $CtlHmac = sprintf( 
            CMCIC_CTLHMAC, $oTpe->sVersion, $oTpe->sNumero,
            $oHmac->computeHmac( 
                sprintf( 
                    CMCIC_CTLHMACSTR, $oTpe->sVersion, $oTpe->sNumero 
                ) 
            ) 
        );

        // Data to certify
        $PHP1_FIELDS = sprintf( 
            CMCIC_CGI1_FIELDS, $oTpe->sNumero, $sDate, $sMontant, $sDevise, $sReference,
            $sTexteLibre, $oTpe->sVersion, $oTpe->sLangue, $oTpe->sCodeSociete, $sEmail, $sNbrEch,
            $sDateEcheance1, $sMontantEcheance1, $sDateEcheance2, $sMontantEcheance2,
            $sDateEcheance3, $sMontantEcheance3, $sDateEcheance4, $sMontantEcheance4, $sOptions 
        );

        // MAC computation
        $sMAC = $oHmac->computeHmac( $PHP1_FIELDS );

        // Setting the parametters
        $successSession = $this->linker->payment->setCallPage(
            0, $this->bank_code, 'payment_success', $payment
        );
        $this->successUrl = $this->linker->path->getLink(
            'payment/callPage/' . $successSession
        );

        $failureSession = $this->linker->payment->setCallPage(
            0, $this->bank_code, 'payment_failure', $payment
        );
        $this->failureUrl = $this->linker->path->getLink(
            'payment/callPage/' . $failureSession
        );

        $autoresponseSession = $this->linker->payment->setCallPage(
            0, $this->bank_code, 'payment_autoresponse', $payment
        );
        $this->autoresponseUrl = $this->linker->path->getLink(
            'payment/callPage/' . $autoresponseSession
        );

        $baseUri = $this->linker->path->getBaseUri();

        $values['payment']['version'] = $oTpe->sVersion;
        $values['payment']['tpeVersion'] = $oTpe->sNumero;
        $values['payment']['date'] = $sDate;
        $values['payment']['amount'] = $sMontant;
        $values['payment']['currency'] = $sDevise;
        $values['payment']['reference'] = $sReference;
        $values['payment']['smac'] = $sMAC;
        $values['payment']['url_ko'] = $oTpe->sUrlKO;
        $values['payment']['url_ok'] = $oTpe->sUrlOK;
        $values['payment']['lang'] = $oTpe->sLangue;
        $values['payment']['companyCode'] = $oTpe->sCodeSociete;
        $values['payment']['freeText'] = HtmlEncode($sTexteLibre);
        $values['payment']['email'] = $sEmail;

        $rendered = $this->render( 'show', $values, false, false );
        echo $rendered;
        return true;
    }

    public function payment_execute( $payment, $session ) {
        echo 'OK';
    }

    public function payment_getTicket( $payment ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

}
