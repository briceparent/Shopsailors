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
 * Creates buttons
 */
class sh_buttonsBuilder extends sh_core{
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


    /**
     * public function setParams
     *
     */
    public function setParams($type,$variation,$font,$textHeight){
        $templatePath = $this->links->html->getTemplatePath();
        $modelPath = $templatePath.'builder/'.$type.'/';
        $this->buttonParams = new sh_params($modelPath.'params.php');
        $this->type = $type;
        $this->variation = $variation;
        $this->font = SH_FONTS_FOLDER.$font;
        $this->textHeight = $textHeight;
        $this->createParams();
    }

    /**
     * protected function createParams
     *
     */
    protected function createParams(){
        // Creating or getting text masks
        $this->textMasks[self::NORMAL] = $this->getTextMask($this->type,self::NORMAL);
        if(!$this->textMasks[self::NORMAL]){
            echo 'NO NORMAL FILE FOUND!!!';
            return 'NO NORMAL FILE FOUND!!!';
        }
        $this->textMasks[self::FIRST] = $this->getTextMask($this->type,self::FIRST);
        if(!$this->textMasks[self::FIRST]){
            $this->textMasks[self::FIRST] = $this->textMasks[self::NORMAL];
        }
        $this->textMasks[self::LAST] = $this->getTextMask($this->type,self::LAST);
        if(!$this->textMasks[self::LAST]){
            $this->textMasks[self::LAST] = $this->textMasks[self::NORMAL];
        }

    }

    /**
     * public function setColors
     *
     */
    public function setColors($colors){
        $this->colors = $colors;
        if(!is_array($colors['passive'])){
            if($colors['passive'] == ''){
                $this->colors['passive']['R'] = 0;
                $this->colors['passive']['G'] = 0;
                $this->colors['passive']['B'] = 0;
            }else{
                $this->colors['passive'] = $this->hexToRGB($colors['passive']);
            }
        }
        if(isset($colors['active']) && !is_array($colors['active'])){
            if($colors['active'] === 0){
                $this->colors['active']['R'] = 0;
                $this->colors['active']['G'] = 0;
                $this->colors['active']['B'] = 0;
            }else{
                $this->colors['active'] = $this->hexToRGB($colors['active']);
            }
        }
        if(isset($colors['selected']) && !is_array($colors['selected'])){
            if($colors['selected'] === 0){
                $this->colors['selected']['R'] = 0;
                $this->colors['selected']['G'] = 0;
                $this->colors['selected']['B'] = 0;
            }else{
                $this->colors['selected'] = $this->hexToRGB($colors['selected']);
            }
        }
        return true;
    }

    /**
     * protected function hexToRGB
     *
     */
    protected function hexToRGB($color){
        $color = str_replace('#','', strtolower($color));
        $length = strlen($color);
        if($length == 3){
            $out['R'] = hexdec(substr($color, 0,1));
            $out['G'] = hexdec(substr($color, 1,1));
            $out['B'] = hexdec(substr($color, 2,1));
        }elseif($length == 6){
            $out['R'] = hexdec(substr($color, 0,2));
            $out['G'] = hexdec(substr($color, 2,2));
            $out['B'] = hexdec(substr($color, 4,2));
        }
        return $out;
    }

    /**
     * protected function newImage
     *
     */
    protected function newImage($width,$height,$transparent){
        $newImage = imagecreatetruecolor($width, $height);
        $transparent = imagecolorallocate($newImage, $transparent['R'], $transparent['G'], $transparent['B']);
        imagefill($newImage, 0, 0, $transparent);
        imagecolortransparent($newImage,$transparent);
        imageSaveAlpha($newImage, true);
        ImageAlphaBlending($newImage, false);
        return $newImage;
    }

