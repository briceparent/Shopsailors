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
 * class sh_searcher
 *
 */
class sh_searcher extends sh_core {
    protected $scopes = array();
    protected $activeSearch = false;

    const ACTUAL_LANGUAGE = 0;
    const ALL_LANGUAGES = 1;

    /**
     * public function construct
     * Initiate the object
     */
    public function construct() {
        return true;
    }

    protected function getScopesClasses(){
        if(is_dir(SH_CLASS_SHARED_FOLDER.__CLASS__)){
            $classes = scandir(SH_CLASS_SHARED_FOLDER.__CLASS__.'/scopes/');
            foreach($classes as $class){
                if(substr($class,0,1) != '.'){
                    $class = substr($class,0,-4);
                    $this->scopes[] = $class;
                }
            }
        }
        return $this->scopes;
    }

    /**
     * Gets the searcher html
     * @return str xml string for the html searcher form
     */
    public function get(){
        if($this->getParam('activated',true) === false){
            return $this->render('noSearchEngine', $values, false,false);
        }
        if($this->activeSearch){
            $values['search']['active'] = $this->activeSearch;
        }
        $values['search']['action'] = $this->translatePageToUri(
            $this->shortClassName.'/search/'
        );
        // Verifies if there is a custom searchEngine or if we should show the default one
        $rfFromTemplate =  $this->linker->site->templateFolder.'searcher/searchEngine.rf.xml';
        if(file_exists($rfFromTemplate)){
            $rf = $rfFromTemplate;
        }else{
            $rf = 'searchEngine';
        }
        return $this->render($rf, $values, false,false);
    }

    /**
     * Adds an entry that can be found by the search engine.
     * @param str $class Class name that manages to display the response.
     * @param str $describer Argument to pass to the class $scope to get the
     * entry datas that should be shown in responses.
     * @param str $high Contents that are importants, like titles.<br/>
     * The text entered here, if searched, will put the results on top of the list.
     * @param str $medium Contents that are a little less important than
     * the previous one.
     * @param str $low Contents that should be found, but don't really
     * define the page.
     */
    public function addEntry($class,$method,$describer,$high = '',$medium = '',$low = ''){
        $langs = array_keys($high);
        foreach($langs as $lang){
            $this->db_execute(
                'addElement',
                array(
                    'lang'=>$lang,
                    'class' => $class,
                    'method' => $method,
                    'id' => $describer,
                    'level_1' => strip_tags(
                        $this->removeSpecialChars($high[$lang])
                    ),
                    'level_2' => strip_tags(
                        $this->removeSpecialChars($medium[$lang])
                    ),
                    'level_3' => strip_tags(
                        $this->removeSpecialChars($low[$lang])
                    )
                )
            );
        }
    }

    /**
     * Removes an entry that can be found by the search engine.
     * @param str $class Class name that manages to display the response.
     * @param str $describer Argument to pass to the class $scope to get the
     * entry datas that should be shown in responses.
     */
    public function removeEntry(
        $class,
        $method,
        $describer,
        $language = self::ALL_LANGUAGES
    ){
        if($language == self::ALL_LANGUAGES){
            $this->db_execute(
                'removeElementAllLangs',
                array(
                    'lang'=>$lang,
                    'class' => $class,
                    'method' => $method,
                    'id' => $describer
                )
            );
        }elseif($language == self::ACTUAL_LANGUAGE){
            $lang = $this->linker->i18n->getLang();
            $this->db_execute(
                'removeElement',
                array(
                    'lang'=>$lang,
                    'class' => $class,
                    'method' => $method,
                    'id' => $describer
                )
            );
        }else{
            $this->db_execute(
                'removeElement',
                array(
                    'lang'=>$language,
                    'class' => $class,
                    'method' => $method,
                    'id' => $describer
                )
            );
        }
    }

    protected function removeSpecialChars($text){
        $table = array(
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a',
            'Þ'=>'B', 'þ'=>'b',
            'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',  'Ç'=>'C', 'ç'=>'c',
            'Đ'=>'Dj', 'đ'=>'dj',
            'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'è'=>'e', 'é'=>'e', 'ê'=>'e',
            'ë'=>'e',
            'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
            'ï'=>'i',
            'Ñ'=>'N', 'ñ'=>'n',
            'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O',
            'ð'=>'o', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o',
            'Ŕ'=>'R', 'ŕ'=>'r',
            'Š'=>'S', 'š'=>'s', 'ß'=>'Ss',
            'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'ù'=>'u', 'ú'=>'u', 'û'=>'u',
            'Ý'=>'Y', 'ý'=>'y', 'ý'=>'y', 'ÿ'=>'y',
            'Ž'=>'Z', 'ž'=>'z',
            '\''=>' '
        );

        return trim(strtr($text, $table));
    }

