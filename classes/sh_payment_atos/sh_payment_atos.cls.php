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

$paymentObject = sh_linker::getInstance()->payment;

abstract class sh_payment_atos extends sh_banks {

    const CLASS_VERSION = '1.1.12.03.12';

    protected $parmcom_bankExtension = ''; // the extension that is added for the bank's parmcom file
    protected $lastErrorId = 0;
    protected $needsManagementPage = true;
    protected $ready = false;
    protected $bank_code = 0;
    protected $payments = array( );
    protected $paymentsLogParams = null;
    protected $merchant_id = '';
    protected $merchant_country = '';
    protected $countries = array(
        'fr', 'be', 'de', 'it', 'es', 'en'
    );
    protected $merchant_currency = 0;
    protected $currencies = array(
        self::CUR_EUR, self::CUR_USD, self::CUR_CHF, self::CUR_GBP, self::CUR_CAD,
        self::CUR_JPY, self::CUR_MXN, self::CUR_TRY, self::CUR_AUD, self::CUR_NZD,
        self::CUR_NOK, self::CUR_BRL, self::CUR_ARS, self::CUR_KHR, self::CUR_TWD,
        self::CUR_SEK, self::CUR_DKK, self::CUR_KRW, self::CUR_SGD,
    );
    protected $price = false;
    protected $successUrl = '';
    protected $failureUrl = '';
    protected $autoresponseUrl = '';
    protected $pathFolder = '';

    public function construct() {
        $this->pathFolder = SH_ROOT_FOLDER . 'atos/' . substr( md5( SH_SITENAME . $this->bank_code . $this->bankName ),
                                                                    0, 4 ) . '/';
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            if( version_compare( $installedVersion, '1.1.11.05.10', '<' ) ) {
                if( !is_dir( SH_ROOT_FOLDER . 'atos/' ) ) {
                    mkdir( SH_ROOT_FOLDER . 'atos/' );
                }
                if( !is_dir( $this->pathFolder ) ) {
                    mkdir( $this->pathFolder );
                }
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }

        if( empty( $this->parmcom_bankExtension ) ) {
            $this->setError( self::ERROR_BANK_PARAMETTERS,
                             'ATOS : the $parmcom_bankExtension was not filled for one solution' );
        }
        $this->shareI18nFile( 'sh_payment' );
        if( !is_dir( SH_SITE_FOLDER . __CLASS__ ) ) {
            mkdir( SH_SITE_FOLDER . __CLASS__ );
        }
        $this->paymentsLogParams = SH_SITE_FOLDER . __CLASS__ . '/paymentsLog.params.php';
        $this->merchant_id = $this->getParam( 'merchant_id', false );
        if( $this->merchant_id !== false ) {
            $this->ready = true;
            $this->active = $this->getParam( 'active', false );
        }
        $this->merchant_country = $this->getParam( 'merchant_country', 'fr' );
        $this->merchant_currency = $this->getParam( 'currency', self::CUR_EUR );
        if( !file_exists( SH_IMAGES_FOLDER . 'banks/atos/CB.gif' ) ) {
            $thisFolder = dirname( __FILE__ ) . '/logo/';
            $this->linker->payment->addBankLogo_auto( $thisFolder . 'CB.gif', 'atos' );
            $this->linker->payment->addBankLogo_auto( $thisFolder . 'INTERVAL.gif', 'atos' );
            $this->linker->payment->addBankLogo_auto( $thisFolder . 'MASTERCARD.gif', 'atos' );
            $this->linker->payment->addBankLogo_auto( $thisFolder . 'VISA.gif', 'atos' );
        }
    }

