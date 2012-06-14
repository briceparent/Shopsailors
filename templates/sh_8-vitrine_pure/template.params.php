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

$this->version = '0.2';

$this->values = array (
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
    'globalImage' => true,
    'menuButtons' =>
    array (
        1 =>
        array (
            'maxWidth' => false,
            'totalWidth' => 250,
            'textHeight' => 14,
            'expand' => false,
            'type' => 'btn_alpha_999',
            'textColor' => '',
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
        'sh_shop_default_categories' => 'sh_shop/categories.rf.xml',
        'sh_shop_default_product' => 'sh_shop/product.rf.xml',
        'sh_shop_default_products_list' => 'sh_shop/products_grid.rf.xml',
    ),
    'css' => array(
        'sh_shop' => 'sh_shop.css'
    ),
    'sh_shop'=>array(
        'categoriesListing' => array(
            'categoriesNumber' => 9,
            'groupedBy' => 3,
            'fillWith' => '&#160;',
        ),
        'productsListing' => array(
            'grid' => array(
                'productsNumber' => 9,
                'groupedBy' => 3,
                'fillWith' => '&#160;',
            ),
            'default' => 'grid'
        ),
        'product' => array(
            'productsNumber' => 9,
            'groupedBy' => 3,
            'fillWith' => '&#160;',
        )
    ),

);
