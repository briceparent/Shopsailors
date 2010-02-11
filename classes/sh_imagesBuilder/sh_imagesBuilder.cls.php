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
 * Class that builds the images variations.
 */
class sh_imagesBuilder extends sh_core{
    protected $imagesPath = '';
    protected $images = array();
    protected $type = null;
    protected $variation = '';
    protected $font = array(); // $font = array([name],[size],[color]);
    protected $textHeight = 20;
    protected $dimensions = array(); // Used to share the image dimensions
    protected $textMasks = array();
    protected $stretchMasks = array();
    protected $colors = array('passive'=>'#000','active'=>'#111','selected'=>'#222');


    // Images' names are built with [POSITION]_[STATE]
    // Mask files' names are built with [POSITION]_[FILETYPE]
    // Positions
    const NORMAL = 'normal';
    const FIRST = 'first';
    const LAST = 'last';
    // States
    const PASSIVE = 'passive';
    const SELECTED = 'selected';
    const ACTIVE = 'active';
    // File types
    const STRETCH = 'stretch';
    const TEXT = 'text';

    const DEFAULTEXT = '.png';

    /**
     * protected function getMask
     * USED
     */
    protected function getMask($type,$position,$state){
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $imageFile = SH_TEMPLATE_BUILDER.$type.'/model/'.$position.'.png';

        // Verifies the existance of the stretch file
        if(!file_exists($imageFile)){
            return $this->explodeImage($type,$variation,$imageFile);
        }

        include(str_replace('.png','.php',$imageFile));

        return $image;
    }

    public function getRemovingMask($maskFile){
        ini_set("memory_limit", "128M");
        $phpMaskFile = $maskFile.'.mask.php';
        if(!file_exists($phpMaskFile)){
            $maskImage = imagecreatefrompng($maskFile);
            $width = imagesx($maskImage);
            $height = imagesy($maskImage);

            // Gets the pixels that are colored in the first line
            for($x = 1; $x<$width + 1;$x++){
                for($y = 1; $y<$height + 1;$y+=1){
                    $rgb = imagecolorat($maskImage, $x, $y);
                    $rgba = imagecolorsforindex($maskImage,$rgb);
                    $mask[$x][$y] = $rgba['alpha'];
                }
            }
            imagedestroy($maskImage);
            $this->links->helper->writeArrayInFile(
                $phpMaskFile,
                'mask',
                $mask
            );
        }else{
            include($phpMaskFile);
        }
        return $mask;
    }

    public function removePartsUsingMask($originalImage,$maskImage,$returnFolder = true){
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $variation = basename(dirname($originalImage));
        $destinationImage = dirname(dirname($originalImage)).'/masked/'.md5($variation.$originalImage.$maskImage).'.png';
        if(file_exists($destinationImage)){
            if($returnFolder){
                return $destinationImage;
            }else{
                return str_replace(SH_ROOT_FOLDER, SH_ROOT_PATH,$destinationImage);
            }
        }
        if(!is_dir(dirname($destinationImage))){
            mkdir(dirname($destinationImage));
        }
        $folder = SH_TEMPLATE_BUILDER.$type.'/';
        if(!file_exists($originalImage)){
            return false;
        }

        $original = imagecreatefrompng($originalImage);
        $mask = $this->getRemovingMask($maskImage);

        $destDir = dirname($destinationImage);
        if(!is_dir($destDir)){
            mkdir($destDir);
        }

        // Defines the size of the image
        $width = imagesx($original);
        $height = imagesy($original);

        $destination = imageCreateTrueColor($width,$height);
        imagesavealpha($destination, true);
        $transparentColor = imagecolorallocatealpha($destination, 0, 0, 0,127);
        imagefill($destination,0,0,$transparentColor);

        $cpt = 1;
        // Gets the pixels that are colored in the first line
        for($x = 1; $x<$width + 1;$x++){
            for($y = 1; $y<$height + 1;$y+=1){
                $transparency = $mask[$x][$y];
                if($transparency < 127){
                    $rgb = imagecolorat($original, $x, $y);
                    $color = imagecolorsforindex($original,$rgb);
                    $alpha = $color['alpha'] + $transparency;
                    if($alpha < 127){
                        $tempColor = imagecolorallocatealpha(
                            $destination,
                            $color['red'],
                            $color['green'],
                            $color['blue'],
                            $alpha
                        );
                        imagesetpixel($destination, $x, $y, $tempColor);
                    }
                }
            }
        }
        imagepng($destination,$destinationImage);
        imagedestroy($original);
        imagedestroy($destination);
        if($returnFolder){
            return $destinationImage;
        }else{
            return str_replace(SH_ROOT_FOLDER, SH_ROOT_PATH,$destinationImage);
        }
    }

