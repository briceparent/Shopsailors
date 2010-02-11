<?php
/**
  * This file was generated automatically by the Shopsailors engine.
 **/

if(!defined('SH_MARKER')){
    header('location: directCallForbidden.php');
}

$this->values = array (
  'enabled' => true,
  'activateShop' => true,
  'index' => 'Accueil',
  'billBottomText' => '',
  'activateCart' => true,
  'mailCommand' => 'briceparent@free.fr',
  'showQuantity' => false,
  'taxRate' => '19.6',
  'taxes' => 'TTC',
  'showTaxSymbol' => false,
  'currency' => 'Euro',
  'monney_format' => '123 456,78',
  'command' => 
  array (
    'footer' => 'Voila le bas',
    'companyName' => 'Websailors',
    'companyAddress' => '62, av Joannon
Les Palombes
13540 Puyricard',
    'headLine' => 'Voici votre facture',
    'logo' => '/sites/websailors/sh_images/site/banner.jpg',
  ),
  'hideNullQuantityProducts' => true,
  'shipping' => 
  array (
    'activated' => true,
    'showExpeditionInLegacy' => true,
    'supplyers' => 
    array (
      2 => 
      array (
        'name' => 'Coliposte express',
        'logo' => '/sites/websailors/sh_images/shop/shippers/La_Poste.png',
        'price' => '12.70',
        'description' => 'Comme coliposte, mais en seulement 5 jours',
        'activated' => 'on',
      ),
      3 => 
      array (
        'name' => 'UPS',
        'logo' => '/sites/websailors/sh_images/shop/shippers/UPS.png',
        'price' => '21.55',
        'description' => 'Ben c\\\'est le service UPS normal, en 48h chrono.',
        'activated' => 'on',
      ),
    ),
    'comeTakeIt' => 
    array (
      'activated' => true,
      'addresses' => '62 av Joannon
Les Palombes
13540 Puyricard

6 rue St Pons
13290 Les Milles',
      'price' => '0',
    ),
    'discounts' => 
    array (
      'rulePrice0' => '50',
      'rulediscount0' => '5',
      'rulePrice1' => '80',
      'rulediscount1' => '50%',
      'rulePrice2' => '150',
      'rulediscount2' => '100%',
    ),
  ),
  'command_mail' => 'brice@websailors.fr',
  'billColor' => '4',
  'payment' => 
  array (
    'showPaymentInLegacy' => true,
    'supplyers' => 
    array (
      0 => 
      array (
        'logo' => '/sites/websailors/sh_images/shop/payment/Paypal.png',
        'name' => 'Paypal',
        'description' => 'Le paiement sécurisé par Paypal',
        'activated' => 'on',
      ),
      1 => 
      array (
        'logo' => '/sites/websailors/sh_images/shop/payment/La_Poste.png',
        'name' => 'La Banque Postale',
        'description' => 'Le service sécurisé de paiement en ligne de la banque postale',
        'activated' => 'on',
      ),
      2 => 
      array (
        'logo' => '/sites/websailors/sh_images/shop/payment/Cheque.png',
        'name' => 'Chèque bancaire',
        'description' => 'Si ce mode de paiement est choisi, les articles sont réservés dès la commande, mais l\'envoi n\'est effectué qu\'à récéption du chèque de paiement',
        'activated' => 'on',
      ),
      3 => 
      array (
        'logo' => '/sites/websailors/sh_images/shop/payment/Paybox.png',
        'name' => 'Paybox',
        'description' => 'Paybox',
        'activated' => 'on',
      ),
    ),
  ),
  'customProperties' => 
  array (
    1 => 
    array (
      'active' => true,
      'name' => 935,
      'type' => 'list',
      'list' => 936,
    ),
    2 => 
    array (
      'active' => true,
      'name' => 937,
      'type' => 'text',
      'list' => 0,
    ),
    3 => 
    array (
      'active' => true,
      'name' => 938,
      'type' => 'list',
      'list' => 939,
    ),
    4 => 
    array (
      'active' => true,
      'name' => 940,
      'type' => 'i18nText',
      'list' => 0,
    ),
  ),
);