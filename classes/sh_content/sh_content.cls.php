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
 * Class that display and manages html contents, like company presentation for example.
 */
class sh_content extends sh_core {

    public function construct(){
        if(!is_dir(SH_IMAGES_FOLDER.'small/')){
            $this->linker->browser->createFolder(
                SH_IMAGES_FOLDER.'small/',
                sh_browser::ALL
            );
            $this->linker->browser->addDimension(
                SH_IMAGES_FOLDER.'small/',100,100
            );
        }
    }

    public function showList(){
        $this->debug(__FUNCTION__.'();', 2, __LINE__);
        if($this->isAdmin()){
            $replacements['orAny'] = ' OR 1';
        }else{
            $replacements['orAny'] = '';
        }
        $list = $this->db_execute('getList', $replacements);
        foreach($list as $element){
            if($element['active'] == 1){
                $state = 'active';
            }else{
                $state = 'inactive';
            }
            $title = $this->getI18n($element['title']);
            if(strlen($title)>40){
                $title = substr($title,0,37).'...';
            }
            $values[$state][] = array(
                'show_link'=>$this->translatePageToUri(
                    '/show/'.$element['id']
                ),
                'edit_link'=>$this->translatePageToUri(
                    '/edit/'.$element['id']
                ),
                'delete_link'=>$this->translatePageToUri(
                    '/delete/'.$element['id']
                ),
                'title'=>$title,
                'date'=>$element['date']
            );
        }
        $this->render('showList',$values);
        return true;
    }

    public function shortList(){
        $this->debug(__FUNCTION__.'();', 2, __LINE__);
        $id = (int) $this->linker->path->page['id'];
        $list = $this->getParam('list>'.$id.'>activated', false);
        $this->linker->html->setTitle(
            $this->getI18n($this->getParam('list>'.$id.'>name',array()))
        );
        $values['showList']['summary'] = $this->getI18n(
            $this->getParam('list>'.$id.'>summary',0)
        );
        // We verify if there are contents in the list
        if(!$list){
            $this->render('emptyShortList');
            return true;
        }
        // We prepare the rendering
        foreach($list as $element){
            list($element) = $this->db_execute(
                'getShort',
                array('id'=>$element)
            );
            $values['contents'][] = $element;
        }
        foreach($values['contents'] as &$element){
            $element['link'] = $this->linker->path->getLink(
                $this->shortClassName.'/show/'.$element['id']
            );
            $element['title'] = $this->getI18n($element['title']);
            $element['summary'] = $this->getI18n($element['summary']);
        }
        $this->render('shortList_show',$values);
        return true;
    }

