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
      3 => '1',
      4 => '2 323,00',
    ),
    2 => 
    array (
      0 => 'Livraison',
      1 => 'A l\'adresse ci-dessous',
      2 => '0,00',
      3 => 1,
      4 => '0,00',
    ),
  ),
  'comeTakeItIntro' => 'Adresse de livraison :',
  'comeTakeItAddress' => '62 av Joannon<br />Les Palombes<br />13540 Puyricard',
  'totalHT' => '1 999,17€',
  'totalTTC' => '2 391,00€',
  'client' => 
  array (
    'name' => 'Parent Brice',
    'address' => 'Téléphone : 0442921737
E-mail : briceparent@free.fr',
  ),
  'title' => 'Facture du 17/09/2009 pour Parent Brice',
  'subject' => 'Facture du 17/09/2009 pour Parent Brice',
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
  'logo' => '/sites/websailors/sh_images/shop/2009-04-28-173038.jpg',
  'footer' => 'Websailors est une SARL SCOP au capital variable de 1000€
62 av. Joannon - Les Palombes - 13540 Puyricard
Tel: +33 6 81 21 05 56 - facturation@websailors.fr
Siren: 512310814 - RCS d\'Aix-en-Provence',
  'headLine' => 'Madame, Monsieur,
par la présente nous vous joignons la facture réalisée conformément à nos conditions générales de vente:',
);