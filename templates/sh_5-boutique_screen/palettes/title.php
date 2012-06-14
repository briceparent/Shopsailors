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
      'H' => 3,
      'S' => 100,
      'V' => 25,
      'R' => 64,
      'G' => 4,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#400400',
    ),
    'dark' => 
    array (
      'H' => 3,
      'S' => 100,
      'V' => 40,
      'R' => 102,
      'G' => 6,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#660600',
    ),
    'normal' => 
    array (
      'H' => 3,
      'S' => 100,
      'V' => 60,
      'R' => 153,
      'G' => 10,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#990a00',
    ),
    'shiny' => 
    array (
      'H' => 3,
      'S' => 100,
      'V' => 80,
      'R' => 204,
      'G' => 13,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#cc0d00',
    ),
    'reallyShiny' => 
    array (
      'H' => 3,
      'S' => 100,
      'V' => 100,
      'R' => 255,
      'G' => 17,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#ff1100',
    ),
  ),
  10 => 
  array (
    'reallyDark' => 
    array (
      'H' => 14,
      'S' => 100,
      'V' => 25,
      'R' => 64,
      'G' => 15,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#400f00',
    ),
    'dark' => 
    array (
      'H' => 14,
      'S' => 100,
      'V' => 40,
      'R' => 102,
      'G' => 24,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#661800',
    ),
    'normal' => 
    array (
      'H' => 14,
      'S' => 100,
      'V' => 60,
      'R' => 153,
      'G' => 36,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#992400',
    ),
    'shiny' => 
    array (
      'H' => 14,
      'S' => 100,
      'V' => 80,
      'R' => 204,
      'G' => 48,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#cc3000',
    ),
    'reallyShiny' => 
    array (
      'H' => 14,
      'S' => 100,
      'V' => 100,
      'R' => 255,
      'G' => 60,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#ff3c00',
    ),
  ),
  20 => 
  array (
    'reallyDark' => 
    array (
      'H' => 23,
      'S' => 100,
      'V' => 25,
      'R' => 64,
      'G' => 25,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#401900',
    ),
    'dark' => 
    array (
      'H' => 24,
      'S' => 100,
      'V' => 40,
      'R' => 102,
      'G' => 41,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#662900',
    ),
    'normal' => 
    array (
      'H' => 23,
      'S' => 100,
      'V' => 60,
      'R' => 153,
      'G' => 61,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#993d00',
    ),
    'shiny' => 
    array (
      'H' => 24,
      'S' => 100,
      'V' => 80,
      'R' => 204,
      'G' => 82,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#cc5200',
    ),
    'reallyShiny' => 
    array (
      'H' => 24,
      'S' => 100,
      'V' => 100,
      'R' => 255,
      'G' => 103,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#ff6700',
    ),
  ),
  30 => 
  array (
    'reallyDark' => 
    array (
      'H' => 33,
      'S' => 100,
      'V' => 25,
      'R' => 64,
      'G' => 36,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#402400',
    ),
    'dark' => 
    array (
      'H' => 34,
      'S' => 100,
      'V' => 40,
      'R' => 102,
      'G' => 58,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#663a00',
    ),
    'normal' => 
    array (
      'H' => 34,
      'S' => 100,
      'V' => 60,
      'R' => 153,
      'G' => 87,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#995700',
    ),
    'shiny' => 
    array (
      'H' => 34,
      'S' => 100,
      'V' => 80,
      'R' => 204,
      'G' => 116,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#cc7400',
    ),
    'reallyShiny' => 
    array (
      'H' => 34,
      'S' => 100,
      'V' => 100,
      'R' => 255,
      'G' => 146,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#ff9200',
    ),
  ),
  40 => 
  array (
    'reallyDark' => 
    array (
      'H' => 44,
      'S' => 100,
      'V' => 25,
      'R' => 64,
      'G' => 47,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#402f00',
    ),
    'dark' => 
    array (
      'H' => 44,
      'S' => 100,
      'V' => 40,
      'R' => 102,
      'G' => 76,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#664c00',
    ),
    'normal' => 
    array (
      'H' => 44,
      'S' => 100,
      'V' => 60,
      'R' => 153,
      'G' => 114,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#997200',
    ),
    'shiny' => 
    array (
      'H' => 44,
      'S' => 100,
      'V' => 80,
      'R' => 204,
      'G' => 152,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#cc9800',
    ),
    'reallyShiny' => 
    array (
      'H' => 44,
      'S' => 100,
      'V' => 100,
      'R' => 255,
      'G' => 190,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#ffbe00',
    ),
  ),
  50 => 
  array (
    'reallyDark' => 
    array (
      'H' => 54,
      'S' => 100,
      'V' => 25,
      'R' => 64,
      'G' => 58,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#403a00',
    ),
    'dark' => 
    array (
      'H' => 54,
      'S' => 100,
      'V' => 40,
      'R' => 102,
      'G' => 93,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#665d00',
    ),
    'normal' => 
    array (
      'H' => 54,
      'S' => 100,
      'V' => 60,
      'R' => 153,
      'G' => 140,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#998c00',
    ),
    'shiny' => 
    array (
      'H' => 54,
      'S' => 100,
      'V' => 80,
      'R' => 204,
      'G' => 187,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#ccbb00',
    ),
    'reallyShiny' => 
    array (
      'H' => 55,
      'S' => 100,
      'V' => 100,
      'R' => 255,
      'G' => 234,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#ffea00',
    ),
  ),
  60 => 
  array (
    'reallyDark' => 
    array (
      'H' => 65,
      'S' => 100,
      'V' => 25,
      'R' => 58,
      'G' => 64,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#3a4000',
    ),
    'dark' => 
    array (
      'H' => 65,
      'S' => 100,
      'V' => 40,
      'R' => 93,
      'G' => 102,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#5d6600',
    ),
    'normal' => 
    array (
      'H' => 65,
      'S' => 100,
      'V' => 60,
      'R' => 140,
      'G' => 153,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#8c9900',
    ),
    'shiny' => 
    array (
      'H' => 65,
      'S' => 100,
      'V' => 80,
      'R' => 187,
      'G' => 204,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#bbcc00',
    ),
    'reallyShiny' => 
    array (
      'H' => 64,
      'S' => 100,
      'V' => 100,
      'R' => 234,
      'G' => 255,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#eaff00',
    ),
  ),
  70 => 
  array (
    'reallyDark' => 
    array (
      'H' => 75,
      'S' => 100,
      'V' => 25,
      'R' => 47,
      'G' => 64,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#2f4000',
    ),
    'dark' => 
    array (
      'H' => 75,
      'S' => 100,
      'V' => 40,
      'R' => 76,
      'G' => 102,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#4c6600',
    ),
    'normal' => 
    array (
      'H' => 75,
      'S' => 100,
      'V' => 60,
      'R' => 114,
      'G' => 153,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#729900',
    ),
    'shiny' => 
    array (
      'H' => 75,
      'S' => 100,
      'V' => 80,
      'R' => 152,
      'G' => 204,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#98cc00',
    ),
    'reallyShiny' => 
    array (
      'H' => 75,
      'S' => 100,
      'V' => 100,
      'R' => 190,
      'G' => 255,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#beff00',
    ),
  ),
  80 => 
  array (
    'reallyDark' => 
    array (
      'H' => 86,
      'S' => 100,
      'V' => 25,
      'R' => 36,
      'G' => 64,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#244000',
    ),
    'dark' => 
    array (
      'H' => 85,
      'S' => 100,
      'V' => 40,
      'R' => 58,
      'G' => 102,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#3a6600',
    ),
    'normal' => 
    array (
      'H' => 85,
      'S' => 100,
      'V' => 60,
      'R' => 88,
      'G' => 153,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#589900',
    ),
    'shiny' => 
    array (
      'H' => 85,
      'S' => 100,
      'V' => 80,
      'R' => 117,
      'G' => 204,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#75cc00',
    ),
    'reallyShiny' => 
    array (
      'H' => 85,
      'S' => 100,
      'V' => 100,
      'R' => 147,
      'G' => 255,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#93ff00',
    ),
  ),
  90 => 
  array (
    'reallyDark' => 
    array (
      'H' => 96,
      'S' => 100,
      'V' => 25,
      'R' => 25,
      'G' => 64,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#194000',
    ),
    'dark' => 
    array (
      'H' => 95,
      'S' => 100,
      'V' => 40,
      'R' => 41,
      'G' => 102,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#296600',
    ),
    'normal' => 
    array (
      'H' => 96,
      'S' => 100,
      'V' => 60,
      'R' => 61,
      'G' => 153,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#3d9900',
    ),
    'shiny' => 
    array (
      'H' => 95,
      'S' => 100,
      'V' => 80,
      'R' => 82,
      'G' => 204,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#52cc00',
    ),
    'reallyShiny' => 
    array (
      'H' => 95,
      'S' => 100,
      'V' => 100,
      'R' => 103,
      'G' => 255,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#67ff00',
    ),
  ),
  100 => 
  array (
    'reallyDark' => 
    array (
      'H' => 105,
      'S' => 100,
      'V' => 25,
      'R' => 15,
      'G' => 64,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#0f4000',
    ),
    'dark' => 
    array (
      'H' => 105,
      'S' => 100,
      'V' => 40,
      'R' => 24,
      'G' => 102,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#186600',
    ),
    'normal' => 
    array (
      'H' => 105,
      'S' => 100,
      'V' => 60,
      'R' => 36,
      'G' => 153,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#249900',
    ),
    'shiny' => 
    array (
      'H' => 105,
      'S' => 100,
      'V' => 80,
      'R' => 48,
      'G' => 204,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#30cc00',
    ),
    'reallyShiny' => 
    array (
      'H' => 105,
      'S' => 100,
      'V' => 100,
      'R' => 60,
      'G' => 255,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#3cff00',
    ),
  ),
  110 => 
  array (
    'reallyDark' => 
    array (
      'H' => 116,
      'S' => 100,
      'V' => 25,
      'R' => 4,
      'G' => 64,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#044000',
    ),
    'dark' => 
    array (
      'H' => 115,
      'S' => 100,
      'V' => 40,
      'R' => 7,
      'G' => 102,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#076600',
    ),
    'normal' => 
    array (
      'H' => 116,
      'S' => 100,
      'V' => 60,
      'R' => 10,
      'G' => 153,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#0a9900',
    ),
    'shiny' => 
    array (
      'H' => 115,
      'S' => 100,
      'V' => 80,
      'R' => 14,
      'G' => 204,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#0ecc00',
    ),
    'reallyShiny' => 
    array (
      'H' => 115,
      'S' => 100,
      'V' => 100,
      'R' => 18,
      'G' => 255,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#12ff00',
    ),
  ),
  120 => 
  array (
    'reallyDark' => 
    array (
      'H' => 125,
      'S' => 100,
      'V' => 25,
      'R' => 0,
      'G' => 64,
      'B' => 6,
      'alpha' => 0,
      'hex' => '#004006',
    ),
    'dark' => 
    array (
      'H' => 125,
      'S' => 100,
      'V' => 40,
      'R' => 0,
      'G' => 102,
      'B' => 10,
      'alpha' => 0,
      'hex' => '#00660a',
    ),
    'normal' => 
    array (
      'H' => 125,
      'S' => 100,
      'V' => 60,
      'R' => 0,
      'G' => 153,
      'B' => 15,
      'alpha' => 0,
      'hex' => '#00990f',
    ),
    'shiny' => 
    array (
      'H' => 125,
      'S' => 100,
      'V' => 80,
      'R' => 0,
      'G' => 204,
      'B' => 20,
      'alpha' => 0,
      'hex' => '#00cc14',
    ),
    'reallyShiny' => 
    array (
      'H' => 126,
      'S' => 100,
      'V' => 100,
      'R' => 0,
      'G' => 255,
      'B' => 26,
      'alpha' => 0,
      'hex' => '#00ff1a',
    ),
  ),
  130 => 
  array (
    'reallyDark' => 
    array (
      'H' => 135,
      'S' => 100,
      'V' => 25,
      'R' => 0,
      'G' => 64,
      'B' => 17,
      'alpha' => 0,
      'hex' => '#004011',
    ),
    'dark' => 
    array (
      'H' => 135,
      'S' => 100,
      'V' => 40,
      'R' => 0,
      'G' => 102,
      'B' => 27,
      'alpha' => 0,
      'hex' => '#00661b',
    ),
    'normal' => 
    array (
      'H' => 136,
      'S' => 100,
      'V' => 60,
      'R' => 0,
      'G' => 153,
      'B' => 41,
      'alpha' => 0,
      'hex' => '#009929',
    ),
    'shiny' => 
    array (
      'H' => 136,
      'S' => 100,
      'V' => 80,
      'R' => 0,
      'G' => 204,
      'B' => 55,
      'alpha' => 0,
      'hex' => '#00cc37',
    ),
    'reallyShiny' => 
    array (
      'H' => 136,
      'S' => 100,
      'V' => 100,
      'R' => 0,
      'G' => 255,
      'B' => 69,
      'alpha' => 0,
      'hex' => '#00ff45',
    ),
  ),
  140 => 
  array (
    'reallyDark' => 
    array (
      'H' => 146,
      'S' => 100,
      'V' => 25,
      'R' => 0,
      'G' => 64,
      'B' => 28,
      'alpha' => 0,
      'hex' => '#00401c',
    ),
    'dark' => 
    array (
      'H' => 146,
      'S' => 100,
      'V' => 40,
      'R' => 0,
      'G' => 102,
      'B' => 45,
      'alpha' => 0,
      'hex' => '#00662d',
    ),
    'normal' => 
    array (
      'H' => 146,
      'S' => 100,
      'V' => 60,
      'R' => 0,
      'G' => 153,
      'B' => 67,
      'alpha' => 0,
      'hex' => '#009943',
    ),
    'shiny' => 
    array (
      'H' => 146,
      'S' => 100,
      'V' => 80,
      'R' => 0,
      'G' => 204,
      'B' => 90,
      'alpha' => 0,
      'hex' => '#00cc5a',
    ),
    'reallyShiny' => 
    array (
      'H' => 146,
      'S' => 100,
      'V' => 100,
      'R' => 0,
      'G' => 255,
      'B' => 113,
      'alpha' => 0,
      'hex' => '#00ff71',
    ),
  ),
  150 => 
  array (
    'reallyDark' => 
    array (
      'H' => 156,
      'S' => 100,
      'V' => 25,
      'R' => 0,
      'G' => 64,
      'B' => 39,
      'alpha' => 0,
      'hex' => '#004027',
    ),
    'dark' => 
    array (
      'H' => 156,
      'S' => 100,
      'V' => 40,
      'R' => 0,
      'G' => 102,
      'B' => 62,
      'alpha' => 0,
      'hex' => '#00663e',
    ),
    'normal' => 
    array (
      'H' => 156,
      'S' => 100,
      'V' => 60,
      'R' => 0,
      'G' => 153,
      'B' => 93,
      'alpha' => 0,
      'hex' => '#00995d',
    ),
    'shiny' => 
    array (
      'H' => 156,
      'S' => 100,
      'V' => 80,
      'R' => 0,
      'G' => 204,
      'B' => 124,
      'alpha' => 0,
      'hex' => '#00cc7c',
    ),
    'reallyShiny' => 
    array (
      'H' => 156,
      'S' => 100,
      'V' => 100,
      'R' => 0,
      'G' => 255,
      'B' => 156,
      'alpha' => 0,
      'hex' => '#00ff9c',
    ),
  ),
  160 => 
  array (
    'reallyDark' => 
    array (
      'H' => 165,
      'S' => 100,
      'V' => 25,
      'R' => 0,
      'G' => 64,
      'B' => 49,
      'alpha' => 0,
      'hex' => '#004031',
    ),
    'dark' => 
    array (
      'H' => 166,
      'S' => 100,
      'V' => 40,
      'R' => 0,
      'G' => 102,
      'B' => 79,
      'alpha' => 0,
      'hex' => '#00664f',
    ),
    'normal' => 
    array (
      'H' => 166,
      'S' => 100,
      'V' => 60,
      'R' => 0,
      'G' => 153,
      'B' => 119,
      'alpha' => 0,
      'hex' => '#009977',
    ),
    'shiny' => 
    array (
      'H' => 166,
      'S' => 100,
      'V' => 80,
      'R' => 0,
      'G' => 204,
      'B' => 159,
      'alpha' => 0,
      'hex' => '#00cc9f',
    ),
    'reallyShiny' => 
    array (
      'H' => 166,
      'S' => 100,
      'V' => 100,
      'R' => 0,
      'G' => 255,
      'B' => 199,
      'alpha' => 0,
      'hex' => '#00ffc7',
    ),
  ),
  170 => 
  array (
    'reallyDark' => 
    array (
      'H' => 176,
      'S' => 100,
      'V' => 25,
      'R' => 0,
      'G' => 64,
      'B' => 60,
      'alpha' => 0,
      'hex' => '#00403c',
    ),
    'dark' => 
    array (
      'H' => 176,
      'S' => 100,
      'V' => 40,
      'R' => 0,
      'G' => 102,
      'B' => 96,
      'alpha' => 0,
      'hex' => '#006660',
    ),
    'normal' => 
    array (
      'H' => 176,
      'S' => 100,
      'V' => 60,
      'R' => 0,
      'G' => 153,
      'B' => 145,
      'alpha' => 0,
      'hex' => '#009991',
    ),
    'shiny' => 
    array (
      'H' => 176,
      'S' => 100,
      'V' => 80,
      'R' => 0,
      'G' => 204,
      'B' => 193,
      'alpha' => 0,
      'hex' => '#00ccc1',
    ),
    'reallyShiny' => 
    array (
      'H' => 176,
      'S' => 100,
      'V' => 100,
      'R' => 0,
      'G' => 255,
      'B' => 242,
      'alpha' => 0,
      'hex' => '#00fff2',
    ),
  ),
  180 => 
  array (
    'reallyDark' => 
    array (
      'H' => 187,
      'S' => 100,
      'V' => 25,
      'R' => 0,
      'G' => 56,
      'B' => 64,
      'alpha' => 0,
      'hex' => '#003840',
    ),
    'dark' => 
    array (
      'H' => 187,
      'S' => 100,
      'V' => 40,
      'R' => 0,
      'G' => 90,
      'B' => 102,
      'alpha' => 0,
      'hex' => '#005a66',
    ),
    'normal' => 
    array (
      'H' => 187,
      'S' => 100,
      'V' => 60,
      'R' => 0,
      'G' => 135,
      'B' => 153,
      'alpha' => 0,
      'hex' => '#008799',
    ),
    'shiny' => 
    array (
      'H' => 187,
      'S' => 100,
      'V' => 80,
      'R' => 0,
      'G' => 180,
      'B' => 204,
      'alpha' => 0,
      'hex' => '#00b4cc',
    ),
    'reallyShiny' => 
    array (
      'H' => 187,
      'S' => 100,
      'V' => 100,
      'R' => 0,
      'G' => 225,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#00e1ff',
    ),
  ),
  190 => 
  array (
    'reallyDark' => 
    array (
      'H' => 197,
      'S' => 100,
      'V' => 25,
      'R' => 0,
      'G' => 45,
      'B' => 64,
      'alpha' => 0,
      'hex' => '#002d40',
    ),
    'dark' => 
    array (
      'H' => 197,
      'S' => 100,
      'V' => 40,
      'R' => 0,
      'G' => 72,
      'B' => 102,
      'alpha' => 0,
      'hex' => '#004866',
    ),
    'normal' => 
    array (
      'H' => 197,
      'S' => 100,
      'V' => 60,
      'R' => 0,
      'G' => 108,
      'B' => 153,
      'alpha' => 0,
      'hex' => '#006c99',
    ),
    'shiny' => 
    array (
      'H' => 197,
      'S' => 100,
      'V' => 80,
      'R' => 0,
      'G' => 144,
      'B' => 204,
      'alpha' => 0,
      'hex' => '#0090cc',
    ),
    'reallyShiny' => 
    array (
      'H' => 197,
      'S' => 100,
      'V' => 100,
      'R' => 0,
      'G' => 181,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#00b5ff',
    ),
  ),
  200 => 
  array (
    'reallyDark' => 
    array (
      'H' => 208,
      'S' => 100,
      'V' => 25,
      'R' => 0,
      'G' => 34,
      'B' => 64,
      'alpha' => 0,
      'hex' => '#002240',
    ),
    'dark' => 
    array (
      'H' => 207,
      'S' => 100,
      'V' => 40,
      'R' => 0,
      'G' => 55,
      'B' => 102,
      'alpha' => 0,
      'hex' => '#003766',
    ),
    'normal' => 
    array (
      'H' => 207,
      'S' => 100,
      'V' => 60,
      'R' => 0,
      'G' => 82,
      'B' => 153,
      'alpha' => 0,
      'hex' => '#005299',
    ),
    'shiny' => 
    array (
      'H' => 207,
      'S' => 100,
      'V' => 80,
      'R' => 0,
      'G' => 110,
      'B' => 204,
      'alpha' => 0,
      'hex' => '#006ecc',
    ),
    'reallyShiny' => 
    array (
      'H' => 207,
      'S' => 100,
      'V' => 100,
      'R' => 0,
      'G' => 138,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#008aff',
    ),
  ),
  210 => 
  array (
    'reallyDark' => 
    array (
      'H' => 218,
      'S' => 100,
      'V' => 25,
      'R' => 0,
      'G' => 23,
      'B' => 64,
      'alpha' => 0,
      'hex' => '#001740',
    ),
    'dark' => 
    array (
      'H' => 217,
      'S' => 100,
      'V' => 40,
      'R' => 0,
      'G' => 38,
      'B' => 102,
      'alpha' => 0,
      'hex' => '#002666',
    ),
    'normal' => 
    array (
      'H' => 217,
      'S' => 100,
      'V' => 60,
      'R' => 0,
      'G' => 57,
      'B' => 153,
      'alpha' => 0,
      'hex' => '#003999',
    ),
    'shiny' => 
    array (
      'H' => 217,
      'S' => 100,
      'V' => 80,
      'R' => 0,
      'G' => 76,
      'B' => 204,
      'alpha' => 0,
      'hex' => '#004ccc',
    ),
    'reallyShiny' => 
    array (
      'H' => 217,
      'S' => 100,
      'V' => 100,
      'R' => 0,
      'G' => 95,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#005fff',
    ),
  ),
  220 => 
  array (
    'reallyDark' => 
    array (
      'H' => 228,
      'S' => 100,
      'V' => 25,
      'R' => 0,
      'G' => 12,
      'B' => 64,
      'alpha' => 0,
      'hex' => '#000c40',
    ),
    'dark' => 
    array (
      'H' => 228,
      'S' => 100,
      'V' => 40,
      'R' => 0,
      'G' => 20,
      'B' => 102,
      'alpha' => 0,
      'hex' => '#001466',
    ),
    'normal' => 
    array (
      'H' => 228,
      'S' => 100,
      'V' => 60,
      'R' => 0,
      'G' => 30,
      'B' => 153,
      'alpha' => 0,
      'hex' => '#001e99',
    ),
    'shiny' => 
    array (
      'H' => 228,
      'S' => 100,
      'V' => 80,
      'R' => 0,
      'G' => 40,
      'B' => 204,
      'alpha' => 0,
      'hex' => '#0028cc',
    ),
    'reallyShiny' => 
    array (
      'H' => 227,
      'S' => 100,
      'V' => 100,
      'R' => 0,
      'G' => 51,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#0033ff',
    ),
  ),
  230 => 
  array (
    'reallyDark' => 
    array (
      'H' => 238,
      'S' => 100,
      'V' => 25,
      'R' => 0,
      'G' => 2,
      'B' => 64,
      'alpha' => 0,
      'hex' => '#000240',
    ),
    'dark' => 
    array (
      'H' => 238,
      'S' => 100,
      'V' => 40,
      'R' => 0,
      'G' => 3,
      'B' => 102,
      'alpha' => 0,
      'hex' => '#000366',
    ),
    'normal' => 
    array (
      'H' => 238,
      'S' => 100,
      'V' => 60,
      'R' => 0,
      'G' => 4,
      'B' => 153,
      'alpha' => 0,
      'hex' => '#000499',
    ),
    'shiny' => 
    array (
      'H' => 238,
      'S' => 100,
      'V' => 80,
      'R' => 0,
      'G' => 6,
      'B' => 204,
      'alpha' => 0,
      'hex' => '#0006cc',
    ),
    'reallyShiny' => 
    array (
      'H' => 238,
      'S' => 100,
      'V' => 100,
      'R' => 0,
      'G' => 8,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#0008ff',
    ),
  ),
  240 => 
  array (
    'reallyDark' => 
    array (
      'H' => 247,
      'S' => 100,
      'V' => 25,
      'R' => 8,
      'G' => 0,
      'B' => 64,
      'alpha' => 0,
      'hex' => '#080040',
    ),
    'dark' => 
    array (
      'H' => 248,
      'S' => 100,
      'V' => 40,
      'R' => 14,
      'G' => 0,
      'B' => 102,
      'alpha' => 0,
      'hex' => '#0e0066',
    ),
    'normal' => 
    array (
      'H' => 248,
      'S' => 100,
      'V' => 60,
      'R' => 21,
      'G' => 0,
      'B' => 153,
      'alpha' => 0,
      'hex' => '#150099',
    ),
    'shiny' => 
    array (
      'H' => 248,
      'S' => 100,
      'V' => 80,
      'R' => 28,
      'G' => 0,
      'B' => 204,
      'alpha' => 0,
      'hex' => '#1c00cc',
    ),
    'reallyShiny' => 
    array (
      'H' => 248,
      'S' => 100,
      'V' => 100,
      'R' => 35,
      'G' => 0,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#2300ff',
    ),
  ),
  250 => 
  array (
    'reallyDark' => 
    array (
      'H' => 257,
      'S' => 100,
      'V' => 25,
      'R' => 19,
      'G' => 0,
      'B' => 64,
      'alpha' => 0,
      'hex' => '#130040',
    ),
    'dark' => 
    array (
      'H' => 258,
      'S' => 100,
      'V' => 40,
      'R' => 31,
      'G' => 0,
      'B' => 102,
      'alpha' => 0,
      'hex' => '#1f0066',
    ),
    'normal' => 
    array (
      'H' => 258,
      'S' => 100,
      'V' => 60,
      'R' => 46,
      'G' => 0,
      'B' => 153,
      'alpha' => 0,
      'hex' => '#2e0099',
    ),
    'shiny' => 
    array (
      'H' => 258,
      'S' => 100,
      'V' => 80,
      'R' => 62,
      'G' => 0,
      'B' => 204,
      'alpha' => 0,
      'hex' => '#3e00cc',
    ),
    'reallyShiny' => 
    array (
      'H' => 258,
      'S' => 100,
      'V' => 100,
      'R' => 78,
      'G' => 0,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#4e00ff',
    ),
  ),
  260 => 
  array (
    'reallyDark' => 
    array (
      'H' => 268,
      'S' => 100,
      'V' => 25,
      'R' => 30,
      'G' => 0,
      'B' => 64,
      'alpha' => 0,
      'hex' => '#1e0040',
    ),
    'dark' => 
    array (
      'H' => 268,
      'S' => 100,
      'V' => 40,
      'R' => 48,
      'G' => 0,
      'B' => 102,
      'alpha' => 0,
      'hex' => '#300066',
    ),
    'normal' => 
    array (
      'H' => 268,
      'S' => 100,
      'V' => 60,
      'R' => 73,
      'G' => 0,
      'B' => 153,
      'alpha' => 0,
      'hex' => '#490099',
    ),
    'shiny' => 
    array (
      'H' => 268,
      'S' => 100,
      'V' => 80,
      'R' => 97,
      'G' => 0,
      'B' => 204,
      'alpha' => 0,
      'hex' => '#6100cc',
    ),
    'reallyShiny' => 
    array (
      'H' => 268,
      'S' => 100,
      'V' => 100,
      'R' => 122,
      'G' => 0,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#7a00ff',
    ),
  ),
  270 => 
  array (
    'reallyDark' => 
    array (
      'H' => 278,
      'S' => 100,
      'V' => 25,
      'R' => 41,
      'G' => 0,
      'B' => 64,
      'alpha' => 0,
      'hex' => '#290040',
    ),
    'dark' => 
    array (
      'H' => 278,
      'S' => 100,
      'V' => 40,
      'R' => 66,
      'G' => 0,
      'B' => 102,
      'alpha' => 0,
      'hex' => '#420066',
    ),
    'normal' => 
    array (
      'H' => 278,
      'S' => 100,
      'V' => 60,
      'R' => 99,
      'G' => 0,
      'B' => 153,
      'alpha' => 0,
      'hex' => '#630099',
    ),
    'shiny' => 
    array (
      'H' => 278,
      'S' => 100,
      'V' => 80,
      'R' => 132,
      'G' => 0,
      'B' => 204,
      'alpha' => 0,
      'hex' => '#8400cc',
    ),
    'reallyShiny' => 
    array (
      'H' => 278,
      'S' => 100,
      'V' => 100,
      'R' => 165,
      'G' => 0,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#a500ff',
    ),
  ),
  280 => 
  array (
    'reallyDark' => 
    array (
      'H' => 288,
      'S' => 100,
      'V' => 25,
      'R' => 52,
      'G' => 0,
      'B' => 64,
      'alpha' => 0,
      'hex' => '#340040',
    ),
    'dark' => 
    array (
      'H' => 288,
      'S' => 100,
      'V' => 40,
      'R' => 83,
      'G' => 0,
      'B' => 102,
      'alpha' => 0,
      'hex' => '#530066',
    ),
    'normal' => 
    array (
      'H' => 288,
      'S' => 100,
      'V' => 60,
      'R' => 124,
      'G' => 0,
      'B' => 153,
      'alpha' => 0,
      'hex' => '#7c0099',
    ),
    'shiny' => 
    array (
      'H' => 288,
      'S' => 100,
      'V' => 80,
      'R' => 166,
      'G' => 0,
      'B' => 204,
      'alpha' => 0,
      'hex' => '#a600cc',
    ),
    'reallyShiny' => 
    array (
      'H' => 288,
      'S' => 100,
      'V' => 100,
      'R' => 208,
      'G' => 0,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#d000ff',
    ),
  ),
  290 => 
  array (
    'reallyDark' => 
    array (
      'H' => 298,
      'S' => 100,
      'V' => 25,
      'R' => 62,
      'G' => 0,
      'B' => 64,
      'alpha' => 0,
      'hex' => '#3e0040',
    ),
    'dark' => 
    array (
      'H' => 298,
      'S' => 100,
      'V' => 40,
      'R' => 100,
      'G' => 0,
      'B' => 102,
      'alpha' => 0,
      'hex' => '#640066',
    ),
    'normal' => 
    array (
      'H' => 298,
      'S' => 100,
      'V' => 60,
      'R' => 150,
      'G' => 0,
      'B' => 153,
      'alpha' => 0,
      'hex' => '#960099',
    ),
    'shiny' => 
    array (
      'H' => 298,
      'S' => 100,
      'V' => 80,
      'R' => 200,
      'G' => 0,
      'B' => 204,
      'alpha' => 0,
      'hex' => '#c800cc',
    ),
    'reallyShiny' => 
    array (
      'H' => 299,
      'S' => 100,
      'V' => 100,
      'R' => 251,
      'G' => 0,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#fb00ff',
    ),
  ),
  300 => 
  array (
    'reallyDark' => 
    array (
      'H' => 309,
      'S' => 100,
      'V' => 25,
      'R' => 64,
      'G' => 0,
      'B' => 54,
      'alpha' => 0,
      'hex' => '#400036',
    ),
    'dark' => 
    array (
      'H' => 309,
      'S' => 100,
      'V' => 40,
      'R' => 102,
      'G' => 0,
      'B' => 86,
      'alpha' => 0,
      'hex' => '#660056',
    ),
    'normal' => 
    array (
      'H' => 309,
      'S' => 100,
      'V' => 60,
      'R' => 153,
      'G' => 0,
      'B' => 129,
      'alpha' => 0,
      'hex' => '#990081',
    ),
    'shiny' => 
    array (
      'H' => 309,
      'S' => 100,
      'V' => 80,
      'R' => 204,
      'G' => 0,
      'B' => 172,
      'alpha' => 0,
      'hex' => '#cc00ac',
    ),
    'reallyShiny' => 
    array (
      'H' => 309,
      'S' => 100,
      'V' => 100,
      'R' => 255,
      'G' => 0,
      'B' => 216,
      'alpha' => 0,
      'hex' => '#ff00d8',
    ),
  ),
  310 => 
  array (
    'reallyDark' => 
    array (
      'H' => 319,
      'S' => 100,
      'V' => 25,
      'R' => 64,
      'G' => 0,
      'B' => 43,
      'alpha' => 0,
      'hex' => '#40002b',
    ),
    'dark' => 
    array (
      'H' => 320,
      'S' => 100,
      'V' => 40,
      'R' => 102,
      'G' => 0,
      'B' => 68,
      'alpha' => 0,
      'hex' => '#660044',
    ),
    'normal' => 
    array (
      'H' => 319,
      'S' => 100,
      'V' => 60,
      'R' => 153,
      'G' => 0,
      'B' => 103,
      'alpha' => 0,
      'hex' => '#990067',
    ),
    'shiny' => 
    array (
      'H' => 319,
      'S' => 100,
      'V' => 80,
      'R' => 204,
      'G' => 0,
      'B' => 137,
      'alpha' => 0,
      'hex' => '#cc0089',
    ),
    'reallyShiny' => 
    array (
      'H' => 319,
      'S' => 100,
      'V' => 100,
      'R' => 255,
      'G' => 0,
      'B' => 172,
      'alpha' => 0,
      'hex' => '#ff00ac',
    ),
  ),
  320 => 
  array (
    'reallyDark' => 
    array (
      'H' => 330,
      'S' => 100,
      'V' => 25,
      'R' => 64,
      'G' => 0,
      'B' => 32,
      'alpha' => 0,
      'hex' => '#400020',
    ),
    'dark' => 
    array (
      'H' => 330,
      'S' => 100,
      'V' => 40,
      'R' => 102,
      'G' => 0,
      'B' => 51,
      'alpha' => 0,
      'hex' => '#660033',
    ),
    'normal' => 
    array (
      'H' => 329,
      'S' => 100,
      'V' => 60,
      'R' => 153,
      'G' => 0,
      'B' => 77,
      'alpha' => 0,
      'hex' => '#99004d',
    ),
    'shiny' => 
    array (
      'H' => 329,
      'S' => 100,
      'V' => 80,
      'R' => 204,
      'G' => 0,
      'B' => 103,
      'alpha' => 0,
      'hex' => '#cc0067',
    ),
    'reallyShiny' => 
    array (
      'H' => 329,
      'S' => 100,
      'V' => 100,
      'R' => 255,
      'G' => 0,
      'B' => 129,
      'alpha' => 0,
      'hex' => '#ff0081',
    ),
  ),
  330 => 
  array (
    'reallyDark' => 
    array (
      'H' => 340,
      'S' => 100,
      'V' => 25,
      'R' => 64,
      'G' => 0,
      'B' => 21,
      'alpha' => 0,
      'hex' => '#400015',
    ),
    'dark' => 
    array (
      'H' => 340,
      'S' => 100,
      'V' => 40,
      'R' => 102,
      'G' => 0,
      'B' => 34,
      'alpha' => 0,
      'hex' => '#660022',
    ),
    'normal' => 
    array (
      'H' => 340,
      'S' => 100,
      'V' => 60,
      'R' => 153,
      'G' => 0,
      'B' => 51,
      'alpha' => 0,
      'hex' => '#990033',
    ),
    'shiny' => 
    array (
      'H' => 340,
      'S' => 100,
      'V' => 80,
      'R' => 204,
      'G' => 0,
      'B' => 68,
      'alpha' => 0,
      'hex' => '#cc0044',
    ),
    'reallyShiny' => 
    array (
      'H' => 340,
      'S' => 100,
      'V' => 100,
      'R' => 255,
      'G' => 0,
      'B' => 85,
      'alpha' => 0,
      'hex' => '#ff0055',
    ),
  ),
  340 => 
  array (
    'reallyDark' => 
    array (
      'H' => 350,
      'S' => 100,
      'V' => 25,
      'R' => 64,
      'G' => 0,
      'B' => 10,
      'alpha' => 0,
      'hex' => '#40000a',
    ),
    'dark' => 
    array (
      'H' => 350,
      'S' => 100,
      'V' => 40,
      'R' => 102,
      'G' => 0,
      'B' => 16,
      'alpha' => 0,
      'hex' => '#660010',
    ),
    'normal' => 
    array (
      'H' => 350,
      'S' => 100,
      'V' => 60,
      'R' => 153,
      'G' => 0,
      'B' => 25,
      'alpha' => 0,
      'hex' => '#990019',
    ),
    'shiny' => 
    array (
      'H' => 350,
      'S' => 100,
      'V' => 80,
      'R' => 204,
      'G' => 0,
      'B' => 33,
      'alpha' => 0,
      'hex' => '#cc0021',
    ),
    'reallyShiny' => 
    array (
      'H' => 350,
      'S' => 100,
      'V' => 100,
      'R' => 255,
      'G' => 0,
      'B' => 42,
      'alpha' => 0,
      'hex' => '#ff002a',
    ),
  ),
  350 => 
  array (
    'reallyDark' => 
    array (
      'H' => 0,
      'S' => 0,
      'V' => 4,
      'R' => 12,
      'G' => 12,
      'B' => 12,
      'alpha' => 0,
      'hex' => '#0c0c0c',
    ),
    'dark' => 
    array (
      'H' => 0,
      'S' => 0,
      'V' => 7,
      'R' => 20,
      'G' => 20,
      'B' => 20,
      'alpha' => 0,
      'hex' => '#141414',
    ),
    'normal' => 
    array (
      'H' => 0,
      'S' => 0,
      'V' => 11,
      'R' => 30,
      'G' => 30,
      'B' => 30,
      'alpha' => 0,
      'hex' => '#1e1e1e',
    ),
    'shiny' => 
    array (
      'H' => 0,
      'S' => 0,
      'V' => 15,
      'R' => 40,
      'G' => 40,
      'B' => 40,
      'alpha' => 0,
      'hex' => '#282828',
    ),
    'reallyShiny' => 
    array (
      'H' => 0,
      'S' => 0,
      'V' => 20,
      'R' => 51,
      'G' => 51,
      'B' => 51,
      'alpha' => 0,
      'hex' => '#333333',
    ),
  ),
  360 => 
  array (
    'reallyDark' => 
    array (
      'H' => 0,
      'S' => 0,
      'V' => 4,
      'R' => 12,
      'G' => 12,
      'B' => 12,
      'alpha' => 0,
      'hex' => '#0c0c0c',
    ),
    'dark' => 
    array (
      'H' => 0,
      'S' => 0,
      'V' => 7,
      'R' => 20,
      'G' => 20,
      'B' => 20,
      'alpha' => 0,
      'hex' => '#141414',
    ),
    'normal' => 
    array (
      'H' => 0,
      'S' => 0,
      'V' => 11,
      'R' => 30,
      'G' => 30,
      'B' => 30,
      'alpha' => 0,
      'hex' => '#1e1e1e',
    ),
    'shiny' => 
    array (
      'H' => 0,
      'S' => 0,
      'V' => 15,
      'R' => 40,
      'G' => 40,
      'B' => 40,
      'alpha' => 0,
      'hex' => '#282828',
    ),
    'reallyShiny' => 
    array (
      'H' => 0,
      'S' => 0,
      'V' => 20,
      'R' => 51,
      'G' => 51,
      'B' => 51,
      'alpha' => 0,
      'hex' => '#333333',
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