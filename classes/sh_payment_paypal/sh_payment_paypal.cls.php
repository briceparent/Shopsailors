<?php

/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Shopsailors 2012
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if( !defined( 'SH_MARKER' ) ) {
    header( 'location: directCallForbidden.php' );
}

// as the banks class is in the payment class, we load it to be able to extend sh_banks
sh_linker::getInstance()->payment;

class sh_payment_paypal extends sh_banks {

    const CLASS_VERSION = '1.1.13.01.30';

    protected $lastErrorId = 0;
    protected $needsManagementPage = true;

    const DEV_NAME = 'Paypal / Shopsailors';
    const DEV_WEBSITE = 'http://wiki.websailors.fr';
    const DEV_EMAIL = 'brice.parent@websailors.fr';

    protected $bank_code = 100010; // It actually ins't a bank code, but as it is made with 6 digits, it may not leed to errors
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
    public $callWithoutId = array( );
    public $callWithId = array( 'payment_is_prepared' );
    public $billingData = '';
    public $cartContents = array( );

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            if( version_compare( $installedVersion, '1.1.12.12.14' ) < 0 ) {
                $this->helper->addClassesSharedMethods( 'sh_payment', 'banks', __CLASS__ );
                $this->helper->createDir( SH_SITE_FOLDER . __CLASS__ );
            }
            if( version_compare( $installedVersion, '1.1.13.01.30' ) < 0 ) {
                mkdir( SH_SITE_FOLDER . __CLASS__ );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }

