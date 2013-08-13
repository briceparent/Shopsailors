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
      'V' => 29,
      'R' => 76,
      'G' => 5,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#4c0500',
    ),
    'dark' => 
    array (
      'H' => 3,
      'S' => 100,
      'V' => 50,
      'R' => 128,
      'G' => 8,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#800800',
    ),
    'normal' => 
    array (
      'H' => 3,
      'S' => 100,
      'V' => 70,
      'R' => 179,
      'G' => 11,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#b30b00',
    ),
    'shiny' => 
    array (
      'H' => 3,
      'S' => 100,
      'V' => 90,
      'R' => 230,
      'G' => 15,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#e60f00',
    ),
    'reallyShiny' => 
    array (
      'H' => 4,
      'S' => 67,
      'V' => 100,
      'R' => 255,
      'G' => 94,
      'B' => 82,
      'alpha' => 0,
      'hex' => '#ff5e52',
    ),
  ),
  10 => 
  array (
    'reallyDark' => 
    array (
      'H' => 13,
      'S' => 100,
      'V' => 29,
      'R' => 76,
      'G' => 17,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#4c1100',
    ),
    'dark' => 
    array (
      'H' => 14,
      'S' => 100,
      'V' => 50,
      'R' => 128,
      'G' => 30,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#801e00',
    ),
    'normal' => 
    array (
      'H' => 14,
      'S' => 100,
      'V' => 70,
      'R' => 179,
      'G' => 42,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#b32a00',
    ),
    'shiny' => 
    array (
      'H' => 14,
      'S' => 100,
      'V' => 90,
      'R' => 230,
      'G' => 54,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#e63600',
    ),
    'reallyShiny' => 
    array (
      'H' => 14,
      'S' => 67,
      'V' => 100,
      'R' => 255,
      'G' => 123,
      'B' => 82,
      'alpha' => 0,
      'hex' => '#ff7b52',
    ),
  ),
  20 => 
  array (
    'reallyDark' => 
    array (
      'H' => 23,
      'S' => 100,
      'V' => 29,
      'R' => 76,
      'G' => 30,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#4c1e00',
    ),
    'dark' => 
    array (
      'H' => 23,
      'S' => 100,
      'V' => 50,
      'R' => 128,
      'G' => 51,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#803300',
    ),
    'normal' => 
    array (
      'H' => 24,
      'S' => 100,
      'V' => 70,
      'R' => 179,
      'G' => 72,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#b34800',
    ),
    'shiny' => 
    array (
      'H' => 23,
      'S' => 100,
      'V' => 90,
      'R' => 230,
      'G' => 92,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#e65c00',
    ),
    'reallyShiny' => 
    array (
      'H' => 24,
      'S' => 67,
      'V' => 100,
      'R' => 255,
      'G' => 152,
      'B' => 82,
      'alpha' => 0,
      'hex' => '#ff9852',
    ),
  ),
  30 => 
  array (
    'reallyDark' => 
    array (
      'H' => 33,
      'S' => 100,
      'V' => 29,
      'R' => 76,
      'G' => 43,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#4c2b00',
    ),
    'dark' => 
    array (
      'H' => 34,
      'S' => 100,
      'V' => 50,
      'R' => 128,
      'G' => 73,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#804900',
    ),
    'normal' => 
    array (
      'H' => 34,
      'S' => 100,
      'V' => 70,
      'R' => 179,
      'G' => 102,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#b36600',
    ),
    'shiny' => 
    array (
      'H' => 34,
      'S' => 100,
      'V' => 90,
      'R' => 230,
      'G' => 131,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#e68300',
    ),
    'reallyShiny' => 
    array (
      'H' => 34,
      'S' => 67,
      'V' => 100,
      'R' => 255,
      'G' => 181,
      'B' => 82,
      'alpha' => 0,
      'hex' => '#ffb552',
    ),
  ),
  40 => 
  array (
    'reallyDark' => 
    array (
      'H' => 44,
      'S' => 100,
      'V' => 29,
      'R' => 76,
      'G' => 56,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#4c3800',
    ),
    'dark' => 
    array (
      'H' => 44,
      'S' => 100,
      'V' => 50,
      'R' => 128,
      'G' => 95,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#805f00',
    ),
    'normal' => 
    array (
      'H' => 44,
      'S' => 100,
      'V' => 70,
      'R' => 179,
      'G' => 133,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#b38500',
    ),
    'shiny' => 
    array (
      'H' => 44,
      'S' => 100,
      'V' => 90,
      'R' => 230,
      'G' => 171,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#e6ab00',
    ),
    'reallyShiny' => 
    array (
      'H' => 44,
      'S' => 67,
      'V' => 100,
      'R' => 255,
      'G' => 211,
      'B' => 82,
      'alpha' => 0,
      'hex' => '#ffd352',
    ),
  ),
  50 => 
  array (
    'reallyDark' => 
    array (
      'H' => 54,
      'S' => 100,
      'V' => 29,
      'R' => 76,
      'G' => 69,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#4c4500',
    ),
    'dark' => 
    array (
      'H' => 54,
      'S' => 100,
      'V' => 50,
      'R' => 128,
      'G' => 117,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#807500',
    ),
    'normal' => 
    array (
      'H' => 54,
      'S' => 100,
      'V' => 70,
      'R' => 179,
      'G' => 164,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#b3a400',
    ),
    'shiny' => 
    array (
      'H' => 55,
      'S' => 100,
      'V' => 90,
      'R' => 230,
      'G' => 211,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#e6d300',
    ),
    'reallyShiny' => 
    array (
      'H' => 55,
      'S' => 67,
      'V' => 100,
      'R' => 255,
      'G' => 241,
      'B' => 82,
      'alpha' => 0,
      'hex' => '#fff152',
    ),
  ),
  60 => 
  array (
    'reallyDark' => 
    array (
      'H' => 65,
      'S' => 100,
      'V' => 29,
      'R' => 69,
      'G' => 76,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#454c00',
    ),
    'dark' => 
    array (
      'H' => 65,
      'S' => 100,
      'V' => 50,
      'R' => 117,
      'G' => 128,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#758000',
    ),
    'normal' => 
    array (
      'H' => 65,
      'S' => 100,
      'V' => 70,
      'R' => 164,
      'G' => 179,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#a4b300',
    ),
    'shiny' => 
    array (
      'H' => 64,
      'S' => 100,
      'V' => 90,
      'R' => 211,
      'G' => 230,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#d3e600',
    ),
    'reallyShiny' => 
    array (
      'H' => 64,
      'S' => 67,
      'V' => 100,
      'R' => 241,
      'G' => 255,
      'B' => 82,
      'alpha' => 0,
      'hex' => '#f1ff52',
    ),
  ),
  70 => 
  array (
    'reallyDark' => 
    array (
      'H' => 75,
      'S' => 100,
      'V' => 29,
      'R' => 56,
      'G' => 76,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#384c00',
    ),
    'dark' => 
    array (
      'H' => 75,
      'S' => 100,
      'V' => 50,
      'R' => 95,
      'G' => 128,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#5f8000',
    ),
    'normal' => 
    array (
      'H' => 75,
      'S' => 100,
      'V' => 70,
      'R' => 133,
      'G' => 179,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#85b300',
    ),
    'shiny' => 
    array (
      'H' => 75,
      'S' => 100,
      'V' => 90,
      'R' => 171,
      'G' => 230,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#abe600',
    ),
    'reallyShiny' => 
    array (
      'H' => 75,
      'S' => 67,
      'V' => 100,
      'R' => 211,
      'G' => 255,
      'B' => 82,
      'alpha' => 0,
      'hex' => '#d3ff52',
    ),
  ),
  80 => 
  array (
    'reallyDark' => 
    array (
      'H' => 86,
      'S' => 100,
      'V' => 29,
      'R' => 43,
      'G' => 76,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#2b4c00',
    ),
    'dark' => 
    array (
      'H' => 85,
      'S' => 100,
      'V' => 50,
      'R' => 73,
      'G' => 128,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#498000',
    ),
    'normal' => 
    array (
      'H' => 85,
      'S' => 100,
      'V' => 70,
      'R' => 103,
      'G' => 179,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#67b300',
    ),
    'shiny' => 
    array (
      'H' => 85,
      'S' => 100,
      'V' => 90,
      'R' => 132,
      'G' => 230,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#84e600',
    ),
    'reallyShiny' => 
    array (
      'H' => 85,
      'S' => 67,
      'V' => 100,
      'R' => 182,
      'G' => 255,
      'B' => 82,
      'alpha' => 0,
      'hex' => '#b6ff52',
    ),
  ),
  90 => 
  array (
    'reallyDark' => 
    array (
      'H' => 96,
      'S' => 100,
      'V' => 29,
      'R' => 30,
      'G' => 76,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#1e4c00',
    ),
    'dark' => 
    array (
      'H' => 96,
      'S' => 100,
      'V' => 50,
      'R' => 51,
      'G' => 128,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#338000',
    ),
    'normal' => 
    array (
      'H' => 95,
      'S' => 100,
      'V' => 70,
      'R' => 72,
      'G' => 179,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#48b300',
    ),
    'shiny' => 
    array (
      'H' => 96,
      'S' => 100,
      'V' => 90,
      'R' => 92,
      'G' => 230,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#5ce600',
    ),
    'reallyShiny' => 
    array (
      'H' => 95,
      'S' => 67,
      'V' => 100,
      'R' => 152,
      'G' => 255,
      'B' => 82,
      'alpha' => 0,
      'hex' => '#98ff52',
    ),
  ),
  100 => 
  array (
    'reallyDark' => 
    array (
      'H' => 106,
      'S' => 100,
      'V' => 29,
      'R' => 17,
      'G' => 76,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#114c00',
    ),
    'dark' => 
    array (
      'H' => 105,
      'S' => 100,
      'V' => 50,
      'R' => 30,
      'G' => 128,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#1e8000',
    ),
    'normal' => 
    array (
      'H' => 105,
      'S' => 100,
      'V' => 70,
      'R' => 42,
      'G' => 179,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#2ab300',
    ),
    'shiny' => 
    array (
      'H' => 105,
      'S' => 100,
      'V' => 90,
      'R' => 54,
      'G' => 230,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#36e600',
    ),
    'reallyShiny' => 
    array (
      'H' => 105,
      'S' => 67,
      'V' => 100,
      'R' => 123,
      'G' => 255,
      'B' => 82,
      'alpha' => 0,
      'hex' => '#7bff52',
    ),
  ),
  110 => 
  array (
    'reallyDark' => 
    array (
      'H' => 116,
      'S' => 100,
      'V' => 29,
      'R' => 5,
      'G' => 76,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#054c00',
    ),
    'dark' => 
    array (
      'H' => 115,
      'S' => 100,
      'V' => 50,
      'R' => 9,
      'G' => 128,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#098000',
    ),
    'normal' => 
    array (
      'H' => 115,
      'S' => 100,
      'V' => 70,
      'R' => 12,
      'G' => 179,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#0cb300',
    ),
    'shiny' => 
    array (
      'H' => 115,
      'S' => 100,
      'V' => 90,
      'R' => 16,
      'G' => 230,
      'B' => 0,
      'alpha' => 0,
      'hex' => '#10e600',
    ),
    'reallyShiny' => 
    array (
      'H' => 115,
      'S' => 67,
      'V' => 100,
      'R' => 95,
      'G' => 255,
      'B' => 82,
      'alpha' => 0,
      'hex' => '#5fff52',
    ),
  ),
  120 => 
  array (
    'reallyDark' => 
    array (
      'H' => 125,
      'S' => 100,
      'V' => 29,
      'R' => 0,
      'G' => 76,
      'B' => 7,
      'alpha' => 0,
      'hex' => '#004c07',
    ),
    'dark' => 
    array (
      'H' => 126,
      'S' => 100,
      'V' => 50,
      'R' => 0,
      'G' => 128,
      'B' => 13,
      'alpha' => 0,
      'hex' => '#00800d',
    ),
    'normal' => 
    array (
      'H' => 126,
      'S' => 100,
      'V' => 70,
      'R' => 0,
      'G' => 179,
      'B' => 18,
      'alpha' => 0,
      'hex' => '#00b312',
    ),
    'shiny' => 
    array (
      'H' => 125,
      'S' => 100,
      'V' => 90,
      'R' => 0,
      'G' => 230,
      'B' => 23,
      'alpha' => 0,
      'hex' => '#00e617',
    ),
    'reallyShiny' => 
    array (
      'H' => 126,
      'S' => 67,
      'V' => 100,
      'R' => 82,
      'G' => 255,
      'B' => 100,
      'alpha' => 0,
      'hex' => '#52ff64',
    ),
  ),
  130 => 
  array (
    'reallyDark' => 
    array (
      'H' => 135,
      'S' => 100,
      'V' => 29,
      'R' => 0,
      'G' => 76,
      'B' => 20,
      'alpha' => 0,
      'hex' => '#004c14',
    ),
    'dark' => 
    array (
      'H' => 135,
      'S' => 100,
      'V' => 50,
      'R' => 0,
      'G' => 128,
      'B' => 34,
      'alpha' => 0,
      'hex' => '#008022',
    ),
    'normal' => 
    array (
      'H' => 136,
      'S' => 100,
      'V' => 70,
      'R' => 0,
      'G' => 179,
      'B' => 48,
      'alpha' => 0,
      'hex' => '#00b330',
    ),
    'shiny' => 
    array (
      'H' => 136,
      'S' => 100,
      'V' => 90,
      'R' => 0,
      'G' => 230,
      'B' => 62,
      'alpha' => 0,
      'hex' => '#00e63e',
    ),
    'reallyShiny' => 
    array (
      'H' => 136,
      'S' => 67,
      'V' => 100,
      'R' => 82,
      'G' => 255,
      'B' => 129,
      'alpha' => 0,
      'hex' => '#52ff81',
    ),
  ),
  140 => 
  array (
    'reallyDark' => 
    array (
      'H' => 146,
      'S' => 100,
      'V' => 29,
      'R' => 0,
      'G' => 76,
      'B' => 33,
      'alpha' => 0,
      'hex' => '#004c21',
    ),
    'dark' => 
    array (
      'H' => 146,
      'S' => 100,
      'V' => 50,
      'R' => 0,
      'G' => 128,
      'B' => 56,
      'alpha' => 0,
      'hex' => '#008038',
    ),
    'normal' => 
    array (
      'H' => 146,
      'S' => 100,
      'V' => 70,
      'R' => 0,
      'G' => 179,
      'B' => 79,
      'alpha' => 0,
      'hex' => '#00b34f',
    ),
    'shiny' => 
    array (
      'H' => 146,
      'S' => 100,
      'V' => 90,
      'R' => 0,
      'G' => 230,
      'B' => 101,
      'alpha' => 0,
      'hex' => '#00e665',
    ),
    'reallyShiny' => 
    array (
      'H' => 146,
      'S' => 67,
      'V' => 100,
      'R' => 82,
      'G' => 255,
      'B' => 159,
      'alpha' => 0,
      'hex' => '#52ff9f',
    ),
  ),
  150 => 
  array (
    'reallyDark' => 
    array (
      'H' => 156,
      'S' => 100,
      'V' => 29,
      'R' => 0,
      'G' => 76,
      'B' => 46,
      'alpha' => 0,
      'hex' => '#004c2e',
    ),
    'dark' => 
    array (
      'H' => 156,
      'S' => 100,
      'V' => 50,
      'R' => 0,
      'G' => 128,
      'B' => 78,
      'alpha' => 0,
      'hex' => '#00804e',
    ),
    'normal' => 
    array (
      'H' => 156,
      'S' => 100,
      'V' => 70,
      'R' => 0,
      'G' => 179,
      'B' => 109,
      'alpha' => 0,
      'hex' => '#00b36d',
    ),
    'shiny' => 
    array (
      'H' => 156,
      'S' => 100,
      'V' => 90,
      'R' => 0,
      'G' => 230,
      'B' => 140,
      'alpha' => 0,
      'hex' => '#00e68c',
    ),
    'reallyShiny' => 
    array (
      'H' => 156,
      'S' => 67,
      'V' => 100,
      'R' => 82,
      'G' => 255,
      'B' => 188,
      'alpha' => 0,
      'hex' => '#52ffbc',
    ),
  ),
  160 => 
  array (
    'reallyDark' => 
    array (
      'H' => 166,
      'S' => 100,
      'V' => 29,
      'R' => 0,
      'G' => 76,
      'B' => 59,
      'alpha' => 0,
      'hex' => '#004c3b',
    ),
    'dark' => 
    array (
      'H' => 166,
      'S' => 100,
      'V' => 50,
      'R' => 0,
      'G' => 128,
      'B' => 99,
      'alpha' => 0,
      'hex' => '#008063',
    ),
    'normal' => 
    array (
      'H' => 166,
      'S' => 100,
      'V' => 70,
      'R' => 0,
      'G' => 179,
      'B' => 139,
      'alpha' => 0,
      'hex' => '#00b38b',
    ),
    'shiny' => 
    array (
      'H' => 166,
      'S' => 100,
      'V' => 90,
      'R' => 0,
      'G' => 230,
      'B' => 179,
      'alpha' => 0,
      'hex' => '#00e6b3',
    ),
    'reallyShiny' => 
    array (
      'H' => 166,
      'S' => 67,
      'V' => 100,
      'R' => 82,
      'G' => 255,
      'B' => 217,
      'alpha' => 0,
      'hex' => '#52ffd9',
    ),
  ),
  170 => 
  array (
    'reallyDark' => 
    array (
      'H' => 176,
      'S' => 100,
      'V' => 29,
      'R' => 0,
      'G' => 76,
      'B' => 72,
      'alpha' => 0,
      'hex' => '#004c48',
    ),
    'dark' => 
    array (
      'H' => 176,
      'S' => 100,
      'V' => 50,
      'R' => 0,
      'G' => 128,
      'B' => 121,
      'alpha' => 0,
      'hex' => '#008079',
    ),
    'normal' => 
    array (
      'H' => 176,
      'S' => 100,
      'V' => 70,
      'R' => 0,
      'G' => 179,
      'B' => 169,
      'alpha' => 0,
      'hex' => '#00b3a9',
    ),
    'shiny' => 
    array (
      'H' => 176,
      'S' => 100,
      'V' => 90,
      'R' => 0,
      'G' => 230,
      'B' => 218,
      'alpha' => 0,
      'hex' => '#00e6da',
    ),
    'reallyShiny' => 
    array (
      'H' => 176,
      'S' => 67,
      'V' => 100,
      'R' => 82,
      'G' => 255,
      'B' => 246,
      'alpha' => 0,
      'hex' => '#52fff6',
    ),
  ),
  180 => 
  array (
    'reallyDark' => 
    array (
      'H' => 187,
      'S' => 100,
      'V' => 29,
      'R' => 0,
      'G' => 67,
      'B' => 76,
      'alpha' => 0,
      'hex' => '#00434c',
    ),
    'dark' => 
    array (
      'H' => 187,
      'S' => 100,
      'V' => 50,
      'R' => 0,
      'G' => 112,
      'B' => 128,
      'alpha' => 0,
      'hex' => '#007080',
    ),
    'normal' => 
    array (
      'H' => 187,
      'S' => 100,
      'V' => 70,
      'R' => 0,
      'G' => 157,
      'B' => 179,
      'alpha' => 0,
      'hex' => '#009db3',
    ),
    'shiny' => 
    array (
      'H' => 187,
      'S' => 100,
      'V' => 90,
      'R' => 0,
      'G' => 202,
      'B' => 230,
      'alpha' => 0,
      'hex' => '#00cae6',
    ),
    'reallyShiny' => 
    array (
      'H' => 186,
      'S' => 67,
      'V' => 100,
      'R' => 82,
      'G' => 235,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#52ebff',
    ),
  ),
  190 => 
  array (
    'reallyDark' => 
    array (
      'H' => 198,
      'S' => 100,
      'V' => 29,
      'R' => 0,
      'G' => 53,
      'B' => 76,
      'alpha' => 0,
      'hex' => '#00354c',
    ),
    'dark' => 
    array (
      'H' => 197,
      'S' => 100,
      'V' => 50,
      'R' => 0,
      'G' => 90,
      'B' => 128,
      'alpha' => 0,
      'hex' => '#005a80',
    ),
    'normal' => 
    array (
      'H' => 197,
      'S' => 100,
      'V' => 70,
      'R' => 0,
      'G' => 127,
      'B' => 179,
      'alpha' => 0,
      'hex' => '#007fb3',
    ),
    'shiny' => 
    array (
      'H' => 197,
      'S' => 100,
      'V' => 90,
      'R' => 0,
      'G' => 163,
      'B' => 230,
      'alpha' => 0,
      'hex' => '#00a3e6',
    ),
    'reallyShiny' => 
    array (
      'H' => 197,
      'S' => 67,
      'V' => 100,
      'R' => 82,
      'G' => 205,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#52cdff',
    ),
  ),
  200 => 
  array (
    'reallyDark' => 
    array (
      'H' => 207,
      'S' => 100,
      'V' => 29,
      'R' => 0,
      'G' => 41,
      'B' => 76,
      'alpha' => 0,
      'hex' => '#00294c',
    ),
    'dark' => 
    array (
      'H' => 207,
      'S' => 100,
      'V' => 50,
      'R' => 0,
      'G' => 69,
      'B' => 128,
      'alpha' => 0,
      'hex' => '#004580',
    ),
    'normal' => 
    array (
      'H' => 207,
      'S' => 100,
      'V' => 70,
      'R' => 0,
      'G' => 96,
      'B' => 179,
      'alpha' => 0,
      'hex' => '#0060b3',
    ),
    'shiny' => 
    array (
      'H' => 207,
      'S' => 100,
      'V' => 90,
      'R' => 0,
      'G' => 124,
      'B' => 230,
      'alpha' => 0,
      'hex' => '#007ce6',
    ),
    'reallyShiny' => 
    array (
      'H' => 207,
      'S' => 67,
      'V' => 100,
      'R' => 82,
      'G' => 176,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#52b0ff',
    ),
  ),
  210 => 
  array (
    'reallyDark' => 
    array (
      'H' => 217,
      'S' => 100,
      'V' => 29,
      'R' => 0,
      'G' => 28,
      'B' => 76,
      'alpha' => 0,
      'hex' => '#001c4c',
    ),
    'dark' => 
    array (
      'H' => 217,
      'S' => 100,
      'V' => 50,
      'R' => 0,
      'G' => 47,
      'B' => 128,
      'alpha' => 0,
      'hex' => '#002f80',
    ),
    'normal' => 
    array (
      'H' => 217,
      'S' => 100,
      'V' => 70,
      'R' => 0,
      'G' => 66,
      'B' => 179,
      'alpha' => 0,
      'hex' => '#0042b3',
    ),
    'shiny' => 
    array (
      'H' => 217,
      'S' => 100,
      'V' => 90,
      'R' => 0,
      'G' => 85,
      'B' => 230,
      'alpha' => 0,
      'hex' => '#0055e6',
    ),
    'reallyShiny' => 
    array (
      'H' => 217,
      'S' => 67,
      'V' => 100,
      'R' => 82,
      'G' => 147,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#5293ff',
    ),
  ),
  220 => 
  array (
    'reallyDark' => 
    array (
      'H' => 228,
      'S' => 100,
      'V' => 29,
      'R' => 0,
      'G' => 15,
      'B' => 76,
      'alpha' => 0,
      'hex' => '#000f4c',
    ),
    'dark' => 
    array (
      'H' => 228,
      'S' => 100,
      'V' => 50,
      'R' => 0,
      'G' => 25,
      'B' => 128,
      'alpha' => 0,
      'hex' => '#001980',
    ),
    'normal' => 
    array (
      'H' => 228,
      'S' => 100,
      'V' => 70,
      'R' => 0,
      'G' => 35,
      'B' => 179,
      'alpha' => 0,
      'hex' => '#0023b3',
    ),
    'shiny' => 
    array (
      'H' => 227,
      'S' => 100,
      'V' => 90,
      'R' => 0,
      'G' => 46,
      'B' => 230,
      'alpha' => 0,
      'hex' => '#002ee6',
    ),
    'reallyShiny' => 
    array (
      'H' => 227,
      'S' => 67,
      'V' => 100,
      'R' => 82,
      'G' => 117,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#5275ff',
    ),
  ),
  230 => 
  array (
    'reallyDark' => 
    array (
      'H' => 238,
      'S' => 100,
      'V' => 29,
      'R' => 0,
      'G' => 2,
      'B' => 76,
      'alpha' => 0,
      'hex' => '#00024c',
    ),
    'dark' => 
    array (
      'H' => 238,
      'S' => 100,
      'V' => 50,
      'R' => 0,
      'G' => 4,
      'B' => 128,
      'alpha' => 0,
      'hex' => '#000480',
    ),
    'normal' => 
    array (
      'H' => 238,
      'S' => 100,
      'V' => 70,
      'R' => 0,
      'G' => 5,
      'B' => 179,
      'alpha' => 0,
      'hex' => '#0005b3',
    ),
    'shiny' => 
    array (
      'H' => 238,
      'S' => 100,
      'V' => 90,
      'R' => 0,
      'G' => 7,
      'B' => 230,
      'alpha' => 0,
      'hex' => '#0007e6',
    ),
    'reallyShiny' => 
    array (
      'H' => 237,
      'S' => 67,
      'V' => 100,
      'R' => 82,
      'G' => 88,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#5258ff',
    ),
  ),
  240 => 
  array (
    'reallyDark' => 
    array (
      'H' => 247,
      'S' => 100,
      'V' => 29,
      'R' => 10,
      'G' => 0,
      'B' => 76,
      'alpha' => 0,
      'hex' => '#0a004c',
    ),
    'dark' => 
    array (
      'H' => 247,
      'S' => 100,
      'V' => 50,
      'R' => 17,
      'G' => 0,
      'B' => 128,
      'alpha' => 0,
      'hex' => '#110080',
    ),
    'normal' => 
    array (
      'H' => 248,
      'S' => 100,
      'V' => 70,
      'R' => 24,
      'G' => 0,
      'B' => 179,
      'alpha' => 0,
      'hex' => '#1800b3',
    ),
    'shiny' => 
    array (
      'H' => 248,
      'S' => 100,
      'V' => 90,
      'R' => 31,
      'G' => 0,
      'B' => 230,
      'alpha' => 0,
      'hex' => '#1f00e6',
    ),
    'reallyShiny' => 
    array (
      'H' => 248,
      'S' => 67,
      'V' => 100,
      'R' => 106,
      'G' => 82,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#6a52ff',
    ),
  ),
  250 => 
  array (
    'reallyDark' => 
    array (
      'H' => 258,
      'S' => 100,
      'V' => 29,
      'R' => 23,
      'G' => 0,
      'B' => 76,
      'alpha' => 0,
      'hex' => '#17004c',
    ),
    'dark' => 
    array (
      'H' => 258,
      'S' => 100,
      'V' => 50,
      'R' => 39,
      'G' => 0,
      'B' => 128,
      'alpha' => 0,
      'hex' => '#270080',
    ),
    'normal' => 
    array (
      'H' => 258,
      'S' => 100,
      'V' => 70,
      'R' => 54,
      'G' => 0,
      'B' => 179,
      'alpha' => 0,
      'hex' => '#3600b3',
    ),
    'shiny' => 
    array (
      'H' => 258,
      'S' => 100,
      'V' => 90,
      'R' => 70,
      'G' => 0,
      'B' => 230,
      'alpha' => 0,
      'hex' => '#4600e6',
    ),
    'reallyShiny' => 
    array (
      'H' => 258,
      'S' => 67,
      'V' => 100,
      'R' => 135,
      'G' => 82,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#8752ff',
    ),
  ),
  260 => 
  array (
    'reallyDark' => 
    array (
      'H' => 268,
      'S' => 100,
      'V' => 29,
      'R' => 36,
      'G' => 0,
      'B' => 76,
      'alpha' => 0,
      'hex' => '#24004c',
    ),
    'dark' => 
    array (
      'H' => 268,
      'S' => 100,
      'V' => 50,
      'R' => 61,
      'G' => 0,
      'B' => 128,
      'alpha' => 0,
      'hex' => '#3d0080',
    ),
    'normal' => 
    array (
      'H' => 268,
      'S' => 100,
      'V' => 70,
      'R' => 85,
      'G' => 0,
      'B' => 179,
      'alpha' => 0,
      'hex' => '#5500b3',
    ),
    'shiny' => 
    array (
      'H' => 268,
      'S' => 100,
      'V' => 90,
      'R' => 110,
      'G' => 0,
      'B' => 230,
      'alpha' => 0,
      'hex' => '#6e00e6',
    ),
    'reallyShiny' => 
    array (
      'H' => 268,
      'S' => 67,
      'V' => 100,
      'R' => 165,
      'G' => 82,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#a552ff',
    ),
  ),
  270 => 
  array (
    'reallyDark' => 
    array (
      'H' => 278,
      'S' => 100,
      'V' => 29,
      'R' => 49,
      'G' => 0,
      'B' => 76,
      'alpha' => 0,
      'hex' => '#31004c',
    ),
    'dark' => 
    array (
      'H' => 278,
      'S' => 100,
      'V' => 50,
      'R' => 82,
      'G' => 0,
      'B' => 128,
      'alpha' => 0,
      'hex' => '#520080',
    ),
    'normal' => 
    array (
      'H' => 278,
      'S' => 100,
      'V' => 70,
      'R' => 115,
      'G' => 0,
      'B' => 179,
      'alpha' => 0,
      'hex' => '#7300b3',
    ),
    'shiny' => 
    array (
      'H' => 278,
      'S' => 100,
      'V' => 90,
      'R' => 148,
      'G' => 0,
      'B' => 230,
      'alpha' => 0,
      'hex' => '#9400e6',
    ),
    'reallyShiny' => 
    array (
      'H' => 278,
      'S' => 67,
      'V' => 100,
      'R' => 194,
      'G' => 82,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#c252ff',
    ),
  ),
  280 => 
  array (
    'reallyDark' => 
    array (
      'H' => 288,
      'S' => 100,
      'V' => 29,
      'R' => 61,
      'G' => 0,
      'B' => 76,
      'alpha' => 0,
      'hex' => '#3d004c',
    ),
    'dark' => 
    array (
      'H' => 288,
      'S' => 100,
      'V' => 50,
      'R' => 104,
      'G' => 0,
      'B' => 128,
      'alpha' => 0,
      'hex' => '#680080',
    ),
    'normal' => 
    array (
      'H' => 288,
      'S' => 100,
      'V' => 70,
      'R' => 146,
      'G' => 0,
      'B' => 179,
      'alpha' => 0,
      'hex' => '#9200b3',
    ),
    'shiny' => 
    array (
      'H' => 288,
      'S' => 100,
      'V' => 90,
      'R' => 187,
      'G' => 0,
      'B' => 230,
      'alpha' => 0,
      'hex' => '#bb00e6',
    ),
    'reallyShiny' => 
    array (
      'H' => 288,
      'S' => 67,
      'V' => 100,
      'R' => 223,
      'G' => 82,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#df52ff',
    ),
  ),
  290 => 
  array (
    'reallyDark' => 
    array (
      'H' => 298,
      'S' => 100,
      'V' => 29,
      'R' => 74,
      'G' => 0,
      'B' => 76,
      'alpha' => 0,
      'hex' => '#4a004c',
    ),
    'dark' => 
    array (
      'H' => 298,
      'S' => 100,
      'V' => 50,
      'R' => 125,
      'G' => 0,
      'B' => 128,
      'alpha' => 0,
      'hex' => '#7d0080',
    ),
    'normal' => 
    array (
      'H' => 298,
      'S' => 100,
      'V' => 70,
      'R' => 176,
      'G' => 0,
      'B' => 179,
      'alpha' => 0,
      'hex' => '#b000b3',
    ),
    'shiny' => 
    array (
      'H' => 298,
      'S' => 100,
      'V' => 90,
      'R' => 226,
      'G' => 0,
      'B' => 230,
      'alpha' => 0,
      'hex' => '#e200e6',
    ),
    'reallyShiny' => 
    array (
      'H' => 298,
      'S' => 67,
      'V' => 100,
      'R' => 252,
      'G' => 82,
      'B' => 255,
      'alpha' => 0,
      'hex' => '#fc52ff',
    ),
  ),
  300 => 
  array (
    'reallyDark' => 
    array (
      'H' => 309,
      'S' => 100,
      'V' => 29,
      'R' => 76,
      'G' => 0,
      'B' => 64,
      'alpha' => 0,
      'hex' => '#4c0040',
    ),
    'dark' => 
    array (
      'H' => 309,
      'S' => 100,
      'V' => 50,
      'R' => 128,
      'G' => 0,
      'B' => 108,
      'alpha' => 0,
      'hex' => '#80006c',
    ),
    'normal' => 
    array (
      'H' => 309,
      'S' => 100,
      'V' => 70,
      'R' => 179,
      'G' => 0,
      'B' => 151,
      'alpha' => 0,
      'hex' => '#b30097',
    ),
    'shiny' => 
    array (
      'H' => 309,
      'S' => 100,
      'V' => 90,
      'R' => 230,
      'G' => 0,
      'B' => 194,
      'alpha' => 0,
      'hex' => '#e600c2',
    ),
    'reallyShiny' => 
    array (
      'H' => 309,
      'S' => 67,
      'V' => 100,
      'R' => 255,
      'G' => 82,
      'B' => 229,
      'alpha' => 0,
      'hex' => '#ff52e5',
    ),
  ),
  310 => 
  array (
    'reallyDark' => 
    array (
      'H' => 319,
      'S' => 100,
      'V' => 29,
      'R' => 76,
      'G' => 0,
      'B' => 51,
      'alpha' => 0,
      'hex' => '#4c0033',
    ),
    'dark' => 
    array (
      'H' => 319,
      'S' => 100,
      'V' => 50,
      'R' => 128,
      'G' => 0,
      'B' => 86,
      'alpha' => 0,
      'hex' => '#800056',
    ),
    'normal' => 
    array (
      'H' => 319,
      'S' => 100,
      'V' => 70,
      'R' => 179,
      'G' => 0,
      'B' => 120,
      'alpha' => 0,
      'hex' => '#b30078',
    ),
    'shiny' => 
    array (
      'H' => 319,
      'S' => 100,
      'V' => 90,
      'R' => 230,
      'G' => 0,
      'B' => 155,
      'alpha' => 0,
      'hex' => '#e6009b',
    ),
    'reallyShiny' => 
    array (
      'H' => 319,
      'S' => 67,
      'V' => 100,
      'R' => 255,
      'G' => 82,
      'B' => 199,
      'alpha' => 0,
      'hex' => '#ff52c7',
    ),
  ),
  320 => 
  array (
    'reallyDark' => 
    array (
      'H' => 330,
      'S' => 100,
      'V' => 29,
      'R' => 76,
      'G' => 0,
      'B' => 38,
      'alpha' => 0,
      'hex' => '#4c0026',
    ),
    'dark' => 
    array (
      'H' => 330,
      'S' => 100,
      'V' => 50,
      'R' => 128,
      'G' => 0,
      'B' => 64,
      'alpha' => 0,
      'hex' => '#800040',
    ),
    'normal' => 
    array (
      'H' => 329,
      'S' => 100,
      'V' => 70,
      'R' => 179,
      'G' => 0,
      'B' => 90,
      'alpha' => 0,
      'hex' => '#b3005a',
    ),
    'shiny' => 
    array (
      'H' => 329,
      'S' => 100,
      'V' => 90,
      'R' => 230,
      'G' => 0,
      'B' => 116,
      'alpha' => 0,
      'hex' => '#e60074',
    ),
    'reallyShiny' => 
    array (
      'H' => 329,
      'S' => 67,
      'V' => 100,
      'R' => 255,
      'G' => 82,
      'B' => 170,
      'alpha' => 0,
      'hex' => '#ff52aa',
    ),
  ),
  330 => 
  array (
    'reallyDark' => 
    array (
      'H' => 340,
      'S' => 100,
      'V' => 29,
      'R' => 76,
      'G' => 0,
      'B' => 25,
      'alpha' => 0,
      'hex' => '#4c0019',
    ),
    'dark' => 
    array (
      'H' => 340,
      'S' => 100,
      'V' => 50,
      'R' => 128,
      'G' => 0,
      'B' => 42,
      'alpha' => 0,
      'hex' => '#80002a',
    ),
    'normal' => 
    array (
      'H' => 340,
      'S' => 100,
      'V' => 70,
      'R' => 179,
      'G' => 0,
      'B' => 59,
      'alpha' => 0,
      'hex' => '#b3003b',
    ),
    'shiny' => 
    array (
      'H' => 340,
      'S' => 100,
      'V' => 90,
      'R' => 230,
      'G' => 0,
      'B' => 76,
      'alpha' => 0,
      'hex' => '#e6004c',
    ),
    'reallyShiny' => 
    array (
      'H' => 339,
      'S' => 67,
      'V' => 100,
      'R' => 255,
      'G' => 82,
      'B' => 140,
      'alpha' => 0,
      'hex' => '#ff528c',
    ),
  ),
  340 => 
  array (
    'reallyDark' => 
    array (
      'H' => 350,
      'S' => 100,
      'V' => 29,
      'R' => 76,
      'G' => 0,
      'B' => 12,
      'alpha' => 0,
      'hex' => '#4c000c',
    ),
    'dark' => 
    array (
      'H' => 350,
      'S' => 100,
      'V' => 50,
      'R' => 128,
      'G' => 0,
      'B' => 21,
      'alpha' => 0,
      'hex' => '#800015',
    ),
    'normal' => 
    array (
      'H' => 350,
      'S' => 100,
      'V' => 70,
      'R' => 179,
      'G' => 0,
      'B' => 29,
      'alpha' => 0,
      'hex' => '#b3001d',
    ),
    'shiny' => 
    array (
      'H' => 350,
      'S' => 100,
      'V' => 90,
      'R' => 230,
      'G' => 0,
      'B' => 37,
      'alpha' => 0,
      'hex' => '#e60025',
    ),
    'reallyShiny' => 
    array (
      'H' => 349,
      'S' => 67,
      'V' => 100,
      'R' => 255,
      'G' => 82,
      'B' => 111,
      'alpha' => 0,
      'hex' => '#ff526f',
    ),
  ),
  350 => 
  array (
    'reallyDark' => 
    array (
      'H' => 0,
      'S' => 0,
      'V' => 5,
      'R' => 15,
      'G' => 15,
      'B' => 15,
      'alpha' => 0,
      'hex' => '#0f0f0f',
    ),
    'dark' => 
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
    'normal' => 
    array (
      'H' => 0,
      'S' => 0,
      'V' => 13,
      'R' => 35,
      'G' => 35,
      'B' => 35,
      'alpha' => 0,
      'hex' => '#232323',
    ),
    'shiny' => 
    array (
      'H' => 0,
      'S' => 0,
      'V' => 18,
      'R' => 46,
      'G' => 46,
      'B' => 46,
      'alpha' => 0,
      'hex' => '#2e2e2e',
    ),
    'reallyShiny' => 
    array (
      'H' => 0,
      'S' => 0,
      'V' => 45,
      'R' => 117,
      'G' => 117,
      'B' => 117,
      'alpha' => 0,
      'hex' => '#757575',
    ),
  ),
  360 => 
  array (
    'reallyDark' => 
    array (
      'H' => 0,
      'S' => 0,
      'V' => 5,
      'R' => 15,
      'G' => 15,
      'B' => 15,
      'alpha' => 0,
      'hex' => '#0f0f0f',
    ),
    'dark' => 
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
    'normal' => 
    array (
      'H' => 0,
      'S' => 0,
      'V' => 13,
      'R' => 35,
      'G' => 35,
      'B' => 35,
      'alpha' => 0,
      'hex' => '#232323',
    ),
    'shiny' => 
    array (
      'H' => 0,
      'S' => 0,
      'V' => 18,
      'R' => 46,
      'G' => 46,
      'B' => 46,
      'alpha' => 0,
      'hex' => '#2e2e2e',
    ),
    'reallyShiny' => 
    array (
      'H' => 0,
      'S' => 0,
      'V' => 45,
      'R' => 117,
      'G' => 117,
      'B' => 117,
      'alpha' => 0,
      'hex' => '#757575',
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