    public function editShortList(){
        $this->debug(__FUNCTION__.'();', 2, __LINE__);
        $this->onlyAdmin();
        $id = (int) $this->linker->path->page['id'];
        if($this->formSubmitted('delete_shortList')){
            $name = $this->getParam('list>'.$id.'>name');
            $summary = $this->getParam('list>'.$id.'>summary');
            $this->removeI18n($name);
            $this->removeParam('list>'.$id);
            $this->writeParams();
            $this->removeFromSitemap($this->shortClassName.'/shortList/'.$id);
            $this->linker->path->redirect(
                $this->translatePageToUri('/'.__FUNCTION__.'/0')
            );
        }elseif($this->formSubmitted('contentListEditor')){
            if($id == 0){
                $lists = array_keys(
                    $this->getParam('list',array())
                );
                $id = max($lists) + 1;
                $name = $this->setI18n(0,'new');
                $summary = $this->setI18n(0,'new');
                $this->setParam('list>'.$id.'>name', $name);
                $this->setParam('list>'.$id.'>summary', $summary);
                $this->writeParams();
            }

            $name = $this->getParam('list>'.$id.'>name',0);
            $summary = $this->getParam('list>'.$id.'>summary',0);
            $this->setI18n($name,$_POST['name']);
            $summary = $this->setI18n($summary,$_POST['summary']);
            $order = explode('-',$_POST['order']);
            $this->setParam('list>'.$id.'>summary', $summary);
            $this->setParam('list>'.$id.'>activated', $order);
            $this->setParam('list>'.$id.'>image',$_POST['image']);
            $this->setParam('list>'.$id.'>date',date('Y-m-d H:i:s'));
            $this->writeParams();
            $this->removeFromSitemap($this->shortClassName.'/shortList/'.$id);
            $sitemapPriority = $this->getParam('sitemap>shortList>Priority',0.7);
            $this->addToSitemap($this->shortClassName.'/shortList/'.$id,$sitemapPriority);
            $this->linker->path->redirect(__CLASS__,'shortList',$id);
        }

        $lists = $this->getParam('list',array());
        $values['lists'][0] = array(
            'link' => $this->linker->path->getLink(
                $this->shortClassName.'/'.__FUNCTION__.'/0'
            ),
            'name' => $this->getI18n('newShortList')
        );
        if(is_array($lists)){
            foreach($lists as $oneId=>$oneList){
                $values['lists'][] = array(
                    'link' => $this->linker->path->getLink(
                        $this->shortClassName.'/'.__FUNCTION__.'/'.$oneId
                    ),
                    'name' => $oneId.' - '.$this->getI18n($oneList['name'])
                );
            }
        }

        // We are editing a list
        if($id != 0){
            $values['list']['name'] = $this->getParam(
                'list>'.$id.'>name',
                array()
            );
            $values['list']['title'] = $this->getI18n(
                $this->getParam(
                    'list>'.$id.'>name',
                    ''
                )
            );
            $values['list']['summary'] = $this->getParam(
                'list>'.$id.'>summary',
                array()
            );
        }else{
            $values['list']['name'] = $this->getI18n('newShortList_title');
            $values['list']['title'] = $this->getI18n('newShortList_title');
            $values['list']['isNew'] = true;
        }
        $replacements['orAny'] = ' ORDER BY `id`';

        $values['content']['image'] = $this->getParam('list>'.$id.'>image','');
        $list = $this->db_execute('getList', $replacements,$qry);
        $activated = $this->getParam('list>'.$id.'>activated',array());
        if(is_array($list)){
            foreach($list as $element){
                $values['contents'][] = array(
                    'title'=>$this->getI18n($element['title']),
                    'date'=>$element['date'],
                    'id'=>$element['id']
                );
                $key = array_search($element['id'],$activated);
                if($key !== false){
                    $thereAreActiveContents = true;
                    $values['activecontents'][$key] = array(
                        'title'=>$this->getI18n($element['title']),
                        'date'=>$element['date'],
                        'id'=>$element['id']
                    );
                }
            }
        }
        if($thereAreActiveContents){
            // We sort the active contents by their keys
            ksort($values['activecontents']);
        }
        $values['style']['bgColor'] = '#CCCCCC';

        $values['content']['image_folder'] = SH_IMAGES_FOLDER.'small/';

        $this->render('shortList_edit',$values);
        return true;
    }

    /**
     * public function show
     */
    public function show(){
        $this->debug(__FUNCTION__.'();', 2, __LINE__);

        $id = (int) $this->linker->path->page['id'];

        if($id != ''){
            $this->linker->admin->insertPage(
                $this->shortClassName.'/edit/'.$id,'Contenu','bank1/picto_modify.png'
            );
        }

        if($id == 0){
            $this->linker->path->error('404');
        }

        $replacements = array('id' => $id);
        list($content['content']) = $this->db_execute('get',$replacements);

        if(!isset($content['content']['id'])){
            $this->linker->path->error('404');
        }

       $content['content']['content'] = $this->getI18n(
           $content['content']['content']
       );
       $content['content']['title'] = $this->getI18n(
           $content['content']['title']
       );
       $content['content']['summary'] = $this->getI18n(
           $content['content']['summary']
       );


        if($content['content']['showDate'] == 0){
            unset($content['content']['date']);
        }

        if($content['content']['showTitle'] == 1){
            $this->linker->html->setTitle($content['content']['title']);
        }else{
            $this->linker->html->setTitle('');
        }

        $rendered = $this->render('content',$content);
        return true;
    }