        $this->active = $this->getParam( 'active', false );
    }

    public function cron_job( $type ) {
        sh_cache::disable();
        $start = time();
        if( $type == sh_cron::JOB_HOUR ) {
            // We check if there are any pending payment review
            echo 'Paypal : Looking for reviewed payments... ';
            if(date('H') % 6 == 0 || isset($_GET['force_this_paypal'])){
                $reviews = $this->getParam( 'payment_reviews_4', array( ) );
                foreach( $reviews as $id => $transactionID ) {
                    echo 'We check the payment #'.$id.' for more than four times<br />';
                    $this->removeParam( 'payment_reviews>' . $id );
                    $this->writeParams();
                    $this->checkPending_and_logPayment( $id, $transactionID, 4 );
                }
            }
            if(date('H') % 2 == 1 || isset($_GET['force_this_paypal'])){
                $reviews = $this->getParam( 'payment_reviews_3', array( ) );
                foreach( $reviews as $id => $transactionID ) {
                    echo 'We check the payment #'.$id.' for the fourth time<br />';
                    $this->removeParam( 'payment_reviews>' . $id );
                    $this->writeParams();
                    $this->checkPending_and_logPayment( $id, $transactionID, 3 );
                }
            }
            if(date('H') % 2 == 0 || isset($_GET['force_this_paypal'])){
                $reviews = $this->getParam( 'payment_reviews_2', array( ) );
                foreach( $reviews as $id => $transactionID ) {
                    echo 'We check the payment #'.$id.' for the third time<br />';
                    $this->removeParam( 'payment_reviews>' . $id );
                    $this->writeParams();
                    $this->checkPending_and_logPayment( $id, $transactionID, 2 );
                }
            }
            $reviews = $this->getParam( 'payment_reviews_1', array( ) );
            foreach( $reviews as $id => $transactionID ) {
                echo 'We check the payment #'.$id.' for the second time<br />';
                $this->removeParam( 'payment_reviews>' . $id );
                $this->writeParams();
                $this->checkPending_and_logPayment( $id, $transactionID, 1 );
            }
            echo 'Done!<br />';
        }
        return true;
    }

    public function payment_setBillingData( $payment, $billingData ) {
        $this->billingData[ $payment ] = $billingData;
    }

    public function payment_setCartContents( $payment, $cartContents ) {
        $this->cartContents[ $payment ] = $cartContents;
    }

    public function bank_getLogo() {
        return $this->getSinglePath() . 'logo.png';
    }

    public function externallySetPhoneNumber( $phone ) {
        $_SESSION[ __CLASS__ ][ 'force_phone' ] = $phone;
    }

    public function manage( $message = '' ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( !empty( $message ) ) {
            $values[ 'message' ][ 'text' ] = $message;
        }
        if( $this->formSubmitted( 'bank_manager' ) ) {
            // State
            $this->setParam( 'active', isset( $_POST[ 'active' ] ) );
            $this->setParam( 'api_username', $_POST[ 'api_username' ] );
            $this->setParam( 'api_password', $_POST[ 'api_password' ] );
            $this->setParam( 'api_signature', $_POST[ 'api_signature' ] );
            // Description
            $description = $this->getParam( 'description', 0 );
            $description = $this->setI18n( $description, $_POST[ 'description' ] );
            $description = $this->setParam( 'description', $description );
            // Extra text added on the generated bills
            $extraTextForBill = $this->getParam( 'extraTextForBill', 0 );
            $extraTextForBill = $this->setI18n( $extraTextForBill, $_POST[ 'extraTextForBill' ] );
            $extraTextForBill = $this->setParam( 'extraTextForBill', $extraTextForBill );
            
            $this->setParam( 'sandbox', isset( $_POST[ 'sandbox' ] ) );
            // Saving
            $this->writeParams();
        }
        if( $this->getParam( 'active', false ) ) {
            $values[ 'bank' ][ 'state' ] = 'checked';
        }
        $values[ 'bank' ][ 'name' ] = $this->bank_getName();
        $values[ 'bank' ][ 'code' ] = $this->bank_getCode();
        $values[ 'bank' ][ 'logo' ] = $this->bank_getLogo();
        $values[ 'bank' ][ 'api_username' ] = $this->getParam( 'api_username', '' );
        $values[ 'bank' ][ 'api_password' ] = $this->getParam( 'api_password', '' );
        $values[ 'bank' ][ 'api_signature' ] = $this->getParam( 'api_signature', '' );

        $values[ 'bank' ][ 'description' ] = $this->getParam( 'description', 0 );
        $values[ 'bank' ][ 'extraTextForBill' ] = $this->getParam( 'extraTextForBill', 0 );
        
        if( $this->getParam( 'sandbox', true ) ) {
            $values[ 'bank' ][ 'sandbox' ] = 'checked';
        }
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
        return $this->getI18n( 'paypal_payment_name' );
    }

    public function payment_setPrice( $payment, $price, $decimalPrice ) {
        $this->debug( __FUNCTION__ . "($payment, $price, $decimalPrice)", 2, __LINE__ );
        if( $price > 0 ) {
            $this->price[ $payment ] = $decimalPrice / 100;
            return true;
        }
        return $this->setError( self::ERROR_NEGATIVE_PRICES_FORBIDDEN );
    }

    public function payment_setCurrency( $payment, $currency ) {
        $this->debug( __FUNCTION__ . "($payment,$currency)", 2, __LINE__ );
        $this->currency[ $payment ] = $this->getI18n( 'code_' . $currency );
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

    public function payment_setCustomerShippingDatas( $payment, $shipToName, $shipToStreet1, $shipToStreet2,
                                                      $shipToCity, $shipToZIP ) {
        $this->shipTo[ $payment ][ 'name' ] = $shipToName;
        $this->shipTo[ $payment ][ 'street1' ] = $shipToStreet1;
        $this->shipTo[ $payment ][ 'street2' ] = $shipToStreet2;
        $this->shipTo[ $payment ][ 'city' ] = $shipToCity;
        $this->shipTo[ $payment ][ 'zip' ] = $shipToZIP;
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
        $this->linker->params->set( $paramsFile, $payment . '>shipTo', $this->shipTo[ $payment ] );
        $this->linker->params->set( $paramsFile, $payment . '>billingData', $this->billingData[ $payment ] );
        $this->linker->params->set( $paramsFile, $payment . '>cartContents', $this->cartContents[ $payment ] );

        $this->linker->params->write( $paramsFile );

        $data = $this->linker->params->get( $paramsFile, $payment );


        $values[ 'datas' ][ 'price' ] = $this->price[ $payment ];
        $values[ 'datas' ][ 'merchant_id' ] = $this->merchant_id[ $payment ];
        $values[ 'bank' ][ 'name' ] = $this->bank_getName();
        $values[ 'bank' ][ 'logo' ] = $this->bank_getLogo();
        $description = $this->getParam( 'description', 0 );
        $values[ 'payment' ][ 'description' ] = $this->getI18n( $description );

        $paymentSession = $this->linker->payment->setCallPage(
            0, $this->bank_code, 'payNow', $payment
        );
        $values[ 'payment' ][ 'link' ] = $this->linker->path->getLink(
            'payment/callPage/' . $paymentSession
        );
        $this->linker->params->set( $paramsFile, $payment . '>session', $this->failurePage[ $payment ] );
        $values[ 'i18n' ] = 'sh_payment';
        return $this->render( 'show', $values, false, false );
    }

    public function payment_getTicket( $payment ) {
        $this->debug( __FUNCTION__ . "($payment)", 2, __LINE__ );
    }

    public function get_pending_payments_count() {
        $paramsFile = SH_SITE_FOLDER . __CLASS__ . '/pending_payments.params.php';
        $this->linker->params->addElement( $paramsFile, true );
        $payments = $this->linker->params->get( $paramsFile, 'list', array( ) );
        return count( $payments );
    }

    public function get_pending_payments() {
        $paramsFile = SH_SITE_FOLDER . __CLASS__ . '/pending_payments.params.php';
        $this->linker->params->addElement( $paramsFile, true );
        $payments = $this->linker->params->get( $paramsFile, 'list', array( ) );
        return $payments;
    }

    public function payment_is_prepared( $id = null ) {
        if( $id == null ) {
            $id = ( int ) $this->linker->path->page[ 'id' ];
        }

        $paramsFile = SH_SITE_FOLDER . __CLASS__ . '/payments_prepared.params.php';
        $this->linker->params->addElement( $paramsFile, true );
        $datas = $this->linker->params->get( $paramsFile, $id, array( ) );

        $token = $_GET[ "token" ];
        $payerid = $_GET[ "PayerID" ];

        $SandboxFlag = $this->getParam( 'sandbox', true );
        if( $SandboxFlag == true ) {
            $this->API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
            $PAYPAL_URL = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=";
        } else {
            $this->API_Endpoint = "https://api-3t.paypal.com/nvp";
            $PAYPAL_URL = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
        }

        $this->API_UserName = $this->getParam( 'api_username', '' );
        $this->API_Password = $this->getParam( 'api_password', '' );
        $this->API_Signature = $this->getParam( 'api_signature', '' );
        $this->API_sBNCode = "PP-ECWizard";
        $this->API_Version = "93";

        $USE_PROXY = false;
        $PROXY_HOST = '127.0.0.1';
        $PROXY_PORT = '808';

        $nvpstr = '&TOKEN=' . urlencode( $token ) .
            '&PAYERID=' . urlencode( $payerid ) .
            '&PAYMENTACTION=' . urlencode( "sale" ) .
            '&AMT=' . urlencode( $datas[ 'price' ] ) .
            '&CURRENCYCODE=' . urlencode( $datas[ 'currency' ] );

        $resArray = $this->paypal_hash_call( "DoExpressCheckoutPayment", $nvpstr );

        $ack = strtoupper( $resArray[ "ACK" ] );

        if( "SUCCESS" == strtoupper( $ack ) || "SUCCESSWITHWARNING" == strtoupper( $ack ) ) {
            $this->linker->html->setTitle( 'Paiement effectué avec succès' );
            $this->linker->html->insert( '<p>Identifiant de la transaction : ' . $resArray[ "TRANSACTIONID" ] . '</p>' );

            if( 'Completed' == $resArray[ "PAYMENTSTATUS" ] ) {
                $this->linker->html->insert( '<p>Votre commande sera traitée dans les plus brefs délais.</p>' );
                $this->linker->html->insert( '<p>Nous vous remercions pour votre confiance.</p>' );
            } elseif( 'Pending' == $resArray[ "PAYMENTSTATUS" ] ) {
                $this->linker->html->insert( '<p style="margin-top:20px;">Bien que la transaction ait été effectuée avec succès, le paiement est toujours en attente.<br /><br />
                    La commande sera prise en compte dès lors que Paypal aura confirmé le paiement.<br />Pour cela, Paypal ou votre
                    banque pourraient avoir à vous contacter.</p>' );
                $this->linker->html->insert( '<p style="margin-top:20px;">Nous vous remercions pour votre confiance et pour votre compréhension.</p>' );
            }
            
            list($class, $method, $id) = explode( '/', $datas['success'] );
            if( $this->linker->method_exists( $class, $method ) ) {
                $this->linker->$class->$method( $id, $this->bank_code );
            }

            $transactionID = urlencode( $resArray[ "TRANSACTIONID" ] );
            $this->checkPending_and_logPayment( $id, $transactionID );
        } else {

            $log = "<?php \n";
            $log .= "-------------------------------------------\n";
            $log .= 'Date : ' . date( 'd/m/Y H:i:s' ) . "\n";
            $log .= 'ERROR ! ' . "\n";
            $log .= 'Details : ' . print_r( $resArray, true ) . "\n";
            $log .= "-------------------------------------------\n";
            $log .= "?>\n";

            $logFile = SH_SITE_FOLDER . __CLASS__ . '/payments_log_' . date( 'Y-m-d' ) . '.php';
            E_FOLDER . __CLASS__ . '/payments_log_' . date( 'Y-m-d' ) . '.php';

            $this->helper->writeInFile( $logFile, $log, 2 );
            
            list($class, $method, $id) = explode( '/', $datas['unauthorized'] );
            if( $this->linker->method_exists( $class, $method ) ) {
                return $this->linker->$class->$method( $id, $this->bank_code );
            }
        }
    }

    protected function checkPending_and_logPayment( $id, $transactionID, $numberOfTests = 0 ) {
        $this->API_UserName = $this->getParam( 'api_username', '' );
        $this->API_Password = $this->getParam( 'api_password', '' );
        $this->API_Signature = $this->getParam( 'api_signature', '' );
        $this->API_sBNCode = "PP-ECWizard";
        $this->API_Version = "93";

        $USE_PROXY = false;
        $PROXY_HOST = '127.0.0.1';
        $PROXY_PORT = '808';
        
        $SandboxFlag = $this->getParam( 'sandbox', true );
        if( $SandboxFlag == true ) {
            $this->API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
            $PAYPAL_URL = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=";
        } else {
            $this->API_Endpoint = "https://api-3t.paypal.com/nvp";
            $PAYPAL_URL = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
        }
        
        $nvpstr = "&TRANSACTIONID=" . $transactionID;

        $resArray = $this->paypal_hash_call( "GetTransactionDetails", $nvpstr );
        $ack = strtoupper( $resArray[ "ACK" ] );

        if( "SUCCESS" == $ack || "SUCCESSWITHWARNING" == $ack ) {
            
            $paramsFile = SH_SITE_FOLDER . __CLASS__ . '/payments_prepared.params.php';
            $this->linker->params->addElement( $paramsFile, true );
            $datas = $this->linker->params->get( $paramsFile, $id, array( ) );

            $log = "<?php \n";
            $log .= "-------------------------------------------\n";
            $log .= 'Date : ' . date( 'd/m/Y H:i:s' ) . "\n";
            $log .= 'ACK : ' . $resArray[ 'ACK' ] . "\n";
            $log .= 'TRANSACTIONID : ' . $resArray[ 'TRANSACTIONID' ] . "\n";
            $log .= 'RECEIPTID : ' . $resArray[ 'RECEIPTID' ] . "\n";
            $log .= 'PAYMENTSTATUS : ' . $resArray[ 'PAYMENTSTATUS' ] . "\n";
            $log .= 'PENDINGREASON : ' . $resArray[ 'PENDINGREASON' ] . "\n";
            $log .= 'RECEIVEREMAIL : ' . $resArray[ 'RECEIVEREMAIL' ] . "\n";
            $log .= 'RECEIVERID : ' . $resArray[ 'RECEIVERID' ] . "\n";
            $log .= 'EMAIL : ' . $resArray[ 'EMAIL' ] . "\n";
            $log .= 'PAYERID : ' . $resArray[ 'PAYERID' ] . "\n";
            $log .= 'PAYERSTATUS : ' . $resArray[ 'PAYERSTATUS' ] . "\n";
            $log .= 'COUNTRYCODE : ' . $resArray[ 'COUNTRYCODE' ] . "\n";
            $log .= 'ADDRESSOWNER : ' . $resArray[ 'ADDRESSOWNER' ] . "\n";
            $log .= 'TIMESTAMP : ' . $resArray[ 'TIMESTAMP' ] . "\n";
            $log .= 'CORRELATIONID : ' . $resArray[ 'CORRELATIONID' ] . "\n";
            $log .= 'VERSION : ' . $resArray[ 'VERSION' ] . "\n";
            $log .= 'BUILD : ' . $resArray[ 'BUILD' ] . "\n";
            $log .= 'FIRSTNAME : ' . $resArray[ 'FIRSTNAME' ] . "\n";
            $log .= 'LASTNAME : ' . $resArray[ 'LASTNAME' ] . "\n";
            $log .= 'TRANSACTIONTYPE : ' . $resArray[ 'TRANSACTIONTYPE' ] . "\n";
            $log .= 'PAYMENTTYPE : ' . $resArray[ 'PAYMENTTYPE' ] . "\n";
            $log .= 'ORDERTIME : ' . $resArray[ 'ORDERTIME' ] . "\n";
            $log .= 'AMT : ' . $resArray[ 'AMT' ] . "\n";
            $log .= 'FEEAMT : ' . $resArray[ 'FEEAMT' ] . "\n";
            $log .= 'CURRENCYCODE : ' . $resArray[ 'CURRENCYCODE' ] . "\n";
            $log .= 'REASONCODE : ' . $resArray[ 'REASONCODE' ] . "\n";
            $log .= 'PROTECTIONELIGIBILITY : ' . $resArray[ 'PROTECTIONELIGIBILITY' ] . "\n";
            $log .= 'PROTECTIONELIGIBILITYTYPE : ' . $resArray[ 'PROTECTIONELIGIBILITYTYPE' ] . "\n";
            $log .= 'L_QTY0 : ' . $resArray[ 'L_QTY0' ] . "\n";
            $log .= "-------------------------------------------\n";
            $log .= "?>\n";

            $logFile = SH_SITE_FOLDER . __CLASS__ . '/payments_log_' . date( 'Y-m-d' ) . '.php';

            $this->helper->writeInFile( $logFile, $log, 2 );
            
            if( 'Completed' == $resArray[ "PAYMENTSTATUS" ] ) {
                // The command is validated, so we should mark the command as complete and send the bills
                list($class, $method, $id) = explode( '/', $datas['validated'] );
                if( $this->linker->method_exists( $class, $method ) ) {
                    $this->linker->$class->$method( $id, $this->bank_code );
                }
            } elseif( 'Pending' == $resArray[ "PAYMENTSTATUS" ] ) {
                // We should try later and check if the command is complete
                $numberOfTests++;
                $this->setParam( 'payment_reviews_'.$numberOfTests.'>' . $id, $transactionID );
                $this->writeParams();
            }
            return true;
            
        }

        $log = "<?php \n";
        $log .= "-------------------------------------------\n";
        $log .= 'Date : ' . date( 'd/m/Y H:i:s' ) . "\n";
        $log .= 'ERROR ! ' . "\n";
        $log .= 'Details : ' . print_r( $resArray, true ) . "\n";
        $log .= "-------------------------------------------\n";
        $log .= "?>\n";

        $logFile = SH_SITE_FOLDER . __CLASS__ . '/payments_log_' . date( 'Y-m-d' ) . '.php';

        $this->helper->writeInFile( $logFile, $log, 2 );

        list($class, $method, $id) = explode( '/', $datas['unauthorized'] );
        if( $this->linker->method_exists( $class, $method ) ) {
            return $this->linker->$class->$method( $id, $this->bank_code );
        }
        return false;
    }

    public function payNow( $payment, $id ) {
        $paramsFile = SH_SITE_FOLDER . __CLASS__ . '/payments_prepared.params.php';
        $this->linker->params->addElement( $paramsFile, true );
        $datas = $this->linker->params->get( $paramsFile, $payment, array( ) );

        $SandboxFlag = $this->getParam( 'sandbox', true );
        if( $SandboxFlag == true ) {
            $this->API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
            $PAYPAL_URL = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=";
        } else {
            $this->API_Endpoint = "https://api-3t.paypal.com/nvp";
            $PAYPAL_URL = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
        }
        $this->API_UserName = $this->getParam( 'api_username', '' );
        $this->API_Password = $this->getParam( 'api_password', '' );
        $this->API_Signature = $this->getParam( 'api_signature', '' );
        $this->API_sBNCode = "PP-ECWizard";
        $this->API_Version = "93";

        $USE_PROXY = false;
        $PROXY_HOST = '127.0.0.1';
        $PROXY_PORT = '808';

        $nvpstr = "&PAYMENTREQUEST_0_AMT=" . urlencode( $datas[ 'price' ] );
        $nvpstr = $nvpstr . "&PAYMENTREQUEST_0_PAYMENTACTION=Sale";
        $nvpstr = $nvpstr . "&RETURNURL=" . urlencode(
                $this->linker->path->getBaseUri() . '/' . __CLASS__ . '/payment_is_prepared/' . $payment . '.php?id=' . $id
        );
        $nvpstr = $nvpstr . "&CANCELURL=" . urlencode( $this->linker->path->getBaseUri() . $this->linker->path->getLink( $datas[ 'failure' ] ) );
        $nvpstr = $nvpstr . "&PAYMENTREQUEST_0_CURRENCYCODE=" . urlencode( $datas[ 'currency' ] );
        $nvpstr = $nvpstr . "&ADDROVERRIDE=1";
        $nvpstr = $nvpstr . "&NOSHIPPING=1";
        $nvpstr = $nvpstr . "&REQCONFIRMSHIPPING=0";
        $nvpstr = $nvpstr . "&EMAIL=" . urlencode( 'brice@websailors.fr' );
        $nvpstr = $nvpstr . "&PAYMENTREQUEST_0_SHIPTONAME =" . urlencode( 'Brice PARENT' );
        $nvpstr = $nvpstr . "&PAYMENTREQUEST_0_DESC =" . urlencode( 'Paiement du contenu de votre panier' );
        $nvpstr = $nvpstr . "&ALLOWNOTE=0";
        $nvpstr = $nvpstr . "&LOCALECODE=FR";
        $nvpstr = $nvpstr . "&LANDINGPAGE=Billing";

        $nvpstr = $nvpstr . "&PAYMENTREQUEST_0_ITEMAMT=" . urlencode( $datas[ 'price' ] );
        $prodCpt = 0;
        foreach( $datas[ 'cartContents' ] as $product ) {
            $name = str_replace( '<br />', "\n", $product[ 'name' ] );
            $desc = str_replace( '<br />', "\n", $product[ 'shortDescription' ] );
            $nvpstr = $nvpstr . "&L_PAYMENTREQUEST_0_NAME" . $prodCpt . "=" . urlencode( $name );
            $nvpstr = $nvpstr . "&L_PAYMENTREQUEST_0_DESC" . $prodCpt . "=" . urlencode( $desc );
            $nvpstr = $nvpstr . "&L_PAYMENTREQUEST_0_AMT" . $prodCpt . "=" . urlencode( $product[ 'priceOnly' ] );
            $nvpstr = $nvpstr . "&L_PAYMENTREQUEST_0_QTY" . $prodCpt . "=" . urlencode( $product[ 'quantity' ] );
            $prodCpt++;
        }

        $_SESSION[ "currencyCodeType" ] = $currencyCodeType;
        $_SESSION[ "PaymentType" ] = $paymentType;
        $resArray = $this->paypal_hash_call( "SetExpressCheckout", $nvpstr );
        echo '<div><span class="bold">$resArray : </span>' . nl2br( str_replace( ' ', '&#160;',
                                                                            htmlentities( print_r( $resArray, true ) ) ) ) . '</div>';

        $ack = strtoupper( $resArray[ "ACK" ] );
        if( $ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING" ) {
            $token = urldecode( $resArray[ "TOKEN" ] );
            $_SESSION[ 'TOKEN' ] = $token;
            
            $SandboxFlag = $this->getParam( 'sandbox', true );
            if( $SandboxFlag == true ) {
                $paypal_domain = "https://www.sandbox.paypal.com";
            } else {
                $paypal_domain = "https://www.paypal.com";
            }
            header( 'location: '.$paypal_domain.'/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=' . $token );
            return true;
        }
        die( 'ERROR CONTACTING PAYPAL' );
    }

    /**
      '-------------------------------------------------------------------------------------------------------------------------------------------
     * hash_call: Function to perform the API call to PayPal using API signature
     * @methodName is name of API  method.
     * @nvpStr is nvp string.
     * returns an associtive array containing the response from the server.
      '-------------------------------------------------------------------------------------------------------------------------------------------
     */
    protected function paypal_hash_call( $methodName, $nvpStr ) {
        //declaring of global variables
        $API_UserName = $this->API_UserName;
        $API_Password = $this->API_Password;
        $API_Signature = trim( $this->API_Signature );
        $API_Version = $this->API_Version;
        $USE_PROXY = false;
        $PROXY_HOST = '127.0.0.1';
        $PROXY_PORT = '808';
        $API_sBNCode = $this->API_sBNCode;

        //setting the curl parameters.
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $this->API_Endpoint );
        curl_setopt( $ch, CURLOPT_VERBOSE, 1 );

        //turning off the server and peer verification(TrustManager Concept).
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );

        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, 1 );

        //if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
        //Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php 
        if( $USE_PROXY ) {
            curl_setopt( $ch, CURLOPT_PROXY, $PROXY_HOST . ":" . $PROXY_PORT );
        }

        //NVPRequest for submitting to server
        $nvpreq = "METHOD=" . urlencode( $methodName ) . "&VERSION=" . urlencode( $API_Version ) .
            "&PWD=" . urlencode( $API_Password ) . "&USER=" . urlencode( $API_UserName ) .
            "&SIGNATURE=" . urlencode( $API_Signature ) . $nvpStr . "&BUTTONSOURCE=" . urlencode( $API_sBNCode );

        //setting the nvpreq as POST FIELD to curl
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $nvpreq );

        //getting response from server
        $response = curl_exec( $ch );

        //convrting NVPResponse to an Associative Array
        $nvpResArray = $this->paypal_deformatNVP( $response );
        $nvpReqArray = $this->paypal_deformatNVP( $nvpreq );

        if( curl_errno( $ch ) ) {
            // moving to display page to display curl errors
            $_SESSION[ 'curl_error_no' ] = curl_errno( $ch );
            $_SESSION[ 'curl_error_msg' ] = curl_error( $ch );

            //Execute the Error handling module to display errors. 
        } else {
            //closing the curl
            curl_close( $ch );
        }

        return $nvpResArray;
    }

    protected function paypal_deformatNVP( $nvpstr ) {
        $intial = 0;
        $nvpArray = array( );

        while( strlen( $nvpstr ) ) {
            //postion of Key
            $keypos = strpos( $nvpstr, '=' );
            //position of value
            $valuepos = strpos( $nvpstr, '&' ) ? strpos( $nvpstr, '&' ) : strlen( $nvpstr );

            /* getting the Key and Value values and storing in a Associative Array */
            $keyval = substr( $nvpstr, $intial, $keypos );
            $valval = substr( $nvpstr, $keypos + 1, $valuepos - $keypos - 1 );
            //decoding the respose
            $nvpArray[ urldecode( $keyval ) ] = urldecode( $valval );
            $nvpstr = substr( $nvpstr, $valuepos + 1, strlen( $nvpstr ) );
        }
        return $nvpArray;
    }

    function confirmation() {
        $SandboxFlag = $this->getParam( 'sandbox', true );
        $API_UserName = $this->getParam( 'api_username', '' );
        $API_Password = $this->getParam( 'api_password', '' );
        $API_Signature = $this->getParam( 'api_signature', '' );
        require_once ("paypalfunctions.php");

        if( $PaymentOption == "PayPal" ) {
            /*
              '------------------------------------
              ' The paymentAmount is the total value of
              ' the shopping cart, that was set
              ' earlier in a session variable
              ' by the shopping cart page
              '------------------------------------
             */

            $finalPaymentAmount = $_SESSION[ "Payment_Amount" ];

            /*
              '------------------------------------
              ' Calls the DoExpressCheckoutPayment API call
              '
              ' The ConfirmPayment function is defined in the file PayPalFunctions.jsp,
              ' that is included at the top of this file.
              '-------------------------------------------------
             */

            $resArray = ConfirmPayment( $finalPaymentAmount );
            $ack = strtoupper( $resArray[ "ACK" ] );
            if( $ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING" ) {
                /*
                  '********************************************************************************************************************
                  '
                  ' THE PARTNER SHOULD SAVE THE KEY TRANSACTION RELATED INFORMATION LIKE
                  '                    transactionId & orderTime
                  '  IN THEIR OWN  DATABASE
                  ' AND THE REST OF THE INFORMATION CAN BE USED TO UNDERSTAND THE STATUS OF THE PAYMENT
                  '
                  '********************************************************************************************************************
                 */

                $transactionId = $resArray[ "PAYMENTINFO_0_TRANSACTIONID" ]; // ' Unique transaction ID of the payment. Note:  If the PaymentAction of the request was Authorization or Order, this value is your AuthorizationID for use with the Authorization & Capture APIs. 
                $transactionType = $resArray[ "PAYMENTINFO_0_TRANSACTIONTYPE" ]; //' The type of transaction Possible values: l  cart l  express-checkout 
                $paymentType = $resArray[ "PAYMENTINFO_0_PAYMENTTYPE" ];  //' Indicates whether the payment is instant or delayed. Possible values: l  none l  echeck l  instant 
                $orderTime = $resArray[ "PAYMENTINFO_0_ORDERTIME" ];  //' Time/date stamp of payment
                $amt = $resArray[ "PAYMENTINFO_0_AMT" ];  //' The final amount charged, including any shipping and taxes from your Merchant Profile.
                $currencyCode = $resArray[ "PAYMENTINFO_0_CURRENCYCODE" ];  //' A three-character currency code for one of the currencies listed in PayPay-Supported Transactional Currencies. Default: USD. 
                $feeAmt = $resArray[ "PAYMENTINFO_0_FEEAMT" ];  //' PayPal fee amount charged for the transaction
                $settleAmt = $resArray[ "PAYMENTINFO_0_SETTLEAMT" ];  //' Amount deposited in your PayPal account after a currency conversion.
                $taxAmt = $resArray[ "PAYMENTINFO_0_TAXAMT" ];  //' Tax charged on the transaction.
                $exchangeRate = $resArray[ "PAYMENTINFO_0_EXCHANGERATE" ];  //' Exchange rate if a currency conversion occurred. Relevant only if your are billing in their non-primary currency. If the customer chooses to pay with a currency other than the non-primary currency, the conversion occurs in the customer's account.

                /*
                  ' Status of the payment:
                  'Completed: The payment has been completed, and the funds have been added successfully to your account balance.
                  'Pending: The payment is pending. See the PendingReason element for more information.
                 */

                $paymentStatus = $resArray[ "PAYMENTINFO_0_PAYMENTSTATUS" ];

                /*
                  'The reason the payment is pending:
                  '  none: No pending reason
                  '  address: The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set such that you want to manually accept or deny each of these payments. To change your preference, go to the Preferences section of your Profile.
                  '  echeck: The payment is pending because it was made by an eCheck that has not yet cleared.
                  '  intl: The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview.
                  '  multi-currency: You do not have a balance in the currency sent, and you do not have your Payment Receiving Preferences set to automatically convert and accept this payment. You must manually accept or deny this payment.
                  '  verify: The payment is pending because you are not yet verified. You must verify your account before you can accept this payment.
                  '  other: The payment is pending for a reason other than those listed above. For more information, contact PayPal customer service.
                 */

                $pendingReason = $resArray[ "PAYMENTINFO_0_PENDINGREASON" ];

                /*
                  'The reason for a reversal if TransactionType is reversal:
                  '  none: No reason code
                  '  chargeback: A reversal has occurred on this transaction due to a chargeback by your customer.
                  '  guarantee: A reversal has occurred on this transaction due to your customer triggering a money-back guarantee.
                  '  buyer-complaint: A reversal has occurred on this transaction due to a complaint about the transaction from your customer.
                  '  refund: A reversal has occurred on this transaction because you have given the customer a refund.
                  '  other: A reversal has occurred on this transaction due to a reason not listed above.
                 */

                $reasonCode = $resArray[ "PAYMENTINFO_0_REASONCODE" ];
            } else {
                //Display a user friendly Error on the page using any of the following error information returned by PayPal
                $ErrorCode = urldecode( $resArray[ "L_ERRORCODE0" ] );
                $ErrorShortMsg = urldecode( $resArray[ "L_SHORTMESSAGE0" ] );
                $ErrorLongMsg = urldecode( $resArray[ "L_LONGMESSAGE0" ] );
                $ErrorSeverityCode = urldecode( $resArray[ "L_SEVERITYCODE0" ] );

                echo "GetExpressCheckoutDetails API call failed. ";
                echo "Detailed Error Message: " . $ErrorLongMsg;
                echo "Short Error Message: " . $ErrorShortMsg;
                echo "Error Code: " . $ErrorCode;
                echo "Error Severity Code: " . $ErrorSeverityCode;
            }
        }
    }

    function review() {
        $token = "";
        if( isset( $_REQUEST[ 'token' ] ) ) {
            $token = $_REQUEST[ 'token' ];
        }

        if( $token != "" ) {

            $SandboxFlag = $this->getParam( 'sandbox', true );
            $API_UserName = $this->getParam( 'api_username', '' );
            $API_Password = $this->getParam( 'api_password', '' );
            $API_Signature = $this->getParam( 'api_signature', '' );
            require_once ("paypalfunctions.php");



            $resArray = GetShippingDetails( $token );
            $ack = strtoupper( $resArray[ "ACK" ] );
            if( $ack == "SUCCESS" || $ack == "SUCESSWITHWARNING" ) {
                /*
                  ' The information that is returned by the GetExpressCheckoutDetails call should be integrated by the partner into his Order Review
                  ' page
                 */
                $email = $resArray[ "EMAIL" ]; // ' Email address of payer.
                $payerId = $resArray[ "PAYERID" ]; // ' Unique PayPal customer account identification number.
                $payerStatus = $resArray[ "PAYERSTATUS" ]; // ' Status of payer. Character length and limitations: 10 single-byte alphabetic characters.
                $salutation = $resArray[ "SALUTATION" ]; // ' Payer's salutation.
                $firstName = $resArray[ "FIRSTNAME" ]; // ' Payer's first name.
                $middleName = $resArray[ "MIDDLENAME" ]; // ' Payer's middle name.
                $lastName = $resArray[ "LASTNAME" ]; // ' Payer's last name.
                $suffix = $resArray[ "SUFFIX" ]; // ' Payer's suffix.
                $cntryCode = $resArray[ "COUNTRYCODE" ]; // ' Payer's country of residence in the form of ISO standard 3166 two-character country codes.
                $business = $resArray[ "BUSINESS" ]; // ' Payer's business name.
                $shipToName = $resArray[ "PAYMENTREQUEST_0_SHIPTONAME" ]; // ' Person's name associated with this address.
                $shipToStreet = $resArray[ "PAYMENTREQUEST_0_SHIPTOSTREET" ]; // ' First street address.
                $shipToStreet2 = $resArray[ "PAYMENTREQUEST_0_SHIPTOSTREET2" ]; // ' Second street address.
                $shipToCity = $resArray[ "PAYMENTREQUEST_0_SHIPTOCITY" ]; // ' Name of city.
                $shipToState = $resArray[ "PAYMENTREQUEST_0_SHIPTOSTATE" ]; // ' State or province
                $shipToCntryCode = $resArray[ "PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE" ]; // ' Country code. 
                $shipToZip = $resArray[ "PAYMENTREQUEST_0_SHIPTOZIP" ]; // ' U.S. Zip code or other country-specific postal code.
                $addressStatus = $resArray[ "ADDRESSSTATUS" ]; // ' Status of street address on file with PayPal   
                $invoiceNumber = $resArray[ "INVNUM" ]; // ' Your own invoice or tracking number, as set by you in the element of the same name in SetExpressCheckout request .
                $phonNumber = $resArray[ "PHONENUM" ]; // ' Payer's contact telephone number. Note:  PayPal returns a contact telephone number only if your Merchant account profile settings require that the buyer enter one. 
            } else {
                //Display a user friendly Error on the page using any of the following error information returned by PayPal
                $ErrorCode = urldecode( $resArray[ "L_ERRORCODE0" ] );
                $ErrorShortMsg = urldecode( $resArray[ "L_SHORTMESSAGE0" ] );
                $ErrorLongMsg = urldecode( $resArray[ "L_LONGMESSAGE0" ] );
                $ErrorSeverityCode = urldecode( $resArray[ "L_SEVERITYCODE0" ] );

                echo "GetExpressCheckoutDetails API call failed. ";
                echo "Detailed Error Message: " . $ErrorLongMsg;
                echo "Short Error Message: " . $ErrorShortMsg;
                echo "Error Code: " . $ErrorCode;
                echo "Error Severity Code: " . $ErrorSeverityCode;
            }
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