    /**
     * protected function getTextMask
     *
     */
    protected function getTextMask($type = '',$position = self::NORMAL){
        if($type == ''){
            $type = $this->type;
        }
        $folder = SH_TEMPLATE_BUILDER.$type.'/';
        if(!file_exists($folder.$position.'_'.self::TEXT.'.png')){
            return false;
        }
        $original = imagecreatefrompng($folder.'/'.$position.'_'.self::TEXT.'.png');

        $destDir = SH_TEMPLATE_BUILDER.$type.'/masks/';
        if(!is_dir($destDir)){
            mkdir($destDir);
        }
        $textMaskFile = $destDir.$position.'_textMask.php';
        if(file_exists($textMaskFile)){
            include($textMaskFile);
            return $textMask;
        }
        if(!is_dir($folder)){
            mkdir($folder);
        }
        $width = imagesx($original);
        $height = imagesy($original);
        for($a = 0; $a<$width;$a++){
            $rgb = imagecolorat($original, $a, 0);
            $alpha = ($rgb & 0x7F000000) >> 24;
            if($alpha != 127){
                $found['x'][] = $a;
            }
        }
        for($a = 0; $a<$height;$a++){
            $rgb = imagecolorat($original, 0, $a);
            $alpha = ($rgb & 0x7F000000) >> 24;
            if($alpha != 127){
                $found['y'][] = $a;
            }
        }
        $found['x'][0]++;
        $found['y'][0]++;

        $ret['startLeft'] = $found['x'][0] - 1;
        $ret['startBottom'] = $height - $found['y'][1] - 1;

        $ret['fixWidth'] = $width - $found['x'][1] + $found['x'][0] - 2;
        $ret['fixHeight'] = $height - $found['y'][1] + $found['y'][0] - 2;

        if(count($found['x']) == 2 && count($found['x']) == 2){
            $content = "<?php
/**
 * Details file
 * Licensed under LGPL
 */

if(!defined('SH_MARKER')){
        header('location: directCallForbidden.php');
}\n\n";
            $content .= '$textMask = '.var_export($ret,true).";\n";
            $textMaskFile = fopen($textMaskFile,'w');
            $rep = fwrite($textMaskFile,$content) ;
            fclose($textMaskFile);
            return $found;
        }
    }

