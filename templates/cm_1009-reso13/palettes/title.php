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

$palette = array (
      
      0 => 
      array (
        'reallyDark' => 
        array (
          'H' => 0,
          'S' => 100,
          'V' => 9,
          'R' => 25,
          'G' => 0,
          'B' => 0,
          'alpha' => 0,
          'hex' => '#190000',
        ),
        'dark' => 
        array (
          'H' => 0,
          'S' => 97,
          'V' => 40,
          'R' => 104,
          'G' => 3,
          'B' => 3,
          'alpha' => 0,
          'hex' => '#680303',
        ),
        'normal' => 
        array (
          'H' => 0,
          'S' => 97,
          'V' => 65,
          'R' => 168,
          'G' => 5,
          'B' => 5,
          'alpha' => 0,
          'hex' => '#a80505',
        ),
        'shiny' => 
        array (
          'H' => 0,
          'S' => 97,
          'V' => 90,
          'R' => 232,
          'G' => 6,
          'B' => 6,
          'alpha' => 0,
          'hex' => '#e80606',
        ),
        'reallyShiny' => 
        array (
          'H' => 0,
          'S' => 97,
          'V' => 100,
          'R' => 255,
          'G' => 7,
          'B' => 7,
          'alpha' => 0,
          'hex' => '#ff0707',
        ),
      ),
      10 => 
      array (
        'reallyDark' => 
        array (
          'H' => 9,
          'S' => 100,
          'V' => 9,
          'R' => 25,
          'G' => 4,
          'B' => 0,
          'alpha' => 0,
          'hex' => '#190400',
        ),
        'dark' => 
        array (
          'H' => 10,
          'S' => 97,
          'V' => 40,
          'R' => 104,
          'G' => 20,
          'B' => 3,
          'alpha' => 0,
          'hex' => '#681403',
        ),
        'normal' => 
        array (
          'H' => 9,
          'S' => 97,
          'V' => 65,
          'R' => 168,
          'G' => 32,
          'B' => 5,
          'alpha' => 0,
          'hex' => '#a82005',
        ),
        'shiny' => 
        array (
          'H' => 10,
          'S' => 97,
          'V' => 90,
          'R' => 232,
          'G' => 44,
          'B' => 6,
          'alpha' => 0,
          'hex' => '#e82c06',
        ),
        'reallyShiny' => 
        array (
          'H' => 9,
          'S' => 97,
          'V' => 100,
          'R' => 255,
          'G' => 48,
          'B' => 7,
          'alpha' => 0,
          'hex' => '#ff3007',
        ),
      ),
      20 => 
      array (
        'reallyDark' => 
        array (
          'H' => 19,
          'S' => 100,
          'V' => 9,
          'R' => 25,
          'G' => 8,
          'B' => 0,
          'alpha' => 0,
          'hex' => '#190800',
        ),
        'dark' => 
        array (
          'H' => 19,
          'S' => 97,
          'V' => 40,
          'R' => 104,
          'G' => 36,
          'B' => 3,
          'alpha' => 0,
          'hex' => '#682403',
        ),
        'normal' => 
        array (
          'H' => 19,
          'S' => 97,
          'V' => 65,
          'R' => 168,
          'G' => 59,
          'B' => 5,
          'alpha' => 0,
          'hex' => '#a83b05',
        ),
        'shiny' => 
        array (
          'H' => 19,
          'S' => 97,
          'V' => 90,
          'R' => 232,
          'G' => 81,
          'B' => 6,
          'alpha' => 0,
          'hex' => '#e85106',
        ),
        'reallyShiny' => 
        array (
          'H' => 20,
          'S' => 97,
          'V' => 100,
          'R' => 255,
          'G' => 90,
          'B' => 7,
          'alpha' => 0,
          'hex' => '#ff5a07',
        ),
      ),
      30 => 
      array (
        'reallyDark' => 
        array (
          'H' => 31,
          'S' => 100,
          'V' => 9,
          'R' => 25,
          'G' => 13,
          'B' => 0,
          'alpha' => 0,
          'hex' => '#190d00',
        ),
        'dark' => 
        array (
          'H' => 29,
          'S' => 97,
          'V' => 40,
          'R' => 104,
          'G' => 53,
          'B' => 3,
          'alpha' => 0,
          'hex' => '#683503',
        ),
        'normal' => 
        array (
          'H' => 29,
          'S' => 97,
          'V' => 65,
          'R' => 168,
          'G' => 86,
          'B' => 5,
          'alpha' => 0,
          'hex' => '#a85605',
        ),
        'shiny' => 
        array (
          'H' => 30,
          'S' => 97,
          'V' => 90,
          'R' => 232,
          'G' => 119,
          'B' => 6,
          'alpha' => 0,
          'hex' => '#e87706',
        ),
        'reallyShiny' => 
        array (
          'H' => 30,
          'S' => 97,
          'V' => 100,
          'R' => 255,
          'G' => 131,
          'B' => 7,
          'alpha' => 0,
          'hex' => '#ff8307',
        ),
      ),
      40 => 
      array (
        'reallyDark' => 
        array (
          'H' => 38,
          'S' => 100,
          'V' => 9,
          'R' => 25,
          'G' => 16,
          'B' => 0,
          'alpha' => 0,
          'hex' => '#191000',
        ),
        'dark' => 
        array (
          'H' => 39,
          'S' => 97,
          'V' => 40,
          'R' => 104,
          'G' => 70,
          'B' => 3,
          'alpha' => 0,
          'hex' => '#684603',
        ),
        'normal' => 
        array (
          'H' => 39,
          'S' => 97,
          'V' => 65,
          'R' => 168,
          'G' => 113,
          'B' => 5,
          'alpha' => 0,
          'hex' => '#a87105',
        ),
        'shiny' => 
        array (
          'H' => 40,
          'S' => 97,
          'V' => 90,
          'R' => 232,
          'G' => 157,
          'B' => 6,
          'alpha' => 0,
          'hex' => '#e89d06',
        ),
        'reallyShiny' => 
        array (
          'H' => 39,
          'S' => 97,
          'V' => 100,
          'R' => 255,
          'G' => 172,
          'B' => 7,
          'alpha' => 0,
          'hex' => '#ffac07',
        ),
      ),
      50 => 
      array (
        'reallyDark' => 
        array (
          'H' => 50,
          'S' => 100,
          'V' => 9,
          'R' => 25,
          'G' => 21,
          'B' => 0,
          'alpha' => 0,
          'hex' => '#191500',
        ),
        'dark' => 
        array (
          'H' => 49,
          'S' => 97,
          'V' => 40,
          'R' => 104,
          'G' => 87,
          'B' => 3,
          'alpha' => 0,
          'hex' => '#685703',
        ),
        'normal' => 
        array (
          'H' => 50,
          'S' => 97,
          'V' => 65,
          'R' => 168,
          'G' => 141,
          'B' => 5,
          'alpha' => 0,
          'hex' => '#a88d05',
        ),
        'shiny' => 
        array (
          'H' => 49,
          'S' => 97,
          'V' => 90,
          'R' => 232,
          'G' => 194,
          'B' => 6,
          'alpha' => 0,
          'hex' => '#e8c206',
        ),
        'reallyShiny' => 
        array (
          'H' => 49,
          'S' => 97,
          'V' => 100,
          'R' => 255,
          'G' => 213,
          'B' => 7,
          'alpha' => 0,
          'hex' => '#ffd507',
        ),
      ),
      60 => 
      array (
        'reallyDark' => 
        array (
          'H' => 60,
          'S' => 100,
          'V' => 9,
          'R' => 25,
          'G' => 25,
          'B' => 0,
          'alpha' => 0,
          'hex' => '#191900',
        ),
        'dark' => 
        array (
          'H' => 60,
          'S' => 97,
          'V' => 40,
          'R' => 104,
          'G' => 104,
          'B' => 3,
          'alpha' => 0,
          'hex' => '#686803',
        ),
        'normal' => 
        array (
          'H' => 60,
          'S' => 97,
          'V' => 65,
          'R' => 168,
          'G' => 168,
          'B' => 5,
          'alpha' => 0,
          'hex' => '#a8a805',
        ),
        'shiny' => 
        array (
          'H' => 60,
          'S' => 97,
          'V' => 90,
          'R' => 232,
          'G' => 232,
          'B' => 6,
          'alpha' => 0,
          'hex' => '#e8e806',
        ),
        'reallyShiny' => 
        array (
          'H' => 60,
          'S' => 97,
          'V' => 100,
          'R' => 255,
          'G' => 255,
          'B' => 7,
          'alpha' => 0,
          'hex' => '#ffff07',
        ),
      ),
      70 => 
      array (
        'reallyDark' => 
        array (
          'H' => 69,
          'S' => 100,
          'V' => 9,
          'R' => 21,
          'G' => 25,
          'B' => 0,
          'alpha' => 0,
          'hex' => '#151900',
        ),
        'dark' => 
        array (
          'H' => 70,
          'S' => 97,
          'V' => 40,
          'R' => 87,
          'G' => 104,
          'B' => 3,
          'alpha' => 0,
          'hex' => '#576803',
        ),
        'normal' => 
        array (
          'H' => 69,
          'S' => 97,
          'V' => 65,
          'R' => 141,
          'G' => 168,
          'B' => 5,
          'alpha' => 0,
          'hex' => '#8da805',
        ),
        'shiny' => 
        array (
          'H' => 70,
          'S' => 97,
          'V' => 90,
          'R' => 194,
          'G' => 232,
          'B' => 6,
          'alpha' => 0,
          'hex' => '#c2e806',
        ),
        'reallyShiny' => 
        array (
          'H' => 70,
          'S' => 97,
          'V' => 100,
          'R' => 213,
          'G' => 255,
          'B' => 7,
          'alpha' => 0,
          'hex' => '#d5ff07',
        ),
      ),
      80 => 
      array (
        'reallyDark' => 
        array (
          'H' => 81,
          'S' => 100,
          'V' => 9,
          'R' => 16,
          'G' => 25,
          'B' => 0,
          'alpha' => 0,
          'hex' => '#101900',
        ),
        'dark' => 
        array (
          'H' => 80,
          'S' => 97,
          'V' => 40,
          'R' => 70,
          'G' => 104,
          'B' => 3,
          'alpha' => 0,
          'hex' => '#466803',
        ),
        'normal' => 
        array (
          'H' => 80,
          'S' => 97,
          'V' => 65,
          'R' => 113,
          'G' => 168,
          'B' => 5,
          'alpha' => 0,
          'hex' => '#71a805',
        ),
        'shiny' => 
        array (
          'H' => 79,
          'S' => 97,
          'V' => 90,
          'R' => 157,
          'G' => 232,
          'B' => 6,
          'alpha' => 0,
          'hex' => '#9de806',
        ),
        'reallyShiny' => 
        array (
          'H' => 80,
          'S' => 97,
          'V' => 100,
          'R' => 172,
          'G' => 255,
          'B' => 7,
          'alpha' => 0,
          'hex' => '#acff07',
        ),
      ),
      90 => 
      array (
        'reallyDark' => 
        array (
          'H' => 88,
          'S' => 100,
          'V' => 9,
          'R' => 13,
          'G' => 25,
          'B' => 0,
          'alpha' => 0,
          'hex' => '#0d1900',
        ),
        'dark' => 
        array (
          'H' => 90,
          'S' => 97,
          'V' => 40,
          'R' => 53,
          'G' => 104,
          'B' => 3,
          'alpha' => 0,
          'hex' => '#356803',
        ),
        'normal' => 
        array (
          'H' => 90,
          'S' => 97,
          'V' => 65,
          'R' => 86,
          'G' => 168,
          'B' => 5,
          'alpha' => 0,
          'hex' => '#56a805',
        ),
        'shiny' => 
        array (
          'H' => 89,
          'S' => 97,
          'V' => 90,
          'R' => 119,
          'G' => 232,
          'B' => 6,
          'alpha' => 0,
          'hex' => '#77e806',
        ),
        'reallyShiny' => 
        array (
          'H' => 90,
          'S' => 97,
          'V' => 100,
          'R' => 131,
          'G' => 255,
          'B' => 7,
          'alpha' => 0,
          'hex' => '#83ff07',
        ),
      ),
      100 => 
      array (
        'reallyDark' => 
        array (
          'H' => 100,
          'S' => 100,
          'V' => 9,
          'R' => 8,
          'G' => 25,
          'B' => 0,
          'alpha' => 0,
          'hex' => '#081900',
        ),
        'dark' => 
        array (
          'H' => 100,
          'S' => 97,
          'V' => 40,
          'R' => 36,
          'G' => 104,
          'B' => 3,
          'alpha' => 0,
          'hex' => '#246803',
        ),
        'normal' => 
        array (
          'H' => 100,
          'S' => 97,
          'V' => 65,
          'R' => 59,
          'G' => 168,
          'B' => 5,
          'alpha' => 0,
          'hex' => '#3ba805',
        ),
        'shiny' => 
        array (
          'H' => 100,
          'S' => 97,
          'V' => 90,
          'R' => 81,
          'G' => 232,
          'B' => 6,
          'alpha' => 0,
          'hex' => '#51e806',
        ),
        'reallyShiny' => 
        array (
          'H' => 99,
          'S' => 97,
          'V' => 100,
          'R' => 90,
          'G' => 255,
          'B' => 7,
          'alpha' => 0,
          'hex' => '#5aff07',
        ),
      ),
      110 => 
      array (
        'reallyDark' => 
        array (
          'H' => 110,
          'S' => 100,
          'V' => 9,
          'R' => 4,
          'G' => 25,
          'B' => 0,
          'alpha' => 0,
          'hex' => '#041900',
        ),
        'dark' => 
        array (
          'H' => 109,
          'S' => 97,
          'V' => 40,
          'R' => 20,
          'G' => 104,
          'B' => 3,
          'alpha' => 0,
          'hex' => '#146803',
        ),
        'normal' => 
        array (
          'H' => 110,
          'S' => 97,
          'V' => 65,
          'R' => 32,
          'G' => 168,
          'B' => 5,
          'alpha' => 0,
          'hex' => '#20a805',
        ),
        'shiny' => 
        array (
          'H' => 109,
          'S' => 97,
          'V' => 90,
          'R' => 44,
          'G' => 232,
          'B' => 6,
          'alpha' => 0,
          'hex' => '#2ce806',
        ),
        'reallyShiny' => 
        array (
          'H' => 110,
          'S' => 97,
          'V' => 100,
          'R' => 48,
          'G' => 255,
          'B' => 7,
          'alpha' => 0,
          'hex' => '#30ff07',
        ),
      ),
      120 => 
      array (
        'reallyDark' => 
        array (
          'H' => 119,
          'S' => 100,
          'V' => 9,
          'R' => 0,
          'G' => 25,
          'B' => 0,
          'alpha' => 0,
          'hex' => '#001900',
        ),
        'dark' => 
        array (
          'H' => 119,
          'S' => 97,
          'V' => 40,
          'R' => 3,
          'G' => 104,
          'B' => 3,
          'alpha' => 0,
          'hex' => '#036803',
        ),
        'normal' => 
        array (
          'H' => 119,
          'S' => 97,
          'V' => 65,
          'R' => 5,
          'G' => 168,
          'B' => 5,
          'alpha' => 0,
          'hex' => '#05a805',
        ),
        'shiny' => 
        array (
          'H' => 119,
          'S' => 97,
          'V' => 90,
          'R' => 6,
          'G' => 232,
          'B' => 6,
          'alpha' => 0,
          'hex' => '#06e806',
        ),
        'reallyShiny' => 
        array (
          'H' => 119,
          'S' => 97,
          'V' => 100,
          'R' => 7,
          'G' => 255,
          'B' => 7,
          'alpha' => 0,
          'hex' => '#07ff07',
        ),
      ),
      130 => 
      array (
        'reallyDark' => 
        array (
          'H' => 129,
          'S' => 100,
          'V' => 9,
          'R' => 0,
          'G' => 25,
          'B' => 4,
          'alpha' => 0,
          'hex' => '#001904',
        ),
        'dark' => 
        array (
          'H' => 130,
          'S' => 97,
          'V' => 40,
          'R' => 3,
          'G' => 104,
          'B' => 20,
          'alpha' => 0,
          'hex' => '#036814',
        ),
        'normal' => 
        array (
          'H' => 129,
          'S' => 97,
          'V' => 65,
          'R' => 5,
          'G' => 168,
          'B' => 32,
          'alpha' => 0,
          'hex' => '#05a820',
        ),
        'shiny' => 
        array (
          'H' => 130,
          'S' => 97,
          'V' => 90,
          'R' => 6,
          'G' => 232,
          'B' => 44,
          'alpha' => 0,
          'hex' => '#06e82c',
        ),
        'reallyShiny' => 
        array (
          'H' => 129,
          'S' => 97,
          'V' => 100,
          'R' => 7,
          'G' => 255,
          'B' => 48,
          'alpha' => 0,
          'hex' => '#07ff30',
        ),
      ),
      140 => 
      array (
        'reallyDark' => 
        array (
          'H' => 139,
          'S' => 100,
          'V' => 9,
          'R' => 0,
          'G' => 25,
          'B' => 8,
          'alpha' => 0,
          'hex' => '#001908',
        ),
        'dark' => 
        array (
          'H' => 139,
          'S' => 97,
          'V' => 40,
          'R' => 3,
          'G' => 104,
          'B' => 36,
          'alpha' => 0,
          'hex' => '#036824',
        ),
        'normal' => 
        array (
          'H' => 139,
          'S' => 97,
          'V' => 65,
          'R' => 5,
          'G' => 168,
          'B' => 59,
          'alpha' => 0,
          'hex' => '#05a83b',
        ),
        'shiny' => 
        array (
          'H' => 139,
          'S' => 97,
          'V' => 90,
          'R' => 6,
          'G' => 232,
          'B' => 81,
          'alpha' => 0,
          'hex' => '#06e851',
        ),
        'reallyShiny' => 
        array (
          'H' => 140,
          'S' => 97,
          'V' => 100,
          'R' => 7,
          'G' => 255,
          'B' => 90,
          'alpha' => 0,
          'hex' => '#07ff5a',
        ),
      ),
      150 => 
      array (
        'reallyDark' => 
        array (
          'H' => 151,
          'S' => 100,
          'V' => 9,
          'R' => 0,
          'G' => 25,
          'B' => 13,
          'alpha' => 0,
          'hex' => '#00190d',
        ),
        'dark' => 
        array (
          'H' => 149,
          'S' => 97,
          'V' => 40,
          'R' => 3,
          'G' => 104,
          'B' => 53,
          'alpha' => 0,
          'hex' => '#036835',
        ),
        'normal' => 
        array (
          'H' => 149,
          'S' => 97,
          'V' => 65,
          'R' => 5,
          'G' => 168,
          'B' => 86,
          'alpha' => 0,
          'hex' => '#05a856',
        ),
        'shiny' => 
        array (
          'H' => 150,
          'S' => 97,
          'V' => 90,
          'R' => 6,
          'G' => 232,
          'B' => 119,
          'alpha' => 0,
          'hex' => '#06e877',
        ),
        'reallyShiny' => 
        array (
          'H' => 150,
          'S' => 97,
          'V' => 100,
          'R' => 7,
          'G' => 255,
          'B' => 131,
          'alpha' => 0,
          'hex' => '#07ff83',
        ),
      ),
      160 => 
      array (
        'reallyDark' => 
        array (
          'H' => 158,
          'S' => 100,
          'V' => 9,
          'R' => 0,
          'G' => 25,
          'B' => 16,
          'alpha' => 0,
          'hex' => '#001910',
        ),
        'dark' => 
        array (
          'H' => 159,
          'S' => 97,
          'V' => 40,
          'R' => 3,
          'G' => 104,
          'B' => 70,
          'alpha' => 0,
          'hex' => '#036846',
        ),
        'normal' => 
        array (
          'H' => 159,
          'S' => 97,
          'V' => 65,
          'R' => 5,
          'G' => 168,
          'B' => 113,
          'alpha' => 0,
          'hex' => '#05a871',
        ),
        'shiny' => 
        array (
          'H' => 160,
          'S' => 97,
          'V' => 90,
          'R' => 6,
          'G' => 232,
          'B' => 157,
          'alpha' => 0,
          'hex' => '#06e89d',
        ),
        'reallyShiny' => 
        array (
          'H' => 159,
          'S' => 97,
          'V' => 100,
          'R' => 7,
          'G' => 255,
          'B' => 172,
          'alpha' => 0,
          'hex' => '#07ffac',
        ),
      ),
      170 => 
      array (
        'reallyDark' => 
        array (
          'H' => 170,
          'S' => 100,
          'V' => 9,
          'R' => 0,
          'G' => 25,
          'B' => 21,
          'alpha' => 0,
          'hex' => '#001915',
        ),
        'dark' => 
        array (
          'H' => 169,
          'S' => 97,
          'V' => 40,
          'R' => 3,
          'G' => 104,
          'B' => 87,
          'alpha' => 0,
          'hex' => '#036857',
        ),
        'normal' => 
        array (
          'H' => 170,
          'S' => 97,
          'V' => 65,
          'R' => 5,
          'G' => 168,
          'B' => 141,
          'alpha' => 0,
          'hex' => '#05a88d',
        ),
        'shiny' => 
        array (
          'H' => 169,
          'S' => 97,
          'V' => 90,
          'R' => 6,
          'G' => 232,
          'B' => 194,
          'alpha' => 0,
          'hex' => '#06e8c2',
        ),
        'reallyShiny' => 
        array (
          'H' => 169,
          'S' => 97,
          'V' => 100,
          'R' => 7,
          'G' => 255,
          'B' => 213,
          'alpha' => 0,
          'hex' => '#07ffd5',
        ),
      ),
      180 => 
      array (
        'reallyDark' => 
        array (
          'H' => 180,
          'S' => 100,
          'V' => 9,
          'R' => 0,
          'G' => 25,
          'B' => 25,
          'alpha' => 0,
          'hex' => '#001919',
        ),
        'dark' => 
        array (
          'H' => 180,
          'S' => 97,
          'V' => 40,
          'R' => 3,
          'G' => 104,
          'B' => 104,
          'alpha' => 0,
          'hex' => '#036868',
        ),
        'normal' => 
        array (
          'H' => 180,
          'S' => 97,
          'V' => 65,
          'R' => 5,
          'G' => 168,
          'B' => 168,
          'alpha' => 0,
          'hex' => '#05a8a8',
        ),
        'shiny' => 
        array (
          'H' => 180,
          'S' => 97,
          'V' => 90,
          'R' => 6,
          'G' => 232,
          'B' => 232,
          'alpha' => 0,
          'hex' => '#06e8e8',
        ),
        'reallyShiny' => 
        array (
          'H' => 180,
          'S' => 97,
          'V' => 100,
          'R' => 7,
          'G' => 255,
          'B' => 255,
          'alpha' => 0,
          'hex' => '#07ffff',
        ),
      ),
      190 => 
      array (
        'reallyDark' => 
        array (
          'H' => 189,
          'S' => 100,
          'V' => 9,
          'R' => 0,
          'G' => 21,
          'B' => 25,
          'alpha' => 0,
          'hex' => '#001519',
        ),
        'dark' => 
        array (
          'H' => 190,
          'S' => 97,
          'V' => 40,
          'R' => 3,
          'G' => 87,
          'B' => 104,
          'alpha' => 0,
          'hex' => '#035768',
        ),
        'normal' => 
        array (
          'H' => 189,
          'S' => 97,
          'V' => 65,
          'R' => 5,
          'G' => 141,
          'B' => 168,
          'alpha' => 0,
          'hex' => '#058da8',
        ),
        'shiny' => 
        array (
          'H' => 190,
          'S' => 97,
          'V' => 90,
          'R' => 6,
          'G' => 194,
          'B' => 232,
          'alpha' => 0,
          'hex' => '#06c2e8',
        ),
        'reallyShiny' => 
        array (
          'H' => 190,
          'S' => 97,
          'V' => 100,
          'R' => 7,
          'G' => 213,
          'B' => 255,
          'alpha' => 0,
          'hex' => '#07d5ff',
        ),
      ),
      200 => 
      array (
        'reallyDark' => 
        array (
          'H' => 201,
          'S' => 100,
          'V' => 9,
          'R' => 0,
          'G' => 16,
          'B' => 25,
          'alpha' => 0,
          'hex' => '#001019',
        ),
        'dark' => 
        array (
          'H' => 200,
          'S' => 97,
          'V' => 40,
          'R' => 3,
          'G' => 70,
          'B' => 104,
          'alpha' => 0,
          'hex' => '#034668',
        ),
        'normal' => 
        array (
          'H' => 200,
          'S' => 97,
          'V' => 65,
          'R' => 5,
          'G' => 113,
          'B' => 168,
          'alpha' => 0,
          'hex' => '#0571a8',
        ),
        'shiny' => 
        array (
          'H' => 199,
          'S' => 97,
          'V' => 90,
          'R' => 6,
          'G' => 157,
          'B' => 232,
          'alpha' => 0,
          'hex' => '#069de8',
        ),
        'reallyShiny' => 
        array (
          'H' => 200,
          'S' => 97,
          'V' => 100,
          'R' => 7,
          'G' => 172,
          'B' => 255,
          'alpha' => 0,
          'hex' => '#07acff',
        ),
      ),
      210 => 
      array (
        'reallyDark' => 
        array (
          'H' => 208,
          'S' => 100,
          'V' => 9,
          'R' => 0,
          'G' => 13,
          'B' => 25,
          'alpha' => 0,
          'hex' => '#000d19',
        ),
        'dark' => 
        array (
          'H' => 210,
          'S' => 97,
          'V' => 40,
          'R' => 3,
          'G' => 53,
          'B' => 104,
          'alpha' => 0,
          'hex' => '#033568',
        ),
        'normal' => 
        array (
          'H' => 210,
          'S' => 97,
          'V' => 65,
          'R' => 5,
          'G' => 86,
          'B' => 168,
          'alpha' => 0,
          'hex' => '#0556a8',
        ),
        'shiny' => 
        array (
          'H' => 209,
          'S' => 97,
          'V' => 90,
          'R' => 6,
          'G' => 119,
          'B' => 232,
          'alpha' => 0,
          'hex' => '#0677e8',
        ),
        'reallyShiny' => 
        array (
          'H' => 209,
          'S' => 97,
          'V' => 100,
          'R' => 7,
          'G' => 131,
          'B' => 255,
          'alpha' => 0,
          'hex' => '#0783ff',
        ),
      ),
      220 => 
      array (
        'reallyDark' => 
        array (
          'H' => 220,
          'S' => 100,
          'V' => 9,
          'R' => 0,
          'G' => 8,
          'B' => 25,
          'alpha' => 0,
          'hex' => '#000819',
        ),
        'dark' => 
        array (
          'H' => 220,
          'S' => 97,
          'V' => 40,
          'R' => 3,
          'G' => 36,
          'B' => 104,
          'alpha' => 0,
          'hex' => '#032468',
        ),
        'normal' => 
        array (
          'H' => 220,
          'S' => 97,
          'V' => 65,
          'R' => 5,
          'G' => 59,
          'B' => 168,
          'alpha' => 0,
          'hex' => '#053ba8',
        ),
        'shiny' => 
        array (
          'H' => 220,
          'S' => 97,
          'V' => 90,
          'R' => 6,
          'G' => 81,
          'B' => 232,
          'alpha' => 0,
          'hex' => '#0651e8',
        ),
        'reallyShiny' => 
        array (
          'H' => 219,
          'S' => 97,
          'V' => 100,
          'R' => 7,
          'G' => 90,
          'B' => 255,
          'alpha' => 0,
          'hex' => '#075aff',
        ),
      ),
      230 => 
      array (
        'reallyDark' => 
        array (
          'H' => 230,
          'S' => 100,
          'V' => 9,
          'R' => 0,
          'G' => 4,
          'B' => 25,
          'alpha' => 0,
          'hex' => '#000419',
        ),
        'dark' => 
        array (
          'H' => 229,
          'S' => 97,
          'V' => 40,
          'R' => 3,
          'G' => 20,
          'B' => 104,
          'alpha' => 0,
          'hex' => '#031468',
        ),
        'normal' => 
        array (
          'H' => 230,
          'S' => 97,
          'V' => 65,
          'R' => 5,
          'G' => 32,
          'B' => 168,
          'alpha' => 0,
          'hex' => '#0520a8',
        ),
        'shiny' => 
        array (
          'H' => 229,
          'S' => 97,
          'V' => 90,
          'R' => 6,
          'G' => 44,
          'B' => 232,
          'alpha' => 0,
          'hex' => '#062ce8',
        ),
        'reallyShiny' => 
        array (
          'H' => 230,
          'S' => 97,
          'V' => 100,
          'R' => 7,
          'G' => 48,
          'B' => 255,
          'alpha' => 0,
          'hex' => '#0730ff',
        ),
      ),
      240 => 
      array (
        'reallyDark' => 
        array (
          'H' => 240,
          'S' => 100,
          'V' => 9,
          'R' => 0,
          'G' => 0,
          'B' => 25,
          'alpha' => 0,
          'hex' => '#000019',
        ),
        'dark' => 
        array (
          'H' => 240,
          'S' => 97,
          'V' => 40,
          'R' => 3,
          'G' => 3,
          'B' => 104,
          'alpha' => 0,
          'hex' => '#030368',
        ),
        'normal' => 
        array (
          'H' => 240,
          'S' => 97,
          'V' => 65,
          'R' => 5,
          'G' => 5,
          'B' => 168,
          'alpha' => 0,
          'hex' => '#0505a8',
        ),
        'shiny' => 
        array (
          'H' => 240,
          'S' => 97,
          'V' => 90,
          'R' => 6,
          'G' => 6,
          'B' => 232,
          'alpha' => 0,
          'hex' => '#0606e8',
        ),
        'reallyShiny' => 
        array (
          'H' => 240,
          'S' => 97,
          'V' => 100,
          'R' => 7,
          'G' => 7,
          'B' => 255,
          'alpha' => 0,
          'hex' => '#0707ff',
        ),
      ),
      250 => 
      array (
        'reallyDark' => 
        array (
          'H' => 249,
          'S' => 100,
          'V' => 9,
          'R' => 4,
          'G' => 0,
          'B' => 25,
          'alpha' => 0,
          'hex' => '#040019',
        ),
        'dark' => 
        array (
          'H' => 250,
          'S' => 97,
          'V' => 40,
          'R' => 20,
          'G' => 3,
          'B' => 104,
          'alpha' => 0,
          'hex' => '#140368',
        ),
        'normal' => 
        array (
          'H' => 249,
          'S' => 97,
          'V' => 65,
          'R' => 32,
          'G' => 5,
          'B' => 168,
          'alpha' => 0,
          'hex' => '#2005a8',
        ),
        'shiny' => 
        array (
          'H' => 250,
          'S' => 97,
          'V' => 90,
          'R' => 44,
          'G' => 6,
          'B' => 232,
          'alpha' => 0,
          'hex' => '#2c06e8',
        ),
        'reallyShiny' => 
        array (
          'H' => 249,
          'S' => 97,
          'V' => 100,
          'R' => 48,
          'G' => 7,
          'B' => 255,
          'alpha' => 0,
          'hex' => '#3007ff',
        ),
      ),
      260 => 
      array (
        'reallyDark' => 
        array (
          'H' => 259,
          'S' => 100,
          'V' => 9,
          'R' => 8,
          'G' => 0,
          'B' => 25,
          'alpha' => 0,
          'hex' => '#080019',
        ),
        'dark' => 
        array (
          'H' => 259,
          'S' => 97,
          'V' => 40,
          'R' => 36,
          'G' => 3,
          'B' => 104,
          'alpha' => 0,
          'hex' => '#240368',
        ),
        'normal' => 
        array (
          'H' => 259,
          'S' => 97,
          'V' => 65,
          'R' => 59,
          'G' => 5,
          'B' => 168,
          'alpha' => 0,
          'hex' => '#3b05a8',
        ),
        'shiny' => 
        array (
          'H' => 259,
          'S' => 97,
          'V' => 90,
          'R' => 81,
          'G' => 6,
          'B' => 232,
          'alpha' => 0,
          'hex' => '#5106e8',
        ),
        'reallyShiny' => 
        array (
          'H' => 260,
          'S' => 97,
          'V' => 100,
          'R' => 90,
          'G' => 7,
          'B' => 255,
          'alpha' => 0,
          'hex' => '#5a07ff',
        ),
      ),
      270 => 
      array (
        'reallyDark' => 
        array (
          'H' => 271,
          'S' => 100,
          'V' => 9,
          'R' => 13,
          'G' => 0,
          'B' => 25,
          'alpha' => 0,
          'hex' => '#0d0019',
        ),
        'dark' => 
        array (
          'H' => 269,
          'S' => 97,
          'V' => 40,
          'R' => 53,
          'G' => 3,
          'B' => 104,
          'alpha' => 0,
          'hex' => '#350368',
        ),
        'normal' => 
        array (
          'H' => 269,
          'S' => 97,
          'V' => 65,
          'R' => 86,
          'G' => 5,
          'B' => 168,
          'alpha' => 0,
          'hex' => '#5605a8',
        ),
        'shiny' => 
        array (
          'H' => 270,
          'S' => 97,
          'V' => 90,
          'R' => 119,
          'G' => 6,
          'B' => 232,
          'alpha' => 0,
          'hex' => '#7706e8',
        ),
        'reallyShiny' => 
        array (
          'H' => 270,
          'S' => 97,
          'V' => 100,
          'R' => 131,
          'G' => 7,
          'B' => 255,
          'alpha' => 0,
          'hex' => '#8307ff',
        ),
      ),
      280 => 
      array (
        'reallyDark' => 
        array (
          'H' => 278,
          'S' => 100,
          'V' => 9,
          'R' => 16,
          'G' => 0,
          'B' => 25,
          'alpha' => 0,
          'hex' => '#100019',
        ),
        'dark' => 
        array (
          'H' => 279,
          'S' => 97,
          'V' => 40,
          'R' => 70,
          'G' => 3,
          'B' => 104,
          'alpha' => 0,
          'hex' => '#460368',
        ),
        'normal' => 
        array (
          'H' => 279,
          'S' => 97,
          'V' => 65,
          'R' => 113,
          'G' => 5,
          'B' => 168,
          'alpha' => 0,
          'hex' => '#7105a8',
        ),
        'shiny' => 
        array (
          'H' => 280,
          'S' => 97,
          'V' => 90,
          'R' => 157,
          'G' => 6,
          'B' => 232,
          'alpha' => 0,
          'hex' => '#9d06e8',
        ),
        'reallyShiny' => 
        array (
          'H' => 279,
          'S' => 97,
          'V' => 100,
          'R' => 172,
          'G' => 7,
          'B' => 255,
          'alpha' => 0,
          'hex' => '#ac07ff',
        ),
      ),
      290 => 
      array (
        'reallyDark' => 
        array (
          'H' => 290,
          'S' => 100,
          'V' => 9,
          'R' => 21,
          'G' => 0,
          'B' => 25,
          'alpha' => 0,
          'hex' => '#150019',
        ),
        'dark' => 
        array (
          'H' => 289,
          'S' => 97,
          'V' => 40,
          'R' => 87,
          'G' => 3,
          'B' => 104,
          'alpha' => 0,
          'hex' => '#570368',
        ),
        'normal' => 
        array (
          'H' => 290,
          'S' => 97,
          'V' => 65,
          'R' => 141,
          'G' => 5,
          'B' => 168,
          'alpha' => 0,
          'hex' => '#8d05a8',
        ),
        'shiny' => 
        array (
          'H' => 289,
          'S' => 97,
          'V' => 90,
          'R' => 194,
          'G' => 6,
          'B' => 232,
          'alpha' => 0,
          'hex' => '#c206e8',
        ),
        'reallyShiny' => 
        array (
          'H' => 289,
          'S' => 97,
          'V' => 100,
          'R' => 213,
          'G' => 7,
          'B' => 255,
          'alpha' => 0,
          'hex' => '#d507ff',
        ),
      ),
      300 => 
      array (
        'reallyDark' => 
        array (
          'H' => 300,
          'S' => 100,
          'V' => 9,
          'R' => 25,
          'G' => 0,
          'B' => 25,
          'alpha' => 0,
          'hex' => '#190019',
        ),
        'dark' => 
        array (
          'H' => 300,
          'S' => 97,
          'V' => 40,
          'R' => 104,
          'G' => 3,
          'B' => 104,
          'alpha' => 0,
          'hex' => '#680368',
        ),
        'normal' => 
        array (
          'H' => 300,
          'S' => 97,
          'V' => 65,
          'R' => 168,
          'G' => 5,
          'B' => 168,
          'alpha' => 0,
          'hex' => '#a805a8',
        ),
        'shiny' => 
        array (
          'H' => 300,
          'S' => 97,
          'V' => 90,
          'R' => 232,
          'G' => 6,
          'B' => 232,
          'alpha' => 0,
          'hex' => '#e806e8',
        ),
        'reallyShiny' => 
        array (
          'H' => 300,
          'S' => 97,
          'V' => 100,
          'R' => 255,
          'G' => 7,
          'B' => 255,
          'alpha' => 0,
          'hex' => '#ff07ff',
        ),
      ),
      310 => 
      array (
        'reallyDark' => 
        array (
          'H' => 309,
          'S' => 100,
          'V' => 9,
          'R' => 25,
          'G' => 0,
          'B' => 21,
          'alpha' => 0,
          'hex' => '#190015',
        ),
        'dark' => 
        array (
          'H' => 310,
          'S' => 97,
          'V' => 40,
          'R' => 104,
          'G' => 3,
          'B' => 87,
          'alpha' => 0,
          'hex' => '#680357',
        ),
        'normal' => 
        array (
          'H' => 309,
          'S' => 97,
          'V' => 65,
          'R' => 168,
          'G' => 5,
          'B' => 141,
          'alpha' => 0,
          'hex' => '#a8058d',
        ),
        'shiny' => 
        array (
          'H' => 310,
          'S' => 97,
          'V' => 90,
          'R' => 232,
          'G' => 6,
          'B' => 194,
          'alpha' => 0,
          'hex' => '#e806c2',
        ),
        'reallyShiny' => 
        array (
          'H' => 310,
          'S' => 97,
          'V' => 100,
          'R' => 255,
          'G' => 7,
          'B' => 213,
          'alpha' => 0,
          'hex' => '#ff07d5',
        ),
      ),
      320 => 
      array (
        'reallyDark' => 
        array (
          'H' => 321,
          'S' => 100,
          'V' => 9,
          'R' => 25,
          'G' => 0,
          'B' => 16,
          'alpha' => 0,
          'hex' => '#190010',
        ),
        'dark' => 
        array (
          'H' => 320,
          'S' => 97,
          'V' => 40,
          'R' => 104,
          'G' => 3,
          'B' => 70,
          'alpha' => 0,
          'hex' => '#680346',
        ),
        'normal' => 
        array (
          'H' => 320,
          'S' => 97,
          'V' => 65,
          'R' => 168,
          'G' => 5,
          'B' => 113,
          'alpha' => 0,
          'hex' => '#a80571',
        ),
        'shiny' => 
        array (
          'H' => 319,
          'S' => 97,
          'V' => 90,
          'R' => 232,
          'G' => 6,
          'B' => 157,
          'alpha' => 0,
          'hex' => '#e8069d',
        ),
        'reallyShiny' => 
        array (
          'H' => 320,
          'S' => 97,
          'V' => 100,
          'R' => 255,
          'G' => 7,
          'B' => 172,
          'alpha' => 0,
          'hex' => '#ff07ac',
        ),
      ),
      330 => 
      array (
        'reallyDark' => 
        array (
          'H' => 328,
          'S' => 100,
          'V' => 9,
          'R' => 25,
          'G' => 0,
          'B' => 13,
          'alpha' => 0,
          'hex' => '#19000d',
        ),
        'dark' => 
        array (
          'H' => 330,
          'S' => 97,
          'V' => 40,
          'R' => 104,
          'G' => 3,
          'B' => 53,
          'alpha' => 0,
          'hex' => '#680335',
        ),
        'normal' => 
        array (
          'H' => 330,
          'S' => 97,
          'V' => 65,
          'R' => 168,
          'G' => 5,
          'B' => 86,
          'alpha' => 0,
          'hex' => '#a80556',
        ),
        'shiny' => 
        array (
          'H' => 329,
          'S' => 97,
          'V' => 90,
          'R' => 232,
          'G' => 6,
          'B' => 119,
          'alpha' => 0,
          'hex' => '#e80677',
        ),
        'reallyShiny' => 
        array (
          'H' => 330,
          'S' => 97,
          'V' => 100,
          'R' => 255,
          'G' => 7,
          'B' => 131,
          'alpha' => 0,
          'hex' => '#ff0783',
        ),
      ),
      340 => 
      array (
        'reallyDark' => 
        array (
          'H' => 340,
          'S' => 100,
          'V' => 9,
          'R' => 25,
          'G' => 0,
          'B' => 8,
          'alpha' => 0,
          'hex' => '#190008',
        ),
        'dark' => 
        array (
          'H' => 340,
          'S' => 97,
          'V' => 40,
          'R' => 104,
          'G' => 3,
          'B' => 36,
          'alpha' => 0,
          'hex' => '#680324',
        ),
        'normal' => 
        array (
          'H' => 340,
          'S' => 97,
          'V' => 65,
          'R' => 168,
          'G' => 5,
          'B' => 59,
          'alpha' => 0,
          'hex' => '#a8053b',
        ),
        'shiny' => 
        array (
          'H' => 340,
          'S' => 97,
          'V' => 90,
          'R' => 232,
          'G' => 6,
          'B' => 81,
          'alpha' => 0,
          'hex' => '#e80651',
        ),
        'reallyShiny' => 
        array (
          'H' => 339,
          'S' => 97,
          'V' => 100,
          'R' => 255,
          'G' => 7,
          'B' => 90,
          'alpha' => 0,
          'hex' => '#ff075a',
        ),
      ),
      350 => 
      array (
        'reallyDark' => 
        array (
          'H' => 350,
          'S' => 100,
          'V' => 9,
          'R' => 25,
          'G' => 0,
          'B' => 4,
          'alpha' => 0,
          'hex' => '#190004',
        ),
        'dark' => 
        array (
          'H' => 349,
          'S' => 97,
          'V' => 40,
          'R' => 104,
          'G' => 3,
          'B' => 20,
          'alpha' => 0,
          'hex' => '#680314',
        ),
        'normal' => 
        array (
          'H' => 350,
          'S' => 97,
          'V' => 65,
          'R' => 168,
          'G' => 5,
          'B' => 32,
          'alpha' => 0,
          'hex' => '#a80520',
        ),
        'shiny' => 
        array (
          'H' => 349,
          'S' => 97,
          'V' => 90,
          'R' => 232,
          'G' => 6,
          'B' => 44,
          'alpha' => 0,
          'hex' => '#e8062c',
        ),
        'reallyShiny' => 
        array (
          'H' => 350,
          'S' => 97,
          'V' => 100,
          'R' => 255,
          'G' => 7,
          'B' => 48,
          'alpha' => 0,
          'hex' => '#ff0730',
        ),
      ),
      360 => 
      array (
        'reallyDark' => 
        array (
          'H' => 0,
          'S' => 0,
          'V' => 9,
          'R' => 25,
          'G' => 25,
          'B' => 25,
          'alpha' => 0,
          'hex' => '#191919',
        ),
        'dark' => 
        array (
          'H' => 0,
          'S' => 0,
          'V' => 40,
          'R' => 104,
          'G' => 104,
          'B' => 104,
          'alpha' => 0,
          'hex' => '#686868',
        ),
        'normal' => 
        array (
          'H' => 0,
          'S' => 0,
          'V' => 65,
          'R' => 168,
          'G' => 168,
          'B' => 168,
          'alpha' => 0,
          'hex' => '#a8a8a8',
        ),
        'shiny' => 
        array (
          'H' => 0,
          'S' => 0,
          'V' => 90,
          'R' => 232,
          'G' => 232,
          'B' => 232,
          'alpha' => 0,
          'hex' => '#e8e8e8',
        ),
        'reallyShiny' => 
        array (
          'H' => 0,
          'S' => 0,
          'V' => 100,
          'R' => 255,
          'G' => 255,
          'B' => 255,
          'alpha' => 0,
          'hex' => '#ffffff',
        ),
      ),
      370 => 
      array (
        'reallyDark' => 
        array (
          'H' => 0,
          'S' => 0,
          'V' => 100,
          'R' => 255,
          'G' => 255,
          'B' => 255,
          'alpha' => 0,
          'hex' => '#ffffff',
        ),
        'dark' => 
        array (
          'H' => 0,
          'S' => 0,
          'V' => 100,
          'R' => 255,
          'G' => 255,
          'B' => 255,
          'alpha' => 0,
          'hex' => '#ffffff',
        ),
        'normal' => 
        array (
          'H' => 0,
          'S' => 0,
          'V' => 100,
          'R' => 255,
          'G' => 255,
          'B' => 255,
          'alpha' => 0,
          'hex' => '#ffffff',
        ),
        'shiny' => 
        array (
          'H' => 0,
          'S' => 0,
          'V' => 100,
          'R' => 255,
          'G' => 255,
          'B' => 255,
          'alpha' => 0,
          'hex' => '#ffffff',
        ),
        'reallyShiny' => 
        array (
          'H' => 0,
          'S' => 0,
          'V' => 100,
          'R' => 255,
          'G' => 255,
          'B' => 255,
          'alpha' => 0,
          'hex' => '#ffffff',
        ),
      ),
    
);
