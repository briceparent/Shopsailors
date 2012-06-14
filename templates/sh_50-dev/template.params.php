<?php
/**
 * Params file
 *
 * Params file version : 0.2
 * Licensed under LGPL
 */

if(!defined('SH_MARKER')) {
    header('location: directCallForbidden.php');
}

$this->values = array (
    'version' => '1.2',
    'customizations'=>array(
        'images_groups' => array(
            'background',
            'background_draw' => array(
                'none'=>'none',
                'bg_1'=>'bg_1.png',
                'bg_2'=>'bg_2.png',
                'bg_3'=>'bg_3.png',
                'bg_4'=>'bg_4.png',
                'bg_5'=>'bg_5.png',
                'bg_6'=>'bg_6.png',
                'bg_7'=>'bg_7.png',
            ),
            'header',
            'menu',
            'content'
        ),
        'menus' => array(
            'menu_passive' => 'palette_passive.php',
            'menu_hover' => 'palette_hover.php',
            'menu_selected' => 'palette_selected.php'
        ),
        'texts' => array(
            'headline' => 'headline.php',
            'page_title' => 'page_title.php',
            'spacer2' => 'spacer',
            'links_passive' => 'palette_links_passive.php',
            'links_hover' => 'palette_links_hover.php',
            'links_visited' => 'palette_links_visited.php',
            'links_visited2' => 'palette_links_visited.php'
        ),
        'images' => array(
            'main_background.png'=>'background',
            'main_background_ending.png'=>'background',
            'background_draw.png'=>'background_draw',
            'header_background.png' => 'header',
            'connectionBar_background.png' => 'header',
            'menu_background.png' => 'menu',
            'content_background.png' => 'content',
            'content_bottom_background.png' => 'content'
        )
    ),
    
    'default_customizations'=>array(
      'images' => 
      array (
        'background' => '32f91f',
        'background_draw' => 'none',
        'header' => '1DBA3A',
        'menu' => 'b1c308',
        'content' => '4AFC2A',
      ),
      'menus' => 
      array (
        'menu_passive' => '3fbfcb',
        'menu_hover' => '498c7b',
        'menu_selected' => '3ce1d2',
      ),
      'texts' => 
      array (
        'headline' => 'EF4040',
        'page_title' => '08b4a5',
        'page_content' => 'c29340',
        'links_passive' => 'dc5edd',
        'links_hover' => '42968c',
        'links_visited' => '4e1fe8',
            'links_visited2' => '123456'
      ),
    ),
    
    'width' => 900,
    'menusNumber' => 1,
    'menusDescription'=>array(
        0=>'principal'
    ),
    'palettes' => array(
        'headline' => 'headline.php',
        'title' => 'title.php',
        'tables_border' => 'tables_border.php',
    ),
    'menuButtons' =>
    array (
        1 =>
        array (
            'maxWidth' => true,
            'totalWidth' => 778,
            'textHeight' => 18,
            'expand' => false,
            'type' => 'btn_default',
            'font' => 'FreeFontBold.ttf',
            'hasSubmenus' => true,
            'renderFile' => 'horizontal',
            'fonts' =>
            array (
                'Aarvark_Cafe.ttf'=>SH_FONTS_FOLDER.'Aarvark_Cafe.ttf',
                'Abduction2002.ttf'=>SH_FONTS_FOLDER.'Abduction2002.ttf',
                'Alpine_Regular.ttf'=>SH_FONTS_FOLDER.'Alpine_Regular.ttf',
                'designer.ttf'=>SH_FONTS_FOLDER.'designer.ttf',
                'FreeFont.ttf'=>SH_FONTS_FOLDER.'FreeFont.ttf',
                'FreeFontBold.ttf'=>SH_FONTS_FOLDER.'FreeFontBold.ttf',
                'FreeFontBoldOblique.ttf'=>SH_FONTS_FOLDER.'FreeFontBoldOblique.ttf',
                'FreeFontOblique.ttf'=>SH_FONTS_FOLDER.'FreeFontOblique.ttf',
                'FreeFontSerif.ttf'=>SH_FONTS_FOLDER.'FreeFontSerif.ttf',
                'FreeFontSerifBold.ttf'=>SH_FONTS_FOLDER.'FreeFontSerifBold.ttf',
                'FreeFontSerifBoldOblique.ttf'=>SH_FONTS_FOLDER.'FreeFontSerifBoldOblique.ttf',
                'FreeFontSerifOblique.ttf'=>SH_FONTS_FOLDER.'FreeFontSerifOblique.ttf',
                'Hall_Fetica_Decompose.ttf'=>SH_FONTS_FOLDER.'Hall_Fetica_Decompose.ttf',
                'Hall_Fetica_Decompose_Italic.ttf'=>SH_FONTS_FOLDER.'Hall_Fetica_Decompose_Italic.ttf',
                'LatiniaBlack.ttf'=>SH_FONTS_FOLDER.'LatiniaBlack.ttf',
                'Little_Lord_Fontleroy.ttf'=>SH_FONTS_FOLDER.'Little_Lord_Fontleroy.ttf',
                'MiddleSaxonyText.ttf'=>SH_FONTS_FOLDER.'MiddleSaxonyText.ttf',
                'Vera.ttf'=>SH_FONTS_FOLDER.'Vera.ttf',
                'VeraBold.ttf'=>SH_FONTS_FOLDER.'VeraBold.ttf',
                'VeraBoldOblique.ttf'=>SH_FONTS_FOLDER.'VeraBoldOblique.ttf',
                'VeraSe.ttf'=>SH_FONTS_FOLDER.'VeraSe.ttf',
                'VeraSeBd.ttf'=>SH_FONTS_FOLDER.'VeraSeBd.ttf',
            ),
        ),
    ),
    'renderFiles' => array(
    ),
    'sh_shop'=>array(
        'categoriesListing' => array(
            'categoriesNumber' => 10
        ),
        'productsListing' => array(
            'list' => array(
                'productsNumber' => 12
            ),
            'table' => array(
                'productsNumber' => 20
            ),
            'miniature' => array(
                'productsNumber' => 12
            ),
            'default' => 'miniature'
        ),
        'product' => array(
            'productsNumber' => 4
        )
    ),
);