    /**
     * protected function buildBackground
     * Takes the orimginal image, and stretches it to the wanted dimensions
     */
    protected function buildBackground($image, $type = '',$position = self::NORMAL, $state = self::PASSIVE){
        // Sets the type if needed
        if($type == ''){
            $type = $this->type;
        }
        // Verifies the existance of the stretch file
        if(!file_exists(SH_TEMPLATE_BUILDER.$type.'/'.$position.'_'.self::STRETCH.'.png')){
            echo "Le fichier $type./.$position._.self::STRETCH.png n'a pas été trouvé...<br />";
            return false;
        }
        // creates the folder if needed
        $folder = SH_TEMPLATE_BUILDER.$type.'/masks/';
        if(!is_dir($folder)){
            mkdir($folder);
        }

        // Gets the mask
        $maskImage = imagecreatefrompng(SH_TEMPLATE_BUILDER.$type.'/'.$position.'_'.self::STRETCH.'.png');
        // Gets the un-stretched image, and prepares it
        $originalImage = imagecreatefrompng(SH_TEMPLATE_BUILDER.$type.'/'.$position.'_'.$state.'.png');
        ImageAlphaBlending($originalImage, false);
        imageSaveAlpha($originalImage, true);

        // Reads some dimensions
        $maskWidth = imagesx($maskImage);
        $maskHeight = imagesy($maskImage);

        $destWidth = imagesx($image);
        $destHeight = imagesy($image);


        $temp1 = imagecreatetruecolor($destWidth, $destHeight);
        $transparent = imagecolorallocate($temp1, 1,2,3);
        imagefill($temp1, 0, 0, $transparent);
        imagecolortransparent($temp1,$transparent);
        ImageAlphaBlending($temp1, false);
        imageSaveAlpha($temp1, true);

        $found = array('x'=>array(-1),'y'=>array(-1));
        $open = false;
        for($a = 0; $a<$maskWidth;$a++){
            $rgb = imagecolorat($maskImage, $a, 0);
            $alpha = ($rgb & 0x7F000000) >> 24;
            if(!$open && $alpha != 127){
                $open = true;
                $found['x'][] = $a;
            }elseif($open && $alpha == 127){
                $found['x'][] = $a-1;
                $open = false;
            }
        }
        for($a = 0; $a<$maskHeight;$a++){
            $rgb = imagecolorat($maskImage, 0, $a);
            $alpha = ($rgb & 0x7F000000) >> 24;
            if(!$open && $alpha != 127){
                $open = true;
                $found['y'][] = $a;
            }elseif($open && $alpha == 127){
                $found['y'][] = $a-1;
                $open = false;
            }
        }

        $found['x'][] = $maskWidth;
        $found['y'][] = $maskHeight;
        $min['x'] = -1;
        imageDestroy($maskImage);

        // Horizontal copies
        $firstWidth = $found['x'][1] - $found['x'][0] - 1;
        imageCopy($temp1,$originalImage,0,0,0,0,$firstWidth,$destHeight);
        if(count($found['x']) == 4){
            // right copy
            $thirdWidth = $found['x'][5] - $found['x'][4];
            $thirdDestX = $destWidth - $thirdWidth;
            imageCopy($temp1,$originalImage,$thirdDestX,0,$found['x'][4],0,$thirdWidth,$destHeight);
            // center resized copy
            $tempSrcWidth = $found['x'][2] - $found['x'][1] - 1;
            $tempDestWidth = $destWidth - $firstWidth - $secondWidth;
            imageCopyresized($temp1,$originalImage,$firstWidth,0,$firstWidth,0,$tempDestWidth,$destHeight,$tempSrcWidth,$destHeight);
        }elseif(count($found['x']) == 6){
            // center copy
            $secondWidth = $found['x'][3] - $found['x'][2] - 1;
            $secondDestX = ($destWidth / 2) - ($secondWidth / 2);
            imageCopy($temp1,$originalImage,$secondDestX,0,$found['x'][2],0,$secondWidth,$destHeight);
            // right copy
            $thirdWidth = $found['x'][5] - $found['x'][4];
            $thirdDestX = $destWidth - $thirdWidth;
            imageCopy($temp1,$originalImage,$thirdDestX,0,$found['x'][4],0,$thirdWidth,$destHeight);
            // left resized copy
            $tempSrcWidth = $found['x'][2] - $found['x'][1] - 1;
            $tempDestWidth = $secondDestX - $firstWidth;
            imageCopyresized($temp1,$originalImage,$firstWidth,0,$firstWidth,0,$tempDestWidth,$destHeight,$tempSrcWidth,$destHeight);
            // right resized copy
            $tempSrcWidth = $found['x'][4] - $found['x'][3] - 1;
            $tempDestX = $secondDestX + $secondWidth;
            $tempDestWidth = $thirdDestX - $tempDestX + 1;
            imageCopyresized($temp1,$originalImage,$tempDestX,0,$found['x'][3],0,$tempDestWidth,$destHeight,$tempSrcWidth,$destHeight);
        }
        imageDestroy($originalImage);

        // Vertical copies
        $firstHeight = $found['y'][1] - $found['y'][0] - 1;
        imageCopy($image,$temp1,0,0,0,0,$destWidth,$firstHeight);
        if(count($found['y']) == 4){
            // bottom copy
            $thirdHeight = $found['y'][5] - $found['y'][4];
            $thirdDestY = $destHeight - $thirdHeight;
            imageCopy($image,$temp1,0,$thirdDestY,0,$found['y'][4],$destWidth,$thirdHeight);
            // middle resized copy
            $tempSrcHeight = $found['y'][2] - $found['y'][1] - 1;
            $tempDestHeight = $destHeight - $firstHeight - $secondHeight;
            imageCopyresized($image,$temp1,0,$firstHeight,0,$firstHeight,$tempDestHeight,$destHeight,$destWidth,$tempSrcHeight);
        }elseif(count($found['y']) == 6){
            // middle copy
            $secondHeight = $found['y'][3] - $found['y'][2] - 1;
            $secondDestY = round($destHeight / 2) - round($secondHeight / 2);
            imageCopy($image,$temp1,0,$secondDestY,0,$found['y'][2]+1,$destWidth,$secondHeight);
            // bottom copy
            $thirdHeight = $found['y'][5] - $found['y'][4];
            $thirdDestY = $destHeight - $thirdHeight;
            imageCopy($image,$temp1,0,$thirdDestY,0,$found['y'][4]+1,$destWidth,$thirdHeight);
            // top resized copy
            $tempSrcHeight = $found['y'][2] - $found['y'][1] - 1;
            $tempDestHeight = $secondDestY - $firstHeight;
            imageCopyresized($image,$temp1,0,$firstHeight,0,$firstHeight,$destWidth,$tempDestHeight,$destWidth,$tempSrcHeight);
            // bottom resized copy
            $tempSrcHeight = $found['y'][4] - $found['y'][3] - 1;
            $tempDestY = $secondDestY + $secondHeight;
            $tempDestHeight = $thirdDestY - $tempDestY;
            imageCopyresized($image,$temp1,0,$tempDestY,0,$found['y'][3],$destWidth,$tempDestHeight,$destWidth,$tempSrcHeight);
        }
        imageDestroy($temp1);
        return $image;
    }

