<?php
if(!defined('SH_MARKER')){
    header('location: directCallForbidden.php');
}

$command = array (
  'titles' => 
  array (
    0 => 'Référence',
    1 => 'Produit',
    2 => 'PU TTC',
    3 => 'Qté',
    4 => 'Total TTC',
  ),
  'elements' => 
  array (
    0 => 
    array (
      0 => 'P782-02-6',
      1 => 'DiamondMax 1000',
      2 => '53,00',
      3 => '1',
      4 => '53,00',
    ),
    1 => 
    array (
      0 => 'Livraison',
      1 => 'UPS',
      2 => '16,55',
      3 => 1,
      4 => '16,55',
    ),
  ),
  'billingAddressIntro' => 'Adresse de facturation :',
  'billingAddress' => 
  array (
    'name' => 'Brice Parent',
    'address' => '62 av Joannon
Les Palombes',
    'zip' => '13540',
    'city' => 'Puyricard',
  ),
  'shippingAddressIntro' => 'Adresse de livraison :',
  'shippingAddress' => 
  array (
    'name' => 'Brice Parent',
    'address' => '62 av Joannon
Les Palombes',
    'zip' => '13540',
    'city' => 'Puyricard',
  ),
  'totalHT' => '58,16€',
  'totalTTC' => '69,55€',
  'client' => 
  array (
    'name' => 'Parent Brice',
    'address' => 'Téléphone : 0442921737
E-mail : briceparent@free.fr',
  ),
  'seller' => 
  array (
    'name' => 'Websailors',
    'address' => '62, av Joannon
Les Palombes
13540 Puyricard',
  ),
  'author' => 'Websailors pour Websailors',
  'totalHTName' => 'Total HT : ',
  'totalTTCName' => 'Total TTC : ',
  'logo' => '/sites/websailors/sh_images/site/banner.jpg',
  'footer' => 'Websailors est une SARL SCOP au capital variable de 1000€ - Siège : 62 av. Joannon - Les Palombes - 13540 Puyricard
Tel: +33 6 81 21 05 56 - facturation@websailors.fr - Siren : 512310814 - RCS d\'Aix-en-Provence',
  'headLine' => 'Madame, Monsieur,
par la présente nous vous joignons la facture réalisée conformément à nos conditions générales de vente:',
  'fillColor' => 
  array (
    0 => 200,
    1 => 200,
    2 => 255,
  ),
  'billId' => 1018,
  'title' => 'Facture n°1018',
  'subject' => 'Facture du 18/09/2009 pour Parent Brice',
);