    public function manage( $message = '' ) {
        if( !empty( $message ) ) {
            $values[ 'message' ][ 'text' ] = $message;
        }
        if( $this->formSubmitted( 'bank_manager' ) ) {
            $pathFolder = $this->pathFolder;
            $pathfile = $pathFolder . 'pathfile';
            $tmp_file = $_FILES[ 'certif' ][ 'tmp_name' ];
            $country = $this->merchant_country;
            $certificate = $this->merchant_id;
            if( is_uploaded_file( $tmp_file ) ) {
                // We check the name
                $fileName = $_FILES[ 'certif' ][ 'name' ];
                if( preg_match( '`^certif\.([a-zA-Z0-9]+)\.([0-9]+$)`', $fileName, $matches ) ) {
                    if( move_uploaded_file( $tmp_file, $pathFolder . $fileName ) ) {
                        $country = $matches[ 1 ];
                        $certificate = $matches[ 2 ];
                        $this->setParam( 'merchant_id', $certificate );
                        $this->setParam( 'merchant_country', $country );
                        $this->linker->html->addMessage(
                            'Fichier de certificat envoyé avec succès!'
                        );
                    } else {
                        $_SESSION[ __CLASS__ ][ 'error_sending_certif' ] = true;
                    }
                } else {
                    $_SESSION[ __CLASS__ ][ 'error_wrong_certif_file' ] = true;
                }
            }

            $this->setParam( 'active', isset( $_POST[ 'active' ] ) );
            $this->setParam( 'currency', $_POST[ 'currency' ] );
            $this->writeParams();
            // We have to create the pathfile file
            $requestFile = $pathFolder . 'request';
            $folder = dirname( __FILE__ ) . '/';
            copy( $folder . 'parmcom.' . $this->parmcom_bankExtension,
                  $pathFolder . 'parmcom.' . $this->parmcom_bankExtension );
            $this->helper->writeInFile(
                $pathfile,
                'DEBUG!NO' . "!\n" .
                'D_LOGO!' . SH_IMAGES_PATH . 'banks/atos/' . "!\n" .
                'F_DEFAULT!' . $pathFolder . 'parmcom.' . $this->parmcom_bankExtension . "!\n" .
                'F_PARAM!' . $pathFolder . 'parmcom' . "!\n" .
                'F_CERTIFICATE!' . $pathFolder . 'certif' . "!\n"
            );
            $this->helper->writeInFile(
                $pathFolder . 'parmcom.' . $certificate,
                'CURRENCY!' . $_POST[ 'currency' ] . "!\n" .
                'HEADER_FLAG!' . 'no' . "!\n" .
                'PAYMENT_MEANS!' . 'CB,2,VISA,2,MASTERCARD,2' . "!\n" .
                'F_CERTIFICATE!' . $pathFolder . 'certif' . "!\n"
            );

            $this->linker->path->refresh();
        }
        if( isset( $_SESSION[ __CLASS__ ][ 'error_sending_certif' ] ) ) {
            $values[ 'error' ][ 'error_sending_certif' ] = true;
            unset( $_SESSION[ __CLASS__ ][ 'error_sending_certif' ] );
        }
        if( isset( $_SESSION[ __CLASS__ ][ 'error_wrong_certif_file' ] ) ) {
            $values[ 'error' ][ 'error_wrong_certif_file' ] = true;
            unset( $_SESSION[ __CLASS__ ][ 'error_wrong_certif_file' ] );
        }
        if( $this->getParam( 'active', false ) ) {
            $values[ 'bank' ][ 'state' ] = 'checked';
        }
        $values[ 'bank' ][ 'logo' ] = $this->bank_getLogo();
        $values[ 'bank' ][ 'code' ] = $this->bank_getCode();
        $values[ 'bank' ][ 'name' ] = $this->bank_getName();
        $values[ 'bank' ][ 'merchant_id' ] = $this->merchant_id;
        $values[ 'bank' ][ 'merchant_country' ] = $this->merchant_country;
        foreach( $this->currencies as $currency ) {
            $values[ 'currencies' ][ $currency ][ 'name' ] = $currency;
            if( $currency == $this->merchant_currency ) {
                $values[ 'currencies' ][ $currency ][ 'state' ] = 'selected';
            }
        }
        $values[ 'i18n' ] = 'sh_payment_atos';
        $this->render( dirname( __FILE__ ) . '/renderFiles/manage.rf.xml', $values );
    }