    /**
     * public function buildVariation
     * Builds the button's background for a variation
     */
    public function buildVariation($model,$color){
        // creates the folder if needed
        $this->templateParams =& $this->links->html->templateParams;
        $templatePath = $this->links->html->getTemplatePath();
        $variationsFolder = $templatePath.'builder/'.$model.'/variations';
        if(!is_dir($variationsFolder)){
            mkdir($variationsFolder);
        }
        $folder = $variationsFolder.'/'.$color['name'].'/';
        if(!is_dir($folder)){
            mkdir($folder);
        }
        if(file_exists($folder.'button_'.self::NORMAL.'_'.self::PASSIVE.'.png')){
            echo $folder.'button_'.self::NORMAL.'.png existe déjà<br />';
            return false;
        }

        // Verifies the existance of the required files
        if(!file_exists($templatePath.'builder/'.$model.'/'.self::NORMAL.'_model.png')){
            echo 'Le fichier '.$templatePath.'builder/'.$model.'/'.self::NORMAL.'_model.png n\'a pas été trouvé...<br />';
            return false;
        }

        // Prepares the normal background
        $positionLoop[] = self::NORMAL;
        $file[self::NORMAL]=self::NORMAL;

        // Prepares the first background, if needed
        if(file_exists($templatePath.'builder/'.$model.'/'.self::FIRST.'_model.png')){
            $positionLoop[] = self::FIRST;
            $file[self::FIRST]=self::FIRST;
        }else{
            $file[self::FIRST]=self::NORMAL;
        }

        // Prepares the last background, if needed
        if(file_exists($templatePath.'builder/'.$model.'/'.self::LAST.'_model.png')){
            $positionLoop[] = self::LAST;
            $file[self::LAST]=self::LAST;
        }else{
            $file[self::LAST]=self::NORMAL;
        }

        // Prepares the states (passive, selected, active)
        $statesLoop[] = array('name'=>self::PASSIVE);
        $file[self::PASSIVE]=self::PASSIVE;

        // Prepares the selected background, if different from the passive one
        $selectedVariation = $this->buttonParams->get('selected');
        if($selectedVariation !== array('H' => 0,'S' => 0,'V' => 0)){
            $statesLoop[] = array('name'=>self::SELECTED,'color'=>$selectedVariation);
            $file[self::SELECTED]=self::SELECTED;
        }else{
            $file[self::SELECTED]=self::PASSIVE;
        }

        // Prepares the active background, if different from the passive one
        $activeVariation = $this->buttonParams->get('active');
        if($activeVariation !== array('H' => 0,'S' => 0,'V' => 0)){
            $statesLoop[] = array('name'=>self::ACTIVE,'color'=>$activeVariation);
            $file[self::ACTIVE]=self::ACTIVE;
        }else{
            $file[self::ACTIVE]=self::PASSIVE;
        }

        $originalColor = $this->buttonParams->get('color');
        $addedColor = array('H'=>$color['H'] - $originalColor['H'],
                            'S'=>$color['S'] - $originalColor['S'],
                            'V'=>$color['V'] - $originalColor['V']);

        foreach($positionLoop as $position){
            // Gets the original
            $originalImage = $templatePath.'builder/'.$model.'/'.$position.'_model.png';
            foreach($statesLoop as $state){
                $tempAddedColor = array(
                    'H'=>$addedColor['H'] + $state['color']['H'],
                    'S'=>$addedColor['S'] + $state['color']['S'],
                    'V'=>$addedColor['V'] + $state['color']['V']
                    );

                $destImage = $folder.'button_'.$position.'_'.$state['name'].'.png';
                sh_colors::modifyImageWithDelta($originalImage, $destImage, $tempAddedColor);
            }
        }
        $f = fopen($folder.'files.php','w+');
        $fileContent = "<?php
/**
 * This file tells the parts of names that should be used to build the new images
 *
 * Licensed under LGPL
 */

if(!defined('SH_MARKER')){
        header('location: directCallForbidden.php');
}\n\n".'
$files = '.var_export($file,true).";\n";
        fputs($f,$fileContent);
        fclose($f);
        return true;
    }

