<?php

 /*
  * This source is based on the following class :
  * ------------------------------------------------
  * Created on 4 mai 07
  *
  * @autor : The Kankrelune 
  * @copyright : The WebFaktory 2006/2007
  * @license: Creative Commons by-nc
  * ------------------------------------------------
  *
  * Changes by Brice PARENT for Shopsailors :
  * - Removed some other letters from $chars
  * - Changed the way the captcha's contents is stored in session
  * - Now we don't switch beetween the effects, but use 2 of them :
  * dispertion and horizontal waves, both of them smaller than original ones.
  * It now can be read more easy.
  * - This file is now encoded as UTF8 (so some changes were made on french accents)
  */
/************************************************** Configuration **************************************************/
$chars = 'ABCDEFGHKMNPRTUVWX3689';		// liste des charactère (certain caractères ne sont pas présents pour éviter les confusions)
$nbChar = 5;					// nombre de charactères du code
$startOffset = 5;				// offset de départ sur l'image (en pixels)
$size_min = 20;					// taille minimum des caractères
$size_max = 28;					// taille maximum des caractères
$angle_min = 0;					// angle minimum d'inclinaison des caractères
$angle_max = 12;				// angle maximum d'inclinaison des caractères
$width = 130;					// largeur de l'image
$height = 30;					// hauteur de l'image
$addBlur = false;				// ajouter un floutage au code
$blurLevel = 1;					// niveau de floutage (entre 1 et 10 / 1 ou 2 conseillé après ça forme une bande autour des caractères)
$policePath =  dirname(__FILE__).'/captcha.ttf';// chemin de la police à utiliser

/********************************************* Ne rien toucher au dela *********************************************/

$form = $_GET['page'];
if($form == '') {
    $form = 'captchaResult';
}

if(!isset($_SESSION)) {
    session_start();
}

// creation de l'image contenant le code
$_charsImgHandler = imagecreatetruecolor($width,$height) OR exit('please activate GD lib');
$white = imagecolorallocate($_charsImgHandler, 255, 255, 255);
imagefill($_charsImgHandler, 0, 0,$white);

// on prépare et on copie le code en session et sur l'image
$i = -1;
$pos_x = $startOffset;
$cnt = strlen($chars)-1;
$charList = '';

while(++$i<$nbChar) {
    $char = $chars[mt_rand(0,$cnt)];
    $charList .= $char;
    $color = imagecolorallocate(
        $_charsImgHandler,
        mt_rand(0,150),
        mt_rand(0,150),
        mt_rand(0,150)
    );
    $size =  mt_rand($size_min, $size_max);
    $pos_y = mt_rand( min($size,$height-3), max($size,$height-3));
    imagettftext(
        $_charsImgHandler,
        $size,
        mt_rand($angle_min,$angle_max),
        $pos_x,
        $pos_y,
        $color,
        $policePath,
        $char
    );
    $pos_x += mt_rand(22,28);
}
$classFolderName = basename(dirname(dirname(__FILE__)));
$_SESSION[$classFolderName][$form]['captcha'] = $charList;



/* vagues horizontales */
if($addBlur === true) {
    if($blurLevel < 1)
        $blurLevel = 1;
    elseif($blurLevel > 10)
        $blurLevel = 10;

    $coeffs = array (
        array ( 1),
        array ( 1,  1),
        array ( 1,  2,  1),
        array ( 1,  3,  3,   1),
        array ( 1,  4,  6,   4,   1),
        array ( 1,  5, 10,  10,   5,   1),
        array ( 1,  6, 15,  20,  15,   6,   1),
        array ( 1,  7, 21,  35,  35,  21,   7,   1),
        array ( 1,  8, 28,  56,  70,  56,  28,   8,   1),
        array ( 1,  9, 36,  84, 126, 126,  84,  36,   9,  1),
        array ( 1, 10, 45, 120, 210, 252, 210, 120,  45, 10,  1)
    );

    $sum = pow(2, $blurLevel);
    $temp1 = imagecreatetruecolor($width, $height);
    $temp2 = imagecreatetruecolor($width, $height);
    imagecopy($temp2,$_charsImgHandler,0,0,0,0,$width,$height);

    $y = -1;
    while(++$y<=$height) {
        $x = -1;
        while(++$x<=$width) {
            $sumr = 0;
            $sumg = 0;
            $sumb = 0;
            $k = -1;

            while(++$k<=$blurLevel) {
                $color = @imagecolorat(
                    $_charsImgHandler,
                    ($x-(($blurLevel)/2)+$k),
                    $y
                );
                $sumr += (($color >> 16) & 0xFF) * $coeffs[$blurLevel][$k];
                $sumg += (($color >> 8) & 0xFF) * $coeffs[$blurLevel][$k];
                $sumb += ($color & 0xFF) * $coeffs[$blurLevel][$k];
            }

            $color = imagecolorallocate(
                $temp1,
                ($sumr/$sum),
                ($sumg/$sum),
                ($sumb/$sum)
            );
            imagesetpixel($temp1,$x,$y,$color);
        }
    }

    imagedestroy($_charsImgHandler);
    $_charsImgHandler = $temp2;

    for($x=0;$x<$width;++$x) {
        for($y=0;$y<$height;++$y) {
            $sumr=0; $sumg=0; $sumb=0;

            for($k=0;$k<=$blurLevel;++$k) {
                $color = @imagecolorat($temp1, $x,($y-(($blurLevel)/2)+$k));
                $sumr += (($color >> 16) & 0xFF) * $coeffs[$blurLevel][$k];
                $sumg += (($color >> 8) & 0xFF) * $coeffs[$blurLevel][$k];
                $sumb += ($color & 0xFF) * $coeffs[$blurLevel][$k];
            }

            $color = imagecolorallocate(
                $_charsImgHandler,
                ($sumr/$sum),
                ($sumg/$sum),
                ($sumb/$sum)
            );
            imagesetpixel($_charsImgHandler,$x,$y,$color);
        }
    }

    imagedestroy($temp1);
}

// on rend transparent le fond de l'image contenant les caract�res
imagecolortransparent($_charsImgHandler,$white);

// cr�ation image de fond
$_bgImgHandler = imagecreatetruecolor($width,$height);
imagefill($_bgImgHandler, 0, 0,$white);

// choix de la couleur des lignes et du type de quadrillage
$line = imagecolorallocate(
    $_bgImgHandler,
    mt_rand(180,200),
    mt_rand(190,210),
    mt_rand(180,200)
);
$grill = imagecolorallocate(
    $_bgImgHandler,
    mt_rand(190,210),
    mt_rand(180,200),
    mt_rand(190,210)
);
$lineType = mt_rand(0,11);


/* Eventails */
if($lineType !== 3) // eventail horizontal ou en grille
{
    for($y=0,$z=0; $y<$width; ++$y,$z+=8)
        imageline( $_bgImgHandler, $width*2, 0, 0, $z, $line);
}
if($lineType !== 4) // eventail vertical ou en grille
{
    for($y=0,$z=0; $y<$height; ++$y,$z+=6)
        imageline( $_bgImgHandler, 0, $width, $z, 0, $grill);
}	




// fusion du code et du fond
imagecopymerge(
    $_bgImgHandler, $_charsImgHandler, 0, 0, 0, 0, $width, $height,100
);
imagedestroy($_charsImgHandler);

// on affiche
header('Pragma: no-cache');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Cache-Control: private',false);



header ('Content-type: image/gif');
imagegif($_bgImgHandler);