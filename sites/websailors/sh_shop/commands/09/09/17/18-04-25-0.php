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
      0 => 'P1105-02-20',
      1 => 'DiamondMax 500',
      2 => '28,00',
      3 => '1',
      4 => '28,00',
    ),
    1 => 
    array (
      0 => 'Livraison',
      1 => 'UPS',
      2 => '21,55',
      3 => 1,
      4 => '21,55',
    ),
  ),
  'shippingAddressIntro' => 'Adresse de livraison :',
  'shippingAddress' => '',
  'totalHT' => '41,43€',
  'totalTTC' => '49,55€',
  'client' => 
  array (
    'name' => 'Gauci Sylvain',
    'address' => 'Téléphone : 
E-mail : sylvaingauci@hotmail.fr',
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
  'billId' => 1012,
  'title' => 'Facture n°1012',
  'subject' => 'Facture du 17/09/2009 pour Gauci Sylvain',
);