    /**
     * public function build
     * $type is the base name of the png to use
     * $text is the text...
     * $position['first'], $position['last'] are booleans to tell if the image
     *  is a first, a last, none of that, or both
     * $space serves to add some space before and after the text (half on each side)
     */
    public function build($type, $text, $position = array(), $space = 0){
        // verify the "temp" folder
        if(!is_dir(SH_IMAGES_FOLDER.'temp/')){
            mkdir(SH_IMAGES_FOLDER.'temp/');
        }
        // verify the "generated" folder
        if(!is_dir(SH_IMAGES_FOLDER.'generated/')){
            mkdir(SH_IMAGES_FOLDER.'generated/');
        }

        $newColor = array('H'=>56,'S'=>83,'V'=>78,'name'=>'jaune_poussin');
        $this->buildVariation($type, $newColor);
        return true;

        $this->templateParams =& $this->links->html->templateParams;

        // Prepares all the small images folders, even if they are not used
        if($position['first']){
            $textPosition = self::FIRST;
        }elseif($position['last']){
            $textPosition = self::LAST;
        }else{
            $textPosition = self::NORMAL;
        }
        $textMask = $this->getTextMask($type,$textPosition);


        // Gets the dimensions
        $boxDim = $this->getDimensions($text);

        $imageWidth = $space + $boxDim['width'];

        $totalWidth = $textMask['fixWidth'] + $imageWidth;
        $totalHeight = $textMask['fixHeight'] + $this->textHeight;

        // Loops to make the 3 images
        $state = self::PASSIVE;
        for($a=0;$a<3;$a++){
            $color = $this->colors['passive'];

            // Selects the image we're creating
            if($a == 0){
            $color = $this->colors['passive'];
                $image =& $imageNormal;
            }elseif($a == 1){
                $state = self::SELECTED;
                $image =& $imageSelected;
                if(isset($this->colors['selected'])){
                    $color = $this->colors['selected'];
                }
            }else{
                $state = self::ACTIVE;
                $image =& $imageActive;
                if(isset($this->colors['active'])){
                    $color = $this->colors['active'];
                }
            }

            // Creates a background color [that should not be used anywhere else]
            // to transform it as the transparent color
            if($color['R']<150){
                $rTransparent = $color['R'] + 150;
                }else{
                $rTransparent = $color['R'] - 150;
            }

            $image = imagecreatetruecolor($totalWidth, $totalHeight);

            // Sets the colors, and fill the the image with the transparent color
            $textColor = imagecolorallocate($image, $color['R'], $color['G'], $color['B']);
            $transparent = imagecolorallocate($image, $rTransparent, $color['G'], $color['B']);
            imagefill($image, 0, 0, $transparent);
            imagecolortransparent($image,$transparent);
            ImageAlphaBlending($image, false);
            imageSaveAlpha($image, true);

            // creation of the button itself
            $files = SH_TEMPLATE_BUILDER.$type.'/splitted/'.$textPosition.'_'.$state.'_';


            // Builds the background
            $image = $this->buildBackground($image, $type,$textPosition, $state);

            // ...And saves it as a temporary png file
            if($params['temporary']){
                $fileName = 'temp/'.MD5($file.$text);
            }else{
                $fileName = 'generated/'.MD5($file.$text);
            }

            imagepng($image,SH_IMAGES_FOLDER.$fileName.'_'.$state.'.png');
            imageDestroy($image);
            $image = imagecreatefrompng(SH_IMAGES_FOLDER.$fileName.'_'.$state.'.png');
            ImageAlphaBlending($image, false);
            imageSaveAlpha($image, true);


            // its text
            imagettftext(   $image,
                $this->fontSize[0],
                0,
                $textMask['startLeft'] + $space / 2,
                $totalHeight - $textMask['startBottom'],
                $textColor,
                $this->font,
                $text);

            imagepng($image,SH_IMAGES_FOLDER.$fileName.'_'.$state.'.png');/**/
            if($a == 0){
                $ret = $fileName;
            }
            imageDestroy($image);
        }
        return SH_IMAGES_PATH.$ret;
    }


