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

class sh_payment_cheque extends sh_banks {

    const CLASS_VERSION = '1.1.11.12.06';

    protected $lastErrorId = 0;
    protected $needsManagementPage = true;
    const DEV_NAME = 'Cheque / Shopsailors';
    const DEV_WEBSITE = 'http://wiki.websailors.fr';
    const DEV_EMAIL = 'brice.parent@websailors.fr';
    protected $bank_code = 100000; // It actually ins't a bank code, but as it is made with 6 digits, it may not leed to errors
    protected $curTrans = array( );
    protected $payments = array( );
    protected $merchant_id = '';
    protected $merchant_country = '';
    protected $currency = array( );
    protected $price = array( );
    protected $allowedCurrencies = array( );
    protected $successPage = array( );
    protected $failurePage = array( );
    protected $validatedPage = array( );
    protected $country = '';
    protected $ready = true;

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            if( version_compare( $installedVersion, '1.1.11.12.05' ) < 0 ) {
                $this->helper->addClassesSharedMethods( 'sh_payment', 'banks', __CLASS__ );
            }
            if( version_compare( $installedVersion, '1.1.11.12.06' ) < 0 ) {
                $this->helper->addClassesSharedMethods( 'sh_payment', 'require_manual_collection', __CLASS__ );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }

        $this->active = $this->getParam( 'active', false );
    }

    public function manage( $message = '' ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( !empty( $message ) ) {
            $values[ 'message' ][ 'text' ] = $message;
        }
        if( $this->formSubmitted( 'bank_manager' ) ) {
            // State
            $description = $this->setParam( 'active', isset( $_POST[ 'active' ] ) );
            // Description
            $description = $this->getParam( 'description', 0 );
            $description = $this->setI18n( $description, $_POST[ 'description' ] );
            $description = $this->setParam( 'description', $description );
            // Extra text added on the generated bills
            $extraTextForBill = $this->getParam( 'extraTextForBill', 0 );
            $extraTextForBill = $this->setI18n( $extraTextForBill, $_POST[ 'extraTextForBill' ] );
            $extraTextForBill = $this->setParam( 'extraTextForBill', $extraTextForBill );
            // Saving
            $description = $this->writeParams();
        }
        if( $this->getParam( 'active', false ) ) {
            $values[ 'bank' ][ 'state' ] = 'checked';
        }
        $values[ 'bank' ][ 'name' ] = $this->bank_getName();
        $values[ 'bank' ][ 'code' ] = $this->bank_getCode();
        $values[ 'bank' ][ 'logo' ] = $this->bank_getLogo();
        $values[ 'bank' ][ 'description' ] = $this->getParam( 'description', 0 );
        $values[ 'bank' ][ 'extraTextForBill' ] = $this->getParam( 'extraTextForBill', 0 );
        $this->render( 'manage', $values );
    }

    public function bank_getDescription() {
        $description = $this->getParam( 'description', 0 );
        if( $description > 0 ) {
            return $this->getI18n( $description );
        }
        return false;
    }

    public function bank_getName() {
        return $this->getI18n( 'cheque_payment_name' );
    }

    public function payment_setPrice( $payment, $price, $decimalPrice ) {
        $this->debug( __FUNCTION__ . "($payment, $price, $decimalPrice)", 2, __LINE__ );
        if( $price > 0 ) {
            $this->price[ $payment ] = $price;
            return true;
        }
        return $this->setError( self::ERROR_NEGATIVE_PRICES_FORBIDDEN );
    }

    public function payment_setCurrency( $payment, $currency ) {
        $this->debug( __FUNCTION__ . "($payment,$currency)", 2, __LINE__ );
        $this->currency[ $payment ] = $currency;
        return true;
    }

    public function payment_setCountry( $payment, $country ) {
        $this->debug( __FUNCTION__ . "($payment,$country)", 2, __LINE__ );
        // This doesn't matter...
    }

    public function payment_setSuccessPage( $payment, $page ) {
        $this->debug( __FUNCTION__ . "($payment,$page)", 2, __LINE__ );
        $this->successPage[ $payment ] = $page;
    }

    public function payment_setFailurePage( $payment, $page ) {
        $this->debug( __FUNCTION__ . "($payment,$page)", 2, __LINE__ );
        $this->failurePage[ $payment ] = $page;
    }

    public function payment_setValidatedPage( $payment, $page ) {
        $this->debug( __FUNCTION__ . "($payment,$page)", 2, __LINE__ );
        $this->validatedPage[ $payment ] = $page;
    }

    public function payment_setUnauthorizedPage( $payment, $page ) {
        $this->debug( __FUNCTION__ . "($payment,$page)", 2, __LINE__ );
        $this->unauthorizedPage[ $payment ] = $page;
    }

    public function payment_action( $payment ) {
        $this->debug( __FUNCTION__ . "($payment)", 2, __LINE__ );

        $paramsFile = SH_SITE_FOLDER . __CLASS__ . '/payments_prepared.params.php';
        $this->linker->params->addElement( $paramsFile, true );
        $this->linker->params->set( $paramsFile, $payment . '>price', $this->price[ $payment ] );
        $this->linker->params->set( $paramsFile, $payment . '>currency', $this->currency[ $payment ] );
        $this->linker->params->set( $paramsFile, $payment . '>date', date( 'Y-m-d' ) );
        $this->linker->params->set( $paramsFile, $payment . '>time', date( 'H:i:s' ) );
        $this->linker->params->set( $paramsFile, $payment . '>paid', false );
        $this->linker->params->set( $paramsFile, $payment . '>success', $this->successPage[ $payment ] );
        $this->linker->params->set( $paramsFile, $payment . '>failure', $this->failurePage[ $payment ] );
        $this->linker->params->set( $paramsFile, $payment . '>validated', $this->validatedPage[ $payment ] );
        $this->linker->params->set( $paramsFile, $payment . '>unauthorized', $this->unauthorizedPage[ $payment ] );
        $this->linker->params->write( $paramsFile );
        $values[ 'datas' ][ 'price' ] = $this->price[ $payment ];
        $values[ 'datas' ][ 'merchant_id' ] = $this->merchant_id[ $payment ];
        $values[ 'bank' ][ 'name' ] = $this->bank_getName();
        $values[ 'bank' ][ 'logo' ] = $this->bank_getLogo();
        $description = $this->getParam( 'description', 0 );
        $values[ 'payment' ][ 'description' ] = $this->getI18n( $description );

        $paymentSession = $this->linker->payment->setCallPage(
            0, $this->bank_code, 'paymentByCheque', $payment
        );
        $values[ 'payment' ][ 'link' ] = $this->linker->path->getLink(
            'payment/callPage/' . $paymentSession
        );
        $this->linker->params->set( $paramsFile, $payment . '>session', $this->failurePage[ $payment ] );
        $values[ 'i18n' ] = 'sh_payment';
        return $this->render( 'show', $values, false, false );
    }

    public function payment_manual_collector() {
        $paramsFile = SH_SITE_FOLDER . __CLASS__ . '/payments_pending.params.php';
        $this->linker->params->addElement( $paramsFile, true );
        $ret[ 'payments' ] = $this->linker->params->get( $paramsFile, '', array( ) );
        $ret[ 'className' ] = 'Chèque';
        return $ret;
    }

    public function payment_getTicket( $payment ) {
        $this->debug( __FUNCTION__ . "($payment)", 2, __LINE__ );
    }

    public function paymentByCheque( $id, $session ) {
        $this->debug( __FUNCTION__ . "($id,$session)", 2, __LINE__ );
        // We are paying by cheque
        // We move the datas from payment_prepare to payment_done
        $command = $id;
        $code = $id % 99999;
        $preparedFile = SH_SITE_FOLDER . __CLASS__ . '/payments_prepared.params.php';
        $this->linker->params->addElement( $preparedFile, true );
        $pendingFile = SH_SITE_FOLDER . __CLASS__ . '/payments_pending.params.php';
        $this->linker->params->addElement( $pendingFile, true );
        $payment = $this->linker->params->get( $preparedFile, $id, array( ) );



        $this->linker->params->set(
            $pendingFile, 'list>' . $code,
            array(
            'id' => $id, 'session' => $session, 'code' => $code, 'timestamp' => date( 'U' )
            )
        );



        // We call the success page, because there is no failure at this point
        $paymentMode = array(
            'name' => $this->bank_getName(),
            'description' => $this->bank_getDescription(),
            'extraTextForBill' => $this->getI18n( $this->getParam( 'extraTextForBill' ) )
        );

        list($class, $method, $id) = explode( '/', $payment[ 'success' ] );

        if( $this->linker->method_exists( $class, $method ) ) {
            $this->linker->$class->$method( $id, $paymentMode );
        } else {
            $this->debug( 'The method ' . "$class -> $method -> $id" . ' does not exist', 0, __LINE__ );
        }
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

}
