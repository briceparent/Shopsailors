<?php

/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See sh_searcher::CLASS_VERSION
 * @package Shopsailors Core Classes
 */
if( !defined( 'SH_MARKER' ) ) {
    header( 'location: directCallForbidden.php' );
}

/**
 * This class manages the search button, the search itself, and the showing of the results.<br/><br />
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
class sh_searcher extends sh_core {

    const CLASS_VERSION = '1.1.12.02.23';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    protected $scopes = array( );
    protected $activeSearch = false;

    const ACTUAL_LANGUAGE = 0;
    const ALL_LANGUAGES = 1;

    /**
     * public function construct
     * Initiate the object
     */
    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            // The class datas are not in the same version as this file, or don't exist (installation)
            $this->helper->addClassesSharedMethods( 'sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__ );

            if( version_compare( $installedVersion, '1.1.11.03.28', '<' ) ) {
                $this->linker->db->updateQueries( __CLASS__ );
                $this->db_execute( 'create_table', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.12.02.23', '<' ) ) {
                $this->helper->addClassesSharedMethods( 'sh_cron', '', __CLASS__ );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
        return true;
    }

    public function cron_job( $type ) {
        sh_cache::disable();
        $start = time();
        if( $type == sh_cron::JOB_WEEK ) {
            $scopes = $this->getScopesClasses();
            foreach( $scopes as $scopesClasses ) {
                if( method_exists( $scopesClasses, 'searcher_refresh_content' ) ) {
                    // We remove the old search entries
                    $this->removeEntry( $scopesClasses, '*', '*' );
                    // and ask for the creation of the new ones
                    $this->linker->$scopesClasses->searcher_refresh_content();
                }
            }
        }
    }

    public function master_getMenuContent() {
        return array( );
    }

    public function admin_getMenuContent() {
        $adminMenu[ 'Contenu' ][ ] = array(
            'link' => 'searcher/manage/',
            'text' => 'Recherche',
            'icon' => 'picto_search.png'
        );

        return $adminMenu;
    }

    protected function getScopesClasses() {
        $this->debug( __FUNCTION__, 3, __LINE__ );
        $classes = $this->get_shared_methods( 'scopes' );
        $this->scopes = array( );
        foreach( $classes as $class ) {
            $this->scopes[ ] = $class;
        }
        return $this->scopes;
    }

    /**
     * Gets the searcher html
     * @return str xml string for the html searcher form
     */
    public function get() {
        $this->debug( __FUNCTION__, 3, __LINE__ );
        if( $this->getParam( 'activated', true ) === false ) {
            return $this->render( 'noSearchEngine', $values, false, false );
        }
        if( $this->activeSearch ) {
            $values[ 'search' ][ 'active' ] = $this->activeSearch;
        }
        $values[ 'search' ][ 'action' ] = $this->translatePageToUri(
            $this->shortClassName . '/search/'
        );
        // Verifies if there is a custom searchEngine or if we should show the default one
        $rf = 'searchEngine';

        return $this->render( $rf, $values, false, false );
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
    public function addEntry( $class, $method, $describer, $high = '', $medium = '', $low = '' ) {
        $this->debug( __FUNCTION__, 3, __LINE__ );
        $class = $this->linker->getShortClassName( $class );

        $defaultLang = $this->linker->i18n->getLang();

        if( !is_array( $high ) ) {
            $high = array( $defaultLang => $high );
        }

        if( !is_array( $medium ) ) {
            $medium = array( $defaultLang => $medium );
        }

        if( !is_array( $low ) ) {
            $low = array( $defaultLang => $low );
        }

        $langs = array_merge( array_keys( $high ), array_keys( $medium ), array_keys( $low ) );
        $langs = array_unique( $langs );
        foreach( $langs as $lang ) {
            if( empty( $high[ $lang ] ) ) {
                $high[ $lang ] = $high[ $defaultLang ];
            }
            if( empty( $medium[ $lang ] ) ) {
                $medium[ $lang ] = $medium[ $defaultLang ];
            }
            if( empty( $low[ $lang ] ) ) {
                $low[ $lang ] = $low[ $defaultLang ];
            }
            $this->db_execute(
                'addElement',
                array(
                'lang' => $lang,
                'class' => $class,
                'method' => $method,
                'id' => $describer,
                'level_1' => $this->cleanSearchText( $high[ $lang ] ),
                'level_2' => $this->cleanSearchText( $medium[ $lang ] ),
                'level_3' => $this->cleanSearchText( $low[ $lang ] )
                )
            );
        }
    }

    protected function cleanSearchText( $text ) {
        $text = strtolower( $this->helper->replaceSpecialChars( $text ) );
        $text = strip_tags( str_replace( '>', '> ', $text ) );
        $text = preg_replace(
            array('` (.{1,2} )+`','`([\.,;:])`','` +`'),
            array(' ', ' ', ' '),
            $text
        );
        return $text;
    }

    /**
     * Removes an entry that can be found by the search engine.
     * @param str $class Class name that manages to display the response.
     * @param str $method The method called to show the response.
     * @param str $describer Argument to pass to the class $scope to get the
     * entry datas that should be shown in responses.
     */
    public function removeEntry( $class, $method, $describer, $language = self::ALL_LANGUAGES ) {
        $this->debug( __FUNCTION__, 3, __LINE__ );

        $class = $this->linker->getShortClassName( $class );
        if( $language == self::ALL_LANGUAGES ) {
            if( $method != '*' ) {
                $this->db_execute(
                    'removeElementAllLangs',
                    array(
                    'lang' => $lang,
                    'class' => $class,
                    'method' => $method,
                    'id' => $describer
                    )
                );
            } else {
                $this->db_execute(
                    'removeAllElementsForOneClass', array(
                    'class' => $class
                    )
                );
            }
        } elseif( $language == self::ACTUAL_LANGUAGE ) {
            $lang = $this->linker->i18n->getLang();
            $this->db_execute(
                'removeElement',
                array(
                'lang' => $lang,
                'class' => $class,
                'method' => $method,
                'id' => $describer
                )
            );
        } else {
            $this->db_execute(
                'removeElement',
                array(
                'lang' => $language,
                'class' => $class,
                'method' => $method,
                'id' => $describer
                )
            );
        }
    }

    /**
     * public function search
     */
    public function search() {
        $this->debug( __FUNCTION__, 3, __LINE__ );
        if( $this->getParam( 'activated', true ) === false ) {
            $this->linker->path->error( 404 );
        }
        sh_cache::disable();
        $search = stripslashes( urldecode( $_GET[ 'value' ] ) );
        $this->linker->html->setTitle(
            $this->getI18n( 'theQueryWas' ) . ' [' . str_replace( array( '&', '<', '>' ),
                                                                  array( '&#38;', '&#60;', '&#62;' ), $search ) . ']'
        );
        $this->activeSearch = $search;
        $search = trim($this->cleanSearchText( $search ));

        $rfNoResults = 'show_noResults';

        if( strlen( trim( $search ) ) < 3 ) {
            $values[ 'error' ][ 'tooShort' ] = true;
            $this->render( $rfNoResults, $values );
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

        if( !is_array( $allKeyWordsResults ) ) {
            $this->render( $rfNoResults );
            return true;
        }
        // Giving points for the amount of found words in all rows
        $sorted = array( );
        foreach( $allKeyWordsResults as $res ) {
            $name = &$sorted[ $res[ 'class' ] ][ $res[ 'method' ] ][ $res[ 'id' ] ];
            $name = 1000 - 100 * $res[ 'keywords' ];
        }

        // Search in each rows
        $results[ 0 ] = $this->db_execute(
            'search',
            array(
            'search' => $search,
            'lang' => $this->linker->i18n->getLang(),
            'level' => 1,
            'weight' => 8
            )
        );
        $results[ 1 ] = $this->db_execute(
            'search',
            array(
            'search' => $search,
            'lang' => $this->linker->i18n->getLang(),
            'level' => 2,
            'weight' => 3
            )
        );
        $results[ 2 ] = $this->db_execute(
            'search',
            array(
            'search' => $search,
            'lang' => $this->linker->i18n->getLang(),
            'level' => 3,
            'weight' => 1
            )
        );

        // Giving points using Mysql's match return
        foreach( $results as $level => $oneLevelResults ) {
            if( !is_null( $oneLevelResults ) ) {
                foreach( $oneLevelResults as $res ) {
                    $name = &$sorted[ $res[ 'class' ] ][ $res[ 'method' ] ][ $res[ 'id' ] ];
                    $name -= $res[ 'match' ] * $res[ 'weight' ];
                }
            }
        }

        // Sorting the results
        foreach( $sorted as $class => &$methods ) {
            foreach( $methods as $method => &$elements ) {
                asort( $elements );
                $counts[ $class ][ $method ] = count( $elements );
                $elements = array_chunk( $elements, 8, true );
                if( count( $elements[ 0 ] ) > 5 ) {
                    $cpt = 0;
                    foreach( $elements[ 0 ] as $id => $content ) {
                        $smallResultsList[ $id ] = $content;
                        if( ++$cpt >= 5 ) {
                            break;
                        }
                    }
                    array_unshift( $elements, $smallResultsList );
                } else {
                    array_unshift( $elements, $elements[ 0 ] );
                }
            }
        }

        // We save the results in the session in order to show some other results
        $searchId = substr( md5( $search ), 0, 6 );
        $_SESSION[ __CLASS__ ][ 'results' ][ $searchId ] = $sorted;
        $_SESSION[ __CLASS__ ][ 'results' ][ $searchId ][ 'search' ] = $search;

        // Rendering
        $showingOrder = $this->getParam( 'showingOrder' );
        $resultsLink = $this->translatePageToUri( $this->shortClassName . '/showResults/' );

        $cpt = 0;
        if( is_array( $showingOrder ) ) {
            foreach( $showingOrder as $type ) {
                if( is_array( $sorted[ $type ] ) ) {
                    foreach( $sorted[ $type ] as $method => $element ) {
                        if( $this->linker->method_exists( $type, 'searcher_showResults' ) ) {
                            $link = $resultsLink . '?searchId=' . $searchId;
                            $link .= '&scope=' . $type . '&action=' . $method;
                            $rendered = $this->linker->$type->searcher_showResults(
                                $method, array_keys( $element[ 0 ] )
                            );
                            
                            if(!empty($rendered)){
                                $values[ 'results' ][ $cpt ] = $rendered;
                                if( isset( $element[ 2 ] ) || count( $element[ 1 ] ) > count( $element[ 0 ] ) ) {
                                    $values[ 'results' ][ $cpt ][ 'listLink' ] = $link;
                                }
                                if( $counts[ $type ][ $method ] > 1 ) {
                                    $values[ 'results' ][ $cpt ][ 'count' ] = $counts[ $type ][ $method ];
                                }
                                $cpt++;
                            }
                        }
                    }
                }
            }
        }
        if( empty( $values[ 'results' ] ) ) {
            $this->render( $rfNoResults );
            return true;
        }

        $this->render( 'show_results', $values );
        return true;
    }

    public function showResults() {
        if( $this->getParam( 'activated', true ) === false ) {
            $this->linker->path->error( 404 );
        }
        $searchId = $_GET[ 'searchId' ];
        $scope = $_GET[ 'scope' ];
        $action = $_GET[ 'action' ];
        if( !isset( $_SESSION[ __CLASS__ ][ 'results' ][ $searchId ][ $scope ][ $action ] ) ) {
            $this->render( 'show_noResults' );
            return true;
        }
        $element = $_SESSION[ __CLASS__ ][ 'results' ][ $searchId ][ $scope ][ $action ];

        if( isset( $_GET[ 'page' ] ) && is_array( $element[ $_GET[ 'page' ] ] ) ) {
            $page = $_GET[ 'page' ];
        } else {
            $page = 1;
        }

        $rendered = $this->linker->$scope->searcher_showResults(
            $action, array_keys( $element[ $page ] )
        );
        $values[ 'results' ] = $rendered;

        $nbPages = count( $element ) - 1;
        if( $nbPages > 1 ) {
            $link = $this->translatePageToUri( $this->shortClassName . '/showResults/' );
            $link .= '?searchId=' . $searchId;
            $link .= '&scope=' . $scope . '&action=' . $action;
            $values[ 'results' ][ 'pages' ] = '';
            for( $a = 1; $a <= $nbPages; $a++ ) {
                if( $a == $page ) {
                    $values[ 'resultsPages' ][ ] = array(
                        'num' => $a
                    );
                } else {
                    $values[ 'resultsPages' ][ ] = array(
                        'num' => $a,
                        'link' => $link . '&page=' . $a
                    );
                }
            }
        }
        $rf = 'show_results_filtered';

        $this->render( $rf, $values );
        return true;
    }

    public function manage() {
        $this->onlyAdmin( true );
        if( $this->formSubmitted( 'searcherManager' ) ) {
            $this->setParam( 'activated', isset( $_POST[ 'activated' ] ) );
            if( is_array( $_POST[ 'scopes' ] ) ) {
                foreach( $_POST[ 'scopes' ] as $scope => $state ) {
                    $scopes[ ] = $scope;
                }
            }
            $this->setParams( 'showingOrder', $scopes );
            // Finaly writes the params
            $this->writeParams();
        }

        if( $this->getParam( 'activated', true ) === true ) {
            $values[ 'activated' ][ 'checked' ] = 'checked';
        }
        $allowedScopes = $this->getParam( 'showingOrder', array( ) );
        $scopesClasses = $this->getScopesClasses();

        foreach( $scopesClasses as $scopesClasses ) {
            $scope = $this->linker->$scopesClasses->searcher_getScope();
            if( in_array( $scope[ 'scope' ], $allowedScopes ) ) {
                $checked = 'checked';
            } else {
                $checked = '';
            }
            $types[ ] = array(
                'name' => $scope[ 'name' ],
                'scope' => $scope[ 'scope' ],
                'state' => $checked
            );
        }

        $values[ 'scopes' ] = $types;

        $this->render( 'manage', $values );
        return true;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri( $page ) {
        if( $page == $this->shortClassName . '/search/' ) {
            return '/' . $this->shortClassName . '/search.php';
        }
        if( $page == $this->shortClassName . '/manage/' ) {
            return '/' . $this->shortClassName . '/manage.php';
        }
        if( $page == $this->shortClassName . '/showResults/' ) {
            return '/' . $this->shortClassName . '/showResults.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage( $uri ) {
        if( $uri == '/' . $this->shortClassName . '/search.php' ) {
            return $this->shortClassName . '/search/';
        }
        if( $uri == '/' . $this->shortClassName . '/manage.php' ) {
            return $this->shortClassName . '/manage/';
        }
        if( $uri == '/' . $this->shortClassName . '/showResults.php' ) {
            return $this->shortClassName . '/showResults/';
        }
        return false;
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

}