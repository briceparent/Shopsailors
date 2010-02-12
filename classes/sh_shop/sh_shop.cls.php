<?php
/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')) {header('location: directCallForbidden.php');}

/**
 * Class that manages the shop, categories, products, cart, and commands
 */
class sh_shop extends sh_core {
    protected $minimal = array(
        'switchProductState' => true,
        'getCustomPropertyValues'=>true
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

    static $default_logo = '';
    protected $listType = null;
    protected $shopFolder = '';
    protected $productsFolder = '';
    protected $categoriesFolder = '';
    protected $cartsFolder = '';

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

    const ARG_PRODUCTLISTTYPE = 'product_list_type';

    const ROUND_TO_LOWER = 1;
    const ROUND_TO_NEARER = 2;
    const ROUND_TO_UPPER = 3;

    protected $templateIsCustomized = false;

    //////////////////////////////////////////////////////////////////////////////////////
    //                                  GENERAL PART                                    //
    //////////////////////////////////////////////////////////////////////////////////////
    /**
     * public function construct
     * Initiates the object
     */
    public function construct() {
        define('SH_SHOPIMAGES_PATH',SH_IMAGES_PATH.'shop/');
        define('SH_SHOPIMAGES_FOLDER',SH_IMAGES_FOLDER.'shop/');

        if(is_dir($this->links->site->templateFolder.$this->shortClassName)){
            $this->links->html->addCSS(__CLASS__.'.css',__CLASS__);
            $this->templateIsCustomized = true;
        }else{
            $this->links->html->addCSS(
                SH_TEMPLATE_FOLDER.'global/'.__CLASS__.'.css',
                __CLASS__
            );
        }

        self::$default_logo = '/images/shared/default/shop_default_logo_bill.png';
        $this->shopFolder = SH_SITE_FOLDER.__CLASS__.'/';
        $this->productsFolder = $this->shopFolder.'products/';
        $this->categoriesFolder = $this->shopFolder.'categories/';
        $this->cartsFolder = $this->shopFolder.'carts/';

        $monneyFormat = $this->getParam('monney_format');
        $monneyKey = 'monney_formats_listing>'.$monneyFormat.'>';
        $this->decimals = $this->getParam($monneyKey.'decimals',2);
        $this->decSeparator = $this->getParam($monneyKey.'decSeparator',',');
        $this->thousSeparator = $this->getParam($monneyKey.'thousSeparator',' ');

        $this->currency = $this->getParam('currency','Euro');
        $this->currencyBefore = $this->getParam(
            'currencies>'.$this->currency.'>before',''
        );
        $this->currencyAfter = $this->getParam(
            'currencies>'.$this->currency.'>after','€'
        );

        $this->taxes = $this->getParam('taxes');
        $this->taxRate = $this->getParam('taxRate');
        $this->showTaxSymbol = $this->getParam('showTaxSymbol');

        // Defining the constants
        $this->constants = array(
            'currency'=>$this->currency,
            'currencyBefore'=>$this->currencyBefore,
            'currencyAfter'=>$this->currencyAfter,
            'taxes'=>$this->taxes,
            'taxRate'=>$this->taxRate,
            'I18N_NAME'=>self::I18N_NAME,
            'I18N_REFERENCE'=>self::I18N_REFERENCE,
            'I18N_DESCRIPTION'=>self::I18N_DESCRIPTION,
            'I18N_SHORTDESCRIPTION'=>self::I18N_SHORTDESCRIPTION,
            'I18N_STOCK'=>self::I18N_STOCK,
            'I18N_IMAGE'=>self::I18N_IMAGE,
            'I18N_PRICE'=>self::I18N_PRICE,
            'I18N_BILLHEADLINE'=>self::I18N_BILLHEADLINE,
            'I18N_BILLFOOTER'=>self::I18N_BILLFOOTER,
            'I18N_BILLCUSTOMERSERVICE'=>self::I18N_BILLCUSTOMERSERVICE
        );
        if($this->showTaxSymbol) {
            $this->constants['showTaxSymbol'] = true;
        }
        if($this->getParam('showQuantity',true)) {
            $this->constants['showQuantity'] = true;
        }
        if($this->getParam('shipping>activated',true)){
            $this->constants['shipping_activated'] = true;
        }
        $this->renderer_addConstants($this->constants);

        // We check and build the images folders structure, if necessary
        if(!is_dir(SH_IMAGES_FOLDER.'shop/')){
            $this->links->browser->createFolder(
                SH_IMAGES_FOLDER.'shop/',
                sh_browser::READ
            );
        }
        if(!is_dir(SH_IMAGES_FOLDER.'shop/products/')){
            $this->links->browser->createFolder(
                SH_IMAGES_FOLDER.'shop/products/',
                sh_browser::ALL
            );
            $this->links->browser->addDimension(
                SH_IMAGES_FOLDER.'shop/products/',250,250
            );
        }
        if(!is_dir(SH_IMAGES_FOLDER.'shop/categories/')){
            $this->links->browser->createFolder(
                SH_IMAGES_FOLDER.'shop/categories/',
                sh_browser::ALL
            );
            $this->links->browser->addDimension(
                SH_IMAGES_FOLDER.'shop/categories/',250,250
            );
        }
        if(!is_dir(SH_IMAGES_FOLDER.'shop/high_quality/')){
            $this->links->browser->createFolder(
                SH_IMAGES_FOLDER.'shop/high_quality/',
                sh_browser::ALL
            );
            $this->links->browser->addDimension(
                SH_IMAGES_FOLDER.'shop/high_quality/',1000,1000
            );
        }

        return true;
    }

    /**
     * This method helps other classes to know whether the shop is enabled or not.
     * @return bool <b>true</b> if the shop is enabled, <b>false</b> if not.
     */
    public function isActivated() {
        return $this->getParam('activateShop',true);
    }

    /**
     * Gets the rendered notActive page
     */
    public function notActive() {
        $this->render('notActive', array());
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
        $this->links->html->setTitle($this->getI18n('error_product_not_found_title'));
        $this->render('product_not_found');
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
    public function searcher_showResults($method,$elements) {
        $this->debug(__FUNCTION__.'('.$method.','.print_r($elements,true).');', 2, __LINE__);

        if($method == 'showProduct') {
            foreach($elements as $element) {
                if(file_exists($this->productsFolder.$element.'/product.php')) {
                    include($this->productsFolder.$element.'/product.php');
                    if($product['stock'] > 0 || !$hideNullQuantityProducts) {
                        $values['category_elements'][$cpt]['name'] =
                            $this->getI18n($product['name']);
                        $values['category_elements'][$cpt]['image'] = $product['image'];
                        $values['category_elements'][$cpt]['shortDescription'] =
                            $this->getI18n($product['shortDescription']);
                        $values['category_elements'][$cpt]['description'] =
                            $this->getI18n($product['description']);
                        $link = $this->links->path->getLink('shop/showProduct/'.$element);
                        $values['category_elements'][$cpt]['link'] = $link;
                        $values['category_elements'][$cpt]['reference'] = $product['reference'];
                        $values['category_elements'][$cpt]['price'] = $this->monney_format($product['price']);
                        if($product['stock'] > 0){
                            $values['category_elements'][$cpt]['stock'] = $product['stock'];
                            $addToCartLink = $this->links->path->getLink('shop/addToCart/');
                            $addToCartLink .= '?product='.$element;
                            $values['category_elements'][$cpt]['picto_addToCart_link'] = $addToCartLink;
                        }else{
                            $values['category_elements'][$cpt]['stock'] = $this->getI18n('product_nomorestock');
                        }
                        $values['category_elements'][$cpt]['picto_show_link'] = $link;
                        $cpt++;
                    }
                }
            }
            return array(
                'name' =>  $this->getI18n('search_productsTitle'),
                'content' => $this->render(
                    'searcher_showProductResults',
                    $values,
                    false,
                    false
                )
            );
        }elseif($method == 'showCategory'){
            foreach($elements as $element){
                if(file_exists($this->categoriesFolder.$element.'/category.php')){
                    include($this->categoriesFolder.$element.'/category.php');
                    $values['category_elements'][$cpt]['name'] =
                        $this->getI18n($category['name']);
                    $values['category_elements'][$cpt]['image'] = $category['image'];
                    $values['category_elements'][$cpt]['shortDescription'] =
                        $this->getI18n($category['shortDescription']);
                    $values['category_elements'][$cpt]['description'] =
                        $this->getI18n($category['description']);
                    $link = $this->links->path->getLink('shop/showCategory/'.$element);
                    $values['category_elements'][$cpt]['link'] = $link;
                    $values['category_elements'][$cpt]['picto_show_link'] = $link;
                    $cpt++;
                }
            }
            return array(
                'name' => $this->getI18n('search_categoriesTitle'),
                'content' => $this->render(
                    'searcher_showCategoryResults',
                    $values,
                    false,
                    false
                )
            );
        }
        return false;

    }

    /**
     * Gets the list of the contents types that the searcher should search in.
     * @return array An array containing the list of search types.
     */
    public function searcher_getScope(){
        return array(
            'scope' => 'shop',
            'name' => $this->getI18n('search_shopTitle')
        );
    }

    /**
     * protected function increment_counter
     *
     */
    protected function increment_counter($file){
        $counter = file_get_contents($file);
        $counter += 1;
        $this->links->helper->writeInFile($file,$counter);
        return $counter;
    }

    /**
     * public function sitemap_renew
     *
     */
    public function sitemap_renew(){
        if($this->getParam('activateShop',true)){
            // Cart
            if($this->getParam('activateCart')){
                $this->addToSitemap('shop/cart_show/', 0.2);
            }

            // Categories
            $categories = scandir($this->categoriesFolder);
            foreach($categories as $categoryId){
                if(file_exists($this->categoriesFolder.$categoryId.'/category.php')){
                    include($this->categoriesFolder.$categoryId.'/category.php');
                    $this->addToSitemap('shop/showCategory/'.$categoryId, 0.8);
                }
            }

            // Products
            $products = scandir($this->productsFolder);
            foreach($products as $productId){
                if(file_exists($this->productsFolder.$productId.'/product.php')){
                    include($this->productsFolder.$productId.'/product.php');
                    if(!isset($product['active']) || $product['active'] == true){
                        $this->addToSitemap('shop/showProduct/'.$productId, 0.6);
                    }
                }
            }

            if($this->getParam('shipping>showExpeditionInLegacy')){
                $this->addToSitemap($this->shortClassName.'/showShipModes/',0.2);
            }
            if($this->getParam('payment>showPaymentInLegacy')){
                $this->addToSitemap($this->shortClassName.'/showPaymentModes/',0.2);
            }
        }
        return true;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        list($class,$method,$id) = explode('/',$page);
        $short = '/'.$this->shortClassName.'/';
        if($method == 'editParams'){
            return $short.'manage.php';
        }
        if($method == 'notActive'){
            return $short.'not_active.php';
        }
        if($method == 'showProduct'){
            $name = urlencode(trim($this->getProductName($id)));
            if($name != ''){
                return $short.'product/'.$id.'-'.$name.'.php';
            }else{
                return $short.'product/'.$id.'.php';
            }
        }
        if($method == 'showCategory'){
            $name = urlencode(trim($this->getCategoryName($id)));
            if($name != ''){
                return $short.'category/'.$id.'-'.$name.'.php';
            }else{
                return $short.'product/'.$id.'.php';
            }
        }
        if($method == 'cart_show'){
            return $short.'cart.php';
        }
        if($method == 'showAllProducts'){
            return $short.'products/all.php';
        }
        if($method == 'editProduct'){
            return $short.'editProduct/'.$id.'.php';
        }
        if($method == 'editCategory'){
            return $short.'editCategory/'.$id.'.php';
        }
        if($method == 'editCustomProperty'){
            return $short.'editCustomProperty/'.$id.'.php';
        }
        if($method == 'deleteCustomProperty'){
            return $short.'deleteCustomProperty/'.$id.'.php';
        }
        if($method == 'switchProductState'){
            return $short.'switchProductState.php';
        }
        if($method == 'addToCart'){
            return $short.'add_to_cart.php';
        }
        if($method == 'cart_removeProduct'){
            return $short.'cart/cart/remove_product.php';
        }
        if($method == 'cart_doAction'){
            return $short.'cart_action.php';
        }
        if($method == 'editPaymentModes'){
            return $short.'editPaymentModes.php';
        }
        if($method == 'editShipModes'){
            return $short.'editShipModes.php';
        }
        if($method == 'showShipModes'){
            return $short.'showShipModes.php';
        }
        if($method == 'showPaymentModes'){
            return $short.'showPaymentModes.php';
        }
        if($method == 'showCommands'){
            return $short.'showCommands.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        $short = $this->shortClassName.'/';
        if($uri == '/'.$short.'manage.php'){
            return $short.'editParams/';
        }
        $len = strlen('/'.$short);

        if(substr($uri,0,$len) == '/'.$short && !$this->getParam('activateShop',true)){
            return $short.'notActive/';
        }
        // These next elements also use an id
        if(preg_match('`/'.$short.'([^/]+)/([0-9]+)(-[^/]*)?\.php`',$uri,$matches)){
            if($matches[1] == 'product'){
                return $short.'showProduct/'.$matches[2];
            }
            if($matches[1] == 'category'){
                return $short.'showCategory/'.$matches[2];
            }
            if($matches[1] == 'editProduct'){
                return $short.'editProduct/'.$matches[2];
            }
            if($matches[1] == 'editCategory'){
                return $short.'editCategory/'.$matches[2];
            }
            if($matches[1] == 'editCustomProperty'){
                return $short.'editCustomProperty/'.$matches[2];
            }
            if($matches[1] == 'deleteCustomProperty'){
                return $short.'deleteCustomProperty/'.$matches[2];
            }
        }
        // These one have no id
        if($uri == '/'.$short.'cart.php'){
            return $short.'cart_show/';
        }
        if($uri == '/'.$short.'add_to_cart.php'){
            return $short.'addToCart/';
        }
        if($uri == '/'.$short.'products/all.php'){
            return $short.'showAllProducts/';
        }
        if($uri == '/'.$short.'switchProductState.php'){
            return $short.'switchProductState/';
        }
        if($uri == '/'.$short.'cart/cart/remove_product.php'){
            return $short.'cart_removeProduct/';
        }
        if($uri == '/'.$short.'cart_action.php'){
            return $short.'cart_doAction/';
        }
        if($uri == '/'.$short.'editPaymentModes.php'){
            return $short.'editPaymentModes/';
        }
        if($uri == '/'.$short.'editShipModes.php'){
            return $short.'editShipModes/';
        }
        if($uri == '/'.$short.'showShipModes.php'){
            return $short.'showShipModes/';
        }
        if($uri == '/'.$short.'showPaymentModes.php'){
            return $short.'showPaymentModes/';
        }
        if($uri == '/'.$short.'showCommands.php'){
            return $short.'showCommands/';
        }
        return false;
    }

    /**
     * public function getPageName
     *
     */
    public function getPageName($action, $id = null){
        if($action == 'showProduct'){
            if($this->productExists($id)){
                return $this->getI18n('chooseProduct').$this->getProductName($id);
            }
        }
        if($action == 'showCategory'){
            if($this->productExists($id)){
                return $this->getI18n('chooseCategory').$this->getCategoryName($id);
            }
        }
        if($action == 'cart_show'){
            return $this->getI18n('pageName_cart_show');
        }
        if($action == 'showPaymentModes'){
            return $this->getI18n('pageName_showPaymentModes');
        }
        if($action == 'showShipModes'){
            return $this->getI18n('pageName_showShipModes');
        }
        return $this->__toString().'->'.$action.'->'.$id;
    }

    /**
     * public function getProductName
     *
     */
    public function getProductName($id){
        if($this->productExists($id)){
            include($this->productsFolder.$id.'/product.php');
            return $this->getI18n($product['name']);
        }
        return false;
    }

    /**
     * public function getCategoryName
     *
     */
    public function getCategoryName($id){
        if($this->productExists($id)){
            include($this->categoriesFolder.$id.'/category.php');
            return $this->getI18n($category['name']);
        }
        return false;
    }

    public function getLegacyEntry($element){
        $activated = $this->getParam('activateShop');
        if($element == 'shipping'){
            if(
                $activated &&
                $this->getParam('shipping>activated') &&
                $this->getParam('shipping>showExpeditionInLegacy',true)
            ){
                return array(
                    'link'=>$this->translatePageToUri(
                        $this->shortClassName.'/showShipModes/'
                    ),
                    'textBefore'=>'',
                    'text'=>'Modes de livraison',
                    'textAfter'=>''
                );
            }
        }elseif($element == 'payment'){
            if(
                $activated &&
                $this->getParam('payment>showPaymentInLegacy',true)
            ){
                return array(
                    'link'=>$this->translatePageToUri(
                        $this->shortClassName.'/showPaymentModes/'
                    ),
                    'textBefore'=>'',
                    'text'=>'Modes de paiement',
                    'textAfter'=>''
                );
            }
        }
        return false;
    }

    protected function discounts_formatForRendering(){
        $discounts['rulePrice0'] = $this->getParam(
            'shipping>discounts>rulePrice0',''
        );
        $discounts['rulediscount0'] = $this->getParam(
            'shipping>discounts>rulediscount0',''
        );
        $discounts['rulePrice1'] = $this->getParam(
            'shipping>discounts>rulePrice1',''
        );
        $discounts['rulediscount1'] = $this->getParam(
            'shipping>discounts>rulediscount1',''
        );
        $discounts['rulePrice2'] = $this->getParam(
            'shipping>discounts>rulePrice2',''
        );
        $discounts['rulediscount2'] = $this->getParam(
            'shipping>discounts>rulediscount2',''
        );
        $discountsCount = 0;
        for($a=0;$a<3;$a++){
            $price = trim($discounts['rulePrice'.$a]);
            $discount = trim($discounts['rulediscount'.$a]);
            if($price > 0){
                $values['display']['discounts'] = true;
                if(strpos($discount,'%') === false){
                    $discount = $this->monney_format($discount);
                }
                $values['discount'.$a] = array(
                    'price'=>$this->monney_format($price),
                    'discount'=>$discount,
                    'activated'=>true
                );
                $discountsCount++;
            }
            if($discountsCount > 1){
                $values['display']['moreThan1discount'] = true;
            }
        }
        return $values;
    }

    public function showPaymentModes(){
        $paymentModes = $this->getParam('payment>supplyers');
        $this->links->html->setTitle($this->getI18n('paymentModes_title'));

        $cpt = 0;
        if(is_array($paymentModes)){
            foreach($paymentModes as $paymentMode){
                if(isset($paymentMode['activated'])){
                    $values['paymentModes'][] = $paymentMode;
                    $cpt++;
                }
            }
        }
        if($cpt > 1){
            $values['payment']['moreThanOneMode'] = true;
        }

        $this->render('payment_showPaymentModes', $values);
        return true;
    }

    public function showShipModes(){
        $values = $this->shippers_formatForRendering();
        $discounts = $this->discounts_formatForRendering();
        if(!is_array($discounts)){
            $discounts = array();
        }
        $values = array_merge($values,$discounts);
        $this->render('ship_showShipModes', $values);
        return true;
    }

    public function editShipModes(){
        $this->onlyAdmin();
        if($this->formSubmitted('shopShipModesEditor')){
            // State of the module
            if(isset($_POST['addShipMode'])){
                $_POST['supplyers'][] = array();
            }
            if(isset($_POST['removeShipMode'])){
                $key = array_shift(array_keys($_POST['removeShipMode']));
                unset($_POST['supplyers'][$key]);
            }

            $this->setParam('shipping>activated',isset($_POST['activateShipping']));
            $this->setParam(
                'shipping>showExpeditionInLegacy',
                isset($_POST['showExpeditionInLegacy'])
            );
            if(isset($_POST['showExpeditionInLegacy'])){
                $this->addToSitemap($this->shortClassName.'/showShipModes/',0.2);
            }else{
                $this->removeFromSitemap($this->shortClassName.'/showShipModes/');
            }
            $this->setParam('shipping>supplyers',$_POST['supplyers']);
            if(is_array($_POST['supplyers'])){
                foreach($_POST['supplyers'] as $id=>$supplyer){
                    //We change the decimal separator to a dot if it was a coma
                    $this->setParam(
                        'shipping>supplyers>'.$id.'>price',
                        str_replace(',','.',$supplyer['price'])
                    );
            }
            }
            $this->setParam(
                'shipping>discounts>rulePrice0',
                str_replace(',','.',$_POST['shipRulePrice0'])
            );
            $this->setParam(
                'shipping>discounts>rulediscount0',
                str_replace(',','.',$_POST['shipRulediscount0'])
            );
            $this->setParam(
                'shipping>discounts>rulePrice1',
                str_replace(',','.',$_POST['shipRulePrice1'])
            );
            $this->setParam(
                'shipping>discounts>rulediscount1',
                str_replace(',','.',$_POST['shipRulediscount1'])
            );
            $this->setParam(
                'shipping>discounts>rulePrice2',
                str_replace(',','.',$_POST['shipRulePrice2'])
            );
            $this->setParam(
                'shipping>discounts>rulediscount2',
                str_replace(',','.',$_POST['shipRulediscount2'])
            );

            $this->setParam(
                'shipping>comeTakeIt>activated',
                isset($_POST['comeTakeIt_activated'])
            );
            $this->setParam(
                'shipping>comeTakeIt>price',
                str_replace(',','.',$_POST['comeTakeIt_price'])
            );
            $this->setParam(
                'shipping>comeTakeIt>addresses',
                trim($_POST['comeTakeIt_addresses'])
            );

            $this->writeParams();
        }

        // Gets the values
        if($this->getParam('shipping>activated') === true){
            $values['activateShipping']['checked']= 'checked';
        }
        if($this->getParam('shipping>showExpeditionInLegacy',true) === true){
            $values['showExpeditionInLegacy']['checked']= 'checked';
        }
        $supplyers = $this->getParam('shipping>supplyers',array());
        foreach($supplyers as $id=>$supplyer){
            if(isset($supplyer['activated'])){
                $activated = 'checked';
            }else{
                $activated = '';
            }
            $values['modes'][] = array(
                'activated'=>$activated,
                'name'=>stripslashes($supplyer['name']),
                'price'=>$supplyer['price'],
                'id'=>$id,
                'description'=>stripslashes($supplyer['description']),
                'logo'=>$supplyer['logo']
            );
        }
        $values['ship']['rulePrice0'] = $this->getParam(
            'shipping>discounts>rulePrice0', ''
        );
        $values['ship']['rulediscount0'] = $this->getParam(
            'shipping>discounts>rulediscount0', ''
        );
        $values['ship']['rulePrice1'] = $this->getParam(
            'shipping>discounts>rulePrice1',''
        );
        $values['ship']['rulediscount1'] = $this->getParam(
            'shipping>discounts>rulediscount1',''
        );
        $values['ship']['rulePrice2'] = $this->getParam(
            'shipping>discounts>rulePrice2',''
        );
        $values['ship']['rulediscount2'] = $this->getParam(
            'shipping>discounts>rulediscount2',''
        );

        if(!is_dir(SH_IMAGES_FOLDER.'shop/shippers/')){
            $this->links->browser->addFolder(
                SH_IMAGES_FOLDER.'shop',
                'shippers',
                sh_browser::ADDFILE
                + sh_browser::DELETEFILE
                + sh_browser::RENAMEFILE
                + sh_browser::READ,
                '100x80'
            );
        }

        if($this->getParam('shipping>comeTakeIt>activated',false)){
            $values['comeTakeIt']['activated'] = 'checked';
        }

        $values['comeTakeIt']['price'] = $this->getParam(
            'shipping>comeTakeIt>price',
            0
        );
        $values['comeTakeIt']['addresses'] = $this->getParam(
            'shipping>comeTakeIt>addresses',
            ''
        );

        $values['ship']['imagesFolder'] = SH_IMAGES_FOLDER.'shop/shippers/';
        // Renders the module's manager
        $this->render('ship_edit_modes',$values);
    }

    public function editPaymentModes(){
        $this->onlyAdmin();
        if($this->formSubmitted('shopPaymentModesEditor')){
            // State of the module
            if(isset($_POST['addPaymentMode'])){
                $_POST['supplyers'][] = array();
            }
            if(isset($_POST['removePaymentMode'])){
                $key = array_shift(array_keys($_POST['removePaymentMode']));
                unset($_POST['supplyers'][$key]);
            }

            $this->setParam(
                'payment>showPaymentInLegacy',
                isset($_POST['showPaymentInLegacy'])
            );
            if(isset($_POST['showPaymentInLegacy'])){
                $this->addToSitemap($this->shortClassName.'/showPaymentModes/',0.2);
            }else{
                $this->removeFromSitemap($this->shortClassName.'/showPaymentModes/');
            }

            foreach($_POST['supplyers'] as $supplyer){
                $supplyers[] = array(
                    'logo'=>$supplyer['logo'],
                    'name'=>stripslashes($supplyer['name']),
                    'description'=>stripslashes($supplyer['description']),
                    'activated'=>$supplyer['activated']
                );
            }
            $this->setParam('payment>supplyers',$supplyers);

            $this->writeParams();
        }

        // Gets the values
        if($this->getParam('payment>showPaymentInLegacy',true) === true){
            $values['showPaymentInLegacy']['checked']= 'checked';
        }
        $supplyers = $this->getParam('payment>supplyers',array());
        foreach($supplyers as $id=>$supplyer){
            if(isset($supplyer['activated'])){
                $activated = 'checked';
            }else{
                $activated = '';
            }
            $values['modes'][] = array(
                'activated'=>$activated,
                'name'=>stripslashes($supplyer['name']),
                'id'=>$id,
                'description'=>stripslashes($supplyer['description']),
                'logo'=>$supplyer['logo']
            );
        }

        if(!is_dir(SH_IMAGES_FOLDER.'shop/payment/')){
            $this->links->browser->addFolder(
                SH_IMAGES_FOLDER.'shop',
                'payment',
                sh_browser::ADDFILE
                + sh_browser::DELETEFILE
                + sh_browser::RENAMEFILE
                + sh_browser::READ,
                '100x80'
            );
        }

        $values['payment']['imagesFolder'] = SH_IMAGES_FOLDER.'shop/payment/';
        // Renders the module's manager
        $this->render('payment_edit_modes',$values);
    }

    public function deleteCustomProperty(){
        $this->editCustomProperty();
    }

    public function editCustomProperty(){
        $this->onlyAdmin();

        $id = $this->links->path->page['id'];
        if($this->formSubmitted('shopCustomPropertiesEditor')){
            if($id==0){
                $id = count($this->getParam('customProperties',array())) + 1;
                $name = $this->setI18n(0,$_POST['name']);
                if($_POST['saveTextarea'] == 'true'){
                    $list = $this->setI18n(0,$_POST['list']);
                }else{
                    $list = 0;
                }
            }else{
                $name = $this->getParam('customProperties>'.$id.'>name');
                $this->setI18n($name,$_POST['name']);
                $list = $this->getParam('customProperties>'.$id.'>list');
                if($list == 0 && $_POST['saveTextarea'] == 'true'){
                    $list = $this->setI18n(0,$_POST['list']);
                }
                $this->setI18n($list,$_POST['list']);
            }
            // State of the module
            $this->setParam(
                'customProperties>'.$id.'>active',
                isset($_POST['active'])
            );
            $this->setParam(
                'customProperties>'.$id.'>name',
                $name
            );
            $this->setParam(
                'customProperties>'.$id.'>type',
                $_POST['type']
            );
            $this->setParam(
                'customProperties>'.$id.'>list',
                $list
            );
            $this->writeParams();
        }

        // Gets the values
        $customProperties = $this->getParam('customProperties',array());

        foreach($customProperties as $cpId=>$customProperty){
            $values['customProperties'][] = array(
                'name' => $this->getI18n($customProperty['name']),
                'editLink' => $this->translatePageToUri(
                    // class name is not necessary beacause the method doesn't
                    // check it
                    '/'.__FUNCTION__.'/'.$cpId
                ),
                'deleteLink' => $this->translatePageToUri(
                    '/deleteCustomProperty/'.$cpId
                )
            );
        }

        if($id != 0){
            $values['customProperty'] = $customProperties[$id];
            $values['customProperty']['newLink'] = $this->translatePageToUri(
                '/'.__FUNCTION__.'/0'
            );
            if($values['customProperty']['active']){
                $values['customProperty']['state'] = 'checked';
            }
            $values['customProperty']['is'.$values['customProperty']['type']] = 'selected';
        }

        // Renders the module's manager
        $this->render('edit_customProperty',$values);
    }

    /**
     * public function edit
     *
     */
    public function editParams(){
        $this->onlyAdmin();
        if($this->formSubmitted('shopParamsEditor')){
            //Saves the datas
            // Bills bottom text
            $this->setParam(
                'billBottomText',stripslashes($_POST['billBottomText'])
            );

            if($this->getParam('activateShop', true) != isset($_POST['activateShop'])){
                $renewSitemap = true;
            }else{
                $renewSitemap = false;
            }
            // Enables tthe only shop
            $this->setParam('activateShop',isset($_POST['activateShop']));
            sh_browser::setHidden(SH_SHOPIMAGES_FOLDER, !isset($_POST['activateShop']));

            // Format of the prices and currency
            $this->setParam('monney_format',$_POST['monney_format']);
            $this->setParam('currency',$_POST['currency']);

            // Taxes
            $this->setParam('taxes',$_POST['taxes']);
            $this->setParam('taxRate',$_POST['taxRate']);
            $this->setParam('showTaxSymbol',isset($_POST['showTaxSymbol']));

            // State of the cart
            //$this->setParam('activateCart',isset($_POST['activateCart']));
            $this->setParam('activateCart',true);

            $command_mail = str_replace(
                array(',',';','/','\\','"'),
                "\n",
                stripslashes($_POST['command_mail'])
            );
            $command_mail = explode("\n",$command_mail);
            $mailer = $this->links->mailer->get();
            if(is_array($command_mail)){
                foreach($command_mail as $oneMail){
                    if($mailer->checkAddress($oneMail)){
                        $checkedMails .= $separator.$oneMail;
                        $separator = "\n";
                    }
                }
            }
            if($checkedMails == ''){
                $datas = $this->links->user->getData();
                $checkedMails = $datas['mail'];
            }

            $this->setParam('command_mail',$checkedMails);

            // Enables to show quantity
            $this->setParam('showQuantity',isset($_POST['showQuantity']));

            // Enables to show quantity
            $this->setParam(
                'hideNullQuantityProducts',
                isset($_POST['hideNullQuantityProducts'])
            );

            // Saves the commands parts
            if(trim($_POST['command_logo']) != self::$default_logo){
                $logo = trim($_POST['command_logo']);
            }else{
                $logo = '';
            }

            $this->setI18n(self::I18N_BILLFOOTER, $_POST['command_footer']);
            $this->setI18n(self::I18N_BILLHEADLINE, $_POST['command_headLine']);
            $this->setI18n(self::I18N_BILLHEADLINE, $_POST['command_headLine']);
            $this->setI18n(self::I18N_BILLCUSTOMERSERVICE, $_POST['command_customerService']);

            $this->setParam('command>logo',$logo);
            $this->setParam(
                'command>companyName',
                stripslashes($_POST['command_companyName'])
            );
            $this->setParam(
                'command>companyAddress',
                stripslashes($_POST['command_companyAddress'])
            );

            $this->setParam('billColor',$_POST['billColor']);

            // Finaly writes the params
            $this->writeParams();

            if($renewSitemap){
                $this->links->sitemap->renew();
            }
        }

        if($this->getParam('activateShop',true) === true){
            $values['activateShop']['checked'] = 'checked';
        }
        if($this->getParam('activateCart') === true){
            $values['activateCart']['checked'] = 'checked';
        }
        $values['command_mail']['value']= $this->getParam('command_mail');

        if($this->getParam('showQuantity') === true){
            $values['showQuantity']['checked']= 'checked';
        }

        if($this->getParam('hideNullQuantityProducts') === true){
            $values['hideNullQuantityProducts']['checked']= 'checked';
        }

        // Monney format
        $monneyFormats = $this->getParam('monney_formats_listing');
        $monneyFormat = $this->getParam('monney_format',array());
        foreach(array_keys($monneyFormats) as $name){
            if($name == $monneyFormat){
                $state = 'selected';
            }else{
                $state = '';
            }
            $values['monneyFormats'][] = array(
                'name'=>$name,
                'state'=>$state
            );
        }

        // Currency
        $currency = $this->getParam('currency');
        $currencies = $this->getParam('currencies',array());
        foreach($currencies as $cName=>$cValue){
            if($cName == $currency){
                $state = 'selected';
            }else{
                $state = '';
            }
            $values['currencies'][] = array(
                'name'=>$cName,
                'value'=>$cName.' ('.$cValue['symbol'].')',
                'state'=>$state
            );
        }

        // Taxes
        $values['taxes'][0]['value'] = 'TTC';
        $values['taxes'][0]['text'] = $this->getI18n('prices_showTTC');
        $values['taxes'][1]['value'] = 'HT';
        $values['taxes'][1]['text'] = $this->getI18n('prices_showHT');
        if($this->getParam('taxes','TTC') === 'HT'){
            $values['taxes'][1]['selected'] = 'selected';
            $ht = true;
        }else{
            $values['taxes'][0]['selected'] = 'selected';
        }
        $values['tax']['rate'] = $this->getParam('taxRate',19.6);
        if($this->getParam('showTaxSymbol',false)){
            $values['showTaxSymbol']['state'] = 'checked';
        }

        $values['activateMail']['checked']= 'disabled';
        $values['activateCom']['checked']= 'disabled';

        $billColors = $this->getParam('billColors');
        foreach($billColors as $id=>$billColor){
            $values['billColors'][] = array(
                'id'=>$id,
                'color'=>dechex($billColor[0]).dechex($billColor[1])
                .dechex($billColor[2])
            );
        }
        $values['bill']['color'] = $this->getParam('billColor',0);

        // Saves the commands parts
        $logo = $this->getParam('command>logo',self::$default_logo);
        if(
            empty($logo)
            || !file_exists(
                SH_ROOT_FOLDER.$this->links->path->changeToRealFolder(
                    dirname($logo)
                )
                .'/'.basename($logo)
            )
        ){
            $logo = self::$default_logo;
        }
        $values['command']['logo'] = $logo;

        $values['command']['companyName'] = $this->getParam(
            'command>companyName'
        );
        $values['command']['companyAddress'] = $this->getParam(
            'command>companyAddress'
        );

        $values['command']['onClickReplaceImage'] = sh_browser::getOnClickReplaceImage('vrut');
        $values['command']['onClickReplaceId'] = 'vrut';

        $values['billForm']['bottomText'] = $this->getParam('billBottomText');
        $this->render('edit_params',$values);
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring(){
        return get_class();
    }

    //////////////////////////////////////////////////////////////////////////////////////
    //                                  CATEGORIES PART                                 //
    //////////////////////////////////////////////////////////////////////////////////////
    /**
     * protected function category_insertCategory
     *
     */
    protected function category_insertCategory($mother,$daughter){
        $file = $this->categoriesFolder.$mother.'/categories.php';
        if(file_exists($file)){
            include($file);
            if(is_array($categories)){
                if(!in_array($daughter,$categories)){
                    $categories[] = $daughter;
                }
            }else{
                $categories = array($daughter);
            }
        }else{
            $categories = array($daughter);
        }
        $this->links->helper->writeArrayInFile($file, 'categories', $categories);
        return true;
    }

    /**
     * protected function category_insertProduct
     *
     */
    protected function category_insertProduct($category,$product){
        $file = $this->categoriesFolder.$category.'/products.php';
        if(file_exists($file)){
            include($file);
            if(is_null($products)){
                $products = array();
            }
            if(!in_array($product,$products)){
                $products[] = $product;
                $this->links->helper->writeArrayInFile(
                    $file, 'products', $products
                );
            }
        }
        return true;
    }

    /**
     * protected function category_removeCategory
     *
     */
    protected function category_removeCategory($mother,$daughter){
        $file = $this->categoriesFolder.$mother.'/categories.php';
        if(file_exists($file)){
            include($file);
            if(is_array($categories)){
                foreach($categories as $category){
                    if($daughter != $category){
                        $newCategoryList[] = $category;
                    }
                }
            }
            $this->links->helper->writeArrayInFile(
                $file, 'categories', $newCategoryList
            );
        }
        return true;
    }

    /**
     * protected function category_removeProduct
     *
     */
    protected function category_removeProduct($category,$product){
        $file = $this->categoriesFolder.$category.'/products.php';
        if(file_exists($file)){
            include($file);
            if(is_array($products)){
                foreach($products as $oneProduct){
                    if($product != $oneProduct){
                        $newProductList[] = $oneProduct;
                    }
                }
            }
            $this->links->helper->writeArrayInFile(
                $file, 'products', $newProductList
            );
        }
        return true;
    }

    /**
     * public function editCategory
     *
     */
    public function editCategory(){
        $this->debug('Entering the '.__FUNCTION__.' function',2,__LINE__);
        // We first verify that we are at least administrator
        $this->onlyAdmin();

        // We get the id
        $id = $this->links->path->page['id'];
        // We verify if we have to save the results of a submitted form
        if($this->formSubmitted('categoryEditor')){
            if($id == 0){
                // Case of a new category
                $id = $this->increment_counter(
                    $this->shopFolder.'categories/cpt.php'
                );
                $newFolder = $this->categoriesFolder.$id;
                mkdir($newFolder);
                // creates an empty list of subcategories
                $this->links->helper->writeArrayInFile(
                    $this->categoriesFolder.$id.'/categories.php',
                    'categories',
                    array()
                );
                // creates an empty list of products
                $this->links->helper->writeArrayInFile(
                    $this->categoriesFolder.$id.'/products.php',
                    'products',
                    array()
                );
                $name = $this->setI18n(0,$_POST['name']);
                $description = $this->setI18n(0,$_POST['description']);
                $shortDescription = $this->setI18n(0,$_POST['shortDescription']);
                $values['message']['content'] = $this->getI18n(
                    'category_added_successfully'
                );
                $values['message']['link'] = $this->links->path->getLink(
                    'shop/showCategory/'.$id
                );
            }else{
                // Case of an existing category
                $this->removeFromSitemap('shop/showCategory/'.$id);
                include($this->categoriesFolder.$id.'/category.php');
                unlink($this->categoriesFolder.$id.'/category.php');
                $name = $this->setI18n($category['name'],$_POST['name']);
                $description = $this->setI18n(
                    $category['description'],$_POST['description']
                );
                $shortDescription = $this->setI18n(
                    $category['shortDescription'],$_POST['shortDescription']
                );
                $redirect = true;
                $redirectPage = 'shop/showCategory/'.$id;
            }

            $this->search_removeEntry('showCategory',$id);

            $this->search_addEntry(
                'showCategory',
                $id,
                $_POST['name'],
                $_POST['shortDescription'],
                $_POST['description']
           );

            // We save the category to the disk
            $this->links->helper->writeArrayInFile(
                $this->categoriesFolder.$id.'/category.php',
                'category',
                array(
                    'name'=>$name,
                    'description'=>$description,
                    'shortDescription'=>$shortDescription,
                    'image'=>$_POST['image']
                )
            );
            if($id != '1'){
                //We don't do anything on #1, because isn't owned by any other category
                // We get the old parents categories
                if(file_exists($this->categoriesFolder.$id.'/parents.php')){
                    include($this->categoriesFolder.$id.'/parents.php');
                }
                // We also have to save it into the categories' categories.php files
                // and to take it out of the old categories'categories
                if(is_array($parents)){
                    foreach($parents as $parent){
                        $this->category_removeCategory($parent, $id);
                    }
                }
                // We get the checked categories
                if(!is_array($_POST['category_category'])){
                    $_POST['category_category'][1] = 'On';
                }
                $parents = array_keys($_POST['category_category']);

                // And we write them to the parents.php file
                $this->links->helper->writeArrayInFile(
                    $this->categoriesFolder.$id.'/parents.php',
                    'parents',
                    $parents
                );

                // We add the category into the new categories
                if(is_array($parents)){
                    foreach($parents as $parent){
                        $this->category_insertCategory($parent, $id);
                    }
                }
            }
            $this->addToSitemap('shop/showCategory/'.$id,0.8);

            if($redirect == true){
                $this->links->path->redirect($this->links->path->getLink($redirectPage));
                return true;
            }
            // End of the treatments of the submitted form
        }

        if($id == 0){
            // We are creating a new category, so we give the default values
            $values['category'] = array(
                'image'=>'/images/shared/default/defaultPreviewForm.png',
                'onClickReplaceImage'=>$this->links->browser->getOnClickReplaceImage(
                    'image',
                    SH_IMAGES_FOLDER.'shop/categories/'
                )
            );
        }else{
            // The category should already exist, so we get its values if we can
            if(!file_exists($this->categoriesFolder.$id.'/category.php')){
                return $this->notFound();
            }
            // We get the values
            include($this->categoriesFolder.$id.'/category.php');
            $values['category'] = array(
                'name'=>$category['name'],
                'description'=>$category['description'],
                'shortDescription'=>$category['shortDescription'],
                'image'=>$category['image'],
                'onClickReplaceImage'=>$this->links->browser->getOnClickReplaceImage(
                    'image',
                    SH_IMAGES_FOLDER.'shop/categories/'
                )
            );
        }
        // We set the inputs names, not to have to take'em from the .rf.xml
        $values['inputs'] = array(
            'name'=>'name',
            'description'=>'description',
            'shortDescription'=>'shortDescription',
            'active'=>'active',
            'image'=>'image'
        );
        $values['category']['active'] = 'disabled';
        if($id != 1){
            //We don't do anything on #1, because isn't owned by any other category
            $values['category']['isowned'] = 'true';
            // We also have to list the possible parents of the category
            // So we get its reel parents (if it has some)
            if(file_exists($this->categoriesFolder.$id.'/parents.php')){
                include($this->categoriesFolder.$id.'/parents.php');
            }
            $categoryCategories = $parents;
            // And we get the possible parents
            $lastCategories = $this->getCategoriesCategories();

            // We prepare them to fit the form
            foreach($lastCategories as $categoryId){
                if($categoryId != $id){
                    // Get their parents
                    $line = $separator = '';
                    include($this->categoriesFolder.$categoryId.'/parents.php');
                    $firstParent = array_pop($parents);
                    while($firstParent != '0'){
                        include($this->categoriesFolder.$firstParent.'/category.php');
                        include($this->categoriesFolder.$firstParent.'/parents.php');
                        $firstParent = array_pop($parents);
                        $line = $this->getI18n($category['name']).$separator.$line;
                        $separator = ' > ';
                    }
                    // And prepare them to the render task
                    include($this->categoriesFolder.$categoryId.'/category.php');
                    // We check the ones that are its parents
                    if(is_array($categoryCategories) && in_array($categoryId,$categoryCategories)){
                        $checked = 'checked';
                    }else{
                        $checked = '';
                    }
                    $values['categories'][] = array(
                        'name'=>$line.$separator.$this->getI18n($category['name']),
                        'inputName'=>'category_category['.$categoryId.']',
                        'checked'=>$checked
                    );
                }
            }
        }
        // We render the form
        $this->render('edit_category',$values);
    }

    protected function get_category_contents($id){
        include($this->categoriesFolder.$id.'/category.php');

        $thisCategory['category']['name'] = $this->getI18n($category['name']);
        $thisCategory['category']['description'] = $this->getI18n(
            $category['description']
        );
        $thisCategory['category']['shortDescription'] = $this->getI18n(
            $category['shortDescription']
        );
        $thisCategory['category']['image'] = $category['image'];

        if(file_exists($this->categoriesFolder.$id.'/parents.php')){
            include($this->categoriesFolder.$id.'/parents.php');
            $thisCategory['category']['parent'] = $parents[0];
        }

        return $thisCategory;
    }

     /**
      * Shows the category asked in the url
      * @return bool The status of the function
      */
    public function showCategory(){
        $this->debug('Entering the '.__FUNCTION__.' function',2,__LINE__);

        $this->links->sitemap->renew();

        // We get the category's datas
        $id = $this->links->path->page['id'];

        // We send an error if the product doesn't exist (or isn't activated)
        if(! file_exists($this->categoriesFolder.$id.'/category.php')){
            return $this->notFound();
        }
        // And add an entry in the command panel
        $this->links->admin->insert(
            '<a href="'.$this->links->path->getLink(
                'shop/editCategory/'.$id
            ).'">Modifier cette catégorie</a>',
            'Boutique',
            'bank1/picto_modify.png'
        );

        // We get the first element to show (-1 to start with 0)
        if(isset($_GET['page'])){
            $page = $_GET['page'] - 1;
        }
        if(!isset($page) || $page < 0){
            $page = 0;
        }

        // We get the category's datas
        $category = $this->get_category_contents($id);
        $parent = $category['category']['parent'];

        $elements = $this->get_category_elements($id,$page,$number);

        // We ask for the category bar
        $bar = $this->get_category_bar($id);

        // And set the title
        $this->links->html->setTitle($category['category']['name']);

        // We render the file using the desired renderer
        if($elements['contains_products']){
            // We manage with different types of list
            $listType = $elements['listType'];
            if($this->templateIsCustomized){
                $rf = $this->links->site->templateFolder.'shop/products_'.$listType.'.rf.xml';
            }else{
                $rf = 'default_products_'.$listType;
            }
            // Get the list types
            // We now merge all these values to send them to the renderer
            $listTypes = $this->get_type_selector();
            $values = array_merge($elements, $listTypes, $bar, $category);
        }else{
            if($this->templateIsCustomized){
                $rf = $this->links->site->templateFolder.'shop/categories.rf.xml';
            }else{
                $rf = 'default_categories';
            }
            // We now merge all these values to send them to the renderer
            $values = array_merge($elements, $bar, $category);
        }
        $this->render($rf,$values);


        // Some templates need an image from out of the content part.
        //This is it...
        $this->links->html->setGeneralImage($values['category']['image']);
        return true;
    }

    protected function getCategoriesListingParams(){
        if($this->templateIsCustomized){
            $params = $this->links->template->get(__CLASS__.'>categoriesListing');
        }else{
            $params = $this->getParam('categoriesListing');
        }

        if(is_array($params)){
            return $params;
        }
        return $this->getParam('categoriesListing');
    }


    protected function getProductsListingParams(){
        if($this->links->path->page['action'] == 'showProduct'){
            if($this->templateIsCustomized){
                return array(
                    'product' => $this->links->template->get(__CLASS__.'>product')
                );
            }else{
                return array(
                    'product' => $this->getParam('product')
                );
            }
        }
        if($this->templateIsCustomized){
            $params = $this->links->template->get(__CLASS__.'>productsListing');
        }else{
            $params = $this->getParam('productsListing');
        }

        if(is_array($params)){
            return $params;
        }
        return $this->getParam('productsListing');
    }

    protected function getProductListType(){
        if($this->links->path->page['action'] == 'showProduct'){
            return 'product';
        }
        $arg_prodLType = self::ARG_PRODUCTLISTTYPE;
        if(isset($_GET[$arg_prodLType]) && !empty($_GET[$arg_prodLType])){
            $this->setProductListType($_GET[$arg_prodLType]);
        }
        if(is_null($this->listType)){
            if(!isset($_SESSION[__CLASS__][$arg_prodLType])){
                $this->setProductListType();
            }
            $this->listType = $_SESSION[__CLASS__][$arg_prodLType];
        }
        return $this->listType;
    }

    protected function setProductListType($type = 'default'){
        $arg_prodLType = self::ARG_PRODUCTLISTTYPE;
        $params = $this->getProductsListingParams();
        if($type == 'default' || !is_int($params[$type]['productsNumber'])){
            $type = $params['default'];
            $_SESSION[__CLASS__][$arg_prodLType] = $type;
            $this->listType = $type;
        }else{
            $this->listType = $type;
            $_SESSION[__CLASS__][$arg_prodLType] = $type;
            return true;
        }
        return false;
    }

    /**
     * Gets all the categories containing no other categories
     * @return array An array of that categories
     */
    protected function getProductsCategories(){
        $categoriesList = scandir($this->shopFolder.'categories/');
        foreach($categoriesList as $id){
            if(file_exists($this->categoriesFolder.$id.'/categories.php')){
                include($this->categoriesFolder.$id.'/categories.php');
                if(is_null($categories) || $categories == array()){
                    $lastCategories[] = $id;
                }
            }
        }
        return $lastCategories;
    }

    /**
     * Gets all the categories containing no products
     * @return array An array of that categories
     */
    protected function getCategoriesCategories(){
        $categoriesList = scandir($this->shopFolder.'categories/');

        foreach($categoriesList as $id){
            if(file_exists($this->categoriesFolder.$id.'/categories.php')){
                if(file_exists($this->categoriesFolder.$id.'/products.php')){
                    include($this->categoriesFolder.$id.'/products.php');
                    if(is_null($products) || $products == array()){
                        $lastCategories[] = $id;
                    }
                }else{
                    $lastCategories[] = $id;
                }
            }
        }
        return $lastCategories;
    }

    //////////////////////////////////////////////////////////////////////////////////////
    //                                  PRODUCTS PART                                   //
    //////////////////////////////////////////////////////////////////////////////////////
    /**
     * Gets all the datas stored for a product.
     * @param int $id The id of the product
     * @return array An array containing all the product's data
     */
    protected function get_product_contents($id){
        include($this->productsFolder.$id.'/product.php');

        $productDatas['product']['name'] = $this->getI18n($product['name']);
        $productDatas['product']['reference'] = $product['reference'];
        $productDatas['product']['description'] = nl2br(
            $this->getI18n($product['description'])
        );
        $productDatas['product']['shortDescription'] = $this->getI18n(
            $product['shortDescription']
        );
        if($product['stock'] > 0){
            $productDatas['product']['stock'] = $product['stock'];
            $addToCartLink = $this->links->path->getLink('shop/addToCart/');
            $addToCartLink .= '?product='.$id;
            $productDatas['product']['picto_addToCart_link'] = $addToCartLink;
        }else{
            $productDatas['product']['stock'] = $this->getI18n(
                'product_nomorestock'
            );
        }
        $productDatas['product']['image'] = $product['image'];
        $productDatas['images'] = $product['images'];
        $productDatas['product']['price'] = $this->monney_format($product['price']);

        if(file_exists($this->productsFolder.$id.'/parents.php')){
            include($this->productsFolder.$id.'/parents.php');
            $productDatas['product']['parent'] = array_pop($parents);
        }else{
            $productDatas['product']['parent'] = 1;
        }

        return $productDatas;
    }

    /**
     * Called directly by the end user.
     * Creates and shows the page of a product.
     * @return bool The status of the function
     */
    public function showProduct(){
        $this->links->javascript->get(sh_javascript::LIGHTWINDOW);

        $this->debug('Entering the '.__FUNCTION__.' function',2,__LINE__);
        $id = $this->links->path->page['id'];

        // We send an error if the product doesn't exist (or isn't activated)
        if(!$this->isProductAvailable($id,0)){
            return $this->notFound();
        }
        // And add an entry in the command panel
        $this->links->admin->insert(
            '<a href="'.$this->links->path->getLink('shop/editProduct/'.$id).'">Modifier ce produit</a>',
            'Boutique',
            'bank1/picto_modify.png'
        );

        // We get the first element to show (-1 to start with 0)
        if(isset($_GET['page'])){
            $page = $_GET['page'] - 1;
        }
        if(!isset($page) || $page < 0){
            $page = 0;
        }

        // We get the product's datas
        $product = $this->get_product_contents($id);
        $parent = $product['product']['parent'];
        $images = $product['images'];
        unset($product['images']);
        if(is_array($images)){
            foreach($images as $image){
                if(trim($image) != ''){
                    if($this->links->images->imageExists($image)){
                        $product['productImages'][]['src'] = $image;
                    }
                }
            }
        }

        // We ask for the category bar
        $bar = $this->get_category_bar($parent);

        // And add an entry in the command panel
        $this->links->admin->insert(
            '<a href="'.$this->links->path->getLink(
                'shop/editCategory/'.$parent
            ).'">Modifier cette catégorie</a>',
            'Boutique',
            'bank1/picto_modify.png'
        );

        // We now get all the brother-products
        $categories = $this->get_category_elements($parent,$page);

        // We now merge all these values to send them to the renderer
        $values = array_merge($categories, $bar, $product);

        // We render the product file using the desired renderer
        if($this->templateIsCustomized){
            $rf = $this->links->site->templateFolder.'shop/product.rf.xml';
        }else{
            $rf = 'default_product';
        }
        $this->render($rf,$values);

        $this->links->html->setTitle($values['product']['name']);

        // Some templates need an image from out of the content part.
        //This is it...
        $this->links->html->setGeneralImage($values['product']['image']);
        return true;
    }

    public function getCustomPropertyValues(){

    }

    /**
     * Method that creates and manages the return of the form to edit a product
     */
    public function editProduct(){
        $this->debug('Entering the '.__FUNCTION__.' function',2,__LINE__);
        // We first verify that we are at least administrator
        $this->onlyAdmin();

        // We get the id
        $id = $this->links->path->page['id'];
        // We verify if we have to save the results of a submitted form
        if($this->formSubmitted('productEditor')){
            if(isset($_POST['active'])){
                $active = true;
            }else{
                $active = false;
            }
            if(is_array($_POST['product_category'])){
                if($id == 0){
                    // Case of a new product
                    $id = $this->increment_counter(
                        $this->shopFolder.'products/cpt.php'
                    );
                    $newFolder = $this->productsFolder.$id;
                    mkdir($newFolder);
                    $name = $this->setI18n(0,$_POST['name']);
                    $description = $this->setI18n(0,$_POST['description']);
                    $shortDescription = $this->setI18n(0,$_POST['shortDescription']);

                    $values['message']['content'] = $this->getI18n(
                        'product_added_successfully'
                    );
                    $values['message']['link'] = $this->links->path->getLink(
                        'shop/showProduct/'.$id
                    );
                }else{
                    $this->removeFromSitemap('shop/showProduct/'.$id);
                    // Case of an existing product
                    include($this->productsFolder.$id.'/product.php');
                    unlink($this->productsFolder.$id.'/product.php');
                    $name = $this->setI18n($product['name'],$_POST['name']);
                    $description = $this->setI18n(
                        $product['description'],$_POST['description']
                    );
                    $shortDescription = $this->setI18n(
                        $product['shortDescription'],$_POST['shortDescription']
                    );

                    $this->search_removeEntry('showProduct',$id);

                    $this->search_addEntry(
                        'showProduct',
                        $id,
                        $_POST['name'],
                        $_POST['shortDescription'],
                        $_POST['description']
                   );

                    // We redirect to the product page only if it is active (of course)
                    if($active){
                        $redirect = true;
                        $redirectPage = 'shop/showProduct/'.$id;
                    }
                }
                // We save the product to the disk
                $this->links->helper->writeArrayInFile(
                    $this->productsFolder.$id.'/product.php',
                    'product',
                    array(
                        'name'=>$name,
                        'reference'=>$_POST['reference'],
                        'description'=>$description,
                        'shortDescription'=>$shortDescription,
                        'image'=>$_POST['image'],
                        'stock'=>$_POST['stock'],
                        'active'=>$active,
                        'price'=>str_replace(',','.',$_POST['price']),
                        'images'=>explode('|',$_POST['images'])
                    )
                );
                // We get the old parents categories
                if(file_exists($this->productsFolder.$id.'/parents.php')){
                    include($this->productsFolder.$id.'/parents.php');
                }
                // We also have to save it into the categories' products.php files
                // and to take it out of the old categories'products
                if(is_array($parents)){
                    foreach($parents as $parent){
                        $this->category_removeProduct($parent, $id);
                    }
                }
                // We get the checked categories
                $parents = array_keys($_POST['product_category']);
                // And we write them to the parents.php file
                $this->links->helper->writeArrayInFile(
                    $this->productsFolder.$id.'/parents.php',
                    'parents',
                    $parents
                );

                // We add the product into the new categories
                if(is_array($parents)){
                    foreach($parents as $parent){
                        $this->category_insertProduct($parent, $id);
                    }
                }

                $this->addToSitemap('shop/showProduct/'.$id,0.7);
                if($redirect == true){
                    $this->links->path->redirect(
                        $this->links->path->getLink($redirectPage)
                    );
                    return true;
                }
                // End of the treatments of the submitted form
            }else{
                $values['error']['noCategory'] = 'true';
                if($active){
                    $active = 'checked';
                }else{
                    $active = '';
                }
                $values['product'] = array(
                    'name'=>stripslashes($_POST['name']),
                    'price'=>$_POST['price'],
                    'description'=>stripslashes($_POST['description']),
                    'shortDescription'=>stripslashes($_POST['shortDescription']),
                    'reference'=>$_POST['reference'],
                    'image'=>$_POST['image'],
                    'images'=>explode('|',$_POST['images']),
                    'stock'=>$_POST['stock'],
                    'active'=>$active,
                    'onClickReplaceImage'=>$this->links->browser->getOnClickReplaceImage(
                        'image',
                        SH_SHOPIMAGES_FOLDER.'products/'
                    )
                );
                $values['images'] = $_POST['images'];
                $dontChangeValues = true;
            }
        }

        if(!$dontChangeValues){
            if($id == 0){
                // We are creating a new product, so we give the default values
                $values['product'] = array(
                    'image'=>'/images/shared/default/defaultPreviewForm.png',
                    'stock'=>0,
                    'active'=>'checked',
                    'onClickReplaceImage'=>$this->links->browser->getOnClickReplaceImage(
                        'image',
                        SH_SHOPIMAGES_FOLDER.'products/'
                    )
                );
            }else{
                // The product should already exist, so we get its values if we can
                if(!file_exists($this->productsFolder.$id.'/product.php')){
                    return $this->notFound();
                }
                // We get the values
                include($this->productsFolder.$id.'/product.php');
                if(!isset($product['active']) || $product['active'] == true){
                    $active = 'checked';
                }else{
                    $active = '';
                }
                if(!is_array($product['images'])){
                    $product['images'] = array();
                }
                $values['product'] = array(
                    'name'=>$product['name'],
                    'price'=>$product['price'],
                    'description'=>$product['description'],
                    'shortDescription'=>$product['shortDescription'],
                    'reference'=>$product['reference'],
                    'image'=>$product['image'],
                    'images'=>implode('|',$product['images']),
                    'imagesFolder'=>'SH_SHOPIMAGES_FOLDER',
                    'stock'=>$product['stock'],
                    'active'=>$active,
                    'onClickReplaceImage'=>$this->links->browser->getOnClickReplaceImage(
                        'image',SH_SHOPIMAGES_FOLDER.'products/'
                    )
                );
                $customProperties = $this->getParam('customProperties', array());
                foreach($customProperties as $key=>$customProperty) {
                    $values['customProperties'][$key]['name'] = $this->getI18n(
                        $customProperty['name']
                    );
                    $values['customProperties'][$key]['is'.$customProperty['type']] = true;
                    if($customProperty['type'] == 'list'){
                        $values['customProperties'][$key]['isList'] = true;
                        $elements = explode(
                            "\n",
                            $this->getI18n($customProperty['list'])
                        );
                        $values['customProperties'][$key]['id'] = $key;
                        foreach($elements as $id=>$element) {
                            $values['customProperties'][$key]['values'][$id] = array(
                                'value' => $element,
                                'id'=>$id
                            );
                        }
                    }

                }
            }
        }
        // We set the inputs names, not to have to take'em from the .rf.xml
        $values['inputs'] = array(
            'name'=>'name',
            'price'=>'price',
            'description'=>'description',
            'shortDescription'=>'shortDescription',
            'reference'=>'reference',
            'image'=>'image',
            'images'=>'images',
            'active'=>'active',
            'stock'=>'stock'
        );
        // We also have to list the possible parents of the product
        // So we get its real parents (if it has some)
        if(file_exists($this->productsFolder.$id.'/parents.php')){
            include($this->productsFolder.$id.'/parents.php');
        }
        $productCategories = $parents;
        // And we get the possible parents
        $lastCategories = $this->getProductsCategories();
        // We prepare them to fit the form
        foreach($lastCategories as $categoryId){
            // Get their parents
            $line = $separator = '';
            include($this->categoriesFolder.$categoryId.'/parents.php');
            $firstParent = array_pop($parents);
            while($firstParent != '0'){
                include($this->categoriesFolder.$firstParent.'/category.php');
                include($this->categoriesFolder.$firstParent.'/parents.php');
                $firstParent = array_pop($parents);
                $line = $this->getI18n($category['name']).$separator.$line;
                $separator = ' > ';
            }
            // And prepare them to the render task
            include($this->categoriesFolder.$categoryId.'/category.php');
            // We check the ones that are its parents
            if(is_array($productCategories) && in_array($categoryId,$productCategories)){
                $checked = 'checked';
            }else{
                $checked = '';
            }
            $values['categories'][] = array(
                'name'=>$line.$separator.$this->getI18n($category['name']),
                'inputName'=>'product_category['.$categoryId.']',
                'checked'=>$checked
            );
        }
        // We render the form
        $this->render('edit_product',$values);
    }

    /**
     * protected function productExists
     *
     */
    protected function categoryExists($id){
        if(file_exists($this->categoriesFolder.$id.'/category.php')){
            return true;
        }
        return false;
    }

    /**
     * protected function productExists
     *
     */
    protected function productExists($id){
        if(file_exists($this->productsFolder.$id.'/product.php')){
            return true;
        }
        return false;
    }

    /**
     * protected function isProductAvailable
     *
     */
    protected function isProductAvailable($id,$quantity = 0){
        if($this->productExists($id)){
            include($this->productsFolder.$id.'/product.php');
            if(!isset($product['active']) || $product['active'] == true){
                if($product['stock'] >= $quantity){
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
    protected function changeQuantity($id,$num,$operand = '='){
        if(!$this->productExists($id)){
            return false;
        }
        include($this->productsFolder.$id.'/product.php');
        if($operand == '='){
            $product['stock'] = $num;
        }elseif($operand == '+'){
            $product['stock'] += $num;
        }elseif($operand == '-'){
            $product['stock'] -= $num;
        }
        return $this->links->helper->writeArrayInFile(
            $this->productsFolder.$id.'/product.php',
            'product',
            $product
        );
    }

    /**
     * public function switchProductState
     *
     */
    public function switchProductState($id = null){
        if(is_null($id)){
            $id = $_GET['id'];
        }
        if(!$this->productExists($id)){
            return false;
        }
        include($this->productsFolder.$id.'/product.php');
        if($product['active'] === false){
            $product['active'] = true;
            $ret = 'true';
        }else{
            $product['active'] = false;
            $ret = 'false';
        }
        $this->links->helper->writeArrayInFile(
            $this->productsFolder.$id.'/product.php',
            'product',
            $product
        );
        echo $ret;
        return true;
    }

    public function showAllProducts(){
        $this->onlyAdmin();

        $categories = scandir($this->categoriesFolder);

        foreach($categories as $categoryId){
            if(file_exists($this->categoriesFolder.$categoryId.'/products.php')){
                $products = array();
                include($this->categoriesFolder.$categoryId.'/products.php');
                if(!empty($products)){
                    include($this->categoriesFolder.$categoryId.'/category.php');
                    $pathToCategory = $this->getI18n($category['name']);

                    include($this->categoriesFolder.$categoryId.'/parents.php');
                    $firstParent = array_shift($parents);

                    while($firstParent != '0'){
                        include($this->categoriesFolder.$firstParent.'/category.php');
                        $pathToCategory = $this->getI18n($category['name']).' > '.$pathToCategory;
                        include($this->categoriesFolder.$firstParent.'/parents.php');
                        $firstParent = array_shift($parents);
                    }
                    $link = $this->links->path->getLink(
                        $this->shortClassName.'/'.__FUNCTION__.'/'
                    ).'?listedCategory='.$categoryId;
                    $values['categories'][] = array(
                        'name' => $pathToCategory,
                        'link' => $link
                    );
                }
            }
        }

        if(isset($_GET['listedCategory'])){
            $listedCategory = $_GET['listedCategory'];
            if(file_exists($this->categoriesFolder.$listedCategory.'/products.php')){
                include($this->categoriesFolder.$listedCategory.'/products.php');
                if(!empty($products)){
                    $categories = array();
                    $cpt=0;
                    foreach($products as $id){
                        if(is_dir($this->productsFolder.$id) && $id[0] != '.'){
                            include($this->productsFolder.$id.'/product.php');
                            include($this->productsFolder.$id.'/parents.php');
                            $category = array_shift($parents);
                            if($product['active'] !== false){
                                $state = 'checked';
                            }else{
                                $state = '';
                            }
                            $values['products'][$cpt++] = array(
                                'id' => $id,
                                'name' => $this->getI18n($product['name']),
                                'category' => $category,
                                'state' => $state,
                                'link' => $this->translatePageToUri(
                                    $this->shortClassName.'/editProduct/'.$id

                                    )
                            );
                            if(!in_array($category,$categories)){
                                $categories[] = $category;
                            }
                        }
                    }
                    if(!empty($values['products'])){
                        ksort($values['products']);
                        $values['pages']['switchState'] = $this->translatePageToUri(
                            $this->shortClassName.'/switchProductState/'
                        );
                    }else{
                        $values['products']['empty'] = true;
                    }
                }
            }
        }


        $this->render('list_all', $values);
        return true;
    }

    //////////////////////////////////////////////////////////////////////////////////////
    //                                  NAVIGATOR PART                                  //
    //////////////////////////////////////////////////////////////////////////////////////
    /**
     * protected function getNavigator
     *
     */
    protected function getNavigator($id){
        $this->links->admin->insert(
            '<a href="'.$this->links->path->getLink('shop/editCategory/'.$id)
            .'">Modifier cette catégorie</a>',
            'Boutique',
            '001_45.png'
        );

        include($this->categoriesFolder.$id.'/category.php');
        $mainCategory = $category;
        include($this->categoriesFolder.$id.'/categories.php');
        if(is_array($categories)){
            foreach($categories as $categoryId){
                if(file_exists($this->categoriesFolder.$categoryId.'/category.php')){
                    include($this->categoriesFolder.$categoryId.'/category.php');
                    $category['id'] = $categoryId;
                    $category['type'] = 'category';
                    $elements[] = $category;
                }
            }
        }
        include($this->categoriesFolder.$id.'/products.php');
        if(is_array($products)){
            foreach($products as $productId){
                if(file_exists($this->productsFolder.$productId.'/product.php')){
                    include($this->productsFolder.$productId.'/product.php');
                    $product['id'] = $productId;
                    $product['type'] = 'product';
                    $elements[] = $product;
                }
            }
        }

        // Finds and shows the navigator
        $navigatorBar = $this->getNavigator_bar($id);

        if(!$onlyBreadCrumbs){
            $getNavigator_action = 'getNavigator_'.$navigator_type;
            $navigator_content = $this->$getNavigator_action($elements,$page);
        }

        return $displaySelector.$navigatorBar.$navigator_content;
    }

    protected function get_type_selector(){
        $elementType = 'products';
        $navigator_type = $this->getProductListType();
        $params = $this->getProductsListingParams();
        $navigator_types = array_keys($params);
        $selector = array();
        if(isset($navigator_types['default'])){
            unset($navigator_types['default']);
        }
        if($navigator_type == '' || !in_array($navigator_type,$navigator_types)){
            $changeType = true;
        }
        if(is_array($navigator_types) && count($navigator_types)>1){
            $cpt = 0;
            foreach($navigator_types as $file=>$oneType){
                if($cpt++ == 0 && $changeType){
                    $navigator_type = $oneType;
                    $_SESSION[__CLASS__]['navigator_type'] = $navigator_type;
                }
                if($oneType != $navigator_type && $oneType != 'default'){
                    // We update the args list to create the url
                    $this->links->path->parsed_url['parsed_query'][self::ARG_PRODUCTLISTTYPE] = $oneType;
                    $args = '';
                    $separator = '';
                    foreach($this->links->path->parsed_url['parsed_query'] as $argName=>$argValue){
                        $args .= $separator.$argName.'='.$argValue;
                        $separator = '&';
                    }
                    $destPage = $this->links->path->uri.'?'.$args;
                    $selector['selectors']['link_'.$oneType] = $destPage;
                }
            }
        }

        return $selector;
    }

    /**
     * protected function getNavigator_bar
     *
     *//*
    protected function get_category_bar($id){
        include($this->categoriesFolder.$id.'/parents.php');
        $firstParent = $id;//array_pop($parents);
        $cpt = 0;

        if($firstParent != '0'){
            $genealogy['nav_bar']['display'] = 'true';
        }

        while($firstParent != '0'){
            include($this->categoriesFolder.$firstParent.'/category.php');
            $genealogy['nav_levels'][$cpt] = array(
                'id'=>$firstParent,
                'categoryLink'=>$this->links->path->getLink(
                    'shop/showCategory/'.$firstParent
                ),
                'name'=>$this->getI18n($category['name']),
                'separator'=>$separator
            );
            include($this->categoriesFolder.$firstParent.'/parents.php');
            $firstParent = array_pop($parents);
            if(file_exists($this->categoriesFolder.$firstParent.'/categories.php')){
                $genealogy['nav_levels'][$cpt]['hasSisters'] = true;
                include($this->categoriesFolder.$firstParent.'/categories.php');
                if(is_array($categories)){
                    foreach($categories as $categoryId){
                        include($this->categoriesFolder.$categoryId.'/category.php');
                        $link = $this->links->path->getLink(
                            'shop/showCategory/'.$categoryId
                        );
                        $link .= '?'.self::ARG_PRODUCTLISTTYPE.'='.$this->getProductListType();
                        $genealogy['nav_levels'][$cpt]['sisters'][] = array(
                            'categoryLink'=>$link,
                            'name'=>$this->getI18n($category['name'])
                        );
                    }
                }
            }
            $separator = ' > ';
            $cpt++;
        }
        if(is_array($genealogy['nav_levels'])){
            $genealogy['nav_levels'] = array_reverse($genealogy['nav_levels']);
        }else{
            $genealogy = array();
        }

        return $genealogy;
    }*/

    /**
     * protected function get_category_bar
     *
     */
    protected function get_category_bar($id){
        include($this->categoriesFolder.$id.'/parents.php');
        $parent = $id;//array_pop($parents);
        $cpt = 0;

        if($parent != '0'){
            $genealogy['nav_bar']['display'] = 'true';
        }

        // We loop until we arrive to category #0
        while($parent != '0'){
            // We get the category's datas
            include($this->categoriesFolder.$parent.'/category.php');
            $genealogy['nav_levels'][$cpt] = array(
                'id'=>$parent,
                'link'=>$this->links->path->getLink(
                    'shop/showCategory/'.$parent
                ),
                'name'=>$this->getI18n($category['name'])
            );

            if(file_exists($this->categoriesFolder.$parent.'/categories.php')){
                include($this->categoriesFolder.$parent.'/categories.php');
                if(is_array($categories)){
                    foreach($categories as $categoryId){
                        include($this->categoriesFolder.$categoryId.'/category.php');

                        $link = $this->links->path->getLink(
                            'shop/showCategory/'.$categoryId
                        );
                        $link .= '?'.self::ARG_PRODUCTLISTTYPE.'='.$this->getProductListType();
                        $genealogy['nav_levels'][$cpt]['daughters'][] = array(
                            'link'=>$link,
                            'name'=>$this->getI18n($category['name'])
                        );
                    }
                }
            }
            // And get the category's parent and prepare the next loop
            include($this->categoriesFolder.$parent.'/parents.php');
            $parent = array_pop($parents);
            $cpt++;
        }
        if(is_array($genealogy['nav_levels'])){
            $genealogy['nav_levels'] = array_reverse($genealogy['nav_levels']);
        }

        return $genealogy;
    }

    public function renderer_format($values,$formatName = ''){
        if($formatName == '' || $formatName == 'list' || $formatName == 'table'){
            return $values;
        }
        if($formatName == 'grid'){
            $max = 9;
        }
        if($formName == 'miniature'){
            $max = 12;
        }
    }

    protected function get_category_products($id){
        if(isset($_GET['page'])){
            $page = $_GET['page'] - 1;
        }
        if(!isset($page) || $page < 0){
            $page = 0;
        }
        $elementsByPage = 20;
        include($this->categoriesFolder.$id.'/category.php');
        $mainCategory = $category;
        include($this->categoriesFolder.$id.'/products.php');
        if(is_array($products)){
            foreach($products as $productId){
                if(file_exists($this->productsFolder.$productId.'/product.php')){
                    include($this->productsFolder.$productId.'/product.php');
                    $product['id'] = $productId;
                    $product['type'] = 'product';
                    $elements['category'][] = $product;
                }
            }
            // Verifies if there is anything in the category. If not, we tell it and exit
            // this method
            if(!is_array($elements) || empty($elements)){
                return array('category'=>array('empty'=>true));
            }
            // Prepares the Next & Previous buttons
            $pageActuelle = preg_replace(
                '`(\?page=[0-9]+)`','',$this->links->path->uri
            );
            if($page > 0){
                $ret['products']['previous'] = true;
                $values['products']['previous_link'] = $pageActuelle.'?page='.$page;
            }
            if(count($elements)>=(($page + 1) * ($elementsByPage + 1))){
                $ret['products']['next'] = true;
                $values['products']['next_link'] = $pageActuelle.'?page='.($page + 2);
            }

            if($nav_pages){
                for($nav_pages_num = 1;$nav_pages_num <= ceil(count($elements) / $elementsByPage); $nav_pages_num++){
                    $class = '';
                    if($nav_pages_num == $page + 1){
                        $class = 'bold';
                    }
                    $values['pages'][] = array(
                        'link' => $pageActuelle.'?page='.($nav_pages_num),
                        'number' => $nav_pages_num,
                        'class' => $class
                    );
                }
            }

            // We only keep the used part (depending on $page and $elementsByPage).
            $elements = array_slice(
                $elements, ($page * $elementsByPage), $elementsByPage
            );
            // And we loop on them, to set the required datas
            while($element = array_shift($elements)){
                if(is_array($element)){
                    $values['category_elements'][$cpt]['navigator_image'] = $element['image'];
                    $values['category_elements'][$cpt]['class'] = 'list_image';
                    $values['category_elements'][$cpt]['navigator_desc'] = $this->getI18n($element['shortDescription']);
                    $link = $this->links->path->getLink('shop/showProduct/'.$element['id']);
                    $values['category_elements'][$cpt]['navigator_link'] = $link;
                    $values['category_elements'][$cpt]['navigator_ref'] = $element['ref'];
                    $values['category_elements'][$cpt]['navigator_price'] = $element['price'];
                    $addToCartLink = $this->links->path->getLink('shop/addToCart/');
                    $addToCartLink .= '?product='.$element['id'];
                    $values['category_elements'][$cpt]['pictos'][0]['image'] = '/images/shared/icons/picto_cart.png';
                    $values['category_elements'][$cpt]['pictos'][0]['image_alt'] = 'picto_cart';
                    $values['category_elements'][$cpt]['pictos'][0]['link'] = $addToCartLink;
                    $values['category_elements'][$cpt]['pictos'][1]['image'] = '/images/shared/icons/picto_details.png';
                    $values['category_elements'][$cpt]['pictos'][1]['image_alt'] = 'picto_details';
                    $values['category_elements'][$cpt]['pictos'][1]['link'] = $link;
                    $cpt++;
                }
            }
        }else{
            $elements = array();
        }

        return $elements;
    }

    protected function get_category_elements($id, $page = 0){
        if(!file_exists($this->categoriesFolder.$id.'/category.php')){
            $this->links->path->error(404);
        }
        include($this->categoriesFolder.$id.'/category.php');

        if(file_exists($this->categoriesFolder.$id.'/products.php')){
            include($this->categoriesFolder.$id.'/products.php');
        }else{
            $products = array();
        }
        if(file_exists($this->categoriesFolder.$id.'/categories.php')){
            include($this->categoriesFolder.$id.'/categories.php');
        }else{
            $categories = array();
        }

        if(empty($products) && empty($categories)){
            // This category is empty
            return array('category_elements'=>array('empty'=>true));
        }

        // We check the kind of contents this category has
        if(!empty($products)){
            // This category contains only products
            $totalNumberOfElements = count($products);
            $values['contains_products'] = true;
            $elements = $products;

            $listType = $this->getProductListType();
            $values['listType'] = $listType;
            // We get the template's params for this action
            $params = $this->getProductsListingParams();
            $number = $params[$listType]['productsNumber'];
            if(isset($params[$listType]['groupedBy'])){
                $groupsSize = $params[$listType]['groupedBy'];
                if(isset($params[$listType]['fillWith'])){
                    $fillWith = $params[$listType]['fillWith'];
                }
            }else{
                $groupsSize = null;
            }
        }elseif(empty($categories)){
            $values['category']['empty'] = true;
            return $values;
        }else{
            // This category contains only categories
            $totalNumberOfElements = count($categories);
            $values['contains_categories'] = true;
            $elements = $categories;
            // We get the template's params for this action
            $params = $this->getCategoriesListingParams();
            $number = $params['categoriesNumber'];
            $fillWith = '';
            if(isset($params['groupedBy'])){
                $groupsSize = $params['groupedBy'];
                if(isset($params['fillWith'])){
                    $fillWith = $params['fillWith'];
                }
            }else{
                $groupsSize = null;
            }
        }

        // We limit the categories using the first and the number of element to show
        $chunked = array_chunk($elements,$number);
        if(!isset($chunked[$page])){
            $page = 0;
        }
        $elements = $chunked[$page];

        $cpt = 0;
        $hideNullQuantityProducts = $this->getParam('hideNullQuantityProducts',false);
        // We get their datas
        if($values['contains_products']){
            foreach($elements as $element){
                if(file_exists($this->productsFolder.$element.'/product.php')){
                    include($this->productsFolder.$element.'/product.php');
                    if($product['stock'] > 0 || !$hideNullQuantityProducts){
                        $values['category_elements'][$cpt]['name'] =
                            $this->getI18n($product['name']);
                        $values['category_elements'][$cpt]['image'] = $product['image'];
                        $values['category_elements'][$cpt]['shortDescription'] =
                            $this->getI18n($product['shortDescription']);
                        $values['category_elements'][$cpt]['description'] =
                            $this->getI18n($product['description']);
                        $link = $this->links->path->getLink('shop/showProduct/'.$element);
                        $values['category_elements'][$cpt]['link'] = $link;
                        $values['category_elements'][$cpt]['reference'] = $product['reference'];
                        $values['category_elements'][$cpt]['price'] = $this->monney_format(
                            $product['price']
                        );
                        if($product['stock'] > 0){
                            $values['category_elements'][$cpt]['stock'] = $product['stock'];
                            $addToCartLink = $this->links->path->getLink('shop/addToCart/');
                            $addToCartLink .= '?product='.$element;
                            $values['category_elements'][$cpt]['picto_addToCart_link'] = $addToCartLink;
                        }else{
                            $values['category_elements'][$cpt]['stock'] = $this->getI18n('product_nomorestock');
                        }
                        $values['category_elements'][$cpt]['picto_show_link'] = $link;
                        $cpt++;
                    }
                }
            }
        }else{
            foreach($elements as $element){
                if(file_exists($this->categoriesFolder.$element.'/category.php')){
                    include($this->categoriesFolder.$element.'/category.php');
                    $values['category_elements'][$cpt]['name'] =
                        $this->getI18n($category['name']);
                    $values['category_elements'][$cpt]['image'] = $category['image'];
                    $values['category_elements'][$cpt]['description'] =
                        $this->getI18n($category['description']);
                    $values['category_elements'][$cpt]['shortDescription'] =
                        $this->getI18n($category['shortDescription']);
                    $link = $this->links->path->getLink('shop/showCategory/'.$element);
                    $link .= '?'.self::ARG_PRODUCTLISTTYPE.'='.$this->getProductListType();
                    $values['category_elements'][$cpt]['link'] = $link;
                    $values['category_elements'][$cpt]['pictos'][1]['link'] = $link;
                    $cpt++;
                }
            }
        }
        // We group the elements using the apropriate function, depending on $params
        if(!is_null($groupsSize)){
            $arranged = $values['category_elements'];
            if(!empty($fillWith)){
                $elementsToAdd = ($number - count($arranged));
                for($a = 0; $a < $elementsToAdd;$a++){
                    $arranged[]['empty'] = $fillWith;
                }
            }
            $groups = array_chunk($arranged,$groupsSize);

            foreach($groups as $id=>$group){
                $values['category_elements_groups'][$id]['elements'] = $group;
            }

        }

        $pagesNb = count($chunked);

        // We prepare the previous, next and direct links to other pages
        if($pagesNb > 1){ // There is more than one page
            // We remove the page argument in the url
            unset($this->links->path->parsed_url['parsed_query']['page']);
            $args = '';
            $separator = '';
            foreach($this->links->path->parsed_url['parsed_query'] as $argName=>$argValue){
                $args .= $separator.$argName.'='.$argValue;
                $separator = '&';
            }
            $destPage = $this->links->path->uri.'?'.$args;

            // Previous link
            if($page > 0){
                $values['pageNavigation']['previous'] = true;
                $values['pageNavigation']['previous_link'] = $destPage.'&page='.$page;
                $nav_pages = true;
            }
            // Next link
            if(($pagesNb - 1) > $page){
                $values['pageNavigation']['next'] = true;
                $values['pageNavigation']['next_link'] = $destPage.'&page='.($page + 2);
                $nav_pages = true;
            }
            // Direct links to other pages
            if($nav_pages){
                for($nav_pages_num = 1;$nav_pages_num <= $pagesNb; $nav_pages_num++){
                    $class = '';
                    if($nav_pages_num == $page + 1){
                        $class = 'bold';
                    }
                    $values['pages'][] = array(
                        'link' => $destPage.'&page='.($nav_pages_num),
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
    protected function getNavigator_list($elements,$page){
        $elementsByPage = 12;
        if(!is_array($elements) || empty($elements)){
            return $this->render('navigator_list_empty',array(),false,false);
        }
        if($page > 0){
            $previous = true;
        }
        if(count($elements)>($page + 1) * $elementsByPage){
            $next = true;
        }
        $pageActuelle = preg_replace(
            '`(\?page=[0-9]+)`','',$this->links->path->uri
        );
        if($page>0){
            $values['nav_previous']['image'] = '/images/builder/nav/model1_previous.png';
            $values['nav_previous']['link'] = $pageActuelle.'?page='.$page;
            $nav_pages = true;
        }
        if(count($elements)>=(($page + 1) * ($elementsByPage + 1))){
            $values['nav_next']['image'] = '/images/builder/nav/model1_next.png';
            $values['nav_next']['link'] = $pageActuelle.'?page='.($page + 2);
            $nav_pages = true;
        }
        if($nav_pages){
            for($nav_pages_num = 1;$nav_pages_num <= ceil(count($elements) / $elementsByPage); $nav_pages_num++){
                $class = '';
                if($nav_pages_num == $page + 1){
                    $class = 'bold';
                }
                $values['nav_pages'][] = array(
                    'link' => $pageActuelle.'?page='.($nav_pages_num),
                    'number' => $nav_pages_num,
                    'class' => $class
                );
            }
        }


        $elements = array_slice($elements, ($page * $elementsByPage), $elementsByPage);
        while($element = array_shift($elements)){
            if(is_array($element)){
                $values['list'][$cpt]['navigator_image'] = $element['image'];
                $values['list'][$cpt]['class'] = 'list_image';
                $values['list'][$cpt]['navigator_desc'] = $this->getI18n($element['shortDescription']);
                if($element['type'] == 'product'){
                    $link = $this->links->path->getLink('shop/showProduct/'.$element['id']);
                    $values['list'][$cpt]['navigator_link'] = $link;
                    $values['list'][$cpt]['navigator_ref'] = $element['ref'];
                    $values['list'][$cpt]['navigator_price'] = $element['price'];
                    $addToCartLink = $this->links->path->getLink('shop/addToCart/');
                    $addToCartLink .= '?product='.$element['id'];
                    $values['list'][$cpt]['pictos'][0]['image'] = '/images/shared/icons/picto_cart.png';
                    $values['list'][$cpt]['pictos'][0]['image_alt'] = 'picto_cart';
                    $values['list'][$cpt]['pictos'][0]['link'] = $addToCartLink;
                    $values['list'][$cpt]['pictos'][1]['image'] = '/images/shared/icons/picto_details.png';
                    $values['list'][$cpt]['pictos'][1]['image_alt'] = 'picto_details';
                    $values['list'][$cpt]['pictos'][1]['link'] = $link;
                }else{
                    $link = $this->links->path->getLink('shop/showCategory/'.$element['id']);
                    $values['list'][$cpt]['navigator_link'] = $link;
                    $values['list'][$cpt]['navigator_noprice'] = $link;
                    $values['list'][$cpt]['pictos'][0]['image'] = '/images/shared/icons/picto_details.png';
                    $values['list'][$cpt]['pictos'][0]['image_alt'] = 'picto_details';
                    $values['list'][$cpt]['pictos'][0]['link'] = $link;
                }
                $cpt++;
            }
        }

        return $this->render('navigator_list',$values,false,false);
    }

    /**
     * protected function getNavigator_grid
     *
     */
    protected function getNavigator_table($elements,$page){
        $elementsByPage = 5;
        if(!is_array($elements) || empty($elements)){
            return $this->render('navigator_list_empty',array(),false,false);
        }
        if($page > 0){
            $previous = true;
        }
        if(count($elements)>($page + 1) * $elementsByPage){
            $next = true;
        }
        if($page>0){
            $values['nav_previous']['image'] = '/images/builder/nav/model1_previous.png';
            $pageActuelle = preg_replace(
                '`(\?page=[0-9]+)`','',$this->links->path->uri
            );
            $values['nav_previous']['link'] = $pageActuelle.'?page='.($page - 1);
        }else{
            $values['nav_previous']['image'] = '/images/builder/nav/model1_noprevious.png';
        }
        if(count($elements)>=($page * ($elementsByPage + 1))){
            $values['nav_next']['image'] = '/images/builder/nav/model1_next.png';
            $pageActuelle = preg_replace('`(\?page=[0-9]+)`','',$this->links->path->uri);
            $values['nav_next']['link'] = $pageActuelle.'?page='.($page + 2);
        }else{
            $values['nav_next']['image'] = '/images/builder/nav/model1_nonext.png';
        }
        $elements = array_slice(
            $elements, ($page * $elementsByPage), $elementsByPage
        );

        while($element = array_shift($elements)){
            if(is_array($element)){
                $values['list'][$cpt]['navigator_image'] = $element['image'];
                $values['list'][$cpt]['class'] = 'list_image';
                $values['list'][$cpt]['navigator_desc'] = $this->getI18n($element['shortDescription']);
                if($element['type'] == 'product'){
                    $link = $this->links->path->getLink('shop/showProduct/'.$element['id']);
                    $values['list'][$cpt]['navigator_link'] = $link;
                    $values['list'][$cpt]['navigator_ref'] = $element['ref'];
                    $values['list'][$cpt]['navigator_price'] = $element['price'];
                    $addToCartLink = $this->links->path->getLink('shop/addToCart/');
                    $addToCartLink .= '?product='.$element['id'];
                    $values['list'][$cpt]['pictos'][0]['image'] = '/images/shared/icons/picto_cart.png';
                    $values['list'][$cpt]['pictos'][0]['image_alt'] = 'picto_cart';
                    $values['list'][$cpt]['pictos'][0]['link'] = $addToCartLink;
                    $values['list'][$cpt]['pictos'][1]['image'] = '/images/shared/icons/picto_details.png';
                    $values['list'][$cpt]['pictos'][1]['image_alt'] = 'picto_details';
                    $values['list'][$cpt]['pictos'][1]['link'] = $link;
                }else{
                    $link = $this->links->path->getLink('shop/showCategory/'.$element['id']);
                    $values['list'][$cpt]['navigator_link'] = $link;
                    $values['list'][$cpt]['navigator_noprice'] = $link;
                    $values['list'][$cpt]['pictos'][0]['image'] = '/images/shared/icons/picto_details.png';
                    $values['list'][$cpt]['pictos'][0]['image_alt'] = 'picto_details';
                    $values['list'][$cpt]['pictos'][0]['link'] = $link;
                }
                $cpt++;
            }
        }

        return $this->render('navigator_table',$values,false,false);
    }

    //////////////////////////////////////////////////////////////////////////////////////
    //                                  CART PART                                       //
    //////////////////////////////////////////////////////////////////////////////////////
    /**
     * public function addToCart
     *
     */
    public function addToCart(){
        $product = $_GET['product'];
        if(isset($_SESSION[__CLASS__]['cart'][$product]) && $_SESSION[__CLASS__]['cart'][$product] > 0){
            $_SESSION[__CLASS__]['cart'][$product]++;
        }else{
            $_SESSION[__CLASS__]['cart'][$product] = 1;
        }
        $this->links->path->redirect($this->shortClassName,'cart_show');
    }

    /**
     * public function cart_show
     *
     */
    public function cart_show(){
        $this->links->cache->disable();
        $this->links->html->setTitle($this->getI18n('cart_title'));
        if(is_array($_SESSION[__CLASS__]['cart']) && count($_SESSION[__CLASS__]['cart']) > 0){
            $total = 0;
            foreach($_SESSION[__CLASS__]['cart'] as $id=>$quantity){
                // We verify if we the product [still] exists
                if($this->productExists($id)){
                    include($this->productsFolder.$id.'/product.php');
                    if($quantity>0 && $product['stock']>0){
                        // We verify if we can sell the quantity asked
                        if($quantity > $product['stock']){
                            // The user asks for more than what we have in stock, so we
                            // can only sell all the stock
                            $quantity = $product['stock'];
                            $_SESSION[__CLASS__]['cart'][$id] = $quantity;
                            $_SESSION[__CLASS__]['stocknotsufficient'][] = $product['name'];
                        }
                        $values['contents'][] = array(
                            'image'=>$product['image'],
                            'name'=>$this->getI18n($product['name']),
                            'reference'=>$product['reference'],
                            'shortDescription'=>$this->getI18n(
                                $product['shortDescription']
                            ),
                            'stock'=>$product['stock'],
                            'quantity'=>$quantity,
                            'id'=>$id,
                            'price'=>$this->monney_format(
                                $product['price'],true,false
                            ),
                            'removeLink'=>$this->links->path->getLink(
                                'shop/cart_removeProduct/'
                            ).'?product='.$id,
                            'totalPrice'=>$this->monney_format(
                                $product['price'] * $quantity,true,false
                            ),
                            'link'=>$this->links->path->getLink(
                                'shop/showProduct/'.$id
                            )
                        );
                    }elseif($product['stock'] == 0){
                        // There is no stock anymore
                        $_SESSION[__CLASS__]['noMoreStock'][] = $product['name'];
                    }else{
                        // We wanted a quantity of 0, so we delete it
                        unset($_SESSION[__CLASS__]['cart'][$id]);
                    }
                    $total += $product['price'] * $quantity;
                }
            }
            if(is_array($_SESSION[__CLASS__]['noMoreStock'])){
                foreach($_SESSION[__CLASS__]['noMoreStock'] as $noMore){
                    $values['noMoreStock'][]['name'] = $this->getI18n($noMore);
                }
            }
            unset($_SESSION[__CLASS__]['noMoreStock']);
            if(is_array($_SESSION[__CLASS__]['stocknotsufficient'])){
                foreach($_SESSION[__CLASS__]['stocknotsufficient'] as $stocknotsufficient){
                    $values['stocknotsufficient'][]['name'] = $this->getI18n(
                        $stocknotsufficient
                    );
                }
            }
            unset($_SESSION[__CLASS__]['stocknotsufficient']);

            $values['message']['content'] = $_SESSION[__CLASS__]['cart_message'];
            $values['general']['action'] = $this->links->path->getLink('shop/cart_doAction/');
            if($this->taxes == self::TAXES_EXCLUDED){
                $values['total']['ht'] = $this->monney_format($total,true,false);
                $values['total']['ttc'] = $this->monney_format(
                    $total * (1 + $this->taxRate / 100),
                    true,
                    false
                );
            }else{
                $values['total']['ht'] = $this->monney_format(
                    $total / (1 + $this->taxRate / 100),
                    true,
                    false,
                    self::ROUND_TO_UPPER
                );
                $values['total']['ttc'] = $this->monney_format($total,true,false);
            }
        }

        if(count($values['contents']) == 0){
            $this->render('cart_empty');
            return true;
        }

        if($this->links->user->isConnected()){
            $values['user']['connected'] = true;
        }

        $this->render('cart_show',$values);
    }

    /**
     * public function cart_removeProduct
     *
     */
    public function cart_removeProduct(){
        $id = $_GET['product'];
        unset($_SESSION[__CLASS__]['cart'][$id]);
        $this->links->path->redirect($this->shortClassName,'cart_show');
    }

    /**
     * public function cart_doAction
     *
     */
    public function cart_doAction(){
        $this->links->cache->disable();

        if(isset($_POST['update_quantities_x'])){
            return $this->cart_updateQuantities();
        }
        if(isset($_POST['save_cart_x']) && $_POST['save_cart_x']!=0){
            return $this->cart_save();
        }
        return $this->cart_chooseShiper();
    }

    /**
     * protected function cart_save
     *
     */
    protected function cart_save(){
        if(!is_dir($this->cartsFolder)){
            mkdir($this->cartsFolder);
        }
        $user = $this->links->user->getUserId();
        $cartFile = $this->cartsFolder.$user.'.php';
        if(!file_exists($cartFile)){
            $save = true;
        }
        if($this->formSubmitted('cart_save_existing')){
            if($_POST['action'] == 'replace'){
                $save = true;
            }else{
                return $this->cart_updateQuantities();
            }
        }else{
            $this->cart_updateQuantities(false);
        }
        if($save){
            $values['form']['submitted'] = true;
            $this->links->helper->writeArrayInFile(
                $cartFile,
                'cart',
                $_SESSION[__CLASS__]['cart']
            );
        }else{
             $values['cart']['existing'] = true;
        }
        $this->render('cart_save', $values);
    }

    /**
     * Updates the quantity in the cart for every element
     * @param bool $redirect If true, redurects to the cart page
     * @return bool Always returns true
     */
    protected function cart_updateQuantities($redirect = true){
        if(is_array($_POST['change_quantity'])){
            foreach($_POST['change_quantity'] as $element=>$quantity){
                $_SESSION[__CLASS__]['cart'][$element] = $quantity;
            }
        }
        if($redirect){
            $this->links->path->redirect($this->shortClassName,'cart_show');
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
    protected function monney_format($number,$addCurrency = true,$showTaxSymbol = true,$roundTo = self::ROUND_TO_LOWER){
        $number = str_replace(',','.',$number);
        if($roundTo == self::ROUND_TO_NEARER){
            $number = round($number,$this->decimals);
        }else{
            $factor = pow(10,$this->decimals);
            if($roundTo == self::ROUND_TO_UPPER){
                $number = ceil($number*$factor)/$factor;
                }else{
                $number = floor($number*$factor)/$factor;
            }
        }
        if($showTaxSymbol && $this->showTaxSymbol){
            $taxSymbol = ' '.$this->taxes;
        }else{
            $taxSymbol = '';
        }
        if($addCurrency){
            // Depending on the i18n, the currency could be placed before or after
            // the value
            return $this->currencyBefore.number_format(
                $number,
                $this->decimals,
                $this->decSeparator,
                $this->thousSeparator
            ).$this->currencyAfter.$taxSymbol;
        }
        return number_format(
            $number,
            $this->decimals,
            $this->decSeparator,
            $this->thousSeparator
        );
    }

    protected function list_shipModes(){
        $supplyers = $this->getParam('shipping>supplyers',array());
        $supCpt = 0;
        if(is_array($supplyers)){
            foreach($supplyers as $id=>$supplyer){
                if(isset($supplyer['activated'])){
                    $values['shipping'][$id] = array(
                        'name'=>stripslashes($supplyer['name']),
                        'price'=>$supplyer['price'],
                        'logo'=>$supplyer['logo'],
                        'description'=>stripslashes($supplyer['description']),
                        'id'=>$id
                    );
                    $supCpt++;
                }
            }
        }
        if($this->getParam('shipping>comeTakeIt>activated',false)){
            if($this->getParam('shipping>comeTakeIt>price',0) != 0){
                $price = $this->getParam(
                    'shipping>comeTakeIt>price',
                    0
                );
                $values['comeTakeIt']['price'] = $price;
            }

            $lines = explode(
                "\n",
                $this->getParam('shipping>comeTakeIt>addresses','')."\n"
            );
            $open = false;
            $cpt = 0;
            $address = '';
            foreach($lines as $line){
                if(trim($line) == ''){
                    if($open){
                        $open = false;
                        $addresses[$cpt++] .= $address;
                        $address = '';
                        $separator = '';
                    }
                }else{
                    $open = true;
                    $address .= $separator.$line;
                    $separator = '<br />';
                }
            }
            $values['comeTakeIt']['addresses'] = $addresses;
        }

        return $values;
    }

    protected function shippers_formatForRendering(){
        $supplyers = $this->getParam('shipping>supplyers',array());
        $supCpt = 0;
        if(is_array($supplyers)){
            foreach($supplyers as $id=>$supplyer){
                if(isset($supplyer['activated'])){
                    if($supplyer['price'] > 0){
                        $price = $this->monney_format($supplyer['price']);
                    }else{
                        $price = $this->getI18n('free');
                    }
                    $values['shipModes'][] = array(
                        'name'=>stripslashes($supplyer['name']),
                        'price'=>$price,
                        'logo'=>$supplyer['logo'],
                        'description'=>stripslashes($supplyer['description']),
                        'id'=>$id
                    );
                    $supCpt++;
                }
            }
        }
        if($this->getParam('shipping>comeTakeIt>activated',false)){
            if($supCpt > 0){
                $values['moreThanOne']['shipModes'] = true;
            }
            $values['comeTakeIt']['activated'] = 'checked';
            if($this->getParam('shipping>comeTakeIt>price',0) != 0){
                $price = $this->monney_format(
                    $this->getParam(
                        'shipping>comeTakeIt>price',
                        0
                    )
                );
                $values['comeTakeIt']['price'] = $price;
            }

            $lines = explode(
                "\n",
                $this->getParam('shipping>comeTakeIt>addresses','')."\n"
            );
            $open = false;
            $cpt = 0;
            $address = '';
            foreach($lines as $line){
                if(trim($line) == ''){
                    if($open){
                        $open = false;
                        $addresses[$cpt++] .= $address;
                        $address = '';
                        $separator = '';
                    }
                }else{
                    $open = true;
                    $address .= $separator.$line;
                    $separator = '<br />';
                }
            }
            if(count($addresses) == 1){
                $values['comeTakeIt_singleAddress'] = array(
                    'id'=>0,
                    'address' => str_replace('<br />',' - ',$addresses[0]),
                    'addressMultiline' => $addresses[0],
                );
            }else{
                foreach($addresses as $id=>$address){
                    $values['comeTakeIt_addresses'][] = array(
                        'id'=>$id,
                        'address' => str_replace('<br />',' - ',$address),
                        'addressMultiline' => $address,
                    );
                }
            }
        }elseif($supCpt > 1){
            $values['moreThanOne']['shipModes'] = true;
        }else{
            $values['shipMode'] = $values['shipModes'][0];
        }
        return $values;
    }

    protected function cart_chooseShiper(){
        $this->links->cache->disable();
        if(!$this->links->user->isConnected()){
            $this->links->html->setTitle($this->getI18n('command_requires_connection'));
            $ret = $this->links->user->connect(true,true);
            if(!$ret){
                return true;
            }
        }

        if($this->formSubmitted('cart_chooseShiper') && $_POST['shipMode']!=''){
            $shipModes = $this->list_shipModes();

            $_SESSION[__CLASS__]['shipping']['selected'] = true;
            if($_POST['shipMode'] == 1000){
                $_SESSION[__CLASS__]['shipping']['price'] = $shipModes['comeTakeIt']['price'];
                $_SESSION[__CLASS__]['shipping']['type'] = 'comeTakeIt';
                $_SESSION[__CLASS__]['shipping']['address'] =
                    $shipModes['comeTakeIt']['addresses'][$_POST['comeAndTakeIt_address']];
            }else{
                $_SESSION[__CLASS__]['shipping']['price'] =
                    $shipModes['shipping'][$_POST['shipMode']]['price'];
                $_SESSION[__CLASS__]['shipping']['type'] = 'shipper';
                $_SESSION[__CLASS__]['shipping']['shipper'] =
                    $shipModes['shipping'][$_POST['shipMode']]['name'];
            }
            return $this->cart_submitCommand();
        }

        if($_SESSION[__CLASS__]['shipping']['selected'] && $_GET['action'] != 'change_shipMode'){
            $this->cart_updateQuantities(false);
            return $this->cart_submitCommand();
        }

        $this->cart_updateQuantities(false);
        $this->links->html->setTitle($this->getI18n('ship_chooseShipper_title'));

        $values = $this->shippers_formatForRendering();

        $rules = $this->getParam('shipping>discounts',array());
        $values['form']['action'] = $this->translatePageToUri(
            $this->shortClassName.'/cart_doAction/'
        );
        $discounts = $this->discounts_formatForRendering();
        if(is_array($discounts)){
            $values = array_merge($values,$discounts);
        }
        $this->render('ship_chooseShipper', $values);
        return true;
    }

    /**
     * protected function cart_submitCommand
     *
     */
    protected function cart_submitCommand(){
        $this->links->cache->disable();
        $this->links->html->setTitle($this->getI18n('command_confirm_title'));
        if($this->formSubmitted('command_confirm')){
            $sendOk = true;
            $name = trim($_POST['name']);
            $address = trim($_POST['address']);
            $zip = trim($_POST['zip']);
            $city = trim($_POST['city']);
            if($name != '' && $zip != '' && $city != ''){
                $_SESSION[__CLASS__]['billing_address'] = array(
                    'name' => $name,
                    'address' => $address,
                    'zip' => $zip,
                    'city' => $city
                );
                if($_POST['shipTo'] != 'comeTakeIt' && $_POST['shipTo'] != 'billing'){
                    $name = trim($_POST['ship_name']);
                    $address = trim($_POST['ship_address']);
                    $zip = trim($_POST['ship_zip']);
                    $city = trim($_POST['ship_city']);
                    if($name != '' && $zip != '' && $city != ''){
                        $_SESSION[__CLASS__]['shipping_address'] = array(
                            'name' => $name,
                            'address' => $address,
                            'zip' => $zip,
                            'city' => $city
                        );
                    }else{
                        $values['error']['noShippingAddressnoBillingAddress'] = true;
                        $sendOk = false;
                    }
                }elseif($_POST['shipTo'] != 'comeTakeIt'){
                    $_SESSION[__CLASS__]['shipping_address'] = $_SESSION[__CLASS__]['billing_address'];
                }
            }else{
                $values['error']['noBillingAddress'] = true;
                $sendOk = false;
            }
            // We also have to verify if a payment mode has been selected
            if(!isset($_POST['paymentMode'])){
                $values['error']['noPaymentMode'] = true;
                $sendOk = false;
            }else{
                $paymentMode = $this->getParam('payment>supplyers>'.$_POST['paymentMode'], false);
                if($paymentMode && isset($paymentMode['activated'])){
                    $_SESSION[__CLASS__]['paymentMode'] = $paymentMode;
                }else{
                    // We do as if no payment mode was selected, but in fact,
                    // someone may have tried to hack by selected a value that
                    // shouldn't be selectable
                    $values['error']['noPaymentMode'] = true;
                    $sendOk = false;
                }
            }

            if($sendOk){
                return $this->sendCommand();
            }
        }
        if(is_array($_SESSION[__CLASS__]['cart']) && count($_SESSION[__CLASS__]['cart']) > 0){
            $total = 0;
            foreach($_SESSION[__CLASS__]['cart'] as $id=>$quantity){
                // We verify if we the product [still] exists
                if($this->productExists($id)){
                    include($this->productsFolder.$id.'/product.php');
                    if($quantity>0 && $product['stock']>0){
                        // We verify if we can sell the quantity asked
                        if($quantity > $product['stock']){
                            // The user asks for more than what we have in stock, so we
                            // can only sell all the stock
                            $quantity = $product['stock'];
                            $_SESSION[__CLASS__]['cart'][$id] = $quantity;
                            $_SESSION[__CLASS__]['stocknotsufficient'][] = $product['name'];
                        }
                        $values['contents'][] = array(
                            'name'=>$this->getI18n($product['name']),
                            'reference'=>$product['reference'],
                            'quantity'=>$quantity,
                            'price'=>$this->monney_format(
                                $product['price'],true,false
                            ),
                            'totalPrice'=>$this->monney_format(
                                $product['price'] * $quantity,true,false
                            )
                        );
                    }elseif($product['stock'] == 0){
                        // There is no stock anymore
                        $_SESSION[__CLASS__]['noMoreStock'][] = $product['name'];
                    }else{
                        // We wanted a quantity of 0, so we delete it
                        unset($_SESSION[__CLASS__]['cart'][$id]);
                    }
                    $total += $product['price'] * $quantity;
                }
            }


            if($this->getParam('shipping>activated',true)){
                $price = $_SESSION[__CLASS__]['shipping']['price'];
                $this->ship_getPrice($total, $price, $explanation);

                if($_SESSION[__CLASS__]['shipping']['type'] == 'comeTakeIt'){
                    $values['contents'][] = array(
                        'name'=>$this->getI18n('command_comeTakeItTitle'),
                        'reference'=>$_SESSION[__CLASS__]['shipping']['address'].$explanation,
                        'quantity'=>1,
                        'price'=>$this->monney_format($price,true,false),
                        'totalPrice'=>$this->monney_format($price,true,false)
                    );
                    $values['shipping']['comeTakeIt'] = true;
                }else{
                    $values['contents'][] = array(
                        'name'=>$this->getI18n('command_shippingTitle'),
                        'reference'=>$_SESSION[__CLASS__]['shipping']['shipper'].$explanation,
                        'quantity'=>1,
                        'price'=>$this->monney_format($price,true,false),
                        'totalPrice'=>$this->monney_format($price,true,false)
                    );
                    $values['shipping']['toCustomer'] = true;
                }
                $total += $price;
            }

            if(is_array($_SESSION[__CLASS__]['noMoreStock'])){
                foreach($_SESSION[__CLASS__]['noMoreStock'] as $noMore){
                    $values['noMoreStock'][]['name'] = $this->getI18n($noMore);
                }
            }
            unset($_SESSION[__CLASS__]['noMoreStock']);
            if(is_array($_SESSION[__CLASS__]['stocknotsufficient'])){
                foreach($_SESSION[__CLASS__]['stocknotsufficient'] as $stocknotsufficient){
                    $values['stocknotsufficient'][]['name'] = $this->getI18n(
                        $stocknotsufficient
                    );
                }
            }
            unset($_SESSION[__CLASS__]['stocknotsufficient']);

            $values['message']['content'] = $_SESSION[__CLASS__]['cart_message'];
            $values['general']['action'] = $this->links->path->getLink(
                'shop/cart_doAction/'
            );
            if($this->taxes == self::TAXES_EXCLUDED){
                $values['total']['ht'] = $this->monney_format($total,true,false);
                $values['total']['ttc'] = $this->monney_format(
                    $total * (1 + $this->taxRate / 100),
                    true,
                    false
                );
            }else{
                // We round to upper for the client not to think his TTC is too
                //big for that HT
                $values['total']['ht'] = $this->monney_format(
                    $total / (1 + $this->taxRate / 100),
                    true,
                    false,
                    self::ROUND_TO_UPPER
                );
                $values['total']['ttc'] = $this->monney_format($total,true,false);
            }
        }

        if(count($values['contents']) == 0){
            $this->render('cart_empty');
            return true;
        }

        $values['billing']['name'] = $this->links->user->get('lastName').' '.$this->links->user->get('name');

        // Payment modes
        $paymentModes = $this->getParam('payment>supplyers', array());
        // Only keeps payment modes that are available
        foreach($paymentModes as $id=>$paymentMode){
            if(!isset($paymentMode['activated'])){
                unset($paymentModes[$id]);
            }elseif(!isset($firstId)){
                $firstId = $id;
            }
        }
        if(count($paymentModes) > 1){
            $values['payment']['moreThanOneMode'] = true;
            foreach($paymentModes as $id=>$paymentMode){
                $values['paymentModes'][$id] = $paymentMode;
                $values['paymentModes'][$id]['id'] = $id;
            }
        }elseif(count($paymentModes) == 1){
            $values['paymentMode'] = $paymentModes[$firstId];
            $values['paymentMode']['id'] = $firstId;
        }

        $this->render('command_confirm',$values);
    }

    protected function ship_getPrice($total,&$price,&$explanation = ''){
        $possibleDiscounts = array(0);
        if(!($price > 0)){
            return true;
        }else{
            $discounts['rulePrice0'] = $this->getParam('shipping>discounts>rulePrice0','');
            $discounts['rulediscount0'] = $this->getParam('shipping>discounts>rulediscount0','');
            $discounts['rulePrice1'] = $this->getParam('shipping>discounts>rulePrice1','');
            $discounts['rulediscount1'] = $this->getParam('shipping>discounts>rulediscount1','');
            $discounts['rulePrice2'] = $this->getParam('shipping>discounts>rulePrice2','');
            $discounts['rulediscount2'] = $this->getParam('shipping>discounts>rulediscount2','');
            for($a=0;$a<3;$a++){
                $minimumPrice = trim($discounts['rulePrice'.$a]);
                if($total >= $minimumPrice){
                    $discount = trim($discounts['rulediscount'.$a]);
                    if(strpos($discount,'%') !== false){
                        $possibleDiscounts[] = ceil($price * trim(str_replace('%','',$discount))) / 100;
                    }else{
                        $possibleDiscounts[] = $discount;
                    }
                }
            }
        }

        $discount = max($possibleDiscounts);
        if($discount > 0){
            $oldPrice = $price;
            $price -= $discount;
            if($price < 0){
                $discount = $price;
                $price = 0;
            }
            $explanation = '<br />Réduction appliquée en fonction du montant de la facture : ';
            $explanation .= $this->monney_format($oldPrice,false,false).
                ' - '.$this->monney_format($discount,false,false).
                ' = '.$this->monney_format($price);
        }
        return true;
    }

    /**
     * public function sendCommand
     *
     */
    public function sendCommand(){
        if(!$this->links->user->isConnected()){
            $this->cart_submitCommand();
            return true;
        }

        if(is_array($_SESSION[__CLASS__]['cart']) && count($_SESSION[__CLASS__]['cart']) > 0){
            $datas['titles'] = array(
                $this->getI18n('bill_table_ref'),
                $this->getI18n('bill_table_product'),
                $this->getI18n('bill_table_price').' '.$this->taxes,
                $this->getI18n('bill_table_quantity'),
                $this->getI18n('bill_table_totalPrice').' '.$this->taxes
            );
            $total = 0;
            foreach($_SESSION[__CLASS__]['cart'] as $id=>$quantity){
                // We verify if we the product [still] exists
                if($this->productExists($id)){
                    include($this->productsFolder.$id.'/product.php');
                    if($quantity>0 && $product['stock']>0){
                        // We verify if we can sell the quantity asked
                        if($quantity > $product['stock']){
                            // The user asks for more than what we have in stock, so we
                            // can only sell all the stock
                            $quantity = $product['stock'];
                            $_SESSION[__CLASS__]['cart'][$id] = $quantity;
                            $_SESSION[__CLASS__]['stocknotsufficient'][] = $product['name'];
                        }
                        $datas['elements'][] = array(
                            $product['reference'],
                            $this->getI18n($product['name']),
                            $this->monney_format($product['price'],false,false),
                            $quantity,
                            $this->monney_format($product['price'] * $quantity,false,false)
                        );
                    }elseif($product['stock'] == 0){
                        // There is no stock anymore
                        $_SESSION[__CLASS__]['noMoreStock'][] = $product['name'];
                    }else{
                        // We wanted a quantity of 0, so we delete it
                        unset($_SESSION[__CLASS__]['cart'][$id]);
                    }
                    $total += $product['price'] * $quantity;
                }
            }
            if(is_array($_SESSION[__CLASS__]['noMoreStock'])){
                foreach($_SESSION[__CLASS__]['noMoreStock'] as $noMore){
                    $values['noMoreStock'][]['name'] = $this->getI18n($noMore);
                }
            }
            unset($_SESSION[__CLASS__]['noMoreStock']);
            if(is_array($_SESSION[__CLASS__]['stocknotsufficient'])){
                foreach($_SESSION[__CLASS__]['stocknotsufficient'] as $stocknotsufficient){
                    $values['stocknotsufficient'][]['name'] = $this->getI18n(
                        $stocknotsufficient
                    );
                }
            }
            unset($_SESSION[__CLASS__]['stocknotsufficient']);

            $datas['customerService'] = explode(
                "\r\n",
                $this->getI18n(self::I18N_BILLCUSTOMERSERVICE)
            );
            $datas['billingAddressIntro'] = $this->getI18n('bill_AddressIntro');
            $address = str_replace(
                array("\r\n","\r"),
                "\n",
                $_SESSION[__CLASS__]['billing_address']['address']
            );
            $datas['billingAddress'] = array_merge(
                array($_SESSION[__CLASS__]['billing_address']['name']),
                explode("\n",$address),
                array(
                    $_SESSION[__CLASS__]['billing_address']['zip'].' '.
                        $_SESSION[__CLASS__]['billing_address']['city']
                )
            );

            if($this->getParam('shipping>activated',true)){
                $price = $_SESSION[__CLASS__]['shipping']['price'];
                $this->ship_getPrice($total, $price, $explanation);
                $datas['shippingAddressIntro'] = $this->getI18n(
                    'bill_comeTakeItTextIntro'
                );

                if($_SESSION[__CLASS__]['shipping']['type'] == 'comeTakeIt'){
                    $datas['elements'][] = array(
                        $this->getI18n('bill_shippingTitle'),
                        $this->getI18n('bill_comeTakeItText'),
                        $this->monney_format($price,false,false),
                        1,
                        $this->monney_format($price,false,false)
                    );
                    $address = preg_replace(
                        "`([\r\n]+)`",
                        '<br />',
                        $_SESSION[__CLASS__]['shipping']['address']
                    );
                    $address = preg_replace(
                        '`(<br */>)+`',
                        '<br />',
                        $address
                    );
                    $datas['shippingAddress'] = explode(
                        '<br />',
                        $address
                    );
                }else{
                    $datas['elements'][] = array(
                        $this->getI18n('bill_shippingTitle'),
                        $_SESSION[__CLASS__]['shipping']['shipper'],
                        $this->monney_format($price,false,false),
                        1,
                        $this->monney_format($price,false,false)
                    );
                    $datas['shippingAddress'] = $_SESSION[__CLASS__]['shipping_address']['name'];
                    $address = str_replace(
                        array("\r\n","\r"),
                        "\n",
                        $_SESSION[__CLASS__]['shipping_address']['address']
                    );
                    $datas['shippingAddress'] = array_merge(
                        array($_SESSION[__CLASS__]['shipping_address']['name']),
                        explode("\n",$address),
                        array(
                            $_SESSION[__CLASS__]['shipping_address']['zip'].' '.
                                $_SESSION[__CLASS__]['shipping_address']['city']
                        )
                    );
                }
                $total += $price;
            }

            if($this->taxes == self::TAXES_EXCLUDED){
                $datas['totalHT'] = $this->monney_format($total,true,false);
                $datas['totalTTC'] = $this->monney_format(
                    $total * (1 + $this->taxRate / 100),
                    true,
                    false
                );
            }else{
                // We round to upper for the client not to think his TTC is too
                //big for that HT
                $datas['totalHT'] = $this->monney_format(
                    $total / (1 + $this->taxRate / 100),
                    true,
                    false,
                    self::ROUND_TO_UPPER
                );
                $datas['totalTTC'] = $this->monney_format($total,true,false);
            }
        }

        // Payment modes
        $paymentModeId = $_SESSION[__CLASS__]['paymentMode'];
        $datas['paymentMode']['name'] = $paymentModeId['name'];

        if(count($datas['elements']) == 0){
            $this->render('cart_empty');
            return true;
        }
        $user = $this->links->user->getData();

        $datas['client'] = array(
            'id'=>$user['id'],
            'name'=>$user['lastName'].' '.strtoupper($user['name']),
            'address'=>'N° client : '.$user['id']."\n".$user['phone']."\n".$user['mail'],
        );

        $datas['seller'] = array(
            'name'=>$this->getParam('command>companyName'),
            'address'=>$this->getParam('command>companyAddress'),
        );
        $datas['author'] = 'Websailors pour '.$datas['seller']['name'];
        $datas['totalHTName'] = 'Total HT : ';
        $datas['totalTTCName'] = 'Total TTC : ';

        $datas['logo'] = $this->getParam('command>logo');
        $datas['footer'] = $this->getI18n(self::I18N_BILLFOOTER);
        $datas['headLine'] = $this->getI18n(self::I18N_BILLHEADLINE);

        $billColor = $this->getParam('billColor',0);
        $datas['fillColor'] = $this->getParam('billColors>'.$billColor);

        $fileNames = SH_SITE_FOLDER.__CLASS__.'/commands/';
        $fileNames .= date('y').'/'.date('m').'/'.date('d').'/'.date('H-i-s').'-';
        // Saves it to the file
        //The commands have a unic number which is incremented for each new command
        $cpt = 0;
        //We get the first id available by checking the file to create
        while(file_exists($fileNames.$cpt.'.php')){
            $cpt++;
        }
        $fileNames .= $cpt;

        $commandListFile = SH_SITE_FOLDER.__CLASS__.'/commands/list.php';
        if(file_exists($commandListFile)){
            include($commandListFile);
            $newId = max(array_keys($commandList)) + 1;
        }else{
            $commandList = array();
            $newId = 1000;
        }
        $commandList[$newId] = str_replace(
            SH_SITE_FOLDER.__CLASS__.'/commands/',
            '',
            $fileNames
        );

        $this->links->helper->writeArrayInFile(
            $commandListFile,'commandList',$commandList
        );

        $datas['billId'] = $newId;

        $datas['title'] = 'Facture n°'.$newId;
        $datas['subject'] = 'Facture du '.date('d/m/Y').' pour '.$datas['client']['name'];

        $this->links->helper->createDir(dirname($fileNames));
        $this->links->helper->writeArrayInFile(
            $fileNames.'.php','command',$datas,false
        );

        $pdf = $this->links->pdf;
        $pdfFile = $pdf->createBill($datas, $fileNames.'.pdf');

        //Creating the content of the email
        $values['mail']['date'] = date('d/m/Y H:i:s');
        $values['mail']['siteName'] = 'http://'.$this->links->path->getDomain();
        $content = $this->render('command_mail',$values,false,false);
        $contentSender = $this->render('command_mailSender',$values,false,false);
        

        // Creating and sending the email itself
        $mailer = $this->links->mailer->get();
        $mail = $mailer->em_create();
        $address = $user['mail'];


        $mailer->em_addSubject(
            $mail,
            'http://'.$this->links->path->getDomain().' - Confirmation de commande'
        );
        $mailer->em_addContent($mail,$content);
        $mailer->em_attach(
            $mail,
            $pdfFile,
            'command_'.date('d-m-Y_His').'.pdf'
        );

        if(!$mailer->em_send($mail,array(array($address)))){
            // Error sending the email
            echo 'Erreur dans l\'envoi du mail de validation...';
        }
        $mails = explode("\n",$this->getParam('command_mail'));
        if(is_array($mails)){
            $mailer->em_addContent($mail,$contentSender);
            foreach($mails as $oneMail){
                $list[][0] = $oneMail;
            }
            $mailer->em_send($mail,$list);
        }

        // Updates stock
        foreach($_SESSION[__CLASS__]['cart'] as $id=>$quantity){
            $this->changeQuantity($id,$quantity,'-');
        }

        unset($_SESSION[__CLASS__]['cart']);

        $values2['command']['pdf'] = $pdfFile;
        $this->render('command_mailSent',$values2);
    }

    public function showCommands(){
        $this->onlyAdmin();

    }
}