    /**
     * public function search
     */
    public function search(){
        /**
         * The searches are made in 3 rows of the tables #searcher.
         * It is done in 2 steps:
         * - We first look in the 3 together, to see how many words of the query
         * are found in every answer.
         * - We then look in each of the 3 rows, and apply a different weight to
         * the 3 answers.
         * After that, we sort the answers this way:
         * ->1000 points for every answer
         * ->-100 points for every single word found (not depending on the number of
         * time they are found).
         * ->The number of points given by mysql for each row, multiplied by the
         * weight of the row (-8, -3 and -1 for level_1, 2 and 3).
         * (We remove poiunts instead of adding, in order to use the asort function
         * to sort the answers).
         *
         * Then, the smallest value for an answer indicates the best answer.
         *
         * @todo Make the research only on active scopes, instead of filtering
         * the results.
         */
        if($this->getParam('activated',true) === false){
            $this->linker->path->error(404);
        }
        sh_cache::disable();
        $search = stripslashes(urldecode($_GET['value']));
        $this->linker->html->setTitle(
            $this->getI18n('theQueryWas').' ['.$search.']'
        );
        $this->activeSearch = $search;
        $search = strtolower(
            $this->removeSpecialChars($search)
        );

        $rfNoResultsTemplate =  $this->linker->site->templateFolder.'searcher/show_noResults.rf.xml';
        if(file_exists($rfNoResultsTemplate)){
            $rfNoResults = $rfNoResultsTemplate;
        }else{
            $rfNoResults = 'show_noResults';
        }
        if(strlen(trim($search)) < 3){
            $values['error']['tooShort'] = true;
            $this->render($rfNoResults,$values);
            return true;
        }

        // Global search
        $allKeyWordsResults = $this->db_execute(
            'searchAllWords',
            array(
                'search' => $search,
                'lang' => $this->linker->i18n->getLang()
            )
        );

        if(!is_array($allKeyWordsResults)){
            $this->render($rfNoResults);
            return true;
        }
        // Giving points for the amount of found words in all rows
        $sorted = array();
        foreach($allKeyWordsResults as $res){
            $name = &$sorted[$res['class']][$res['method']][$res['id']];
            $name = 1000 - 100 * $res['keywords'];
        }

        // Search in each rows
        $results[0] = $this->db_execute(
            'search',
            array(
                'search' => $search,
                'lang' => $this->linker->i18n->getLang(),
                'level' => 1,
                'weight' => 8
            )
        );
        $results[1] = $this->db_execute(
            'search',
            array(
                'search' => $search,
                'lang' => $this->linker->i18n->getLang(),
                'level' => 2,
                'weight' => 3
            )
        );
        $results[2] = $this->db_execute(
            'search',
            array(
                'search' => $search,
                'lang' => $this->linker->i18n->getLang(),
                'level' => 3,
                'weight' => 1
            )
        );

        // Giving points using Mysql's match return
        foreach($results as $level=>$oneLevelResults){
            if(!is_null($oneLevelResults)){
                foreach($oneLevelResults as $res){
                    $name = &$sorted[$res['class']][$res['method']][$res['id']];
                    $name -= $res['match'] * $res['weight'];
                }
            }
        }

        // Sorting the results
        foreach($sorted as $class=>&$methods){
            foreach($methods as $method=>&$elements){
                asort($elements);
                $counts[$class][$method] = count($elements);
                $elements = array_chunk($elements,8,true);
                if(count($elements[0]) > 5){
                    $cpt = 0;
                    foreach($elements[0] as $id=>$content){
                        $smallResultsList[$id] = $content;
                        if(++$cpt >= 5){
                            break;
                        }
                    }
                    array_unshift($elements,$smallResultsList);
                }else{
                    array_unshift($elements,$elements[0]);
                }
            }
        }

        // We save the results in the session in order to show some other results
        $searchId = substr(md5($search),0,6);
        $_SESSION[__CLASS__]['results'][$searchId] = $sorted;
        $_SESSION[__CLASS__]['results'][$searchId]['search'] = $search;

        // Rendering
        $showingOrder = $this->getParam('showingOrder');
        $resultsLink = $this->translatePageToUri($this->shortClassName.'/showResults/');

        $cpt = 0;
        foreach($showingOrder as $type){
            if(isset($sorted[$type])){
                foreach($sorted[$type] as $method=>$element){
                    if($this->linker->method_exists($type,'searcher_showResults')){
                        $link = $resultsLink.'?searchId='.$searchId;
                        $link .= '&scope='.$type.'&action='.$method;
                        $rendered = $this->linker->$type->searcher_showResults(
                            $method,
                            array_keys($element[0])
                        );
                        $values['results'][$cpt] = $rendered;
                        if(isset($element[2]) || count($element[1]) > count($element[0])){
                            $values['results'][$cpt]['listLink'] = $link;
                        }
                        if($counts[$type][$method] > 1){
                            $values['results'][$cpt]['count'] = $counts[$type][$method];
                        }
                        $cpt++;
                    }
                }
            }
        }

        $rfFromTemplate =  $this->linker->site->templateFolder.'searcher/show_results.rf.xml';
        if(file_exists($rfFromTemplate)){
            $rf = $rfFromTemplate;
        }else{
            $rf = 'show_results';
        }
        $this->render($rf,$values);
        return true;
    }