    /**
     * public function stretchImage
     * USED
     */
    public function stretchImage($type,$position,$state,&$destImageName,$destWidth,$destHeight){
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $site = $this->links->site;
        $variation = $site->variation;

        if(!is_dir(dirname($destImageName))){
            mkdir(dirname($destImageName),0777,true);
        }

        // Get or generate (and get) the mask
        $mask = $this->getMask($type,$position,$state);
        if($mask == false){
            // There was an error reading or creating the mask file
            return false;
        }

        if(!empty($state)){
            $state = '.'.$state;
        }

        $srcImageName = SH_TEMPLATE_BUILDER.$type.'/variations/'.$variation.'/'.$position.$state.'.png';

        $this->stretchHorizontally($destImageName,$destWidth,$srcImageName,$mask);
        $this->stretchVertically($destImageName,$destHeight,$destImageName,$mask);
        return true;
    }

    public function createImageWithBackground($image,$backgroundColor,$width=100,$height=100){
        $destImage = imagecreatetruecolor($width, $height);
        $color = sh_colors::RGBStringToRGBArray($backgroundColor);
        $color=imagecolorallocate($destImage,$color['R'],$color['G'],$color['B']);
        imagefill($destImage,0,0,$color);
        imagepng($destImage,$image);
        imageDestroy($destImage);
        return true;
    }

    /**
     * protected function stretchHorizontally
     * USED
     */
    protected function stretchHorizontally($destImageName,$width,$srcImageName,$mask){
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        // Gets the un-stretched image, and prepares it
        $srcImage = imagecreatefrompng($srcImageName);

        ImageAlphaBlending($srcImage, false);
        imageSaveAlpha($srcImage, true);
        $srcWidth = imagesX($srcImage);
        $srcHeight = imagesY($srcImage);

        $destWidth = $width;
        $destHeight = $srcHeight;

        // creates the temp image
        $destImage = imagecreatetruecolor($destWidth, $destHeight);
        $transparent=imagecolorallocatealpha($destImage,255,255,255,127);
        ImageAlphaBlending($destImage, false);
        imageSaveAlpha($destImage, true);
        imagefill($destImage,0,0,$transparent);

        // Horizontal copies
        if(count($mask['stretchLeft']) == 0 || ($mask['stretchLeft'][0]['start'] == 0 && !isset($mask['stretchLeft'][0]['stop']))){
            // case #1
            imageCopyresized($destImage,$srcImage,
                0,0,
                0,0,
                $destWidth,$destHeight,
                $srcWidth,$destHeight);
        }else{
            // We will only use stretch index 0 and 1, if they exist
            if(is_array($mask['stretchLeft'][1])){
                $lastIndex = 1;
            }else{
                $lastIndex = 0;
            }
            $afterFirstPart = 0;
            $unusedWidth = $destWidth;
            // Unstretched parts
            if($mask['stretchLeft'][0]['start'] != 0){
                // copying the first unstretched part for cases #3, #4, #5, #7
                $firstWidth = $mask['stretchLeft'][0]['start'];
                imageCopy($destImage,$srcImage,0,0,0,0,$firstWidth,$destHeight);
                $unusedWidth -= $firstWidth;
                $afterFirstPart = $firstWidth;
            }
            if($mask['stretchLeft'][$lastIndex]['stop'] != 0){
                // copying the last unstretched part for cases #2, #4, #6, #7
                $lastWidth = $srcWidth - $mask['stretchLeft'][$lastIndex]['stop'];
                $srcLastStart = $mask['stretchLeft'][$lastIndex]['stop'];
                $destLastStart = $destWidth - $lastWidth;
                imageCopy($destImage,$srcImage,$destLastStart,0,$srcLastStart,0,$lastWidth,$destHeight);
                $unusedWidth -= $lastWidth;
            }
            if($lastIndex == 1){
                // copying the middle unstretched part for cases #5, #6, #7
                $thisWidth = $mask['stretchLeft'][1]['start'] - $mask['stretchLeft'][0]['stop'];
                $unusedWidth -= $thisWidth;
                $startingPoint = $afterFirstPart + $unusedWidth - round($unusedWidth/ 2);
                imageCopy($destImage,$srcImage,
                    $startingPoint,0,
                    $mask['stretchLeft'][0]['stop'],0,
                    $thisWidth,$destHeight);
            }

            // Stretched parts
            if($lastIndex == 0){
                $stretchWidth = $unusedWidth;
            }else{
                $stretchWidth = round($unusedWidth / 2);
                $addToFirst = $unusedWidth - 2 * $stretchWidth;
            }
            if($unusedWidth > 0 && $mask['stretchLeft'][0]['start'] == 0){
                // copying the first stretched part for cases #2, #6
                $originalStretchWidth = $mask['stretchLeft'][0]['stop'] - $mask['stretchLeft'][0]['start'];
                imageCopyresized($destImage,$srcImage,
                    $afterFirstPart,0,
                    $afterFirstPart,0,
                    $stretchWidth + $addToFirst,$destHeight,
                    $originalStretchWidth,$destHeight);
                $unusedWidth -= $stretchWidth + $addToFirst;
            }
            if($unusedWidth > 0 && !isset($mask['stretchLeft'][$lastIndex]['stop'])){
                // copying the last stretched part for cases #3, #5
                $destStartingPoint = $destWidth - $stretchWidth;
                $originalStretchWidth = $srcWidth - $mask['stretchLeft'][$lastIndex]['start'];
                imageCopyresized($destImage,$srcImage,
                    $destStartingPoint,0,
                    $mask['stretchLeft'][$lastIndex]['start'],0,
                    $stretchWidth,$destHeight,
                    $originalStretchWidth,$destHeight);
                $unusedWidth -= $stretchWidth;
            }
            if($unusedWidth > 0 && $mask['stretchLeft'][0]['start'] != 0){
                // copying the first stretched part for cases #4, #5, #7
                $originalStretchWidth = $mask['stretchLeft'][0]['stop'] - $mask['stretchLeft'][0]['start'];
                imageCopyresized($destImage,$srcImage,
                    $mask['stretchLeft'][0]['start'],0,
                    $mask['stretchLeft'][0]['start'],0,
                    $stretchWidth,$destHeight,
                    $originalStretchWidth,$destHeight);
                $unusedWidth -= $stretchWidth;
            }
            if($unusedWidth > 0){
                // copying the last stretched part for cases #6, #7
                $destStartingPoint = $destLastStart - $stretchWidth;
                $originalStretchWidth = $mask['stretchLeft'][1]['stop'] - $mask['stretchLeft'][1]['start'];
                imageCopyresized($destImage,$srcImage,
                    $destStartingPoint,0,
                    $mask['stretchLeft'][1]['start'],0,
                    $stretchWidth,$destHeight,
                    $originalStretchWidth,$destHeight);
                $unusedWidth -= $stretchWidth;
            }
        }
        // We don't need the original image anymore, so we free the space
        imagedestroy($srcImage);
        // We create the png and free the space
        imagepng($destImage,$destImageName);
        imageDestroy($destImage);

        return true;
    }