    public function delete(){
        $this->debug(__FUNCTION__.'();', 2, __LINE__);
        $this->onlyAdmin();
        $id = (int) $this->linker->path->page['id'];
        if($id == 0){
            $this->linker->path->error('404');
        }
        
        if($this->formSubmitted('delete_content')){
            list($content) = $this->db_execute(
                'getWithInactive',
                array('id' => $id)
            );
            $this->db_execute(
                'delete',
                array('id' => $id)
            );
            $this->removeI18n($content['title']);
            $this->removeI18n($content['summary']);
            $this->removeI18n($content['content']);
            $this->linker->path->redirect(
                $this->translatePageToUri('/showList/')
            );
            return true;
        }

        $this->linker->html->setTitle($this->getI18n('deletePage_title'));

        list($values['content']) = $this->db_execute(
            'getWithInactive',
            array('id' => $id)
        );

        $values['content']['title'] = $this->getI18n($values['content']['title']);

        if(!$values['content']['active']){
            unset($values['content']['active']);
        }
        
        $rendered = $this->render('delete',$values);
        return true;
    }

    /**
     * public function edit
     */
    public function edit(){
        $this->debug(__FUNCTION__.'();', 2, __LINE__);
        $this->onlyAdmin();
        $id = (int) $this->linker->path->page['id'];
        if($id == 0){
            $content['editcontent']['title'] = $this->getI18n('new_page_title');
        }else{
            $content['editcontent']['title'] = $this->getI18n('edit_this_page');
        }
        // Creates the small images folder, if needed
        $folder = SH_IMAGES_FOLDER.'small/';
        if(!is_dir($folder)){
            // We don't use $this->addFolder because only masters may write in that folder
            mkdir($folder);
            $this->linker->helper->writeInFile(
                $folder.sh_browser::RIGHTSFILE,sh_browser::ALL
            );
            $this->linker->helper->writeInFile(
                $folder.sh_browser::DIMENSIONFILE,'100x100'
            );
            $this->linker->helper->writeInFile(
                $folder.sh_browser::OWNERFILE,$this->userName
            );
        }

        if($this->formSubmitted('content_edit')){
            if($id == 0){
                $this->db_execute('create',array());
                $id = $this->db_insertId();
                $isNew = true;
            }
            $newAndNotActive = $this->save($id,$isNew);
        }
        

        $content['content']['image_folder'] = SH_IMAGES_FOLDER.'small/';
        if($newAndNotActive){
            $content['content']['newAndNotActive'] = true;
            $content['content']['newAndNotActiveLink'] = 
                $this->translatePageToUri('/'.__FUNCTION__.'/'.$id)
            ;
        }
        if($id == 0 || $newAndNotActive){
            $content['content']['active'] = 'checked';
            $content['content']['showtitle'] = 'checked';
        }else{
            // We read the values for the article
            $replacements = array('id' => $id);
            list($content['content']) = $this->db_execute(
                'getWithInactive',
                $replacements
            );
            // We load the values that are in db
            $content['content']['active'] = $this->addChecked(
                $content['content']['active']
            );
            $content['content']['showdate'] = $this->addChecked(
                $content['content']['showDate']
            );
            $content['content']['showtitle'] = $this->addChecked(
                $content['content']['showTitle']
            );            
        }
        //sh_diaporama::addToPreviews('content_editor');
        $this->render('edit',$content);
    }

    /**
     * protected function addChecked
     *
     */
    protected function addChecked($condition){
        $this->debug(__FUNCTION__.'();', 2, __LINE__);
        if($condition == 1){
            return 'checked';
        }
        return '';
    }

