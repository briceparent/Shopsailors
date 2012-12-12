<?php

/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See sh_shop::CLASS_VERSION
 * @package Shopsailors Core Classes
 */
if( !defined( 'SH_MARKER' ) ) {
    header( 'location: directCallForbidden.php' );
}

/**
 * Class that manages the shop, categories, products, cart, and commands
 */
class sh_shop extends sh_core {

    const CLASS_VERSION = '1.1.12.12.10';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db', 'sh_browser', 'sh_helper', 'sh_site', 'searcher', 'i18n'
    );
    protected static $usesRightsManagement = true;
    public $rights_methods = array(
        'showProduct', 'editProduct', 'showCategory', 'editCategory'
    );
    public $rights_shared_ids = array(
        array( 'showProduct', 'editProduct' ),
        array( 'showCategory', 'editCategory' )
    );
    protected $minimal = array(
        'switchProductState' => true, 'getCustomPropertyValues' => true, 'getBillPDF' => true,
        'add_to_cart_ajax' => true, 'productOrCategoryPicker' => true, 'productPicker' => true,
        'pack_get_variants' => true, 'get_cart_plugin_ajax' => true
    );
    public $callWithoutId = array(
        'showCommands', 'showBill', 'getBillPDF', 'showPaymentModes', 'showShipModes', 'editPaymentModes',
        'cart_doAction', 'cart_removeProduct', 'addToCart', 'switchProductState', 'editShipModes',
        'showAllProducts', 'cart_show', 'showBillList', 'editParams', 'notActive', 'inactiveProducts',
        'listDiscounts', 'add_to_cart_ajax', 'productOrCategoryPicker', 'pack_get_variants', 'packsList',
        'get_cart_plugin_ajax'
    );
    public $callWithId = array(
        'showCategory', 'showProduct', 'editCategory', 'editProduct',
        'editCustomProperty', 'editDiscount', 'editPack', 'productPicker',
        'showPack', 'editLayout'
    );
    protected $command_available_actions = array(
        'set_shipper', 'set_billing_address', 'set_shipping_address', 'set_external_datas', 'choose_payment'
    );

    // Constants
    const I18N_NAME = 1;
    const I18N_REFERENCE = 2;
    const I18N_DESCRIPTION = 3;
    const I18N_SHORTDESCRIPTION = 4;
    const I18N_STOCK = 5;
    const I18N_IMAGE = 6;
    const I18N_PRICE = 7;
    const I18N_BILLHEADLINE = 8;
    const I18N_BILLFOOTER = 9;
    const I18N_BILLCUSTOMERSERVICE = 10;

    const EXTERNAL_REMOVE_PRODUCT_FROM_CART = 'remove_from_cart';

    static $default_logo = '';
    protected $listType = null;
    protected $shopFolder = '';
    protected $commandsFolder = '';
    protected $cartsFolder = '';
    protected $sellingActivated = false;

    // Monney variables - (default to french format)
    const TAXES_INCLUDED = 'TTC';
    const TAXES_EXCLUDED = 'HT';
    protected $decimals = 2;
    protected $decSeparator = ',';
    protected $thousSeparator = ' ';
    protected $currency = 'Euro';
    protected $currencyBefore = '';
    protected $currencyAfter = '€';
    protected $taxes = self::TAXES_INCLUDED;
    protected $taxRate = 19.6;
    protected $showTaxSymbol = false;
    protected $activateShop = false;

    const ARG_PRODUCTLISTTYPE = 'product_list_type';

    const ROUND_TO_LOWER = 1;
    const ROUND_TO_NEARER = 2;
    const ROUND_TO_UPPER = 3;

    const CATEGORIES_CATEGORY = 1;
    const PRODUCTS_CATEGORY = 2;

    const EXTERNAL_PRODUCT_PRICE = 'price';
    const EXTERNAL_PRODUCT_IMAGE = 'image';
    const EXTERNAL_PRODUCT_NAME = 'name';
    const EXTERNAL_PRODUCT_REFERENCE = 'reference';
    const EXTERNAL_PRODUCT_SHORTDESCRIPTION = 'shortDescription';
    const EXTERNAL_PRODUCT_STOCK = 'stock';
    const EXTERNAL_PRODUCT_LINK = 'link';
    const EXTERNAL_PRODUCT_TAXRATE = 'taxRate';
    const EXTERNAL_PRODUCT_NEEDS_SHIPMENT = 'needsShipment';

    const CUSTOMPROPERTIES_UNSHOWN = 'unshown';
    const CUSTOMPROPERTIES_EMPTY = 'empty';
    const CUSTOMPROPERTIES_EMPTY_DB = 0;

    const PRODUCT_IS_A_PACK = 'product_is_a_pack';

    //////////////////////////////////////////////////////////////////////////////////////
    //                                  GENERAL PART                                    //
    //////////////////////////////////////////////////////////////////////////////////////

    /**
     * public function construct
     * Initiates the object
     */
    public function construct() {
        if( $this->linker->user->isMasterServer( false ) && !SH_MASTERISUSER ) {
            // No need for this class on a masterServer
            return true;
        }
        define( 'SH_SHOPIMAGES_PATH', SH_IMAGES_PATH . 'shop/' );
        define( 'SH_SHOPIMAGES_FOLDER', SH_IMAGES_FOLDER . 'shop/' );

        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            $this->linker->db->updateQueries( __CLASS__ );
            // The class datas are not in the same version as this file, or don't exist (installation)
            if( version_compare( $installedVersion, '1.1.11.03.28', '<' ) ) {
                $this->helper->addClassesSharedMethods( 'sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_cron', '', __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_facebook', '', __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_legacy', '', __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_payment', 'beforeNoPaymentModes', __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_payment', 'onNoPaymentModes', __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_searcher', 'scopes', __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_sitemap', '', __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_user', 'accountTabs', __CLASS__ );
                // Constructing the database tables
                for( $a = 1; $a <= 10; $a++ ) {
                    $this->db_execute( 'create_table_' . $a, array( ) );
                }
            }
            if( version_compare( $installedVersion, '1.1.11.05.27', '<' ) ) {
                $this->helper->addClassesSharedMethods( 'sh_template', 'change', __CLASS__ );
            }
            if( version_compare( $installedVersion, '1.1.11.05.31.3', '<' ) ) {
                $this->db_execute( 'addActiveFieldToCategories', array( ) );
                $this->db_execute( 'create_table_11', array( ) );
                $this->db_execute( 'create_table_12', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.05.31.4', '<' ) ) {
                $this->db_execute( 'create_table_13', array( ) );
                $this->db_execute( 'create_table_14', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.05.31.5', '<' ) ) {
                $this->db_execute( 'update_table_price_cache_1', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.05.31.6', '<' ) ) {
                $this->db_execute( 'update_table_price_cache_2', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.06.01', '<' ) ) {
                $this->db_execute( 'update_table_price_cache_3', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.06.01.2', '<' ) ) {
                $this->db_execute( 'update_table_promotions_1', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.06.01.3', '<' ) ) {
                $this->db_execute( 'update_table_promotions_2', array( ) );
                $this->db_execute( 'update_table_promotions_3', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.06.09', '<' ) ) {
                if( !is_dir( SH_IMAGES_FOLDER . 'shop/' ) ) {
                    mkdir( SH_IMAGES_FOLDER . 'shop/' );
                    $this->shopsailors_addSite( SH_SITENAME );
                }
                sh_browser::setRights(
                    SH_IMAGES_FOLDER . 'shop/',
                    sh_browser::ADDFOLDER
                    + sh_browser::DELETEFOLDER
                    + sh_browser::RENAMEFOLDER
                    + sh_browser::READ
                    + sh_browser::ADDFOLDER
                );
                sh_browser::setOwner( SH_IMAGES_FOLDER . 'shop/' );

                if( !is_dir( SH_IMAGES_FOLDER . 'shop/shippers/' ) ) {
                    mkdir( SH_IMAGES_FOLDER . 'shop/shippers/' );
                }
                sh_browser::setRights(
                    SH_IMAGES_FOLDER . 'shop/shippers/',
                    sh_browser::ADDFILE
                    + sh_browser::DELETEFILE
                    + sh_browser::RENAMEFILE
                    + sh_browser::READ
                    + sh_browser::ADDFOLDER
                );
                sh_browser::setOwner( SH_IMAGES_FOLDER . 'shop/shippers/' );
                sh_browser::setDimensions( SH_IMAGES_FOLDER . 'shop/shippers/', 100, 80 );

                if( !is_dir( SH_IMAGES_FOLDER . 'shop/categories/' ) ) {
                    mkdir( SH_IMAGES_FOLDER . 'shop/categories/' );
                }
                sh_browser::setRights(
                    SH_IMAGES_FOLDER . 'shop/categories/',
                    sh_browser::ADDFILE
                    + sh_browser::DELETEFILE
                    + sh_browser::RENAMEFILE
                    + sh_browser::READ
                    + sh_browser::ADDFOLDER
                );
                sh_browser::setOwner( SH_IMAGES_FOLDER . 'shop/categories/' );

                if( !is_dir( SH_IMAGES_FOLDER . 'shop/products/' ) ) {
                    mkdir( SH_IMAGES_FOLDER . 'shop/products/' );
                }
                sh_browser::setRights(
                    SH_IMAGES_FOLDER . 'shop/products/',
                    sh_browser::ADDFILE
                    + sh_browser::DELETEFILE
                    + sh_browser::RENAMEFILE
                    + sh_browser::READ
                    + sh_browser::ADDFOLDER
                );
                sh_browser::setOwner( SH_IMAGES_FOLDER . 'shop/products/' );
            }
            if( version_compare( $installedVersion, '1.1.11.06.09.1', '<' ) ) {
                sh_browser::setOwner( SH_IMAGES_FOLDER . 'shop/shippers/' );
            }
            if( version_compare( $installedVersion, '1.1.11.06.14', '<' ) ) {
                // We should update the prices
                $this->cron_job( sh_cron::JOB_DAY );
            }
            if( version_compare( $installedVersion, '1.1.11.06.17', '<' ) ) {
                $this->db_execute( 'update_table_products', array( ) );
                $this->db_execute( 'create_table_packs', array( ) );
                $this->db_execute( 'create_table_packs_contents', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.06.20', '<' ) ) {
                $this->db_execute( 'update_table_products_2', array( ) );
                $this->db_execute( 'update_table_products_3', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.06.21', '<' ) ) {
                $this->db_execute( 'update_table_packs', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.09.01', '<' ) ) {
                $name = $this->setI18n( 0, $this->getI18n( 'mainCategory_name' ) );
                $description = $this->setI18n( 0, $this->getI18n( 'mainCategory_description' ) );
                $shortDescription = $this->setI18n( 0, $this->getI18n( 'mainCategory_shortDescription' ) );
                $this->db_execute( 'create_main_category',
                                   array( 'name' => $name, 'description' => $description, 'shortDescription' => $shortDescription )
                );
            }
            if( version_compare( $installedVersion, '1.1.11.09.01.2', '<' ) ) {
                $this->linker->sitemap->renew();
            }
            if( version_compare( $installedVersion, '1.1.11.11.30', '<' ) ) {
                $this->db_execute( 'create_layout_table', array( ) );
                $this->db_execute( 'layout_create_first',
                                   array( 'id' => 1, 'top' => 0, 'bottom' => 0, 'name' => 'Default' ) );
            }
            if( version_compare( $installedVersion, '1.1.11.11.30.2', '<' ) ) {
                $this->db_execute( 'update_categories_add_layout', array( ) );
                $this->db_execute( 'update_products_add_layout', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.12.01', '<' ) ) {
                $this->db_execute( 'update_packs_add_layout', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.12.07', '<' ) ) {
                $this->db_execute( 'create_categories_cached_datas_table', array( ) );
                $this->db_execute( 'update_prices_cache_unique_add_min_quantity', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.12.03.22', '<' ) ) {
                // Re-generating the list of commands that could have been deleted by error by a previous version
                $commandListFile = SH_SITE_FOLDER . __CLASS__ . '/commands/list.php';
                if( file_exists( $commandListFile ) ) {
                    include($commandListFile);

                    $files = glob( SH_SITE_FOLDER . __CLASS__ . '/commands/*/*/*/*.php' );
                    foreach( $files as $filename ) {
                        include($filename);
                        $file = substr( str_replace( SH_SITE_FOLDER . __CLASS__ . '/commands/', '', $filename ), 0, -4 );
                        $commandList[ $command[ 'billId' ] ] = $file;
                        $command[ 'totalHT_float' ] = str_replace( array( ' ', '€', ',' ), array( '', '', '.' ),
                                                                   $command[ 'totalHT' ] );
                        $command[ 'totalTTC_float' ] = str_replace( array( ' ', '€', ',' ), array( '', '', '.' ),
                                                                    $command[ 'totalTTC' ] );
                        $this->helper->writeArrayInFile(
                            $filename, 'command', $command
                        );
                    }
                    $this->helper->writeArrayInFile(
                        $commandListFile, 'commandList', $commandList
                    );
                }
            }
            if( version_compare( $installedVersion, '1.1.12.08.17', '<' ) ) {
                $this->helper->addClassesSharedMethods( 'sh_site', 'sharedSettings', __CLASS__ );
            }
            if( version_compare( $installedVersion, '1.1.12.12.10', '<' ) ) {
                $this->linker->renderer->add_render_tag( 'render_shopproduct', __CLASS__, 'render_shopproduct' );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }

        if( !$this->getParam( 'activateShop', true ) ) {
            return true;
        }

        $this->activateShop = true;

        $css = $this->linker->template->getCSSFile(
            __CLASS__, SH_TEMPLATE_FOLDER . 'global/' . __CLASS__ . '.css'
        );

        $this->linker->html->addCSS( $css, __CLASS__ );

        self::$default_logo = '/images/shared/default/shop_default_logo_bill.png';
        $this->shopFolder = SH_SITE_FOLDER . __CLASS__ . '/';

        $this->commandsFolder = $this->shopFolder . 'commands/';
        $this->cartsFolder = $this->shopFolder . 'carts/';

        $monneyFormat = $this->getParam( 'monney_format' );
        $monneyKey = 'monney_formats_listing>' . $monneyFormat . '>';
        $this->decimals = $this->getParam( $monneyKey . 'decimals', 2 );
        $this->decSeparator = $this->getParam( $monneyKey . 'decSeparator', ',' );
        $this->thousSeparator = $this->getParam( $monneyKey . 'thousSeparator', ' ' );

        $this->currency = $this->getParam( 'currency', 'Euro' );
        $this->currencyBefore = $this->getParam(
            'currencies>' . $this->currency . '>before', ''
        );
        $this->currencyAfter = $this->getParam(
            'currencies>' . $this->currency . '>after', '€'
        );

        $this->taxes = $this->getParam( 'taxes' );
        $this->taxRate = $this->getParam( 'taxRate' );
        $this->showTaxSymbol = $this->getParam( 'showTaxSymbol' );

        // Defining the constants
        $this->constants = array(
            'currency' => $this->currency,
            'currencyBefore' => $this->currencyBefore,
            'currencyAfter' => $this->currencyAfter,
            'taxes' => $this->taxes,
            'taxRate' => $this->taxRate,
            'I18N_NAME' => self::I18N_NAME,
            'I18N_REFERENCE' => self::I18N_REFERENCE,
            'I18N_DESCRIPTION' => self::I18N_DESCRIPTION,
            'I18N_SHORTDESCRIPTION' => self::I18N_SHORTDESCRIPTION,
            'I18N_STOCK' => self::I18N_STOCK,
            'I18N_IMAGE' => self::I18N_IMAGE,
            'I18N_PRICE' => self::I18N_PRICE,
            'I18N_BILLHEADLINE' => self::I18N_BILLHEADLINE,
            'I18N_BILLFOOTER' => self::I18N_BILLFOOTER,
            'I18N_BILLCUSTOMERSERVICE' => self::I18N_BILLCUSTOMERSERVICE
        );
        if( $this->showTaxSymbol ) {
            $this->constants[ 'showTaxSymbol' ] = true;
        }
        if( $this->getParam( 'showQuantity', true ) ) {
            $this->constants[ 'showQuantity' ] = true;
        }
        if( $this->getParam( 'shipping>activated', true ) ) {
            $this->constants[ 'shipping_activated' ] = true;
        }
        $this->sellingActivated = $this->getParam( 'selling>activated' );
        if( $this->sellingActivated ) {
            $this->constants[ 'sellingActivated' ] = true;
        } else {
            $this->constants[ 'sellingNotActivated' ] = true;
        }
        $this->renderer_addConstants( $this->constants );

        $this->linker->html->addSpecialContents( 'shop_cart_content', $this->get_cart_plugin() );
        return true;
    }

    public function getSharedSettings() {
        $values[ 'settings' ][ 'active' ] = $this->getParam( 'activateShop', true ) ? 'checked' : '';
        $values[ 'settings' ][ 'payment' ] = $this->getParam( 'selling>activated', false ) ? 'checked' : '';
        $return = array(
            'title' => 'Vitrine / Boutique en ligne',
            'form' => $this->render( 'sharedSettingsForm', $values, false, false )
        );
        return $return;
    }

    public function setSharedSetting() {
        $this->setParam( 'activateShop', isset( $_POST[ 'shop' ][ 'active' ] ) );
        if( !$_POST[ 'shop' ][ 'active' ] ) {
            unset( $_POST[ 'shop' ][ 'payment' ] );
        }
        $this->setParam( 'selling>activated', isset( $_POST[ 'shop' ][ 'payment' ] ) );
        $this->writeParams();
    }

    public function template_change( $template ) {
        $this->onlyAdmin();
        $params = $this->linker->template->get( 'sh_shop', array( ) );
        $this->setParam( 'sh_shop', $params );
        $this->writeParams();
    }

    protected function get_cart_plugin() {
        $values[ 'cart' ][ 'link' ] = __CLASS__ . '/cart_show.php';
        return $this->render( 'cart_plugin', $values, false, false );
    }

    public function get_cart_plugin_ajax() {
        sh_cache::disable();
        $values[ 'cart' ][ 'total' ] = $_SESSION[ __CLASS__ ][ 'command' ][ 'total' ][ 'ttc' ];
        $values[ 'cart' ][ 'link' ] = __CLASS__ . '/cart_show.php';
        echo $this->render( 'cart_plugin_ajax', $values, false, false );
    }

    public function master_getMenuContent() {
        return array( );
    }

    public function admin_getMenuContent() {
        $adminMenu[ 'Boutique' ][ ] = array(
            'link' => 'shop/editParams/',
            'text' => 'Gérer la boutique',
            'icon' => 'picto_tool.png'
        );
        $shopClass = sh_linker::getInstance()->shop;
        if( $shopClass->isActivated() ) {
            $adminMenu[ 'Boutique' ][ ] = array(
                'link' => 'shop/editProduct/0',
                'text' => 'Ajouter un produit',
                'icon' => 'picto_add.png'
            );
            $adminMenu[ 'Boutique' ][ ] = array(
                'link' => 'shop/packsList/',
                'text' => 'Lots de produits',
                'icon' => 'picto_list.png'
            );
            $adminMenu[ 'Boutique' ][ ] = array(
                'link' => 'shop/editCategory/0',
                'text' => 'Ajouter une categorie',
                'icon' => 'picto_add.png'
            );
            $adminMenu[ 'Boutique' ][ ] = array(
                'link' => 'shop/listDiscounts/',
                'text' => 'Liste des promotions',
                'icon' => 'picto_list.png'
            );
            $adminMenu[ 'Boutique' ][ ] = array(
                'link' => 'shop/showCommands/',
                'text' => 'Liste des commandes',
                'icon' => 'ship_modes.png'
            );
            $adminMenu[ 'Boutique' ][ ] = array(
                'link' => 'shop/inactiveProducts/',
                'text' => 'Produits désactivés',
                'icon' => 'picto_list.png'
            );
            $adminMenu[ 'Boutique' ][ ] = array(
                'link' => 'shop/editLayout/1',
                'text' => 'Habillage de la boutique',
                'icon' => 'picto_layout.png'
            );
            $adminMenu[ 'Boutique' ][ ] = array(
                'link' => 'shop/editShipModes/',
                'text' => 'Modes d\'expédition',
                'icon' => 'ship_modes.png'
            );
            $adminMenu[ 'Boutique' ][ ] = array(
                'link' => 'shop/editCustomProperty/0',
                'text' => 'Propriétés personnalisées des produits',
                'icon' => 'picto_modify.png'
            );
            $adminMenu[ 'Boutique' ][ ] = array(
                'link' => 'shop/editPaymentModes/',
                'text' => 'Modes de paiement',
                'icon' => 'picto_money.png'
            );
        }
        return $adminMenu;
    }

    public function export_datas() {
        // We get the i18n entries
        $this->linker->i18n->export(
            __CLASS__, SH_SITE_FOLDER . __CLASS__ . '/exports/i18n.php'
        );
    }

    public function export_shop() {
        // We get the i18n entries
        $this->linker->i18n->export(
            __CLASS__, SH_SITE_FOLDER . __CLASS__ . '/exports/i18n.php'
        );
    }

    public function import_shop() {
        // We get the i18n entries
        $this->linker->i18n->import(
            __CLASS__, SH_SITE_FOLDER . __CLASS__ . '/exports/i18n.php'
        );
    }

    public function payment_beforeNoPaymentModes() {
        if( $this->sellingActivated ) {
            return $this->getI18n( 'before_selling_disactivation_no_payment_mode' );
        }
        return '';
    }

    public function payment_onNoPaymentModes() {
        if( $this->sellingActivated ) {
            $this->sellingActivated = false;
            $this->setParam( 'selling>activated', false );
            $this->writeParams();
            return $this->getI18n( 'selling_disactivated_no_payment_mode' );
        }
        return '';
    }

    public function showBillList() {
        $this->onlyMaster();
        $commandsFolder = SH_SITE_FOLDER . __CLASS__ . '/commands/';
        if( file_exists( $commandsFolder . 'list.php' ) ) {
            include($commandsFolder . 'list.php');
            foreach( $commandList as $id => $commandFile ) {
                if( file_exists( $commandsFolder . $commandFile . '.php' ) ) {
                    include($commandsFolder . $commandFile . '.php');
                    $values[ 'commands' ][ $id ][ 'totalHT' ] = $command[ 'totalHT' ];
                    $values[ 'commands' ][ $id ][ 'totalTTC' ] = $command[ 'totalTTC' ];
                    $values[ 'commands' ][ $id ][ 'customerName' ] = $command[ 'client' ][ 'name' ];
                    $values[ 'commands' ][ $id ][ 'id' ] = $id;
                    $values[ 'commands' ][ $id ][ 'date' ] = $this->linker->datePicker->dateToLocal(
                        substr( $commandFile, 0, 10 )
                    );
                    $values[ 'commands' ][ $id ][ 'link' ] = $this->linker->path->getLink(
                            $this->shortClassName . '/showBill/'
                        ) . '?bill_id=' . $id;
                    $values[ 'commands' ][ $id ][ 'pdf' ] = $this->linker->path->getLink(
                            $this->shortClassName . '/getBillPDF/'
                        ) . '?bill_id=' . $id;
                }
            }
            $this->render( 'billing_list', $values );
        } else {
            $this->render( 'billing_empty', $values );
        }
    }

    /**
     * @todo This method dosen't seem to be used. Remove it after the 14/05/2012 if no use case has been found.
      public function getBill( $id ) {
      $this->onlyAdmin();
      $commandsFolder = SH_SITE_FOLDER . __CLASS__ . '/commands/';
      include($commandsFolder . 'list.php');
      echo '<div><span class="bold">$commandList : </span>' . nl2br( str_replace( ' ', '&#160;',
      htmlentities( print_r( $commandList,
      true ) ) ) ) . '</div>';
      if( isset( $commandList[ $id ] ) && file_exists( $commandsFolder . $commandList[ $id ] . '.php' ) ) {
      include($commandsFolder . $commandList[ $id ] . '.php');
      return $command;
      }
      return false;
      }
     */
    public function showBill() {
        $commandsFolder = SH_SITE_FOLDER . __CLASS__ . '/commands/';
        $id = $_GET[ 'bill_id' ];
        include($commandsFolder . 'list.php');
        if( isset( $commandList[ $id ] ) && file_exists( $commandsFolder . $commandList[ $id ] . '.php' ) ) {
            include($commandsFolder . $commandList[ $id ] . '.php');
            if( $command[ 'client' ][ 'id' ] != $this->linker->user->userId && !$this->isAdmin() ) {
                $this->linker->path->error( 403 );
                return true;
            }
            if( is_array( $command[ 'elements' ] ) ) {
                foreach( $command[ 'elements' ] as $element ) {
                    $values[ 'elements' ][ ] = array(
                        'ref' => $element[ 0 ],
                        'name' => $element[ 1 ],
                        'price' => $element[ 2 ],
                        'quantity' => $element[ 3 ],
                        'total' => $element[ 4 ]
                    );
                }
            }
            $values[ 'bill' ][ 'customerName' ] = $command[ 'client' ][ 'name' ];
            $values[ 'bill' ][ 'customerAddress' ] = nl2br( $command[ 'client' ][ 'address' ] );
            $values[ 'bill' ][ 'customerId' ] = $command[ 'client' ][ 'id' ];
            $values[ 'bill' ][ 'id' ] = $id;
            $values[ 'bill' ][ 'totalHT' ] = $command[ 'totalHT' ];
            $values[ 'bill' ][ 'totalTTC' ] = $command[ 'totalTTC' ];
            $values[ 'bill' ][ 'paymentMode' ] = $command[ 'paymentMode' ][ 'name' ];
            $values[ 'bill' ][ 'pdf' ] = $this->linker->path->getLink(
                    $this->shortClassName . '/getBillPDF/'
                ) . '?bill_id=' . $id;
            if( is_array( $command[ 'billingAddress' ] ) ) {
                $values[ 'bill' ][ 'billingAddress' ] = implode( '<br />', $command[ 'billingAddress' ] );
            }
            if( is_array( $command[ 'shippingAddress' ] ) ) {
                $values[ 'bill' ][ 'shippingAddress' ] = implode( '<br />', $command[ 'shippingAddress' ] );
            }
            $values[ 'bill' ][ 'date' ] = $this->linker->datePicker->dateToLocal(
                substr( $commandList[ $id ], 0, 10 )
            );
        }
        $this->render( 'billing_details', $values );
    }

    function getBillPDF() {
        $this->onlyAdmin();
        $commandsFolder = SH_SITE_FOLDER . __CLASS__ . '/commands/';
        $id = $_GET[ 'bill_id' ];
        include($commandsFolder . 'list.php');
        if( isset( $commandList[ $id ] ) && file_exists( $commandsFolder . $commandList[ $id ] . '.pdf' ) ) {
            header( 'Content-type: application/pdf' );
            header( 'Content-Disposition: attachment; filename="' . $this->getI18n( 'bill' ) . '_' . $id . '.pdf"' );
            readFile( $commandsFolder . $commandList[ $id ] . '.pdf' );
        }
        return true;
    }

    /**
     * Clears the entries for the search engine, and re-creates them.
     */
    public function reinit_searcherEntries() {
        // Removing old entries
        $this->search_removeEntry( '*', 0 );
        // Adding categories
        $categories = $this->db_execute( 'categories_get_all', array( ) );
        $site = $this->linker->site;
        $langs = $site->langs;
        foreach( $categories as $categoryId ) {
            if( $this->categoryExists( $categoryId ) ) {
                list($category) = $this->db_execute( 'product_get', array( 'id' => $categoryId ) );
                foreach( $langs as $lang ) {
                    $categoryName[ $lang ] = $this->getI18n( $category[ 'name' ], $lang );
                    $categoryDescription[ $lang ] = $this->getI18n( $category[ 'description' ], $lang );
                    $categoryShortDescription[ $lang ] = $this->getI18n( $category[ 'shortDescription' ], $lang );
                }
                $this->search_addEntry(
                    'showCategory', $categoryId, $categoryName, $categoryDescription, $categoryShortDescription
                );
            }
        }
        // Adding products
        $products = $this->db_execute( 'products_get_active', array( ) );

        foreach( $products as $productId ) {
            if( $this->productExists( $productId ) ) {
                list($product) = $this->db_execute( 'product_get', array( 'id' => $productId ) );
                foreach( $langs as $lang ) {
                    $productName[ $lang ] = $this->getI18n( $product[ 'name' ], $lang );
                    $productDescription[ $lang ] = $this->getI18n( $product[ 'description' ], $lang );
                    $productShortDescription[ $lang ] = $this->getI18n( $product[ 'shortDescription' ], $lang );
                }
                $this->search_addEntry(
                    'showProduct', $productId, $productName, $productDescription, $productShortDescription
                );
            }
        }
    }

    /**
     * This method helps other classes to know whether the shop is enabled or not.
     * @return bool <b>true</b> if the shop is enabled, <b>false</b> if not.
     */
    public function isActivated() {
        return $this->getParam( 'activateShop', true );
    }

    /**
     * Gets the rendered notActive page
     */
    public function notActive() {
        $this->render( 'notActive', array( ) );
    }

    /**
     * Gets whether the connection has been made or not
     * @return bool The return of isActivated
     */
    public function requiresConnection() {
        return $this->isActivated();
    }

    /**
     * protected function notFound
     *
     */
    protected function notFound() {
        $this->linker->html->setTitle( $this->getI18n( 'error_product_not_found_title' ) );
        $this->render( 'product_not_found' );
        return true;
    }

    /**
     * Renders the results of a research (should be called by sh_searcher).
     * @param str $method The method that should be called to access the page
     * of the result
     * @param array $elements An array containing the list of the ids of the
     * elements that are to be shown in the results.
     * @return str The rendered xml for the results.
     */
    public function searcher_showResults( $method, $elements ) {
        if( !$this->activateShop ) {
            return '';
        }
        if( $method == 'showProduct' ) {
            foreach( $elements as $element ) {
                if( $this->productExists( $element ) ) {
                    list($product) = $this->db_execute( 'product_get', array( 'id' => $element ) );

                    // We manage the variants
                    if( $product[ 'hasVariants' ] ) {
                        $variants = $this->db_execute( 'product_get_variants', array( 'product_id' => $element ) );
                        if( $product[ 'variants_change_stock' ] ) {

                            $productDatas[ 'product' ][ 'variants_change_stock' ] = true;
                            $productDatas[ 'variants_fields' ][ ][ 'name' ] = $this->getI18n( 'stock' );
                        }
                    }
                    if( $product[ 'stock' ] > 0 || !$hideNullQuantityProducts ) {
                        $values[ 'category_elements' ][ $cpt ][ 'name' ] =
                            $this->getI18n( $product[ 'name' ] );
                        $values[ 'category_elements' ][ $cpt ][ 'image' ] = $product[ 'image' ];

                        $values[ 'category_elements' ][ $cpt ][ 'shortDescription' ] =
                            $this->getI18n( $product[ 'shortDescription' ] );
                        $values[ 'category_elements' ][ $cpt ][ 'description' ] =
                            $this->getI18n( $product[ 'description' ] );
                        $link = $this->linker->path->getLink( 'shop/showProduct/' . $element );
                        $values[ 'category_elements' ][ $cpt ][ 'link' ] = $link;
                        if( !$product[ 'variants_change_ref' ] ) {
                            $values[ 'category_elements' ][ $cpt ][ 'reference' ] = $product[ 'reference' ];
                        }
                        $values[ 'category_elements' ][ $cpt ][ 'hasVariants' ] =
                            $this->getI18n( $product[ 'hasVariants' ] );

                        $values[ 'category_elements' ][ $cpt ][ 'price' ] = $this->product_get_price_str( $element );
                        if( $this->sellingActivated ) {
                            if( $product[ 'stock' ] > 0 ) {
                                $values[ 'category_elements' ][ $cpt ][ 'stock' ] = $product[ 'stock' ];
                                $addToCartLink = $this->linker->path->getLink( 'shop/addToCart/' );
                                $addToCartLink .= '?product=' . $element;
                                $values[ 'category_elements' ][ $cpt ][ 'picto_addToCart_link' ] = $addToCartLink;
                            } else {
                                $values[ 'category_elements' ][ $cpt ][ 'stock' ] = $this->getI18n( 'product_nomorestock' );
                            }
                        }
                        $values[ 'category_elements' ][ $cpt ][ 'picto_show_link' ] = $link;
                        $cpt++;
                    }
                }
            }
            return array(
                'name' => $this->getI18n( 'search_productsTitle' ),
                'content' => $this->render(
                    'searcher_showProductResults', $values, false, false
                )
            );
        } elseif( $method == 'showCategory' ) {
            foreach( $elements as $element ) {
                if( $this->categoryExists( $element ) ) {
                    list($category) = $this->db_execute( 'category_get', array( 'id' => $element ) );
                    $values[ 'category_elements' ][ $cpt ][ 'name' ] =
                        $this->getI18n( $category[ 'name' ] );
                    $values[ 'category_elements' ][ $cpt ][ 'image' ] = $category[ 'image' ];
                    $values[ 'category_elements' ][ $cpt ][ 'shortDescription' ] =
                        $this->getI18n( $category[ 'shortDescription' ] );
                    $values[ 'category_elements' ][ $cpt ][ 'description' ] =
                        $this->getI18n( $category[ 'description' ] );
                    $link = $this->linker->path->getLink( 'shop/showCategory/' . $element );
                    $values[ 'category_elements' ][ $cpt ][ 'link' ] = $link;
                    $values[ 'category_elements' ][ $cpt ][ 'picto_show_link' ] = $link;
                    $cpt++;
                }
            }
            return array(
                'name' => $this->getI18n( 'search_categoriesTitle' ),
                'content' => $this->render(
                    'searcher_showCategoryResults', $values, false, false
                )
            );
        }
        return false;
    }

    /**
     * Gets the list of the contents types that the searcher should search in.
     * @return array An array containing the list of search types.
     */
    public function searcher_getScope() {
        return array(
            'scope' => 'shop',
            'name' => $this->getI18n( 'search_shopTitle' )
        );
    }

    /**
     * protected function increment_counter
     *
     */
    protected function increment_counter( $file ) {
        if( !file_exists( $file ) ) {
            $this->helper->writeInFile( $file, 1 );
            return 1;
        }
        $counter = file_get_contents( $file );
        $counter += 1;
        $this->helper->writeInFile( $file, $counter );
        return $counter;
    }

    /**
     * public function sitemap_renew
     *
     */
    public function sitemap_renew() {
        if( $this->getParam( 'activateShop', true ) ) {
            // Cart
            if( $this->getParam( 'activateCart' ) ) {
                $this->addToSitemap( 'shop/cart_show/', 0.2 );
            }

            // Legacy pages
            if( $this->getParam( 'shipping>showExpeditionInLegacy' ) ) {
                $this->addToSitemap( $this->shortClassName . '/showShipModes/', 0.2 );
            }
            if( $this->getParam( 'payment>showPaymentInLegacy' ) ) {
                $this->addToSitemap( $this->shortClassName . '/showPaymentModes/', 0.2 );
            }

            // Categories
            $categories = $this->db_execute( 'categories_get_all', array( ) );
            array_unshift( $categories, array( 'id' => 1, 'deepness' => 0 ) );

            foreach( $categories as $categoryId ) {
                $this->addToSitemap( 'shop/showCategory/' . $categoryId[ 'id' ], 0.8 );
            }

            // Products
            $products = $this->db_execute( 'products_get_active', array( ) );

            foreach( $products as $productId ) {
                list($product) = $this->db_execute( 'product_get', array( 'id' => $productId[ 'id' ] ) );
                if( !isset( $product[ 'active' ] ) || $product[ 'active' ] == true ) {
                    $this->addToSitemap( 'shop/showProduct/' . $productId[ 'id' ], 0.6 );
                }
            }
        }
        return true;
    }

    /**
     * public function getPageName
     *
     */
    public function getPageName( $action, $id = null, $forUrl = false ) {
        if( $action == 'showProduct' || ($forUrl && $action == 'editProduct') ) {
            if( $this->productExists( $id ) ) {
                if( $forUrl ) {
                    return $this->getProductName( $id );
                }
                return $this->getI18n( 'chooseProduct' ) . $this->getProductName( $id );
            }
        }
        if( $action == 'showCategory' || ($forUrl && $action == 'editCategory') ) {
            if( $this->categoryExists( $id ) ) {
                if( $forUrl ) {
                    return $this->getCategoryName( $id );
                }
                return $this->getI18n( 'chooseCategory' ) . $this->getCategoryName( $id );
            }
        }
        if( $action == 'cart_show' ) {
            return $this->getI18n( 'pageName_cart_show' );
        }
        if( $action == 'showPaymentModes' ) {
            return $this->getI18n( 'pageName_showPaymentModes' );
        }
        if( $action == 'showShipModes' ) {
            return $this->getI18n( 'pageName_showShipModes' );
        }
        if( $forUrl ) {
            return false;
        }
        return $this->__toString() . '->' . $action . '->' . $id;
    }

    /**
     * public function getProductName
     *
     */
    public function getProductName( $id ) {
        list($product) = $this->db_execute( 'product_get_name', array( 'id' => $id ) );
        return $this->getI18n( $product[ 'name' ] );
    }

    /**
     * public function getCategoryName
     *
     */
    public function getCategoryName( $id ) {
        list($category) = $this->db_execute( 'category_get_name', array( 'id' => $id ) );
        return $this->getI18n( $category[ 'name' ] );
    }

    public function getLegacyEntries() {
        return array( 'shipping', 'payment' );
    }

    public function getLegacyEntry( $element ) {
        $activated = $this->getParam( 'activateShop' );
        if( $element == 'shipping' ) {
            if(
                $activated &&
                $this->getParam( 'shipping>activated' ) &&
                $this->getParam( 'shipping>showExpeditionInLegacy', true )
            ) {
                return array(
                    'link' => $this->linker->path->getLink(
                        $this->shortClassName . '/showShipModes/'
                    ),
                    'textBefore' => '',
                    'text' => $this->getI18n( 'shipModes_title' ),
                    'textAfter' => ''
                );
            }
        } elseif( $element == 'payment' ) {
            if(
                $activated &&
                $this->getParam( 'payment>showPaymentInLegacy', true )
            ) {
                return array(
                    'link' => $this->linker->path->getLink(
                        $this->shortClassName . '/showPaymentModes/'
                    ),
                    'textBefore' => '',
                    'text' => $this->getI18n( 'show_paymentModesTitle' ),
                    'textAfter' => ''
                );
            }
        }
        return false;
    }

    protected function discounts_formatForRendering() {
        $discounts[ 'rulePrice0' ] = $this->getParam(
            'shipping>discounts>rulePrice0', ''
        );
        $discounts[ 'rulediscount0' ] = $this->getParam(
            'shipping>discounts>rulediscount0', ''
        );
        $discounts[ 'rulePrice1' ] = $this->getParam(
            'shipping>discounts>rulePrice1', ''
        );
        $discounts[ 'rulediscount1' ] = $this->getParam(
            'shipping>discounts>rulediscount1', ''
        );
        $discounts[ 'rulePrice2' ] = $this->getParam(
            'shipping>discounts>rulePrice2', ''
        );
        $discounts[ 'rulediscount2' ] = $this->getParam(
            'shipping>discounts>rulediscount2', ''
        );
        $discountsCount = 0;
        for( $a = 0; $a < 3; $a++ ) {
            $price = trim( $discounts[ 'rulePrice' . $a ] );
            $discount = trim( $discounts[ 'rulediscount' . $a ] );
            if( $price > 0 ) {
                $values[ 'display' ][ 'discounts' ] = true;
                if( strpos( $discount, '%' ) === false ) {
                    $discount = $this->monney_format( $discount );
                }
                $values[ 'discount' . $a ] = array(
                    'price' => $this->monney_format( $price ),
                    'discount' => $discount,
                    'activated' => true
                );
                $discountsCount++;
            }
            if( $discountsCount > 1 ) {
                $values[ 'display' ][ 'moreThan1discount' ] = true;
            }
        }
        return $values;
    }

    public function showPaymentModes() {
        $this->linker->html->setTitle( '' );
        $values[ 'paymentModes' ] = $this->linker->payment->getAvailablePaymentModes();
        $activated = $this->getParam( 'payment>modes', array( ) );
        $cpt = 0;
        if( is_array( $values[ 'paymentModes' ] ) ) {
            foreach( $values[ 'paymentModes' ] as $id => $paymentMode ) {
                if( !in_array( $paymentMode[ 'id' ], $activated ) ) {
                    unset( $values[ 'paymentModes' ][ $id ] );
                } else {
                    $cpt++;
                }
            }
        }

        if( $cpt > 1 ) {
            $values[ 'payment' ][ 'moreThanOneMode' ] = true;
        }

        $this->render( 'payment_showPaymentModes', $values );
        return true;
    }

    public function showShipModes() {
        $this->linker->html->setTitle( '' );
        $values = $this->shippers_formatForRendering();
        $discounts = $this->discounts_formatForRendering();
        if( !is_array( $discounts ) ) {
            $discounts = array( );
        }
        $values = array_merge( $values, $discounts );
        $this->render( 'ship_showShipModes', $values );
        return true;
    }

    public function set_minimum_for_supplier( $supplier, $minimum ) {
        $this->setParam( 'shipping>supplyers>' . $supplier . '>minimum', $minimum );
        return $this->writeParams();
    }

    public function editShipModes() {
        $this->onlyAdmin( true );
        if( $this->formSubmitted( 'shopShipModesEditor' ) ) {
            // State of the module
            if( isset( $_POST[ 'addShipMode_x' ] ) || isset( $_POST[ 'addShipMode' ] ) ) {
                $_POST[ 'supplyers' ][ ] = array( );
            }
            if( isset( $_POST[ 'removeShipMode' ] ) ) {
                $key = array_shift( array_keys( $_POST[ 'removeShipMode' ] ) );
                unset( $_POST[ 'supplyers' ][ $key ] );
            }

            $this->setParam( 'shipping>activated', isset( $_POST[ 'activateShipping' ] ) );
            $this->setParam(
                'shipping>showExpeditionInLegacy', isset( $_POST[ 'showExpeditionInLegacy' ] )
            );
            if( isset( $_POST[ 'showExpeditionInLegacy' ] ) ) {
                $this->addToSitemap( $this->shortClassName . '/showShipModes/', 0.2 );
            } else {
                $this->removeFromSitemap( $this->shortClassName . '/showShipModes/' );
            }
            $this->setParam( 'shipping>taxRate', str_replace( array( ',', '%' ), array( '.', '' ), $_POST[ 'taxRate' ] ) );

            $this->setParam( 'shipping>supplyers', $_POST[ 'supplyers' ] );
            if( is_array( $_POST[ 'supplyers' ] ) ) {
                foreach( $_POST[ 'supplyers' ] as $id => $supplyer ) {
                    //We change the decimal separator to a dot if it was a coma
                    $this->setParam(
                        'shipping>supplyers>' . $id . '>price', str_replace( ',', '.', $supplyer[ 'price' ] )
                    );
                }
            }

            $this->setParam(
                'shipping>discounts>rulePrice0', str_replace( ',', '.', $_POST[ 'shipRulePrice0' ] )
            );
            $this->setParam(
                'shipping>discounts>rulediscount0', str_replace( ',', '.', $_POST[ 'shipRulediscount0' ] )
            );
            $this->setParam(
                'shipping>discounts>rulePrice1', str_replace( ',', '.', $_POST[ 'shipRulePrice1' ] )
            );
            $this->setParam(
                'shipping>discounts>rulediscount1', str_replace( ',', '.', $_POST[ 'shipRulediscount1' ] )
            );
            $this->setParam(
                'shipping>discounts>rulePrice2', str_replace( ',', '.', $_POST[ 'shipRulePrice2' ] )
            );
            $this->setParam(
                'shipping>discounts>rulediscount2', str_replace( ',', '.', $_POST[ 'shipRulediscount2' ] )
            );

            $this->setParam(
                'shipping>comeTakeIt>activated', isset( $_POST[ 'comeTakeIt_activated' ] )
            );
            $this->setParam(
                'shipping>comeTakeIt>price', str_replace( ',', '.', $_POST[ 'comeTakeIt_price' ] )
            );
            $this->setParam(
                'shipping>comeTakeIt>addresses', trim( $_POST[ 'comeTakeIt_addresses' ] )
            );

            $this->writeParams();
        }

        // Gets the values
        if( $this->getParam( 'shipping>activated' ) === true ) {
            $values[ 'activateShipping' ][ 'checked' ] = 'checked';
        }
        if( $this->getParam( 'shipping>showExpeditionInLegacy', true ) === true ) {
            $values[ 'showExpeditionInLegacy' ][ 'checked' ] = 'checked';
        }
        $values[ 'taxRate' ][ 'value' ] = $this->getParam( 'shipping>taxRate', 19.6 );
        $supplyers = $this->getParam( 'shipping>supplyers', array( ) );
        foreach( $supplyers as $id => $supplyer ) {
            if( isset( $supplyer[ 'activated' ] ) ) {
                $activated = 'checked';
            } else {
                $activated = '';
            }
            if( empty( $supplyer[ 'name' ] ) ) {
                $supplyer[ 'name' ] = $this->getI18n( 'shipMode_newTitle' );
            }
            $values[ 'modes' ][ ] = array(
                'activated' => $activated,
                'name' => stripslashes( $supplyer[ 'name' ] ),
                'price' => $supplyer[ 'price' ],
                'id' => $id,
                'description' => stripslashes( $supplyer[ 'description' ] ),
                'logo' => $supplyer[ 'logo' ],
                'minimum' => isset( $supplyer[ 'minimum' ] ) ? $supplyer[ 'minimum' ] : 0
            );
        }
        $values[ 'ship' ][ 'rulePrice0' ] = $this->getParam(
            'shipping>discounts>rulePrice0', ''
        );
        $values[ 'ship' ][ 'rulediscount0' ] = $this->getParam(
            'shipping>discounts>rulediscount0', ''
        );
        $values[ 'ship' ][ 'rulePrice1' ] = $this->getParam(
            'shipping>discounts>rulePrice1', ''
        );
        $values[ 'ship' ][ 'rulediscount1' ] = $this->getParam(
            'shipping>discounts>rulediscount1', ''
        );
        $values[ 'ship' ][ 'rulePrice2' ] = $this->getParam(
            'shipping>discounts>rulePrice2', ''
        );
        $values[ 'ship' ][ 'rulediscount2' ] = $this->getParam(
            'shipping>discounts>rulediscount2', ''
        );

        if( $this->getParam( 'shipping>comeTakeIt>activated', false ) ) {
            $values[ 'comeTakeIt' ][ 'activated' ] = 'checked';
        }

        $values[ 'comeTakeIt' ][ 'price' ] = $this->getParam(
            'shipping>comeTakeIt>price', 0
        );
        $values[ 'comeTakeIt' ][ 'addresses' ] = $this->getParam(
            'shipping>comeTakeIt>addresses', ''
        );

        $values[ 'ship' ][ 'imagesFolder' ] = SH_IMAGES_FOLDER . 'shop/shippers/';
        // Renders the module's manager
        $this->render( 'edit_shipModes', $values );
    }

    public function editPaymentModes() {
        $this->onlyAdmin( true );
        if( $this->formSubmitted( 'shopPaymentModesEditor' ) ) {
            $this->setParam(
                'payment>showPaymentInLegacy', isset( $_POST[ 'showPaymentInLegacy' ] )
            );
            if( isset( $_POST[ 'showPaymentInLegacy' ] ) ) {
                $this->addToSitemap( $this->shortClassName . '/showPaymentModes/', 0.2 );
            } else {
                $this->removeFromSitemap( $this->shortClassName . '/showPaymentModes/' );
            }

            if( is_array( $_POST[ 'paymentModes' ] ) ) {
                $this->setParam(
                    'payment>modes', array_keys( $_POST[ 'paymentModes' ] )
                );
            } else {
                $this->setParam( 'payment>modes', array( ) );
            }
            $this->writeParams();
        }
        $values[ 'paymentModes' ] = $this->linker->payment->getAvailablePaymentModes( true );
        $activated = $this->getParam( 'payment>modes', array( ) );
        if( is_array( $values[ 'paymentModes' ] ) ) {
            foreach( $values[ 'paymentModes' ] as &$paymentMode ) {
                if( $paymentMode[ 'ready' ] && $paymentMode[ 'active' ] ) {
                    $paymentMode[ 'is_ready' ] = true;
                }
                if( in_array( $paymentMode[ 'id' ], $activated ) ) {
                    $paymentMode[ 'state' ] = 'checked';
                }
            }
        }

        // Gets the values
        if( $this->getParam( 'payment>showPaymentInLegacy', true ) === true ) {
            $values[ 'showPaymentInLegacy' ][ 'checked' ] = 'checked';
        }

        $values[ 'payment' ][ 'imagesFolder' ] = SH_IMAGES_FOLDER . 'shop/payment/';
        // Renders the module's manager
        $this->render( 'payment_edit_modes', $values );
    }

    public function deleteCustomProperty() {
        $id = $this->linker->path->page[ 'id' ];
        $useCaseAsCP = $this->db_execute( 'customProperty_checkUseCases', array( 'customProperty_id' => $id ), $qry );
        if( empty( $useCaseAsCP ) ) {
            // We may delete it
            // We get the i18n to remove
            list($cp) = $this->db_execute( 'customProperty_get', array( 'id' => $id ) );
            $this->removeI18n( $cp[ 'name' ] );
            foreach( explode( '|', $cp[ 'values' ] ) as $cpValue ) {
                $this->removeI18n( $cpValue );
            }
            $this->db_execute( 'customProperty_delete', array( 'id' => $id ) );
            $this->linker->html->addMessage( $this->getI18n( 'customProperty_deleted_successfully' ), false );
        } else {
            $this->linker->html->addMessage( $this->getI18n( 'customProperty_cant_deleted' ) );
        }
        $this->linker->path->redirect( __CLASS__, 'editCustomProperty', 0 );
    }

    public function getCustomPropertyValues( $id ) {
        list($customProperty) = $this->db_execute( 'customProperty_get', array( 'id' => $id ) );
        $customProperty[ 'name' ] = $this->getI18n( $customProperty[ 'name' ] );
        $values = explode( '|', $customProperty[ 'values' ] );
        $customProperty[ 'values' ] = array( );
        foreach( $values as $valueId => $oneValue ) {
            $val = $this->getI18n( $oneValue );
            if( !empty( $val ) ) {
                $customProperty[ 'values' ][ $valueId ] = $val;
            }
        }
        return $customProperty;
    }

    public function editCustomProperty( $id = null ) {
        $this->onlyAdmin( true );

        if( is_null( $id ) ) {
            $id = $this->linker->path->page[ 'id' ];
        }

        // Saving
        if( $this->formSubmitted( 'shopCustomPropertiesEditor' ) ) {
            if( $id == 0 ) {
                $needsRedirection = true;
                // We get a new unic number for the new custom property, and set the i18n name
                $this->db_execute( 'customProperties_create', array( ) );
                $id = $this->db_insertId();
            }
            // We update the i18n name
            list($customProperty) = $this->db_execute( 'customProperty_get', array( 'id' => $id ) );
            $customProperty[ 'name' ] = $this->setI18n( $customProperty[ 'name' ], $_POST[ 'name' ] );
            // We save the i18n entries for the values
            if( is_array( $_POST[ 'values' ] ) ) {
                foreach( $_POST[ 'values' ] as $oneId => $oneValue ) {
                    if( !empty( $oneValue ) ) {
                        if( !isset( $_POST[ 'deleteEntry' ][ $oneId ] ) ) {
                            // We check if there is text in a language
                            $oneId = $this->setI18n( $oneId, $oneValue );
                            $textValues .= $separator . $oneId;
                            $separator = '|';
                        } else {
                            $this->removeI18n( $oneId );
                        }
                    }
                }
            }
            $this->db_execute( 'customProperty_save',
                               array( 'id' => $id, 'name' => $customProperty[ 'name' ], 'values' => $textValues ) );

            // We set a success message
            $this->linker->html->addMessage( $this->getI18n( 'customProperty_saved_successfully' ), false );

            // We may have to load the page using the good id
            if( $needsRedirection ) {
                $this->linker->path->redirect(
                    $this->linker->path->getLink( __CLASS__ . '/' . __FUNCTION__ . '/' . $id ) . '?addValueAndSubmit=true'
                );
            }
        }

        $customProperties = $this->db_execute( 'customProperties_get_all', array( ) );

        // Showing
        if( $id != 0 ) {
            list($customProperty) = $this->db_execute( 'customProperty_get', array( 'id' => $id ) );

            // It is not a new (empty) custom property
            $values[ 'customProperty' ][ 'name' ] = $customProperty[ 'name' ];
            $values[ 'customProperty' ][ 'id' ] = $customProperty[ 'id' ];

            $cpValues = explode( '|', $customProperty[ 'values' ] );
            foreach( $cpValues as $oneValue ) {
                if( !empty( $oneValue ) ) {
                    $values[ 'customPropertyValues' ][ 'val_' . $oneValue ][ 'name' ] = $oneValue;
                }
            }

            $values[ 'customProperty' ][ 'newLink' ] = $this->linker->path->getLink(
                __CLASS__.'/' . __FUNCTION__ . '/0'
            );
            $values[ 'customProperty' ][ 'pageLink' ] = $this->linker->path->getLink(
                __CLASS__.'/' . __FUNCTION__ . '/' . $id
            );
        }
        $values[ 'customPropertyValues' ][ 'val_new' ][ 'name' ] = 0;
        // Gets the custom properties lists
        foreach( $customProperties as $customProperty ) {
            $thisId = $customProperty[ 'id' ];
            $values[ 'customProperties' ][ ] = array(
                'name' => $this->getI18n( $customProperty[ 'name' ] ),
                'editLink' => $this->linker->path->getLink(
                    $this->shortClassName . '/' . __FUNCTION__ . '/' . $thisId
                ),
                'deleteLink' => $this->linker->path->getLink(
                    $this->shortClassName . '/deleteCustomProperty/' . $thisId
                )
            );
        }
        // Renders the module's manager
        $this->render( 'edit_customProperty', $values );
    }

    /**
     * public function edit
     *
     */
    public function editParams() {
        $this->onlyAdmin( true );
        if( $this->formSubmitted( 'shopParamsEditor' ) ) {
            //Saves the datas
            // Bills bottom text
            $this->setParam(
                'billBottomText', stripslashes( $_POST[ 'billBottomText' ] )
            );

            if( $this->getParam( 'activateShop', true ) != isset( $_POST[ 'activateShop' ] ) ) {
                $renewSitemap = true;
            } else {
                $renewSitemap = false;
            }
            // Enables tthe only shop
            $this->setParam( 'activateShop', isset( $_POST[ 'activateShop' ] ) );
            if( isset( $_POST[ 'activateShop' ] ) ) {
                $this->helper->addClassesSharedMethods( 'sh_html', 'construct', __CLASS__ );
            } else {
                $this->helper->removeClassesSharedMethods( 'sh_html', 'construct', __CLASS__ );
            }
            sh_browser::setHidden( SH_SHOPIMAGES_FOLDER, !isset( $_POST[ 'activateShop' ] ) );

            // Format of the prices and currency
            $this->setParam( 'monney_format', $_POST[ 'monney_format' ] );
            $this->setParam( 'currency', $_POST[ 'currency' ] );

            // Taxes
            $this->setParam( 'taxes', $_POST[ 'taxes' ] );
            $this->setParam( 'taxRate', str_replace( array( ',', '%' ), array( '.', '' ), $_POST[ 'taxRate' ] ) );
            $this->setParam( 'showTaxSymbol', isset( $_POST[ 'showTaxSymbol' ] ) );
            $this->setParam( 'paymentRequiresConnexion', isset( $_POST[ 'paymentRequiresConnexion' ] ) );

            $this->setParam( 'forceUserToCheckConditions', isset( $_POST[ 'forceUserToCheckConditions' ] ) );
            $this->setParam( 'conditions', $_POST[ 'conditions' ] );

            // State of the cart
            if( $this->linker->payment->getActivePaymentModesCount() > 0 ) {
                $this->setParam( 'selling>activated', isset( $_POST[ 'sellingActivated' ] ) );
            }

            $command_mail = str_replace(
                array( ',', ';', '/', '\\', '"' ), "\n", stripslashes( $_POST[ 'command_mail' ] )
            );
            $command_mail = explode( "\n", $command_mail );
            $mailer = $this->linker->mailer->get();
            if( is_array( $command_mail ) ) {
                foreach( $command_mail as $oneMail ) {
                    if( $mailer->checkAddress( $oneMail ) ) {
                        $checkedMails .= $separator . $oneMail;
                        $separator = "\n";
                    }
                }
            }
            if( $checkedMails == '' ) {
                $datas = $this->linker->user->getData();
                $checkedMails = $datas[ 'mail' ];
            }

            $this->setParam( 'command_mail', $checkedMails );

            // Manages the quantity
            $this->setParam( 'showQuantity', isset( $_POST[ 'showQuantity' ] ) );
            $this->setParam(
                'hideNullQuantityProducts', isset( $_POST[ 'hideNullQuantityProducts' ] )
            );

            // Saves the commands parts
            if( trim( $_POST[ 'command_logo' ] ) != self::$default_logo ) {
                $logo = trim( $_POST[ 'command_logo' ] );
            } else {
                $logo = '';
            }

            $this->setI18n( self::I18N_BILLFOOTER, $_POST[ 'command_footer' ] );
            $this->setI18n( self::I18N_BILLHEADLINE, $_POST[ 'command_headLine' ] );
            $this->setI18n( self::I18N_BILLHEADLINE, $_POST[ 'command_headLine' ] );
            $this->setI18n( self::I18N_BILLCUSTOMERSERVICE, $_POST[ 'command_customerService' ] );

            $this->setParam( 'command>logo', $logo );
            $this->setParam(
                'command>companyName', stripslashes( $_POST[ 'command_companyName' ] )
            );
            $this->setParam(
                'command>companyAddress', stripslashes( $_POST[ 'command_companyAddress' ] )
            );

            $this->setParam( 'billColor', $_POST[ 'billColor' ] );

            $this->setParam( 'bill_number_format', $_POST[ 'bill_number_format' ] );

            // Finaly writes the params
            $this->writeParams();

            if( $renewSitemap ) {
                $this->linker->sitemap->renew();
            }
        }

        if( $this->getParam( 'activateShop', true ) === true ) {
            $values[ 'activateShop' ][ 'checked' ] = 'checked';
        }
        if( $this->linker->payment->getActivePaymentModesCount() > 0 ) {
            if( $this->getParam( 'selling>activated' ) === true ) {
                $values[ 'sellingActivated' ][ 'checked' ] = 'checked';
            }
        } else {
            $values[ 'sellingActivated' ][ 'checked' ] = 'disabled';
        }

        if( $this->getParam( 'forceUserToCheckConditions', true ) ) {
            $values[ 'forceUserToCheckConditions' ][ 'checked' ] = 'checked';
        }
        $values[ 'conditions' ][ 'file' ] = $this->getParam( 'conditions', '' );

        $values[ 'command_mail' ][ 'value' ] = $this->getParam( 'command_mail' );

        if( $this->getParam( 'showQuantity' ) === true ) {
            $values[ 'showQuantity' ][ 'checked' ] = 'checked';
        }

        if( $this->getParam( 'hideNullQuantityProducts' ) === true ) {
            $values[ 'hideNullQuantityProducts' ][ 'checked' ] = 'checked';
        }
        $values[ 'command' ][ 'bill_number_format' ] = $this->getParam( 'bill_number_format',
                                                                        '[YEAR4][MONTH2][DAYOFMONTH2]-[INCREMENT3]' );

        // Monney format
        $monneyFormats = $this->getParam( 'monney_formats_listing' );
        $monneyFormat = $this->getParam( 'monney_format', array( ) );
        foreach( array_keys( $monneyFormats ) as $name ) {
            if( $name == $monneyFormat ) {
                $state = 'selected';
            } else {
                $state = '';
            }
            $values[ 'monneyFormats' ][ ] = array(
                'name' => $name,
                'state' => $state
            );
        }

        // Currency
        $currency = $this->getParam( 'currency' );
        $currencies = $this->getParam( 'currencies', array( ) );
        foreach( $currencies as $cName => $cValue ) {
            if( $cName == $currency ) {
                $state = 'selected';
            } else {
                $state = '';
            }
            $values[ 'currencies' ][ ] = array(
                'id' => $cName,
                'name' => $cValue[ 'name' ],
                'value' => $cValue[ 'name' ] . ' (' . $cValue[ 'symbol' ] . ')',
                'state' => $state
            );
        }

        // Taxes
        $values[ 'taxes' ][ 0 ][ 'value' ] = 'TTC';
        $values[ 'taxes' ][ 0 ][ 'text' ] = $this->getI18n( 'prices_showTTC' );
        $values[ 'taxes' ][ 1 ][ 'value' ] = 'HT';
        $values[ 'taxes' ][ 1 ][ 'text' ] = $this->getI18n( 'prices_showHT' );
        if( $this->getParam( 'taxes', 'TTC' ) === 'HT' ) {
            $values[ 'taxes' ][ 1 ][ 'selected' ] = 'selected';
            $ht = true;
        } else {
            $values[ 'taxes' ][ 0 ][ 'selected' ] = 'selected';
        }
        $values[ 'tax' ][ 'rate' ] = $this->getParam( 'taxRate', 19.6 );
        if( $this->getParam( 'showTaxSymbol', false ) ) {
            $values[ 'showTaxSymbol' ][ 'state' ] = 'checked';
        }
        if( $this->getParam( 'paymentRequiresConnexion', true ) ) {
            $values[ 'paymentRequiresConnexion' ][ 'state' ] = 'checked';
        }

        $values[ 'activateMail' ][ 'checked' ] = 'disabled';
        $values[ 'activateCom' ][ 'checked' ] = 'disabled';

        $billColors = $this->getParam( 'billColors' );
        foreach( $billColors as $id => $billColor ) {
            $values[ 'billColors' ][ ] = array(
                'id' => $id,
                'color' => dechex( $billColor[ 0 ] ) . dechex( $billColor[ 1 ] )
                . dechex( $billColor[ 2 ] )
            );
        }
        $values[ 'bill' ][ 'color' ] = $this->getParam( 'billColor', 0 );

        // Saves the commands parts
        $logo = $this->getParam( 'command>logo', '' );

        $values[ 'command' ][ 'logo' ] = $logo;

        $values[ 'command' ][ 'companyName' ] = $this->getParam(
            'command>companyName'
        );
        $values[ 'command' ][ 'companyAddress' ] = $this->getParam(
            'command>companyAddress'
        );

        $values[ 'billForm' ][ 'bottomText' ] = $this->getParam( 'billBottomText' );
        $this->render( 'edit_params', $values );
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

    //////////////////////////////////////////////////////////////////////////////////////
    //                                  CATEGORIES PART                                 //
    //////////////////////////////////////////////////////////////////////////////////////

    protected function getNewCategoryId() {
        $this->db_execute( 'category_create', array( ), $qry );
        $id = $this->db_insertId();
        return $id;
    }

    protected function getCategoryDatas( $id ) {
        list($category) = $this->db_execute( 'category_get', array( 'id' => $id ) );
        return $category;
    }

    protected function category_setChildrenDeepness( $category_id, $category_deepness ) {
        $children = $this->db_execute( 'category_get_children', array( 'parent' => $category_id ), $qry );
        if( is_array( $children ) ) {
            foreach( $children as $child ) {
                // We verify if the children has any other parent that this one
                $parents = $this->db_execute(
                    'category_set_deepness',
                    array( 'parent' => $category_id, 'id' => $child[ 'id' ], 'deepness' => $category_deepness + 1 )
                );
                // We also update this category's children
                $this->category_setChildrenDeepness( $child[ 'id' ], $category_deepness + 1 );
            }
        }
    }

    protected function saveCategory( $datas ) {
        $id = $datas[ 'id' ];

        // We first save the category itself
        $this->db_execute( 'category_save', $datas );

        // We then update the category's categories
        //1 -> removing the category from the ordering table
        $this->db_execute( 'categories_remove_category', array( 'id' => $id ), $qry );
        $this->db_execute( 'category_unset_parents', array( 'id' => $id ) );

        //2 -> getting the smaller deepness of the new parents and inserting the category
        $smallerParentDeepness = 1000;
        $smallerParentId = 0;
        foreach( $datas[ 'categories' ] as $parentCategory ) {
            list($newParentsDeepness) = $this->db_execute( 'category_get_deepness', array( 'id' => $parentCategory ),
                                                           $qry );
            if( $newParentsDeepness[ 'deepness' ] < $smallerParentDeepness ) {
                $smallerParentId = $parentCategory;
                $smallerParentDeepness = $newParentsDeepness[ 'deepness' ];
            }
            $this->db_execute( 'category_set_parent',
                               array( 'id' => $id, 'parent' => $parentCategory, 'deepness' => $newParentsDeepness[ 'deepness' ] + 1 ),
                               $qry );
            $this->db_execute( 'category_add_category', array( 'id' => $parentCategory ), $qry );
        }
        $deepness = $smallerParentDeepness + 2;

        //3 -> changing the deepness of all this category's children (if any) recursively
        $this->category_setChildrenDeepness( $id, $deepness );
        return true;
    }

    /**
     * Gets all the categories containing no products
     * @return array An array of that categories
     */
    protected function getCategoriesCategories() {
        $categoriesList = $this->db_execute( 'categories_get_containing_categories', array( ) );
        $ret = array( );
        foreach( $categoriesList as $category ) {
            $ret[ ] = $category[ 'id' ];
        }
        return $ret;
    }

    /**
     * Tells whether the category $category is a descendant of the category $parent.<br />
     * Note : a category is considered belonging to its own descendency, so <br />
     * category_isDescendantOf($a,$a) always returns true.
     * @staticvar array $checked Returns cacher
     * @param int $category The category to check
     * @param int $children The category that could be an ancestor of $category
     * @return bool Excplicit
     */
    public function category_isDescendantOf( $category, $parent ) {
        static $checked = array( );
        if( isset( $checked[ $category ][ $parent ] ) ) {
            return $checked[ $category ][ $parent ];
        }
        if( $parent == 0 || $parent == 1 || $category == $parent ) {
            $checked[ $category ][ $parent ] = true;
            return true;
        }
        $children = $this->db_execute( 'category_get_children', array( 'parent' => $parent ), $qry );
        $ret = false;
        if( is_array( $children ) ) {
            foreach( $children as $child ) {
                if( $child[ 'parent' ] == $category ) {
                    $ret = true;
                    break;
                }
                $ret = $ret || $this->category_isDescendantOf( $category, $child[ 'id' ] );
                if( $ret ) {
                    break;
                }
            }
        }

        $checked[ $category ][ $parent ] = $ret;
        return $ret;
    }

    /**
     * public function editCategory
     *
     */
    public function editCategory() {
        // We first verify that we are at least administrator
        $this->onlyAdmin( true );

        // We get the id
        $id = $this->linker->path->page[ 'id' ];
        // We verify if we have to save the results of a submitted form
        if( $this->formSubmitted( 'categoryEditor' ) ) {
            if( $id == 0 ) {
                // Case of a new category
                $id = $this->getNewCategoryId();

                $name = $this->setI18n( 0, $_POST[ 'name' ] );
                $description = $this->setI18n( 0, $_POST[ 'description' ] );
                $shortDescription = $this->setI18n( 0, $_POST[ 'shortDescription' ] );
                $values[ 'message' ][ 'content' ] = $this->getI18n(
                    'category_added_successfully'
                );
                $values[ 'message' ][ 'link' ] = $this->linker->path->getLink(
                    'shop/showCategory/' . $id
                );
                $i18nTitleBar = $this->setI18n( 0, $_POST[ 'seo_titleBar' ] );
                $i18nMetaDescription = $this->setI18n( 0, $_POST[ 'seo_metaDescription' ] );
            } else {
                // Case of an existing category
                $this->removeFromSitemap( 'shop/showCategory/' . $id );
                $category = $this->getCategoryDatas( $id );

                $name = $this->setI18n( $category[ 'name' ], $_POST[ 'name' ] );
                $description = $this->setI18n(
                    $category[ 'description' ], $_POST[ 'description' ]
                );
                $shortDescription = $this->setI18n(
                    $category[ 'shortDescription' ], $_POST[ 'shortDescription' ]
                );

                $i18nTitleBar = $this->setI18n( ( int ) $category[ 'seo_titleBar' ], $_POST[ 'seo_titleBar' ] );
                $i18nMetaDescription = $this->setI18n( ( int ) $category[ 'seo_metaDescription' ],
                                                       $_POST[ 'seo_metaDescription' ] );
                $redirectPage = 'shop/showCategory/' . $id;
                $redirect = true;
            }

            // Updates searcher's entries
            $this->search_removeEntry( 'showCategory', $id );
            if( isset( $_POST[ 'active' ] ) ) {
                $this->search_addEntry(
                    'showCategory', $id, $_POST[ 'name' ], $_POST[ 'shortDescription' ], $_POST[ 'description' ]
                );
                $this->addToSitemap( 'shop/showCategory/' . $id, 0.8 );
                $active = 'TRUE';
            } else {
                $active = 'FALSE';
            }

            // We save the category's datas
            if( !is_array( $_POST[ 'category_category' ] ) ) {
                $_POST[ 'category_category' ] = array( );
            }
            $categoryNewDatas = array(
                'id' => $id,
                'name' => $name,
                'description' => $description,
                'shortDescription' => $shortDescription,
                'image' => $_POST[ 'image' ],
                'seo_titleBar' => $i18nTitleBar,
                'seo_metaDescription' => $i18nMetaDescription,
                'categories' => array_keys( $_POST[ 'category_category' ] ),
                'active' => $active,
                'layout' => $_POST[ 'layout' ] > 0 ? $_POST[ 'layout' ] : 1
            );

            $this->saveCategory( $categoryNewDatas );

            // We save the discounts, if any
            $this->db_execute( 'category_removeDiscounts', array( 'category_id' => $id ) );

            if( $_POST[ 'discounts' ] == 'selected' && is_array( $_POST[ 'discount' ] ) ) {
                foreach( $_POST[ 'discount' ] as $value ) {
                    if( $value != 'none' ) {
                        $this->db_execute(
                            'category_addDiscount', array( 'category_id' => $id, 'discount_id' => $value )
                        );
                    }
                }
            }

            $this->cachePrices();

            if( $redirect == true ) {
                $this->linker->path->redirect( $this->linker->path->getLink( $redirectPage ) );
            }
            // End of the treatments of the submitted form
        }

        $layouts = $this->db_execute( 'layouts_get_all' );
        foreach( $layouts as $layout ) {
            $values[ 'layouts' ][ $layout[ 'id' ] ] = $layout;
        }

        $values[ 'discounts' ] = $this->db_execute( 'discounts_get_all_available', $replacements );
        if( $id == 0 ) {
            // We are creating a new category, so we give the default values
            $values[ 'category' ] = array(
                'image' => '/images/shared/default/defaultShopImage.png',
                'onClickReplaceImage' => $this->linker->browser->getOnClickReplaceImage(
                    'image', SH_IMAGES_FOLDER . 'shop/categories/'
                )
            );
            $values[ 'category' ][ 'active' ] = 'checked';
            $values[ 'discounts_none' ][ 'state' ] = 'checked';
        } else {
            $category = $this->getCategoryDatas( $id );

            // The category should already exist, so we get its values if we can
            if( !$category ) {
                return $this->notFound();
            }

            $values[ 'discounts_none' ][ 'state' ] = 'checked';
            $discounts = $this->db_execute( 'category_getDiscounts', array( 'category_id' => $id ) );
            if( !empty( $discounts ) ) {
                $cpt = 1;
                foreach( $discounts as $discount ) {
                    $enabledDiscounts[ ] = array(
                        'input' => 'discount_' . $cpt,
                        'value' => $discount[ 'discount_id' ]
                    );
                    $cpt++;
                }
                $values[ 'discounts_none' ][ 'state' ] = '';
                $values[ 'discounts_those' ][ 'state' ] = 'checked';
                $values[ 'discountValues' ][ 'json' ] = json_encode( $enabledDiscounts );
            }

            $values[ 'category' ] = array(
                'name' => $category[ 'name' ],
                'description' => $category[ 'description' ],
                'shortDescription' => $category[ 'shortDescription' ],
                'image' => $category[ 'image' ],
                'onClickReplaceImage' => $this->linker->browser->getOnClickReplaceImage(
                    'image', SH_IMAGES_FOLDER . 'shop/categories/'
                ),
                'seo_titleBar' => $category[ 'seo_titleBar' ],
                'seo_metaDescription' => $category[ 'seo_metaDescription' ],
                'active' => ($category[ 'active' ] ? 'checked' : ''),
                'layout' => ($category[ 'layout' ] > 0 ? $category[ 'layout' ] : 1)
            );
        }
        // We set the inputs names, not to have to take'em from the .rf.xml
        $values[ 'inputs' ] = array(
            'name' => 'name',
            'description' => 'description',
            'shortDescription' => 'shortDescription',
            'active' => 'active',
            'image' => 'image'
        );
        if( $id != 1 ) {
            // We don't do anything on #1, because isn't owned by any other category
            $values[ 'category' ][ 'isowned' ] = 'true';
            // We also have to list the possible parents of the category
            // So we get its reel parents (if it has some)
            $parents = $this->db_execute( 'category_get_parents', array( 'id' => $id ) );
            $categoryCategories = array( );
            $lookForDescendants = false;
            foreach( $parents as $parent ) {
                $lookForDescendants = true;
                $categoryCategories[ ] = $parent[ 'parent' ];
            }

            // And we get the possible parents
            $categoriesCategories = $this->getCategoriesCategories();
            $results[ 'chain_0001' ] = array(
                'id' => 1,
                'text' => $this->getCategoryName( 1 ),
                'state' => $state
            );

            foreach( $categoriesCategories as $category ) {
                // We create the pathes (cat1 > cat1.1 > cat1.1.1, etc)
                if( $category != $id ) {
                    $categoryId = $category;
                    $cpt = 0;
                    $message = '';
                    list($parentCategory) = $this->db_execute( 'category_get_shortest_parent',
                                                               array( 'id' => $categoryId ) );
                    $chain = $this->getCategoryName( $category );
                    $chainUid = str_pad( $category, 4, '0', STR_PAD_LEFT );
                    $state = '';
                    while( $parentCategory[ 'parent' ] != 0 ) {
                        $chain = $this->getCategoryName( $parentCategory[ 'parent' ] ) . '>' . $chain;
                        $chainUid = str_pad( $parentCategory[ 'parent' ], 4, '0', STR_PAD_LEFT ) . '_' . $chainUid;

                        list($parentCategory) = $this->db_execute( 'category_get_shortest_parent',
                                                                   array( 'id' => $parentCategory[ 'parent' ] ) );
                        $cpt++;
                    }
                    if( $lookForDescendants && $this->category_isDescendantOf( $category, $id ) ) {
                        $state = 'disabled';
                        $message = true;
                    }
                    if( in_array( $category, $categoryCategories ) ) {
                        $state = 'checked';
                    }
                    $results[ 'chain_' . $chainUid ] = array(
                        'id' => $category,
                        'text' => $chain,
                        'state' => $state,
                        'message' => $message
                    );
                }
            }
            ksort( $results );
            $values[ 'categories' ] = $results;
        }
        $values[ 'layouts' ][ $values[ 'category' ][ 'layout' ] ][ 'state' ] = 'selected';
        // We render the form
        $this->render( 'edit_category', $values );
    }

    protected function get_category_contents( $id ) {
        list($category) = $this->db_execute( 'category_get', array( 'id' => $id ) );

        list($layout) = $this->db_execute( 'layout_get', array( 'id' => $category[ 'layout' ] ) );

        $thisCategory[ 'category' ][ 'layout' ] = $category[ 'layout' ];
        $thisCategory[ 'category' ][ 'layout_top' ] = $this->getI18n( $layout[ 'top' ] );
        $thisCategory[ 'category' ][ 'layout_bottom' ] = $this->getI18n( $layout[ 'bottom' ] );

        $thisCategory[ 'category' ][ 'active' ] = $category[ 'active' ];

        $thisCategory[ 'category' ][ 'name' ] = $this->getI18n( $category[ 'name' ] );
        $thisCategory[ 'category' ][ 'description' ] = $this->getI18n(
            $category[ 'description' ]
        );
        $thisCategory[ 'category' ][ 'shortDescription' ] = $this->getI18n(
            $category[ 'shortDescription' ]
        );
        $thisCategory[ 'category' ][ 'image' ] = $category[ 'image' ];

        // SEO
        $thisCategory[ 'category' ][ 'seo_titleBar' ] = $this->getI18n(
            $category[ 'seo_titleBar' ]
        );
        $thisCategory[ 'category' ][ 'seo_metaDescription' ] = $this->getI18n(
            $category[ 'seo_metaDescription' ]
        );

        list($parents) = $this->db_execute( 'category_get_shortest_parent', array( 'id' => $id ) );
        if( isset( $parents[ 'id' ] ) ) {
            $thisCategory[ 'category' ][ 'parent' ] = $parents;
        }

        $categoryDiscounts = $this->db_execute( 'category_getDiscounts', array( 'category_id' => $id ) );
        $discounts = array( );
        foreach( $categoryDiscounts as $discount ) {
            $discounts[ $discount[ 'discount_id' ] ] = $discount[ 'discount_id' ];
        }

        foreach( $discounts as $discount ) {
            list($discountDatas) = $this->db_execute( 'discount_get', array( 'id' => $discount ) );
            $thisCategory[ 'category' ][ 'hasDiscounts' ] = true;
            $thisCategory[ 'discounts' ][ ] = array(
                'title' => $this->getI18n( $discountDatas[ 'title' ] ),
                'description_categories' => $this->getI18n( $discountDatas[ 'description_categories' ] ),
                'description_product' => $this->getI18n( $discountDatas[ 'description_product' ] )
            );
        }

        return $thisCategory;
    }

    public function getSubmenus( $method, $id ) {
        $ret = array( );
        if( $method == 'showCategory' ) {
            // We get the category's datas
            if( !$this->categoryExists( $id ) ) {
                return $ret;
            }
            if( $this->get_category_contents_type( $id ) == self::CATEGORIES_CATEGORY ) {
                // We get the category's datas
                $category = $this->get_category_contents( $id );
                $parent = $category[ 'category' ][ 'parent' ];

                $elements = $this->get_category_elements( $id );
                if( is_array( $elements[ 'category_elements' ] ) ) {
                    foreach( $elements[ 'category_elements' ] as $id => &$element ) {
                        $ret[ $id ] = array(
                            'title' => $element[ 'name' ],
                            'link' => $element[ 'link' ]
                        );
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * Shows the category asked in the url
     * @return bool The status of the function
     */
    public function showCategory() {
        if( !$this->activateShop ) {
            $this->linker->path->error( 404 );
        }
        //$isMobile = $this->linker->session->checkIfIsMobileDevice();
        // We get the category's datas
        $id = $this->linker->path->page[ 'id' ];

        // And add an entry in the command panel
        $this->linker->admin->insert(
            '<a href="' . $this->linker->path->getLink(
                $this->shortClassName . '/editCategory/' . $id
            ) . '">Modifier cette catégorie</a>', 'Boutique', 'picto_modify.png'
        );

        // We get the first element to show (-1 to start with 0)
        if( isset( $_GET[ 'page' ] ) ) {
            $page = $_GET[ 'page' ] - 1;
        }
        if( !isset( $page ) || $page < 0 ) {
            $page = 0;
        }

        // We get the category's datas
        $category = $this->get_category_contents( $id );
        $layout = $category[ 'layout' ];

        // We send an error if the category doesn't exist (or isn't activated)
        if( !$category ) {
            return $this->notFound();
        }
        if( $category[ 'category' ][ 'active' ] == '0' ) {
            if( $this->isAdmin() ) {
                $this->linker->html->addMessage( $this->getI18n( 'categoryShownBecauseAdmin' ), false );
            } else {
                return $this->notFound();
            }
        }
        $parent = $category[ 'category' ][ 'parent' ];

        $elements = $this->get_category_elements( $id, $page, $number );

        // SEO
        if( empty( $category[ 'category' ][ 'seo_titleBar' ] ) ) {
            $category[ 'category' ][ 'seo_titleBar' ] = $category[ 'category' ][ 'name' ];
        }
        $this->linker->html->setMetaTitle( $category[ 'category' ][ 'seo_titleBar' ] );

        if( empty( $category[ 'category' ][ 'seo_metaDescription' ] ) ) {
            $category[ 'category' ][ 'seo_metaDescription' ] = $category[ 'category' ][ 'shortDescription' ];
        }
        $this->linker->html->setMetaDescription( $category[ 'category' ][ 'seo_metaDescription' ] );

        // We ask for the category bar
        $bar = $this->get_category_bar( $id );

        // And set the title
        $this->linker->html->setTitle( $category[ 'category' ][ 'name' ] );

        // We render the file using the desired renderer
        if( $elements[ 'contains_products' ] ) {
            // We manage with different types of list
            $listType = $elements[ 'listType' ];
            if( $isMobile ) {
                $rf = 'mobile_products_list';
                if( !empty( $parentCategories ) ) {
                    $category[ 'category' ][ 'parentLink' ] = $this->linker->path->getLink( '/showCategory/' . $parent );
                }
            } else {
                $rf = 'default_products_' . $listType;
            }
            // Get the list types
            // We now merge all these values to send them to the renderer
            $listTypes = $this->get_type_selector();
            $values = array_merge( $elements, $listTypes, $bar, $category );
        } else {
            if( $isMobile ) {
                $rf = 'mobile_categories';
                if( !empty( $parentCategories ) ) {
                    $category[ 'category' ][ 'parentLink' ] = $this->linker->path->getLink( '/showCategory/' . $parent );
                }
            } else {
                $rf = 'default_categories';
            }
            // We now merge all these values to send them to the renderer
            $values = array_merge( $elements, $bar, $category );
        }

        $values[ 'buy' ][ 'action' ] = $this->linker->path->getLink( __CLASS__ . '/add_to_cart_ajax/' );

        $this->render( $rf, $values );

        // Some templates need an image from out of the content part.
        //This is it...
        $this->linker->html->setGeneralImage( $values[ 'category' ][ 'image' ] );
        return true;
    }

    protected function getCategoriesListingParams() {
        $params = $this->getParam( 'categoriesListing' );

        if( is_array( $params ) ) {
            return $params;
        }
        return $this->getParam( 'categoriesListing' );
    }

    protected function getProductsListingParams() {
        $params = $this->getParam();
        if( $this->linker->path->page[ 'action' ] == 'showProduct' ) {
            return array(
                'product' => $this->getParam( 'sh_shop>product|product', array( ) )
            );
        }

        $params = $this->getParam( 'sh_shop>productsListing|productsListing', array( ) );

        if( is_array( $params ) ) {
            return $params;
        }
        return $this->getParam( 'sh_shop>productsListing|productsListing', array( ) );
    }

    protected function getProductListType() {
        if( $this->linker->path->page[ 'action' ] == 'showProduct' ) {
            return 'product';
        }
        $arg_prodLType = self::ARG_PRODUCTLISTTYPE;
        if( isset( $_GET[ $arg_prodLType ] ) && !empty( $_GET[ $arg_prodLType ] ) ) {
            $this->setProductListType( $_GET[ $arg_prodLType ] );
        }
        if( is_null( $this->listType ) ) {
            if( !isset( $_SESSION[ __CLASS__ ][ $arg_prodLType ] ) ) {
                $this->setProductListType();
            }
            $this->listType = $_SESSION[ __CLASS__ ][ $arg_prodLType ];
        }
        return $this->listType;
    }

    protected function setProductListType( $type = 'default' ) {
        $arg_prodLType = self::ARG_PRODUCTLISTTYPE;
        $params = $this->getProductsListingParams();
        if( $type == 'default' || !is_int( $params[ $type ][ 'productsNumber' ] ) ) {
            $type = $params[ 'default' ];
            $_SESSION[ __CLASS__ ][ $arg_prodLType ] = $type;
            $this->listType = $type;
        } else {
            $this->listType = $type;
            $_SESSION[ __CLASS__ ][ $arg_prodLType ] = $type;
            return true;
        }
        return false;
    }

    //////////////////////////////////////////////////////////////////////////////////////
    //                                  PRODUCTS PART                                   //
    //////////////////////////////////////////////////////////////////////////////////////

    protected function getPriceExplanation( $product, $variant = 0, $quantity = 1 ) {
        if( !is_array( $product ) ) {
            $id = $product;
            list($product) = $this->db_execute( 'product_get', array( 'id' => $id ) );
        } else {
            $id = $product[ 'id' ];
        }
        /* Setting the prices */
        if( !$product[ 'hasVariants' ] || !$product[ 'variants_change_price' ] ) {
            // variants don't change the price
            list($price) = $this->db_execute(
                'product_get_price', array( 'product' => $id, 'quantity' => $quantity )
            );
        } else {
            // variants change the price
            list($price) = $this->db_execute(
                'variant_get_price', array( 'product' => $id, 'quantity' => $quantity, 'variant' => $variant )
            );
            $productDatas[ 'product' ][ 'variantsChangePrice' ] = true;
        }
        if( $price[ 'discount_id' ] > 0 ) {
            list($normalPrice) = $this->db_execute(
                'product_get_normal_price', array( 'id' => $id), $qry
            );
            if( $normalPrice[ 'hasVariants' ] && $normalPrice[ 'variants_change_price' ] ) {
                list($normalPrice) = $this->db_execute(
                    'variant_get_normal_price', array( 'product_id' => $id, 'variant_id' => $variant ), $qry
                );
            }
        }
        if( $quantity > 1 ) {
            $productDatas[ 'active' ][ 'priceFor1' ] = $this->monney_format(
                $price[ 'price' ]
            );
            if( $price[ 'discount_id' ] > 0 ) {
                $productDatas[ 'active' ][ 'normalPriceFor1' ] = $this->monney_format(
                    $normalPrice[ 'price' ]
                );
            }
        }
        $productDatas[ 'active' ][ 'priceForAll' ] = $this->monney_format(
            $quantity * $price[ 'price' ]
        );
        if( $price[ 'discount_id' ] > 0 ) {
            $productDatas[ 'active' ][ 'normalPriceForAll' ] = $this->monney_format(
                $quantity * $normalPrice[ 'price' ]
            );
            $productDatas[ 'active' ][ 'reduction' ] = round( 100 - $price[ 'price' ] / $normalPrice[ 'price' ] * 100 ) . '%';
            if( $productDatas[ 'active' ][ 'reduction' ] > 0 ) {
                $productDatas[ 'active' ][ 'thereIsAReduction' ] = true;
            }
        }
        $ret = $this->render( 'priceExplanation', $productDatas, false, false );
        return $ret;
    }

    /**
     * Gets all the datas stored for a product.
     * @param int $id The id of the product
     * @return array An array containing all the product's data
     */
    protected function get_product_contents( $id, $variant = 0, $quantity = 1 ) {
        list($product) = $this->db_execute( 'product_get', array( 'id' => $id ) );
        list($layout) = $this->db_execute( 'layout_get', array( 'id' => $product[ 'layout' ] ) );

        if( $product[ 'pack_id' ] > 0 ) {
            return array(
                'rep' => self::PRODUCT_IS_A_PACK,
                'pack_id' => $product[ 'pack_id' ]
            );
        }
        $product[ 'rep' ] = true;

        list($smallestPrice) = $this->db_execute( 'product_get_smallest_price', array( 'product' => $id ) );

        if( $smallestPrice[ 'discount_id' ] == 0 ) {
            $hasDiscount = false;
            if( $product[ 'hasVariants' ] && $product[ 'variants_change_price' ] ) {
                $price = $this->getI18n( 'product_price_from_before' );
                $price .= $this->monney_format( $smallestPrice[ 'price' ], true, false );
                $price .= $this->getI18n( 'product_price_from_after' );
            } else {
                $price = $this->monney_format( $smallestPrice[ 'price' ], true, false );
            }
        } else {
            $hasDiscount = true;

            $price = $this->getI18n( 'product_price_from_before' );
            $price .= $this->monney_format( $smallestPrice[ 'price' ], true, false );
            $price .= $this->getI18n( 'product_price_from_after' );

            // We prepare the discounts that may be applied to this product
            $productDiscounts = $this->db_execute( 'product_getDiscounts', array( 'product_id' => $id ) );
            foreach( $productDiscounts as $discount ) {
                $discounts[ $discount[ 'discount_id' ] ] = $discount[ 'discount_id' ];
            }
            $categories = $this->db_execute( 'product_get_categories', array( 'product_id' => $id ) );

            foreach( $categories as $category ) {
                $categoryDiscounts = $this->db_execute( 'category_getDiscounts',
                                                        array( 'category_id' => $category[ 'category_id' ] ) );
                foreach( $categoryDiscounts as $discount ) {
                    $discounts[ $discount[ 'discount_id' ] ] = $discount[ 'discount_id' ];
                }
            }

            foreach( $discounts as $discount ) {
                list($discountDatas) = $this->db_execute( 'discount_get', array( 'id' => $discount ) );
                $productDatas[ 'discounts' ][ ] = array(
                    'title' => $this->getI18n( $discountDatas[ 'title' ] ),
                    'description_categories' => $this->getI18n( $discountDatas[ 'description_categories' ] ),
                    'description_product' => $this->getI18n( $discountDatas[ 'description_product' ] )
                );
            }
        }

        $productDatas[ 'product' ] = array(
            'id' => $id,
            'name' => $this->getI18n( $product[ 'name' ] ),
            'reference' => $product[ 'reference' ],
            'description' => $this->getI18n( $product[ 'description' ] ),
            'shortDescription' => $this->getI18n( $product[ 'shortDescription' ] ),
            'seo_titleBar' => $this->getI18n( $product[ 'seo_titleBar' ] ),
            'seo_metaDescription' => $this->getI18n( $product[ 'seo_metaDescription' ] ),
            'image' => $product[ 'image' ],
            'images' => explode( '|', $product[ 'images' ] ),
            'price' => $price,
            'hasDiscount' => $hasDiscount,
            'hasVariants' => $product[ 'hasVariants' ]
        );
        $productDatas[ 'product' ][ 'layout' ] = $product[ 'layout' ];
        $productDatas[ 'product' ][ 'layout_top' ] = $this->getI18n( $layout[ 'top' ] );
        $productDatas[ 'product' ][ 'layout_bottom' ] = $this->getI18n( $layout[ 'bottom' ] );

        $cpUsedForVariants = array( );
        $productDatas[ 'active' ][ 'quantity' ] = $quantity;

        $productDatas[ 'price' ][ 'explanations' ] = $this->getPriceExplanation( $product, $variant, $quantity );
        /* prices set */

        // We manage the variants
        if( $product[ 'hasVariants' ] ) {
            $productDatas[ 'product' ][ 'hasVariants' ] = true;
            if( $product[ 'variants_change_price' ] ) {
                $productDatas[ 'product' ][ 'price_is_from' ] = true;
            }
            $variants = $this->db_execute( 'product_get_variants', array( 'product_id' => $id ), $qry );

            if( $product[ 'variants_change_ref' ] ) {
                $productDatas[ 'product' ][ 'variants_change_ref' ] = true;
            }
            $hideNullQuantityProducts = $this->getParam( 'hideNullQuantityProducts', false );
            $variantsDatas = array( );
            $variantsTypes = array( );
            foreach( $variants as $variantId => $variant ) {
                $showLink = true;
                if( $product[ 'variants_change_stock' ] && !$variant[ 'stock' ] ) {
                    if( $hideNullQuantityProducts ) {
                        continue;
                    }
                    $showLink = false;
                }

                //$uid = $variant['customProperties'];
                $separatorTitle = '';
                $separatorName = '';
                $variantsDatas = array( );
                $variantsDatas[ 'name' ] = '';
                $variantsDatas[ 'title' ] = '';
                $variantsDatas[ 'id' ] = $variant[ 'variant_id' ];

                if( $product[ 'variants_change_ref' ] ) {
                    $variantsDatas[ 'title' ] .= $separatorTitle . $variant[ 'ref' ];
                    $separatorTitle = ' - ';
                }
                $cpVariants = explode( '|', $variant[ 'customProperties' ] );
                foreach( $cpVariants as $oneCpVariant ) {
                    list($oneCpVariantId, $oneCpVariantValue) = explode( ':', $oneCpVariant );
                    list($variantFieldName) = $this->db_execute( 'customProperty_get_name',
                                                                 array( 'id' => $oneCpVariantId ) );
                    if(!isset($variantsTypes[ $oneCpVariantId ])){
                        $variantsTypes[ $oneCpVariantId ] = array(
                            'name' => $this->getI18n( $variantFieldName[ 'name' ] ) ,
                            'value' => $oneCpVariantId,
                        );
                    }
                    $variantsTypes[ $oneCpVariantId ]['values'][$oneCpVariantValue] = array(
                        'name' => $this->getI18n( $oneCpVariantValue ),
                        'value' => $oneCpVariantValue
                    );

                    $variantsDatas[ 'title' ] .= $separatorTitle . $this->getI18n( $variantFieldName[ 'name' ] ) . ' : ' . $this->getI18n( $oneCpVariantValue );
                    $variantsDatas[ 'name' ] .= $separatorName . $this->getI18n( $variantFieldName[ 'name' ] ) . ' : ' . $this->getI18n( $oneCpVariantValue );
                    $separatorTitle = ' - ';
                    $separatorName = ' - ';
                }
                if( $product[ 'variants_change_price' ] ) {
                    list($price) = $this->db_execute(
                        'variant_get_smallest_price', array( 'product' => $id, 'variant' => $variantId )
                    );

                    if( $price[ 'discount_id' ] > 0 ) {
                        $price[ 'price' ] = 'A partir de ' . $this->monney_format(
                                $price[ 'price' ]
                        );
                    } else {
                        $price[ 'price' ] = $this->monney_format(
                            $price[ 'price' ]
                        );
                    }
                    $variantsDatas[ 'name' ] .= $separatorName . $price[ 'price' ];
                    $variantsDatas[ 'title' ] .= $separatorTitle . $price[ 'price' ];
                    $separatorTitle = ' - ';
                    $separatorName = ' - ';
                }
                if( $showLink ) {
                    $productDatas[ 'variants' ][ $variantsDatas[ 'id' ] ] = $variantsDatas;
                } else {
                    $productDatas[ 'variants_noStock' ][ $variantsDatas[ 'id' ] ] = $variantsDatas;
                }
            }
            if( is_array( $productDatas[ 'variants' ] ) ) {
                ksort( $productDatas[ 'variants' ] );
            }
        }
        $productDatas['variantsTypes'] = $variantsTypes;
        $productDatas['shop']['page'] = $this->linker->path->getLink( __CLASS__ . '/showProduct/' . $id );

        // We manage the custom properties, if any
        $customProperties = $this->db_execute( 'product_get_customProperties_withStructure',
                                               array( 'product_id' => $id ) );
        if( is_array( $customProperties ) ) {
            foreach( $customProperties as $value ) {
                if( !in_array( $value[ 'customProperty_id' ], $cpUsedForVariants ) && $value[ 'customProperty_value' ] != self::CUSTOMPROPERTIES_EMPTY_DB ) {
                    $productDatas[ 'customProperties' ][ ] = array(
                        'name' => $this->getI18n( $value[ 'name' ] ),
                        'value' => $this->getI18n( $value[ 'customProperty_value' ] )
                    );
                }
            }
        }

        if( $this->sellingActivated ) {
            if( !$product[ 'hasVariants' ] && $product[ 'stock' ] > 0 ) {
                $productDatas[ 'product' ][ 'stock' ] = $product[ 'stock' ];
                $addToCartLink = $this->linker->path->getLink( 'shop/addToCart/' );
                $addToCartLink .= '?product=' . $id;
                $productDatas[ 'product' ][ 'picto_addToCart_link' ] = $addToCartLink;
            } elseif( $product[ 'hasVariants' ] ) {
                $productDatas[ 'product' ][ 'stock' ] = $this->getI18n(
                    'product_chooseVariant'
                );
            } else {
                $productDatas[ 'product' ][ 'stock' ] = $this->getI18n(
                    'product_nomorestock'
                );
                $productDatas[ 'product' ][ 'no_more_stock' ] = true;
            }
        }
        list($categories) = $this->db_execute( 'product_get_categories', array( 'product_id' => $id ) );

        $productDatas[ 'product' ][ 'parent' ] = $categories[ 'category_id' ];

        return $productDatas;
    }

    /**
     * This ajaxly called method is used to add a product to the cart, and propose
     * to stay on the page or to go to the cart.<br />
     * If javascript isn't active, the user is directly redirected to the cart without
     * a call of this method.
     * @return bool true
     */
    public function add_to_cart_ajax() {
        $this->addToCart( $_POST[ 'product' ], $_POST[ 'variant' ], $_POST[ 'quantity' ], false );
        $values[ 'links' ][ 'cart' ] = $this->linker->path->getLink(
            $this->shortClassName . '/cart_show/'
        );
        // Updating the total amounts
        $this->cart_updateTotalAmount();

        echo $this->render( 'add_to_cart_ajax', $values, false, false );
        return true;
    }

    protected function cart_updateTotalAmount() {
        $total = 0;
        $taxeIncluded = ($this->taxes == self::TAXES_INCLUDED);
        $totalPrice = new sh_price( $taxeIncluded, $this->taxRate / 100, 0 );
        if( empty( $_SESSION[ __CLASS__ ][ 'cart' ] ) ) {
            $_SESSION[ __CLASS__ ][ 'cart' ] = array( );
        }
        if( empty( $_SESSION[ __CLASS__ ][ 'cart_external' ] ) ) {
            $_SESSION[ __CLASS__ ][ 'cart_external' ] = array( );
        }
        foreach( $_SESSION[ __CLASS__ ][ 'cart' ] as $oneProduct => $quantity ) {
            list($product, $variant) = explode( '|', $oneProduct );
            if( empty( $variant ) ) {
                $variant = null;
            }
            $totalPrice->add(
                new sh_price( $taxeIncluded, $this->taxRate / 100, $quantity * $this->product_get_price( $product,
                                                                                                         $variant ) )
            );
        }
        foreach( $_SESSION[ __CLASS__ ][ 'cart_external' ] as $id => $product ) {
            $className = $product[ 'class' ];
            $shortId = $product[ 'id' ];
            $class = $this->linker->$className;
            $price = $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_PRICE );
            $tax = $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_TAXRATE );
            $totalPrice->add(
                new sh_price( $taxeIncluded, $tax / 100, $price * $product[ 'qty' ] )
            );
        }
        $price = $totalPrice->get();
        $_SESSION[ __CLASS__ ][ 'command' ][ 'total' ] = array(
            'ht' => $this->monney_format( $price[ 'untaxed' ], true, false ),
            'ttc' => $this->monney_format( $price[ 'taxed' ], true, false ),
            'paid' => $price[ 'taxed' ]
        );
    }
    
    public function shallWe_render_shopproduct( $attributes = array( ) ) {
        $this->isRenderingWEditor = $this->isRenderingWEditor || $this->linker->wEditor->isRendering();
        $rep = !$this->isRenderingWEditor;
        return $rep;
    }
    
    public function render_shopproduct($attributes){
        if( isset( $attributes[ 'id' ] ) ) {
            $id = $attributes[ 'id' ];
        } else {
            return false;
        }
        $this->linker->html->addScript( '/' . __CLASS__ . '/singles/embedded_product.js' );
        $values = $this->get_product_contents( $id, $variant, $quantity );
        foreach($values['product']['images'] as $oneImage){
            $values['images'][]['src'] = $oneImage;
        }
        $this->linker->admin->insert(
            '<a href="' . $this->linker->path->getLink( 'shop/editProduct/' . $id ) . '">Modifier "'.$id.' - '.$values['product']['name'].'"</a>',
                                                        'Boutique', 'picto_modify.png'
        );
        
        return $this->render('render_shopProduct', $values,false,false);
    }

    /**
     * Called directly by the end user.
     * Creates and shows the page of a product.
     * @return bool The status of the function
     */
    public function showProduct() {
        if( !$this->activateShop ) {
            $this->linker->path->error( 404 );
        }
        //$isMobile = $this->linker->session->checkIfIsMobileDevice();
        $this->linker->javascript->get( sh_javascript::LIGHTBOX );

        $id = $this->linker->path->page[ 'id' ];

        if( $this->formSubmitted( 'product_buy', sh_form_verifier::DONT_VERIFY_CAPTCHA, sh_form_verifier::ERASE_IF_TRUE ) ) {
            if( isset( $_POST[ 'usePack' ] ) ) {
                list($pack_id) = array_keys( $_POST[ 'usePack' ] );
                $_SESSION[ __CLASS__ ][ 'used_pack' ] = array(
                    'product' => $id,
                    'variant' => $_POST[ 'variant' ],
                    'quantity' => $_POST[ 'quantity' ]
                );
                $this->linker->path->redirect( __CLASS__, 'showPack', $pack_id );
            }
            if( isset( $_POST[ 'validate' ] ) ) {
                if( $_POST[ 'goToCart' ] == 'true' ) {
                    $this->addToCart( $id, $_POST[ 'variant' ], $_POST[ 'quantity' ], true );
                }
            }
        }

        $quantity = max( ( int ) $_POST[ 'quantity' ], ( int ) $_GET[ 'quantity' ] );
        if( !isset( $_POST[ 'variant' ] ) && !isset( $_GET[ 'variant' ] ) ) {
            $variant = 0;
        } elseif( $_GET['variant'] == 'splitted' || $_POST['variant'] == 'splitted' ){
            // We should get the variant id by its values
            $variantValues = '';$separator = '';
            foreach($_POST as $name=>$value){
                if(substr($name,0,8) == 'variant_'){
                    $variantValues .= $separator.substr($name,8).':'.$value;
                    $separator = '|';
                }
            }
            $r = $this->db_execute('variant_get_by_cp', array('product_id'=>$id,'cp'=>$variantValues));
            $variant = $r[0]['variant_id'];
        } else {
            $variant = max( ( int ) $_POST[ 'variant' ], ( int ) $_GET[ 'variant' ] );
        }
        if( $quantity <= 0 ) {
            $quantity = 1;
        }
        if( isset( $_POST[ 'ajax' ] ) ) {
            if(isset($_POST['quantityElement'])){
                $quantityElement = $_POST['quantityElement'];
            }else{
                $quantityElement = 'quantity';
            }
            echo $this->getPriceExplanation( $id, $variant, $quantity );
            echo '<script type="text/javascript">';
            echo '$("'.$quantityElement.'").value = ' . $quantity . ';' . "\n";
            echo '</script>';
            if(isset($_POST['variantElement'])){
                echo '<input type="hidden" id="'.$_POST['variantElement'].'" value="'.$variant.'"/>';
            }
            exit;
        }

        // We send an error if the product doesn't exist (or isn't activated)
        if( !$this->isProductAvailable( $id, 0 ) ) {
            return $this->notFound();
        }

        // We get the product's datas
        $product = $this->get_product_contents( $id, $variant, $quantity );
        if( $product[ 'rep' ] == self::PRODUCT_IS_A_PACK ) {
            $this->linker->path->redirect( __CLASS__, 'showPack', $product[ 'pack_id' ] );
        }

        // And add an entry in the command panel
        $this->linker->admin->insert(
            '<a href="' . $this->linker->path->getLink( 'shop/editProduct/' . $id ) . '">Modifier ce produit</a>',
                                                        'Boutique', 'picto_modify.png'
        );
        $this->linker->admin->insert(
            '<a href="' . $this->linker->path->getLink( 'shop/editProduct/0' ) . '?basedOn=' . $id . '">Dupliquer ce produit</a>',
                                                        'Boutique', 'picto_duplicate.png'
        );

        // We get the first element to show (-1 to start with 0)
        if( isset( $_GET[ 'page' ] ) ) {
            $page = $_GET[ 'page' ] - 1;
        }
        if( !isset( $page ) || $page < 0 ) {
            $page = 0;
        }

        $product[ 'variants' ][ $variant ][ 'state' ] = 'selected';
        $product[ 'product' ][ 'quantity' ] = $quantity;

        $product[ 'shop' ][ 'active' ] = $this->sellingActivated;

        $product[ 'update' ][ 'action' ] = $this->linker->path->uri . '?submitted=submitted';
        $product[ 'buy' ][ 'action' ] = $this->linker->path->getLink( __CLASS__ . '/add_to_cart_ajax/' );
        $parent = $product[ 'product' ][ 'parent' ];
        $images = $product[ 'product' ][ 'images' ];
        unset( $product[ 'images' ] );
        if( is_array( $images ) ) {
            foreach( $images as $image ) {
                if( trim( $image ) != '' ) {
                    if( $this->linker->images->imageExists( $image ) ) {
                        $product[ 'productImages' ][ ][ 'src' ] = $image;
                    }
                }
            }
        }

        // SEO
        if( empty( $product[ 'product' ][ 'seo_titleBar' ] ) ) {
            $product[ 'product' ][ 'seo_titleBar' ] = $product[ 'product' ][ 'name' ];
        }
        $this->linker->html->setMetaTitle( $product[ 'product' ][ 'seo_titleBar' ] );

        if( empty( $product[ 'product' ][ 'seo_metaDescription' ] ) ) {
            $product[ 'product' ][ 'seo_metaDescription' ] = $product[ 'product' ][ 'shortDescription' ];
        }
        $this->linker->html->setMetaDescription( $product[ 'product' ][ 'seo_metaDescription' ] );

        // We ask for the category bar
        $bar = $this->get_category_bar( $parent );

        // And add an entry in the command panel
        $this->linker->admin->insert(
            '<a href="' . $this->linker->path->getLink(
                'shop/editCategory/' . $parent
            ) . '">Modifier cette catégorie</a>', 'Boutique', 'picto_modify.png'
        );

        // We now get all the brother-products
        $categories = $this->get_category_elements( $parent, $page, $id );

        // We now merge all these values to send them to the renderer
        $values = array_merge( $categories, $bar, $product );

        // We render the product file using the desired renderer
        if( $isMobile ) {
            $rf = 'mobile_product';
            $values[ 'product' ][ 'categoryLink' ] = $this->linker->path->getLink( '/showCategory/' . $parent );
        } else {
            $rf = 'default_product';
        }

        $this->linker->html->setTitle( $values[ 'product' ][ 'name' ] );

        // Packs management
        $categories = implode( ',', $this->getProductCategories( $id ) );
        $packs = $this->db_execute( 'pack_get_contents_by_categories_or_product',
                                    array( 'categories' => $categories, 'product' => $id ) );

        if( is_array( $packs ) ) {
            foreach( $packs as $pack ) {
                $values[ 'packs_bloc' ][ 'show' ] = true;
                $values[ 'packs' ][ ] = array(
                    'id' => $pack[ 'id' ],
                    'name' => $this->getI18n( $pack[ 'name' ] )
                );
            }
        }

        $this->render( $rf, $values );

        // Some templates need an image from out of the content part.
        //This is it...
        $this->linker->html->setGeneralImage( $values[ 'product' ][ 'image' ] );
        return true;
    }

    protected function getNewProductId() {
        $this->db_execute( 'product_create', array( ), $qry );
        $id = $this->db_insertId();
        return $id;
    }

    protected function getProductDatas( $id ) {
        list($product) = $this->db_execute( 'product_get', array( 'id' => $id ), $qry );
        if( empty( $product ) ) {
            $product = false;
        }
        return $product;
    }

    protected function saveProduct( $productDatas ) {
        $id = $productDatas[ 'id' ];

        // Managing variants
        // Removing the first entries in the variants, because they are the unfilled models
        $variantsCount = 0;
        if( is_array( $productDatas[ 'variants' ] ) ) {
            foreach( $productDatas[ 'variants' ] as $varName => $variants ) {
                if( substr( $varName, 0, 8 ) == 'variant_' ) {
                    array_shift( $variants );
                    $productDatas[ 'variants' ][ $varName ] = $variants;
                    $variantsCount = max( $variantsCount, count( $variants ) );
                }
            }
        }
        // Preparing the datas that are stored within the products table
        $productDatas[ 'hasVariants' ] = isset( $productDatas[ 'variants' ][ 'hasVariants' ] );
        $productDatas[ 'variants_change_price' ] = isset( $productDatas[ 'variants' ][ 'changePrice' ] );
        $productDatas[ 'variants_change_stock' ] = isset( $productDatas[ 'variants' ][ 'changeStock' ] );
        $productDatas[ 'variants_change_ref' ] = isset( $productDatas[ 'variants' ][ 'changeRef' ] );

        // Removing old variants
        $this->db_execute( 'product_unset_variants', array( 'product_id' => $id ), $qry );
        // If there are variants, and there are changes in the price between the variants, we use the smaller
        if( $productDatas[ 'hasVariants' ] ) {
            if( $productDatas[ 'variants_change_price' ] ) {
                // We get the minimal price, and set the default price to it
                $minimal = min( $productDatas[ 'variants' ][ 'variant_price' ] );
                $productDatas[ 'price' ] = str_replace( ',', '.', $minimal );
            }
            if( $productDatas[ 'variants_change_stock' ] ) {
                // We get the total stock, and set the default stock to it
                $productDatas[ 'stock' ] = array_sum( $productDatas[ 'variants' ][ 'variant_stock' ] );
            }

            // We should also save the variants' datas
            // So we look if there are custom properties to save
            $variantsDatas = array( );
            $separator = '';
            if( is_array( $productDatas[ 'variants' ][ 'ActivatedCP' ] ) ) {
                foreach( array_keys( $productDatas[ 'variants' ][ 'ActivatedCP' ] ) as $activeCustomProperties ) {
                    $variantId = md5( microtime() );
                    foreach( $productDatas[ 'variants' ][ 'variant_' . $activeCustomProperties ] as $cpt => $variantValue ) {
                        $variantsDatas[ $cpt ][ 'customProperties' ] .= $separator . $activeCustomProperties . ':' . $variantValue;
                    }
                    $separator = '|';
                }
            }

            // Adding the new variants (if any)
            for( $cpt = 0; $cpt < $variantsCount; $cpt++ ) {
                $variantsDatas[ $cpt ][ 'price' ] = $productDatas[ 'variants' ][ 'variant_price' ][ $cpt ];
                $variantsDatas[ $cpt ][ 'stock' ] = $productDatas[ 'variants' ][ 'variant_stock' ][ $cpt ];
                $variantsDatas[ $cpt ][ 'ref' ] = $productDatas[ 'variants' ][ 'variant_ref' ][ $cpt ];
                $variantsDatas[ $cpt ][ 'product_id' ] = $id;
                $variantsDatas[ $cpt ][ 'variant_id' ] = $cpt;
                $this->db_execute( 'product_set_variant', $variantsDatas[ $cpt ] );
            }
        }

        // We save the product itself
        $this->db_execute( 'product_save', $productDatas );


        // We then update the product's categories
        $this->db_execute( 'categories_remove_product', array( 'id' => $id ) );
        $this->db_execute( 'product_unset_categories', array( 'product_id' => $id ), $qry );
        foreach( $productDatas[ 'categories' ] as $oneCategory ) {
            $this->db_execute( 'product_set_category', array( 'product_id' => $id, 'category_id' => $oneCategory ) );
            $this->db_execute( 'category_add_product', array( 'id' => $oneCategory ) );
        }

        // We update the sitemap's pages list
        $this->removeFromSitemap( 'shop/showProduct/' . $id );
        $this->addToSitemap( 'shop/showProduct/' . $id, 0.7 );

        // We save the custom properties
        $this->db_execute( 'product_unset_customProperties', array( 'product_id' => $id ) );
        //echo $qry.'<br />';
        if( is_array( $_POST[ 'customProperties' ] ) ) {
            foreach( $_POST[ 'customProperties' ] as $customPropertyId => $customPropertyValue ) {
                if( $customPropertyValue != self::CUSTOMPROPERTIES_UNSHOWN ) {
                    if( $customPropertyValue == self::CUSTOMPROPERTIES_EMPTY ) {
                        $customPropertyValue = self::CUSTOMPROPERTIES_EMPTY_DB;
                    }
                    $this->db_execute(
                        'product_set_customProperty',
                        array( 'product_id' => $id, 'customProperty_id' => $customPropertyId, 'customProperty_value' => $customPropertyValue )
                    );
                }
            }
        }


        // Removing the product from every categories
        $this->db_execute( 'product_unset_categories', array( 'product_id' => $id ) );

        // Adding the product to the selected categories and setting the categories as products containers
        foreach( $productDatas[ 'categories' ] as $category ) {
            $this->db_execute( 'product_set_category', array( 'product_id' => $id, 'category_id' => $category ) );

            $this->db_execute( 'category_set_type', array( 'type' => 'products', 'id' => $category ) );
        }
    }

    protected function getProductCategories( $product ) {
        $categories = $this->db_execute( 'product_get_categories', array( 'product_id' => $product ) );
        $ret = array( );
        foreach( $categories as $category ) {
            $ret[ ] = $category[ 'category_id' ];
        }
        return $ret;
    }

    /**
     * Gets all the categories containing no other categories
     * @return array An array of that categories (as full shortest path, like : main_cat>sub_cat1>sub_cat11)
     */
    protected function getProductsCategories( $separator = '>' ) {
        $categories = $this->db_execute( 'categories_get_containing_products', array( ) );
        $ret = array( );
        foreach( $categories as $category ) {
            $ret[ ] = $category[ 'id' ];
        }
        return $ret;
    }

    public function listDiscounts() {
        // We first verify that we are at least administrator
        $this->onlyAdmin( true );

        $discounts = $this->db_execute( 'discounts_get_all', array( ), $qry );
        foreach( $discounts as $discount ) {
            $values[ 'discounts' ][ ] = array(
                'name' => $discount[ 'name' ],
                'link' => $this->linker->path->getLink( $this->shortClassName . '/editDiscount/' . $discount[ 'id' ] )
            );
        }
        $values[ 'newDiscount' ][ 'link' ] = $this->linker->path->getLink( $this->shortClassName . '/editDiscount/0' );

        $this->render( 'listDiscounts', $values );
    }

    public function editDiscount() {
        // We first verify that we are at least administrator
        $this->onlyAdmin( true );

        // We get the id
        $id = $this->linker->path->page[ 'id' ];
        if( $this->formSubmitted( 'discountEditor' ) ) {
            $filled = true;
            if( trim( $_POST[ 'name' ] ) != '' ) {
                $values[ 'discount' ][ 'name' ] = trim( $_POST[ 'name' ] );
            } else {
                $error = true;
                $this->linker->html->addMessage( $this->getI18n( 'discount_error_needName' ) );
            }
            if( $_POST[ 'when' ] == 'always' ) {
                $values[ 'discount' ][ 'when_always' ] = 'checked';
            } elseif( $_POST[ 'when' ] == 'period' ) {
                $values[ 'discount' ][ 'when_period' ] = 'checked';
                $values[ 'discount' ][ 'from' ] = $_POST[ 'from' ];
                $values[ 'discount' ][ 'to' ] = $_POST[ 'to' ];
                $to = str_replace( '-', '', $values[ 'discount' ][ 'to' ] );
                if( $to < date( 'Ymd' ) ) {
                    $error = true;
                    $this->linker->html->addMessage( $this->getI18n( 'discount_error_toPast' ) );
                }
            }

            if( $_POST[ 'daysOfWeek' ] == 'all' ) {
                $values[ 'discount' ][ 'days_all' ] = 'checked';
                $days = array(
                    '1' => true, '2' => true, '3' => true, '4' => true, '5' => true, '6' => true, '7' => true
                );
            } elseif( $_POST[ 'daysOfWeek' ] == 'selected' ) {
                $daysSelected = false;
                $values[ 'discount' ][ 'days_selected' ] = 'checked';
                if( is_array( $_POST[ 'days' ] ) ) {
                    $days = array( );
                    foreach( array_keys( $_POST[ 'days' ] ) as $day ) {
                        $values[ 'discount' ][ 'days_' . $day ] = 'checked';
                        $daysSelected = true;
                        $days[ $day ] = true;
                    }
                }
                if( !$daysSelected ) {
                    $error = true;
                    $this->linker->html->addMessage( $this->getI18n( 'discount_error_dayOfWeekNotSeletced' ) );
                }
            }

            $values[ 'discount' ][ 'quantity' ] = $_POST[ 'discount' ][ 'quantity' ];
            if( $_POST[ 'discount' ][ 'quantity' ] < 1 ) {
                $_POST[ 'discount' ][ 'quantity' ] = 1;
            }

            if( $_POST[ 'discount' ][ 'type' ] == 'percents' ) {
                $values[ 'discount' ][ 'type_percents' ] = 'checked';
                $values[ 'discount' ][ 'percents' ] = $_POST[ 'discount' ][ 'percents' ];
                if( ( int ) $_POST[ 'discount' ][ 'percents' ] <= 0 || ( int ) $_POST[ 'discount' ][ 'percents' ] > 100 ) {
                    $error = true;
                    $this->linker->html->addMessage( $this->getI18n( 'discount_error_needPercents' ) );
                }
            } elseif( $_POST[ 'discount' ][ 'type' ] == 'monney' ) {
                $values[ 'discount' ][ 'type_monney' ] = 'checked';
                $values[ 'discount' ][ 'monney' ] = $_POST[ 'discount' ][ 'monney' ];
                if( ( int ) $_POST[ 'discount' ][ 'monney' ] <= 0 ) {
                    $error = true;
                    $this->linker->html->addMessage( $this->getI18n( 'discount_error_needMonney' ) );
                }
            } elseif( $_POST[ 'discount' ][ 'type' ] == 'gift' ) {
                $values[ 'discount' ][ 'type_gift' ] = 'checked';
                $values[ 'discount' ][ 'gift_addMoney' ] = $_POST[ 'discount' ][ 'gift_addMoney' ];
                $values[ 'discount' ][ 'gift_quantity' ] = $_POST[ 'discount' ][ 'gift_quantity' ];
                $values[ 'discount' ][ 'gift_category' ] = $_POST[ 'discount' ][ 'gift_category' ];
                if( ( int ) $_POST[ 'discount' ][ 'gift_quantity' ] <= 0 ) {
                    $error = true;
                    $this->linker->html->addMessage( $this->getI18n( 'discount_error_needGiftQuantity' ) );
                }
            }

            list($oldValues) = $this->db_execute( 'discount_get', array( 'id' => $id ) );
            if( isset( $oldValues[ 'title' ] ) && isset( $oldValues[ 'description' ] ) ) {
                $titleI18nId = $oldValues[ 'title' ];
                $descriptionCategoriesI18nId = $oldValues[ 'description_categories' ];
                $descriptionProductI18nId = $oldValues[ 'description_product' ];
            } else {
                $titleI18nId = 0;
                $descriptionCategoriesI18nId = 0;
                $descriptionProductI18nId = 0;
            }
            $titleI18nId = $this->setI18n( $titleI18nId, $_POST[ 'discount' ][ 'title' ] );
            $descriptionCategoriesI18nId = $this->setI18n( $descriptionCategoriesI18nId,
                                                           $_POST[ 'discount' ][ 'description_categories' ] );
            $descriptionProductI18nId = $this->setI18n( $descriptionProductI18nId,
                                                        $_POST[ 'discount' ][ 'description_product' ] );

            if( !$error ) {
                if( $id == 0 ) {
                    // We create a new discount
                    $this->db_execute(
                        'discount_create', array( )
                    );
                    $id = $this->db_insertId();

                    $redirect = true;
                }
                $replacements = array(
                    'id' => $id,
                    'name' => $_POST[ 'name' ],
                    'when' => $_POST[ 'when' ],
                    'from' => $_POST[ 'from' ],
                    'to' => $_POST[ 'to' ],
                    'monday' => $days[ 1 ] === true ? 'TRUE' : 'FALSE',
                    'tuesday' => $days[ 2 ] === true ? 'TRUE' : 'FALSE',
                    'wednesday' => $days[ 3 ] === true ? 'TRUE' : 'FALSE',
                    'thursday' => $days[ 4 ] === true ? 'TRUE' : 'FALSE',
                    'friday' => $days[ 5 ] === true ? 'TRUE' : 'FALSE',
                    'saturday' => $days[ 6 ] === true ? 'TRUE' : 'FALSE',
                    'sunday' => $days[ 7 ] === true ? 'TRUE' : 'FALSE',
                    'quantity' => $_POST[ 'discount' ][ 'quantity' ],
                    'type' => $_POST[ 'discount' ][ 'type' ],
                    'percents' => $_POST[ 'discount' ][ 'percents' ],
                    'monney' => $_POST[ 'discount' ][ 'monney' ],
                    'gift_addMoney' => $_POST[ 'discount' ][ 'gift_addMoney' ],
                    'gift_quantity' => $_POST[ 'discount' ][ 'gift_quantity' ],
                    'gift_category' => $_POST[ 'discount' ][ 'gift_category' ],
                    'title' => $titleI18nId,
                    'description_categories' => $descriptionCategoriesI18nId,
                    'description_product' => $descriptionProductI18nId
                );

                // We should save the discount
                $this->db_execute(
                    'discount_save', $replacements
                );

                $this->cachePrices();
            }
        }

        $productsCategories = $this->getProductsCategories();
        $results = array( );
        foreach( $productsCategories as $category ) {
            // We create the pathes (cat1 > cat1.1 > cat1.1.1, etc)
            $categoryId = $category;
            $cpt = 0;
            $message = '';
            list($parentCategory) = $this->db_execute( 'category_get_shortest_parent', array( 'id' => $categoryId ) );
            $chain = $this->getCategoryName( $category );
            $chainUid = $category;
            $state = '';
            while( $parentCategory[ 'parent' ] != 0 ) {
                $chain = $this->getCategoryName( $parentCategory[ 'parent' ] ) . '>' . $chain;
                $chainUid = $parentCategory[ 'parent' ] . '_' . $chainUid;

                list($parentCategory) = $this->db_execute( 'category_get_shortest_parent',
                                                           array( 'id' => $parentCategory[ 'parent' ] ) );
                $cpt++;
            }
            $results[ 'chain_' . $chainUid ] = array(
                'id' => $category,
                'name' => $chain,
                'checked' => $state
            );
        }

        ksort( $results );
        $values[ 'categories' ] = $results;

        if( $id == 0 && !$filled ) {
            $values[ 'discount' ][ 'when_always' ] = 'checked';
            $values[ 'discount' ][ 'days_all' ] = 'checked';
            $values[ 'discount' ][ 'quantity' ] = '2';
            $values[ 'discount' ][ 'type_percents' ] = 'checked';
            $values[ 'discount' ][ 'percents' ] = '50';
        } else {
            list($discount) = $this->db_execute( 'discount_get', array( 'id' => $id ) );

            $values[ 'discount' ][ 'name' ] = $discount[ 'name' ];
            $values[ 'discount' ][ 'title' ] = $discount[ 'title' ];
            $values[ 'discount' ][ 'description_categories' ] = $discount[ 'description_categories' ];
            $values[ 'discount' ][ 'description_product' ] = $discount[ 'description_product' ];

            if( $discount[ 'when' ] == 'always' ) {
                $values[ 'discount' ][ 'when_always' ] = 'checked';
            } else {
                $values[ 'discount' ][ 'when_period' ] = 'checked';
            }
            $values[ 'discount' ][ 'from' ] = $discount[ 'from' ];
            $values[ 'discount' ][ 'to' ] = $discount[ 'to' ];

            $days = array( 1 => 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
            $everyday = true;
            foreach( $days as $day ) {
                $everyday = $everyday && $discount[ $day ];
            }
            if( $everyday ) {
                $values[ 'discount' ][ 'days_all' ] = 'checked';
            } else {
                $daysSelected = false;
                $values[ 'discount' ][ 'days_selected' ] = 'checked';
                foreach( $days as $dayNum => $day ) {
                    if( $discount[ $day ] ) {
                        $values[ 'discount' ][ 'days_' . $dayNum ] = 'checked';
                    }
                }
            }

            $values[ 'discount' ][ 'quantity' ] = $discount[ 'quantity' ];
            if( $_POST[ 'discount' ][ 'quantity' ] < 1 ) {
                $_POST[ 'discount' ][ 'quantity' ] = 1;
            }

            if( $discount[ 'type' ] == 'percents' ) {
                $values[ 'discount' ][ 'type_percents' ] = 'checked';
            } elseif( $discount[ 'type' ] == 'monney' ) {
                $values[ 'discount' ][ 'type_monney' ] = 'checked';
            } else {
                $values[ 'discount' ][ 'type_gift' ] = 'checked';
            }
            $values[ 'discount' ][ 'percents' ] = $discount[ 'percents' ];
            $values[ 'discount' ][ 'monney' ] = $discount[ 'monney' ];
            $values[ 'discount' ][ 'gift_addMoney' ] = $discount[ 'gift_addMoney' ];
            $values[ 'discount' ][ 'gift_quantity' ] = $discount[ 'gift_quantity' ];
            $values[ 'discount' ][ 'gift_category' ] = $discount[ 'gift_category' ];
        }

        $this->linker->html->setTitle( $this->getI18n( 'discountEditor_title' ) );

        $this->render( 'edit_discount', $values );
    }

    public function productPicker() {
        $this->onlyAdmin( true );
        $id = $this->linker->path->page[ 'id' ];
        $values = $this->get_category_elements( $id );
        echo $this->render( 'pack_productPicker', $values, false, false );
    }

    public function productOrCategoryPicker() {
        $this->onlyAdmin( true );
        $categories = $this->getProductsCategories();
        foreach( $categories as $category ) {
            $values[ 'categories' ][ $category ][ 'id' ] = $category;
            $values[ 'categories' ][ $category ][ 'name' ] = $this->getCategoryName( $category );
        }
        echo $this->render( 'pack_productOrCategoryPicker', $values, false, false );
    }

    public function category_minimumProductPrice( $category ) {
        $products = $this->category_get_all_products( $category );
        if( !empty( $products ) ) {
            $smallestPrice = 1000000000;
            $singlePrice = true;
            foreach( $products as $product ) {
                list($thisPrice) = $this->db_execute( 'product_get_price_noDiscount',
                                                      array(
                    'product' => $product[ 'id' ], 'quantity' => 1
                    ) );
                if( $doneOnce && $smallestPrice != $thisPrice[ 'price' ] ) {
                    $singlePrice = false;
                }
                if( $smallestPrice > $thisPrice[ 'price' ] ) {
                    $smallestPrice = $thisPrice[ 'price' ];
                }
                if( $product[ 'variants_change_price' ] ) {
                    $singlePrice = false;
                }
                $doneOnce = true;
            }
            return array(
                'smallestPrice' => $smallestPrice,
                'singlePrice' => $singlePrice
            );
        } else {
            return false;
        }
    }

    public function pack_get_variants( $uid = null, $product = null, $selected = null, $quantity = 1 ) {
        if( is_null( $uid ) ) {
            $echo = true;
            $product = $_POST[ 'product' ];
            $uid = $_POST[ 'uid' ];
            $selected = 'none';
        }

        $values[ 'category' ][ 'uid' ] = $uid;

        list($hasVariants) = $this->db_execute(
            'product_has_variant', array( 'product_id' => $product )
        );
        if( !empty( $hasVariants[ 'hasVariants' ] ) ) {
            $values[ 'variants' ] = $this->db_execute(
                'product_get_variants', array( 'product_id' => $product )
            );

            foreach( $values[ 'variants' ] as $variantNum => $variant ) {
                $cps = explode( '|', $variant[ 'customProperties' ] );
                $separator = '';
                $text = '';
                $isSelectedVariant = true;
                foreach( $cps as $cp ) {
                    list($cpId, $cpValue) = explode( ':', $cp );
                    list($cpName) = $this->db_execute( 'customProperty_get', array( 'id' => $cpId ) );
                    $text .= $separator . $this->getI18n( $cpName[ 'name' ] ) . ' : ' . $this->getI18n( $cpValue );
                    $separator = ' - ';
                    $cpValues = explode( '|', $cpName[ 'values' ] );
                    if( $cpValues[ $selected ] !== $cpValue ) {
                        $isSelectedVariant = false;
                    }
                }
                if( $isSelectedVariant ) {
                    $values[ 'variants' ][ $variantNum ][ 'state' ] = 'selected';
                }
                $values[ 'variants' ][ $variantNum ][ 'name' ] = $text;
            }
        } else {
            $values[ 'variants' ][ 'none' ] = true;
        }

        $ret = $this->render( 'default_pack_variants', $values, false, false );
        if( $echo ) {
            echo $ret;
            return true;
        } else {
            return $ret;
        }
    }

    public function showPack() {
        if( !$this->activateShop ) {
            $this->linker->path->error( 404 );
        }
        $id = $this->linker->path->page[ 'id' ];
        list($values[ 'pack' ]) = $this->db_execute( 'pack_get', array( 'id' => $id ) );

        list($layout) = $this->db_execute( 'layout_get', array( 'id' => $values[ 'pack' ][ 'layout' ] ) );

        $values[ 'pack' ][ 'layout' ] = $values[ 'pack' ][ 'layout' ];
        $values[ 'pack' ][ 'layout_top' ] = $this->getI18n( $layout[ 'top' ] );
        $values[ 'pack' ][ 'layout_bottom' ] = $this->getI18n( $layout[ 'bottom' ] );

        if( $this->formSubmitted( 'choosePack' ) ) {
            $product_pack_contents = implode( '|', $_POST[ 'product' ] );
            // We check if the product exists
            list($product) = $this->db_execute(
                'pack_get_product',
                array(
                'pack_id' => $id,
                'pack_variant' => $product_pack_contents
                )
            );
            if( !isset( $product[ 'id' ] ) ) {
                $hasVariants = false;
                $price = $values[ 'pack' ][ $values[ 'pack' ][ 'cost' ] ];
                $addPrices = ($values[ 'pack' ][ 'cost' ] == 'add');
                $noShippingCost = true;
                $variantChangeStock = false;
                // The product has to be generated so we need more informations to create it
                foreach( $_POST[ 'product' ] as $number => $working_product ) {
                    list($productDatas) = $this->db_execute( 'product_get_active', array( 'id' => $working_product ) );
                    list($productDatasFromPack) = $this->db_execute(
                        'pack_get_content', array( 'pack_id' => $id, 'number' => $number )
                    );
                    // We cache them in order not to have to make the same queries later
                    $cacheProductsDatas[ $number ] = $productDatas;
                    $cacheProductsDatasFromPack[ $number ] = $productDatasFromPack;

                    if( empty( $productDatas ) ) {
                        $this->linker->html->addMessage( 'One of the products you selected may not be found...' );
                        $this->linker->path->refresh();
                    }
                    if( !isset( $stock ) || $productDatas[ 'stock' ] < $stock ) {
                        $stock = $productDatas[ 'stock' ];
                        if( $productDatas[ 'hasVariants' ] && $productDatas[ 'variants_change_stock' ] ) {
                            $variantChangeStock = true;
                        }
                    }
                    if( $addPrices && !$productDatasFromPack[ 'free' ] ) {
                        if( $productDatas[ 'hasVariants' ] && $productDatas[ 'variants_change_price' ] ) {
                            $variantChangePrice = true;
                        }

                        $price += $productDatas[ 'price' ] * $productDatasFromPack[ 'quantity' ];
                    }
                    $noShippingCost = $noShippingCost && $productDatas[ 'noShippingCost' ];

                    if( $productDatas[ 'hasVariants' ] ) {
                        $hasVariants = true;
                    }
                }
                $this->db_execute( 'product_create' );
                $product[ 'id' ] = $this->db_insertId();
                $replacements = array(
                    'id' => $product[ 'id' ],
                    'pack_id' => $id,
                    'pack_variant' => $product_pack_contents,
                    'name' => $values[ 'pack' ][ 'name' ],
                    'reference' => '',
                    'stock' => $stock,
                    'price' => $price,
                    'hasVariants' => $hasVariants,
                    'variants_change_price' => $variantChangePrice,
                    'variants_change_stock' => $variantChangeStock,
                    'noShippingCosts' => $noShippingCost,
                    'taxRate' => $values[ 'pack' ][ 'taxRate' ]
                );
                $this->db_execute( 'pack_product_save', $replacements );

                list($product) = $this->db_execute(
                    'pack_get_product',
                    array(
                    'pack_id' => $id,
                    'pack_variant' => $product_pack_contents
                    )
                );
                unset( $stock );
            }

            if( $product[ 'hasVariants' ] ) {
                if( empty( $_POST[ 'variant' ] ) ) {
                    // We should have chosen the variants by now...
                    $this->linker->html->addMessage( 'You should have selected a variant for at least one product...' );
                    $this->linker->path->refresh();
                }

                // Getting the variant_id if it exists, or creating a new one
                $separator = '';
                foreach( $_POST[ 'product' ] as $number => $working_product ) {
                    if( isset( $_POST[ 'variant' ][ $number ] ) ) {
                        $working_variant = $_POST[ 'variant' ][ $number ];
                        $customProperties .= $separator . $working_product . ':' . $working_variant;
                    } else {
                        $working_variant = '';
                        $customProperties .= $separator . $working_product;
                    }

                    $variantsProducts[ $number ] = array(
                        'product' => $working_product,
                        'variant' => $working_variant
                    );
                    $separator = '|';
                }

                list($variantDatas) = $this->db_execute(
                    'pack_get_variant',
                    array(
                    'product_id' => $product[ 'id' ],
                    'customProperties' => $customProperties
                    )
                );
                if( empty( $variantDatas ) ) {
                    // We should create the variant
                    list($max_variant_id) = $this->db_execute(
                        'pack_get_max_variant',
                        array(
                        'product_id' => $product[ 'id' ]
                        )
                    );
                    if( is_null( $max_variant_id[ 'max' ] ) ) {
                        $nextId = 0;
                    } else {
                        $nextId = $max_variant_id[ 'max' ] + 1;
                    }

                    $price = $values[ 'pack' ][ $values[ 'pack' ][ 'cost' ] ];
                    $addPrices = ($values[ 'pack' ][ 'cost' ] == 'add');
                    $separator = '';
                    // We should get the stock and the price
                    foreach( $variantsProducts as $number => $oneProduct ) {
                        if( empty( $cacheProductsDatas[ $number ] ) ) {
                            list($cacheProductsDatas[ $number ]) = $this->db_execute(
                                'product_get_active', array( 'id' => $oneProduct[ 'product' ] )
                            );
                            list($cacheProductsDatasFromPack[ $number ]) = $this->db_execute(
                                'pack_get_content', array( 'pack_id' => $id, 'number' => $number )
                            );
                        }
                        $productDatas = $cacheProductsDatas[ $number ];
                        $productDatasFromPack = $cacheProductsDatasFromPack[ $number ];
                        list($variantDatas) = $this->db_execute(
                            'product_get_variant',
                            array(
                            'product_id' => $oneProduct[ 'product' ],
                            'variant_id' => $oneProduct[ 'variant' ]
                            )
                        );
                        if( $addPrices && !$productDatasFromPack[ 'free' ] ) {
                            if( $productDatas[ 'hasVariants' ] && $productDatas[ 'variants_change_price' ] ) {
                                $variantChangePrice = true;
                            }

                            $price += $productDatas[ 'price' ] * $productDatasFromPack[ 'quantity' ];
                        }

                        if( $productDatas[ 'hasVariants' ] && $productDatas[ 'variants_change_stock' ] ) {
                            if( !isset( $stock ) || $variantDatas[ 'stock' ] < $stock ) {
                                $stock = $variantDatas[ 'stock' ];
                            }
                        } elseif( !isset( $stock ) || $productDatas[ 'stock' ] < $stock ) {
                            $stock = $productDatas[ 'stock' ];
                        }
                    }
                    $this->db_execute(
                        'pack_insert_variant',
                        array(
                        'product_id' => $product[ 'id' ],
                        'variant_id' => $nextId,
                        'stock' => $stock,
                        'price' => $price,
                        'ref' => '',
                        'customProperties' => $customProperties
                        )
                    );

                    list($variantDatas) = $this->db_execute(
                        'pack_get_variant',
                        array(
                        'product_id' => $product[ 'id' ],
                        'customProperties' => $customProperties
                        )
                    );
                }
                // Adding the product's variant to the cart
                $this->addToCart( $product[ 'id' ], $variantDatas[ 'variant_id' ], $_POST[ 'quantity' ], true );
            } else {
                // Adding the product to the cart
                $this->addToCart( $product[ 'id' ], null, $_POST[ 'quantity' ], true );
            }
        }

        // And add an entry in the command panel
        $this->linker->admin->insert(
            '<a href="' . $this->linker->path->getLink(
                $this->shortClassName . '/editPack/' . $id
            ) . '">Modifier ce lot</a>', 'Boutique', 'picto_modify.png'
        );

        if( is_array( $_SESSION[ __CLASS__ ][ 'used_pack' ] ) ) {
            $values[ 'base' ] = $_SESSION[ __CLASS__ ][ 'used_pack' ];
        }

        $values[ 'pack' ][ 'name' ] = $this->getI18n( $values[ 'pack' ][ 'name' ] );
        $this->linker->html->setTitle( $values[ 'pack' ][ 'name' ] );
        $totalPrice = $values[ 'pack' ][ $values[ 'pack' ][ 'cost' ] ];
        $singlePrice = true;

        $values[ 'elements' ] = $this->db_execute( 'pack_get_contents_active', array( 'pack_id' => $id ) );

        foreach( $values[ 'elements' ] as $key => $element ) {
            if( $element[ 'element_type' ] == 'category' ) {
                $values[ 'elements' ][ $key ][ 'uid' ] = $element[ 'number' ];
                $values[ 'elements' ][ $key ][ 'element_name' ] = $this->getCategoryName( $element[ 'element_id' ] );

                $products = $this->category_get_all_products( $element[ 'element_id' ] );


                $category_minimumProductPrice = $this->category_minimumProductPrice(
                    $element[ 'element_id' ]
                );

                if( $values[ 'pack' ][ 'cost' ] == 'add' && !$element[ 'free' ] ) {
                    // We should get the minimum price
                    list($minimum_price) = $this->db_execute(
                        'product_get_price_noDiscount',
                        array(
                        'product' => $element[ 'element_id' ],
                        'quantity' => 1
                        )
                    );

                    $singlePrice = $singlePrice && $category_minimumProductPrice[ 'singlePrice' ];

                    $totalPrice += $category_minimumProductPrice[ 'smallestPrice' ];
                }

                foreach( $products as $num => $working_product ) {
                    $productDatas = &$values[ 'elements' ][ $key ][ 'products' ][ $num ];
                    list($productDatas) = $this->db_execute(
                        'product_get_active', array( 'id' => $working_product[ 'id' ] )
                    );
                    if( $working_product[ 'id' ] == $values[ 'base' ][ 'product' ] ) {
                        $productDatas[ 'state' ] = 'selected';
                        if( $productDatas[ 'hasVariants' ] ) {
                            $productDatas[ 'variants' ] = $this->pack_get_variants(
                                $values[ 'elements' ][ $key ][ 'uid' ], $working_product[ 'id' ],
                                $values[ 'base' ][ 'variant' ]
                            );
                        }
                    }
                    $productDatas[ 'name' ] = $this->getI18n(
                        $productDatas[ 'name' ]
                    );
                    $productDatas[ 'shortDescription' ] = $this->getI18n(
                        $productDatas[ 'shortDescription' ]
                    );
                    if( $values[ 'pack' ][ 'cost' ] == 'add' && !$element[ 'free' ] && !$category_minimumProductPrice[ 'singlePrice' ] ) {
                        $productDatas[ 'showPrice' ] = true;
                    }
                    if( !$category_minimumProductPrice[ 'singlePrice' ] ) {
                        $shownPrice = $productDatas[ 'price' ] - $category_minimumProductPrice[ 'smallestPrice' ];

                        if( $working_product[ 'hasVariants' ] && $working_product[ 'variants_change_price' ] ) {
                            $productDatas[ 'price' ] = $this->getI18n( 'product_price_from_before' );
                            $productDatas[ 'price' ] .= '+' . $this->monney_format( $shownPrice, true, false );
                            $productDatas[ 'price' ] .= $this->getI18n( 'product_price_from_after' );
                        } elseif( $shownPrice > 0 ) {
                            $productDatas[ 'price' ] = '+' . $this->monney_format( $shownPrice, true, false );
                        } else {
                            $productDatas[ 'showPrice' ] = false;
                        }
                    }
                }
            } else {
                $productDatas = &$values[ 'elements' ][ $key ];
                list($productDatas) = $this->db_execute(
                    'product_get_active', array( 'id' => $element[ 'element_id' ] )
                );
                $values[ 'elements' ][ $key ][ 'uid' ] = $element[ 'number' ];
                $productDatas[ 'quantity' ] = $element[ 'quantity' ];
                $productDatas[ 'name' ] = $this->getI18n(
                    $productDatas[ 'name' ]
                );
                $productDatas[ 'shortDescription' ] = $this->getI18n(
                    $productDatas[ 'shortDescription' ]
                );

                if( $values[ 'pack' ][ 'cost' ] == 'add' && !$element[ 'free' ] ) {
                    if( $productDatas[ 'hasVariants' ] && $productDatas[ 'variants_change_price' ] ) {
                        $singlePrice = false;
                    }
                    $totalPrice += $productDatas[ 'quantity' ] * $productDatas[ 'price' ];
                }
                if( $productDatas[ 'hasVariants' ] ) {
                    $productDatas[ 'variants' ] = $this->pack_get_variants(
                        $values[ 'elements' ][ $key ][ 'uid' ], $productDatas[ 'id' ], $values[ 'base' ][ 'variant' ],
                        $productDatas[ 'quantity' ]
                    );
                }
            }
        }
        $values[ 'price' ][ 'minimum' ] = $this->monney_format( $totalPrice, true, false );
        $values[ 'price' ][ 'single' ] = $singlePrice;
        $values[ 'getVariant' ][ 'action' ] = $this->linker->path->getLink( __CLASS__ . '/pack_get_variants/' );

        $this->render( 'default_pack', $values );
    }

    public function packsList() {
        $this->onlyAdmin( true );
        $this->linker->html->setTitle( $this->getI18n( 'packsList_title' ) );
        if( $this->formSubmitted( 'packs_list' ) ) {
            // We should create a pack
            $this->linker->path->redirect( __CLASS__, editPack, 0 );
        }
        $values[ 'active' ] = $this->db_execute( 'packs_get_active' );
        foreach( $values[ 'active' ] as $id => $active ) {
            $values[ 'active' ][ $id ][ 'editLink' ] = $this->linker->path->getLink( __CLASS__ . '/editPack/' . $active[ 'id' ] );
            $values[ 'active' ][ $id ][ 'showLink' ] = $this->linker->path->getLink( __CLASS__ . '/showPack/' . $active[ 'id' ] );
            $values[ 'active' ][ $id ][ 'name' ] = $this->getI18n( $active[ 'name' ] );
            $values[ 'present' ][ 'active' ] = true;
        }
        $values[ 'inactive' ] = $this->db_execute( 'packs_get_inactive' );
        foreach( $values[ 'inactive' ] as $id => $inactive ) {
            $values[ 'inactive' ][ $id ][ 'editLink' ] = $this->linker->path->getLink( __CLASS__ . '/editPack/' . $inactive[ 'id' ] );
            $values[ 'inactive' ][ $id ][ 'name' ] = $this->getI18n( $inactive[ 'name' ] );
            $values[ 'present' ][ 'inactive' ] = true;
        }
        $this->render( 'packsList', $values );
    }

    public function editPack() {
        // We first verify that we are at least administrator
        $this->onlyAdmin( true );
        $this->linker->html->setTitle( $this->getI18n( 'editPack_title' ) );

        $id = $this->linker->path->page[ 'id' ];

        if( $this->formSubmitted( 'packEditor' ) ) {
            if( $id == 0 ) {
                // New pack - We get a new id by inserting a new empty pack in the db
                /* $this->db_execute( 'pack_create_product' );
                  $productId = $this->db_insertId(); */

                $this->db_execute( 'pack_create', array( ) );
                $id = $this->db_insertId();

                $redirect = true;
            }

            // getting old values
            list($oldValues) = $this->db_execute( 'pack_get', array( 'id' => $id ) );

            // Saving the pack
            $name = $this->setI18n( $oldValues[ 'name' ], $_POST[ 'name' ] );
            $this->db_execute(
                'pack_save',
                array(
                'id' => $id,
                'name' => $name,
                'active' => isset( $_POST[ 'active' ] ),
                'layout' => $_POST[ 'layout' ] > 0 ? $_POST[ 'layout' ] : 1,
                'cost' => $_POST[ 'cost' ],
                'add' => $_POST[ 'add' ],
                'total' => $_POST[ 'total' ],
                'seo_titleBar' => 0,
                'seo_metaDescription' => 0,
                'taxRate' => $_POST[ 'taxRate' ]
                )
            );

            $i18ns = $this->db_execute( 'pack_get_contents_i18ns', array( 'pack_id' => $id ) );
            foreach( $i18ns as $i18n ) {
                if( $i18n[ 'name' ] > 0 ) {
                    $this->removeI18n( $i18n[ 'name' ] );
                }
            }
            $this->db_execute( 'pack_remove_elements', array( 'pack_id' => $id ) );

            $thereIsMoreThanOnePrice = false;
            if( is_array( $_POST[ 'products' ] ) ) {
                $atLeastOneMainProduct = false;
                $totalNumberOfProducts = 0;
                $list = array_keys( $_POST[ 'products' ][ 'element' ] );
                $number = 0;
                foreach( $list as $key ) {
                    $minimumPrice = 0;
                    if( $_POST[ 'products' ][ 'quantity' ][ $key ] > 0 ) {
                        if( preg_match( '`([cp])([0-9]+) .*`', $_POST[ 'products' ][ 'element' ][ $key ], $matches ) ) {
                            $element_id = $matches[ 2 ];
                            if( $matches[ 1 ] == 'c' ) {
                                $element_type = 'category';
                            } else {
                                $element_type = 'product';
                            }
                            $name = 0;
                            if( $_POST[ 'products' ][ 'type' ][ $key ] == 'main' ) {
                                $atLeastOneMainProduct = true;
                                $name = $this->setI18n( 0, $_POST[ 'products' ][ 'text' ][ $key ] );
                            }
                            $this->db_execute(
                                'pack_add_element',
                                array(
                                "pack_id" => $id,
                                "type" => $_POST[ 'products' ][ 'type' ][ $key ],
                                "element_type" => $element_type,
                                "element_id" => $element_id,
                                "quantity" => $_POST[ 'products' ][ 'quantity' ][ $key ],
                                "free" => $_POST[ 'products' ][ 'free' ][ $key ] == 'checked',
                                "name" => $name,
                                "number" => $number
                                )
                            );
                            $number++;
                            $totalNumberOfProducts += $_POST[ 'products' ][ 'quantity' ][ $key ];
                        }
                    }
                }
            }
            if( !$atLeastOneMainProduct ) {
                $this->db_execute( 'pack_deActivate', array( 'id' => $id ) );
                $this->linker->html->addMessage( 'Il faut au moins 1 produit principal.' );
            }
            if( $totalNumberOfProducts < 2 ) {
                $this->db_execute( 'pack_deActivate', array( 'id' => $id ) );
                $this->linker->html->addMessage( 'Un lot ne peut être composé d\'un unique produit.' );
            }

            $this->linker->path->redirect( __CLASS__, 'showPack', $id );
        }

        if( $id == 0 ) {
            // New pack
            $values[ 'pack' ][ 'addState' ] = 'checked';
            $values[ 'pack' ][ 'layout' ] = 1;
            $values[ 'pack' ][ 'add' ] = '0';
            $values[ 'pack' ][ 'total' ] = '0';
        } else {
            list($values[ 'pack' ]) = $this->db_execute( 'pack_get', array( 'id' => $id ) );
            if( $values[ 'pack' ][ 'active' ] ) {
                $values[ 'pack' ][ 'active' ] = 'checked';
            } else {
                unset( $values[ 'pack' ][ 'active' ] );
            }
            if( $values[ 'pack' ][ 'cost' ] == 'add' ) {
                $values[ 'pack' ][ 'addState' ] = 'checked';
            } else {
                $values[ 'pack' ][ 'totalState' ] = 'checked';
            }
            $products = $this->db_execute( 'pack_get_contents', array( 'id' => $id ) );
            if( is_array( $products ) ) {
                foreach( $products as $product ) {
                    if( $product[ 'element_type' ] == 'category' ) {
                        $element = 'c';
                        $name = $this->getCategoryName( $product[ 'element_id' ] );
                    } else {
                        $element = 'p';
                        $name = $this->getProductName( $product[ 'element_id' ] );
                    }
                    $element .= $product[ 'element_id' ] . ' - ' . $name;
                    $free = $product[ 'free' ] ? 'checked' : '';
                    if( $product[ 'type' ] == 'main' ) {
                        $values[ 'main' ][ ] = array(
                            'element' => $element,
                            'quantity' => $product[ 'quantity' ],
                            'free' => $free,
                            'text' => $product[ 'name' ]
                        );
                    } else {
                        $values[ 'secondary' ][ ] = array(
                            'element' => $element,
                            'quantity' => $product[ 'quantity' ],
                            'free' => $free
                        );
                    }
                }
            }
        }
        $layouts = $this->db_execute( 'layouts_get_all' );
        foreach( $layouts as $layout ) {
            $values[ 'layouts' ][ $layout[ 'id' ] ] = $layout;
        }
        $values[ 'layouts' ][ $values[ 'pack' ][ 'layout' ] ][ 'state' ] = 'selected';

        $this->render( 'edit_pack', $values );
    }

    public function editLayout() {
        $this->onlyAdmin( true );
        $id = $this->linker->path->page[ 'id' ];
        if( $this->formSubmitted( 'shoplayoutEditor' ) ) {
            if( $id == 0 ) {
                // Creating a new one
                $redirect = true;
                $this->db_execute( 'layout_create', array( 'id' => 1, 'top' => 0, 'bottom' => 0 ) );

                $id = $this->db_insertId();
            }
            list($datas) = $this->db_execute( 'layout_get', array( 'id' => $id ) );
            $top = $this->setI18n( $datas[ 'top' ], $_POST[ 'top' ] );
            $bottom = $this->setI18n( $datas[ 'bottom' ], $_POST[ 'bottom' ] );
            if( empty( $_POST[ 'name' ] ) ) {
                $_POST[ 'name' ] = 'N°' . $id;
            }
            $this->db_execute(
                'layout_update',
                array(
                'id' => $id, 'name' => addslashes( $_POST[ 'name' ] ), 'top' => $top, 'bottom' => $bottom
                )
            );
            if( $redirect ) {
                $this->linker->path->redirect( __CLASS__, __FUNCTION__, $id );
            }
        }
        $values[ 'layouts' ] = $this->db_execute( 'layouts_get_all' );
        foreach( $values[ 'layouts' ] as $layoutId => $layout ) {
            if( $layout[ 'id' ] == 1 ) {
                // This is the default layout
                $values[ 'layouts' ][ $layoutId ][ 'undeletable' ] = true;
            }
            if( $layout[ 'id' ] == $id ) {
                $values[ 'layout' ] = $layout;
                unset( $values[ 'layouts' ][ $layoutId ] );
            } else {
                $values[ 'layouts' ][ $layoutId ][ 'editLink' ] = $this->linker->path->getLink( __CLASS__ . '/' . __FUNCTION__ . '/' . $layout[ 'id' ] );
                $values[ 'layouts' ][ $layoutId ][ 'deleteLink' ] = $this->linker->path->getLink( __CLASS__ . '/' . __FUNCTION__ . '/' . $layout[ 'id' ] );
            }
        }
        if( $id == 1 ) {
            $values[ 'layout' ][ 'is_default_layout' ] = true;
        }
        $values[ 'newLayout' ][ 'link' ] = $this->linker->path->getLink( __CLASS__ . '/' . __FUNCTION__ . '/0' );
        $this->render( 'edit_layout', $values );
    }

    /**
     * Method that creates and manages the return of the form to edit a product
     */
    public function editProduct() {// We first verify that we are at least administrator
        $this->onlyAdmin( true );

        // We get the id
        $id = $this->linker->path->page[ 'id' ];

        $this->linker->html->setTitle( $this->getI18n( 'productEditor_title' ) );
        $variants = array( );
        // We verify if we have to save the results of a submitted form
        if( $this->formSubmitted( 'productEditor' ) ) {
            if( isset( $_POST[ 'active' ] ) ) {
                $active = true;
            } else {
                $active = false;
            }
            if( $id == 0 ) {
                // New product
                $id = $this->getNewProductId();
                $name = $this->setI18n( 0, $_POST[ 'name' ] );
                $description = $this->setI18n( 0, $_POST[ 'description' ] );
                $shortDescription = $this->setI18n( 0, $_POST[ 'shortDescription' ] );
                $i18nTitleBar = $this->setI18n( 0, $_POST[ 'seo_titleBar' ] );
                $i18nMetaDescription = $this->setI18n( 0, $_POST[ 'seo_metaDescription' ] );

                $values[ 'message' ][ 'content' ] = $this->getI18n(
                    'product_added_successfully'
                );
                $values[ 'message' ][ 'link' ] = $this->linker->path->getLink(
                    'shop/showProduct/' . $id
                );
            } else {
                // Existing product
                $this->removeFromSitemap( 'shop/showProduct/' . $id );
                $this->db_execute( 'product_unset_customProperties', array( 'product_id' => $id ) );
                $product = $this->getProductDatas( $id );
                $name = $this->setI18n( $product[ 'name' ], $_POST[ 'name' ] );
                $description = $this->setI18n(
                    $product[ 'description' ], $_POST[ 'description' ]
                );
                $shortDescription = $this->setI18n(
                    $product[ 'shortDescription' ], $_POST[ 'shortDescription' ]
                );

                $i18nTitleBar = $this->setI18n( ( int ) $product[ 'seo_titleBar' ], $_POST[ 'seo_titleBar' ] );
                $i18nMetaDescription = $this->setI18n( ( int ) $product[ 'seo_metaDescription' ],
                                                       $_POST[ 'seo_metaDescription' ] );


                // We redirect to the product page only if it is active (of course)
                if( $active ) {
                    $redirect = true;
                }
            }

            // Updates searcher's entries
            $this->search_removeEntry( 'showProduct', $id );

            if( isset( $_POST[ 'variants' ][ 'changeRef' ] ) ) {
                $firstLevelSearchEntry = $this->addToAllI18ns( $_POST[ 'name' ], $_POST[ 'variants' ][ 'variant_ref' ] );
            } else {
                $firstLevelSearchEntry = $this->addToAllI18ns( $_POST[ 'name' ], $_POST[ 'reference' ] );
            }

            $this->search_addEntry(
                'showProduct', $id, $firstLevelSearchEntry, $_POST[ 'shortDescription' ], $_POST[ 'description' ]
            );

            // We save the product's datas
            $productNewDatas = array(
                'id' => $id,
                'name' => $name,
                'reference' => $_POST[ 'reference' ],
                'description' => $description,
                'shortDescription' => $shortDescription,
                'active' => $active,
                'image' => $_POST[ 'image' ],
                'stock' => $_POST[ 'stock' ],
                'active' => $active,
                'layout' => $_POST[ 'layout' ],
                'price' => str_replace( ',', '.', $_POST[ 'price' ] ),
                'taxRate' => str_replace( array( ',', '%' ), array( '.', '' ), $_POST[ 'taxRate' ] ),
                'images' => $_POST[ 'images' ],
                'customProperties' => $_POST[ 'customProperties' ],
                'noShippingCost' => isset( $_POST[ 'noShippingCost' ] ),
                'seo_titleBar' => $i18nTitleBar,
                'seo_metaDescription' => $i18nMetaDescription,
                'customProperties' => $_POST[ 'customProperties' ],
                'categories' => array_keys( $_POST[ 'product_categories' ] ),
                'variants' => $_POST[ 'variants' ]
            );
            $this->saveProduct( $productNewDatas );

            // We save the discounts, if any
            $this->db_execute( 'product_removeDiscounts', array( 'product_id' => $id ) );

            if( $_POST[ 'discounts' ] == 'selected' && is_array( $_POST[ 'discount' ] ) ) {
                foreach( $_POST[ 'discount' ] as $value ) {
                    if( $value != 'none' ) {
                        $this->db_execute(
                            'product_addDiscount', array( 'product_id' => $id, 'discount_id' => $value )
                        );
                    }
                }
            }
            $this->cachePrices( $id );

            if( $redirect == true ) {
                $this->linker->path->redirect( __CLASS__, 'showProduct', $id );
            }
        }
        if( isset( $_GET[ 'basedOn' ] ) ) {
            $id = $_GET[ 'basedOn' ];
        }

        // We get the custom properties
        $customProperties = $this->db_execute( 'customProperties_get_all', array( ) );

        foreach( $customProperties as $property ) {
            $propertyId = $property[ 'id' ];
            if( $productsCustomProperties[ $propertyId ] === 0 ) {
                $values[ 'customProperties' ][ $propertyId ][ 'emptyState' ] = 'selected';
            }
            $values[ 'customPropertiesBox' ][ 'show' ] = true;
            $values[ 'customProperties' ][ $propertyId ][ 'name' ] = $this->getI18n( $property[ 'name' ] );
            $values[ 'customProperties' ][ $propertyId ][ 'id' ] = $propertyId;
            $property[ 'values' ] = explode( '|', $property[ 'values' ] );
            list($productCustomProperty) = $this->db_execute( 'product_get_customProperty',
                                                              array( 'product_id' => $id, 'customProperty_id' => $propertyId ) );

            // It has entries, so we get them
            foreach( $property[ 'values' ] as $oneValue ) {
                $valId = $oneValue;
                $selected = '';
                if( $productCustomProperty[ 'customProperty_value' ] == $oneValue ) {
                    $selected = 'selected';
                }
                $values[ 'customProperties' ][ $propertyId ][ 'values' ][ $valId ] = array(
                    'value' => $this->getI18n( $oneValue ),
                    'id' => $oneValue,
                    'state' => $selected
                );
            }

            if( $id > 0 ) {
                // We also get the variants
                $variants = $this->db_execute( 'product_get_variants', array( 'product_id' => $id ) );
                $variantsCopy = $variants; // copy for shift not to erase a value
                $oneVariant = array_shift( $variantsCopy );
                foreach( explode( '|', $oneVariant[ 'customProperties' ] ) as $oneVariantCP ) {
                    list($cpUsedForVariants[ ], $variantValueexplode) = explode( ':', $oneVariantCP );
                }
                // We verify if it is used for the variants
                if( in_array( $propertyId, $cpUsedForVariants ) ) {
                    $values[ 'customProperties' ][ $propertyId ][ 'variant_state' ] = 'checked';
                }
            }
            $values[ 'shop' ][ 'there_are_custom_properties' ] = true;
        }

        $values[ 'discounts_none' ][ 'state' ] = 'checked';
        $values[ 'discounts' ] = $this->db_execute( 'discounts_get_all_available', $replacements );
        $discounts = $this->db_execute( 'product_getDiscounts', array( 'product_id' => $id ) );
        if( !empty( $discounts ) ) {
            $cpt = 1;
            foreach( $discounts as $discount ) {
                $enabledDiscounts[ ] = array(
                    'input' => 'discount_' . $cpt,
                    'value' => $discount[ 'discount_id' ]
                );
                $cpt++;
            }
            $values[ 'discounts_none' ][ 'state' ] = '';
            $values[ 'discounts_those' ][ 'state' ] = 'checked';
            $values[ 'discountValues' ][ 'json' ] = json_encode( $enabledDiscounts );
        } else {
            $values[ 'discountValues' ][ 'json' ] = json_encode( array( ) );
        }

        if( $id == 0 ) {
            // We are creating a new product, so we give the default values
            $values[ 'product' ] = array(
                'image' => '/images/shared/default/defaultShopImage.png',
                'stock' => 0,
                'active' => 'checked',
                'layout' => 1,
                'taxRate' => $this->getParam( 'taxRate', 19.6 ),
                'onClickReplaceImage' => $this->linker->browser->getOnClickReplaceImage(
                    'image', SH_SHOPIMAGES_FOLDER . 'products/'
                )
            );
        } else {
            $product = $this->getProductDatas( $id );
            // The product should already exist, so we get its values if we can
            if( !$product ) {
                return $this->notFound();
            }
            if( !isset( $product[ 'active' ] ) || $product[ 'active' ] == true ) {
                $active = 'checked';
            } else {
                $active = '';
            }
            if( $product[ 'hasVariants' ] ) {
                $hasVariants = 'checked';
            }
            if( $product[ 'variants_change_price' ] ) {
                $variants_change_price = 'checked';
            }
            if( $product[ 'variants_change_ref' ] ) {
                $variants_change_ref = 'checked';
            }
            if( $product[ 'variants_change_stock' ] ) {
                $variants_change_stock = 'checked';
            }


            foreach( $variants as $oneVariant ) {
                $values[ 'variants' ][ $oneVariant[ 'variant_id' ] ] = $oneVariant;
                $cps = explode( '|', $oneVariant[ 'customProperties' ] );
                foreach( $cps as $cp ) {
                    list($cpId, $cpValue) = explode( ':', $cp );
                    $thisProperty = $values[ 'customProperties' ][ $cpId ];
                    $thisProperty[ 'value' ] = $values[ 'customProperties' ][ $cpId ][ 'values' ][ $cpValue ][ 'value' ];
                    $thisProperty[ 'value_id' ] = $values[ 'customProperties' ][ $cpId ][ 'values' ][ $cpValue ][ 'id' ];
                    $values[ 'variants' ][ $oneVariant[ 'variant_id' ] ][ 'properties' ][ $cpId ] = $thisProperty;
                }
            }

            $values[ 'product' ] = array(
                'name' => $product[ 'name' ],
                'price' => $product[ 'price' ],
                'taxRate' => $product[ 'taxRate' ],
                'description' => $product[ 'description' ],
                'shortDescription' => $product[ 'shortDescription' ],
                'reference' => $product[ 'reference' ],
                'image' => $product[ 'image' ],
                'images' => $product[ 'images' ],
                'imagesFolder' => 'SH_SHOPIMAGES_FOLDER',
                'stock' => $product[ 'stock' ],
                'active' => $active,
                'layout' => $_POST[ 'layout' ] > 0 ? $_POST[ 'layout' ] : 1,
                'onClickReplaceImage' => $this->linker->browser->getOnClickReplaceImage(
                    'image', SH_SHOPIMAGES_FOLDER . 'products/'
                ),
                'seo_titleBar' => $product[ 'i18nTitleBar' ],
                'seo_metaDescription' => $product[ 'i18nMetaDescription' ],
                'hasVariants' => $hasVariants,
                'variants_change_price' => $variants_change_price,
                'variants_change_ref' => $variants_change_ref,
                'variants_change_stock' => $variants_change_stock
            );
            if( isset( $product[ 'noShippingCost' ] ) && $product[ 'noShippingCost' ] ) {
                $values[ 'product' ][ 'noShippingCost' ] = 'checked';
            }
        }
        // We have to list the possible parents of the product
        // So we get its real parents (if it has any)
        // We get the categories this product belongs to
        $productCategories = $this->getProductCategories( $id );

        // And we get the possible parents
        $productsCategories = $this->getProductsCategories();
        $results = array( );
        foreach( $productsCategories as $category ) {
            // We create the pathes (cat1 > cat1.1 > cat1.1.1, etc)
            $categoryId = $category;
            $cpt = 0;
            $message = '';
            list($parentCategory) = $this->db_execute( 'category_get_shortest_parent_withInactive',
                                                       array( 'id' => $categoryId ) );
            $chain = $this->getCategoryName( $category );
            $chainUid = str_pad( $category, 4, '0', STR_PAD_LEFT );
            $state = '';
            while( $parentCategory[ 'parent' ] != 0 ) {
                $chain = $this->getCategoryName( $parentCategory[ 'parent' ] ) . '>' . $chain;
                $chainUid = str_pad( $parentCategory[ 'parent' ], 4, '0', STR_PAD_LEFT ) . '_' . $chainUid;

                list($parentCategory) = $this->db_execute( 'category_get_shortest_parent_withInactive',
                                                           array( 'id' => $parentCategory[ 'parent' ] ) );
                $cpt++;
            }
            if( in_array( $category, $productCategories ) ) {
                $state = 'checked';
            }
            $results[ 'chain_' . str_pad( $chainUid, 4, '0', STR_PAD_LEFT ) ] = array(
                'id' => $category,
                'name' => $chain,
                'checked' => $state
            );
        }

        ksort( $results );
        $values[ 'categories' ] = $results;

        $layouts = $this->db_execute( 'layouts_get_all' );
        foreach( $layouts as $layout ) {
            $values[ 'layouts' ][ $layout[ 'id' ] ] = $layout;
        }
        $values[ 'layouts' ][ $values[ 'product' ][ 'layout' ] ][ 'state' ] = 'selected';

        // We render the form
        $this->render( 'edit_product', $values );
    }

    function addToAllI18ns( $i18nElements, $elementToAdd ) {
        if( is_array( $elementToAdd ) ) {
            array_shift( $elementToAdd );
            $elementToAdd = implode( ' - ', $elementToAdd );
        }
        foreach( $i18nElements as $i18nId => $i18nValue ) {
            $i18nElements[ $i18nId ] .= ' - ' . $elementToAdd;
        }
        return $i18nElements;
    }

    /**
     * protected function productExists
     *
     */
    protected function categoryExists( $id ) {
        list($rep) = $this->db_execute( 'category_exists', array( 'id' => $id ) );

        return $rep[ 'count' ] > 0;
    }

    /**
     * protected function productExists
     *
     */
    protected function productExists( $id ) {
        list($rep) = $this->db_execute( 'product_exists', array( 'id' => $id ) );
        return $rep[ 'count' ] > 0;
    }

    /**
     * protected function isProductAvailable
     *
     */
    protected function isProductAvailable( $id, $quantity = 0 ) {
        if( $this->productExists( $id ) ) {
            list($product) = $this->db_execute( 'product_get', array( 'id' => $id ) );
            if( !isset( $product[ 'active' ] ) || $product[ 'active' ] == true ) {
                if( $product[ 'stock' ] >= $quantity ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * protected function changeQuantity
     *
     */
    protected function changeQuantity( $id, $variant, $num, $operand = '=' ) {
        if( !$this->productExists( $id ) ) {
            return false;
        }
        // We update the product's stock (or product's global stock if it has variants, and if they have different stocks)
        list($product) = $this->db_execute( 'product_get', array( 'id' => $id ) );
        if( $operand == '=' ) {
            $product[ 'stock' ] = $num;
        } elseif( $operand == '+' ) {
            $product[ 'stock' ] += $num;
        } elseif( $operand == '-' ) {
            $product[ 'stock' ] -= $num;
        }
        $this->db_execute( 'product_set_stock', array( 'id' => $id, 'stock' => $product[ 'stock' ] ) );

        // We check if we also have to update the variant's stock
        if( $product[ 'hasVariants' ] && $product[ 'variants_change_stock' ] ) {
            // We do, so we get the initial value
            list($variant) = $this->db_execute( 'product_get_variant', array( 'product_id' => $id ) );
            // We calculate and update the stock
            if( $operand == '=' ) {
                $variant[ 'stock' ] = $num;
            } elseif( $operand == '+' ) {
                $variant[ 'stock' ] += $num;
            } elseif( $operand == '-' ) {
                $variant[ 'stock' ] -= $num;
            }
            $this->db_execute( 'variant_set_stock',
                               array( 'product_id' => $id, 'variant_id' => $variant, 'stock' => $product[ 'stock' ] ) );
        }
    }

    /**
     * public function switchProductState
     *
     */
    public function switchProductState( $id = null ) {
        if( is_null( $id ) ) {
            $id = $_GET[ 'id' ];
        }
        if( !$this->productExists( $id ) ) {
            echo 'ERROR';
            return false;
        }
        $this->db_execute( 'product_switch_active_state', array( 'id' => $id ), $qry );
        list($active) = $this->db_execute( 'product_get_active_state', array( 'id' => $id ), $qry );

        if( $active[ 'active' ] ) {
            $ret = 'true';
        } else {
            $ret = 'false';
        }
        echo $ret;
        return true;
    }

    public function inactiveProducts() {
        $this->onlyAdmin( true );

        $this->linker->html->setTitle( $this->getI18n( 'inactiveProducts_title' ) );

        $products = $this->db_execute( 'products_get_inactive', array( ) );

        if( !empty( $products ) ) {
            foreach( $products as $product ) {
                $values[ 'products' ][ ] = array(
                    'id' => $product[ 'id' ],
                    'name' => $this->getI18n( $product[ 'name' ] ),
                    'reference' => $product[ 'reference' ],
                    'shortDescription' => $this->getI18n( $product[ 'shortDescription' ] ),
                    'image' => $product[ 'image' ],
                    'price' => $product[ 'price' ],
                    'editLink' => $this->linker->path->getLink( __CLASS__ . '/editProduct/' . $product[ 'id' ] )
                );
            }

            $values[ 'pages' ][ 'switchState' ] = $this->linker->path->getLink(
                $this->shortClassName . '/switchProductState/'
            );

            $this->render( 'inactiveProducts', $values );
        } else {
            $this->render( 'inactiveProducts_empty', $values );
        }
    }

    public function getAllCategoriesList() {
        $categories = $this->db_execute( 'categories_get_list', array( ) );
        foreach( $categories as $id => $category ) {
            $categories[ $id ][ 'name' ] = $this->getI18n( $categories[ $id ][ 'name' ] );
            $categories[ $id ][ 'link' ] = $this->linker->path->getLink(
                __CLASS__ . '/showCategory/' . $categories[ $id ][ 'id' ]
            );
        }
        return $categories;
    }
    
    public function getProductsListForWEditor($field){
        $values['field']['id'] = $field;
        if(!isset($_POST['cat'])){
            $values['categories'] = $this->getAllCategoriesList();
            $values['link']['base'] = $_SERVER['REQUEST_URI'];
            return $this->render( 'productsListForWeditor', $values, false, false);
        }
        
        $products = $this->db_execute(
            'category_get_products',
            array(
            'category_id' => (int) $_POST['cat']
            )
        );
        if( !empty( $products ) ) {
            foreach( $products as $product ) {
                $values['products'][ $product[ 'product_id' ] ] = array(
                    'id' => $product[ 'product_id' ],
                    'name' => $this->getProductName( $product[ 'product_id' ] )
                );
            }
        }
        echo $this->render( 'productsListForWeditor_products', $values, false, false);
        exit;
    }

    //////////////////////////////////////////////////////////////////////////////////////
    //                                  NAVIGATOR PART                                  //
    //////////////////////////////////////////////////////////////////////////////////////

    /**
     * protected function getNavigator
     *
     */
    protected function getNavigator( $id ) {
        $this->linker->admin->insert(
            '<a href="' . $this->linker->path->getLink( 'shop/editCategory/' . $id )
            . '">Modifier cette catégorie</a>', 'Boutique', '001_45.png'
        );

        list($category) = $this->db_execute( 'category_get', array( 'id' => $id ) );
        $mainCategory = $category;
        $categories = $this->db_execute( 'category_get_children', array( 'parent' => $id ) );

        if( is_array( $categories ) ) {
            foreach( $categories as $categoryId ) {
                if( $this->categoryExists( $categoryId ) ) {
                    list($category) = $this->db_execute( 'category_get', array( 'id' => $categoryId ) );
                    $category[ 'id' ] = $categoryId;
                    $category[ 'type' ] = 'category';
                    $elements[ ] = $category;
                }
            }
        }
        $products = $this->db_execute( 'category_get_products', array( ) );

        if( is_array( $products ) ) {
            foreach( $products as $productId ) {
                if( $this->productExists( $productId ) ) {
                    list($product) = $this->db_execute( 'product_get', array( 'id' => $productId ) );
                    $product[ 'id' ] = $productId;
                    $product[ 'type' ] = 'product';
                    $elements[ ] = $product;
                }
            }
        }

        // Finds and shows the navigator
        $navigatorBar = $this->getNavigator_bar( $id );

        $getNavigator_action = 'getNavigator_' . $navigator_type;
        $navigator_content = $this->$getNavigator_action( $elements, $page );

        return $displaySelector . $navigatorBar . $navigator_content;
    }

    protected function get_type_selector() {
        $elementType = 'products';
        $navigator_type = $this->getProductListType();
        $params = $this->getProductsListingParams();
        $navigator_types = array_keys( $params );
        $selector = array( );
        if( isset( $navigator_types[ 'default' ] ) ) {
            unset( $navigator_types[ 'default' ] );
        }
        if( $navigator_type == '' || !in_array( $navigator_type, $navigator_types ) ) {
            $changeType = true;
        }
        if( is_array( $navigator_types ) && count( $navigator_types ) > 1 ) {
            $cpt = 0;
            foreach( $navigator_types as $file => $oneType ) {
                if( $cpt++ == 0 && $changeType ) {
                    $navigator_type = $oneType;
                    $_SESSION[ __CLASS__ ][ 'navigator_type' ] = $navigator_type;
                }
                if( $oneType != $navigator_type && $oneType != 'default' ) {
                    // We update the args list to create the url
                    $this->linker->path->parsed_url[ 'parsed_query' ][ self::ARG_PRODUCTLISTTYPE ] = $oneType;
                    $args = '';
                    $separator = '';
                    foreach( $this->linker->path->parsed_url[ 'parsed_query' ] as $argName => $argValue ) {
                        $args .= $separator . $argName . '=' . $argValue;
                        $separator = '&';
                    }
                    $destPage = $this->linker->path->uri . '?' . $args;
                    $selector[ 'selectors' ][ 'link_' . $oneType ] = $destPage;
                }
            }
        }

        return $selector;
    }

    /**
     * protected function get_category_bar
     *
     */
    protected function get_category_bar( $id ) {
        if( $id == 1 ) {
            $genealogy[ 'nav_levels' ] = array( );
            return $genealogy;
        }
        $genealogy[ 'nav_bar' ][ 'display' ] = 'true';
        $categories[ ] = $id;
        list($parent) = $this->db_execute( 'category_get_shortest_parent', array( 'id' => $id ) );
        while( $parent != array( ) ) {
            if( $parent[ 'parent' ] > 0 ) {
                $categories[ ] = $parent[ 'parent' ];
            }
            list($parent) = $this->db_execute( 'category_get_shortest_parent', array( 'id' => $parent[ 'parent' ] ) );
        }
        $categories = array_reverse( $categories );

        foreach( $categories as $category ) {
            $link = $this->linker->path->getLink(
                'shop/showCategory/' . $category
            );
            $link .= '?' . self::ARG_PRODUCTLISTTYPE . '=' . $this->getProductListType();
            $genealogy[ 'nav_levels' ][ $category ] = array(
                'id' => $category,
                'name' => $this->getCategoryName( $category ),
                'link' => $link
            );
            $children = $this->db_execute( 'category_get_children', array( 'parent' => $category ) );

            if( is_array( $children ) ) {
                foreach( $children as $child ) {
                    $link = $this->linker->path->getLink(
                        'shop/showCategory/' . $child[ 'id' ]
                    );
                    $link .= '?' . self::ARG_PRODUCTLISTTYPE . '=' . $this->getProductListType();
                    $genealogy[ 'nav_levels' ][ $category ][ 'daughters' ][ ] = array(
                        'name' => $this->getCategoryName( $child[ 'id' ] ),
                        'link' => $link
                    );
                }
            }
        }
        return $genealogy;
    }

    public function renderer_format( $values, $formatName = '' ) {
        if( $formatName == '' || $formatName == 'list' || $formatName == 'table' ) {
            return $values;
        }
        if( $formatName == 'grid' ) {
            $max = 9;
        }
        if( $formName == 'miniature' ) {
            $max = 12;
        }
    }

    protected function category_get_all_products( $id ) {
        static $cache = array( );
        if( !isset( $cache[ $id ] ) ) {
            $ret = array( );
            $products = $this->db_execute( 'category_get_products', array( 'category_id' => $id ) );
            if( !empty( $products ) ) {
                foreach( $products as $product ) {
                    $ret[ $product[ 'product_id' ] ] = array(
                        'id' => $product[ 'product_id' ],
                        'name' => $this->getProductName( $product[ 'product_id' ] )
                    );
                }
            }
            $cache[ $id ] = $ret;
        }
        return $cache[ $id ];
    }

    protected function get_category_products( $id ) {
        if( isset( $_GET[ 'page' ] ) ) {
            $page = $_GET[ 'page' ] - 1;
        }
        if( !isset( $page ) || $page < 0 ) {
            $page = 0;
        }
        $elementsByPage = 20;
        list($category) = $this->db_execute( 'category_get', array( 'id' => $id ) );
        $mainCategory = $category;

        $products = $this->db_execute( 'category_get_products', array( 'category_id' => $id ) );
        if( !empty( $products ) ) {
            foreach( $products as $productId ) {
                if( $this->productExists( $productId ) ) {

                    list($product) = $this->db_execute( 'product_get', array( 'id' => $productId ) );
                    if( !isset( $product[ 'active' ] ) || $product[ 'active' ] == true ) {
                        $product[ 'id' ] = $productId;
                        $product[ 'type' ] = 'product';
                        $elements[ 'category' ][ ] = $product;
                    }
                }
            }
            // Verifies if there is anything in the category. If not, we tell it and exit
            // this method
            if( !is_array( $elements ) || empty( $elements ) ) {
                return array( 'category' => array( 'empty' => true ) );
            }
            // Prepares the Next & Previous buttons
            $pageActuelle = preg_replace(
                '`(\?page=[0-9]+)`', '', $this->linker->path->uri
            );
            if( $page > 0 ) {
                $ret[ 'products' ][ 'previous' ] = true;
                $values[ 'products' ][ 'previous_link' ] = $pageActuelle . '?page=' . $page;
            }
            if( count( $elements ) >= (($page + 1) * ($elementsByPage + 1)) ) {
                $ret[ 'products' ][ 'next' ] = true;
                $values[ 'products' ][ 'next_link' ] = $pageActuelle . '?page=' . ($page + 2);
            }

            if( $nav_pages ) {
                for( $nav_pages_num = 1; $nav_pages_num <= ceil( count( $elements ) / $elementsByPage ); $nav_pages_num++ ) {
                    $class = '';
                    if( $nav_pages_num == $page + 1 ) {
                        $class = 'bold';
                    }
                    $values[ 'pages' ][ ] = array(
                        'link' => $pageActuelle . '?page=' . ($nav_pages_num),
                        'number' => $nav_pages_num,
                        'class' => $class
                    );
                }
            }

            // We only keep the used part (depending on $page and $elementsByPage).
            $elements = array_slice(
                $elements, ($page * $elementsByPage ), $elementsByPage
            );
            // And we loop on them, to set the required datas
            while( $element = array_shift( $elements ) ) {
                if( is_array( $element ) ) {
                    $values[ 'category_elements' ][ $cpt ][ 'navigator_image' ] = $element[ 'image' ];
                    $values[ 'category_elements' ][ $cpt ][ 'class' ] = 'list_image';
                    $values[ 'category_elements' ][ $cpt ][ 'navigator_desc' ] = $this->getI18n( $element[ 'shortDescription' ] );
                    $link = $this->linker->path->getLink( 'shop/showProduct/' . $element[ 'id' ] );
                    $values[ 'category_elements' ][ $cpt ][ 'navigator_link' ] = $link;
                    $values[ 'category_elements' ][ $cpt ][ 'navigator_ref' ] = $element[ 'ref' ];
                    $values[ 'category_elements' ][ $cpt ][ 'navigator_price' ] = $element[ 'price' ];
                    if( $this->sellingActivated ) {
                        $addToCartLink = $this->linker->path->getLink( 'shop/addToCart/' );
                        $addToCartLink .= '?product=' . $element[ 'id' ];
                        $values[ 'category_elements' ][ $cpt ][ 'pictos' ][ 0 ][ 'image' ] = '/images/shared/icons/picto_cart.png';
                        $values[ 'category_elements' ][ $cpt ][ 'pictos' ][ 0 ][ 'image_alt' ] = 'picto_cart';
                        $values[ 'category_elements' ][ $cpt ][ 'pictos' ][ 0 ][ 'link' ] = $addToCartLink;
                    }
                    $values[ 'category_elements' ][ $cpt ][ 'pictos' ][ 1 ][ 'image' ] = '/images/shared/icons/picto_details.png';
                    $values[ 'category_elements' ][ $cpt ][ 'pictos' ][ 1 ][ 'image_alt' ] = 'picto_details';
                    $values[ 'category_elements' ][ $cpt ][ 'pictos' ][ 1 ][ 'link' ] = $link;
                    $cpt++;
                }
            }
        } else {
            $elements = array( );
        }

        return $elements;
    }

    protected function get_category_contents_type( $id ) {
        list($type) = $this->db_execute( 'category_get_type', array( 'id' => $id ) );
        if( $type[ 'type' ] == 'products' ) {
            return self::PRODUCTS_CATEGORY;
        }
        return self::CATEGORIES_CATEGORY;
    }

    protected function get_category_elements( $id, $page = 0, $exclude = 0 ) {
        list($categoryType) = $this->db_execute( 'category_get_type', array( 'id' => $id ) );
        $categoryType = $categoryType[ 'type' ];

        $cpt = 0;
        $hideNullQuantityProducts = $this->getParam( 'hideNullQuantityProducts', false );

        if( $categoryType > 0 ) {
            // This category contains only categories
            // We get the template's params for this action
            $params = $this->getCategoriesListingParams();
            $number = $params[ 'categoriesNumber' ];
            $categories = $this->db_execute(
                'category_get_children_part', array( 'parent' => $id, 'start' => $number * $page, 'length' => $number )
            );

            $totalNumberOfElements = count( $categories );
            $values[ 'contains_categories' ] = true;
            $elements = $categories;
            $fillWith = '';
            if( isset( $params[ 'groupedBy' ] ) ) {
                $groupsSize = $params[ 'groupedBy' ];
                if( isset( $params[ 'fillWith' ] ) ) {
                    $fillWith = $params[ 'fillWith' ];
                }
            } else {
                $groupsSize = null;
            }


            foreach( $elements as $element ) {
                list($category) = $this->db_execute( 'category_get', array( 'id' => $element[ 'id' ] ) );
                $values[ 'category_elements' ][ $cpt ][ 'id' ] = $element[ 'id' ];
                $values[ 'category_elements' ][ $cpt ][ 'name' ] =
                    $this->getI18n( $category[ 'name' ] );
                $values[ 'category_elements' ][ $cpt ][ 'image' ] = $category[ 'image' ];
                $values[ 'category_elements' ][ $cpt ][ 'description' ] =
                    $this->getI18n( $category[ 'description' ] );
                $values[ 'category_elements' ][ $cpt ][ 'shortDescription' ] =
                    $this->getI18n( $category[ 'shortDescription' ] );
                $link = $this->linker->path->getLink( 'shop/showCategory/' . $element[ 'id' ] );
                $link .= '?' . self::ARG_PRODUCTLISTTYPE . '=' . $this->getProductListType();
                $values[ 'category_elements' ][ $cpt ][ 'link' ] = $link;
                $values[ 'category_elements' ][ $cpt ][ 'pictos' ][ 1 ][ 'link' ] = $link;
                $cpt++;
            }
        } elseif( $categoryType < 0 ) {
            // This category contains only products
            // We get the template's params for this action
            $params = $this->getProductsListingParams();
            $listType = $this->getProductListType();
            $number = $params[ $listType ][ 'productsNumber' ];

            if( $number == 0 ) {
                $number = 20;
            }
            if( empty( $exclude ) ) {
                $exclude = 0;
            }
            $products = $this->db_execute(
                'category_get_products_part',
                array(
                'category_id' => $id,
                'start' => $number * $page,
                'length' => $number,
                'exclude' => $exclude
                )
            );

            $elements = array( );
            foreach( $products as $key => $product ) {
                // We verify if the products are not disabled
                if( $product[ 'product_id' ] === $exclude ) {
                    unset( $products[ $id ] );
                } else {
                    $elements[ ] = $product[ 'product_id' ];
                }
            }

            list($totalNumberOfElements) = $this->db_execute(
                'category_count_products', array( 'category_id' => $id )
            );
            $totalNumberOfElements = $totalNumberOfElements[ 'count' ];
            $values[ 'contains_products' ] = true;

            $values[ 'listType' ] = $listType;

            if( isset( $params[ $listType ][ 'groupedBy' ] ) ) {
                $groupsSize = $params[ $listType ][ 'groupedBy' ];
                if( isset( $params[ $listType ][ 'fillWith' ] ) ) {
                    $fillWith = $params[ $listType ][ 'fillWith' ];
                }
            } else {
                $groupsSize = null;
            }

            foreach( $elements as $cpt => $element ) {
                list($product) = $this->db_execute( 'product_get', array( 'id' => $element ), $qry );
                $link = $this->linker->path->getLink( 'shop/showProduct/' . $element );

                list($smallestPrice) = $this->db_execute( 'product_get_smallest_price', array( 'product' => $element ) );

                if( $smallestPrice[ 'discount_id' ] == 0 ) {
                    $hasDiscount = false;
                    if( $product[ 'hasVariants' ] && $product[ 'variants_change_price' ] ) {
                        $price = $this->getI18n( 'product_price_from_before' );
                        $price .= $this->monney_format( $smallestPrice[ 'price' ], true, false );
                        $price .= $this->getI18n( 'product_price_from_after' );
                    } else {
                        $price = $this->monney_format( $smallestPrice[ 'price' ], true, false );
                    }
                } else {
                    $hasDiscount = true;
                    $price = $this->getI18n( 'product_price_from_before' );
                    $price .= $this->monney_format( $smallestPrice[ 'price' ], true, false );
                    $price .= $this->getI18n( 'product_price_from_after' );
                }
                $values[ 'category_elements' ][ $cpt ] = array(
                    'id' => $element,
                    'name' => $this->getI18n( $product[ 'name' ] ),
                    'shortDescription' => $this->getI18n( $product[ 'shortDescription' ] ),
                    'image' => $product[ 'image' ],
                    'link' => $link,
                    'reference' => $product[ 'reference' ],
                    'price' => $price,
                    'picto_show_link' => $link,
                    'hasDiscount' => $hasDiscount,
                    'hasVariants' => $product[ 'hasVariants' ]
                );
                if( !$product[ 'hasVariants' ] || !$product[ 'variants_change_ref' ] ) {
                    $values[ 'category_elements' ][ $cpt ][ 'showRef' ] = true;
                }
                if( $this->sellingActivated ) {
                    if( $product[ 'stock' ] > 0 ) {
                        $values[ 'category_elements' ][ $cpt ][ 'stock' ] = $product[ 'stock' ];
                        $addToCartLink = $this->linker->path->getLink( 'shop/addToCart/' );
                        $addToCartLink .= '?product=' . $element;
                        $values[ 'category_elements' ][ $cpt ][ 'picto_addToCart_link' ] = $addToCartLink;
                    } else {
                        $values[ 'category_elements' ][ $cpt ][ 'stock' ] = $this->getI18n( 'product_nomorestock' );
                    }
                }
            }
        } else {
            // This category is empty
            return array( 'category_elements' => array( 'empty' => true ) );
        }

        list($category) = $this->db_execute( 'category_get', array( 'id' => $id ) );

        // We group the elements using the apropriate function, depending on $params
        if( !is_null( $groupsSize ) ) {
            $arranged = $values[ 'category_elements' ];
            if( !empty( $fillWith ) ) {
                $elementsToAdd = ($number - count( $arranged ));
                for( $a = 0; $a < $elementsToAdd; $a++ ) {
                    $arranged[ ][ 'empty' ] = $fillWith;
                }
            }
            $groups = array_chunk( $arranged, $groupsSize );

            foreach( $groups as $id => $group ) {
                $values[ 'category_elements_groups' ][ $id ][ 'elements' ] = $group;
            }
        }

        $pagesNb = ceil( $totalNumberOfElements / $number );

        // We prepare the previous, next and direct links to other pages
        if( $pagesNb > 1 ) { // There is more than one page
            // We remove the page argument in the url
            unset( $this->linker->path->parsed_url[ 'parsed_query' ][ 'page' ] );
            $args = '';
            $separator = '';
            foreach( $this->linker->path->parsed_url[ 'parsed_query' ] as $argName => $argValue ) {
                $args .= $separator . $argName . '=' . $argValue;
                $separator = '&';
            }
            $destPage = $this->linker->path->uri . '?' . $args;

            // Previous link
            if( $page > 0 ) {
                $values[ 'pageNavigation' ][ 'previous' ] = true;
                $values[ 'pageNavigation' ][ 'previous_link' ] = $destPage . '&page=' . $page;
                $nav_pages = true;
            }
            // Next link
            if( ($pagesNb - 1) > $page ) {
                $values[ 'pageNavigation' ][ 'next' ] = true;
                $values[ 'pageNavigation' ][ 'next_link' ] = $destPage . '&page=' . ($page + 2);
                $nav_pages = true;
            }
            // Direct links to other pages
            if( $nav_pages ) {
                for( $nav_pages_num = 1; $nav_pages_num <= $pagesNb; $nav_pages_num++ ) {
                    $class = '';
                    if( $nav_pages_num == $page + 1 ) {
                        $class = 'bold';
                    }
                    $values[ 'pages' ][ ] = array(
                        'link' => $destPage . '&page=' . ($nav_pages_num),
                        'number' => $nav_pages_num,
                        'class' => $class
                    );
                }
            }
        }
        return $values;
    }

    /**
     * protected function getNavigator_grid
     *
     */
    protected function getNavigator_list( $elements, $page ) {
        $elementsByPage = 12;
        if( !is_array( $elements ) || empty( $elements ) ) {
            return $this->render( 'navigator_list_empty', array( ), false, false );
        }
        if( $page > 0 ) {
            $previous = true;
        }
        if( count( $elements ) > ($page + 1) * $elementsByPage ) {
            $next = true;
        }
        $pageActuelle = preg_replace(
            '`(\?page=[0-9]+)`', '', $this->linker->path->uri
        );
        if( $page > 0 ) {
            $values[ 'nav_previous' ][ 'image' ] = '/images/builder/nav/model1_previous.png';
            $values[ 'nav_previous' ][ 'link' ] = $pageActuelle . '?page=' . $page;
            $nav_pages = true;
        }
        if( count( $elements ) >= (($page + 1) * ($elementsByPage + 1)) ) {
            $values[ 'nav_next' ][ 'image' ] = '/images/builder/nav/model1_next.png';
            $values[ 'nav_next' ][ 'link' ] = $pageActuelle . '?page=' . ($page + 2);
            $nav_pages = true;
        }
        if( $nav_pages ) {
            for( $nav_pages_num = 1; $nav_pages_num <= ceil( count( $elements ) / $elementsByPage ); $nav_pages_num++ ) {
                $class = '';
                if( $nav_pages_num == $page + 1 ) {
                    $class = 'bold';
                }
                $values[ 'nav_pages' ][ ] = array(
                    'link' => $pageActuelle . '?page=' . ($nav_pages_num),
                    'number' => $nav_pages_num,
                    'class' => $class
                );
            }
        }


        $elements = array_slice( $elements, ($page * $elementsByPage ), $elementsByPage );
        while( $element = array_shift( $elements ) ) {
            if( is_array( $element ) ) {
                $values[ 'list' ][ $cpt ][ 'navigator_image' ] = $element[ 'image' ];
                $values[ 'list' ][ $cpt ][ 'class' ] = 'list_image';
                $values[ 'list' ][ $cpt ][ 'navigator_desc' ] = $this->getI18n( $element[ 'shortDescription' ] );
                if( $element[ 'type' ] == 'product' ) {
                    $link = $this->linker->path->getLink( 'shop/showProduct/' . $element[ 'id' ] );
                    $values[ 'list' ][ $cpt ][ 'navigator_link' ] = $link;
                    $values[ 'list' ][ $cpt ][ 'navigator_ref' ] = $element[ 'ref' ];
                    $values[ 'list' ][ $cpt ][ 'navigator_price' ] = $element[ 'price' ];
                    $addToCartLink = $this->linker->path->getLink( 'shop/addToCart/' );
                    $addToCartLink .= '?product=' . $element[ 'id' ];
                    $values[ 'list' ][ $cpt ][ 'pictos' ][ 0 ][ 'image' ] = '/images/shared/icons/picto_cart.png';
                    $values[ 'list' ][ $cpt ][ 'pictos' ][ 0 ][ 'image_alt' ] = 'picto_cart';
                    $values[ 'list' ][ $cpt ][ 'pictos' ][ 0 ][ 'link' ] = $addToCartLink;
                    $values[ 'list' ][ $cpt ][ 'pictos' ][ 1 ][ 'image' ] = '/images/shared/icons/picto_details.png';
                    $values[ 'list' ][ $cpt ][ 'pictos' ][ 1 ][ 'image_alt' ] = 'picto_details';
                    $values[ 'list' ][ $cpt ][ 'pictos' ][ 1 ][ 'link' ] = $link;
                } else {
                    $link = $this->linker->path->getLink( 'shop/showCategory/' . $element[ 'id' ] );
                    $values[ 'list' ][ $cpt ][ 'navigator_link' ] = $link;
                    $values[ 'list' ][ $cpt ][ 'navigator_noprice' ] = $link;
                    $values[ 'list' ][ $cpt ][ 'pictos' ][ 0 ][ 'image' ] = '/images/shared/icons/picto_details.png';
                    $values[ 'list' ][ $cpt ][ 'pictos' ][ 0 ][ 'image_alt' ] = 'picto_details';
                    $values[ 'list' ][ $cpt ][ 'pictos' ][ 0 ][ 'link' ] = $link;
                }
                $cpt++;
            }
        }

        return $this->render( 'navigator_list', $values, false, false );
    }

    /**
     * protected function getNavigator_grid
     *
     */
    protected function getNavigator_table( $elements, $page ) {
        $elementsByPage = 5;
        if( !is_array( $elements ) || empty( $elements ) ) {
            return $this->render( 'navigator_list_empty', array( ), false, false );
        }
        if( $page > 0 ) {
            $previous = true;
        }
        if( count( $elements ) > ($page + 1) * $elementsByPage ) {
            $next = true;
        }
        if( $page > 0 ) {
            $values[ 'nav_previous' ][ 'image' ] = '/images/builder/nav/model1_previous.png';
            $pageActuelle = preg_replace(
                '`(\?page=[0-9]+)`', '', $this->linker->path->uri
            );
            $values[ 'nav_previous' ][ 'link' ] = $pageActuelle . '?page=' . ($page - 1);
        } else {
            $values[ 'nav_previous' ][ 'image' ] = '/images/builder/nav/model1_noprevious.png';
        }
        if( count( $elements ) >= ($page * ($elementsByPage + 1)) ) {
            $values[ 'nav_next' ][ 'image' ] = '/images/builder/nav/model1_next.png';
            $pageActuelle = preg_replace( '`(\?page=[0-9]+)`', '', $this->linker->path->uri );
            $values[ 'nav_next' ][ 'link' ] = $pageActuelle . '?page=' . ($page + 2);
        } else {
            $values[ 'nav_next' ][ 'image' ] = '/images/builder/nav/model1_nonext.png';
        }
        $elements = array_slice(
            $elements, ($page * $elementsByPage ), $elementsByPage
        );

        while( $element = array_shift( $elements ) ) {
            if( is_array( $element ) ) {
                $values[ 'list' ][ $cpt ][ 'navigator_image' ] = $element[ 'image' ];
                $values[ 'list' ][ $cpt ][ 'class' ] = 'list_image';
                $values[ 'list' ][ $cpt ][ 'navigator_desc' ] = $this->getI18n( $element[ 'shortDescription' ] );
                if( $element[ 'type' ] == 'product' ) {
                    $link = $this->linker->path->getLink( 'shop/showProduct/' . $element[ 'id' ] );
                    $values[ 'list' ][ $cpt ][ 'navigator_link' ] = $link;
                    $values[ 'list' ][ $cpt ][ 'navigator_ref' ] = $element[ 'ref' ];
                    $values[ 'list' ][ $cpt ][ 'navigator_price' ] = $element[ 'price' ];
                    $addToCartLink = $this->linker->path->getLink( 'shop/addToCart/' );
                    $addToCartLink .= '?product=' . $element[ 'id' ];
                    $values[ 'list' ][ $cpt ][ 'pictos' ][ 0 ][ 'image' ] = '/images/shared/icons/picto_cart.png';
                    $values[ 'list' ][ $cpt ][ 'pictos' ][ 0 ][ 'image_alt' ] = 'picto_cart';
                    $values[ 'list' ][ $cpt ][ 'pictos' ][ 0 ][ 'link' ] = $addToCartLink;
                    $values[ 'list' ][ $cpt ][ 'pictos' ][ 1 ][ 'image' ] = '/images/shared/icons/picto_details.png';
                    $values[ 'list' ][ $cpt ][ 'pictos' ][ 1 ][ 'image_alt' ] = 'picto_details';
                    $values[ 'list' ][ $cpt ][ 'pictos' ][ 1 ][ 'link' ] = $link;
                } else {
                    $link = $this->linker->path->getLink( 'shop/showCategory/' . $element[ 'id' ] );
                    $values[ 'list' ][ $cpt ][ 'navigator_link' ] = $link;
                    $values[ 'list' ][ $cpt ][ 'navigator_noprice' ] = $link;
                    $values[ 'list' ][ $cpt ][ 'pictos' ][ 0 ][ 'image' ] = '/images/shared/icons/picto_details.png';
                    $values[ 'list' ][ $cpt ][ 'pictos' ][ 0 ][ 'image_alt' ] = 'picto_details';
                    $values[ 'list' ][ $cpt ][ 'pictos' ][ 0 ][ 'link' ] = $link;
                }
                $cpt++;
            }
        }

        return $this->render( 'navigator_table', $values, false, false );
    }

    /**
     * Returns the price as string (eventually with 'From ' before in the case of a product with variants)
     * @param int $product The id of the product
     * @param int $variant The id of the variant, or <b>null</b> (default) if we don't want the price of a variant,
     * or if the price doesn't have any.
     * @param bool $forceIfInactive <b>false</b> (default), if we want the method to return false if the product
     * is inactive, or <b>true</b> if we want the price anyway.
     */
    protected function product_get_price_str( $product, $variant = null, $forceIfInactive = false ) {

        if( !$forceIfInactive ) {
            list($productDatas) = $this->db_execute( 'product_get_active', array( 'id' => $product ) );
        } else {
            list($productDatas) = $this->db_execute( 'product_get', array( 'id' => $product ) );
        }

        // We check if the product has variants
        if( $productDatas[ 'hasVariants' ] && $productDatas[ 'variants_change_price' ] ) {
            if( is_null( $variant ) ) {
                // We return the smallest price, with the "From " text
                $ret = $this->getI18n( 'product_price_from_before' );
                $ret .= $this->monney_format( $productDatas[ 'price' ], true, false );
                $ret .= $this->getI18n( 'product_price_from_after' );
                return $ret;
            }
            // We return the variant's price
            list($variantDatas) = $this->db_execute( 'product_get_variant',
                                                     array( 'product_id' => $product, 'variant_id' => $variant ) );
            if( empty( $variantDatas ) ) {
                // The variant wasn\'t found
                return false;
            }
            return $this->monney_format( $variantDatas[ 'price' ], true, false );
        }
        return $this->monney_format( $productDatas[ 'price' ], true, false );
    }

    /**
     * Returns the price as double
     * @param int $product The id of the product
     * @param int $variant The id of the variant, or <b>null</b> (default) if the price doesn't have any.<br />
     * <b>Caution</b> Unlike product_get_price_str(), if we don't ask for a variant's price, and the product has variants
     * for whose the price changes, the method won't return a price, but <b>false</b>.
     * @param bool $forceIfInactive <b>false</b> (default), if we want the method to return false if the product
     * is inactive, or <b>true</b> if we want the price anyway.
     */
    protected function product_get_price( $product, $variant = null, $forceIfInactive = false ) {
        if( !$forceIfInactive ) {
            list($productDatas) = $this->db_execute( 'product_get_active', array( 'id' => $product ) );
        } else {
            list($productDatas) = $this->db_execute( 'product_get', array( 'id' => $product ) );
        }

        // We check if the product has variants
        if( $productDatas[ 'hasVariants' ] && $productDatas[ 'variants_change_price' ] ) {
            if( is_null( $variant ) ) {
                return false;
            }
            list($variantDatas) = $this->db_execute( 'product_get_variant',
                                                     array( 'product_id' => $product, 'variant_id' => $variant ) );
            if( empty( $variantDatas ) ) {
                return false;
            }
            return $variantDatas[ 'price' ];
        }
        return $productDatas[ 'price' ];
    }

    //////////////////////////////////////////////////////////////////////////////////////
    //                                  CART PART                                       //
    //////////////////////////////////////////////////////////////////////////////////////

    /**
     * public function addToCart
     *
     */
    public function addToCart( $product = null, $variant = null, $quantity = null, $redirect = true ) {
        if( !$this->sellingActivated ) {
            $this->linker->path->error( 404 );
        }
        // We check it it was added by php (shop->addToCart()), a GET or a POST request
        if( !is_null( $product ) ) {
            // called by php
            if( !is_null( $variant ) || $variant === 0 ) {
                $isVariant = true;
                $product .= '|' . $variant;
            }
        } else {
            // request
            $quantity = 1;
            list($product, $variant) = explode( '_', $_GET[ 'product' ] );
            $isVariant = !empty( $variant ) || $variant === '0';
            if( $isVariant ) {
                $product .= '|' . $variant;
            }
        }
        if( $redirect && !$isVariant ) {

            list($hasVariant) = $this->db_execute( 'product_has_variant', array( 'product_id' => $product ), $qry );
            if( $hasVariant[ 'hasVariants' ] ) {
                // We are not allowed to add this product, as we don't know which variant to add, so we redirect to the
                //product sheet
                $showProductLink = $this->linker->path->getLink( 'shop/showProduct/' . $product );
                $this->linker->path->redirect( $showProductLink );
                return false;
            }
        }

        if( isset( $_SESSION[ __CLASS__ ][ 'cart' ][ $product ] ) && $_SESSION[ __CLASS__ ][ 'cart' ][ $product ] > 0 ) {
            $_SESSION[ __CLASS__ ][ 'cart' ][ $product ] += $quantity;
        } else {
            $_SESSION[ __CLASS__ ][ 'cart' ][ $product ] = $quantity;
        }
        // Updating the total amounts
        $this->cart_updateTotalAmount();

        $this->linker->html->addSpecialContents( 'shop_cart_content', $this->get_cart_plugin() );
        if( $redirect ) {
            $this->linker->path->redirect( $this->shortClassName, 'cart_show' );
        }
        return true;
    }

    /**
     * Adds a product to the cart.
     * @param str $class The name of the class on which to call cart_modify($productId,$quantity) if the quantity
     * is changed (or the product deleted).
     * @param int $productId An integer representing the product. It will be passed to cart_modify($productId,$quantity)
     * if needed.
     * @param int|str $productQuantity The quantity of product to add. Defaults to 1.<br />
     * To remove some quantity, should be negative.<br />
     * May equal a constant like self::EXTERNAL_REMOVE_PRODUCT_FROM_CART, if we want to remove the product.<br />
     * If it starts with an =  sign, it means we want the new quantity to be this, no matter how much
     * there was before.
     * @param bool $redirectToCart If <b>false</b> (default), will return the link to the cart page.<br />
     * If <b>true</b>, will redirect to the cart page after the product is added.
     * @return str The link to the cart page, or nothing (redirection) if $redirectToCart is true.
     */
    public function addToCart_external( $class, $productId, $productQuantity = 1, $redirectToCart = true ) {
        $id = $class . '_' . $productId;

        $forceValue = false;
        if( substr( $productQuantity, 0, 1 ) == '=' ) {
            $productQuantity = substr( $productQuantity, 1 );
            $forceValue = true;
        }

        if( $productQuantity != 0 && $productQuantity != self::EXTERNAL_REMOVE_PRODUCT_FROM_CART ) {

            if( $forceValue ) {
                $_SESSION[ __CLASS__ ][ 'cart_external' ][ $id ][ 'qty' ] = $productQuantity;
            } elseif( isset( $_SESSION[ __CLASS__ ][ 'cart_external' ][ $id ] ) && $_SESSION[ __CLASS__ ][ 'cart_external' ][ $id ] > 0 ) {
                $_SESSION[ __CLASS__ ][ 'cart_external' ][ $id ][ 'qty' ] += $productQuantity;
            } else {
                $_SESSION[ __CLASS__ ][ 'cart_external' ][ $id ][ 'qty' ] = $productQuantity;
            }
            $_SESSION[ __CLASS__ ][ 'cart_external' ][ $id ][ 'class' ] = $class;
            $_SESSION[ __CLASS__ ][ 'cart_external' ][ $id ][ 'id' ] = $productId;
            if( $_SESSION[ __CLASS__ ][ 'cart_external' ][ $id ][ 'qty' ] < 1 ) {
                $this->cart_removeProduct( $id, false );
            }
        } elseif( $productQuantity === self::EXTERNAL_REMOVE_PRODUCT_FROM_CART ) {
            $this->cart_removeProduct( $id, false );
        }

        // Updating the total amounts
        $this->cart_updateTotalAmount();

        if( $redirectToCart ) {
            $this->linker->path->redirect( $this->shortClassName, 'cart_show' );
        }
        return $this->linker->path->getLink( $this->shortClassName . '/cart_show/' );
    }

    public function getQuantityFromCart_external( $class, $id ) {
        if( isset( $_SESSION[ __CLASS__ ][ 'cart_external' ][ $class . '_' . $id ] ) && $_SESSION[ __CLASS__ ][ 'cart_external' ][ $class . '_' . $id ] > 0 ) {
            return $_SESSION[ __CLASS__ ][ 'cart_external' ][ $class . '_' . $id ][ 'qty' ];
        }
        return 0;
    }

    public function addToCartMessage( $message ) {
        if( !empty( $_SESSION[ __CLASS__ ][ 'cart_message' ] ) ) {
            $_SESSION[ __CLASS__ ][ 'cart_message' ] .= '<br /><br />' . $message;
        } else {
            $_SESSION[ __CLASS__ ][ 'cart_message' ] = $message;
        }
    }

    protected function cart_containsProducts( $withExternalProducts = true ) {
        if( !is_array( $_SESSION[ __CLASS__ ][ 'cart' ] ) || count( $_SESSION[ __CLASS__ ][ 'cart' ] ) == 0 ) {
            $_SESSION[ __CLASS__ ][ 'cart' ] = array( );
            $normalProducts = false;
        } else {
            $normalProducts = true;
        }
        if( !$withExternalProducts || !is_array( $_SESSION[ __CLASS__ ][ 'cart_external' ] ) || count( $_SESSION[ __CLASS__ ][ 'cart_external' ] ) == 0 ) {
            $_SESSION[ __CLASS__ ][ 'cart_external' ] = array( );
            $externalProducts = false;
        } else {
            $externalProducts = true;
        }
        return $normalProducts || $externalProducts;
    }

    /**
     * public function cart_show
     *
     */
    public function cart_show() {
        if( !$this->sellingActivated ) {
            $this->linker->path->error( 404 );
        }
        $this->linker->events->onCartShow();
        $this->linker->cache->disable();
        $this->linker->html->setMetaTitle( $this->getI18n( 'cart_show_title' ) );

        if( $this->formSubmitted( 'cart_form' ) ) {
            $this->cart_updateQuantities( false );
            if( isset( $_POST[ 'save_cart' ] ) || isset( $_POST[ 'save_cart_x' ] ) ) {
                $this->linker->path->redirect( __CLASS__, 'cart_save' );
            }
            if( isset( $_POST[ 'update_quantities_x' ] ) ) {
                // We only had to update the quantities
                $done = true;
            }
            if( !$done ) {
                if( isset( $_POST[ 'accept_conditions' ] ) || !$this->getParam( 'forceUserToCheckConditions', true ) ) {
                    if( !$this->isConnected() && $this->getParam( 'paymentRequiresConnexion', true ) ) {
                        $this->linker->path->redirect( $this->shortClassName, 'command_connect' );
                    }
                    $needShipment = $this->getParam( 'shipping>activated', true ) && $_SESSION[ __CLASS__ ][ 'shipping' ][ 'type' ] != 'comeTakeIt';

                    if( $needShipment ) {
                        $this->linker->path->redirect( $this->shortClassName, 'command_set_shipper' );
                    } else {
                        $this->linker->path->redirect( $this->shortClassName, 'command_set_billing_address' );
                    }
                } else {
                    $this->linker->html->addMessage( $this->getI18n( 'cart_conditions_should_be_accepted' ) );
                }
            }
        }

        if( $this->getParam( 'forceUserToCheckConditions', true ) ) {
            $values[ 'conditions' ][ 'file' ] = $this->getParam( 'conditions', '' );
        }


        $this->linker->html->setTitle( '' );
        if( $this->cart_containsProducts() ) {
            $taxeIncluded = ($this->taxes == self::TAXES_INCLUDED);
            $totalPrice = new sh_price( $taxeIncluded, $this->taxRate / 100, 0 );
            foreach( $_SESSION[ __CLASS__ ][ 'cart_external' ] as $id => $product ) {
                $className = $product[ 'class' ];
                $class = $this->linker->$className;
                $shortId = $product[ 'id' ];
                $price = $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_PRICE );
                $tax = $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_TAXRATE );
                $values[ 'contents' ][ ] = array(
                    'image' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_IMAGE ),
                    'name' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_NAME ),
                    'reference' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_REFERENCE ),
                    'shortDescription' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_SHORTDESCRIPTION ),
                    'stock' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_STOCK ),
                    'quantity' => $product[ 'qty' ],
                    'id' => $id,
                    'price' => $this->monney_format(
                        $price, true, false
                    ),
                    'totalPrice' => $this->monney_format(
                        $price * $product[ 'qty' ], true, false
                    ),
                    'link' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_LINK ),
                    'taxRate' => $tax
                );
                $totalPrice->add(
                    new sh_price( $taxeIncluded, $tax / 100, $price * $product[ 'qty' ] )
                );
            }
            foreach( $_SESSION[ __CLASS__ ][ 'cart' ] as $askedId => $quantity ) {
                list($id, $variantId) = explode( '|', $askedId );
                // We verify if we the product [still] exists
                if( $this->productExists( $id ) ) {
                    list($product) = $this->db_execute( 'product_get', array( 'id' => $id ) );
                    $taxRate = $product[ 'taxRate' ];
                    $addToDescription = '';

                    // We update the product datas with the variant's datas if needed
                    if( $product[ 'hasVariants' ] ) {
                        list($variant) = $this->db_execute( 'product_get_variant',
                                                            array( 'product_id' => $id, 'variant_id' => $variantId ) );
                        if( $product[ 'variants_change_stock' ] ) {
                            // We don't use the global stock, but the variant's one
                            $product[ 'stock' ] = $variant[ 'stock' ];
                        }

                        $product[ 'price' ] = $this->product_get_price( $id, $variantId );

                        if( $product[ 'variants_change_ref' ] ) {
                            // We don't use the global ref, but the variant's one
                            $product[ 'reference' ] = $variant[ 'ref' ];
                        }
                        if( $product[ 'pack_id' ] == 0 ) {
                            // This is a simple product with variants
                            $cps = $this->variantCP_explode( $variant[ 'customProperties' ] );
                            $separator = '';
                            foreach( $cps as $cp ) {
                                $addToDescription .= $separator . $cp[ 'name' ] . ' : ' . $cp[ 'value' ];
                                $separator = '<br />';
                            }
                        }
                    }

                    if( $product[ 'pack_id' ] > 0 ) {
                        // As this is a pack, we should add some descriptions
                        $taxRate = $product[ 'taxRate' ];
                        $noShortDescription = true;
                        $packElements = explode( '|', $product[ 'pack_variant' ] );
                        // in this case, $cps doesn't contain [cpId]:[cpValue] but [product_id]:[variant_id]
                        $addToDescription = '<ul>';
                        foreach( $packElements as $packElement ) {
                            list($pack_product, $pack_variant) = explode( ':', $packElement );
                            list($productName) = $this->db_execute( 'product_get_name', array( 'id' => $pack_product ),
                                                                    $qry );
                            $addToDescription .= '<li>';
                            $addToDescription .= $this->getI18n( $productName[ 'name' ] );
                            list($productsVariant) = $this->db_execute(
                                'product_get_variant_if_any',
                                array( 'product_id' => $pack_product, 'variant_id' => $pack_variant )
                            );

                            if( !empty( $productsVariant ) ) {
                                $cps = $this->variantCP_explode( $productsVariant[ 'customProperties' ] );
                                foreach( $cps as $cp ) {
                                    $addToDescription .= '<br />' . $cp[ 'name' ] . ' : ' . $cp[ 'value' ];
                                }
                            }
                            $addToDescription .= '</li>';
                        }
                        $addToDescription .= '</ul>';
                        $isPack = true;

                        $taxRate = $product[ 'taxRate' ];
                    }


                    if( $quantity > 0 && $product[ 'stock' ] > 0 ) {
                        // We verify if we can sell the quantity asked
                        if( $quantity > $product[ 'stock' ] ) {
                            // The user asks for more than what we have in stock, so we can only sell all the stock
                            $quantity = $product[ 'stock' ];
                            $_SESSION[ __CLASS__ ][ 'cart' ][ $askedId ] = $quantity;
                            $_SESSION[ __CLASS__ ][ 'stocknotsufficient' ][ ] = $product[ 'name' ];
                        }
                        if( !$noShortDescription ) {
                            $shortDescription = $this->getI18n(
                                $product[ 'shortDescription' ]
                            );
                        } else {
                            $shortDescription = '';
                        }
                        $values[ 'contents' ][ ] = array(
                            'image' => $product[ 'image' ],
                            'name' => $this->getI18n( $product[ 'name' ] ),
                            'reference' => $product[ 'reference' ],
                            'shortDescription' => $shortDescription,
                            'stock' => $product[ 'stock' ],
                            'quantity' => $quantity,
                            'id' => $askedId,
                            'price' => $this->monney_format(
                                $product[ 'price' ], true, false
                            ),
                            'totalPrice' => $this->monney_format(
                                $product[ 'price' ] * $quantity, true, false
                            ),
                            'link' => $this->linker->path->getLink(
                                'shop/showProduct/' . $id
                            ),
                            'taxRate' => $taxRate,
                            'addToDescription' => $addToDescription,
                            'is_pack' => $isPack
                        );
                    } elseif( $product[ 'stock' ] == 0 ) {
                        // There is no stock anymore
                        $_SESSION[ __CLASS__ ][ 'noMoreStock' ][ ] = $product[ 'name' ];
                        unset( $_SESSION[ __CLASS__ ][ 'cart' ][ $id ] );
                    } else {
                        // We wanted a quantity of 0, so we delete it
                        unset( $_SESSION[ __CLASS__ ][ 'cart' ][ $id ] );
                    }
                    if( !is_numeric( $taxRate ) ) {
                        $taxRate = $this->taxRate;
                    }
                    $totalPrice->add(
                        new sh_price( $taxeIncluded, $taxRate / 100, $product[ 'price' ] * $quantity )
                    );
                }
            }
            if( is_array( $_SESSION[ __CLASS__ ][ 'noMoreStock' ] ) ) {
                foreach( $_SESSION[ __CLASS__ ][ 'noMoreStock' ] as $noMore ) {
                    $values[ 'noMoreStock' ][ ][ 'name' ] = $this->getI18n( $noMore );
                }
            }
            unset( $_SESSION[ __CLASS__ ][ 'noMoreStock' ] );
            if( is_array( $_SESSION[ __CLASS__ ][ 'stocknotsufficient' ] ) ) {
                foreach( $_SESSION[ __CLASS__ ][ 'stocknotsufficient' ] as $stocknotsufficient ) {
                    $values[ 'stocknotsufficient' ][ ][ 'name' ] = $this->getI18n(
                        $stocknotsufficient
                    );
                }
            }
            unset( $_SESSION[ __CLASS__ ][ 'stocknotsufficient' ] );

            $values[ 'general' ][ 'action' ] = $this->linker->path->getLink( 'shop/cart_doAction/' );

            $ht = $totalPrice->get( sh_price::PRICE_UNTAXED );
            $ttc = $totalPrice->get( sh_price::PRICE_TAXED );

            if( $totalPrice->get( sh_price::TAX_TYPE ) == sh_price::TAX_MODE_EXCLUDE ) {
                $values[ 'total' ][ 'ht' ] = $this->monney_format( $ht, true, false );
                $values[ 'total' ][ 'ttc' ] = $this->monney_format( $ttc, true, false );
            } else {
                $values[ 'total' ][ 'ht' ] = $this->monney_format( $ht, true, false, self::ROUND_TO_UPPER );
                $values[ 'total' ][ 'ttc' ] = $this->monney_format( $ttc, true, false );
            }
            $values[ 'total' ][ 'paid' ] = $ttc;
            $_SESSION[ __CLASS__ ][ 'cart_total' ] = $values[ 'total' ][ 'ttc' ];
            $_SESSION[ __CLASS__ ][ 'command' ][ 'total' ] = $values[ 'total' ];

            $this->linker->html->addSpecialContents( 'shop_cart_content', $this->get_cart_plugin() );
        } else {
            unset( $_SESSION[ __CLASS__ ][ 'cart_total' ] );
        }

        if( $this->linker->user->isConnected() ) {
            $values[ 'user' ][ 'connected' ] = true;
        }

        $values[ 'message' ][ 'content' ] = $_SESSION[ __CLASS__ ][ 'cart_message' ];
        $_SESSION[ __CLASS__ ][ 'cart_message' ] = '';

        if( count( $values[ 'contents' ] ) > 0 ) {
            $this->render( 'cart_show', $values );
        } else {
            $this->render( 'cart_empty', $values );
        }
    }

    protected function variantCP_explode( $variant ) {
        $ret = array( );
        $cps = explode( '|', $variant );
        foreach( $cps as $cp ) {
            list($cpId, $cpValue) = explode( ':', $cp );
            list($cpName) = $this->db_execute( 'customProperty_get_name', array( 'id' => $cpId ), $qry );
            $ret[ $cpId ][ 'name' ] = $this->getI18n( $cpName[ 'name' ] );
            $ret[ $cpId ][ 'value' ] = $this->getI18n( $cpValue );
        }
        return $ret;
    }

    /**
     * public function cart_removeProduct
     *
     */
    public function cart_removeProduct( $id = 'get', $redirectToCart = true ) {
        if( $id == 'get' ) {
            $id = $_GET[ 'product' ];
        }
        if( !$this->sellingActivated ) {
            $this->linker->path->error( 404 );
        }
        if( isset( $_SESSION[ __CLASS__ ][ 'cart' ][ $id ] ) ) {
            unset( $_SESSION[ __CLASS__ ][ 'cart' ][ $id ] );
        } elseif( isset( $_SESSION[ __CLASS__ ][ 'cart_external' ][ $id ] ) ) {
            $class = $_SESSION[ __CLASS__ ][ 'cart_external' ][ $id ][ 'class' ];
            if( $this->linker->$class->cart_remove_product( $id ) ) {
                unset( $_SESSION[ __CLASS__ ][ 'cart_external' ][ $id ] );
            }
        }
        if( $redirectToCart ) {
            $this->linker->path->redirect( $this->shortClassName, 'cart_show' );
        }
    }

    /**
     * public function cart_save
     *
     */
    public function cart_save() {
        $this->linker->html->setTitle( $this->getI18n( 'cart_save_title' ) );
        if( $this->formSubmitted( 'cart_save' ) ) {
            if( trim( empty( $_POST[ 'name' ] ) ) ) {
                $_POST[ 'name' ] = implode( $this->getI18n( 'cart_save_defaultName_middle' ),
                                                            $this->linker->datePicker->dateAndTimeToLocal() );
            }

            $this->db_execute(
                'carts_create',
                array( 'user' => $this->linker->user->get( 'id' ), 'name' => stripslashes( $_POST[ 'name' ] ) )
            );
            $cart_id = $this->db_insertId();
            // We insert 1 by 1 the elements that are in the cart...
            foreach( $_SESSION[ __CLASS__ ][ 'cart_external' ] as $id => $product ) {
                // ... starting by external products (why not?)
                $className = $product[ 'class' ];
                $this->db_execute(
                    'cart_add_content',
                    array(
                    'cart_id' => $cart_id,
                    'product_id' => $product[ 'id' ],
                    'variant_id' => '',
                    'class' => $className,
                    'quantity' => $product[ 'qty' ]
                    )
                );
            }
            foreach( $_SESSION[ __CLASS__ ][ 'cart' ] as $askedId => $quantity ) {
                // ... and finishing by the shop's products
                list($id, $variantId) = explode( '|', $askedId );
                $this->db_execute(
                    'cart_add_content',
                    array(
                    'cart_id' => $cart_id,
                    'product_id' => $id,
                    'variant_id' => $variantId,
                    'class' => '',
                    'quantity' => $quantity
                    )
                );
            }
            $values[ 'links' ][ 'profile_page' ];

            $msg = $this->render( 'cart_saved_successfully', $values, false, false );
            $this->linker->html->addMessage( $msg, false );
            $this->linker->path->redirect( __CLASS__, 'cart_show' );
        }
        $values[ 'cart' ][ 'name' ] = implode( $this->getI18n( 'cart_save_defaultName_middle' ),
                                                               $this->linker->datePicker->dateAndTimeToLocal() );
        $this->render( 'cart_save', $values );
    }

    /**
     * Updates the quantity in the cart for every element
     * @param bool $redirect If true, redurects to the cart page
     * @return bool Always returns true
     */
    protected function cart_updateQuantities( $redirect = true ) {
        if( !$this->sellingActivated ) {
            $this->linker->path->error( 404 );
        }
        $_SESSION[ __CLASS__ ][ 'shippingSelected' ] = false;
        $_SESSION[ __CLASS__ ][ 'externalActionsEverytime' ] = false;
        if( is_array( $_POST[ 'change_quantity' ] ) ) {
            foreach( $_POST[ 'change_quantity' ] as $element => $quantity ) {
                $quantity += 0;
                if( $quantity < 0 ) {
                    $quantity = 0;
                }
                if( isset( $_SESSION[ __CLASS__ ][ 'cart' ][ $element ] ) ) {
                    if( $quantity == 0 ) {
                        unset( $_SESSION[ __CLASS__ ][ 'cart' ][ $element ] );
                    } else {
                        $_SESSION[ __CLASS__ ][ 'cart' ][ $element ] = $quantity;
                    }
                } elseif( isset( $_SESSION[ __CLASS__ ][ 'cart_external' ][ $element ] ) ) {
                    // We first verify if we are allowed to change the quantity for this product
                    $class = $_SESSION[ __CLASS__ ][ 'cart_external' ][ $element ][ 'class' ];
                    $id = $_SESSION[ __CLASS__ ][ 'cart_external' ][ $element ][ 'id' ];
                    $external_classes[ $class ] = $class;
                    $newQuantity = $this->linker->$class->cart_modify( $id, $quantity );
                    /* if( $newQuantity !== false ) {
                      $_SESSION[__CLASS__]['cart_external'][$element] = $quantity;
                      } */
                }
            }
        }
        if( is_array( $external_classes ) ) {
            foreach( $external_classes as $class ) {
                $this->addToCartMessage( $this->linker->$class->getCartMessage() );
            }
        }
        if( $redirect ) {
            $this->linker->path->redirect( $this->shortClassName, 'cart_show' );
        }
        return true;
    }

    /**
     * Creates a string containing the number passed as argument, in the correct
     * format, and rounded in the correct way if necessary
     * @param double $number The number we want to format
     * @param bool $addCurrency <b>True (default)</b> to add the default currency.<br />
     * <b>false</b> to return the number only.
     * @param int $roundTo Method of the rounding (with 2 decimals):
     * <ul><li>self::ROUND_TO_LOWER (default) : Rounds using the floor() function.</li>
     * <li>self::ROUND_TO_NEARER : Rounds using the round() method with default parametters</li>
     * <li>self::ROUND_TO_UPPER : Rounds using the floor() function.</li></ul>
     * @return str The number, as a text (always with 2 decimals), with the
     * correct separators.
     */
    public function monney_format( $number, $addCurrency = true, $showTaxSymbol = true, $roundTo = self::ROUND_TO_LOWER ) {
        $number = str_replace( ',', '.', $number );
        if( $roundTo == self::ROUND_TO_NEARER ) {
            $number = round( $number, $this->decimals );
        } else {
            $factor = pow( 10, $this->decimals );
            if( $roundTo == self::ROUND_TO_UPPER ) {
                $number = ceil( $number * $factor ) / $factor;
            } else {
                $number = floor( ($number * $factor) . '.9' ) / $factor;
            }
        }
        if( $showTaxSymbol && $this->showTaxSymbol ) {
            $taxSymbol = ' ' . $this->taxes;
        } else {
            $taxSymbol = '';
        }
        if( $addCurrency ) {
            // Depending on the i18n, the currency could be placed before or after
            // the value
            return $this->currencyBefore . number_format(
                    $number, $this->decimals, $this->decSeparator, $this->thousSeparator
                ) . $this->currencyAfter . $taxSymbol;
        }
        return number_format(
                $number, $this->decimals, $this->decSeparator, $this->thousSeparator
        );
    }

    public function get_comeAndTakeIt_addresses() {

        $lines = explode(
            "\n", $this->getParam( 'shipping>comeTakeIt>addresses', '' ) . "\n"
        );
        $open = false;
        $cpt = 0;
        $address = '';
        foreach( $lines as $line ) {
            if( trim( $line ) == '' ) {
                if( $open ) {
                    $open = false;
                    $addresses[ $cpt++ ] .= $address;
                    $address = '';
                    $separator = '';
                }
            } else {
                $open = true;
                $address .= $separator . $line;
                $separator = '<br />';
            }
        }
        foreach( $addresses as $id => $address ) {
            $ret[ ] = array(
                'id' => $id,
                'address' => str_replace( '<br />', ' - ', $address ),
                'addressMultiline' => $address,
            );
        }

        return $ret;
    }

    protected function list_shipModes() {
        $supplyers = $this->getParam( 'shipping>supplyers', array( ) );
        $supCpt = 0;
        if( is_array( $supplyers ) ) {
            foreach( $supplyers as $id => $supplyer ) {
                if( isset( $supplyer[ 'activated' ] ) ) {
                    $values[ 'shipping' ][ $id ] = array(
                        'name' => stripslashes( $supplyer[ 'name' ] ),
                        'price' => $supplyer[ 'price' ],
                        'logo' => $supplyer[ 'logo' ],
                        'description' => stripslashes( $supplyer[ 'description' ] ),
                        'id' => $id
                    );
                    $supCpt++;
                }
            }
        }
        if( $this->getParam( 'shipping>comeTakeIt>activated', false ) ) {
            if( $this->getParam( 'shipping>comeTakeIt>price', 0 ) != 0 ) {
                $price = $this->getParam(
                    'shipping>comeTakeIt>price', 0
                );
                $values[ 'comeTakeIt' ][ 'price' ] = $price;
            }

            $lines = explode(
                "\n", $this->getParam( 'shipping>comeTakeIt>addresses', '' ) . "\n"
            );
            $open = false;
            $cpt = 0;
            $address = '';
            foreach( $lines as $line ) {
                if( trim( $line ) == '' ) {
                    if( $open ) {
                        $open = false;
                        $addresses[ $cpt++ ] .= $address;
                        $address = '';
                        $separator = '';
                    }
                } else {
                    $open = true;
                    $address .= $separator . $line;
                    $separator = '<br />';
                }
            }
            $values[ 'comeTakeIt' ][ 'addresses' ] = $addresses;
        }

        return $values;
    }

    protected function shippers_formatForRendering() {
        $supplyers = $this->getParam( 'shipping>supplyers', array( ) );
        $total = $_SESSION[ __CLASS__ ][ 'command' ][ 'total' ][ 'paid' ];

        $supCpt = 0;
        if( is_array( $supplyers ) ) {
            foreach( $supplyers as $id => $supplyer ) {
                if( isset( $supplyer[ 'activated' ] ) ) {
                    if( $total >= $supplyer[ 'minimum' ] ) {
                        if( $supplyer[ 'price' ] > 0 ) {
                            $price = $this->monney_format( $supplyer[ 'price' ] );
                        } else {
                            $price = $this->getI18n( 'free' );
                        }
                        $values[ 'shipModes' ][ ] = array(
                            'name' => stripslashes( $supplyer[ 'name' ] ),
                            'price' => $price,
                            'logo' => $supplyer[ 'logo' ],
                            'description' => stripslashes( $supplyer[ 'description' ] ),
                            'id' => $id
                        );
                        $supCpt++;
                    } else {
                        $this->linker->html->addMessage(
                            str_replace(
                                array( '[SHIPPER]', '[MIN]' ),
                                array( stripslashes( $supplyer[ 'name' ] ), $supplyer[ 'minimum' ] ),
                                                     $this->getI18n( 'supplyer_unavailable_minimum' )
                            ), false
                        );
                    }
                }
            }
        }
        if( $this->getParam( 'shipping>comeTakeIt>activated', false ) ) {
            if( $supCpt > 0 ) {
                $values[ 'moreThanOne' ][ 'shipModes' ] = true;
            }
            $values[ 'comeTakeIt' ][ 'activated' ] = 'checked';
            if( $this->getParam( 'shipping>comeTakeIt>price', 0 ) != 0 ) {
                $price = $this->monney_format(
                    $this->getParam(
                        'shipping>comeTakeIt>price', 0
                    )
                );
                $values[ 'comeTakeIt' ][ 'price' ] = $price;
            }

            $lines = explode(
                "\n", $this->getParam( 'shipping>comeTakeIt>addresses', '' ) . "\n"
            );
            $open = false;
            $cpt = 0;
            $address = '';
            foreach( $lines as $line ) {
                if( trim( $line ) == '' ) {
                    if( $open ) {
                        $open = false;
                        $addresses[ $cpt++ ] .= $address;
                        $address = '';
                        $separator = '';
                    }
                } else {
                    $open = true;
                    $address .= $separator . $line;
                    $separator = '<br />';
                }
            }
            if( count( $addresses ) == 1 ) {
                $values[ 'comeTakeIt_singleAddress' ] = array(
                    'id' => 0,
                    'address' => str_replace( '<br />', ' - ', $addresses[ 0 ] ),
                    'addressMultiline' => $addresses[ 0 ],
                );
            } else {
                foreach( $addresses as $id => $address ) {
                    $values[ 'comeTakeIt_addresses' ][ ] = array(
                        'id' => $id,
                        'address' => str_replace( '<br />', ' - ', $address ),
                        'addressMultiline' => $address,
                    );
                }
            }
        } elseif( $supCpt > 1 ) {
            $values[ 'moreThanOne' ][ 'shipModes' ] = true;
        } else {
            $values[ 'shipMode' ] = $values[ 'shipModes' ][ 0 ];
        }
        return $values;
    }

    protected function command_getReadOnly() {
        if( !is_array( $_SESSION[ __CLASS__ ][ 'cart' ] ) || count( $_SESSION[ __CLASS__ ][ 'cart' ] ) == 0 ) {
            $_SESSION[ __CLASS__ ][ 'cart' ] = array( );
            $normalProducts = false;
        } else {
            $normalProducts = true;
        }
        if( !is_array( $_SESSION[ __CLASS__ ][ 'cart_external' ] ) || count( $_SESSION[ __CLASS__ ][ 'cart_external' ] ) == 0 ) {
            $_SESSION[ __CLASS__ ][ 'cart_external' ] = array( );
            $externalProducts = false;
        } else {
            $externalProducts = true;
        }
        if( $normalProducts || $externalProducts ) {
            $total = 0;
            $taxes = 0;
            foreach( $_SESSION[ __CLASS__ ][ 'cart_external' ] as $id => $product ) {
                $className = $product[ 'class' ];
                $class = $this->linker->$className;
                $shortId = $product[ 'id' ];
                $price = $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_PRICE );
                $values[ 'contents' ][ ] = array(
                    'image' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_IMAGE ),
                    'name' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_NAME ),
                    'reference' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_REFERENCE ),
                    'shortDescription' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_SHORTDESCRIPTION ),
                    'stock' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_STOCK ),
                    'quantity' => $product[ 'qty' ],
                    'id' => $id,
                    'price' => $this->monney_format(
                        $price, true, false
                    ),
                    'totalPrice' => $this->monney_format(
                        $price * $product[ 'qty' ], true, false
                    ),
                    'link' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_LINK )
                );
                $tax = $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_TAXRATE );
                $taxes += ( $price * $product[ 'qty' ]) * $tax / 100;
                $total += $price * $product[ 'qty' ];
            }
            foreach( $_SESSION[ __CLASS__ ][ 'cart' ] as $askedId => $quantity ) {
                list($id, $variant) = explode( '|', $askedId );

                // We verify if we the product [still] exists
                if( $this->productExists( $id ) ) {
                    $addToDescription = '';

                    list($product) = $this->db_execute( 'product_get', array( 'id' => $id ) );

                    // We update the product datas with the variant's if needed
                    if( $product[ 'hasVariants' ] ) {
                        list($variant) = $this->db_execute( 'product_get_variant',
                                                            array( 'product_id' => $id, 'variant_id' => $variant ) );
                        if( $product[ 'variants_change_stock' ] ) {
                            // We don't use the global stock, but the variant's one
                            $product[ 'stock' ] = $variant[ 'stock' ];
                        }
                        if( $product[ 'variants_change_price' ] ) {
                            // We don't use the global price, but the variant's one
                            $product[ 'price' ] = $variant[ 'price' ];
                        }
                        if( $product[ 'variants_change_ref' ] ) {
                            // We don't use the global ref, but the variant's one
                            $product[ 'reference' ] = $variant[ 'ref' ];
                        }
                        $cps = $this->variantCP_explode( $variant[ 'customProperties' ] );

                        foreach( $cps as $cp ) {
                            $addToDescription .= '<br />' . $cp[ 'name' ] . ' : ' . $cp[ 'value' ];
                        }
                    }
                    if( $quantity > 0 && $product[ 'stock' ] > 0 ) {
                        // We verify if we can sell the quantity asked
                        if( $quantity > $product[ 'stock' ] ) {
                            // The user asks for more than what we have in stock, so we
                            // can only sell all the stock
                            $quantity = $product[ 'stock' ];
                            $_SESSION[ __CLASS__ ][ 'cart' ][ $askedId ] = $quantity;
                            $_SESSION[ __CLASS__ ][ 'stocknotsufficient' ][ ] = $product[ 'name' ];
                        }
                        $values[ 'contents' ][ ] = array(
                            'id' => $askedId,
                            'name' => $this->getI18n( $product[ 'name' ] ),
                            'reference' => $product[ 'reference' ] . $addToDescription,
                            'shortDescription' => $this->getI18n(
                                $product[ 'shortDescription' ]
                            ) . $addToDescription,
                            'quantity' => $quantity,
                            'priceOnly' => $product[ 'price' ],
                            'price' => $this->monney_format(
                                $product[ 'price' ], true, false
                            ),
                            'totalPrice' => $this->monney_format(
                                $product[ 'price' ] * $quantity, true, false
                            )
                        );
                    } elseif( $product[ 'stock' ] == 0 ) {
                        // There is no stock anymore
                        $_SESSION[ __CLASS__ ][ 'noMoreStock' ][ ] = $product[ 'name' ];
                    } else {
                        // We wanted a quantity of 0, so we delete it
                        unset( $_SESSION[ __CLASS__ ][ 'cart' ][ $id ] );
                    }
                    $taxRate = $product[ 'taxRate' ];
                    if( !is_numeric( $taxRate ) ) {
                        $taxRate = $this->taxRate;
                    }
                    $total += $product[ 'price' ] * $quantity;
                    $taxes += ( $product[ 'price' ] * $quantity) * $taxRate / 100;
                }
            }

            if( $this->getParam( 'shipping>activated', true ) && $_SESSION[ __CLASS__ ][ 'shippingSelected' ] ) {
                if( $_GET[ 'action' ] != 'change_shipMode' ) {
                    $price = $_SESSION[ __CLASS__ ][ 'shipping' ][ 'price' ];
                    $this->ship_getPrice( $total, $price, $explanation, $taxRate );

                    if( $_SESSION[ __CLASS__ ][ 'shipping' ][ 'type' ] == 'comeTakeIt' ) {
                        $values[ 'contents' ][ ] = array(
                            'id' => 0,
                            'name' => $this->getI18n( 'command_comeTakeItTitle' ),
                            'reference' => $_SESSION[ __CLASS__ ][ 'shipping' ][ 'address' ] . $explanation,
                            'quantity' => 1,
                            'price' => $this->monney_format( $price, true, false ),
                            'totalPrice' => $this->monney_format( $price, true, false )
                        );
                        $values[ 'shipping' ][ 'comeTakeIt' ] = true;
                    } else {
                        $values[ 'contents' ][ ] = array(
                            'id' => 0,
                            'name' => $this->getI18n( 'command_shippingTitle' ),
                            'reference' => $_SESSION[ __CLASS__ ][ 'shipping' ][ 'shipper' ] . $explanation,
                            'quantity' => 1,
                            'price' => $this->monney_format( $price, true, false ),
                            'totalPrice' => $this->monney_format( $price, true, false )
                        );
                        $values[ 'shipping' ][ 'shipper' ] = $_SESSION[ __CLASS__ ][ 'shipping' ][ 'shipper' ];
                        $values[ 'shipping' ][ 'toCustomer' ] = true;
                    }
                    $total += $price;
                    $taxes += $price * $taxRate / 100;
                }
            }

            if( is_array( $_SESSION[ __CLASS__ ][ 'noMoreStock' ] ) ) {
                foreach( $_SESSION[ __CLASS__ ][ 'noMoreStock' ] as $noMore ) {
                    $values[ 'noMoreStock' ][ ][ 'name' ] = $this->getI18n( $noMore );
                }
            }
            unset( $_SESSION[ __CLASS__ ][ 'noMoreStock' ] );
            if( is_array( $_SESSION[ __CLASS__ ][ 'stocknotsufficient' ] ) ) {
                foreach( $_SESSION[ __CLASS__ ][ 'stocknotsufficient' ] as $stocknotsufficient ) {
                    $values[ 'stocknotsufficient' ][ ][ 'name' ] = $this->getI18n(
                        $stocknotsufficient
                    );
                }
            }
            unset( $_SESSION[ __CLASS__ ][ 'stocknotsufficient' ] );

            $values[ 'message' ][ 'content' ] = $_SESSION[ __CLASS__ ][ 'cart_message' ];
            if( $this->taxes == self::TAXES_EXCLUDED ) {
                $values[ 'total' ][ 'ht' ] = $this->monney_format( $total, true, false );
                $values[ 'total' ][ 'paid' ] = $total + $taxes; // TTC with no format
                $values[ 'total' ][ 'ttc' ] = $this->monney_format(
                    $total + $taxes, true, false
                );
            } else {
                $values[ 'total' ][ 'ht' ] = $this->monney_format(
                    $total - $taxes, true, false, self::ROUND_TO_UPPER
                );
                $values[ 'total' ][ 'ttc' ] = $this->monney_format( $total, true, false );
                $values[ 'total' ][ 'paid' ] = $total; // TTC with no format
            }
        }
        $_SESSION[ __CLASS__ ][ 'command' ][ 'elements' ] = $values[ 'contents' ];
        $_SESSION[ __CLASS__ ][ 'command' ][ 'total' ] = $values[ 'total' ];

        $this->linker->html->addSpecialContents( 'shop_cart_content', $this->get_cart_plugin() );

        $values[ 'billing' ][ 'name' ] = $this->linker->user->get( 'lastName' ) . ' ' . $this->linker->user->get( 'name' );
        $ret = $this->render( 'command_getReadOnly', $values, false, false );
        return $ret;
    }

    protected function command_validate_choose_payment() {
        return true;
    }

    public function command_choose_payment() {
        $this->linker->html->setTitle( $this->getI18n( 'choose_payment_title' ) );
        $values[ 'summary' ][ ][ 'content' ] = $this->command_set_shipper_summary();

        $values[ 'summary' ][ ][ 'content' ] = $this->command_set_billing_address_summary();

        $needShipment = $this->getParam( 'shipping>activated', true ) && $_SESSION[ __CLASS__ ][ 'shipping' ][ 'type' ] != 'comeTakeIt';
        if( $needShipment ) {
            $values[ 'summary' ][ ][ 'content' ] = $this->command_set_shipping_address_summary();
        }

        if( $values[ 'command' ][ 'needShipment' ] && $_SESSION[ __CLASS__ ][ 'shipping' ][ 'type' ] != 'comeTakeIt' ) {
            $values[ 'summary' ][ ][ 'content' ] = $this->command_set_shipping_address_summary();
        }

        $extraDatas = $this->command_set_external_datas_summary();
        if( !empty( $extraDatas ) ) {
            $values[ 'summary' ][ ][ 'content' ] = $extraDatas;
        }

        // Saving the command as pending
        $paymentId = $this->linker->payment->createPaymentId();
        $pendingFile = SH_SITE_FOLDER . __CLASS__ . '/commands/pending/' . $paymentId . '.params.php';
        $this->linker->params->addElement( $pendingFile, true );
        $values[ 'content' ][ 'cart' ] = $_SESSION[ __CLASS__ ][ 'cart' ];
        $values[ 'content' ][ 'cart_external' ] = $_SESSION[ __CLASS__ ][ 'cart_external' ];
        $values[ 'content' ][ 'shipping' ] = $_SESSION[ __CLASS__ ][ 'shipping' ];
        $values[ 'content' ][ 'shipping_address' ] = $_SESSION[ __CLASS__ ][ 'shipping_address' ];
        $values[ 'content' ][ 'billing_address' ] = $_SESSION[ __CLASS__ ][ 'billing_address' ];
        $values[ 'content' ][ 'billing_mail' ] = $_SESSION[ __CLASS__ ][ 'billing_mail' ];
        $values[ 'content' ][ 'billing_phone' ] = $_SESSION[ __CLASS__ ][ 'billing_phone' ];
        $values[ 'content' ][ 'paymentMode' ] = $_SESSION[ __CLASS__ ][ 'paymentMode' ];
        $values[ 'content' ][ 'extra_datas' ] = $this->command_get_extra_stored_datas();
        $this->linker->params->set( $pendingFile, 'command', $values );
        $this->linker->params->set( $pendingFile, 'command>submit_date', date( 'U' ) );
        $this->linker->params->write( $pendingFile );
        $values[ 'paymentModes' ] = $this->linker->payment->getAvailablePaymentModes();

        // Payment modes
        $activated = $this->getParam( 'payment>modes', array( ) );

        if( is_array( $values[ 'paymentModes' ] ) ) {
            foreach( $values[ 'paymentModes' ] as $key => $paymentMode ) {
                if( !in_array( $paymentMode[ 'id' ], $activated ) ) {
                    unset( $values[ 'paymentModes' ][ $key ] );
                } else {
                    $bank = $this->linker->payment->get( $paymentMode[ 'id' ] );

                    if( $bank ) {
                        $payment = $bank->payment_prepare( $paymentId );
                        $currency = $this->getParam( 'currency' );
                        $bank->payment_setCurrency( $payment, $currency );
                        $bank->payment_setFailurePage( $payment, __CLASS__ . '/command_failure/' . $payment );
                        $bank->payment_setSuccessPage( $payment, __CLASS__ . '/command_success/' . $payment );
                        $bank->payment_setValidatedPage( $payment, __CLASS__ . '/command_validated/' . $payment );
                        $bank->payment_setUnauthorizedPage( $payment, __CLASS__ . '/command_unauthorized/' . $payment );

                        $price = $_SESSION[ __CLASS__ ][ 'command' ][ 'total' ][ 'ttc' ];
                        $toDecimals = $this->getParam( 'currencies>' . $currency . '>toDecimals', 100 );
                        $priceInDecimal = $_SESSION[ __CLASS__ ][ 'command' ][ 'total' ][ 'paid' ] * $toDecimals;
                        $bank->payment_setPrice( $payment, $price, $priceInDecimal );
                        $values[ 'paymentModes' ][ $key ][ 'form' ] = $bank->payment_action( $payment, $paymentId );
                        $activePaymentMode = $key;
                    } else {
                        unset( $values[ 'paymentModes' ][ $key ] );
                    }
                }
            }
        }
        $values[ 'command' ][ 'readonly' ] = $this->command_getReadOnly();

        $summary = $this->render( 'command_summary', $values, false, false );
        $afterTab = $this->render( 'command_choose_payment', $values, false, false );

        return $this->command_step( 'choose_payment', $summary, $afterTab );
    }

    protected function command_get_extra_stored_datas() {
        $classes = $this->get_shared_methods( 'billing_external_stored_datas' );
        $datas = array( );
        foreach( $classes as $class ) {
            $datas[ $class ] = $this->linker->$class->billing_external_stored_datas();
        }
        return $datas;
    }

    protected function command_set_external_datas_summary() {
        $values[ 'modify' ][ 'link' ] = $this->linker->path->getLink( __CLASS__ . '/command_set_external_datas/' );

        $external_step = '';
        $modifyLink = $this->linker->path->getLink( __CLASS__ . '/command_set_external_datas/' );
        foreach( $_SESSION[ __CLASS__ ][ 'command_external_classes' ] as $className ) {
            if( $this->linker->method_exists( $className, 'billing_get_external_step' ) ) {
                $external_step .= $this->linker->$className->billing_get_external_step_summary( $modifyLink );
            }
        }
        $values[ 'steps' ][ 'content' ] = $external_step;
        if( empty( $external_step ) ) {
            return '';
        }
        return $this->render( 'command_set_external_datas_summary', $values, false, false );
    }

    public function command_set_external_datas() {
        $this->linker->html->setTitle( $this->getI18n( 'external_datas_title' ) );
        $classes = $this->get_shared_methods( 'billing_external_classes' );
        $external_step = '';
        $_SESSION[ __CLASS__ ][ 'command_external_classes' ] = array( );
        foreach( $classes as $className ) {
            $external_step .= $this->linker->$className->billing_get_external_step();
            $_SESSION[ __CLASS__ ][ 'command_external_classes' ][ ] = $className;
        }
        $values[ 'steps' ][ 'content' ] = $external_step;
        $values[ 'command' ][ 'readonly' ] = $this->command_getReadOnly();
        $content = $this->render( 'command_set_external_datas', $values, false, false );
        return $this->command_step( 'set_external_datas', $content );
    }

    protected function command_validate_set_external_datas() {
        $rep = true;
        foreach( $_SESSION[ __CLASS__ ][ 'command_external_classes' ] as $class ) {
            if( $this->linker->method_exists( $class, 'billing_check_external_step' ) ) {
                // We add && $rep in the end to be able to show the error messages once for every error
                $rep = $this->linker->$class->billing_check_external_step() && $rep;
            }
        }
        return $rep;
    }

    protected function command_validate_set_billing_address() {
        $sendOk = true;
        if( sh_form_verifier::checkMail( $_POST[ 'mail' ] ) ) {
            $_SESSION[ __CLASS__ ][ 'billing_mail' ] = $_POST[ 'mail' ];
        } else {
            $sendOk = false;
            $this->linker->html->addMessage( $this->getI18n( 'error_noBillingMail' ) );
        }
        $phone = str_replace( array( ' ', '-', '.' ), '', $_POST[ 'phone' ] );
        if( preg_match( '`\+?[0-9]{10,}`', $phone ) ) {
            $_SESSION[ __CLASS__ ][ 'billing_phone' ] = $phone;
        } else {
            $sendOk = false;
            $this->linker->html->addMessage( $this->getI18n( 'error_noBillingPhone' ) );
        }
        $name = trim( $_POST[ 'name' ] );
        $address = trim( $_POST[ 'address' ] );
        $zip = trim( $_POST[ 'zip' ] );
        $city = trim( $_POST[ 'city' ] );
        $_SESSION[ __CLASS__ ][ 'billing_address' ] = array(
            'name' => $name,
            'address' => $address,
            'zip' => $zip,
            'city' => $city
        );
        if( $name == '' || $zip == '' || $city == '' ) {
            $sendOk = false;
            $this->linker->html->addMessage( $this->getI18n( 'error_noBillingAddress' ) );
        }
        if( $sendOk ) {
            $_SESSION[ __CLASS__ ][ 'billing_address' ][ 'selected' ] = true;
            $_SESSION[ __CLASS__ ][ 'command_completed_step' ][ 'set_billing_address' ] = 'set_billing_address';
            return true;
        }
        return false;
    }

    protected function command_validate_set_shipping_address() {
        $sendOk = true;
        $name = trim( $_POST[ 'name' ] );
        $address = trim( $_POST[ 'address' ] );
        $zip = trim( $_POST[ 'zip' ] );
        $city = trim( $_POST[ 'city' ] );
        $_SESSION[ __CLASS__ ][ 'shipping_address' ] = array(
            'name' => $name,
            'address' => $address,
            'zip' => $zip,
            'city' => $city,
            'more' => trim( $_POST[ 'address_more' ] )
        );
        if( $name == '' || $zip == '' || $city == '' ) {
            $sendOk = false;
            $this->linker->html->addMessage( $this->getI18n( 'error_noshippingAddress' ) );
        }
        if( $sendOk ) {
            $_SESSION[ __CLASS__ ][ 'shipping_address' ][ 'selected' ] = true;
            $_SESSION[ __CLASS__ ][ 'command_completed_step' ][ 'set_shipping_address' ] = 'set_shipping_address';
            return true;
        }
        return false;
    }

    protected function command_set_billing_address_summary() {
        $values[ 'billing' ] = $_SESSION[ __CLASS__ ][ 'billing_address' ];
        $values[ 'user' ][ 'mail' ] = $_SESSION[ __CLASS__ ][ 'billing_mail' ];
        $values[ 'user' ][ 'phone' ] = $_SESSION[ __CLASS__ ][ 'billing_phone' ];

        $values[ 'modify' ][ 'link' ] = $this->linker->path->getLink( __CLASS__ . '/command_set_billing_address/' );
        return $this->render( 'command_set_billing_address_summary', $values, false, false );
    }

    protected function command_set_shipping_address_summary() {
        $values[ 'shipping' ] = $_SESSION[ __CLASS__ ][ 'shipping_address' ];
        foreach( $values[ 'shipping' ] as $entry => $data ) {
            $values[ 'shipping' ][ $entry ] = nl2br( $data );
        }

        $values[ 'modify' ][ 'link' ] = $this->linker->path->getLink( __CLASS__ . '/command_set_shipping_address/' );
        return $this->render( 'command_set_shipping_address_summary', $values, false, false );
    }

    public function command_set_billing_address() {
        if( !$this->sellingActivated ) {
            $this->linker->path->error( 404 );
        }
        $this->linker->html->setTitle( $this->getI18n( 'billing_address_title' ) );
        $_SESSION[ __CLASS__ ][ 'billing_address' ][ 'selected' ] = false;
        $values[ 'billing' ] = $_SESSION[ __CLASS__ ][ 'billing_address' ];
        $values[ 'billing' ][ 'mail' ] = $_SESSION[ __CLASS__ ][ 'billing_mail' ];
        $values[ 'billing' ][ 'phone' ] = $_SESSION[ __CLASS__ ][ 'billing_phone' ];
        if( empty( $values[ 'billing' ][ 'mail' ] ) && $this->isConnected() ) {
            $values[ 'billing' ][ 'phone' ] = $this->linker->user->get( 'phone' );
            $values[ 'billing' ][ 'mail' ] = $this->linker->user->get( 'mail' );
            $values[ 'billing' ][ 'name' ] = $this->linker->user->get( 'completeName' );
            $values[ 'billing' ][ 'address' ] = $this->linker->user->get( 'address' );
            $values[ 'billing' ][ 'zip' ] = $this->linker->user->get( 'zip' );
            $values[ 'billing' ][ 'city' ] = $this->linker->user->get( 'city' );
        }

        $values = array_merge( $this->cart_getReadonlyCommand(), $values );
        $values[ 'command' ][ 'readonly' ] = $this->command_getReadOnly();

        $content = $this->render( 'command_set_billing_address', $values, false, false );
        return $this->command_step( 'set_billing_address', $content );
    }

    public function command_set_shipping_address() {
        if( !$this->sellingActivated ) {
            $this->linker->path->error( 404 );
        }
        $this->linker->html->setTitle( $this->getI18n( 'shipping_address_title' ) );
        $_SESSION[ __CLASS__ ][ 'shipping_address' ][ 'selected' ] = false;
        $values[ 'shipping' ] = $_SESSION[ __CLASS__ ][ 'shipping_address' ];
        if( empty( $values[ 'billing' ][ 'zip' ] ) ) {
            $values[ 'shipping' ] = $_SESSION[ __CLASS__ ][ 'billing_address' ];
        }

        $values[ 'command' ][ 'readonly' ] = $this->command_getReadOnly();

        $values = array_merge( $this->cart_getReadonlyCommand(), $values );

        $content = $this->render( 'command_set_shipping_address', $values, false, false );
        return $this->command_step( 'set_shipping_address', $content );
    }

    protected function command_step( $step, $content, $afterTab = '' ) {
        if( !$this->cart_containsProducts() ) {
            // We redirect to the cart, which should be shown as empty
            $this->linker->path->redirect( __CLASS__, 'cart_show' );
        }
        sh_cache::disable();

        $values[ 'command' ][ 'readonly' ] = $this->command_getReadOnly();

        foreach( $this->command_available_actions as $oneStep ) {
            if( isset( $_SESSION[ __CLASS__ ][ 'command_completed_step' ][ $oneStep ] ) ) {
                $values[ 'links' ][ $oneStep ] = $this->linker->path->getLink( __CLASS__ . '/command_' . $oneStep . '/' );
            }
        }
        $values[ 'command' ][ 'needShipment' ] = $this->getParam( 'shipping>activated', true );
        $isFirst = ($values[ 'command' ][ 'needShipment' ] && $step == 'set_shipper');
        $isFirst = $isFirst || (!$values[ 'command' ][ 'needShipment' ] && $step == 'set_billing_address');

        if( !$isFirst && $step != 'choose_payment' ) {
            $values[ 'navigation' ][ 'previous' ] = $this->getI18n( 'command_previous_step' );
        }
        if( $step != 'choose_payment' ) {
            $values[ 'navigation' ][ 'next' ] = $this->getI18n( 'command_next_step' );
        }
        $values[ 'step' ][ $step ] = $content;

        $values[ 'form' ][ 'action' ] = $this->linker->path->getLink( __CLASS__ . '/command_validate_step/' );

        // We check if there are external classes that want to show anything
        $values[ 'external_classes' ][ 'shown' ] = $this->command_areThereExternalDatas();

        if( !empty( $afterTab ) ) {
            $values[ 'afterTab' ][ 'content' ] = $afterTab;
        }

        if( $values[ 'command' ][ 'needShipment' ] && $_SESSION[ __CLASS__ ][ 'shipping' ][ 'type' ] != 'comeTakeIt' ) {
            $values[ 'shipping' ][ 'shown' ] = true;
        }

        if( $step == 'set_billing_address' ) {
            $values[ 'done' ][ 'set_shipper' ] = '/images/shared/icons/picto_validate.png';
        } elseif( $step == 'set_shipping_address' ) {
            $values[ 'done' ][ 'set_shipper' ] = '/images/shared/icons/picto_validate.png';
            $values[ 'done' ][ 'set_billing_address' ] = '/images/shared/icons/picto_validate.png';
        } elseif( $step == 'set_external_datas' ) {
            $values[ 'done' ][ 'set_shipper' ] = '/images/shared/icons/picto_validate.png';
            $values[ 'done' ][ 'set_billing_address' ] = '/images/shared/icons/picto_validate.png';
            $values[ 'done' ][ 'set_shipping_address' ] = '/images/shared/icons/picto_validate.png';
        } elseif( $step == 'choose_payment' ) {
            $values[ 'done' ][ 'set_shipper' ] = '/images/shared/icons/picto_validate.png';
            $values[ 'done' ][ 'set_billing_address' ] = '/images/shared/icons/picto_validate.png';
            $values[ 'done' ][ 'set_shipping_address' ] = '/images/shared/icons/picto_validate.png';
            $values[ 'done' ][ 'set_external_datas' ] = '/images/shared/icons/picto_validate.png';
        }

        $this->render( 'command_step', $values );
    }

    protected function command_areThereExternalDatas() {
        if( !isset( $_SESSION[ __CLASS__ ][ 'command_areThereExternalDatas' ] ) ) {
            $classes = $this->get_shared_methods( 'billing_external_classes' );
            $_SESSION[ __CLASS__ ][ 'command_areThereExternalDatas' ] = false;
            foreach( $classes as $className ) {
                $_SESSION[ __CLASS__ ][ 'command_areThereExternalDatas' ] = true;
                break;
            }
        }
        return $_SESSION[ __CLASS__ ][ 'command_areThereExternalDatas' ];
    }

    public function command_validate_step() {
        sh_cache::disable();
        if( $this->formSubmitted( 'command_step' ) ) {
            $step = $_POST[ 'step' ];
            $method = 'command_validate_' . $step;
            $activeStepId = array_search( $step, $this->command_available_actions );
            if( $this->$method() ) {
                $needShipment = $this->getParam( 'shipping>activated', true ) && $_SESSION[ __CLASS__ ][ 'shipping' ][ 'type' ] != 'comeTakeIt';
                if( isset( $_POST[ 'next' ] ) ) {
                    // We go to next step
                    $increment = 1;
                    $nextStep = $this->command_available_actions[ $activeStepId + $increment ];
                    if( $nextStep == 'set_shipping_address' && !$needShipment ) {
                        $increment++;
                    }
                    if( $nextStep == 'set_external_datas' && !$this->command_areThereExternalDatas() ) {
                        $increment++;
                    }
                    $nextStep = $this->command_available_actions[ $activeStepId + $increment ];
                } else {
                    // We go to previous step
                    $increment = 1;
                    $nextStep = $this->command_available_actions[ $activeStepId - $increment ];
                    if( $nextStep == 'set_shipping_address' && !$needShipment ) {
                        $increment++;
                        $nextStep = $this->command_available_actions[ $activeStepId - $increment ];
                    }
                    if( $nextStep == 'set_external_datas' && !$this->command_areThereExternalDatas() ) {
                        $increment++;
                        $nextStep = $this->command_available_actions[ $activeStepId - $increment ];
                    }
                }
                $this->linker->path->redirect( __CLASS__, 'command_' . $nextStep );
            }
            // There was an error in the form, so we show it again (the command_validate_* method should prepare the error
            //message itself
            $this->linker->path->redirect( __CLASS__, 'command_' . $step );
        }
        // There was an error in the submission of the form, so it should be a hacking. -> 404
        $this->linker->path->error( 404 );
    }

    public function command_connect() {
        if( $values[ 'command' ][ 'needShipment' ] ) {
            $action = 'command_set_shipper';
        } else {
            $action = 'command_set_billing_address';
        }
        if( !$this->isConnected() ) {
            $this->linker->html->setTitle( $this->getI18n( 'command_requires_connection' ) );
            $this->linker->user->afterAccountCreation_addLink(
                $this->linker->path->getLink( $this->shortClassName . '/cart_show/' ),
                                              $this->getI18n( 'accountCreation_getBackToCart' ),
                                                              '/images/shared/icons/picto_backToCart.png',
                                                              'shop_backToCart'
            );
            $this->linker->user->afterAccountValidation_addLink(
                $this->linker->path->getLink( $this->shortClassName . '/' . $action . '/' ),
                                              $this->getI18n( 'accountCreation_getBackToCommand' ),
                                                              '/images/shared/icons/picto_backToCart.png',
                                                              'shop_backToCommand'
            );
            $ret = $this->linker->user->connect( true, true );
            return true;
        }

        $this->linker->path->redirect( $this->shortClassName, $action );
    }

    public function command_force_comeTakeIt( $addressId ) {
        $shipModes = $this->list_shipModes();

        $_SESSION[ __CLASS__ ][ 'shipping' ][ 'price' ] = $shipModes[ 'comeTakeIt' ][ 'price' ];
        $_SESSION[ __CLASS__ ][ 'shipping' ][ 'type' ] = 'comeTakeIt';
        $_SESSION[ __CLASS__ ][ 'shipping' ][ 'comeTakeIt' ] = $addressId;
        $_SESSION[ __CLASS__ ][ 'shipping' ][ 'address' ] = $shipModes[ 'comeTakeIt' ][ 'addresses' ][ $addressId ];
    }

    protected function command_validate_set_shipper() {
        if( isset( $_POST[ 'shipMode' ] ) ) {
            $shipModes = $this->list_shipModes();

            $_SESSION[ __CLASS__ ][ 'shipping' ][ 'selected' ] = true;
            if( $_POST[ 'shipMode' ] == 1000 ) {
                $_SESSION[ __CLASS__ ][ 'shipping' ][ 'price' ] = $shipModes[ 'comeTakeIt' ][ 'price' ];
                $_SESSION[ __CLASS__ ][ 'shipping' ][ 'type' ] = 'comeTakeIt';
                $_SESSION[ __CLASS__ ][ 'shipping' ][ 'comeTakeIt' ] = $_POST[ 'comeAndTakeIt_address' ];
                $_SESSION[ __CLASS__ ][ 'shipping' ][ 'address' ] =
                    $shipModes[ 'comeTakeIt' ][ 'addresses' ][ $_POST[ 'comeAndTakeIt_address' ] ];
            } else {
                $_SESSION[ __CLASS__ ][ 'shipping' ][ 'price' ] =
                    $shipModes[ 'shipping' ][ $_POST[ 'shipMode' ] ][ 'price' ];
                $_SESSION[ __CLASS__ ][ 'shipping' ][ 'type' ] = 'shipper';
                $_SESSION[ __CLASS__ ][ 'shipping' ][ 'shipper' ] =
                    $shipModes[ 'shipping' ][ $_POST[ 'shipMode' ] ][ 'name' ];
            }
            $_SESSION[ __CLASS__ ][ 'shippingSelected' ] = true;
            $_SESSION[ __CLASS__ ][ 'command_completed_step' ][ 'set_shipper' ] = 'set_shipper';
            return true;
        }
        if( $_SESSION[ __CLASS__ ][ 'noShippingNeeded' ] ) {
            return true;
        }
        $this->linker->html->addMessage( $this->getI18n( 'shipping_youShouldPickOne' ) );
        return false;
    }

    protected function command_set_shipper_summary() {
        $price = $_SESSION[ __CLASS__ ][ 'command' ][ 'total' ][ 'ht' ];
        if( $this->getParam( 'shipping>activated', true ) ) {
            $price = $_SESSION[ __CLASS__ ][ 'shipping' ][ 'price' ];
            $this->ship_getPrice( $total, $price, $explanation, $taxRate );

            if( $_SESSION[ __CLASS__ ][ 'shipping' ][ 'type' ] == 'comeTakeIt' ) {
                $values[ 'shipping' ] = array(
                    'address' => $_SESSION[ __CLASS__ ][ 'shipping' ][ 'address' ] . $explanation,
                    'price' => $this->monney_format( $price, true, false ),
                    'totalPrice' => $this->monney_format( $price, true, false )
                );
                $values[ 'shipping' ][ 'comeTakeIt' ] = true;
            } else {
                $shippers = $this->getParam( 'shipping>supplyers', array( ) );
                foreach( $shippers as $shipper ) {
                    if( $shipper[ 'name' ] == $_SESSION[ __CLASS__ ][ 'shipping' ][ 'shipper' ] ) {
                        $logo = $shipper[ 'logo' ];
                        break;
                    }
                }
                $values[ 'shipping' ] = array(
                    'shipper' => $_SESSION[ __CLASS__ ][ 'shipping' ][ 'shipper' ] . $explanation,
                    'price' => $this->monney_format( $price, true, false ),
                    'totalPrice' => $this->monney_format( $price, true, false ),
                    'logo' => $logo
                );
            }
        } else {
            return '';
        }

        $values[ 'modify' ][ 'link' ] = $this->linker->path->getLink( __CLASS__ . '/command_set_shipper/' );

        return $this->render( 'command_set_shipper_summary', $values, false, false );
    }

    public function command_set_shipper() {
        // We verify if we have to choose a shipper (the products that are baught may all be free of shipping)
        // First in the external products (if any)
        $this->command_getReadOnly(); // this is used to update the total price
        $needsShipment = false;
        if( is_array( $_SESSION[ __CLASS__ ][ 'cart_external' ] ) ) {
            foreach( $_SESSION[ __CLASS__ ][ 'cart_external' ] as $id => $product ) {
                $className = $product[ 'class' ];
                $class = $this->linker->$className;
                $shortId = $product[ 'id' ];
                $needsShipment = $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_NEEDS_SHIPMENT );
                if( $needsShipment ) {
                    break;
                }
            }
        }
        if( is_array( $_SESSION[ __CLASS__ ][ 'cart' ] ) ) {
            if( !$needsShipment ) {
                // Second in the internal
                foreach( $_SESSION[ __CLASS__ ][ 'cart' ] as $id => $quantity ) {
                    list($id, $variant) = explode( '|', $id );
                    // We verify if we the product [still] exists
                    if( $this->productExists( $id ) ) {
                        list($product) = $this->db_execute( 'product_get', array( 'id' => $id ) );
                        if( $product[ 'noShippingCost' ] == false ) {
                            $needsShipment = true;
                            break;
                        }
                    }
                }
            }
        }

        $total = $_SESSION[ __CLASS__ ][ 'command' ][ 'total' ];
        $this->linker->html->setTitle( $this->getI18n( 'ship_chooseShipper_title' ) );
        if( !$needsShipment ) {
            $_SESSION[ __CLASS__ ][ 'shippingSelected' ] = true;
            $_SESSION[ __CLASS__ ][ 'noShippingNeeded' ] = true;
            $content = $this->render( 'command_no_shipper_needed', array( ), false, false );
        } else {
            $this->cart_updateQuantities( false );

            $values = $this->shippers_formatForRendering();

            $rules = $this->getParam( 'shipping>discounts', array( ) );
            $discounts = $this->discounts_formatForRendering();
            if( is_array( $discounts ) ) {
                $values = array_merge( $values, $discounts );
            };

            $content = $this->render( 'command_set_shipper', $values, false, false );
        }
        return $this->command_step( 'set_shipper', $content );
    }

    /**
     * This method creates the datas to make a read only cart, for the pages like
     * the payment mode chooser.
     * @return str The php array
     */
    protected function cart_getReadonlyCommand() {
        if( !is_array( $_SESSION[ __CLASS__ ][ 'cart' ] ) || count( $_SESSION[ __CLASS__ ][ 'cart' ] ) == 0 ) {
            $_SESSION[ __CLASS__ ][ 'cart' ] = array( );
            $normalProducts = false;
        } else {
            $normalProducts = true;
        }
        if( !is_array( $_SESSION[ __CLASS__ ][ 'cart_external' ] ) || count( $_SESSION[ __CLASS__ ][ 'cart_external' ] ) == 0 ) {
            $_SESSION[ __CLASS__ ][ 'cart_external' ] = array( );
            $externalProducts = false;
        } else {
            $externalProducts = true;
        }
        if( $normalProducts || $externalProducts ) {
            $taxeIncluded = ($this->taxes == self::TAXES_INCLUDED);
            $totalPrice = new sh_price( $taxeIncluded, $this->taxRate / 100, 0 );
            $total = 0;
            $taxes = 0;
            foreach( $_SESSION[ __CLASS__ ][ 'cart_external' ] as $id => $product ) {
                $className = $product[ 'class' ];
                $class = $this->linker->$className;
                $shortId = $product[ 'id' ];
                $price = $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_PRICE );

                $values[ 'contents' ][ ] = array(
                    'image' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_IMAGE ),
                    'name' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_NAME ),
                    'reference' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_REFERENCE ),
                    'shortDescription' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_SHORTDESCRIPTION ),
                    'stock' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_STOCK ),
                    'quantity' => $product[ 'qty' ],
                    'id' => $id,
                    'price' => $this->monney_format(
                        $price, true, false
                    ),
                    'totalPrice' => $this->monney_format(
                        $price * $product[ 'qty' ], true, false
                    ),
                    'link' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_LINK )
                );
                $tax = $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_TAXRATE );
                $totalPrice->add(
                    new sh_price( $taxeIncluded, $tax / 100, $price * $product[ 'qty' ] )
                );
                $taxes += ( $price * $product[ 'qty' ]) * $tax / 100;
                $total += $price * $product[ 'qty' ];
            }
            foreach( $_SESSION[ __CLASS__ ][ 'cart' ] as $askedId => $quantity ) {
                list($id, $variant) = explode( '|', $askedId );

                // We verify if we the product [still] exists
                if( $this->productExists( $id ) ) {
                    $addToDescription = '';

                    list($product) = $this->db_execute( 'product_get', array( 'id' => $id ) );

                    // We update the product datas with the variant's if needed
                    if( $product[ 'hasVariants' ] ) {
                        list($variant) = $this->db_execute( 'product_get_variant',
                                                            array( 'product_id' => $id, 'variant_id' => $variant ) );
                        if( $product[ 'variants_change_stock' ] ) {
                            // We don't use the global stock, but the variant's one
                            $product[ 'stock' ] = $variant[ 'stock' ];
                        }
                        if( $product[ 'variants_change_price' ] ) {
                            // We don't use the global price, but the variant's one
                            $product[ 'price' ] = $variant[ 'price' ];
                        }
                        if( $product[ 'variants_change_ref' ] ) {
                            // We don't use the global ref, but the variant's one
                            $product[ 'reference' ] = $variant[ 'ref' ];
                        }
                        $cps = $this->variantCP_explode( $variant[ 'customProperties' ] );

                        foreach( $cps as $cp ) {
                            $addToDescription .= '<br />' . $cp[ 'name' ] . ' : ' . $cp[ 'value' ];
                        }
                    }
                    if( $quantity > 0 && $product[ 'stock' ] > 0 ) {
                        // We verify if we can sell the quantity asked
                        if( $quantity > $product[ 'stock' ] ) {
                            // The user asks for more than what we have in stock, so we
                            // can only sell all the stock
                            $quantity = $product[ 'stock' ];
                            $_SESSION[ __CLASS__ ][ 'cart' ][ $askedId ] = $quantity;
                            $_SESSION[ __CLASS__ ][ 'stocknotsufficient' ][ ] = $product[ 'name' ];
                        }
                        $values[ 'contents' ][ ] = array(
                            'id' => $askedId,
                            'name' => $this->getI18n( $product[ 'name' ] ),
                            'reference' => $product[ 'reference' ] . $addToDescription,
                            'shortDescription' => $this->getI18n(
                                $product[ 'shortDescription' ]
                            ) . $addToDescription,
                            'quantity' => $quantity,
                            'priceOnly' => $product[ 'price' ],
                            'price' => $this->monney_format(
                                $product[ 'price' ], true, false
                            ),
                            'totalPrice' => $this->monney_format(
                                $product[ 'price' ] * $quantity, true, false
                            )
                        );
                    } elseif( $product[ 'stock' ] == 0 ) {
                        // There is no stock anymore
                        $_SESSION[ __CLASS__ ][ 'noMoreStock' ][ ] = $product[ 'name' ];
                    } else {
                        // We wanted a quantity of 0, so we delete it
                        unset( $_SESSION[ __CLASS__ ][ 'cart' ][ $id ] );
                    }
                    $taxRate = $product[ 'taxRate' ];
                    if( !is_numeric( $taxRate ) ) {
                        $taxRate = $this->taxRate;
                    }
                    $totalPrice->add(
                        new sh_price( $taxeIncluded, $taxRate / 100, $product[ 'price' ] * $quantity )
                    );
                    $total += $product[ 'price' ] * $quantity;
                    $taxes += ( $product[ 'price' ] * $quantity) * $taxRate / 100;
                }
            }

            if( $this->getParam( 'shipping>activated', true ) ) {
                $price = $_SESSION[ __CLASS__ ][ 'shipping' ][ 'price' ];
                $this->ship_getPrice( $total, $price, $explanation, $taxRate );

                if( $_SESSION[ __CLASS__ ][ 'shipping' ][ 'type' ] == 'comeTakeIt' ) {
                    $values[ 'contents' ][ ] = array(
                        'id' => 0,
                        'name' => $this->getI18n( 'command_comeTakeItTitle' ),
                        'reference' => $_SESSION[ __CLASS__ ][ 'shipping' ][ 'address' ] . $explanation,
                        'quantity' => 1,
                        'price' => $this->monney_format( $price, true, false ),
                        'totalPrice' => $this->monney_format( $price, true, false )
                    );
                    $values[ 'shipping' ][ 'comeTakeIt' ] = true;
                } else {
                    $values[ 'contents' ][ ] = array(
                        'id' => 0,
                        'name' => $this->getI18n( 'command_shippingTitle' ),
                        'reference' => $_SESSION[ __CLASS__ ][ 'shipping' ][ 'shipper' ] . $explanation,
                        'quantity' => 1,
                        'price' => $this->monney_format( $price, true, false ),
                        'totalPrice' => $this->monney_format( $price, true, false )
                    );
                    $values[ 'shipping' ][ 'shipper' ] = $_SESSION[ __CLASS__ ][ 'shipping' ][ 'shipper' ];
                    $values[ 'shipping' ][ 'toCustomer' ] = true;
                }
                $totalPrice->add(
                    new sh_price( $taxeIncluded, $taxRate / 100, $price )
                );
                $total += $price;
                $taxes += $price * $taxRate / 100;
            }

            if( is_array( $_SESSION[ __CLASS__ ][ 'noMoreStock' ] ) ) {
                foreach( $_SESSION[ __CLASS__ ][ 'noMoreStock' ] as $noMore ) {
                    $values[ 'noMoreStock' ][ ][ 'name' ] = $this->getI18n( $noMore );
                }
            }
            unset( $_SESSION[ __CLASS__ ][ 'noMoreStock' ] );
            if( is_array( $_SESSION[ __CLASS__ ][ 'stocknotsufficient' ] ) ) {
                foreach( $_SESSION[ __CLASS__ ][ 'stocknotsufficient' ] as $stocknotsufficient ) {
                    $values[ 'stocknotsufficient' ][ ][ 'name' ] = $this->getI18n(
                        $stocknotsufficient
                    );
                }
            }
            unset( $_SESSION[ __CLASS__ ][ 'stocknotsufficient' ] );

            $values[ 'message' ][ 'content' ] = $_SESSION[ __CLASS__ ][ 'cart_message' ];
            $values[ 'general' ][ 'action' ] = $this->linker->path->getLink(
                'shop/cart_doAction/'
            );


            $ht = $totalPrice->get( sh_price::PRICE_UNTAXED );
            $ttc = $totalPrice->get( sh_price::PRICE_TAXED );

            $_SESSION[ __CLASS__ ][ 'cart_total' ] = $values[ 'total' ][ 'ttc' ];

            if( $totalPrice->get( sh_price::TAX_TYPE ) == sh_price::TAX_MODE_EXCLUDE ) {
                $values[ 'total' ][ 'ht' ] = $this->monney_format( $ht, true, false );
                $values[ 'total' ][ 'ttc' ] = $this->monney_format( $ttc, true, false );
                $values[ 'total' ][ 'paid' ] = $ttc; // TTC with no format
            } else {
                $values[ 'total' ][ 'ht' ] = $this->monney_format( $ht, true, false, self::ROUND_TO_UPPER );
                $values[ 'total' ][ 'ttc' ] = $this->monney_format( $ttc, true, false );
                $values[ 'total' ][ 'paid' ] = $ttc; // TTC with no format
            }
        }
        $_SESSION[ __CLASS__ ][ 'command' ][ 'elements' ] = $values[ 'contents' ];
        $_SESSION[ __CLASS__ ][ 'command' ][ 'total' ] = $values[ 'total' ];

        $this->linker->html->addSpecialContents( 'shop_cart_content', $this->get_cart_plugin() );

        $values[ 'billing' ][ 'name' ] = $this->linker->user->get( 'lastName' ) . ' ' . $this->linker->user->get( 'name' );
        return $values;
    }

    public function command_failure( $session ) {
        // We should show the page telling the payment wasn't ok, so the command isn't validated
        $values[ 'link' ][ 'retry' ] = $this->linker->path->getLink( __CLASS__ . '/command_choose_payment/' );
        $this->render( 'payment_aborted', $values );
    }

    public function command_unauthorized( $session ) {
        if( !$this->sellingActivated ) {
            $this->linker->path->error( 404 );
        }
        // The payment was refused...
        // We should put the stock back and tell the owner not to send the command
        if( file_exists( SH_SITE_FOLDER . __CLASS__ . '/commands/validated/' . $session . '.params.php' ) ) {
            // There are some content to put back to the stocks
            $validated = SH_SITE_FOLDER . __CLASS__ . '/commands/validated/' . $session . '.params.php';
            $this->linker->params->addElement( $validated, true );
            $elements = $this->linker->params->get( $validated, '', array( ) );
        }
    }

    public function command_success( $session ) {
        unset( $_SESSION[ __CLASS__ ][ 'cart' ] );
        unset( $_SESSION[ __CLASS__ ][ 'cart_external' ] );
        unset( $_SESSION[ __CLASS__ ][ 'command' ] );
        $this->render( 'command_mailSent', array( ) );
    }

    public function command_validated( $session, $bank_code = 0 ) {
        file_put_contents( SH_SITE_FOLDER . __CLASS__ . '/temp_debug_brice.txt',
                           "session : $session\nbank_code : $bank_code\nFile to remove : " .
            SH_SITE_FOLDER . __CLASS__ . '/commands/pending/' . $session . '.params.php' );
        if( file_exists( SH_SITE_FOLDER . __CLASS__ . '/commands/validated/' . $session . '.params.php' ) ) {
            $this->render( 'command_already_sent' );
            return true;
        }

        $this->render( 'command_mailSent', array( ) );

        $pendingFile = SH_SITE_FOLDER . __CLASS__ . '/commands/pending/' . $session . '.params.php';
        $this->linker->params->addElement( $pendingFile, true );
        $command = $this->linker->params->get( $pendingFile, 'command', 'deleted' );
        $command[ 'content' ][ 'paymentMode' ] = $bank_code;
        if( $command != 'deleted' ) {
            $this->sendCommand( $session, $command );
            unlink( $pendingFile );
            return true;
        }
        return false;
    }

    public function getPendingCommandDatas( $paymentId, $session ) {
        $pendingFile = SH_SITE_FOLDER . __CLASS__ . '/commands/pending/' . $paymentId . '.params.php';
        $this->linker->params->addElement( $pendingFile, true );
        $command = $this->linker->params->get( $pendingFile, 'command', 'deleted' );
        return $command;
    }

    protected function ship_getPrice( $total, &$price, &$explanation = '', &$tax = 19.6 ) {
        $possibleDiscounts = array( 0 );
        $tax = $this->getParam( 'shipping>taxRate', 19.6 );
        if( !($price > 0) ) {
            return true;
        } else {
            $discounts[ 'rulePrice0' ] = $this->getParam( 'shipping>discounts>rulePrice0', '' );
            $discounts[ 'rulediscount0' ] = $this->getParam( 'shipping>discounts>rulediscount0', '' );
            $discounts[ 'rulePrice1' ] = $this->getParam( 'shipping>discounts>rulePrice1', '' );
            $discounts[ 'rulediscount1' ] = $this->getParam( 'shipping>discounts>rulediscount1', '' );
            $discounts[ 'rulePrice2' ] = $this->getParam( 'shipping>discounts>rulePrice2', '' );
            $discounts[ 'rulediscount2' ] = $this->getParam( 'shipping>discounts>rulediscount2', '' );
            for( $a = 0; $a < 3; $a++ ) {
                $minimumPrice = trim( $discounts[ 'rulePrice' . $a ] );
                if( $total >= $minimumPrice ) {
                    $discount = trim( $discounts[ 'rulediscount' . $a ] );
                    if( strpos( $discount, '%' ) !== false ) {
                        $possibleDiscounts[ ] = ceil( $price * trim( str_replace( '%', '', $discount ) ) ) / 100;
                    } else {
                        $possibleDiscounts[ ] = $discount;
                    }
                }
            }
        }

        $discount = max( $possibleDiscounts );
        if( $discount > 0 ) {
            $oldPrice = $price;
            $price -= $discount;
            if( $price < 0 ) {
                $discount = $price;
                $price = 0;
            }
            $explanation = $this->getI18n( 'ship_getPrice_explanation' );
            $explanation .= $this->monney_format( $oldPrice, false, false ) .
                '&#160;-&#160;' . $this->monney_format( $discount, false, false ) .
                '&#160;=&#160;' . $this->monney_format( $price );
        }
        return true;
    }

    public function cron_job( $type ) {
        sh_cache::disable();
        $start = time();
        if( $type == sh_cron::JOB_QUARTERHOUR ) {
            echo 'Shop : Sending the bills... ';
            // We check if there are any bill to send made
            $bills = $this->getParam( 'bills_to_create', array( ) );
            if( !empty( $bills ) ) {
                foreach( $bills as $billId => $bill ) {
                    if( $time + 60 * 12 > time() ) {
                        // We won't take the risk to have 2 cron jobs in the same time trying to do the same thing...
                        // so we stop after 12 minutes
                        echo 'Time is out, so we will continue on next cron job<br />';
                        echo 'Started at ' . $time . ', and time is now ' . ($time + 60 * 12) . '<br />';
                        break;
                    }
                    if( $bill != 'done' ) {
                        $this->sendBill( $bill );
                        $this->setParam( 'bills_to_create>' . $billId, 'done' );
                        $this->writeParams();
                        echo 'One bill has been sent...<br />';
                        flush();
                    }
                }
            } else {
                echo 'There is no bill to create and send';
            }
        } elseif( $type == sh_cron::JOB_HALFDAY ) {
            echo 'Shop : Deleting old pending files... ';
            // Cleaning old pending command files (older than 4 hours)
            $timestamp = floatval( date( 'YmdHi', mktime( date( 'H' ) - 4, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) ) ) );
            $pendingFiles = scandir( $this->commandsFolder . 'pending' );
            foreach( $pendingFiles as $file ) {
                if( substr( $file, 0, 1 ) != '.' ) {
                    $fileTimestamp = floatval( substr( $file, 0, 12 ) );
                    if( $fileTimestamp < $timestamp ) {
                        echo 'Removing old pending file ' . $file . '<br />';
                        unlink( $this->commandsFolder . 'pending/' . $file );
                        $pendingFilesDeleted = true;
                    }
                }
            }
            if( !$pendingFilesDeleted ) {
                echo 'There was no old pending files to delete<br />';
            }
        } elseif( $type == sh_cron::JOB_DAY ) {
            // We should update the prices, in case there are discounts that have
            //just started/ended
            echo 'Shop : Caching the prices... ';
            $this->cachePrices();
            echo 'Done!<br />';
        }
        return true;
    }

    protected function cachePrices( $cachePriceFor = null ) {
        // We first cache the price for all the products
        if( is_null( $cachePriceFor ) ) {
            // We remove all the cached prices
            $this->db_execute( 'prices_cache_remove' );

            // We list all the products that are active
            $products = $this->db_execute( 'products_get_active', array( ) );
        } else {
            $this->db_execute( 'prices_cache_remove_for_product', array( 'product' => $cachePriceFor ) );
            $products = $this->db_execute( 'product_get', array( 'id' => $cachePriceFor ) );
        }

        // and all the discounts
        $days = array( 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
        $day = $days[ date( 'w' ) ];
        $tempDiscounts = $this->db_execute( 'discounts_get_all_for_cache', array( 'day' => $day ), $qry );

        foreach( $tempDiscounts as $discount ) {
            $discounts[ $discount[ 'id' ] ] = $discount;
        }
        unset( $tempDiscounts );

        $tempDiscountsOnCategories = $this->db_execute( 'allCategories_getDiscounts', array( ) );
        foreach( $tempDiscountsOnCategories as $oneDiscountOnCategory ) {
            $discountsOnCategories[ $oneDiscountOnCategory[ 'category_id' ] ][ ] = $oneDiscountOnCategory;
        }
        unset( $tempDiscountsOnCategories );

        foreach( $products as $product ) {
            $onePrice = array( );
            // We get the categories
            $categories = $this->db_execute( 'product_get_categories', array( 'product_id' => $product[ 'id' ] ) );
            if( is_array( $categories ) ) {
                foreach( $categories as $category ) {
                    if( isset( $discountsOnCategories[ $category[ 'category_id' ] ] ) ) {
                        foreach( $discountsOnCategories[ $category[ 'category_id' ] ] as $discount ) {
                            $discountId = $discount[ 'discount_id' ];
                            if( isset( $discounts[ $discountId ] ) ) {
                                // We calculate the price with this promo
                                $minimum = $discounts[ $discountId ][ 'quantity' ];
                                if( $product[ 'hasVariants' ] && $product[ 'variants_change_price' ] ) {
                                    $variants = $this->db_execute( 'product_get_variants',
                                                                   array( 'product_id' => $product[ 'id' ] ) );
                                    foreach( $variants as $variant ) {
                                        $price = $this->calculatePriceWithDiscount( $variant[ 'price' ],
                                                                                    $discounts[ $discountId ] );
                                        $replacements = array(
                                            'product' => $product[ 'id' ],
                                            'variant' => $variant[ 'variant_id' ],
                                            'min_quantity' => $minimum,
                                            'price' => $price,
                                            'discount_id' => $discountId
                                        );
                                        $this->db_execute( 'prices_cache_insert', $replacements );
                                        $this->db_execute( 'prices_cache_update', $replacements );
                                    }
                                } else {
                                    $price = $this->calculatePriceWithDiscount( $product[ 'price' ],
                                                                                $discounts[ $discountId ] );
                                    $replacements = array(
                                        'product' => $product[ 'id' ],
                                        'variant' => 0,
                                        'min_quantity' => $minimum,
                                        'price' => $price,
                                        'discount_id' => $discountId
                                    );
                                    $this->db_execute( 'prices_cache_insert', $replacements );
                                    $this->db_execute( 'prices_cache_update', $replacements );
                                }
                            }
                        }
                    }
                }
            }

            $discountsOnProduct = $this->db_execute( 'product_getDiscounts', array( 'product_id' => $product[ 'id' ] ) );
            if( !empty( $discountsOnProduct ) ) {
                foreach( $discountsOnProduct as $discountId ) {
                    // We calculate the price with this promo
                    $discount = $discounts[ $discountId[ 'discount_id' ] ];
                    $minimum = $discount[ 'quantity' ];
                    if( $product[ 'hasVariants' ] && $product[ 'variants_change_price' ] ) {
                        $variants = $this->db_execute( 'product_get_variants', array( 'product_id' => $product[ 'id' ] ) );
                        foreach( $variants as $variant ) {
                            $price = $this->calculatePriceWithDiscount( $variant[ 'price' ], $discount );
                            $replacements = array(
                                'product' => $product[ 'id' ],
                                'variant' => $variant[ 'variant_id' ],
                                'min_quantity' => $minimum,
                                'price' => $price,
                                'discount_id' => $discountId[ 'discount_id' ]
                            );
                            $this->db_execute( 'prices_cache_insert', $replacements );
                            $this->db_execute( 'prices_cache_update', $replacements );
                        }
                    } else {
                        $price = $this->calculatePriceWithDiscount( $product[ 'price' ], $discount );
                        $replacements = array(
                            'product' => $product[ 'id' ],
                            'variant' => 0,
                            'min_quantity' => $minimum,
                            'price' => $price,
                            'discount_id' => $discountId[ 'discount_id' ]
                        );
                        $this->db_execute( 'prices_cache_insert', $replacements );
                        $this->db_execute( 'prices_cache_update', $replacements );
                    }
                }
            }

            if( $product[ 'hasVariants' ] && $product[ 'variants_change_price' ] ) {
                $variants = $this->db_execute( 'product_get_variants', array( 'product_id' => $product[ 'id' ] ) );
                foreach( $variants as $variant ) {
                    $replacements = array(
                        'product' => $product[ 'id' ],
                        'variant' => $variant[ 'variant_id' ],
                        'min_quantity' => 1,
                        'price' => $variant[ 'price' ],
                        'discount_id' => 0
                    );
                    $this->db_execute( 'prices_cache_insert', $replacements );
                    $this->db_execute( 'prices_cache_update', $replacements );
                }
            } else {
                $replacements = array(
                    'product' => $product[ 'id' ],
                    'variant' => 0,
                    'min_quantity' => 1,
                    'price' => $product[ 'price' ],
                    'discount_id' => 0
                );
                $this->db_execute( 'prices_cache_insert', $replacements );
                $this->db_execute( 'prices_cache_update', $replacements );
            }
        }

        if( is_null( $cachePriceFor ) ) {
            // We remove all the cached prices
            $this->db_execute( 'categories_cache_remove' );
            // We list all the products that are active
            $productsCategories = $this->db_execute( 'categories_get_containing_products' );
        } else {
            $categories = $this->db_execute( 'product_get_categories', array( 'product_id' => $cachePriceFor ) );
            $productsCategories = array( );
            $categories_list = '';
            $separator = '';
            foreach( $categories as $category ) {
                $categories_list .= $separator . $category[ 'category_id' ];
                $separator = ',';
                $productsCategories[ 'id' ] = $category[ 'category_id' ];
            }
            $products = $this->db_execute( 'categories_cache_remove_list', array( 'product_id' => $cachePriceFor ) );
        }

        // We then cache special datas for the categories, depending on cached prices for products
        foreach( $productsCategories as $oneCategory ) {

            $products = $this->db_execute( 'category_get_products', array( 'category_id' => $oneCategory[ 'id' ] ) );
            $productsList = '';
            $separator = '';
            foreach( $products as $product ) {
                $productsList .= $separator . $product[ 'product_id' ];
                $separator = ',';
            }
            $priceForLastQuantity = false;
            $quantities = $this->db_execute( 'prices_cache_get_quantities_for_products',
                                             array( 'products_list' => $productsList ) );
            if( !empty( $quantities ) ) {
                foreach( $quantities as $oneQuantity ) {
                    $prices = $this->db_execute( 'prices_cache_get_for_products',
                                                 array( 'products_list' => $productsList, 'min_quantity' => $oneQuantity[ 'min_quantity' ] ) );
                    $min_price = $prices[ 0 ];
                    $refPrice = false;
                    $multiplePrice = false;
                    foreach( $prices as $onePrice ) {
                        if( $refPrice ) {
                            if( $onePrice[ 'price' ] != $refPrice ) {
                                $multiplePrice = true;
                            }
                        } else {
                            $refPrice = $onePrice[ 'price' ];
                        }
                    }
                    $this->db_execute(
                        'category_cache_insert',
                        array(
                        'category' => $oneCategory[ 'id' ],
                        'min_quantity' => $oneQuantity[ 'min_quantity' ],
                        'price' => $min_price[ 'price' ],
                        'multiplePrices' => $multiplePrice
                        )
                    );
                }
            }
        }
    }

    protected function calculatePriceWithDiscount( $price, $discount ) {
        if( $discount[ 'type' ] == 'percents' ) {
            $percents = 1 - $discount[ 'percents' ] / 100;
            $price *= $percents;
        } elseif( $discount[ 'type' ] == 'monney' ) {
            $price -= $discount[ 'monney' ];
            if( $price < 0 ) {
                $price = 0;
            }
        }
        return $price;
    }

    protected function sendBill( $bill ) {
        if( file_exists( $bill[ 'datas' ] ) ) {
            include($bill[ 'datas' ]); // gets the datas needed by the pdf builder
            $pdf = $this->linker->pdf;

            echo 'PDF : creating (' . microtime( true ) . ')<br />';
            flush();
            $pdfFile = $pdf->createBill( $command, substr( $bill[ 'datas' ], 0, -4 ) . '.pdf' );
            echo 'Done (' . microtime( true ) . ')<br />';
            echo $pdfFile . '<br />';

            echo 'Sending mail<br />';
            flush();

            // Sending the mails
            $mailer = $this->linker->mailer->get();
            $mail_customer = $mailer->em_create();

            $mailer->em_addSubject(
                $mail_customer, 'http://' . $this->linker->path->getDomain() . $this->getI18n( 'bill_mailTitle' )
            );
            $mailer->em_addContent( $mail_customer, $bill[ 'billContent' ] );
            $mailer->em_attach(
                $mail_customer, $pdfFile, 'command_' . date( 'd-m-Y_His' ) . '.pdf'
            );

            // Customer's mail
            $address = $command[ 'client' ][ 'mail' ];
            if( !$mailer->em_send( $mail_customer, array( array( $address ) ) ) ) {
                // Error sending the email
                echo 'Erreur dans l\'envoi du mail de validation...';
            } else {
                echo 'Le mail a bien été envoyé à ' . $address . '<br />';
            }

            $mail_seller = $mailer->em_create();
            $mailer->em_addSubject(
                $mail_seller, 'http://' . $this->linker->path->getDomain() . $this->getI18n( 'bill_mailTitle' )
            );
            $mailer->em_addContent( $mail_seller, $bill[ 'billContent' ] );
            $mailer->em_attach(
                $mail_seller, $pdfFile, 'command_' . date( 'd-m-Y_His' ) . '.pdf'
            );
            // Admin's mail
            if( !empty( $command[ 'extra_docs_mail' ] ) ) {
                foreach( $command[ 'extra_docs_mail' ] as $class ) {
                    if( is_array( $class ) ) {
                        foreach( $class as $element ) {
                            echo 'Attaching doc ' . $element[ 'file' ] . ' with the name ' . $element[ 'name' ] . '<br />';
                            $mailer->em_attach(
                                $mail_seller, $element[ 'file' ], $element[ 'name' ]
                            );
                        }
                    }
                }
            }
            $mails = explode( "\n", $this->getParam( 'command_mail' ) );
            $otherMailsCLasses = $this->get_shared_methods( 'bills_addSendTo' );
            foreach( $otherMailsCLasses as $class ) {
                $otherMails = $this->linker->$class->bills_addSendTo( $bill );
                foreach( $otherMails as $oneOtherMail ) {
                    if( !in_array( $oneOtherMail, $mails ) ) {
                        $mails[ ] = $oneOtherMail;
                    }
                }
            }

            if( is_array( $mails ) ) {
                $mailer->em_addContent( $mail_seller, $bill[ 'billContentSender' ] );
                foreach( $mails as $oneMail ) {
                    $mailer->em_addAddress( $mail_seller, $oneMail );
                }
                if( !$mailer->em_send( $mail_seller ) ) {
                    echo 'There was a problem sending the mail to the seller...';
                } else {
                    echo 'The mail was successfully sent to ' . print_r( $mails, true ) . '<br />';
                }
            } else {
                echo 'No seller mail is set...';
            }
        } else {
            echo basename( __FILE__ ) . ':' . __LINE__ . ' - File ' . $bill[ 'data' ] . ' not found<br />';
        }
    }

    protected function sendCommand( $session, $command ) {
        if( !$this->sellingActivated ) {
            $this->linker->path->error( 404 );
        }

        $elementsInCart = (is_array( $command[ 'content' ][ 'cart' ] ) && count( $command[ 'content' ][ 'cart' ] ) > 0);
        $elementsInCartExternal = (is_array( $command[ 'content' ][ 'cart_external' ] ) && count( $command[ 'content' ][ 'cart_external' ] ) > 0);
        $elementsToUpdate = array( );
        if( $elementsInCart || $elementsInCartExternal ) {
            $datas[ 'titles' ] = array(
                $this->getI18n( 'bill_table_ref' ),
                $this->getI18n( 'bill_table_product' ),
                $this->getI18n( 'bill_table_price' ) . ' ' . $this->taxes,
                $this->getI18n( 'bill_table_quantity' ),
                $this->getI18n( 'bill_table_totalPrice' ) . ' ' . $this->taxes
            );
            $total = 0;
            $taxes = 0;
            $tva = array( );
            if( is_array( $command[ 'content' ][ 'cart_external' ] ) ) {
                foreach( $command[ 'content' ][ 'cart_external' ] as $id => $product ) {
                    $className = $product[ 'class' ];
                    $class = $this->linker->$className;
                    $shortId = $product[ 'id' ];
                    $price = $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_PRICE );
                    $datas[ 'elements' ][ ] = array(
                        $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_REFERENCE ),
                        $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_NAME ),
                        $this->monney_format( $price, false, false ),
                        $product[ 'qty' ],
                        $this->monney_format( $price * $product[ 'qty' ], false, false )
                    );
                    $elementsToUpdate[ $className ][ $shortId ] = $product[ 'qty' ];
                    $tax = $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_TAXRATE );
                    $thisTVA = ( $price * $product[ 'qty' ]) * $tax / 100;
                    $tva[ $tax ] += $thisTVA;
                    $taxes += $thisTVA;
                    $total += $price * $product[ 'qty' ];
                }
            }

            if( is_array( $command[ 'content' ][ 'cart' ] ) ) {
                foreach( $command[ 'content' ][ 'cart' ] as $id => $quantity ) {
                    list($id, $variant) = explode( '|', $id );
                    // We verify if we the product [still] exists
                    if( $this->productExists( $id ) ) {
                        list($product) = $this->db_execute( 'product_get', array( 'id' => $id ) );

                        // We update the product datas with the variant's if needed
                        $addToDescription = '';
                        if( $product[ 'hasVariants' ] ) {
                            list($variant) = $this->db_execute( 'product_get_variant',
                                                                array( 'product_id' => $id, 'variant_id' => $variant ) );
                            if( $product[ 'variants_change_stock' ] ) {
                                // We don't use the global stock, but the variant's one
                                $product[ 'stock' ] = $variant[ 'stock' ];
                            }
                            if( $product[ 'variants_change_price' ] ) {
                                // We don't use the global price, but the variant's one
                                $product[ 'price' ] = $variant[ 'price' ];
                            }
                            if( $product[ 'variants_change_ref' ] ) {
                                // We don't use the global ref, but the variant's one
                                $product[ 'reference' ] = $variant[ 'ref' ];
                            }
                            $cps = $this->variantCP_explode( $variant[ 'customProperties' ] );

                            foreach( $cps as $cp ) {
                                $addToDescription .= '<br />' . $cp[ 'name' ] . ' : ' . $cp[ 'value' ];
                            }
                        }

                        if( $product[ 'pack_id' ] > 0 ) {
                            // As this is a pack, we should add some descriptions
                            $taxRate = $product[ 'taxRate' ];
                            $noShortDescription = true;
                            $packElements = explode( '|', $product[ 'pack_variant' ] );
                            // in this case, $cps doesn't contain [cpId]:[cpValue] but [product_id]:[variant_id]
                            $addToDescription = '<ul>';
                            foreach( $packElements as $packElement ) {
                                list($pack_product, $pack_variant) = explode( ':', $packElement );
                                list($productName) = $this->db_execute( 'product_get_name',
                                                                        array( 'id' => $pack_product ), $qry );
                                $addToDescription .= '<li>';
                                $addToDescription .= $this->getI18n( $productName[ 'name' ] );
                                list($productsVariant) = $this->db_execute(
                                    'product_get_variant_if_any',
                                    array( 'product_id' => $pack_product, 'variant_id' => $pack_variant )
                                );

                                if( !empty( $productsVariant ) ) {
                                    $cps = $this->variantCP_explode( $productsVariant[ 'customProperties' ] );
                                    foreach( $cps as $cp ) {
                                        $addToDescription .= '<br />' . $cp[ 'name' ] . ' : ' . $cp[ 'value' ];
                                    }
                                }
                                $addToDescription .= '</li>';
                            }
                            $addToDescription .= '</ul>';
                            $isPack = true;

                            $taxRate = $product[ 'taxRate' ];
                        }

                        if( $quantity > 0 && $product[ 'stock' ] > 0 ) {
                            $taxRate = $product[ 'taxRate' ];
                            // We verify if we can sell the quantity asked
                            if( $quantity > $product[ 'stock' ] ) {
                                // The user asks for more than what we have in stock, so we
                                // can only sell all the stock
                                $quantity = $product[ 'stock' ];
                                $command[ 'content' ][ 'cart' ][ $id ] = $quantity;
                            }
                            $datas[ 'elements' ][ ] = array(
                                $product[ 'reference' ],
                                $this->getI18n( $product[ 'name' ] ) . $addToDescription,
                                $this->monney_format( $product[ 'price' ], false, false ),
                                $quantity,
                                $this->monney_format( $product[ 'price' ] * $quantity, false, false )
                            );

                            $thisTVA = ( $product[ 'price' ] * $quantity) * $taxRate / 100;
                            $tva[ $taxRate ] += $thisTVA;
                            $taxes += $thisTVA;

                            $total += $product[ 'price' ] * $quantity;
                        } elseif( $product[ 'stock' ] == 0 ) {
                            // There is no stock anymore, so we don't add it
                        } else {
                            // We wanted a quantity of 0, so we delete it
                            unset( $command[ 'content' ][ 'cart' ][ $id ] );
                        }
                    }
                }
            }

            $datas[ 'customerService' ] = explode(
                "\r\n", $this->getI18n( self::I18N_BILLCUSTOMERSERVICE )
            );
            $datas[ 'billingAddressIntro' ] = $this->getI18n( 'bill_AddressIntro' );
            $address = str_replace(
                array( "\r\n", "\r" ), "\n", $command[ 'content' ][ 'billing_address' ][ 'address' ]
            );
            $datas[ 'billingAddress' ] = array_merge(
                array( $command[ 'content' ][ 'billing_address' ][ 'name' ] ), explode( "\n", $address ),
                                                                                        array(
                $command[ 'content' ][ 'billing_address' ][ 'zip' ] . ' ' .
                $command[ 'content' ][ 'billing_address' ][ 'city' ]
                )
            );

            if( $this->getParam( 'shipping>activated', true ) ) {
                $price = $command[ 'content' ][ 'shipping' ][ 'price' ];
                $this->ship_getPrice( $total, $price, $explanation, $taxRate );
                $datas[ 'shipping_type' ] = $command[ 'content' ][ 'shipping' ][ 'type' ];
                $datas[ 'shippingAddressIntro' ] = $this->getI18n(
                    'bill_comeTakeItTextIntro'
                );

                if( $command[ 'content' ][ 'shipping' ][ 'type' ] == 'comeTakeIt' ) {
                    $datas[ 'elements' ][ ] = array(
                        $this->getI18n( 'bill_shippingTitle' ),
                        $this->getI18n( 'bill_comeTakeItText' ),
                        $this->monney_format( $price, false, false ),
                        1,
                        $this->monney_format( $price, false, false )
                    );
                    $address = preg_replace(
                        "`([\r\n]+)`", '<br />', $command[ 'content' ][ 'shipping' ][ 'address' ]
                    );
                    $address = preg_replace(
                        '`(<br */>)+`', '<br />', $address
                    );
                    $datas[ 'shippingAddress' ] = explode(
                        '<br />', $address
                    );
                } else {
                    $datas[ 'elements' ][ ] = array(
                        $this->getI18n( 'bill_shippingTitle' ),
                        $command[ 'content' ][ 'shipping' ][ 'shipper' ],
                        $this->monney_format( $price, false, false ),
                        1,
                        $this->monney_format( $price, false, false )
                    );
                    $datas[ 'shippingAddress' ] = $command[ 'content' ][ 'shipping_address' ][ 'name' ];
                    $address = str_replace(
                        array( "\r\n", "\r" ), "\n", $command[ 'content' ][ 'shipping_address' ][ 'address' ]
                    );
                    $address_more = str_replace(
                        array( "\r\n", "\r" ), "\n", $command[ 'content' ][ 'shipping_address' ][ 'more' ]
                    );
                    $datas[ 'shippingAddress' ] = array_merge(
                        array( $command[ 'content' ][ 'shipping_address' ][ 'name' ] ), explode( "\n", $address ),
                                                                                                 explode( "\n",
                                                                                                          $address_more ),
                                                                                                          array(
                        $command[ 'content' ][ 'shipping_address' ][ 'zip' ] . ' ' .
                        $command[ 'content' ][ 'shipping_address' ][ 'city' ]
                        )
                    );
                }
                $thisTVA = $price * $taxRate / 100;
                if( $thisTVA > 0 ) {
                    $tva[ $taxRate ] += $thisTVA;
                }
                $taxes += $thisTVA;
                $total += $price;
            }

            if( $this->taxes == self::TAXES_EXCLUDED ) {
                $datas[ 'totalHT_float' ] = $total;
                $datas[ 'totalHT' ] = $this->monney_format( $total, true, false );
                $datas[ 'totalTTC_float' ] = $total + $taxes;
                $datas[ 'totalTTC' ] = $this->monney_format(
                    $total + $taxes, true, false
                );
            } else {
                // We round to upper for the client not to think his TTC is too
                //big for that HT
                $datas[ 'totalHT_float' ] = $total - $taxes;
                $datas[ 'totalHT' ] = $this->monney_format(
                    $total - $taxes, true, false, self::ROUND_TO_UPPER
                );
                $datas[ 'totalTTC_float' ] = $total;
                $datas[ 'totalTTC' ] = $this->monney_format( $total, true, false );
            }
            $datas[ 'tva' ] = $tva;
        }

        // Payment modes
        $paymentModeId = $command[ 'content' ][ 'paymentMode' ];
        $bank_id = $command[ 'content' ][ 'paymentMode' ];
        ;
        $datas[ 'paymentMode' ][ 'id' ] = $bank_id;
        $datas[ 'paymentMode' ][ 'name' ] = $this->linker->payment->get( $bank_id )->bank_getName();

        if( count( $datas[ 'elements' ] ) == 0 ) {
            $this->render( 'cart_empty' );
            return true;
        }
        $user = $this->linker->user->getData();

        // The customer is not connected, se we will fill the lines using the datas he gave
        $address = $command[ 'content' ][ 'billing_address' ][ 'address' ] . "\n";
        $address .= $command[ 'content' ][ 'billing_address' ][ 'zip' ] . ' ' . $command[ 'content' ][ 'billing_address' ][ 'city' ];
        $datas[ 'client' ] = array(
            'name' => $command[ 'content' ][ 'billing_address' ][ 'name' ],
            'address' => $address,
            'mail' => $command[ 'content' ][ 'billing_mail' ]
        );
        $datas[ 'phone' ] = $command[ 'content' ][ 'billing_phone' ];
        $datas[ 'mail' ] = $command[ 'content' ][ 'billing_mail' ];
        if( $user ) {
            $datas[ 'client' ][ 'id' ] = $user[ 'id' ];
            $datas[ 'client' ][ 'address' ] .= "\n" . 'N° client : ' . $user[ 'id' ];
        }

        $datas[ 'seller' ] = array(
            'name' => $this->getParam( 'command>companyName' ),
            'address' => $this->getParam( 'command>companyAddress' ),
        );
        $datas[ 'author' ] = 'Websailors pour ' . $datas[ 'seller' ][ 'name' ];
        $datas[ 'totalHTName' ] = 'Total HT : ';
        $datas[ 'totalTVAName' ] = 'TVA : ';
        $datas[ 'totalTTCName' ] = 'Total TTC : ';

        $datas[ 'logo' ] = $this->getParam( 'command>logo' );
        $datas[ 'footer' ] = $this->getI18n( self::I18N_BILLFOOTER );
        $datas[ 'headLine' ] = $this->getI18n( self::I18N_BILLHEADLINE );

        $billColor = $this->getParam( 'billColor', 0 );
        $datas[ 'fillColor' ] = $this->getParam( 'billColors>' . $billColor );

        // Extra texts
        $classes = $this->get_shared_methods( 'billing_extra_texts' );
        foreach( $classes as $class ) {
            $temp = $this->linker->$class->billing_extra_texts();
            if( !empty( $temp ) ) {
                $datas[ 'extra_texts' ][ ] = $temp;
            }
        }
        $classes = $this->get_shared_methods( 'billing_extra_texts_mail' );
        foreach( $classes as $class ) {
            $temp = $this->linker->$class->billing_extra_texts_mail();
            if( !empty( $temp ) ) {
                $datas[ 'extra_texts_mail' ][ ] = $temp;
            }
        }
        $classes = $this->get_shared_methods( 'billing_extra_docs_mail' );
        foreach( $classes as $class ) {
            $temp = $this->linker->$class->billing_extra_docs_mail();
            if( !empty( $temp ) ) {
                $datas[ 'extra_docs_mail' ][ ] = $temp;
            }
        }
        $classes = $this->get_shared_methods( 'billing_extra_texts_mail_customer' );
        foreach( $classes as $class ) {
            $temp = $this->linker->$class->billing_extra_texts_mail_customer();
            if( !empty( $temp ) ) {
                $datas[ 'extra_texts_mail_customer' ][ ] = $temp;
            }
        }
        $classes = $this->get_shared_methods( 'billing_extra_docs_mail_customer' );
        foreach( $classes as $class ) {
            $temp = $this->linker->$class->billing_extra_docs_mail_customer();
            if( !empty( $temp ) ) {
                $datas[ 'extra_docs_mail_customer' ][ ] = $temp;
            }
        }

        $fileNames = SH_SITE_FOLDER . __CLASS__ . '/commands/';
        $fileNames .= date( 'y' ) . '/' . date( 'm' ) . '/' . date( 'd' ) . '/' . date( 'H-i-s' ) . '-';
        // Saves it to the file
        //The commands have a unic number which is incremented for each new command
        $cpt = 0;
        //We get the first id available by checking the file to create
        while( file_exists( $fileNames . $cpt . '.php' ) ) {
            $cpt++;
        }
        $fileNames .= $cpt;

        $commandListFile = SH_SITE_FOLDER . __CLASS__ . '/commands/list.php';
        include($commandListFile);

        $customBillId = $this->getCustomBillId();
        $commandList[ $customBillId ] = str_replace(
            SH_SITE_FOLDER . __CLASS__ . '/commands/', '', $fileNames
        );

        $this->helper->writeArrayInFile(
            $commandListFile, 'commandList', $commandList
        );
        $datas[ 'billId' ] = $customBillId;

        $datas[ 'title' ] = $this->getI18n( 'bill_number' ) . $customBillId;
        $datas[ 'subject' ] = $this->getI18n( 'bill_subject_beforeDate' ) . $this->linker->datePicker->dateToLocal() . $this->getI18n( 'bill_subject_middle' ) . $datas[ 'client' ][ 'name' ] . $this->getI18n( 'bill_subject_afterName' );

        $datas[ 'date' ] = date( 'Y-m-d H:i:s' );

        $datas[ 'extra_stored_datas' ] = $command[ 'content' ][ 'extra_datas' ];

        $this->linker->events->onSendCommand( $datas );

        $this->helper->createDir( dirname( $fileNames ) );
        $this->helper->writeArrayInFile(
            $fileNames . '.php', 'command', $datas, false
        );

        //Creating the content of the email
        $values[ 'mail' ][ 'date' ] = date( 'd/m/Y H:i:s' );
        $values[ 'mail' ][ 'siteName' ] = 'http://' . $this->linker->path->getDomain();
        if( is_array( $datas[ 'extra_texts_mail' ] ) ) {
            $values[ 'mail' ][ 'extra_texts_mail' ] = implode( '<br /><br />', $datas[ 'extra_texts_mail' ] );
        }
        if( is_array( $datas[ 'extra_texts_mail_customer' ] ) ) {
            $values[ 'mail' ][ 'extra_texts_mail_customer' ] = implode( '<br /><br />',
                                                                        $datas[ 'extra_texts_mail_customer' ] );
        }
        $content = $this->render( 'command_mail', $values, false, false );
        $contentSender = $this->render( 'command_mailSender', $values, false, false );

        $bills_to_create = $this->getParam( 'bills_to_create', array( ) );
        $bills_to_create[ $customBillId ][ 'datas' ] = $fileNames . '.php';
        $bills_to_create[ $customBillId ][ 'billContent' ] = $content;
        $bills_to_create[ $customBillId ][ 'billContentSender' ] = $contentSender;

        $this->setParam( 'bills_to_create', $bills_to_create );
        $this->writeParams();

        // Updates stocks
        if( is_array( $elementsToUpdate ) ) {
            foreach( $elementsToUpdate as $class => $element ) {
                if( method_exists( $class, 'shop_remove_quantity' ) ) {
                    foreach( $element as $id => $quantity ) {
                        $this->linker->$class->shop_remove_quantity( $id, $quantity, $datas );
                    }
                }
            }
        }
        if( is_array( $command[ 'content' ][ 'cart' ] ) ) {
            foreach( $command[ 'content' ][ 'cart' ] as $id => $quantity ) {
                list($id, $variant) = explode( '|', $id );
                $this->changeQuantity( $id, $variant, $quantity, '-' );
            }
        }
    }

    protected function getCustomBillId() {
        $customBillIdFormat = $this->getParam( 'bill_number_format', '[YEAR4][MONTH2][DAYOFMONTH2]-[INCREMENT3]' );

        if( preg_match( '`\[DAYOFMONTH[12]\]`', $customBillIdFormat ) ) {
            $varName = 'dayOfMonth';
            $dateFormat = 'Y-m-d';
        } else if( preg_match( '`\[DAYOFYEAR[123]\]`', $customBillIdFormat ) ) {
            $varName = 'dayOfYear';
            $dateFormat = 'Y-m-d';
        } else if( preg_match( '`\[MONTH[12]\]`', $customBillIdFormat ) ) {
            $varName = 'month';
            $dateFormat = 'Y-m';
        } else if( preg_match( '`\[YEAR[24]\]`', $customBillIdFormat ) ) {
            $varName = 'year';
            $dateFormat = 'Y';
        } else {
            $varName = 'increment';
            $dateFormat = ' ';
        }
        $last = $this->getParam( 'lastIds>' . $varName, array( ) );
        $date = date( $dateFormat );
        if( empty( $last ) || $last[ 'date' ] != $date ) {
            $increment1 = 1;
        } else {
            $increment1 = $last[ 'id' ] + 1;
        }
        $this->setParam( 'lastIds>' . $varName, array( 'date' => $date, 'id' => $increment1 ) );
        $this->writeParams();

        $dOY1 = date( 'z' );

        $replacements = array(
            '[YEAR4]' => date( 'Y' ),
            '[YEAR2]' => date( 'y' ),
            '[MONTH2]' => date( 'm' ),
            '[MONTH1]' => date( 'n' ),
            '[DAYOFMONTH2]' => date( 'd' ),
            '[DAYOFMONTH1]' => date( 'j' ),
            '[DAYOFYEAR3]' => str_pad( $dOY1, 3, '0', STR_PAD_LEFT ),
            '[DAYOFYEAR2]' => str_pad( $dOY1, 2, '0', STR_PAD_LEFT ),
            '[DAYOFYEAR1]' => $dOY1,
            '[INCREMENT8]' => str_pad( $increment1, 8, '0', STR_PAD_LEFT ),
            '[INCREMENT7]' => str_pad( $increment1, 7, '0', STR_PAD_LEFT ),
            '[INCREMENT6]' => str_pad( $increment1, 6, '0', STR_PAD_LEFT ),
            '[INCREMENT5]' => str_pad( $increment1, 5, '0', STR_PAD_LEFT ),
            '[INCREMENT4]' => str_pad( $increment1, 4, '0', STR_PAD_LEFT ),
            '[INCREMENT3]' => str_pad( $increment1, 3, '0', STR_PAD_LEFT ),
            '[INCREMENT2]' => str_pad( $increment1, 2, '0', STR_PAD_LEFT ),
            '[INCREMENT1]' => $increment1,
        );
        $billId = str_replace( array_keys( $replacements ), array_values( $replacements ), $customBillIdFormat );

        return $billId;
    }

    public function user_getAccountTabs() {
        if( !$this->activateShop ) {
            return array( );
        }


        $ret[ 'commandes' ] = array(
            'title' => $this->getI18n( 'billings_tab_title' ),
            'content' => $this->showMyBills()
        );
        $carts = $this->db_execute( 'carts_list', array( 'user' => $this->linker->user->get( 'id' ) ) );
        if( !empty( $carts ) ) {
            $ret[ 'saved_carts' ] = array(
                'title' => $this->getI18n( 'saved_carts_tab' ),
                'content' => $this->showMyCarts( $carts )
            );
        }
        return $ret;
    }

    public function savedCart_delete() {
        // We remove the selected cart and go back to the same page
        $cart_id = $_GET[ 'id' ];
        $user = $this->linker->user->get( 'id' );
        $this->db_execute( 'carts_delete', array( 'id' => $cart_id, 'user' => $user ) );
        $count = $this->db_getRowCount();
        if( $count > 0 ) {
            // We don't delete any entries if no cart was deleted (as there is no owner in this part)
            $this->db_execute( 'cart_delete_content', array( 'cart_id' => $cart_id ) );
            $this->linker->html->addMessage( $this->getI18n( 'saved_cart_removed' ), false );
        }

        $this->linker->path->redirect( $this->linker->path->getHistory( 1 ) );
    }

    public function savedCart_load() {
        $cart_id = $_GET[ 'id' ];
        $user = $this->linker->user->get( 'id' );
        $action = $_GET[ 'action' ];

        list($cart) = $this->db_execute( 'carts_getOne', array( 'id' => $cart_id, 'user' => $user ) );

        if( !empty( $cart ) ) {
            if( $action == 'replace' ) {
                // In this case, we empty the active cart
                unset( $_SESSION[ __CLASS__ ][ 'cart_external' ] );
                unset( $_SESSION[ __CLASS__ ][ 'cart' ] );
            }
            // We add the products to the active cart
            $contents = $this->db_execute( 'cart_get_contents', array( 'cart_id' => $cart_id ) );
            foreach( $contents as $content ) {
                if( !empty( $content[ 'class' ] ) ) {
                    // this is an external product
                    $className = $content[ 'class' ];
                    $shortId = $content[ 'product_id' ];
                    $externallyAdded[ $className ][ $shortId ] = $content[ 'quantity' ];
                } else {
                    $productId = $content[ 'product_id' ];
                    if( !empty( $content[ 'variant_id' ] ) ) {
                        $variantId = $content[ 'variant_id' ];
                    } else {
                        $variantId = null;
                    }
                    $this->addToCart( $productId, $variantId, $content[ 'quantity' ], false );
                }
            }
            foreach( $externallyAdded as $class => $products ) {
                if( $this->linker->method_exists( $class, 'shop_add_products_to_cart' ) ) {
                    $this->linker->$class->shop_add_products_to_cart( $products );
                } else {
                    foreach( $products as $product => $quantity ) {
                        $this->addToCart_external( $class, $product, $quantity );
                    }
                }
            }
            $this->linker->path->redirect( __CLASS__, 'cart_show' );
        } else {
            // The cart was not found, so we do nothing but setting a message
            $this->linker->html->addMessage( $this->getI18n( 'savedCart_load_notFound' ) );
        }
    }

    protected function showMyCarts() {
        if( !$this->activateShop ) {
            $this->linker->path->error( 404 );
        }
        $carts = $this->db_execute( 'carts_list', array( 'user' => $this->linker->user->get( 'id' ) ) );
        $separator = false;
        $loaderLink = $this->linker->path->getLink( __CLASS__ . '/savedCart_load/' );
        $deleterLink = $this->linker->path->getLink( __CLASS__ . '/savedCart_delete/' );
        foreach( $carts as $oneCart ) {
            $id = $oneCart[ 'id' ];
            $values[ 'carts' ][ $id ] = $oneCart;
            $values[ 'carts' ][ $id ][ 'loadLink' ] = $loaderLink . '?id=' . $id . '&action=replace';
            $values[ 'carts' ][ $id ][ 'addLink' ] = $loaderLink . '?id=' . $id . '&action=add';
            $values[ 'carts' ][ $id ][ 'deleteLink' ] = $deleterLink . '?id=' . $id;
            $values[ 'carts' ][ $id ][ 'separator' ] = $separator;
            $separator = true;

            $contents = $this->db_execute( 'cart_get_contents', array( 'cart_id' => $id ) );
            foreach( $contents as $content ) {
                if( !empty( $content[ 'class' ] ) ) {
                    // this is an external product
                    $className = $content[ 'class' ];
                    $shortId = $content[ 'product_id' ];
                    $class = $this->linker->$className;
                    $values[ 'carts' ][ $id ][ 'contents' ][ ] = array(
                        'image' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_IMAGE ),
                        'name' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_NAME ),
                        'reference' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_REFERENCE ),
                        'shortDescription' => $class->cart_getProductData( $shortId,
                                                                           self::EXTERNAL_PRODUCT_SHORTDESCRIPTION ),
                        'quantity' => $content[ 'quantity' ],
                        'id' => $id,
                        'link' => $class->cart_getProductData( $shortId, self::EXTERNAL_PRODUCT_LINK ),
                    );
                } else {
                    // This product is managed using this class
                    $productId = $content[ 'product_id' ];
                    $variantId = $content[ 'variant_id' ];
                    if( $this->productExists( $productId ) ) {
                        list($product) = $this->db_execute( 'product_get', array( 'id' => $productId ) );
                        $addToDescription = '';

                        // We update the product datas with the variant's if needed
                        if( $product[ 'hasVariants' ] ) {
                            list($variant) = $this->db_execute( 'product_get_variant',
                                                                array( 'product_id' => $productId, 'variant_id' => $variantId ) );

                            if( $product[ 'variants_change_ref' ] ) {
                                // We don't use the global ref, but the variant's one
                                $product[ 'reference' ] = $variant[ 'ref' ];
                            }
                            $cps = $this->variantCP_explode( $variant[ 'customProperties' ] );

                            foreach( $cps as $cp ) {
                                $addToDescription .= '<br />' . $cp[ 'name' ] . ' : ' . $cp[ 'value' ];
                            }
                        }

                        $values[ 'carts' ][ $id ][ 'contents' ][ ] = array(
                            'image' => $product[ 'image' ],
                            'name' => $this->getI18n( $product[ 'name' ] ),
                            'reference' => $product[ 'reference' ],
                            'shortDescription' => $this->getI18n(
                                $product[ 'shortDescription' ]
                            ) . $addToDescription,
                            'quantity' => $content[ 'quantity' ],
                            'id' => $productId,
                            'link' => $this->linker->path->getLink(
                                'shop/showProduct/' . $productId
                            )
                        );
                    }
                }
            }
        }

        return $this->render( 'saved_carts', $values, false, false );
    }

    public function showMyBills() {
        if( !$this->activateShop ) {
            $this->linker->path->error( 404 );
        }
        $commandsFolder = SH_SITE_FOLDER . __CLASS__ . '/commands/';
        if( file_exists( $commandsFolder . 'list.php' ) ) {
            include($commandsFolder . 'list.php');
            $commandList = array_reverse( $commandList, true );
            $userId = $this->linker->user->getUserId();
            foreach( $commandList as $id => $commandFile ) {
                if( file_exists( $commandsFolder . $commandFile . '.php' ) ) {
                    list($y) = explode( '/', $commandFile );
                    include($commandsFolder . $commandFile . '.php');
                    if( $command[ 'client' ][ 'id' ] == $userId ) {
                        $values[ 'years' ][ $y ][ 'name' ] = '20' . $y;
                        $values[ 'years' ][ $y ][ 'commands' ][ $id ][ 'totalHT' ] = $command[ 'totalHT' ];
                        $values[ 'years' ][ $y ][ 'commands' ][ $id ][ 'totalTTC' ] = $command[ 'totalTTC' ];
                        $values[ 'years' ][ $y ][ 'commands' ][ $id ][ 'customerName' ] = $command[ 'client' ][ 'name' ];
                        $values[ 'years' ][ $y ][ 'commands' ][ $id ][ 'id' ] = $id;
                        $values[ 'years' ][ $y ][ 'commands' ][ $id ][ 'date' ] = $this->linker->datePicker->dateToLocal(
                            substr( $commandFile, 0, 10 )
                        );
                        $values[ 'years' ][ $y ][ 'commands' ][ $id ][ 'link' ] = $this->linker->path->getLink(
                                $this->shortClassName . '/showBill/'
                            ) . '?bill_id=' . $id;
                    }
                }
            }
        }
        if( is_array( $values[ 'years' ] ) ) {
            return $this->render( 'accountTabs_commandes', $values, false, false );
        }
        return $this->render( 'billing_empty', $values, false, false );
    }

    public function showCommands() {
        $this->onlyAdmin( true );
        $commandsFolder = SH_SITE_FOLDER . __CLASS__ . '/commands/';
        if( file_exists( $commandsFolder . 'list.php' ) ) {
            include($commandsFolder . 'list.php');
            if( !isset( $_POST[ 'from' ] ) ) {
                $_POST[ 'from' ] = date( 'Y' ) . '-01-01';
                $_POST[ 'to' ] = date( 'Y-m-d' );
            }
            $commandList = array_reverse( $commandList, true );
            $dp = $this->linker->datePicker;
            $export = 'Commandes du ' . $dp->dateToLocal( $_POST[ 'from' ] ) . ' au ' . $dp->dateToLocal( $_POST[ 'to' ] ) . "\n\n";
            $export .= 'Facture;date;Client;Total HT;Total TTC' . "\n";
            $values[ 'tva' ] = array( );
            $values[ 'total' ][ 'ttc' ] = 0;
            $values[ 'total' ][ 'ht' ] = 0;
            $values[ 'total' ][ 'tva' ] = 0;
            $products = array( );
            $commandsCount = 0;
            foreach( $commandList as $id => $commandFile ) {
                if( file_exists( $commandsFolder . $commandFile . '.php' ) ) {
                    list($y) = explode( '/', $commandFile );
                    include($commandsFolder . $commandFile . '.php');
                    $date = substr( $command[ 'date' ], 0, 10 );
                    if( str_replace( '-', '', $date ) < str_replace( '-', '', $_POST[ 'from' ] ) ) {
                        continue;
                    }
                    if( str_replace( '-', '', $date ) > str_replace( '-', '', $_POST[ 'to' ] ) ) {
                        continue;
                    }
                    $commandsCount++;
                    foreach( $command[ 'elements' ] as $element ) {
                        $name = empty( $element[ 0 ] ) ? $element[ 1 ] : $element[ 0 ] . ' - ' . $element[ 1 ];
                        $products[ $name ] += $element[ 3 ];
                    }
                    $values[ 'total' ][ 'ttc' ] += $command[ 'totalTTC_float' ];
                    $values[ 'total' ][ 'ht' ] += $command[ 'totalHT_float' ];
                    $values[ 'total' ][ 'tva' ] += ($command[ 'totalTTC_float' ] - $command[ 'totalHT_float' ]);
                    if( is_array( $command[ 'tva' ] ) ) {
                        foreach( $command[ 'tva' ] as $tvaRate => $tvaAmount ) {
                            if( $tvaAmount > 0 ) {
                                $values[ 'tva' ][ $tvaRate ][ 'rate' ] = $tvaRate;
                                $values[ 'tva' ][ $tvaRate ][ 'amount' ] += $tvaAmount;
                            }
                        }
                    }
                    $values[ 'years' ][ $y ][ 'name' ] = '20' . $y;
                    $values[ 'years' ][ $y ][ 'commands' ][ $id ][ 'totalHT' ] = $command[ 'totalHT' ];
                    $values[ 'years' ][ $y ][ 'commands' ][ $id ][ 'totalHT_float' ] = $command[ 'totalHT_float' ];
                    $values[ 'years' ][ $y ][ 'commands' ][ $id ][ 'totalTTC' ] = $command[ 'totalTTC' ];
                    $values[ 'years' ][ $y ][ 'commands' ][ $id ][ 'totalTTC_float' ] = $command[ 'totalTTC_float' ];
                    $values[ 'years' ][ $y ][ 'commands' ][ $id ][ 'customerName' ] = $command[ 'client' ][ 'name' ];
                    $values[ 'years' ][ $y ][ 'commands' ][ $id ][ 'id' ] = $id;
                    $values[ 'years' ][ $y ][ 'commands' ][ $id ][ 'date' ] = $this->linker->datePicker->dateToLocal(
                        substr( $commandFile, 0, 10 )
                    );
                    $values[ 'years' ][ $y ][ 'commands' ][ $id ][ 'link' ] = $this->linker->path->getLink(
                            $this->shortClassName . '/showBill/'
                        ) . '?bill_id=' . $id;
                    $export .= $id . ';';
                    $export .= $values[ 'years' ][ $y ][ 'commands' ][ $id ][ 'date' ] . ';';
                    $export .= $values[ 'years' ][ $y ][ 'commands' ][ $id ][ 'customerName' ] . ';';
                    $export .= $values[ 'years' ][ $y ][ 'commands' ][ $id ][ 'totalHT_float' ] . ';';
                    $export .= $values[ 'years' ][ $y ][ 'commands' ][ $id ][ 'totalTTC_float' ];
                    $export .= "\n";
                }
            }

            $export .= "\n";
            if( is_array( $values[ 'tva' ] ) ) {
                foreach( $values[ 'tva' ] as $tva ) {
                    $export .= ';;;TVA à ' . $tva[ 'rate' ] . ';' . $tva[ 'amount' ] . ';' . "\n";
                }
            }
            $export .= ';;;Total TVA;' . $values[ 'total' ][ 'tva' ] . "\n";
            $export .= ';;;Total HT;' . $values[ 'total' ][ 'ht' ] . "\n";
            $export .= ';;;Total TTC;' . $values[ 'total' ][ 'ttc' ] . "\n\n";

            $export .= 'Nombre total de commandes;' . $commandsCount . "\n";

            $export .= "\n\n\nQuantités vendues : \n";
            foreach( $products as $productName => $productQuantity ) {
                $export .= '"' . strip_tags( str_replace( array( '<li>', '<ul>', '</ul>', '<br />' ), "\n", $productName ) ) . '";' . $productQuantity . "\n";
            }

            $values[ 'filter' ][ 'from' ] = $_POST[ 'from' ];
            $values[ 'filter' ][ 'to' ] = $_POST[ 'to' ];
            if( isset( $_POST[ 'export' ] ) ) {
                header( "Pragma: public" );
                header( "Content-Type: text/csv" );
                header( 'Content-disposition: attachment;filename=export.csv' );
                echo $export;
                exit;
            } else {
                $this->render( 'billing_list', $values );
            }
        } else {
            $this->render( 'billing_empty', $values );
        }
        return true;
    }

    /* FACEBOOK */

    public function facebook_getModules() {
        // There are 2 modules, 1 for the categories, and 1 for the products
        return array(
            'shop_categories' => $this->getI18n( 'facebook_categories' ),
            'shop_products' => $this->getI18n( 'facebook_products' )
        );
    }

}