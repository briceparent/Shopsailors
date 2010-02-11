<?php
/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')){header('location: directCallForbidden.php');}

/**
 * This class is not extending sh_core, because it can't be constructed,
 * and isn't using any other class.
 * Its methods should be called statically.
 */
class sh_colors{
    /**
     * This object should only be used statically, so it can't be __constructed
     */
    protected function __construct() {
    }

    /**
     * public function setHueToImage
     *
     */
    public static function setHueToImage($srcImage,$destImage,$hue,$saturation = 0,$value = 0,$addToTransHue = 0){
        //ini_set('xdebug.show_mem_delta','On');
        //xdebug_start_trace('/tmp/'.basename($srcImage).$hue);
        if($hue !== null){
            if($hue < 0){
                $hue += 360;
            }elseif($hue>360){
                $hue -= 360;
            }
        }
        // Gets the original
        $originalImage = imagecreatefrompng($srcImage);
        $originalWidth = imagesx($originalImage);
        $originalHeight = imagesy($originalImage);

        // Prepares the destination
        $modifiedImage = imagecreatetruecolor($originalWidth, $originalHeight);

        // Determines what will be the transparent color
        // H=9 should never be used because we only use %20 hues
        $transColorHSV = array('H'=>9 + $addToTransHue,'S'=>47,'V'=>55);
        $transColorRGB = self::HSVToRGB($transColorHSV);
        $transparent = imagecolorallocate($modifiedImage, $transColorRGB['R'],$transColorRGB['G'],$transColorRGB['B']);

        imagefill($modifiedImage, 0, 0, $transparent);
        imagecolortransparent($modifiedImage,$transparent);
        ImageAlphaBlending($modifiedImage, false);
        imageSaveAlpha($modifiedImage, true);

        //Copies the pixels one by one, with the modification
        for($x = 0; $x<$originalWidth;$x++){
            for($y = 0; $y<$originalHeight;$y++){
                $rgb = imagecolorat($originalImage, $x, $y);
                $alpha = ($rgb & 0x7F000000) >> 24;
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $initColorRGB = array('R'=>$r,'G'=>$g,'B'=>$b);
                $initColorHSV = self::RGBToHSV(array('R'=>$r,'G'=>$g,'B'=>$b));
                if($hue !== null){
                    $newHue = $hue;
                }else{
                    $newHue = $initColorHSV['H'];
                }
                $newSaturation = $initColorHSV['S'] + $saturation;
                if($newSaturation >= 100){
                    $newSaturation = 100;
                }elseif($newSaturation <= 0){
                    $newSaturation = 0;
                }
                $newValue = $initColorHSV['V'] + $value;
                if($newValue >= 100){
                    $newValue = 100;
                }elseif($newValue <= 0){
                    $newValue = 0;
                }
                $newColor = self::HSVToRGB(array('H'=>$newHue,'S'=>$newSaturation,'V'=>$newValue));
                $tempColor = imagecolorallocatealpha($modifiedImage,$newColor['R'],$newColor['G'],$newColor['B'],$alpha);
                imagesetpixel($modifiedImage,$x,$y,$tempColor);
            }
        }
        imagedestroy($originalImage);
        imagepng($modifiedImage, $destImage);
        imagedestroy($modifiedImage);
        //xdebug_stop_trace();
        return $destImage;
    }


    /**
     * public static function modifyImageWithDelta
     *
     */
    public static function modifyImageWithDelta($srcImage,$destImage,$colorDelta){
//        echo nl2br(print_r(func_get_args(),true)).'<br />';
        // Gets the original
        $originalImage = imagecreatefrompng($srcImage);
        $originalWidth = imagesx($originalImage);
        $originalHeight = imagesy($originalImage);

        // Prepares the destination
        $modifiedImage = imagecreatetruecolor($originalWidth, $originalHeight);
        $transparent = imagecolorallocate($modifiedImage, 1,2,3);
        imagefill($modifiedImage, 0, 0, $transparent);
        imagecolortransparent($modifiedImage,$transparent);
        ImageAlphaBlending($modifiedImage, false);
        imageSaveAlpha($modifiedImage, true);

        //Copies the pixels one by one, with the modification
        for($x = 0; $x<$originalWidth;$x++){
            for($y = 0; $y<$originalHeight;$y++){
                $rgb = imagecolorat($originalImage, $x, $y);
                $alpha = ($rgb & 0x7F000000) >> 24;
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $initColor = array('R'=>$r,'G'=>$g,'B'=>$b);

                $newColor = self::addHSVToRVBColor($initColor,
                                                   $colorDelta);

                $tempColor = imagecolorallocatealpha($modifiedImage,$newColor['R'],$newColor['G'],$newColor['B'],$alpha);
                imagesetpixel($modifiedImage,$x,$y,$tempColor);
            }
        }
        imagedestroy($originalImage);
        imagepng($modifiedImage, $destImage);
        imagedestroy($modifiedImage);
        return $destImage;
    }


