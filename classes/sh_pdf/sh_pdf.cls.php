<?php
/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')){header('location: directCallForbidden.php');}

include(dirname(__FILE__).'/fpdf16/fpdf.php');

class sh_fpdf extends FPDF{
    protected $textFooter = array();
    public $links = null;

    /**
     * Class constructor
     * @param str $orientation Paper orientation
     * @param str $unit The unit that is used
     * @param str $format The aper size
     */
    public function __construct($orientation='P', $unit='mm', $format='A4'){
        parent::__construct($orientation,$unit,$format);
        $this->SetCreator(utf8_decode('Websailors'));
        $this->linker = sh_linker::getInstance();
    }

    function RoundedRect($x, $y, $w, $h, $r, $style = ''){
        $k = $this->k;
        $hp = $this->h;
        if($style=='F'){
            $op='f';
        }elseif($style=='FD' or $style=='DF'){
            $op='B';
        }else{
            $op='S';
        }
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
        $xc = $x+$w-$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));

        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3){
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
                $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }

    /**
     * Sets the name and address of the client, which will be shown in the header
     * @param str $clientName The name of the client
     * @param str $clientAddress The address of the client
     */
    public function setClient($clientName,$clientAddress){
        $this->clientName = $clientName;
        $this->clientAddress = $this->wordWrap($clientAddress,60);
    }

    /**
     * Sets the name and address of the reseller
     * @param str $sellerName The name of the seller
     * @param str $sellerAddress The address of the seller
     * @deprecated These datas are now managed by the footer
     */
    public function setSeller($sellerName,$sellerAddress){
        $this->sellerName = $sellerName;
        $this->sellerAddress = $this->wordWrap($sellerAddress,60);
    }

    /**
     * Sets some lines that will be present in the header
     * @param str $customerService The contents of the lines
     */
    public function setCustomerService($customerService){
        if(is_array($customerService)){
            $this->customerService = $customerService;
        }else{
            $this->customerService = array();
        }
    }

    /**
     * Sets the bill Id that will be present in the header and in the footer
     * @param str $billId The unic id of the bill
     */
    public function setBillId($billId){
        $this->billId = $billId;
    }

    /**
     * Sets the logo that will be present in the header
     * @param str $image The path to the image
     */
    public function setLogo($image){
        if(file_exists(SH_ROOT_FOLDER.$image)){
            $image = SH_ROOT_FOLDER.$image;
        }
        if(file_exists($image)){
            $imageNameParts = explode('.',$image);
            $type = strtolower(array_pop($imageNameParts));
            if($type == 'png'){
                $filename = implode('.',$imageNameParts).'.jpg';
                if(!file_exists($filename)){
                    // Building a jpg version of the logo
                    $gdImage = imagecreatefrompng($image);

                    $destImage = imagecreatetruecolor(imagesx($gdImage), imagesy($gdImage));
                    $white = imagecolorallocate($destImage, 255, 255, 255);
                    imagefill($destImage, 0, 0, $white);

                    imagecopy($destImage,$gdImage,0,0,0,0,imagesx($gdImage),imagesy($gdImage));

                    imagejpeg($destImage,$filename);
                }
                $image = $filename;
            }
            $this->logo = $image;
        }
    }

    /**
     * Creates the header of the pages, which will be repeated on every single page
     */
    function Header(){
        $cols = array(70,60,70);

        $this->SetFillColor(
            $this->fillColor[0],
            $this->fillColor[1],
            $this->fillColor[2]
        );

        $this->SetDrawColor(
            255,
            255,
            255
        );
        //$this->Image(SH_SHAREDIMAGES_FOLDER.'icons/bill_head.png',5,5,200);
        $this->RoundedRect(5, 5, 70, 30, 5, 'DF');

        $this->RoundedRect(135, 5, 70, 30, 5, 'DF');

        $this->SetFont('vera','',10);
        $this->addCell(0,2,'','',0,'');
        $this->Ln();
        $this->addCell($cols[0],5,'Commande n° '.$this->billId,'',0,'L');
        $this->addCell($cols[1],5);
        $this->addCell($cols[2],5,$this->clientName,'',0,'R');
        $this->Ln();
        $this->addCell($cols[0],5,'Date : '.date('d/m/Y'),'',0,'L');
        $this->addCell($cols[1],5);
        $this->addCell($cols[2],5,$this->clientAddress[0],'',0,'R');
        $this->Ln();
        $this->addCell($cols[0],5,$this->customerService[0],'',0,'L');
        $this->addCell($cols[1],5);
        $this->addCell($cols[2],5,$this->clientAddress[1],'',0,'R');
        $this->Ln();
        $this->addCell($cols[0],5,$this->customerService[1],'',0,'L');
        $this->addCell($cols[1],5);
        $this->addCell($cols[2],5,$this->clientAddress[2],'',0,'R');
        $this->Ln();
        $this->addCell($cols[0],5,$this->customerService[2],'',0,'L');
        $this->SetFont('verabold','',12);
        $this->addCell($cols[1],5,'Facture','',0,'C');
        $this->SetFont('vera','',10);
        $this->addCell($cols[2],5,$this->clientAddress[3],'',0,'R');
        $this->Ln();
        $this->addCell(0,10);
        $this->Ln();

        if(trim($this->logo) != '' && file_exists($this->logo)){
            $authorisedWidth = 50;
            $authorisedHeight = 20;
            $authorisedRate = $authorisedWidth/$authorisedHeight;

            list($imageWidth,$imageHeight) = getimagesize($this->logo);
            $imageRate = $imageWidth / $imageHeight;

            if($authorisedRate >= $imageRate){
                $destHeight = $authorisedHeight;
                $destWidth = $destHeight * $imageRate;
            }else{
                $destWidth = $authorisedWidth;
                $destHeight = $destWidth / $imageRate;
            }
            $imageLeft = (210 - $destWidth) / 2;

            $this->Image($this->logo,$imageLeft,5,$destWidth);
        }

        $this->SetFillColor(224,235,255);
    }

    /**
     * Sets the text of the footer that will be used by self::Footer().
     * @param str $footer The text of the footer
     */
    public function setTextFooter($footer){
        $this->textFooter = $footer;
    }

    /**
     * Creates the footer of the pages, which will be repeated on every single page
     */
    function Footer(){
        $lines = $this->wordWrap($this->textFooter, 200);
        if(count($lines) > 4){
            list($lines) = array_chunk($lines,5);
        }
        $footerHeight = (count($lines) + 1) * 3 + 8;
        //Positionnement du bas
        $this->SetY(-$footerHeight);
        //Police Arial italique 8
        $this->SetFont('veraOblique','',8);
        $generatedText = 'Document généré automatiquement par Shopsailors';
        //Numéro de page
        $this->addCell(
            200,4,
            'Facture n°'.$this->billId.' - Page '.$this->PageNo().'/{nb}',
            0,0,'C'
        );
        $this->Ln();
        $this->addCell(
            200,4,
            $generatedText,
            0,0,'C'
        );
        $top = 297 - $footerHeight + 4;

        $left = (210 -  $this->GetStringWidth(utf8_decode($generatedText))) / 2;

        $left += $this->GetStringWidth(
            utf8_decode(
                array_shift(
                    preg_split(
                        '`Websailors`',
                        $generatedText
                    )
                )
            )
        );
        $this->Link( $left, $top,$this->GetStringWidth('Websailors'), 4, 'http://www.websailors.fr');
        if(is_array($lines)){
            foreach($lines as $oneLine){
                $this->Ln();
                $this->addCell(
                    200,3,
                    $oneLine,
                    0,0,'C'
                );
            }
        }
    }

    /**
     * Creates a table
     * @param array $header The titles of the columns
     * @param array $data The datas
     */
    function createTable($header,$data){
        for($a = 0;$a<count($header);$a++){
            $w[$a] = $this->GetStringWidth($header[$a])+6;
            foreach($data as $oneData){
                $thisWidth = $this->GetStringWidth($oneData[$a])+6;
                if($thisWidth>$w[$a]){
                    $w[$a] = round($thisWidth);
                }
            }
        }
        $w[1] = 200 - array_sum($w) + $w[1];

        //Colors, thickness and font weight for the header
        $this->SetFillColor(
            $this->fillColor[0],
            $this->fillColor[1],
            $this->fillColor[2]
        );
        $this->SetDrawColor(80,80,80);
        $this->SetLineWidth(.3);
        $this->SetFont('verabold','');

        //Headers
        for($i=0;$i<count($header);$i++){
            $this->addCell($w[$i],7,$header[$i],1,0,'C',1);
        }
        $this->Ln();

        //Colors, thickness and font weight for the datas
        $this->SetFillColor(
            (255 + 2*$this->fillColor[0]) / 3,
            (255 + 2*$this->fillColor[1]) / 3,
            (255 + 2*$this->fillColor[2]) / 3
        );
        $this->SetFont('','',10);

        //Datas
        $fill=false;
        foreach($data as $row){
            $nbLines = count($row);
            for($i=0;$i<=$nbLines;$i+=1){
                $lines = 'L';
                if($i == ($nbLines - 3) || $i == ($nbLines - 1)){
                    $lines = 'R';
                }elseif($i == ($nbLines - 2)){
                    $lines = 'C';
                }
                $this->addCell($w[$i],6,$row[$i],'LR',0,$lines,$fill);
            }
            $this->Ln();
            $fill=!$fill;
        }
        $this->addCell(array_sum($w),0,'','T');
        $this->Ln();
    }

    /**
     * Adds a text line to the pdf document, using self::WordWrap
     * @param str $text The text to add
     */
    public function addTextLine($text){
        $this->WordWrap($text,200);
        if(is_array($text)){
            foreach($text as $line){
                $this->addCell(200,5,$line,'');
                $this->Ln();
            }
        }
    }

    /**
     * Adds a cell to a table. Is the same as fpdf::Cell, with a utf8_decode on $txt
     * @param int $w The width of the cell
     * @param int $h The height of the cell
     * @param str $txt The text that is into the cell
     * @param str $border The borders that are drawed
     * @param int $ln Is there a new line after the cell?
     * @param str $align Text alignment
     * @param bool $fill Do we have to fill the cell?
     * @param str $link The link the cell brings to
     * @return bool The return of fpdf::Cell, which actually is nothing
     */
    public function addCell($w = 0, $h = 0, $txt = '', $border = 0, $ln = 0, $align = 'L', $fill = false, $link = ''){
        return $this->Cell($w, $h, utf8_decode($txt), $border, $ln, $align, $fill, $link);
    }

    /**
     * Separates a string into an a string array in which every entry is a line
     * which width is $maxwidth maximum.
     * @param str $text The text to split
     * @param int $maxwidth The maximum width of a line, in mm.
     * @return array The array of strings
     */
    function wordWrap($text, $maxwidth){
        $text = trim($text);
        if ($text===''){
            return 0;
        }
        $spaceWidth = $this->GetStringWidth(' ');
        $lines = explode("\n", $text);
        $count = 0;
        $realSpacer = ' ';
        $realSpacerWidth = $this->GetStringWidth($realSpacer);

        foreach ($lines as $line){
            $line = trim($line);
            $words = preg_split('/ +/', $line);
            $lineWidth = 0;
            $ended = false;
            $spacer = '';
            $spacerWidth = 0;
            $newLine = '';
            while(!$ended){
                // We verify if there are still other words to add
                if(count($words) == 0){
                    $returned[] = $newLine;
                    $ended = true;
                    continue;
                }

                // We get the new word and its written width
                $newWord = array_shift($words);
                $newWordWidth = $this->GetStringWidth($newWord);

                // We verify if it can enter in the max width
                if($lineWidth + $spacerWidth + $newWordWidth < $maxwidth){
                    $lineWidth += $spacerWidth + $newWordWidth;
                    $newLine .= $spacer.$newWord;
                    $spacer = $realSpacer;
                    $spacerWidth = $realSpacerWidth;
                    continue;
                }
                // It can not
                if($newLine == ''){
                    // The single word is too long, so we return it alone for this line;
                    $newLine = $newWord;
                }else{
                    // We put it back at the beggining of the array
                    array_unshift($words,$newWord);
                }
                // We start a new line
                $returned[] = $newLine;
                $newLine = '';
                $lineWidth = 0;
                $spacer = '';
                $spacerWidth = 0;
            }
        }
        return $returned;
    }
}

