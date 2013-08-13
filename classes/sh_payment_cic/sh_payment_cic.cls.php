<?php

/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2012
 * @license http://www.cecill.info
 * @version See sh_payment_cic::CLASS_VERSION
 * @package Shopsailors Core Classes
 */
if( !defined( 'SH_MARKER' ) ) {
    header( 'location: directCallForbidden.php' );
}

$paymentObject = sh_linker::getInstance()->payment;

class sh_payment_cic extends sh_banks {

    const CLASS_VERSION = '1.1.12.12.12';

    protected $ready = false;
    protected $bank_code = 30066;
    protected $payments = array( );
    protected $paymentConfirmPagesParams = null;
    protected $paymentsLogParams = null;
    protected $price = false;
    protected $customer_mail = '';
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
    protected $lang = 'FR';
    
    const MODE_TEST = 'https://ssl.paiement.cic-banques.fr/test/paiement.cgi';
    const MODE_PROD = 'https://ssl.paiement.cic-banques.fr/paiement.cgi';

    public function GetClassConstants() {
        $oClass = new ReflectionClass( $this );
        return $oClass->getConstants();
    }

    public function construct() {
        
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {

            if( version_compare( $installedVersion, '1.1.11.05.10', '<' ) ) {
                if( !is_dir( SH_SITE_FOLDER . __CLASS__ ) ) {
                    mkdir( SH_SITE_FOLDER . __CLASS__ );
                }
            }
            if( version_compare( $installedVersion, '1.1.12.12.12' ) < 0 ) {
                $this->helper->addClassesSharedMethods( 'sh_payment', 'banks', __CLASS__ );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
        
        if(strlen($this->getParam('tpe', '')) == 7 && strlen($this->getParam('mac', '')) == 40){
            $this->ready = true;
            $this->active = $this->getParam( 'active', false );
        }
        $this->currency = $this->getParam('currency', self::CUR_EUR);
    }

    public function payment_setLang( $lang ) {
        if( !in_array( $lang, $this->languages ) ) {
            $lang = 'FR';
        }
        $this->lang = $lang;
    }

    public function manage( $message = '' ) {
        if($this->formSubmitted( 'bank_manager')){
            $this->setParam('currency', $_POST['currency']);
            $this->setParam('mode', $_POST['mode']);
            $this->setParam('tpe', $_POST['tpe']);
            $this->setParam('mac', $_POST['mac']);
            $this->setParam('societe', $_POST['societe']);
            $this->setParam('langue', $_POST['langue']);
            $this->setParam('version', $_POST['version']);
            if(strlen($_POST['tpe']) == 7 && strlen($_POST['mac']) == 40){
                $this->setParam( 'active', isset( $_POST[ 'active' ] ) );
            }else{
                $this->setParam( 'active', false );
                $this->linker->html->addMessage('Les champs TPE et Code MAC sont obligatoires.');
            }
            $this->writeParams();
        }
        
        if( $this->getParam( 'active', false ) ) {
            $values[ 'bank' ][ 'state' ] = 'checked';
        }
        $activeCurrency = $this->getParam('currency', self::CUR_EUR);
        foreach($this->currencies as $currency){
            $values['currencies'][$currency]['code'] = $currency;
            if($currency == $activeCurrency){
                $values['currencies'][$currency]['state'] = 'selected';
            }
        }
        $values['mode'][$this->getParam('mode', 'test')] = 'selected';
        $values['bank']['tpe'] = $this->getParam('tpe', '');
        $values['bank']['mac'] = $this->getParam('mac', '');
        $values['bank']['societe'] = $this->getParam('societe', '');
        $values['bank']['langue'] = $this->getParam('langue', '');
        $values['bank']['version'] = $this->getParam('version', '');
        $this->render('manage', $values);
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
            $this->price = substr($decimalPrice,0,-2).'.'.substr($decimalPrice,-2);
            return true;
        }
        return $this->setError( self::ERROR_NEGATIVE_PRICES_FORBIDDEN );
    }

    public function payment_setCurrency( $payment, $currency ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( $currency != $this->getParam('currency', self::CUR_EUR) ) {
            return $this->setError( self::ERROR_CUR_NOT_SUPPORTED );
        }
        $this->currency = $currency;
        return true;
    }

    public function payment_setCountry( $payment, $merchant_country ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( !in_array( $merchant_country, $this->countries ) ) {
            return $this->setError( self::ERROR_COUNTRY_NOT_SUPPORTED );
        }
        $this->merchant_country = $merchant_country;
    }
    
    public function payment_setCustomerMail( $payment, $mail ) {
        $this->debug( __FUNCTION__ . '(' . $payment . ', ' . $page . ')', 2, __LINE__ );
        $this->customer_mail = $mail;
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
        $this->linker->params->set( $this->paymentConfirmPagesParams, 'payments>' . $payment . '>unauthorizedPage', $page );
        $this->linker->params->write( $this->paymentConfirmPagesParams );
    }

    /**
     * This method is called by the user when he clicks "cancel" on the bank's server.
     * @return * The return of the method called
     */
    public function payment_failure( $payment ) {
        return true;
    }

    /**
     * This method is called by the user when he clicks "back to the shop (after payment success)" on the bank's server.
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
    
    protected function prepare_CMCIC_module(){
        include(dirname(__FILE__).'/CMCIC_Tpe.inc.php');
        define ("CMCIC_CLE", $this->getParam( 'mac', ''));
        define ("CMCIC_TPE", $this->getParam( 'tpe', ''));
        define ("CMCIC_VERSION", $this->getParam( 'version', ''));
        define ("CMCIC_SERVEUR", constant(strtoupper('MODE_'.$this->getParam( 'mode', ''))));
        define ("CMCIC_CODESOCIETE", "71af122290dd4a29a033");
    }

    public function payment_action( $payment ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
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
        define ("CMCIC_URLOK", $this->successUrl);
        define ("CMCIC_URLKO", $this->failureUrl);
        
        $this->prepare_CMCIC_module();
        $sLangue = $this->lang;
        $oTpe = new CMCIC_Tpe( $sLangue );
        $oHmac = new CMCIC_Hmac( $oTpe );
        
        $sReference = substr( md5( microtime() ), 0, 12 );
        $sMontant = $this->price;
        $sDevise = $this->getI18n('code_'.$this->currency);
        $sTexteLibre = 'Payment : '.$payment."\n".'Salt : '.md5(microtime().$payment);
        $sDate = date( "d/m/Y:H:i:s" );
        $sEmail = $this->customer_mail;

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
            $sTexteLibre, $oTpe->sVersion, $oTpe->sLangue, $oTpe->sCodeSociete, $sEmail, 
            '', '', '', '', '','', '', '', '', '' 
        );

        // MAC computation
        $sMAC = $oHmac->computeHmac( $PHP1_FIELDS );

        $baseUri = $this->linker->path->getBaseUri();

        if($this->getParam('mode', 'test') == 'test'){
            $values['payment']['url'] = self::MODE_TEST;
        }else{
            $values['payment']['url'] = self::MODE_PROD;
        }
        
        $values['payment']['version'] = $oTpe->sVersion;
        $values['payment']['tpe'] = $oTpe->sNumero;
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

        $rendered = $this->render( dirname( __FILE__ ) . '/renderFiles/show.rf.xml', $values, false, false );
        return $rendered;    
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