    /**
     * public static function addToColor
     * Adds H, S and V values from $addedColor to the RGB $color
     */
    public static function addHSVToRVBColor($color,$addedColor){
        $HSVLoops = array('H','S','V');
        $RGBLoops = array('R','G','B');
        $HSVColor = self::RGBToHSV($color);
        foreach($HSVLoops as $pass){
            $HSVColor[$pass] += $addedColor[$pass];
        }

        $newRGB = self::HSVToRGB($HSVColor);
        foreach($RGBLoops as &$pass){
            if($newRGB[$pass]<0){
                $newRGB[$pass] = 0;
            }
            if($newRGB[$pass]>255){
                $newRGB[$pass] = 255;
            }
        }
        return $newRGB;
    }

    /**
     * public function convertRGBStringToRGBArray
     *
     */
    public static function RGBStringToRGBArray($color){
        if(!is_array($color)){
            if(substr($color,0,1) == '#'){
                $color = substr($color,1);
            }
            $parts = str_split($color,2);
            $r = hexdec($parts[0]);
            $g = hexdec($parts[1]);
            $b = hexdec($parts[2]);
            return array(
                'R'=>$r,
                'G'=>$g,
                'B'=>$b,
                'color'=>$color
            );
        }
        return $color;
    }

    /**
     * public static function RGBToHSV
     *
     */
    public static function RGBToHSV($color){
        if(!is_array($color)){
            $color = self::RGBStringToRGBArray($color);
        }
        $var_R = ( $color['R'] / 255 );                     //RGB from 0 to 255
        $var_G = ( $color['G'] / 255 );
        $var_B = ( $color['B'] / 255 );

        $var_Min = min( $var_R, $var_G, $var_B );    //Min. value of RGB
        $var_Max = max( $var_R, $var_G, $var_B );    //Max. value of RGB
        $del_Max = $var_Max - $var_Min;             //Delta RGB value

        $V = $var_Max;

        if ( $del_Max == 0 ){//This is a gray, no chroma...
           $H = 0;                               //HSV results from 0 to 1
           $S = 0;
        }else{//Chromatic data...
           $S = $del_Max / $var_Max;

           $del_R = ( ( ( $var_Max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
           $del_G = ( ( ( $var_Max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
           $del_B = ( ( ( $var_Max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;

           if( $var_R == $var_Max ){
               $H = $del_B - $del_G;
           }elseif( $var_G == $var_Max ){
               $H = ( 1 / 3 ) + $del_R - $del_B;
           }elseif( $var_B == $var_Max ){
               $H = ( 2 / 3 ) + $del_G - $del_R;
           }

           if ( $H < 0 ) {
               $H += 1;
           }
           if ( $H > 1 ){
              $H -= 1;
           }
        }
        return array('H'=>$H * 360,
                    'S'=>$S * 100,
                    'V'=>$V * 100);
    }

    /**
     * public static function HSVToRGB
     *
     */
    public static function HSVToRGB($color){
        list($H,$S,$V) = array($color['H'] / 360,$color['S'] / 100,$color['V'] / 100);
        if ( $S == 0 ){
           $r = $V * 255;
           $g = $V * 255;
           $b = $V * 255;
        }else{
            $var_h = $H * 6;
            if ( $var_h == 6 ){
               $var_h = 0;      //$H must be < 1
            }
            $var_i = floor( $var_h );
            $var_1 = $V * ( 1 - $S );
            $var_2 = $V * ( 1 - $S * ( $var_h - $var_i ) );
            $var_3 = $V * ( 1 - $S * ( 1 - ( $var_h - $var_i ) ) );

            if( $var_i == 0 ){
               $var_r = $V;
               $var_g = $var_3;
               $var_b = $var_1;
            }elseif ( $var_i == 1 ) {
               $var_r = $var_2 ;
               $var_g = $V;
               $var_b = $var_1;
            }elseif ( $var_i == 2 ) {
               $var_r = $var_1 ;
               $var_g = $V;
               $var_b = $var_3;
            }elseif ( $var_i == 3 ) {
               $var_r = $var_1 ;
               $var_g = $var_2 ;
               $var_b = $V ;
            }elseif ( $var_i == 4 ) {
               $var_r = $var_3 ;
               $var_g = $var_1 ;
               $var_b = $V ;
            }else{ $var_r = $V;
               $var_g = $var_1 ;
               $var_b = $var_2;
            }
            $r = $var_r * 255;
            $g = $var_g * 255;
            $b = $var_b * 255;
        }
        $rgb = decHex($r).decHex($g).decHex($b);
        return array('R'=>$r,
                    'G'=>$g,
                    'B'=>$b,
                    'color'=>$rgb);
    }
}