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

// as the banks class is in the payment class, we load it to be able to extend sh_banks
sh_linker::getInstance()->payment;

class sh_payment_phone extends sh_banks {

    const CLASS_VERSION = '1.1.11.12.06.2';

    protected $lastErrorId = 0;
    protected $needsManagementPage = true;
    const DEV_NAME = 'Phone / Shopsailors';
    const DEV_WEBSITE = 'http://wiki.websailors.fr';
    const DEV_EMAIL = 'brice.parent@websailors.fr';
    protected $bank_code = 100001; // It actually ins't a bank code, but as it is made with 6 digits, it may not leed to errors
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
    public $callWithoutId = array(
        'get_waiting_for_calls'
    );

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            if( version_compare( $installedVersion, '1.1.11.11.15' ) < 0 ) {
                $this->helper->addClassesSharedMethods( 'sh_payment', 'banks', __CLASS__ );
            }
            if( version_compare( $installedVersion, '1.1.11.12.06' ) < 0 ) {
                $this->helper->createDir( SH_SITE_FOLDER . __CLASS__ );
            }
            if( version_compare( $installedVersion, '1.1.11.12.06.2' ) < 0 ) {
                $this->helper->addClassesSharedMethods( 'sh_payment', 'require_manual_collection', __CLASS__ );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }

        $this->active = $this->getParam( 'active', false );
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
            $description = $this->setParam( 'active', isset( $_POST[ 'active' ] ) );
            // Description
            $description = $this->getParam( 'description', 0 );
            $description = $this->setI18n( $description, $_POST[ 'description' ] );
            $description = $this->setParam( 'description', $description );
            // Extra text added on the generated bills
            $extraTextForBill = $this->getParam( 'extraTextForBill', 0 );
            $extraTextForBill = $this->setI18n( $extraTextForBill, $_POST[ 'extraTextForBill' ] );
            $extraTextForBill = $this->setParam( 'extraTextForBill', $extraTextForBill );
            $extraTextForBill = $this->setParam( 'phone', $_POST[ 'phone' ] );
            $extraTextForBill = $this->setParam( 'email', $_POST[ 'email' ] );
            /* $mail_model = str_replace(
              array(
              $this->getI18n( 'mails_text_symbol_phone' ),
              $this->getI18n( 'mails_text_symbol_command' ),
              $this->getI18n( 'mails_text_symbol_code' ),
              ),
              array(
              '[PHONE]',
              '[COMMAND]',
              '[CODE]'
              ), trim( $_POST['mail_model'] )
              );
              $extraTextForBill = $this->setParam( 'mail_model', $mail_model );
             * 
             */
            // Saving
            $description = $this->writeParams();
        }
        if( $this->getParam( 'active', false ) ) {
            $values[ 'bank' ][ 'state' ] = 'checked';
        }
        $values[ 'bank' ][ 'name' ] = $this->bank_getName();
        $values[ 'bank' ][ 'code' ] = $this->bank_getCode();
        $values[ 'bank' ][ 'logo' ] = $this->bank_getLogo();
        $values[ 'bank' ][ 'phone' ] = $this->getParam( 'phone', '' );
        $values[ 'bank' ][ 'email' ] = $this->getParam( 'email', '' );
        $values[ 'bank' ][ 'mail_model' ] = $this->getParam( 'mail_model', '' );

        if( empty( $values[ 'bank' ][ 'mail_model' ] ) ) {
            $values[ 'bank' ][ 'mail_model' ] = $this->getI18n( 'mails_text_model' );
        }
        $values[ 'bank' ][ 'mail_model' ] = str_replace(
            array(
            '[PHONE]',
            '[COMMAND]',
            '[CODE]'
            ),
            array(
            $this->getI18n( 'mails_text_symbol_phone' ),
            $this->getI18n( 'mails_text_symbol_command' ),
            $this->getI18n( 'mails_text_symbol_code' ),
            ), $values[ 'bank' ][ 'mail_model' ]
        );
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
        return $this->getI18n( 'phone_payment_name' );
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
            0, $this->bank_code, 'paymentByPhone', $payment
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

    public function payment_manually_collected( $payment_id, $accepted ) {
        $paramsFile = SH_SITE_FOLDER . __CLASS__ . '/payments_prepared.params.php';
        $this->linker->params->addElement( $paramsFile, true );

        $prepared = $this->linker->params->get( $paramsFile, $payment_id );
        if( $prepared != 'done' ) {
            if( $accepted ) {
                list($class, $action, $id) = explode( '/', $prepared[ 'validated' ] );
                
                $this->linker->html->addMessage( 'Le paiement a été validé avec succès.<br />La facture sera envoyée dans
                    les prochaines minutes.',
                                                 false );
            } else {
                list($class, $action, $id) = explode( '/', $prepared[ 'unauthorized' ] );
                $this->linker->html->addMessage( 'Le paiement a été annulé avec succès.' );
            }
            
            $this->linker->$class->$action( $id, $this->bank_code );

            // We remove the payment from the list
            $this->linker->params->set( $paramsFile, $payment_id, 'done' );
            $this->linker->params->write( $paramsFile );
        }
        return true;
    }

    public function payment_manual_collector() {
        $pendingFile = SH_SITE_FOLDER . __CLASS__ . '/pending_payments.params.php';
        $this->linker->params->addElement( $pendingFile, true );
        $payments = $this->linker->params->get( $pendingFile, 'list', array( ) );

        if( !empty( $payments ) ) {
            $preparedFile = SH_SITE_FOLDER . __CLASS__ . '/payments_prepared.params.php';
            $this->linker->params->addElement( $preparedFile, true );

            $prepared = $this->linker->params->get( $preparedFile, '' );
            foreach( $payments as $paymentID => $payment ) {
                if( $prepared[ $payment[ 'id' ] ] != 'done' ) {
                    $payments[ $paymentID ] = array_merge( $prepared[ $payment[ 'id' ] ], $payment );
                    $payments[ $paymentID ][ 'date' ] = $this->linker->datePicker->dateToLocal( $payments[ $paymentID ][ 'date' ] );

                    $content = $this->linker->shop->getPendingCommandDatas( $payment[ 'id' ],
                                                                            $payments[ $paymentID ][ 'session' ] );
                    if( $content != 'deleted' ) {
                        // We should get the content of the command, in order to show it if needed
                        $values[ 'content' ] = $content;
                        $values[ 'payment' ] = $payments[ $paymentID ];
                        $values[ 'billing' ] = $values[ 'content' ][ 'content' ][ 'billing_address' ];
                        $values[ 'billing' ][ 'mail' ] = $values[ 'content' ][ 'content' ][ 'billing_mail' ];

                        $payments[ $paymentID ][ 'more' ] = $this->render( 'manual_collector_one', $values, false, false );
                    } else {
                        unset( $payments[ $paymentID ] );
                    }
                } else {
                    unset( $payments[ $paymentID ] );
                }
            }
            $ret = array( 'payments' => $payments, 'class' => __CLASS__, 'className' => 'Téléphone' );
        }
        return $ret;
    }

    public function get_waiting_for_calls() {
        $paramsFile = SH_SITE_FOLDER . __CLASS__ . '/waiting_for_calls.params.php';
        $this->linker->params->addElement( $paramsFile, true );
        return $this->linker->params->get( $paramsFile, 'list', array( ) );
    }

    public function paymentByPhone( $id, $session ) {
        $this->debug( __FUNCTION__ . "($id,$session)", 2, __LINE__ );
        
        $paramsFile = SH_SITE_FOLDER . __CLASS__ . '/payments_prepared.params.php';
        $this->linker->params->addElement( $paramsFile, true );
        $prepared = $this->linker->params->get( $paramsFile, $id );
        list($class, $action, $id) = explode( '/', $prepared[ 'success' ] );
        $this->linker->$class->$action($id);
        
        
        $command = $id;
        $code = $session % 9999;
        
        $text = preg_replace( '`[a-zA-Z]`','', md5($session)).'0000';
        $code = substr($text,0, 4);
        
        if( $this->formSubmitted( 'payment_cant_reach' ) ) {
            $paramsFile = SH_SITE_FOLDER . __CLASS__ . '/waiting_for_calls.params.php';
            $params = $this->linker->params->addElement( $paramsFile, true );
            $this->linker->params->set(
                $paramsFile, 'list>' . $code,
                array(
                'id' => $id,
                'session' => $session,
                'code' => $code,
                'phone' => $_POST[ 'phone' ],
                'message' => $_POST[ 'message' ],
                'timestamp' => date( 'U' )
                )
            );
            $this->linker->params->write( $paramsFile );
            $mailer = $this->linker->mailer->get();
            $mail = $mailer->em_create();
            $mailer->em_addSubject( $mail, 'Demande de règlement par téléphone' );
            $content = '<b>Date : </b>' . $this->linker->datePicker->dateToLocal() . '<br />';
            $content .= '<b>Code de la commande à encaisser : </b>' . $code . '<br />';
            $content .= '<b>Tel. du client: </b>' . $_POST[ 'phone' ] . '<br />';
            $content .= '<b>Son message : </b><br />' . $_POST[ 'message' ] . '<br /><br />';
            $mailer->em_addContent( $mail, $content );
            $mailer->em_addAddress( $mail, $this->getParam( 'email' ) );
            $mailer->em_send( $mail );
            $this->render( 'we_will_call_you' );
            return true;
        }
        // We are paying by phone
        $this->linker->html->setTitle( $this->getI18n( 'phone_payment_name' ) );

        if( !isset( $_SESSION[ __CLASS__ ][ 'force_phone' ] ) ) {
            $values[ 'payment' ][ 'phone' ] = $this->getParam( 'phone', '' );
        } else {
            $values[ 'payment' ][ 'phone' ] = $_SESSION[ __CLASS__ ][ 'force_phone' ];
        }

        $values[ 'payment' ][ 'command' ] = $command;

        $this->setParam( 'pending>' . $command, array( 'date' => date( 'YmdHi' ) ) );
        $this->writeParams();

        $values[ 'payment' ][ 'code' ] = $code;

        // This method declares the code and the payment id to the classes that need to be aware of the payments
        //that are to be made
        $classes = $this->get_shared_methods( 'onBeforePaymentCall' );
        foreach( $classes as $class ) {
            $this->linker->$class->onBeforePaymentCall( $id, $code );
        }

        $paramsFile = SH_SITE_FOLDER . __CLASS__ . '/pending_payments.params.php';
        $this->linker->params->addElement( $paramsFile, true );
        $this->linker->params->set(
            $paramsFile, 'list>' . $code,
            array(
            'id' => $id, 'session' => $session, 'code' => $code, 'timestamp' => date( 'U' )
            )
        );
        $this->linker->params->write( $paramsFile );
        $this->render( 'payment', $values );
        /*
          // We call the success page, because there is no failure at this point
          $paymentMode = array(
          'name'=>$this->bank_getName(),
          'description'=>$this->bank_getDescription(),
          'extraTextForBill' => $this->getI18n($this->getParam('extraTextForBill'))
          );

          list($class,$method,$id) = explode('/',$payment['success']);

          if($this->linker->method_exists($class,$method)){
          $this->linker->$class->$method($id,$paymentMode);
          }else{
          $this->debug('The method '."$class -> $method -> $id".' does not exist',0,__LINE__);
          }/* */
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

}