    /**
     * protected function checkedToBinary
     *
     */
    protected function checkedToBinary($element){
        $this->debug(__FUNCTION__.'();', 2, __LINE__);
        if(isset($_POST[$element])){
            return '1';
        }
        return '0';
    }

    /**
     * protected function save
     */
    protected function save($id,$isNew = false){
        $this->debug(__FUNCTION__.'();', 2, __LINE__);
        $active = $this->checkedToBinary('active');
        $showTitle = $this->checkedToBinary('showTitle');
        $showDate = $this->checkedToBinary('showDate');

        list($element) = $this->db_execute('getWithInactive', array('id'=>$id));
        $i18nTitle = $this->setI18n($element['title'], $_POST['title']);
        $i18nSummary = $this->setI18n($element['summary'], $_POST['summary']);
        $i18nContent = $this->setI18n($element['content'], $_POST['content']);

        $replacements = array(
            'id' => $id,
            'image' => $_POST['image'],
            'showTitle' => $showTitle,
            'showDate' => $showDate,
            'active' => $active,
            'title' => $i18nTitle,
            'content' => $i18nContent,
            'summary' => $i18nSummary
        );
        $this->db_execute('save',$replacements);

        $this->removeFromSitemap($this->shortClassName.'/show/'.$id);
        if($active){
            $this->addToSitemap($this->shortClassName.'/show/'.$id,$priority);

            $this->search_removeEntry('show',$id);

            $this->search_addEntry(
                'show',
                $id,
                $_POST['title'],
                $_POST['summary'],
                $_POST['content']
            );
            $this->linker->path->redirect(__CLASS__,'show',$id);
        }
        return $isNew;
    }

    /**
     * Renders the results of a research (should be called by sh_searcher).
     * @param str $method The method that should be called to access the page
     * of the result
     * @param array $elements An array containing the list of the ids of the
     * elements that are to be shown in the results.
     * @return str The rendered xml for the results.
     */
    public function searcher_showResults($method,$elements){
        $this->debug(__FUNCTION__.'('.$method.','.print_r($elements,true).');', 2, __LINE__);

        // We prepare the rendering
        foreach($elements as $element){
            list($element) = $this->db_execute(
                'getShort',
                array('id'=>$element)
            );
            $values['contents'][] = $element;
        }
        foreach($values['contents'] as &$element){
            $element['link'] = $this->linker->path->getLink(
                $this->shortClassName.'/show/'.$element['id']
            );
            $element['title'] = $this->getI18n($element['title']);
            $element['summary'] = $this->getI18n($element['summary']);
        }
        return array(
            'name' =>  $this->getI18n('search_contentsTitle'),
            'content' => $this->render('searcher_results',$values,false,false)
        );
    }

    /**
     * Gets the list of the contents types that the searcher should search in.
     * @return array Un array containing the list of search types.
     */
    public function searcher_getScope(){
        return array(
            'scope' => $this->shortClassName,
            'name' => $this->getI18n('search_contentsTitle')
        );
    }

    /**
     * public function sitemap_renew
     *
     */
    public function sitemap_renew(){
        $replacements['orAny'] = '';

        $list = $this->db_execute('getList', $replacements);
        if(is_array($list)){
            foreach($list as $element){
                $this->addToSitemap(
                    $this->shortClassName.'/show/'.$element['id'],
                    0.8
                );
            }
        }
        $shortLists = $this->getParam('list');
        if(is_array($shortLists)){
            foreach(array_keys($shortLists) as $shortList){
                $this->addToSitemap(
                    $this->shortClassName.'/shortList/'.$shortList,
                    0.8
                );
            }
        }
        return true;
    }