    /**
     * protected function stretchVertically
     * USED
     */
    protected function stretchVertically($destImageName,$height,$srcImageName,$mask){
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        // Gets the un-stretched image, and prepares it
        $srcImage = imagecreatefrompng($srcImageName);

        ImageAlphaBlending($srcImage, false);
        imageSaveAlpha($srcImage, true);
        $srcWidth = imagesX($srcImage);
        $srcHeight = imagesY($srcImage);

        $destWidth = $srcWidth;
        $destHeight = $height;

        // creates the temp image
        $destImage = imagecreatetruecolor($destWidth, $destHeight);
        $transparent=imagecolorallocatealpha($destImage,255,255,255,127);
        ImageAlphaBlending($destImage, false);
        imageSaveAlpha($destImage, true);
        imagefill($destImage,0,0,$transparent);

        // Horizontal copies
        if(count($mask['stretchTop']) == 0 || ($mask['stretchTop'][0]['start'] == 0 && !isset($mask['stretchTop'][0]['stop']))){
            // case #1
            imageCopyresized($destImage,$srcImage,
                0,0,
                0,0,
                $destWidth,$destHeight,
                $destWidth,$srcHeight);
        }else{
            // We will only use stretch index 0 and 1, if they exist
            if(is_array($mask['stretchTop'][1])){
                $lastIndex = 1;
            }else{
                $lastIndex = 0;
            }
            $afterFirstPart = 0;
            $unusedHeight = $destHeight;
            // Unstretched parts
            if($mask['stretchTop'][0]['start'] != 0){
                // copying the first unstretched part for cases #3, #4, #5, #7
                $firstHeight = $mask['stretchTop'][0]['start'];
                imageCopy($destImage,$srcImage,0,0,0,0,$destWidth,$firstHeight);
                $unusedHeight -= $firstHeight;
                $afterFirstPart = $firstHeight;
            }
            if($mask['stretchTop'][$lastIndex]['stop'] != 0){
                // copying the last unstretched part for cases #2, #4, #6, #7
                $lastHeight = $srcHeight - $mask['stretchTop'][$lastIndex]['stop'];
                $srcLastStart = $mask['stretchTop'][$lastIndex]['stop'];
                $destLastStart = $destHeight - $lastHeight;
                imageCopy($destImage,$srcImage,0,$destLastStart,0,$srcLastStart,$destWidth,$lastHeight);
                $unusedHeight -= $lastHeight;
            }
            if($lastIndex == 1){
                // copying the middle unstretched part for cases #5, #6, #7
                $thisHeight = $mask['stretchTop'][1]['start'] - $mask['stretchTop'][0]['stop'];
                $unusedHeight -= $thisHeight;
                $startingPoint = $afterFirstPart + $unusedHeight - round($unusedHeight / 2);
                imageCopy($destImage,$srcImage,
                    0,$startingPoint,
                    0,$mask['stretchTop'][0]['stop'],
                    $destWidth,$thisHeight);
            }

            // Stretched parts
            if($lastIndex == 0){
                $stretchHeight = $unusedHeight;
            }else{
                $stretchHeight = round($unusedHeight / 2);
                $addToFirst = $unusedHeight - 2 * $stretchHeight;
            }
            if($unusedHeight > 0 && $mask['stretchTop'][0]['start'] == 0){
                // copying the first stretched part for cases #2, #6
                $originalStretchHeight = $mask['stretchTop'][0]['stop'] - $mask['stretchTop'][0]['start'];
                imageCopyresized($destImage,$srcImage,
                    $afterFirstPart,0,
                    $afterFirstPart,0,
                    $destWidth,$stretchHeight + $addToFirst,
                    $destWidth,$originalStretchHeight);
                $unusedHeight -= $stretchHeight + $addToFirst;
            }
            if($unusedHeight > 0 && !isset($mask['stretchTop'][$lastIndex]['stop'])){
                // copying the last stretched part for cases #3, #5
                $destStartingPoint = $destHeight - $stretchHeight;
                $originalStretchHeight = $srcHeight - $mask['stretchTop'][$lastIndex]['start'];
                imageCopyresized($destImage,$srcImage,
                    0,$destStartingPoint,
                    0,$mask['stretchTop'][$lastIndex]['start'],
                    $destWidth,$stretchHeight,
                    $destWidth,$originalStretchHeight);
                $unusedHeight -= $stretchHeight;
            }
            if($unusedHeight > 0 && $mask['stretchTop'][0]['start'] != 0){
                // copying the first stretched part for cases #4, #5, #7
                $originalStretchHeight = $mask['stretchTop'][0]['stop'] - $mask['stretchTop'][0]['start'];
                imageCopyresized($destImage,$srcImage,
                    0,$mask['stretchTop'][0]['start'],
                    0,$mask['stretchTop'][0]['start'],
                    $destWidth,$stretchHeight,
                    $destWidth,$originalStretchHeight);
                $unusedHeight -= $stretchHeight;
            }
            if($unusedHeight > 0){
                // copying the last stretched part for cases #6, #7
                $destStartingPoint = $destLastStart - $stretchHeight;
                $originalStretchHeight = $mask['stretchTop'][1]['stop'] - $mask['stretchTop'][1]['start'];
                imageCopyresized($destImage,$srcImage,
                    0,$destStartingPoint,
                    0,$mask['stretchTop'][1]['start'],
                    $destWidth,$stretchHeight,
                    $destWidth,$originalStretchHeight);
                $unusedHeight -= $stretchHeight;
            }
        }
        // We don't need the original image anymore, so we free the space
        imagedestroy($srcImage);
        // We create the png and free the space
        imagepng($destImage,$destImageName);
        imageDestroy($destImage);

        return true;
    }

