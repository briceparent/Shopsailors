<?php

if(!defined('SH_MARKER')){
    header('location: /directCallForbidden.php');
}

/**
 * class sh_hello_someOne
 */
class sh_showArticlesOfADate extends sh_core {

    /**
     * public function sitemap_renew
     *
     */
    public function sitemap_renew(){
        // Adds all pages to the sitemap
        $this->addToSitemap('empty_model/show/', 0.5);
        return true;
    }

    /**
     * Shows the content
     * @return boolean
     */
    public function show(){
        $this->links->html->setTitle($this->getI18n('searchByDateTitle'));
        if($this->formSubmitted('searchByDate_form')){
            $values['previous']['value'] = $_POST['date_searched'];

            $replacements = array('date'=>$_POST['date_searched']);
            $rep = $this->db_execute('getByDate', $replacements,$qry);
            if(isset($rep[0]['id'])){
                foreach($rep as $element){
                    $link = $this->links->path->getLink('content/show/'.$element['id']);
                    $values['responses'][] = array(
                        'title'=>$element['title'],
                        'link'=>$link
                    );
                }
            }
        }
        $this->render('search', $values);
        return true;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        if($page == $this->shortClassName.'/show/'){
            $uri = '/'.$this->shortClassName.'/show.php';
            return $uri;
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        if($uri == '/'.$this->shortClassName.'/show.php'){
            $page = $this->shortClassName.'/show/';
            return $page;
        }
        return false;
    }

    /**
     * public function getPageName
     *
     */
    public function getPageName($action, $id = null){
        if($action == 'show'){
            return $this->getI18n('show_title').$id;
        }
        return $this->__toString().'->'.$action.'->'.$id;
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring(){
        return get_class();
    }

}