    /**
     * protected function insertImage
     *
     */
    protected function insertImage($destImage,$srcImage,$x,$y,$w = -1,$h = -1){
        $tempImage = imagecreatefrompng($srcImage);
        $sizes = getImageSize($srcImage);
        if($w<0){
            $w = $sizes[0];
            $h = $sizes[1];
        }
        imagecopyresized(  $destImage,
                    $tempImage,
                    $x,
                    $y,
                    0,
                    0,
                    $w,
                    $h,
                    $sizes[0],
                    $sizes[1]
                );
        return true;
    }

    /**
     * public function getBordersWidth
     *
     */
    public function getButtonsWidth($texts){
        foreach($texts as $text){
            $tempDim = $this->getDimensions($text);
            $textWidth += $tempDim['width'];
        }
        $totalWidth = (count($texts)-2) * $this->textMasks[self::NORMAL]['fixWidth']
                        + $this->textMasks[self::FIRST]['fixWidth']
                        + $this->textMasks[self::LAST]['fixWidth']
                        + $textWidth;
        return $totalWidth;
    }

    /**
     * public function getDimensions
     *
     */
    public function getDimensions($text, $size = '', $font = ''){
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
     * public function setFontSizeByTextHeight
     * sets $fontsize with an array of
     * 1 -> the font size
     * 2 -> The delta to apply to fit the wanted value
     */
    public function setFontSizeByTextHeight($text){
        $height = $this->textHeight;
        $font = $this->font;
        $this->fontSize = getFontSizeByTextHeight($text);
    }

    /**
     * public function getFontSizeByTextHeight
     * returns an array of
     * 1 -> the font size
     * 2 -> The delta to apply to fit the wanted value
     */
    public function getFontSizeByTextHeight($text,$font='',$height=0){
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
                return array($size,0);
            }
            $done[$size] = $borders['height'];
            if($borders['height'] > $height){
                if(isset($done[$size - 1])){
                    // No exact value, we return the one before, with the delta
                    return array($size - 1, $height - $done[$size - 1]);
                }
                // let's loop again
                $size--;
            }elseif($borders['height'] < $height){
                if(isset($done[$size + 1])){
                   // No exact value, we return the one before, with the delta
                   return array($size, $height - $done[$size]);
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