    public function cron_job( $time ) {
        return true;
    }

    public function bank_getDescription() {
        return $this->getI18n( 'atos_description' );
    }

    public function payment_setPrice( $payment, $price, $decimalPrice ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( $price > 0 ) {
            $this->price = $decimalPrice;
            return true;
        }
        return $this->setError( self::ERROR_NEGATIVE_PRICES_FORBIDDEN );
    }

    public function payment_setCurrency( $payment, $currency ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( !in_array( $currency, $this->currencies ) ) {
            return $this->setError( self::ERROR_CUR_NOT_SUPPORTED );
        }
        $this->merchant_currency = $currency;
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
        $this->helper->storeData(__CLASS__, $payment.'_failure', $page);
    }

    public function payment_setSuccessPage( $payment, $page ) {
        $this->debug( __FUNCTION__ . '(' . $payment . ', ' . $page . ')', 2, __LINE__ );
        $this->helper->storeData(__CLASS__, $payment.'_success', $page);
    }

    public function payment_setValidatedPage( $payment, $page ) {
        $this->debug( __FUNCTION__ . '(' . $payment . ', ' . $page . ')', 2, __LINE__ );
        $this->helper->storeData(__CLASS__, $payment.'_validated', $page);
    }

    public function payment_setUnauthorizedPage( $payment, $page ) {
        $this->debug( __FUNCTION__ . '(' . $payment . ', ' . $page . ')', 2, __LINE__ );
        $this->helper->storeData(__CLASS__, $payment.'_unauthorized', $page);
    }

    /**
     * This method is called by the user when he clicks "cancel" on the bank's atos server.
     * @return * The return of the method called
     */
    public function payment_failure( $payment ) {
        // Getting and launching the failure page
        $page = $this->helper->getData(__CLASS__, $payment.'_failure', false);
        list($class, $method, $id) = explode( '/', $page );

        if( $this->linker->method_exists( $class, $method ) ) {
            return $this->linker->$class->$method( $id );
        }
        $this->linker->path->error( 404 );
        return true;
    }

    /**
     * This method is called by the user when he clicks "back to the shop (after payment success)" on the bank's atos server.
     * @return * The return of the method called
     */
    public function payment_success( $payment ) {
        $this->payment_validated();
        $page = $this->helper->getData(__CLASS__, $payment.'_success', false);

        list($class, $method, $id) = explode( '/', $page );
        if( $this->linker->method_exists( $class, $method ) ) {
            return $this->linker->$class->$method( $id );
        }
        $this->linker->path->error( 404 );
        return true;
    }