    public function buildTextImage($file,$text,$font,$textHeight){
        $type='font';
        $position='normal';

        $texts = array($text);

        $box = $this->getMultipleImagesBox($texts,$font,$textHeight,$type);

        $this->stretchImage(
            $type,
            $position,
            '',
            $file,
            $box['width'],
            $box['height']
        );

        return $this->tagImage(
            $type,
            $position,
            '',
            $file,
            $text,
            $font,
            $box['fontSize'],
            $box['images'][0]['startX'],
            $box['images'][0]['startY'],
            '000000'
        );
    }

    /**
     * public function tagImage
     * USED
     */
    public function tagImage($type,$position,$state,$file,$text,$font,$fontSize,$startX=null,$startY=null,$textColorRGB = 'variation'){
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $addReflect = true;
        $site = $this->links->site;
        $templateFolder = $site->templateFolder;

        // Gets the font and its params
        if(strpos($font,SH_FONTS_FOLDER) === false){
            $font = str_replace(SH_FONTS_PATH,SH_FONTS_FOLDER, $font);
        }
        $fontParamsFile = substr($font,0,-3).'php';
        if(!file_exists($font) || !file_exists($fontParamsFile)){
            return false;
        }
        include($fontParamsFile); // Inserts a variable called $boxes
        foreach($boxes as $textHeight=>$box){

            if($box['fontSize'] == $fontSize){
                break;
            }
        }
        if($textColorRGB == 'variation'){
            $variation = $site->variation;
            if($state == 'active'){
                $textColorRGB = str_replace('#','',$this->links->variation->get(
                    'buttonTextActive|buttonText',
                    '999999'
                ));
            }elseif($state == 'selected'){
                $textColorRGB = str_replace('#','',$this->links->variation->get(
                    'buttonTextSelected|buttonText',
                    '999999'
                ));
            }else{
               $textColorRGB = str_replace('#','',$this->links->variation->get(
                'buttonText',
                 '999999'
                ));
            }
        }

        // text color
        list($r,$g,$b) = str_split($textColorRGB,2);
        $color['R'] = hexDec($r);
        $color['G'] = hexDec($g);
        $color['B'] = hexDec($b);

        // Gets the un-tagged image, and prepares it
        $srcImage = imagecreatefrompng($file);
        imagesavealpha($srcImage, true);
        $width = imagesX($srcImage);
        $height = imagesY($srcImage);

        $newImage = imageCreateTrueColor($width,$height);
        imagesavealpha($newImage, true);

        $textColor = imagecolorallocate($newImage, $color['R'], $color['G'], $color['B']);

        $transparentColor = imagecolorallocatealpha($newImage, 0, 0, 0,127);

        imagefill($newImage,0,0,$transparentColor);
        imagecopy($newImage, $srcImage, 0, 0, 0, 0, $width, $height);
        // Free the space
        imagedestroy($srcImage);

        imagecolortransparent($newImage, $transparentColor);

        if($startX == null || strtoupper($startX) == 'NULL'){
            if(file_exists(SH_TEMPLATE_BUILDER.$type.'/model/'.$position.'.php')){
                include(SH_TEMPLATE_BUILDER.$type.'/model/'.$position.'.php');
                $textBox = imagettfbbox($fontSize,0,$font,$text);
                $startX = $image['startLeft'] + $box['left'];
                $startY = $image['startTop'] + $box['top'];
            }
        }


        if($addReflect == true){
            $reflect = imagecreatetruecolor($width,$textHeight / 2);
            $textColor2 = imagecolorallocate(
                $reflect,
                $color['R'],
                $color['G'],
                $color['B']
            );
            $transparentColor2 = imagecolorallocatealpha(
                $reflect,
                $transparentColorRGB['R'],
                $transparentColorRGB['G'],
                $transparentColorRGB['B'],
                127
            );
            imagefill($reflect,0,0,$transparentColor2);
            imagecolortransparent($reflect, $transparentColor2);

            // writes the text
            imagettftext(
                $reflect,
                $fontSize,
                0,
                $startX,
                $image['startTop'],
                $textColor2,
                $font,
                $text);
                $cpt = 0;
                $open = false;
                // Gets the pixels that are colored in the first line
                for($a = 1; $a<$width + 1;$a++){
                    for($b = 1; $b<($textHeight / 2) - 1;$b++){
                        $color = imagecolorat($reflect, $a, $b);
                        $colorrgb = imagecolorsforindex($reflect,$color);

                        if($colorrgb['alpha'] < 67){
                            $trans = $colorrgb['alpha'] + 127 - ($b / ($textHeight / 2)) * 67;
                            if($trans<127){
                                $tempColor = imagecolorallocatealpha($newImage,
                                    $colorrgb['red'],
                                    $colorrgb['green'],
                                    $colorrgb['blue'],
                                    $trans
                                );
                                imagesetpixel(
                                    $newImage,
                                    $a,
                                    $startY + ($textHeight / 2) - $b,
                                    $tempColor
                                );
                            }
                        }
                    }
                }
            imageDestroy($reflect);
        }

        // writes the text
        imagettftext(   $newImage,
            $fontSize,
            0,
            $startX,
            $startY,
            $textColor,
            $font,
            $text);


        imagepng($newImage,$file);
        imageDestroy($newImage);
        return true;
    }

