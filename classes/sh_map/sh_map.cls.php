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
 * Class that manages the Gmaps.
 */
class sh_map extends sh_model{
    private $functions=array();
    
    public function __construct(){
        parent::__construct($this);
    }

    public function show(){
      $this->linker->html->addScript('/sh_map/singles/maps.js');
      $this->linker->html->addScript('/sh_map/singles/extInfoWindow.js');

      $this->linker->html->addScript('http://maps.google.com/maps?file=api&v=2&key='.$this->params->get('mapKey'));
      
      $this->linker->html->setTitle('Carte');
      
        if(isset($this->linker->path->page['id']))
          $id = $this->linker->path->page['id'];
      else
          $id = $this->params->get('defaultId');
      $this->linker->html->addToBody('onload','loadMap(\''. $id .'\')');
      $this->linker->html->insert('<div id="map" style="width: 800px; height: 500px"></div><div id="mapSidebar"></div>');
    }
    
    public function save($id,$new=false){
        if(!$this->isAdmin())
          header('location: /restricted_area.php');
        
        foreach($_POST as $key=>$value)
            $posted[$key] = mysql_real_escape_string($value);

        $address=$_POST['address'];
        $name=$_POST['name'];
        
        if($address != ''){
            $csv = explode(',',$this->getCoords($address,&$lat,&$lng));
            if($csv[0] != '200'){
                $this->linker->html->insert('Adresse non trouvée...<br />');
                $_SESSION['verif_map_'.$id]='';
                $this->edit(true);
                return false;
            }
            if($new){
                $this->db->insert(array('address'=>$address, 'name'=>$name,
                                    'lat'=>$lat, 'lng'=>$lng),
                              '###gMarkers','',&$qry);
                $id = $this->db->insert_id();
            }else{
              $this->db->update(array('address'=>$address, 'name'=>$name,
                                    'lat'=>$lat, 'lng'=>$lng),
                              '###gMarkers', array('id'=>$id),'LIMIT 1',&$qry);
           }
        }
        
        $this->buildXML();

        header('location: ' . $this->linker->path->getLink('map/show/'.$id,$name));
    }
        
    public function add(){
        $this->edit(true);       
    }
    
    public function remove(){
        if(!$this->isAdmin())
          header('location: /restricted_area.php');
        
        $id=(int) $this->linker->path->page['id'];
        $this->db->delete('###gMarkers',array('id'=>$id),'LIMIT 1');
        
        $this->buildXML();
        
        header('location: '.$this->linker->path->getLink('map/editMap/'));
    }
    
    public function edit($new = false){
        if(!$this->isAdmin())
          header('location: /restricted_area.php');
        $this->linker->html->setTitle('Modifier un élément de la carte GMaps');
        $id=(int) $this->linker->path->page['id'];
        if(isset($_POST['verif']) && $_POST['verif']==$_SESSION['verif_map_'.$id]){
          if(! $this->save($id,$new)){
              return false;
          }
        }
        
        $this->linker->html->addCSS('#TEMPLATE_DIR#CSS/edit.css');
        // gets variables from db
        
        if($new)
            $element=array('name'=>$_POST['name'],'address'=>$_POST['address'],'id'=>$_POST['id']);
        else
            list($element) = $this->db->select(array('name','address','id'),'###gMarkers',array('id'=>$id),'LIMIT 1');
            
        $_SESSION['verif_map_'.$id]=MD5('Brice'.microtime());
        $ret = '<form action="" method="POST">';
        $ret .= '<input type="hidden" name="verif" value="'.$_SESSION['verif_map_'.$id].'" />';
        
        $ret .= '<div class="mapElement">Titre: <input name="name" value="'.$element['name'].'"/><br />'."\n";
        $ret .= 'Adresse: <br /><input name="address" class="large" value="'.$element['address'].'"/><br />'."\n";
        $ret .= '</div>'."\n";
        
        $ret .= '<br /><input type="submit" value="Enregistrer"/>';
        $ret .= '</form>';
        $this->linker->html->insert($ret);
        return true;
    }
    
    public function editMap(){
        if(!$this->isAdmin())
          header('location: /restricted_area.php');
        $this->linker->html->addTextScript('
function confirmation(page){
    if(confirm(\'Etes-vous sur de vouloir supprimer cet élément?\')){
      window.location.href = page;
    }
}');
        $xml = simplexml_load_file($this->params->get('xml'));
        $this->linker->html->setTitle('Modifier les éléments de la carte GMaps');
        $ret = '<ul>';
        foreach($xml->marker as $xmlPart){
            $ret .= '<li><a href="'.$this->linker->path->getLink('map/edit/'.$xmlPart->attributes()->id,$xmlPart->attributes()->name).'">Modifier '.$xmlPart->attributes()->name.'</a><br />';
            $address = 'http://'.$this->linker->path->getDomain().$this->linker->path->getLink('map/show/'.$xmlPart->attributes()->id,$xmlPart->attributes()->name);
            $ret .= 'Voir la page : <a href="'.$address.'">'.$address.'</a>';
            if($xmlPart->attributes()->id != $this->params->get('defaultId'))
                $ret .= '<br />(Pour supprimer cet élément, <span class="falseLink" onclick="confirmation(\''.$this->linker->path->getLink('map/remove/'.$xmlPart->attributes()->id,$xmlPart->attributes()->name).'\')">cliquer ici</span>)';
            $ret .= '</li>'."\n";
        }
        $ret .= '</ul>';
        $ret .= '<br /><br /><a href="'.$this->linker->path->getLink('map/add/').'">Ajouter un élément sur la carte</a>'."\n";
        $this->linker->html->insert($ret);
    }
    
    private function buildXML(){
        if(!$this->isAdmin())
          header('location: /restricted_area.php');
        // Start XML file, create parent node
        $dom = new DOMDocument("1.0");
        $node = $dom->createElement("markers");
        $parnode = $dom->appendChild($node); 
        $elements = $this->db->select(array('name','address','lat','lng','id'),'###gMarkers','1','',&$qry);
        
        foreach($elements as $row){
            // ADD TO XML DOCUMENT NODE  
            $node = $dom->createElement("marker");  
            $newnode = $parnode->appendChild($node);   
            $newnode->setAttribute("name",$row['name']);
            $newnode->setAttribute("address", $row['address']);  
            $newnode->setAttribute("lat", $row['lat']);  
            $newnode->setAttribute("lng", $row['lng']);  
            $newnode->setAttribute("id", $row['id']);
        } 

        $f=fopen($this->params->get('xml'),'w+');
        fputs($f,$dom->saveXML());
        fclose($f);
        return true;
    }

    private function getCoords($address,$lat,$lng){
        $base_url = "http://". $this->params->get('gmapsHost') ."/maps/geo?output=csv&key=". $this->params->get('mapKey');
        $request_url = $base_url . "&q=" . urlencode($address);
        $csv = file_get_contents($request_url) or die("url not loading");
        $csvSplit = split(",", $csv);
        $status = $csvSplit[0];
        $lat = $csvSplit[2];
        $lng = $csvSplit[3];
        return $csv;
        }
    

}