    /**
     * This method may be called automatically by the bank's atos server, or manually by the user whan he clicks on the
     * "cancel" or "back to the shop (after payment success)" buttons
     * @return bool always returns true
     */
    public function payment_validated() {
        // Should link either to validatePage or to unauthorizedPage
        $baseUri = $this->linker->path->getBaseUri();
        // Récupération de la variable cryptée DATA
        $message = escapeshellcmd( 'message=' . $_POST[ 'DATA' ] );

        // Initialisation du chemin du fichier pathfile
        $pathFolder = $this->pathFolder;
        $pathfile = $pathFolder . 'pathfile';
        $pathfile = 'pathfile=' . $pathfile;

        //Initialisation du chemin de l'executable response
        $path_bin = dirname( __FILE__ ) . '/response';

        // Appel du binaire response
        $query = $path_bin . ' ' . $pathfile . ' ' . $message;
        $result = exec( $query );

        //	on separe les differents champs et on les met dans une variable tableau
        list(
            , $code, $error, $merchant_id, $merchant_country, $amount, $transaction_id, $payment_means,
            $transmission_date, $payment_time, $payment_date, $response_code, $payment_certificate,
            $authorisation_id, $currency_code, $card_number, $cvv_flag, $cvv_response_code,
            $bank_response_code, $complementary_code, $complementary_info, $return_context, $caddie,
            $receipt_complement, $merchant_language, $language, $customer_id, $order_id, $customer_email,
            $customer_ip_address, $capture_day, $capture_mode, $data
            ) = explode( "!", $result );

        if( $code == -1 ) {
            $log = "<?php\n";
            $log .= "---------------------------------\n";
            $log .= 'Date : ' . date( 'Y:m:d H:i:s' ) . "\n";
            $log .= "error = $error\n";
            $log .= "-------------------------------------------\n?>\n";
            $logFile = SH_SITE_FOLDER . __CLASS__ . '/payments_log_' . date( 'Y-m' ) . '.php';
            E_FOLDER . __CLASS__ . '/payments_log_' . date( 'Y-m' ) . '.php';

            $this->helper->writeInFile( $logFile, $log, 2 );
            return false;
        }
        $this->linker->params->addElement( $this->paymentsLogParams, true );
        if( $this->linker->params->get( $this->paymentsLogParams, 'done>' . $transaction_id, false ) ) {
            // The payment has already been validated
            return true;
        } else {
            // Logging the transaction_id in order not to validate twice the same payment
            $this->linker->params->set( $this->paymentsLogParams, 'done>' . $transaction_id, true );
            $this->linker->params->write( $this->paymentsLogParams );
        }

        $log = "<?php\n";
        $log .= "---------------------------------\n";
        $log .= 'Date : ' . date( 'Y:m:d H:i:s' ) . "\n";

        $payment_accepted = false;
        $log .= 'Réponse : ';
        if( $response_code == '00' ) {
            $log .= "Paiement accepté\n";
            $payment_accepted = true;
        } else {
            if( $response_code == '02' ) {
                $log .= "Demande d'autorisation - dépassement du plafond\n";
            } elseif( $response_code == '03' ) {
                $log .= "Merchant_id invalide, ou contrat VAD inexistant\n";
            } elseif( $response_code == '05' ) {
                $log .= "Autorisation refusée\n";
            } elseif( $response_code == '12' ) {
                $log .= "Transaction invalide - mauvais paramètres\n";
            } elseif( $response_code == '17' ) {
                $log .= "Annulation par l'internaute\n";
            } elseif( $response_code == '30' ) {
                $log .= "Erreur de format\n";
            } elseif( $response_code == '34' ) {
                $log .= "Suspicion de fraude\n";
            } elseif( $response_code == '75' ) {
                $log .= "Nombre de tentatives dépassé\n";
            } elseif( $response_code == '90' ) {
                $log .= "Service temporairement indisponible\n";
            }
            $log .= $error . "\n";
        }
        $log .= "\n";
        $log .= "merchant_id : $merchant_id\n";
        $log .= "merchant_country : $merchant_country\n";
        $log .= "amount : $amount\n";
        $log .= "transaction_id : $transaction_id\n";
        $log .= "transmission_date: $transmission_date\n";
        $log .= "payment_means: $payment_means\n";
        $log .= "payment_time : $payment_time\n";
        $log .= "payment_date : $payment_date\n";
        $log .= "response_code : $response_code\n";
        $log .= "payment_certificate : $payment_certificate\n";
        $log .= "authorisation_id : $authorisation_id\n";
        $log .= "currency_code : $currency_code\n";
        $log .= "card_number : $card_number\n";
        $log .= "cvv_flag: $cvv_flag\n";
        $log .= "cvv_response_code: $cvv_response_code\n";
        $log .= "bank_response_code: $bank_response_code\n";
        $log .= "complementary_code: $complementary_code\n";
        $log .= "complementary_info: $complementary_info\n";
        $log .= "return_context: $return_context\n";
        $log .= "caddie : $caddie\n";
        $log .= "receipt_complement: $receipt_complement\n";
        $log .= "merchant_language: $merchant_language\n";
        $log .= "language: $language\n";
        $log .= "customer_id: $customer_id\n";
        $log .= "order_id: $order_id\n";
        $log .= "customer_ip_address: $customer_ip_address\n";
        $log .= "capture_day: $capture_day\n";
        $log .= "capture_mode: $capture_mode\n";
        $log .= "data: $data\n";
        $log .= "-------------------------------------------\n?>\n";

        $logFile = SH_SITE_FOLDER . __CLASS__ . '/payments_log_' . date( 'Y-m' ) . '.php';
        E_FOLDER . __CLASS__ . '/payments_log_' . date( 'Y-m' ) . '.php';

        $this->helper->writeInFile( $logFile, $log, 2 );

        if( $payment_accepted ) {
            $page = $this->helper->getData(__CLASS__, $payment.'_validated', false);
        } else {
            $page = $this->helper->getData(__CLASS__, $payment.'_unauthorized', false);
        }
        list($class, $method, $id) = explode( '/', $page );
        if( $this->linker->method_exists( $class, $method ) ) {
            return $this->linker->$class->$method( $id, $this->bank_code );
        }
        return true;
    }

