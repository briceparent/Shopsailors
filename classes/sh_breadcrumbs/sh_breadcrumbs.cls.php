<?php
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

/* class  sh_breadcrumbs
Description: Makes the html for the breadcrumbs
*/
class sh_breadcrumbs extends sh_core{

    /**
     * This method creates a breadcrumbs using the datas named as passed as
     * argument.
     */
    public function render_breadcrumbs($attributes = array(),$contents = '', $renderValues = array()){
        $this->links->helpToolTips->addJavascript();
        if(!isset($attributes['what']) || !isset($renderValues[$attributes['what']])){
            echo 'what, or $values['.$attributes['what'].'] is not set<br />';
            return false;
        }
        $elements = $renderValues[$attributes['what']];

        if(!isset($attributes['separator'])){
            $separator = '&gt;';
        }else{
            $separator = $attributes['separator'];
        }
        if(isset($attributes['sameLevelText'])){
            $values['sameLevel']['text'] = $attributes['sameLevelText'];
        }

        $actualSeparator = '';
        foreach($elements as $key=>$element){
            $values['parents'][$key] = array(
                'uid'=>$key,
                'name'=>$element['name'],
                'link'=>$element['link'],
                'separator'=>$actualSeparator
            );
            if(isset($mySisters)){
                $values['parents'][$key]['hasSisters'] = true;
                foreach($mySisters as $sisterKey=>$sister){
                    if($element['name'] != $sister['name']){
                        $values['parents'][$key]['sisters'][$sisterKey] = array(
                            'uid'=>$key.'.'.$sisterKey,
                            'name'=>$sister['name'],
                            'link'=>$sister['link']
                        );
                    };
                };
                unset($mySisters);
            }
            if(is_array($element['daughters'])){
                $mySisters = $element['daughters'];
            }
            $actualSeparator = $separator;
        }

        return $this->render('breadcrumbs', $values, false, false);
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring(){
        return get_class();
    }
}