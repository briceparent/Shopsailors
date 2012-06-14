<?php

/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if( !defined( 'SH_MARKER' ) ) {
    header( 'location: directCallForbidden.php' );
}

/**
 * Class that display and manages html page contents, like company presentation for example.
 */
class sh_content extends sh_core {

    const CLASS_VERSION = '1.1.11.07.14.2';

    protected $minimal = array( 'delete' => true );
    protected static $usesRightsManagement = true;
    public $rights_methods = array(
        'editShortList', 'shortList', 'delete', 'edit', 'show'
    );
    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db', 'sh_renderer', 'sh_site', 'sh_template', 'sh_variation',
        'sh_admin', 'sh_browser', 'sh_css', 'sh_html', 'sh_events', 'sh_cron', 'sh_helper'
    );
    public $callWithoutId = array(
        'showList', 'news', 'news_edit'
    );
    public $callWithId = array(
        'editShortList', 'shortList', 'show', 'delete', 'edit'
    );

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion( __CLASS__ );

        if( $installedVersion != self::CLASS_VERSION ) {
            $this->linker->db->updateQueries( __CLASS__ );
            if( version_compare( $installedVersion, '1.1.11.03.29', '<' ) ) {
                // The class datas are not in the same version as this file, or don't exist (installation)
                $this->helper->addClassesSharedMethods( 'sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_facebook', '', __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_searcher', 'scopes', __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_sitemap', '', __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_path', '', $this->className );

                if( !is_dir( SH_IMAGES_FOLDER . 'small/' ) ) {
                    mkdir( SH_IMAGES_FOLDER . 'small/' );
                }
                sh_browser::setRights(
                    SH_IMAGES_FOLDER . 'small/', sh_browser::ALL
                );
                sh_browser::setOwner( SH_IMAGES_FOLDER . 'small/' );

                $this->linker->browser->addDimension(
                    SH_IMAGES_FOLDER . 'small/', 100, 100
                );
                $this->db_execute( 'create_table', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.07.13', '<' ) ) {
                $this->db_execute( 'modify_table_1', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.07.14', '<' ) ) {
                $this->linker->renderer->add_render_tag( 'render_newsBox', __CLASS__, 'render_newsBox' );
            }
            if( version_compare( $installedVersion, '1.1.11.07.14.2', '<' ) ) {
                sh_browser::setRights(
                    SH_IMAGES_FOLDER . 'site/', sh_browser::ALL
                );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
    }

    public function searcher_refresh_content() {
        $contents = $this->db_execute( 'getList', array( 'orAny' => '' ) );
        if( is_array( $contents ) ) {
            foreach( $contents as $content ) {
                list($content) = $this->db_execute( 'get', array( 'id' => $content[ 'id' ] ) );
                $title = $this->getI18n( $content[ 'title' ], '*' );
                $summary = $this->getI18n( $content[ 'summary' ], '*' );
                $contentText = $this->getI18n( $content[ 'content' ], '*' );
                $this->search_addEntry(
                    'show', $content[ 'id' ], $title, $summary, $contentText
                );
            }
        }
    }

    public function render_newsBox( $attributes = array( ), $content = '' ) {
        if( !isset( $attributes[ 'count' ] ) ) {
            $count = '8';
        } else {
            $count = $attributes[ 'count' ];
        }

        $values[ 'news_general' ][ 'style' ] = $attributes[ 'style' ];
        $values[ 'news_general' ][ 'id' ] = $attributes[ 'id' ];
        $values[ 'news_general' ][ 'class' ] = $attributes[ 'class' ];

        $values[ 'news' ] = $this->db_execute( 'getNews', array( 'count' => $count ) );
        if( !empty( $values[ 'news' ] ) ) {
            foreach( $values[ 'news' ] as $newsId => $news ) {
                $values[ 'news' ][ $newsId ][ 'title' ] = $this->getI18n( $news[ 'title' ] );
                $values[ 'news' ][ $newsId ][ 'summary' ] = $this->getI18n( $news[ 'summary' ] );
                $values[ 'news' ][ $newsId ][ 'link' ] = $this->linker->path->getLink( __CLASS__ . '/show/' . $news[ 'id' ] );
                $values[ 'bool' ][ 'thereAreNews' ] = true;
            }
        }

        $return = $this->render( 'news_box', $values, false, false );
        return $return;
    }

    public function master_getMenuContent() {
        $masterMenu = array( );
        return $masterMenu;
    }

    public function admin_getMenuContent() {
        $adminMenu[ 'Contenu' ][ ] = array(
            'link' => 'content/edit/0', 'text' => 'Nouvel article', 'icon' => 'picto_add.png'
        );
        $adminMenu[ 'Contenu' ][ ] = array(
            'link' => 'content/news_edit/', 'text' => 'Gérer le fil d\'actualité', 'icon' => 'picto_list.png'
        );
        $adminMenu[ 'Contenu' ][ ] = array(
            'link' => 'content/showList/', 'text' => 'Tous les articles', 'icon' => 'picto_details.png'
        );
        $adminMenu[ 'Contenu' ][ ] = array(
            'link' => 'content/editShortList/0', 'text' => 'Listes d\'articles', 'icon' => 'picto_list.png'
        );

        return $adminMenu;
    }

    public function getShortListList( $id ) {
        $replacements[ 'orAny' ] = ' OR `id` = ' . $id;
        $contents[ 'list' ][ 'name' ] = $this->getI18n( $this->getParam( 'list>' . $id . '>name', 0 ) );
        $list = $this->db_execute( 'getList', $replacements );
        $activated = $this->getParam( 'list>' . $id . '>activated', array( ) );

        $contents[ 'elements' ] = array( );
        if( is_array( $list ) && is_array( $activated ) ) {
            foreach( $list as $element ) {
                $key = array_search( $element[ 'id' ], $activated );
                if( $key !== false ) {
                    $contents[ 'elements' ][ $key ] = array(
                        'title' => $this->getI18n( $element[ 'title' ] ),
                        'date' => $element[ 'date' ],
                        'link' => $this->linker->path->getLink( $this->shortClassName . '/show/' . $element[ 'id' ] ),
                        'id' => $element[ 'id' ]
                    );
                }
            }
            ksort( $contents[ 'elements' ] );
        }

        return $contents;
    }

    public function getShortListsList() {
        $ret = array( );
        $lists = $this->getParam( 'list', array( ) );
        if( is_array( $lists ) ) {
            foreach( $lists as $oneId => $oneList ) {
                $ret[ ] = array(
                    'id' => $oneId,
                    'name' => $oneId . ' - ' . $this->getI18n( $oneList[ 'name' ] )
                );
            }
        }
        return $ret;
    }

    public function getList( $sortByDateDesc = false ) {
        $replacements[ 'orAny' ] = '';
        if( $sortByDateDesc ) {
            $replacements[ 'orAny' ] = ' ORDER BY timestamp DESC';
        }
        $elements = $this->db_execute( 'getList', $replacements );
        $ret = array( );
        if( is_array( $elements ) ) {
            foreach( $elements as $oneRet ) {
                $ret[ $oneRet[ 'id' ] ] = $oneRet;
                $ret[ $oneRet[ 'id' ] ][ 'title' ] = $this->getI18n( $oneRet[ 'title' ] );
            }
        }
        return $ret;
    }

    public function showList() {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        $this->linker->html->setTitle( $this->getI18n( 'list_description' ) );
        if( isset( $_GET[ 'delete' ] ) ) {
            $id = ( int ) $_GET[ 'delete' ];
            list($content) = $this->db_execute(
                'getWithInactive', array( 'id' => $id )
            );
            $this->db_execute(
                'delete', array( 'id' => $id )
            );
            $this->removeI18n( $content[ 'title' ] );
            $this->removeI18n( $content[ 'summary' ] );
            $this->removeI18n( $content[ 'content' ] );
            $this->linker->path->redirect(
                $this->translatePageToUri( '/showList/' )
            );
        }
        if( $this->isAdmin() ) {
            $replacements[ 'orAny' ] = ' OR 1';
        } else {
            $replacements[ 'orAny' ] = '';
        }
        $list = $this->db_execute( 'getList', $replacements );
        foreach( $list as $element ) {
            if( $element[ 'active' ] == 1 ) {
                $state = 'active';
            } else {
                $state = 'inactive';
            }
            $title = $this->getI18n( $element[ 'title' ] );
            if( strlen( $title ) > 40 ) {
                $title = substr( $title, 0, 37 ) . '...';
            }
            $values[ $state ][ ] = array(
                'show_link' => $this->translatePageToUri(
                    '/show/' . $element[ 'id' ]
                ),
                'edit_link' => $this->translatePageToUri(
                    '/edit/' . $element[ 'id' ]
                ),
                'delete_link' => $this->translatePageToUri(
                    '/delete/' . $element[ 'id' ]
                ),
                'title' => $title,
                'date' => $element[ 'date' ],
                'id' => $element[ 'id' ]
            );
        }
        $values[ 'page' ][ 'link' ] = $this->translatePageToUri(
            '/' . __FUNCTION__ . '/'
        );
        $this->render( 'showList', $values );
        return true;
    }

    public function getContentContent( $id ) {
        list($element) = $this->db_execute(
            'getShort', array( 'id' => $id )
        );
        $element[ 'link' ] = $this->linker->path->getLink(
            $this->shortClassName . '/show/' . $element[ 'id' ]
        );
        $element[ 'title' ] = $this->getI18n( $element[ 'title' ] );
        $element[ 'summary' ] = $this->getI18n( $element[ 'summary' ] );
        return $element;
    }

    public function shortList() {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        $id = ( int ) $this->linker->path->page[ 'id' ];
        if( $id != '' ) {
            $this->linker->admin->insertPage(
                $this->shortClassName . '/editShortList/' . $id, 'Contenu', 'picto_modify.png'
            );
        }
        $list = $this->getParam( 'list>' . $id . '>activated', false );
        $title = $this->getI18n( $this->getParam( 'list>' . $id . '>name', array( ) ) );
        $this->linker->html->setTitle( $title );
        $values[ 'showList' ][ 'image' ] = $this->getParam( 'list>' . $id . '>image', '' );
        $values[ 'showList' ][ 'summary' ] = $this->getI18n(
            $this->getParam( 'list>' . $id . '>summary', 0 )
        );

        $meta = $this->getI18n(
            $this->getParam( 'list>' . $id . '>seo_titleBar', 0 )
        );
        if( empty( $meta ) ) {
            $meta = $title;
        }
        $this->linker->html->setMetaTitle( $meta );

        $meta = $this->getI18n(
            $this->getParam( 'list>' . $id . '>seo_metaDescription', 0 )
        );
        if( empty( $meta ) ) {
            $meta = $values[ 'showList' ][ 'summary' ];
        }
        $this->linker->html->setMetaDescription( $meta );

        // We verify if there are contents in the list
        if( !$list ) {
            $this->render( 'emptyShortList' );
            return true;
        }
        // We prepare the rendering
        foreach( $list as $element ) {
            list($element) = $this->db_execute(
                'getShort', array( 'id' => $element )
            );
            $values[ 'contents' ][ ] = $element;
        }
        foreach( $values[ 'contents' ] as &$element ) {
            $element[ 'link' ] = $this->linker->path->getLink(
                $this->shortClassName . '/show/' . $element[ 'id' ]
            );
            $element[ 'title' ] = $this->getI18n( $element[ 'title' ] );
            $element[ 'summary' ] = $this->getI18n( $element[ 'summary' ] );
        }

        $this->render( 'shortList_show', $values );
        return true;
    }

    public function getSubmenus( $method, $id ) {
        $ret = array( );
        if( $method == 'shortList' ) {
            $list = $this->getParam( 'list>' . $id . '>activated', false );
            if( !$this->getParam( 'list>' . $id . '>enable_subMenus', true ) ) {
                return array( );
            }
            if( is_array( $list ) ) {
                foreach( $list as $pageId ) {
                    list($element) = $this->db_execute(
                        'getContentTitle', array( 'id' => $pageId )
                    );
                    $ret[ $pageId ] = array(
                        'title' => $this->getI18n( $element[ 'title' ] ),
                        'link' => $this->linker->path->getLink( $this->shortClassName . '/show/' . $pageId )
                    );
                }
            }
        }
        return $ret;
    }

    public function editShortList() {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        $this->onlyAdmin();
        $this->helper->isAdminPage( true );

        $id = ( int ) $this->linker->path->page[ 'id' ];
        if( $this->formSubmitted( 'delete_shortList' ) ) {
            $name = $this->getParam( 'list>' . $id . '>name' );
            $summary = $this->getParam( 'list>' . $id . '>summary' );
            $this->removeI18n( $name );
            $this->removeParam( 'list>' . $id );
            $this->writeParams();
            $this->removeFromSitemap( $this->shortClassName . '/shortList/' . $id );
            $this->linker->path->redirect(
                $this->translatePageToUri( '/' . __FUNCTION__ . '/0' )
            );
            $this->linker->menu->submenuMayHaveChanged( $this->shortClassName, 'showList', $id );
        } elseif( $this->formSubmitted( 'contentListEditor' ) ) {
            if( $id == 0 ) {
                $lists = array_keys(
                    $this->getParam( 'list', array( ) )
                );
                $id = max( $lists ) + 1;
                // Creating i18n entries
                $name = $this->setI18n( 0, 'new' );
                $summary = $this->setI18n( 0, 'new' );
                $seo_titleBar = $this->setI18n( 0, 'new' );
                $seo_metaDescription = $this->setI18n( 0, 'new' );

                // Saving i18n IDs
                $this->setParam( 'list>' . $id . '>name', $name );
                $this->setParam( 'list>' . $id . '>summary', $summary );
                $this->setParam( 'list>' . $id . '>seo_titleBar', $seo_titleBar );
                $this->setParam( 'list>' . $id . '>seo_metaDescription', $seo_metaDescription );
                $this->writeParams();
            }
            // Getting i18n IDs
            $name = $this->getParam( 'list>' . $id . '>name', 0 );
            $summary = $this->getParam( 'list>' . $id . '>summary', 0 );
            $seo_titleBar = $this->getParam( 'list>' . $id . '>seo_titleBar', 0 );
            $seo_metaDescription = $this->getParam( 'list>' . $id . '>seo_metaDescription', 0 );

            // Saving i18n values
            $this->setI18n( $name, $_POST[ 'name' ] );
            $summary = $this->setI18n( $summary, $_POST[ 'summary' ] );
            $seo_titleBar = $this->setI18n( $seo_titleBar, $_POST[ 'seo_titleBar' ] );
            $seo_metaDescription = $this->setI18n( $seo_metaDescription, $_POST[ 'seo_metaDescription' ] );

            // Saving params
            $order = explode( '-', $_POST[ 'order' ] );
            $this->setParam( 'list>' . $id . '>summary', $summary );
            $this->setParam( 'list>' . $id . '>seo_titleBar', $seo_titleBar );
            $this->setParam( 'list>' . $id . '>seo_metaDescription', $seo_metaDescription );
            $this->setParam( 'list>' . $id . '>activated', $order );
            $this->setParam( 'list>' . $id . '>image', $_POST[ 'image' ] );
            $this->setParam( 'list>' . $id . '>date', date( 'Y-m-d H:i:s' ) );
            $this->setParam( 'list>' . $id . '>enable_subMenus', isset( $_POST[ 'enable_subMenus' ] ) );
            $this->writeParams();

            // Mnaging sitemap
            $this->removeFromSitemap( $this->shortClassName . '/shortList/' . $id );
            $sitemapPriority = $this->getParam( 'sitemap>shortList>Priority', 0.7 );
            $this->addToSitemap( $this->shortClassName . '/shortList/' . $id, $sitemapPriority );

            $this->linker->menu->submenuMayHaveChanged( $this->shortClassName, 'showList', $id );
            // Redirection
            $this->linker->path->redirect( __CLASS__, 'shortList', $id );
        }

        $lists = $this->getParam( 'list', array( ) );
        $values[ 'lists' ][ 0 ] = array(
            'link' => $this->linker->path->getLink(
                $this->shortClassName . '/' . __FUNCTION__ . '/0'
            ),
            'name' => $this->getI18n( 'newShortList' )
        );
        if( is_array( $lists ) ) {
            foreach( $lists as $oneId => $oneList ) {
                $values[ 'lists' ][ ] = array(
                    'link' => $this->linker->path->getLink(
                        $this->shortClassName . '/' . __FUNCTION__ . '/' . $oneId
                    ),
                    'name' => $oneId . ' - ' . $this->getI18n( $oneList[ 'name' ] )
                );
            }
        }

        // We are editing a list
        if( $id != 0 ) {
            $values[ 'list' ][ 'name' ] = $this->getParam(
                'list>' . $id . '>name', 0
            );
            $values[ 'list' ][ 'title' ] = $this->getI18n(
                $this->getParam(
                    'list>' . $id . '>name', 0
                )
            );
            $values[ 'list' ][ 'summary' ] = $this->getParam(
                'list>' . $id . '>summary', 0
            );
            $values[ 'list' ][ 'seo_titleBar' ] = $this->getParam(
                'list>' . $id . '>seo_titleBar', 0
            );
            $values[ 'list' ][ 'seo_metaDescription' ] = $this->getParam(
                'list>' . $id . '>seo_metaDescription', 0
            );
            if( $this->getParam( 'list>' . $id . '>enable_subMenus', true ) ) {
                $values[ 'list' ][ 'enable_subMenus' ] = 'checked';
            }
        } else {
            $values[ 'list' ][ 'name' ] = $this->getI18n( 'newShortList_title' );
            $values[ 'list' ][ 'title' ] = $this->getI18n( 'newShortList_title' );
            $values[ 'list' ][ 'isNew' ] = true;
            $values[ 'list' ][ 'enable_subMenus' ] = 'checked';
        }
        $replacements[ 'orAny' ] = ' ORDER BY `id`';

        $values[ 'content' ][ 'image' ] = $this->getParam( 'list>' . $id . '>image', '' );
        $list = $this->db_execute( 'getList', $replacements, $qry );
        $activated = $this->getParam( 'list>' . $id . '>activated', array( ) );
        if( is_array( $list ) ) {
            foreach( $list as $element ) {
                $values[ 'contents' ][ ] = array(
                    'title' => $this->getI18n( $element[ 'title' ] ),
                    'date' => $element[ 'date' ],
                    'id' => $element[ 'id' ]
                );
                $key = array_search( $element[ 'id' ], $activated );
                if( $key !== false ) {
                    $thereAreActiveContents = true;
                    $values[ 'activecontents' ][ $key ] = array(
                        'title' => $this->getI18n( $element[ 'title' ] ),
                        'date' => $element[ 'date' ],
                        'id' => $element[ 'id' ]
                    );
                }
            }
        }
        if( $thereAreActiveContents ) {
            // We sort the active contents by their keys
            ksort( $values[ 'activecontents' ] );
        }
        $values[ 'style' ][ 'bgColor' ] = '#CCCCCC';

        $values[ 'content' ][ 'image_folder' ] = SH_IMAGES_FOLDER . 'small/';

        $this->render( 'shortList_edit', $values );
        return true;
    }

    /**
     * public function show
     */
    public function show() {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );

        $id = ( int ) $this->linker->path->page[ 'id' ];

        if( $id != '' ) {
            $this->linker->admin->insertPage(
                $this->shortClassName . '/edit/' . $id, 'Contenu', 'picto_modify.png'
            );
        }

        if( $id == 0 ) {
            $this->linker->path->error( '404' );
        }

        $replacements = array( 'id' => $id );
        list($content[ 'content' ]) = $this->db_execute( 'get', $replacements );

        if( !isset( $content[ 'content' ][ 'id' ] ) ) {
            $this->linker->path->error( '404' );
        }


        $content[ 'content' ][ 'content' ] = $this->getI18n(
            $content[ 'content' ][ 'content' ]
        );
        $content[ 'content' ][ 'title' ] = $this->getI18n(
            $content[ 'content' ][ 'title' ]
        );
        $content[ 'content' ][ 'summary' ] = $this->getI18n(
            $content[ 'content' ][ 'summary' ]
        );

        $meta = $this->getI18n(
            $content[ 'content' ][ 'seo_titleBar' ]
        );
        if( empty( $meta ) ) {
            $meta = $content[ 'content' ][ 'title' ];
        }
        $this->linker->html->setMetaTitle( $meta );

        $meta = $this->getI18n(
            $content[ 'content' ][ 'seo_metaDescription' ]
        );
        if( empty( $meta ) ) {
            $meta = $content[ 'content' ][ 'summary' ];
        }
        $this->linker->html->setMetaDescription( $meta );


        if( $content[ 'content' ][ 'showDate' ] == 0 ) {
            unset( $content[ 'content' ][ 'date' ] );
        }

        if( $content[ 'content' ][ 'showTitle' ] == 1 ) {
            $this->linker->html->setTitle( $content[ 'content' ][ 'title' ] );
        } else {
            $this->linker->html->setTitle( '' );
        }

        $rendered = $this->render( 'content', $content );
        return true;
    }

    public function delete() {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        $this->onlyAdmin();
        $this->helper->isAdminPage( true );

        $id = ( int ) $this->linker->path->page[ 'id' ];
        if( $id == 0 ) {
            $this->linker->path->error( '404' );
        }

        if( $this->formSubmitted( 'delete_content' ) ) {
            list($content) = $this->db_execute(
                'getWithInactive', array( 'id' => $id )
            );
            $this->db_execute(
                'delete', array( 'id' => $id )
            );
            $this->removeI18n( $content[ 'title' ] );
            $this->removeI18n( $content[ 'summary' ] );
            $this->removeI18n( $content[ 'content' ] );
            $this->linker->path->redirect(
                $this->translatePageToUri( '/showList/' )
            );
            return true;
        }

        $this->linker->html->setTitle( $this->getI18n( 'deletePage_title' ) );

        list($values[ 'content' ]) = $this->db_execute(
            'getWithInactive', array( 'id' => $id )
        );

        $values[ 'content' ][ 'title' ] = $this->getI18n( $values[ 'content' ][ 'title' ] );

        if( !$values[ 'content' ][ 'active' ] ) {
            unset( $values[ 'content' ][ 'active' ] );
        }

        echo $this->render( 'delete', $values, false, false );
        return true;
    }

    /**
     * public function edit
     */
    public function edit() {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );

        $this->onlyAdmin();
        $this->helper->isAdminPage( true );
        $id = ( int ) $this->linker->path->page[ 'id' ];
        if( $id == 0 ) {
            $content[ 'editcontent' ][ 'title' ] = $this->getI18n( 'new_page_title' );
        } else {
            $content[ 'editcontent' ][ 'title' ] = $this->getI18n( 'edit_this_page' );
        }

        $backupsFolder = SH_SITE_FOLDER . __CLASS__ . '/backups/' . $id . '/';
        if( is_dir( $backupsFolder ) ) {
            $backups = glob( $backupsFolder . '*.php' );
            rsort( $backups );
            foreach( $backups as $backupFile ) {
                $content[ 'global' ][ 'hasPreviousVersions' ] = true;
                include($backupFile);
                $date = preg_replace(
                    '`.*(20[0-9]{2})([01][0-9])([0-3][0-9])-([012][0-9])([0-5][0-9])([0-5][0-9])\.php`',
                    '$1-$2-$3 $4:$5:$6', $backupFile
                );
                $content[ 'previousVersions' ][ ] = array(
                    'file' => basename( $backupFile ),
                    'date' => strtolower( $this->linker->datePicker->dateAndTimeToLocal( $date, true ) )
                );
            }
        }

        // Creates the small images folder, if needed
        $folder = SH_IMAGES_FOLDER . 'small/';
        if( !is_dir( $folder ) ) {
            // We don't use $this->addFolder because only masters may write in that folder
            mkdir( $folder );
            $this->helper->writeInFile(
                $folder . sh_browser::RIGHTSFILE, sh_browser::ALL
            );
            $this->helper->writeInFile(
                $folder . sh_browser::DIMENSIONFILE, '100x100'
            );
            $this->helper->writeInFile(
                $folder . sh_browser::OWNERFILE, $this->userName
            );
        }

        if( $this->formSubmitted( 'content_edit' ) ) {
            if( $id == 0 ) {
                $this->db_execute( 'create', array( ) );
                $id = $this->db_insertId();
                $isNew = true;
            }
            $newAndNotActive = $this->save( $id, $isNew );
        }

        $content[ 'content' ][ 'image_folder' ] = SH_IMAGES_FOLDER . 'small/';
        if( $newAndNotActive ) {
            $content[ 'content' ][ 'newAndNotActive' ] = true;
            $content[ 'content' ][ 'newAndNotActiveLink' ] =
                $this->translatePageToUri( '/' . __FUNCTION__ . '/' . $id )
            ;
        }
        if( $id == 0 || $newAndNotActive ) {
            $content[ 'content' ][ 'active' ] = 'checked';
            $content[ 'content' ][ 'showtitle' ] = 'checked';
            $content[ 'content' ][ 'isNews' ] = '';
            $content[ 'content' ][ 'id' ] = $id;
        } else {

            if( isset( $_GET[ 'version' ] ) && file_exists( $backupsFolder . $_GET[ 'version' ] ) ) {
                include($backupsFolder . $_GET[ 'version' ]);
                $content[ 'global' ][ 'i18n_class' ] = 'temp';
                $this->linker->i18n->set( 'temp', 1, $backup[ 'summary' ] );
                $content[ 'content' ][ 'summary' ] = 1;
                $this->linker->i18n->set( 'temp', 2, $backup[ 'seo_titleBar' ] );
                $content[ 'content' ][ 'seo_titleBar' ] = 2;
                $this->linker->i18n->set( 'temp', 3, $backup[ 'seo_metaDescription' ] );
                $content[ 'content' ][ 'seo_metaDescription' ] = 3;
                $this->linker->i18n->set( 'temp', 4, $backup[ 'content' ] );
                $content[ 'content' ][ 'content' ] = 4;
                $this->linker->i18n->set( 'temp', 5, $backup[ 'title' ] );
                $content[ 'content' ][ 'title' ] = 5;
                $content[ 'global' ][ 'is_already_a_previous_version' ] = true;
            } else {
                // We read the values for the article
                $replacements = array( 'id' => $id );
                list($content[ 'content' ]) = $this->db_execute(
                    'getWithInactive', $replacements
                );
                $content[ 'global' ][ 'i18n_class' ] = __CLASS__;
            }
            // We load the values that are in db
            $content[ 'content' ][ 'active' ] = $this->addChecked(
                $content[ 'content' ][ 'active' ]
            );
            $content[ 'content' ][ 'showdate' ] = $this->addChecked(
                $content[ 'content' ][ 'showDate' ]
            );
            $content[ 'content' ][ 'showtitle' ] = $this->addChecked(
                $content[ 'content' ][ 'showTitle' ]
            );
            $content[ 'content' ][ 'isNews' ] = $this->addChecked(
                $content[ 'content' ][ 'isNews' ]
            );
        }
        //sh_diaporama::addToPreviews('content_editor');
        $this->render( 'edit', $content );
    }

    public function news_edit() {
        $this->onlyAdmin();
        $this->helper->isAdminPage( true );

        $id = 0;
        if( $this->formSubmitted( 'news_edit' ) ) {
            // getting the i18n ids for the title, the intro, and SEO
            $title = $this->getParam( 'news>' . $id . '>title', 0 );
            $intro = $this->getParam( 'news>' . $id . '>intro', 0 );
            $seo_titleBar = $this->getParam( 'news>' . $id . '>seo_titleBar', 0 );
            $seo_metaDescription = $this->getParam( 'news>' . $id . '>seo_metaDescription', 0 );

            $title = $this->setI18n( $title, $_POST[ 'title' ] );
            $intro = $this->setI18n( $intro, $_POST[ 'intro' ] );
            $seo_titleBar = $this->setI18n( $seo_titleBar, $_POST[ 'seo_titleBar' ] );
            $seo_metaDescription = $this->setI18n( $seo_metaDescription, $_POST[ 'seo_metaDescription' ] );

            $this->setParam(
                'news>' . $id,
                array(
                'title' => $title,
                'intro' => $intro,
                'number_by_page' => $_POST [ 'number_by_page' ],
                'seo_titleBar' => $seo_titleBar,
                'seo_metaDescription' => $seo_metaDescription
                )
            );
            $this->writeParams();
        }

        $values[ 'news' ] = $this->getParam( 'news>' . $id, array( ) );

        $this->render( 'news_edit', $values );
    }

    public function news() {
        $id = 0;
        $params = $this->getParam( 'news>' . $id, array( ) );

        $values[ 'new' ][ 'title' ] = $this->getI18n( $params[ 'title' ] );
        $values[ 'new' ][ 'intro' ] = $this->getI18n( $params[ 'intro' ] );
        $number_by_page = $this->getI18n( $params[ 'intro' ] );
        if( !($number_by_page > 0) ) {
            $number_by_page = 16;
        }
        $title = $values[ 'new' ][ 'title' ];
        $this->linker->html->setTitle( $title );

        $meta = $this->getI18n( $params[ 'seo_titleBar' ] );
        if( $meta && $meta != $title ) {
            $this->linker->html->setMetaTitle( $meta );
        }

        $meta = $this->getI18n( $params[ 'seo_metaDescription' ] );
        if( empty( $meta ) ) {
            $meta = $values[ 'new' ][ 'intro' ];
        }
        $this->linker->html->setMetaDescription( $meta );

        $values[ 'news' ] = $this->db_execute( 'getNews', array( 'count' => $number_by_page ) );
        if( is_array( $values[ 'news' ] ) ) {
            foreach( $values[ 'news' ] as $newsId => $oneNews ) {
                $values[ 'news' ][ $newsId ][ 'title' ] = $this->getI18n( $oneNews[ 'title' ] );
                $values[ 'news' ][ $newsId ][ 'summary' ] = $this->getI18n( $oneNews[ 'summary' ] );
                $values[ 'news' ][ $newsId ][ 'link' ] = $this->linker->path->getLink( __CLASS__ . '/show/' . $oneNews[ 'id' ] );
                $values[ 'thereAreNews' ][ 'set' ] = true;
            }
        }
        $this->render( 'news', $values );
    }

    /**
     * protected function addChecked
     *
     */
    protected function addChecked( $condition ) {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        if( $condition == 1 ) {
            return 'checked';
        }
        return '';
    }

    /**
     * protected function checkedToBinary
     *
     */
    protected function checkedToBinary( $element ) {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        if( isset( $_POST[ $element ] ) ) {
            return '1';
        }
        return '0';
    }

    /**
     * protected function save
     */
    protected function save( $id, $isNew = false ) {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        // We create a backup for this new version
        $this->helper->writeArrayInFile(
            SH_SITE_FOLDER . __CLASS__ . '/backups/' . $id . '/' . date( 'Ymd-His' ) . '.php', 'backup', $_POST, false
        );
        $this->saveCredentials( $id );
        $active = $this->checkedToBinary( 'active' );
        $showTitle = $this->checkedToBinary( 'showTitle' );
        $showDate = $this->checkedToBinary( 'showDate' );
        $isNews = $this->checkedToBinary( 'isNews' );

        list($element) = $this->db_execute( 'getWithInactive', array( 'id' => $id ) );
        $i18nTitle = $this->setI18n( $element[ 'title' ], $_POST[ 'title' ] );
        $i18nSummary = $this->setI18n( $element[ 'summary' ], $_POST[ 'summary' ] );
        $i18nContent = $this->setI18n( $element[ 'content' ], $_POST[ 'content' ] );

        $i18nTitleBar = $this->setI18n( $element[ 'seo_titleBar' ], $_POST[ 'seo_titleBar' ] );
        $i18nMetaDescription = $this->setI18n( $element[ 'seo_metaDescription' ], $_POST[ 'seo_metaDescription' ] );
        $replacements = array(
            'id' => $id,
            'isNews' => $isNews,
            'image' => $_POST[ 'image' ],
            'showTitle' => $showTitle,
            'showDate' => $showDate,
            'active' => $active,
            'title' => $i18nTitle,
            'content' => $i18nContent,
            'summary' => $i18nSummary,
            'seo_titleBar' => $i18nTitleBar,
            'seo_metaDescription' => $i18nMetaDescription
        );
        $this->db_execute( 'save', $replacements );

        $this->removeFromSitemap( $this->shortClassName . '/show/' . $id );

        // We check if the page is in a short list
        $lists = $this->getParam( 'list' );
        foreach( $lists as $listId => $list ) {
            if( in_array( $id, $list[ 'activated' ] ) ) {
                $this->linker->menu->submenuMayHaveChanged( $this->shortClassName, 'shortList', $listId );
            }
        }

        if( $active ) {
            $this->addToSitemap( $this->shortClassName . '/show/' . $id, $priority );

            $this->search_removeEntry( 'show', $id );

            $this->search_addEntry(
                'show', $id, $_POST[ 'title' ], $_POST[ 'summary' ], $_POST[ 'content' ]
            );
            $this->linker->path->redirect( __CLASS__, 'show', $id );
        }
        if( isset( $_GET[ 'version' ] ) ) {
            $this->linker->path->redirect( __CLASS__, 'edit', $id );
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
    public function searcher_showResults( $method, $elements ) {
        $this->debug( __FUNCTION__ . '(' . $method . ',' . print_r( $elements, true ) . ');', 2, __LINE__ );

        // We prepare the rendering
        foreach( $elements as $element ) {
            list($element) = $this->db_execute(
                'getShort', array( 'id' => $element )
            );
            $values[ 'contents' ][ ] = $element;
        }
        foreach( $values[ 'contents' ] as &$element ) {
            $element[ 'link' ] = $this->linker->path->getLink(
                $this->shortClassName . '/show/' . $element[ 'id' ]
            );
            $element[ 'title' ] = $this->getI18n( $element[ 'title' ] );
            $element[ 'summary' ] = $this->getI18n( $element[ 'summary' ] );
        }
        return array(
            'name' => $this->getI18n( 'search_contentsTitle' ),
            'content' => $this->render( 'searcher_results', $values, false, false )
        );
    }

    /**
     * Gets the list of the contents types that the searcher should search in.
     * @return array Un array containing the list of search types.
     */
    public function searcher_getScope() {
        return array(
            'scope' => $this->shortClassName,
            'name' => $this->getI18n( 'search_contentsTitle' )
        );
    }

    /**
     * public function sitemap_renew
     *
     */
    public function sitemap_renew() {
        $replacements[ 'orAny' ] = '';

        $list = $this->db_execute( 'getList', $replacements );
        if( is_array( $list ) ) {
            foreach( $list as $element ) {
                $this->addToSitemap(
                    $this->shortClassName . '/show/' . $element[ 'id' ], 0.6
                );
            }
        }
        $shortLists = $this->getParam( 'list' );
        if( is_array( $shortLists ) ) {
            foreach( array_keys( $shortLists ) as $shortList ) {
                $this->addToSitemap(
                    $this->shortClassName . '/shortList/' . $shortList, 0.6
                );
            }
        }
        $this->addToSitemap( $this->shortClassName . '/news/', 0.8 );
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
    public function getPageName( $action, $id = null, $forUrl = false ) {
        $name = $this->getI18n( 'action_' . $action );
        if( $action == 'show' ) {
            list($title) = $this->db_execute( 'getTitleWithInactive', array( 'id' => $id ) );
            $title = $this->getI18n( $title[ 'title' ] );
            if( $forUrl ) {
                return $title;
            }
            $name = str_replace(
                array( '{id}', '{link}', '{title}' ), array( $id, $link, $title ), $name
            );
        } elseif( $action == 'edit' && $forUrl ) {
            list($title) = $this->db_execute( 'getTitleWithInactive', array( 'id' => $id ) );
            $title = $this->getI18n( $title[ 'title' ] );
            return $title;
        } elseif( $action == 'shortList' ) {
            $title = $this->getParam( 'list>' . $id . '>name' );
            $title = $this->getI18n( $title );
            if( $forUrl ) {
                return $title;
            }
            $name = str_replace(
                array( '{id}', '{link}', '{title}' ), array( $id, $link, $title ), $name
            );
        } elseif( $action == 'editShortList' ) {
            $title = $this->getParam( 'list>' . $id . '>name' );
            $title = $this->getI18n( $title );
            if( $forUrl ) {
                return $title;
            }
            $name = str_replace(
                array( '{id}', '{link}', '{title}' ), array( $id, $link, $title ), $name
            );
        } elseif( $forUrl ) {
            return false;
        }

        if( $name != '' ) {
            return $name;
        }
        return $this->__toString() . '->' . $action . '->' . $id;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri( $page ) {
        list($class, $method, $id) = explode( '/', $page );
        if( $method == 'editShortList' ) {
            if( $id == 0 ) {
                return '/' . $this->shortClassName . '/editShortList/' . $id . '-new.php';
            }
            $name = $this->getParam( 'list>' . $id . '>name' );
            $realName = urlencode( trim( $this->getI18n( $name ) ) );
            if( $realName != '' ) {
                $realName = '-' . $realName;
            }
            return '/' . $this->shortClassName . '/editShortList/' . $id . $realName . '.php';
        }
        if( $method == 'shortList' && $id > 0 ) {
            $name = $this->getParam( 'list>' . $id . '>name' );
            $realName = urlencode( trim( $this->getI18n( $name ) ) );
            if( $realName != '' ) {
                $realName = '-' . $realName;
            }
            return '/' . $this->shortClassName . '/shortList/' . $id . $realName . '.php';
        }
        if( $method == 'show' && $id != 0 ) {
            list($title) = $this->db_execute( 'getTitle', array( 'id' => $id ), $qry );
            $title = urlencode( $this->getI18n( $title[ 'title' ] ) );
            if( trim( $title ) != '' ) {
                $realName = '-' . $title;
            } else {
                $realName = '';
            }
            return '/' . $this->shortClassName . '/show/' . $id . $realName . '.php';
        }
        if( $method == 'delete' && $id != 0 ) {
            return '/' . $this->shortClassName . '/delete/' . $id . '.php';
        }
        if( $method == 'showList' ) {
            return '/' . $this->shortClassName . '/showList.php';
        }
        if( $method == 'edit' ) {
            if( $id != 0 ) {
                list($title) = $this->db_execute( 'getTitle', array( 'id' => $id ) );
                $title = urlencode( $this->getI18n( $title[ 'title' ] ) );
                if( !empty( $title ) ) {
                    $title = '-' . $title;
                }
            }
            return '/' . $this->shortClassName . '/edit/' . $id . $title . '.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage( $uri ) {
        if( preg_match( '`/' . $this->shortClassName . '/([^/]+)(/([0-9]+)(-[^/]+)?)?\.php`', $uri, $matches ) ) {
            if( $matches[ 1 ] == 'editShortList' ) {
                return $this->shortClassName . '/editShortList/' . $matches[ 3 ];
            }
            if( $matches[ 1 ] == 'shortList' ) {
                return $this->shortClassName . '/shortList/' . $matches[ 3 ];
            }
            if( $matches[ 1 ] == 'show' ) {
                return $this->shortClassName . '/show/' . $matches[ 3 ];
            }
            if( $matches[ 1 ] == 'edit' ) {
                return $this->shortClassName . '/edit/' . $matches[ 3 ];
            }
            if( $matches[ 1 ] == 'delete' ) {
                return $this->shortClassName . '/delete/' . $matches[ 3 ];
            }
        }
        if( $uri == '/' . $this->shortClassName . '/showList.php' ) {
            return $this->shortClassName . '/showList/';
        }
        return false;
    }

    /* FACEBOOK */

    public function facebook_getModules() {
        // There are 2 modules, 1 for the categories, and 1 for the products
        return array(
            'content_articles' => $this->getI18n( 'facebook_articles' ),
            'content_shortLists' => $this->getI18n( 'facebook_shortLists' )
        );
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

}