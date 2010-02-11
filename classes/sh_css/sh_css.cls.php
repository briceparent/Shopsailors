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
 * Class that renders the css files, replacing the colors by those that should
 * be used in the variation.
 */
class sh_css extends sh_core{
    protected $minimal = array('get' => true);

    /**
     * public function get
     *
     */
    public function get(){
        // TODO: enable the cache for this
        $this->links->cache->disable();

        $file = $_GET['file'];
        
        $templateFolder = $this->links->site->templateFolder;
        header("Content-type: text/css");
       
        // We check if we have things to add at the end
        $method = 'addTo'.ucfirst($file).'CSS';
        $addToContent = '';
        if(is_dir(SH_CLASS_SHARED_FOLDER.__CLASS__.'/'.$method)){
            $classes = scandir(SH_CLASS_SHARED_FOLDER.__CLASS__.'/'.$method);
            foreach($classes as $class){
                if(substr($class,0,1) == '.'){
                    continue;
                }
                $class = substr($class,0,-4);
                if($this->links->method_exists($class,$method)){
                    $addToContent .= "\n".'/* Added contents from '.$class.' */'."\n";
                    $ret = trim($this->links->$class->$method());
                    // We remove the xml intro and content tag, which are here unnecessary
                    $ret = preg_replace(
                        array('`<\?xml[^>]*>`','`<\/?content[^>]*>`'),
                        array('',''),
                        $ret
                    );

                    $addToContent .= $ret;
                }else{
                    $addToContent .= "\n".'/* No new contents from '.$class.' */'."\n";
                }
            }
        }else{
            $addToContent = '/* No files to include */'."\n";
        }
        
        if($_GET['action'] == 'replace'){
            $templateName = $this->links->site->templateName;
            $variation = $this->links->site->variation;
            $siteName = $this->links->site->siteName;
            $replacements['template'] = array('name'=>$templateName);
            $variationReplacements = array_change_key_case(
                $this->links->variation->get(sh_variation::ALL_VALUES)
            );
            $replacements['variation'] = $variationReplacements;
            $replacements['variation']['name'] = $variation;
            $cssFile = $templateFolder.'css/'.$file.'.css';
            
            if(file_exists($cssFile)){
                $ret = $this->render($cssFile,$replacements,false,false);
                $ret = str_replace("\n",'',$ret);
                $getContent = '`<content>(.*)</content>`';
                preg_match($getContent,$ret,$match);
                $content = $match[1];
            }
            $addToContent = $this->render($addToContent,$replacements,false,false);
            $content .= $addToContent;
            if($content == ''){
                $content = '/* The CSS file "'.$cssFile.'" could not be found... */';
            }
        }elseif($_GET['action'] == 'copy'){
            $cssFile = SH_TEMPLATE_FOLDER.'global/'.$file.'.css';
            $content = '';
            if(file_exists($cssFile)){
                $content = file_get_contents($cssFile);
            }
            
            $content .= $addToContent;
            
            if($content == ''){
                $content = '/* The CSS file "'.$cssFile.'" could not be found... */';
            }
        }
        //$content = str_replace("\n",'',$content);
        $content = str_replace(array("\n",'    ','*/','}','&gt;'),array('',' ','*/'."\n",'}'."\n",'>'),$content);
        echo $content;
        return true;
    }

    public function __tostring(){
        return get_class();
    }
}
