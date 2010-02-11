<?php
/**
  * Copyright Shopsailors (2009)
  *
  * briceparent@free.fr
  *
  * This file is a part of a computer program whose purpose is to create,
  * administrate and use a shop over the web.
  *
  * This software is governed by the CeCILL license under French law and
  * abiding by the rules of distribution of free software.  You can  use,
  * modify and/ or redistribute the software under the terms of the CeCILL
  * license as circulated by CEA, CNRS and INRIA at the following URL
  * "http://www.cecill.info".
  *
  * As a counterpart to the access to the source code and  rights to copy,
  * modify and redistribute granted by the license, users are provided only
  * with a limited warranty  and the software's author,  the holder of the
  * economic rights,  and the successive licensors  have only  limited
  * liability.
  *
  * In this respect, the user's attention is drawn to the risks associated
  * with loading,  using,  modifying and/or developing or reproducing the
  * software by the user in light of its specific status of free software,
  * that may mean  that it is complicated to manipulate,  and  that  also
  * therefore means  that it is reserved for developers  and  experienced
  * professionals having in-depth computer knowledge. Users are therefore
  * encouraged to load and test the software's suitability as regards their
  * requirements in conditions enabling the security of their systems and/or
  * data to be ensured and,  more generally, to use and operate it in the
  * same conditions as regards security.
  *
  * The fact that you are presently reading this means that you have had
  * knowledge of the CeCILL license and that you accept its terms.
 **/

/**
  * This file was generated automatically by the Shopsailors engine.
 **/
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
      0 => 'P218-02-15',
      1 => 'Barracuda 120',
      2 => '34,00',
      3 => '2',
      4 => '68,00',
    ),
    1 => 
    array (
      0 => 'nlnln',
      1 => 'Cinébox 400',
      2 => '2 323,00',
      3 => '3',
      4 => '6 969,00',
    ),
    2 => 
    array (
      0 => 'Joli90283',
      1 => 'Joliproduit',
      2 => '23 000,00',
      3 => '1',
      4 => '23 000,00',
    ),
    3 => 
    array (
      0 => 'ks cdksq',
      1 => 'Maxtor 330SE',
      2 => '27 392,00',
      3 => '1',
      4 => '27 392,00',
    ),
    4 => 
    array (
      0 => 'knknkn',
      1 => 'Son nom',
      2 => '8,00',
      3 => '1',
      4 => '8,00',
    ),
    5 => 
    array (
      0 => 'MAX-M120-230204-3',
      1 => 'Disque M120',
      2 => '175,00',
      3 => '1',
      4 => '175,00',
    ),
    6 => 
    array (
      0 => 'vhvhvhv',
      1 => 'vhhvvhvcdcd',
      2 => '0,00',
      3 => '1',
      4 => '0,00',
    ),
    7 => 
    array (
      0 => 'MAX-F230-23932-Z',
      1 => 'Disque F230',
      2 => '160,00',
      3 => '1',
      4 => '160,00',
    ),
    8 => 
    array (
      0 => 'P328-02-27',
      1 => 'CineBox 500',
      2 => '54,00',
      3 => '1',
      4 => '54,00',
    ),
    9 => 
    array (
      0 => 'P475-02-26',
      1 => 'CineBox 250',
      2 => '98,00',
      3 => '1',
      4 => '98,00',
    ),
    10 => 
    array (
      0 => 'P756-02-29',
      1 => 'CineBox 500',
      2 => '135,00',
      3 => '1',
      4 => '135,00',
    ),
    11 => 
    array (
      0 => 'P425-02-28',
      1 => 'CineBox 250',
      2 => '106,00',
      3 => '1',
      4 => '106,00',
    ),
    12 => 
    array (
      0 => '321',
      1 => 'Cinébox 2000',
      2 => '321,00',
      3 => '1',
      4 => '321,00',
    ),
    13 => 
    array (
      0 => '16f5d4svdf',
      1 => 'Cinébox 2200',
      2 => '54,00',
      3 => '1',
      4 => '54,00',
    ),
    14 => 
    array (
      0 => '132a-54x',
      1 => 'Machin chose',
      2 => '654,12',
      3 => '1',
      4 => '654,00',
    ),
    15 => 
    array (
      0 => '54',
      1 => ' hfg hfd ghdfg',
      2 => '45,00',
      3 => '1',
      4 => '45,00',
    ),
    16 => 
    array (
      0 => '132',
      1 => 'yuj uyj yu',
      2 => '654,00',
      3 => '1',
      4 => '654,00',
    ),
    17 => 
    array (
      0 => '132',
      1 => 'sqdsdcqsq fsdqfsdf',
      2 => '654,00',
      3 => '1',
      4 => '654,00',
    ),
    18 => 
    array (
      0 => 'P854-02-4',
      1 => 'Disque DiamondMax 250',
      2 => '139,00',
      3 => '1',
      4 => '139,00',
    ),
    19 => 
    array (
      0 => 'Livraison',
      1 => 'UPS',
      2 => '0,00',
      3 => 1,
      4 => '0,00',
    ),
  ),
  'shippingAddressIntro' => 'Adresse de livraison :',
  'shippingAddress' => '',
  'totalHT' => '50 740,81€',
  'totalTTC' => '60 686,00€',
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
  'billId' => 1004,
  'title' => 'Facture n°1004',
  'subject' => 'Facture du 17/09/2009 pour Parent Brice',
);