/**
 * Class that creates pdf files using FPDF.
 */
class sh_pdf extends sh_core {
    /**
     * @var FPDF The fpdf instance
     */
    protected $pdf = null;

    protected function changeEuroTo128(&$value,$key){
        $value = str_replace('€',chr(128),$value);
    }

    /**
     * public function createBill
     *
     */
    public function createBill($datas, $outputName){
        array_walk_recursive($datas, array($this, 'changeEuroTo128'));

        $titles = $datas['titles'];

        define('FPDF_FONTPATH',dirname(__FILE__).'/fonts/');

        $pdf = new sh_fpdf('P','mm','A4');

        if(!is_array($datas['fillColor'])){
            $pdf->fillColor = array(220,220,220);
        }else{
            $pdf->fillColor = $datas['fillColor'];
        }
        $pdf->setTextFooter( $datas['footer']);

        $pdf->setSeller($datas['seller']['name'], $datas['seller']['address']);
        $pdf->setCustomerService($datas['customerService']);
        $pdf->setClient($datas['client']['name'], $datas['client']['address']);
        $pdf->SetAuthor(utf8_decode($datas['author']));
        $pdf->SetTitle(utf8_decode($datas['title']));
        $pdf->SetSubject(utf8_decode($datas['subject']));
        $pdf->setLogo($datas['logo']);
        $pdf->setBillId($datas['billId']);


        $pdf->SetMargins('5','5');
        $pdf->AddFont('vera');
        $pdf->AddFont('verabold');
        $pdf->AddFont('veraoblique');
        $pdf->SetFont('vera','',12);
        $pdf->AddPage();

        if(isset($datas['headLine']) && trim($datas['headLine']) != ''){
            $pdf->SetFont('vera','',10);
            $headLine = $pdf->wordWrap($datas['headLine'], 200);
            foreach($headLine as $oneHeadLine){
                $pdf->addCell(200,4,$oneHeadLine);
                $pdf->Ln();
            }
            $pdf->addCell(200,2);
            $pdf->Ln();
            $pdf->SetFont('vera','',10);
        }

        $pdf->createTable($titles,$datas['elements']);

        $pdf->addCell(0,3,'','');
        $pdf->Ln();

        $totalWidth = max(
            $pdf->GetStringWidth($datas['totalHT'])+5,
            $pdf->GetStringWidth($datas['totalHT'])+5
        );

        $totalTitleWidth = max(
            $pdf->GetStringWidth($datas['totalHTName'])+5,
            $pdf->GetStringWidth($datas['totalTTCName'])+5
        );

        $pdf->addCell(200 - $totalWidth - $totalTitleWidth,5,'','');
        $pdf->addCell($totalTitleWidth,5,$datas['totalHTName'],'TLB');
        $pdf->addCell($totalWidth,5,$datas['totalHT'],'TBR','','R');
        $pdf->Ln();
        $pdf->addCell(200 - $totalWidth - $totalTitleWidth,5,'','');
        $pdf->addCell($totalTitleWidth,5,$datas['totalTTCName'],'LB');
        $pdf->addCell($totalWidth,5,$datas['totalTTC'],'BR','','R');

        $pdf->SetFont('vera','',10);

        // Billing address
        $lines = array();
        $lines[] = $datas['billingAddressIntro'];
        foreach($datas['billingAddress'] as $addressLine){
            if($addressLine != ''){
                $lines[] = $addressLine;
            }
        }
        foreach($lines as $line){
            $widthes[] = $pdf->GetStringWidth($line)+4;
        }
        $width = max($widthes);
        foreach($lines as $num=>$line){
            $pdf->Ln();
            $pdf->addCell($width,4,$line,$borders);
        }
        $pdf->Ln();
        $pdf->addCell(5,4,'','');
        $pdf->Ln();

        // Shipping address
        if(isset($datas['shippingAddressIntro'])){
            $lines = array();
            $lines[] = $datas['shippingAddressIntro'];
            foreach($datas['shippingAddress'] as $addressLine){
                if($addressLine != ''){
                    $lines[] = $addressLine;
                }
            }
            foreach($lines as $line){
                $widthes[] = $pdf->GetStringWidth($line)+4;
            }
            $width = max($widthes);
            foreach($lines as $num=>$line){
                $pdf->Ln();
                $pdf->addCell($width,4,$line,$borders);
            }
        }

        if(isset($datas['paymentMode']['name'])){
            $pdf->Ln();
            $pdf->addCell(5,4,'','');
            $pdf->Ln();
            $pdf->addCell(0,4,'Moyen de paiement : '.$datas['paymentMode']['name'],'');
        }

        $pdf->AliasNbPages();
        $pdf->Output($outputName,'F');
        return $outputName;
    }


    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring(){
        return get_class();
    }

}