    public function addText($text,$image,$x,$y,$font,$fontSize,$fontColor,$transparency = 0,$addReflect = false){
        $srcImage = imagecreatefrompng($image);
        $width = imagesx($srcImage);
        $box = $this->getDimensions($text, $fontSize, $font);
        $textHeight = $box['height'];
        imagesavealpha($srcImage, true);
        
        $color = sh_colors::RGBStringToRGBArray($fontColor);
        $r = $color['R'];
        $g = $color['G'];
        $b = $color['B'];
        
        $newColor = imagecolorallocatealpha($srcImage,$r,$g,$b,$transparency);

        if($addReflect == true){
            $reflect = imagecreatetruecolor($width,$textHeight / 2);
            $textColor2 = imagecolorallocatealpha(
                $reflect,
                $r,
                $g,
                $b,
                0
            );
            $transparentColor2 = imagecolorallocatealpha(
                $reflect,
                0,
                0,
                0,
                127
            );
            imagefill($reflect,0,0,$transparentColor2);
            imagecolortransparent($reflect, $transparentColor2);

            // writes the text
            imagettftext(
                $reflect,
                $fontSize,
                0,
                $x,
                $textHeight / 2,
                $textColor2,
                $font,
                $text
            );
            $cpt = 0;
            $open = false;
            // Gets the pixels that are colored in the first line
            for($a = 1; $a<$width + 1;$a++){
                for($b = 1; $b<($textHeight / 2) - 1;$b++){
                    $color = imagecolorat($reflect, $a, $b);
                    $colorrgb = imagecolorsforindex($reflect,$color);

                    if($colorrgb['alpha'] < 45){
                        $trans = $colorrgb['alpha'] + 127 - ($b * 1.5 / ($textHeight / 2)) * 45;
                        if($trans<127){
                            $tempColor = imagecolorallocatealpha($srcImage,
                                $colorrgb['red'],
                                $colorrgb['green'],
                                $colorrgb['blue'],
                                $trans
                            );
                            imagesetpixel(
                                $srcImage,
                                $a,
                                $y + ($textHeight / 2) - $b,
                                $tempColor
                            );
                        }
                    }
                }
            }
            imageDestroy($reflect);
        }

        // writes the text
        imagettftext(
            $srcImage,
            $fontSize,
            0,
            $x,
            $y,
            $newColor,
            $font,
            $text
        );

        imagepng($srcImage,$image);
        imageDestroy($srcImage);
        return true;
    }