    /**
     * Creates or gets a name for a page.<br />
     * This name can be used to describe it, like a kind of title.<br />
     * To build good names, the classes that create main contents should
     * extend this function, or put a reverse in the "uri" table of the database
     * @param string $action
     * Action of the page (second part of the page name, like show in shop/show/17)
     * @param integer $id
     * optional - defaults to null<br />
     * Id of the page (third part of the page name, like 17 in shop/show/17)
     * @return string New name of the page
     */
    public function getPageName($action, $id = null){
        $name = $this->getI18n('action_'.$action);
        if($action == 'show'){
            list($title) = $this->db_execute('getTitleWithInactive', array('id'=>$id));
            $title = $this->getI18n($title['title']);
            $name = str_replace(
                array('{id}','{link}','{title}'),
                array($id,$link,$title),
                $name
            );
        }
        if($action == 'shortList'){
            list($title) = $this->db_execute('getTitleWithInactive', array('id'=>$id));
            $title = $this->getParam('list>'.$id.'>name');
            $title = $this->getI18n($title);
            $name = str_replace(
                array('{id}','{link}','{title}'),
                array($id,$link,$title),
                $name
            );
        }
        if(!is_null($id)){
            $page = str_replace(
                    array(SH_PREFIX,SH_CUSTOM_PREFIX),
                    array('',''),
                    $this->__tostring()
                ).'/'.$action.'/'.$id;
            $link = $this->linker->path->getLink($page);
        }
        if($name != ''){
            return $name;
        }
        return $this->__toString().'->'.$action.'->'.$id;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        list($class,$method,$id) = explode('/',$page);
        if($method == 'editShortList'){
            if($id == 0){
                return '/'.$this->shortClassName.'/editShortList/'.$id.'-new.php';
            }
            $name = $this->getParam('list>'.$id.'>name');
            $realName = urlencode(trim($this->getI18n($name)));
            if($realName != ''){
                $realName = '-'.$realName;
            }
            return '/'.$this->shortClassName.'/editShortList/'.$id.$realName.'.php';
        }
        if($method == 'shortList' && $id>0){
            $name = $this->getParam('list>'.$id.'>name');
            $realName = urlencode(trim($this->getI18n($name)));
            if($realName != ''){
                $realName = '-'.$realName;
            }
            return '/'.$this->shortClassName.'/shortList/'.$id.$realName.'.php';
        }
        if($method == 'show' && $id != 0){
            list($title) = $this->db_execute('getTitle', array('id'=>$id),$qry);
            $title = urlencode($this->getI18n($title['title']));
            if(trim($title) != ''){
                $realName = '-'.$title;
            }else{
                $realName = '';
            }
            return '/'.$this->shortClassName.'/show/'.$id.$realName.'.php';
        }
        if($method == 'delete' && $id != 0){
            return '/'.$this->shortClassName.'/delete/'.$id.'.php';
        }
        if($method == 'showList'){
            return '/'.$this->shortClassName.'/showList.php';
        }
        if($method == 'edit'){
            if($id != 0){
                list($title) = $this->db_execute('getTitle', array('id'=>$id));
                $title = urlencode($this->getI18n($title['title']));
                if(!empty($title)){
                    $title = '-'.$title;
                }
            }
            return '/'.$this->shortClassName.'/edit/'.$id.$title.'.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        if(preg_match('`/'.$this->shortClassName.'/([^/]+)(/([0-9]+)(-[^/]+)?)?\.php`',$uri,$matches)){
            if($matches[1] == 'editShortList'){
                return $this->shortClassName.'/editShortList/'.$matches[3];
            }
            if($matches[1] == 'shortList'){
                return $this->shortClassName.'/shortList/'.$matches[3];
            }
            if($matches[1] == 'show'){
                return $this->shortClassName.'/show/'.$matches[3];
            }
            if($matches[1] == 'edit'){
                return $this->shortClassName.'/edit/'.$matches[3];
            }
            if($matches[1] == 'delete'){
                return $this->shortClassName.'/delete/'.$matches[3];
            }
        }
        if($uri == '/'.$this->shortClassName.'/showList.php'){
            return $this->shortClassName.'/showList/';
        }
        return false;
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring(){
        return get_class();
    }
}