    public function payment_action( $payment, $order_id = '' ) {
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
            0, $this->bank_code, 'payment_validated', $payment
        );
        $this->autoresponseUrl = $this->linker->path->getLink(
            'payment/callPage/' . $autoresponseSession
        );

        $baseUri = $this->linker->path->getBaseUri();

        $parm = 'merchant_id=' . $this->merchant_id;
        $parm.= ' merchant_country=fr';
        $parm.= ' amount=' . $this->price;
        $parm.= ' currency_code=' . $this->merchant_currency;
        $pathFolder = $this->pathFolder;
        $pathfile = $pathFolder . 'pathfile';
        $parm.= ' pathfile=' . $pathfile;
        $parm.= ' cancel_return_url=' . $baseUri . $this->failureUrl;
        $parm.= ' normal_return_url=' . $baseUri . $this->successUrl;
        $parm.= ' automatic_response_url=' . $baseUri . $this->autoresponseUrl;
        $parm.= ' header_flag=no';
        if($order_id == ''){
            $order_id = 'order_'.$payment;
        }
        $parm.= ' order_id='.$order_id;
        
        // Path to the binary file
        $path_bin = dirname( __FILE__ ) . '/request';

        // Executing the query
        $this->debug( 'Query : ' . $path_bin . ' ' . $parm, 3, __LINE__ );
        $result = exec( $path_bin . ' ' . $parm );
        $params = explode( ' ', $parm );
        // Getting and analysing the result
        list(, $code, $error, $message) = explode( '!', $result );
        if( ($code == "") && ($error == "") ) {
            $this->debug( 'Could not find the "request" bin', 0, __LINE__ );
            return false;
        } elseif( $code != 0 ) {
            // There was an error
            if( $this->debugging() > 0 ) {
                echo '<hr />ATOS DEBUG : <br />' . $error . '<hr />';
            }
            return false;
        } else {
            $doc = new DOMDocument();
            $doc->loadHTML( '<div>' . $message . '</div>' );
            $values[ 'atos' ][ 'form' ] = $doc->saveXML( $doc->getElementsByTagName( 'div' )->item( 0 ) );
            $values[ 'datas' ][ 'price' ] = $this->price;
            $values[ 'datas' ][ 'merchant_id' ] = $this->merchant_id;
            $values[ 'bank' ][ 'name' ] = $this->bank_getName();
            $values[ 'bank' ][ 'logo' ] = $this->bank_getLogo();
            $values[ 'i18n' ] = 'sh_payment_atos';
            $rendered = $this->render( dirname( __FILE__ ) . '/renderFiles/show.rf.xml', $values, false, false );
            return $rendered;
            //return $this->render( 'show', $values, false, false );
        }