    public function cropEmptyParts($image,$margins = 5,$horizontally = true, $vertically = true){
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);

        if(!file_exists($image)){
            echo 'We return false<br />';
            return false;
        }

        $original = imagecreatefrompng($image);
        //$black = imagecolorallocatealpha($original, 0, 0, 0, 20);

        // Defines the size of the image
        $width = imagesx($original);
        $height = imagesy($original);

        // Gets the pixels that are colored in the first line
        for($x = 1; $x<$width + 1;$x++){
            echo '.';
            flush();
            for($y = 1; $y<$height + 1;$y+=1){
                $rgb = imagecolorat($im, $x, $y);
                $transparency = ($rgba & 0x7F000000) >> 24;
                if($transparency != 127){
                    $xNotTransparent[$x] = true;
                    $yNotTransparent[$y] = true;
                }
            }
        }
        echo '<hr />From left: ';
        // Croping from left:
        for($x = 1; $x<$width + 1;$x++){
            echo '.';
            flush();
            if($xNotTransparent[$x]){
                $start['x'] = $x;
            }else{
                echo 'We should crop at column '.($x + $margins).'<br />';
                //imageline($original, $x, 0, $x, $height, $black);
            }
            if($xNotTransparent[$width - $x]){
                $stop['x'] = $width - $x;
            }else{
                echo 'We should crop at column '.($width - $x + $margins).'<br />';
                //imageline($original, $width - $x, 0, $width - $x, $height, $black);
            }
        }