    public function showResults(){
        if($this->getParam('activated',true) === false){
            $this->linker->path->error(404);
        }
        $searchId = $_GET['searchId'];
        $scope = $_GET['scope'];
        $action = $_GET['action'];
        if(!isset($_SESSION[__CLASS__]['results'][$searchId][$scope][$action])){
            $this->render('show_noResults');
            return true;
        }
        $element = $_SESSION[__CLASS__]['results'][$searchId][$scope][$action];

        if(isset($_GET['page']) && is_array($element[$_GET['page']])){
            $page = $_GET['page'];
        }else{
            $page = 1;
        }

        $rendered = $this->linker->$scope->searcher_showResults(
            $action,
            array_keys($element[$page])
        );
        $values['results'] = $rendered;

        $nbPages = count($element) - 1;
        if($nbPages>1){
            $link = $this->translatePageToUri($this->shortClassName.'/showResults/');
            $link .= '?searchId='.$searchId;
            $link .= '&scope='.$scope.'&action='.$action;
            $values['results']['pages'] = '';
            for($a = 1;$a<=$nbPages;$a++){
                if($a == $page){
                    $values['resultsPages'][] = array(
                        'num'=>$a
                    );
                }else{
                    $values['resultsPages'][] = array(
                        'num'=>$a,
                        'link'=>$link.'&page='.$a
                    );
                }
            }
        }
        $rfFromTemplate =  $this->linker->site->templateFolder.'searcher/show_results_filtered.rf.xml';
        if(file_exists($rfFromTemplate)){
            $rf = $rfFromTemplate;
        }else{
            $rf = 'show_results_filtered';
        }
        $this->render($rf,$values);
        return true;
    }

    public function manage(){
        $this->onlyAdmin();
        if($this->formSubmitted('searcherManager')){
            $this->setParam('activated',isset($_POST['activated']));
            foreach($_POST['scopes'] as $scope=>$state){
                $scopes[] = $scope;
            }
            $this->setParams('showingOrder', $scopes);
            // Finaly writes the params
            $this->writeParams();
        }

        if($this->getParam('activated',true) === true){
            $values['activated']['checked'] = 'checked';
        }
        $allowedScopes = $this->getParam('showingOrder');
        $scopesClasses = $this->getScopesClasses();
        foreach($scopesClasses as $scopesClasses){
            $scope = $this->linker->$scopesClasses->searcher_getScope();
            if(in_array($scope['scope'],$allowedScopes)){
                $checked = 'checked';
            }else{
                $checked = '';
            }
            $types[] = array(
                'name' => $scope['name'],
                'scope' => $scope['scope'],
                'state' => $checked
            );
        }

        $values['scopes'] = $types;

        $this->render('manage',$values);
        return true;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        if($page == $this->shortClassName.'/search/'){
            return '/'.$this->shortClassName.'/search.php';
        }
        if($page == $this->shortClassName.'/manage/'){
            return '/'.$this->shortClassName.'/manage.php';
        }
        if($page == $this->shortClassName.'/showResults/'){
            return '/'.$this->shortClassName.'/showResults.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        if($uri == '/'.$this->shortClassName.'/search.php'){
            return $this->shortClassName.'/search/';
        }
        if($uri == '/'.$this->shortClassName.'/manage.php'){
            return $this->shortClassName.'/manage/';
        }
        if($uri == '/'.$this->shortClassName.'/showResults.php'){
            return $this->shortClassName.'/showResults/';
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