        return false;
    }

    public function payment_execute( $payment, $session ) {
        echo 'OK';
    }

    public function payment_getTicket( $payment ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
    }

    /*

      private static $cvv_flag_code = array(
      '0' => 'Le numéro de contrôle n’est pas remonté par le commerçant',
      '1' => 'Le numéro de contrôle est présent',
      '2' => 'Le numéro de contrôle est présent sur la carte du porteur mais illisible (uniquement pour les cartes CB, VISA et MASTERCARD)',
      '9' => 'Le porteur a informé le commerçant que le numéro de contrôle n’était pas imprimé sur sa carte (uniquement pour les cartes CB, VISA, MASTERCARD et FINAREF)'
      );


      private static $accepted_cards = array(
      'CB', 'VISA', 'MASTERCARD', 'AMEX', 'DINERS',
      'FINAREF', 'FNAC', 'CYRILLUS', 'PRINTEMPS', 'KANGOUROU', 'SURCOUF', 'POCKETCARD', 'CONFORAMA',
      'NUITEA', 'AURORE', 'PASS', 'PLURIEL', 'TOYSRUS', 'CONNEXION', 'HYPERMEDIA', 'DELATOUR',
      'NORAUTO','NOUVFRONT', 'SERAP', 'BOURBON', 'COFINOGA', 'COFINOGA_BHV', 'COFINOGA_CASINOGEANT',
      'COFINOGA_DIAC', 'COFINOGA_GL', 'COFINOGA_GOSPORT', 'COFINOGA_MONOPRIX', 'COFINOGA_MRBRICOLAGE',
      'COFINOGA_SOFICARTE', 'COFINOGA_SYGMA', 'JCB', 'DELTA', 'SWITCH', 'SOLO'
      );

      private static $cvv_verification_code = array(
      '4E' => 'Numéro de contrôle incorrect',
      '4D' => 'Numéro de contrôle correct',
      '50' => 'Numéro de contrôle non traité',
      '53' => 'Le numéro de contrôle est absent de la demande d’autorisation',
      '55' => 'La banque de l’internaute n’est pas certifiée, le contrôle n’a pu être effectué.',
      'NO' => 'Pas de cryptogramme sur la carte.'
      );

      private static $std_response_code = array(
      '00' => 'Transaction approuvée ou traitée avec succès',
      '02' => 'Contacter l’émetteur de carte',
      '03' => 'Accepteur invalide',
      '04' => 'Conserver la carte',
      '05' => 'Ne pas honorer',
      '07' => 'Conserver la carte, conditions spéciales',
      '08' => 'Approuver après identification',
      '12' => 'Transaction invalide',
      '13' => 'Montant invalide',
      '14' => 'Numéro de porteur invalide',
      '15' => 'Emetteur de carte inconnu',
      '30' => 'Erreur de format',
      '31' => 'Identifiant de l’organisme acquéreur inconnu',
      '33' => 'Date de validité de la carte dépassée',
      '34' => 'Suspicion de fraude',
      '41' => 'Carte perdue',
      '43' => 'Carte volée',
      '51' => 'Provision insuffisante ou crédit dépassé',
      '54' => 'Date de validité de la carte dépassée',
      '56' => 'Carte absente du fichier',
      '57' => 'Transaction non permise à ce porteur',
      '58' => 'Transaction interdite au terminal',
      '59' => 'Suspicion de fraude',
      '60' => 'L’accepteur de carte doit contacter l’acquéreur',
      '61' => 'Dépasse la limite du montant de retrait',
      '63' => 'Règles de sécurité non respectées',
      '68' => 'Réponse non parvenue ou reçue trop tard',
      '90' => 'Arrêt momentané du système',
      '91' => 'Emetteur de cartes inaccessible',
      '96' => 'Mauvais fonctionnement du système',
      '97' => 'Échéance de la temporisation de surveillance globale',
      '98' => 'Serveur indisponible routage réseau demandé à nouveau',
      '99' => 'Incident domaine initiateur',
      );

      private static $sips_response_codes = array(
      '00' =>	'Autorisation acceptée',
      '02' =>	'Demande d’autorisation par téléphone à la banque à cause d’un dépassement de plafond d’autorisation sur la carte (cf. annexe I)',
      '03' =>	'Champ merchant_id invalide, vérifier la valeur renseignée dans la requête<br />Contrat de vente à distance inexistant, contacter votre banque.',
      '05' =>	'Autorisation refusée',
      '12' =>	'Transaction invalide, vérifier les paramètres transférés dans la requête.',
      '17' =>	'Annulation de l’internaute',
      '30' =>	'Erreur de format.',
      '34' =>	'Suspicion de fraude',
      '75' =>	'Nombre de tentatives de saisie du numéro de carte dépassé.',
      '90' =>	'Service temporairement indisponible'
      );

      // For request (step 1)
      private $merchant_id;
      private $merchant_country;
      private $amount;
      private $currency_code;
      private $pathfile;
      private $request_cmd;
      private $response_cmd;
      private $order_id;

      // For response (step 2)
      private $transaction_id;
      private $payment_means;
      private $transmission_date;
      private $payment_time;
      private $payment_date;
      private $response_code;
      private $payment_certificate;
      private $authorisation_id;
      private $card_number;
      private $cvv_flag;
      private $cvv_response_code;
      private $bank_response_code;
      private $complementary_code;
      private $complementary_info;
      private $return_context;
      private $caddie;
      private $receipt_complement;
      private $merchant_language;
      private $language;
      private $customer_id;
      private $customer_email;
      private $customer_ip_address;
      private $capture_day;
      private $capture_mode;
      private $data;

      public function __construct()
      {
      $this->pathfile			= realpath(dirname(__FILE__) . '/../config') . DIRECTORY_SEPARATOR . 'pathfile';
      $this->request_cmd		= sfConfig::get('app_sips_payment_request_cmd');
      $this->response_cmd		= sfConfig::get('app_sips_payment_response_cmd');
      }


      public function doRequest()
      {
      if (!in_array($this->currency_code, self::$currencies)) {
      $this->currency_code = 'EUR';
      }

      if (!in_array($this->merchant_country, self::$languages)) {
      $this->merchant_country = 'fr';
      }

      $ctx = sfContext::getInstance();
      $ctl = $ctx->getController();

      if (!is_string(sfConfig::get('app_sips_payment_user_return'))) {
      throw new Exception('Missing sips_payment parameter in app.yml : user_return');
      }

      if (!is_string(sfConfig::get('app_sips_payment_user_cancel'))) {
      throw new Exception('Missing sips_payment parameter in app.yml : user_cancel');
      }

      if (!is_string(sfConfig::get('app_sips_payment_merchant_id'))) {
      throw new Exception('Missing sips_payment parameter in app.yml : merchant_id');
      }

      if (!is_string(sfConfig::get('app_sips_payment_country'))) {
      throw new Exception('Missing sips_payment parameter in app.yml : country');
      }

      if (!$ctx->getUser()->hasParameter('amount', 'sips_payment')) {
      throw new Exception('Missing parameter : amount');
      }

      if (!$ctx->getUser()->hasParameter('currency', 'sips_payment')) {
      throw new Exception('Missing parameter : currency');
      }

      if (!$ctx->getUser()->hasParameter('order_id', 'sips_payment')) {
      throw new Exception('Missing parameter : order_id');
      }


      $this->amount 			= $ctx->getUser()->getParameter('amount', 0.00, 'sips_payment');
      $this->order_id 		= $ctx->getUser()->getParameter('order_id', 0, 'sips_payment');
      $this->currency_code 	= $ctx->getUser()->getParameter('currency', 'EUR', 'sips_payment');
      $this->merchant_id 		= sfConfig::get('app_sips_payment_merchant_id');
      $this->merchant_country = sfConfig::get('app_sips_payment_country');

      if (!array_key_exists($this->currency_code, self::$currencies)) {
      throw new Exception('Incorrect currency : ' . $this->currency_code);
      }

      if (!array_key_exists($this->merchant_country, self::$languages)) {
      throw new Exception('Incorrect country : ' . $this->merchant_country);
      }

      $parm = "merchant_id=" . $this->merchant_id;
      $parm .= " merchant_country=" . $this->merchant_country;
      $parm .= " amount=" . (integer)($this->amount * 100);
      $parm .= " currency_code=" . self::$currencies[$this->currency_code];
      $parm .= " pathfile=" . $this->pathfile;
      $parm .= " normal_return_url=" . $ctl->genUrl(sfConfig::get('app_sips_payment_user_return'), true);
      $parm .= " cancel_return_url=" . $ctl->genUrl(sfConfig::get('app_sips_payment_user_cancel'), true);
      $parm .= " automatic_response_url=" . $ctl->genUrl('sfPaymentSIPS/response', true);

      // Execution de la commande request
      $result = exec($this->request_cmd . ' ' . $parm);
      $tableau = explode ("!", "$result");

      if ($tableau[1] == '' && $tableau[2] == '') {
      throw new Exception("Executable request not found : " . $error);
      } else if ($tableau[1] != 0) {
      throw new Exception("API payment call error : " . $error);
      }

      return $tableau[3];
      }


      public function doResponse()
      {
      $ctx = sfContext::getInstance();
      $message = "message=" . $ctx->getRequest()->getParameter('DATA');
      $pathfile = "pathfile=" . $this->pathfile;

      $result=exec($this->response_cmd . ' ' . $pathfile . ' ' . $message);

      $tableau = explode ("!", $result);

      if ($tableau[1] == '' && $tableau[2] == '') {
      throw new Exception("Executable request not found : " . $error);
      } else if ($tableau[1] != 0) {
      throw new Exception("API payment call error : " . $error);
      }

      // Step 1 params
      $this->merchant_id 			= $tableau[3];
      $this->merchant_country 	= $tableau[4];
      $this->amount 				= $tableau[5];

      // Step 2 params
      $this->transaction_id 		= $tableau[6];
      $this->payment_means 		= $tableau[7];
      $this->transmission_date	= $tableau[8];
      $this->payment_time 		= $tableau[9];
      $this->payment_date 		= $tableau[10];
      $this->response_code 		= $tableau[11];
      $this->payment_certificate 	= $tableau[12];
      $this->authorisation_id 	= $tableau[13];
      $this->currency_code 		= $tableau[14];
      $this->card_number 			= $tableau[15];
      $this->cvv_flag 			= $tableau[16];
      $this->cvv_response_code 	= $tableau[17];
      $this->bank_response_code 	= $tableau[18];
      $this->complementary_code 	= $tableau[19];
      $this->complementary_info	= $tableau[20];
      $this->return_context 		= $tableau[21];
      $this->caddie 				= $tableau[22];
      $this->receipt_complement 	= $tableau[23];
      $this->merchant_language 	= $tableau[24];
      $this->language 			= $tableau[25];
      $this->customer_id 			= $tableau[26];
      $this->order_id 			= $tableau[27];
      $this->customer_email 		= $tableau[28];
      $this->customer_ip_address 	= $tableau[29];
      $this->capture_day 			= $tableau[30];
      $this->capture_mode 		= $tableau[31];
      $this->data 				= $tableau[32];

      $bIsErr = false;

      // Checks bank response code
      if ($this->bank_response_code != '00') {
      return false;
      }

      return true;
      }


      public function getBankResponseMessage()
      {
      return self::$std_response_code[$this->bank_response_code];
      }


      public function getCvvVerificationMessage()
      {
      return self::$cvv_verification_code[$this->cvv_response_code];
      }


      public function getCvvFlagMessage()
      {
      return self::$cvv_flag_code[$this->cvv_flag];
      }
     */

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

}