        //imagepng($original,$image);
        imagedestroy($original);
        return true;
    }

    /**
     * public function prepareButtons
     * Explodes the images to prepare the adding of variations
     * USED
     */
    public function prepareButtons($type,$variation){
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $variationFolder = SH_TEMPLATE_BUILDER.$type.'/variations/'.$variation;
        if(!is_dir($variationFolder)){
            mkdir($variationFolder);
        }
        // Lists the files
        if(file_exists(SH_TEMPLATE_BUILDER.$type.'/menu.php')){
            if(!file_exists(SH_TEMPLATE_BUILDER.$type.'/'.self::NORMAL.'.png')){
                return false;
            }
            $files[] = self::NORMAL.'.png';

            if(file_exists(SH_TEMPLATE_BUILDER.$type.'/'.self::FIRST.'.png')){
                $files[] = self::FIRST.'.png';
            }
            if(file_exists(SH_TEMPLATE_BUILDER.$type.'/'.self::LAST.'.png')){
                $files[] = self::LAST.'.png';
            }
        }else{
            $scannedFiles = scandir(SH_TEMPLATE_BUILDER.$type.'/');
            foreach($scannedFiles as $file){
                if(array_pop(explode('.',$file)) == 'png'){
                    $files[] = $file;
                }
            }
        }

        // Put them in the correct folder, and prepare them for rendering (resize and tag)
        if(file_exists(SH_TEMPLATE_BUILDER.$type.'/resize.php')){
            if(is_array($files)){
                foreach($files as $file){
                    $this->explodeImage($type,$file);
                 }
             }
        }else{
            if(is_array($files)){
                foreach($files as $file){
                    rename(SH_TEMPLATE_BUILDER.$type.'/'.$file,SH_TEMPLATE_BUILDER.$type.'/model/'.$file);
                 }
             }
        }
        return true;
    }

    /**
     * protected function explodeImage
     * USED
     */
    protected function explodeImage($type,$imageFile){
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $folder = SH_TEMPLATE_BUILDER.$type.'/';
        if(!file_exists($folder.$imageFile)){
            // This state hasn't any png file
            return false;
        }

        $original = imagecreatefrompng($folder.'/'.$imageFile);

        $destDir = SH_TEMPLATE_BUILDER.$type.'/model/';
        if(!is_dir($destDir)){
            mkdir($destDir);
        }

        // Sets the text mask file name and include if it already exists
        $textFile = $destDir.str_replace('.png','.php',$imageFile);
        if(file_exists($textFile)){
            include($textFile);
            return $image;
        }

        // Defines the size of the image
        $ret['width'] = imagesx($original) - 2;
        $ret['height'] = imagesy($original) - 2;

        $cpt = 0;
        $open = false;
        // Gets the pixels that are colored in the first line
        for($a = 1; $a<$ret['width'] + 1;$a++){
            $rgb = imagecolorat($original, $a, 0);
            $alpha = ($rgb & 0x7F000000) >> 24;
            if($alpha < 110){
                if($open == false){
                    $ret['stretchLeft'][$cpt]['start'] = $a - 1;
                    $open = true;
                }
            }elseif($open){
                $ret['stretchLeft'][$cpt]['stop'] = $a - 1;
                $open = false;
                $cpt++;
            }
        }
        $cpt = 0;
        $open = false;
        // Gets the pixels that are colored in the first column
        for($a = 1; $a<$ret['height'] + 1;$a++){
            $rgb = imagecolorat($original, 0, $a);
            $alpha = ($rgb & 0x7F000000) >> 24;
            if($alpha < 110){
                if($open == false){
                    $ret['stretchTop'][$cpt]['start'] = $a - 1;
                    $open = true;
                }
            }elseif($open){
                $ret['stretchTop'][$cpt]['stop'] = $a - 1;
                $open = false;
                $cpt++;
            }
        }

        $open = false;
        // Gets the pixels that are colored in the last line
        for($a = 1; $a<$ret['width'] + 1;$a++){
            $rgb = imagecolorat($original, $a, $ret['height'] + 1);
            $alpha = ($rgb & 0x7F000000) >> 24;
            if($alpha < 110){
                if($open == false){
                    $ret['startLeft'] = $a - 1;
                    $open = true;
                }else{
                    $ret['stopLeft'] = $a;
                    $open = false;
                }
            }
        }
        $open = false;
        // Gets the pixels that are colored in the last column
        for($a = 1; $a<$ret['height'] + 1;$a++){
            $rgb = imagecolorat($original, $ret['width'] + 1, $a);
            $alpha = ($rgb & 0x7F000000) >> 24;
            if($alpha < 110){
                if($open == false){
                    $ret['startTop'] = $a - 1;
                    $open = true;
                }else{
                    $ret['stopTop'] = $a;
                    $open = false;
                }
            }
        }

        $ret['fixWidth'] = $ret['width'] - $ret['stopLeft'] + $ret['startLeft'];
        $ret['fixHeight'] = $ret['height'] - $ret['stopTop'] + $ret['startTop'];

        // Writes the params file for the image
        $this->links->helper->writeArrayInFile($textFile,'image',$ret);

        $newImage = imagecreatetruecolor($ret['width'], $ret['height']);
        $transparent = imagecolorallocate($newImage, $transparentColor['R'], $transparentColor['G'], $transparentColor['B']);
        imagefill($newImage, 0, 0, $transparent);
        imagecolortransparent($newImage,$transparent);
        ImageAlphaBlending($newImage, false);
        imageSaveAlpha($newImage, true);

        imageCopy($newImage,$original,0,0,1,1,$ret['width'],$ret['height']);
        imagePng($newImage,$destDir.$imageFile);
        imageDestroy($newImage);
        imageDestroy($original);

        unlink($folder.'/'.$imageFile);
        return $content;
    }

    /**
     * public function getMultipleImagesBox
     * Creates the box that will contain the image
     * USED
     */
    public function getMultipleImagesBox($texts,$font,$textHeight,$type){
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $allText = implode('',$texts);
        $folder = SH_TEMPLATE_BUILDER.$type.'/';
        // Creating or getting text masks
        if(file_exists($folder.'model/'.self::NORMAL.'.php')){
            include($folder.'model/'.self::NORMAL.'.php');
            $this->textMasks[self::NORMAL] = $image;
        }else{
            echo __CLASS__.':'.__LINE__.' - File '.$folder.'model/'.self::NORMAL.'.php not found!<br />';
            return false;
        }
        if(file_exists($folder.'model/'.self::FIRST.'.png') && file_exists($folder.'model/'.self::FIRST.'.php')){
            include($folder.'model/'.self::FIRST.'.php');
            $this->textMasks[self::FIRST] = $image;
            $thereIsFirst = true;
        }
        if(file_exists($folder.'model/'.self::LAST.'.png') && file_exists($folder.'model/'.self::LAST.'.php')){
            include($folder.'model/'.self::LAST.'.php');
            $this->textMasks[self::LAST] = $image;
            $thereIsLast = true;
        }

        list($fontSize,$deltaY) = $this->getFontSizeByTextHeight($allText,$font,$textHeight);

        $allTextDim = $this->getDimensions($allText,$fontSize,$font);
        $lettersBottom = $allTextDim['box'][1];

        $height = $textHeight + $this->textMasks[self::NORMAL]['fixHeight'];

        $cpt=0;
        foreach($texts as $key=>$text){
            $newWidth = $this->getDimensions($text, $fontSize, $font);

            if($cpt==0 && $thereIsFirst){
                $position = self::FIRST;
            }elseif($cpt==(count($texts)-1) && $thereIsLast){
                $position = self::LAST;
            }else{
                $position = self::NORMAL;
            }
            $startX = $this->textMasks[$position]['startLeft'];
            $startY = $height - $this->textMasks[$position]['startBottom'];
            //var_dump($this->textMasks[$position]);
            $startY = $textHeight + $this->textMasks[$position]['startTop'] - $lettersBottom;
            $thisWidth = $this->textMasks[$position]['fixWidth'] + $newWidth['width'];

            $images[$key] = array(
                'startX'=>$startX,
                'startY'=>$startY,
                'text'=>$text,
                'width'=>$thisWidth,
                'height'=>$height,
                'position'=>$position
            );

            $width += $thisWidth;
            $cpt++;
        }

        return array(   'width'=>$width,'height'=>$height,
                        'textWidth'=>$thisWidth,'textHeight'=>$textHeight,
                        'font'=>$font,'fontSize'=>$fontSize,'deltaY'=>$deltaY,
                        'images'=>$images);
    }

    /**
     * public function getDimensions
     * USED
     */
    public function getDimensions($text, $size = '', $font = ''){
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        if($font == ''){
            $font = $this->font;
        }
        if($size == '' && $this->fontSize[0]>0){
            $size = $this->fontSize[0];
        }

        $box = imagettfbbox($size,0,$font,$text);

        $min_x = min(array($box[0], $box[2], $box[4], $box[6]));
        $max_x = max(array($box[0], $box[2], $box[4], $box[6]));
        $min_y = min(array($box[1], $box[3], $box[5], $box[7]));
        $max_y = max(array($box[1], $box[3], $box[5], $box[7]));

        $this->dimensions = array(
                'left' => ($min_x >= -1) ? -abs($min_x + 1) : abs($min_x + 2),
                'top' => abs($min_y),
                'width' => $max_x - $min_x,
                'height' => $max_y - $min_y,
                'box' => $box,
                'text' => $text
        );
        return $this->dimensions;
    }

    /**
     * public function getFontSizeByTextHeight
     * returns an array of
     * 1 -> the font size
     * 2 -> The delta to apply to fit the wanted value
     * USED
     */
    public function getFontSizeByTextHeight($text,$font='',$height=0){
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        if($font == ''){
            $font = $this->font;
        }
        if($height == 0){
            $height = $this->textHeight;
        }
        $size = $height;
        $done = array();
        while(true){
            // Counter, not to loop infinitely
            $cpt++;
            $borders = $this->getDimensions($text,$size,$font);
            if($borders['height'] == $height){
                // We found the exact value
                return array($size,0,$borders);
            }
            $done[$size] = $borders['height'];
            if($borders['height'] > $height){
                if(isset($done[$size - 1])){
                    // No exact value, we return the one before, with the delta
                    return array($size - 1, $height - $done[$size - 1],$borders);
                }
                // let's loop again
                $size--;
            }elseif($borders['height'] < $height){
                if(isset($done[$size + 1])){
                   // No exact value, we return the one before, with the delta
                   return array($size, $height - $done[$size],$borders);
                }
                // let's loop again
                $size++;
            }
            if($cpt>50){
                return false;
            }
        }
    }

    public function __tostring(){
        return get_class();
    }
}
