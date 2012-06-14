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
 * class that manages the queries and the database.
 */
class sh_shopsailors extends sh_core {
    const CLASS_VERSION = '1.1.11.04.04';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    public $callWithoutId = array(
        'manageSites', 'manageSite', 'installMasterServer', 'install', 'addSite', 'backupSite', 'restoreSite'
    );
    public $callWithId = array( );

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            // The class datas are not in the same version as this file, or don't exist (installation)
            if( version_compare( $installedVersion, '1.1.11.04.04', '<' ) ) {
                $this->helper->addClassesSharedMethods( 'sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__ );
            }
            if( version_compare( $installedVersion, '1.1.11.05.27', '<' ) ) {
                $this->helper->addClassesSharedMethods( 'sh_cron', '', __CLASS__ );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
        return true;
    }

    public function cron_job( $type ) {
        sh_cache::disable();
        $start = time();
        if( $type == sh_cron::JOB_HOUR ) {
            $this->backupSite();
        }
    }

    public function master_getMenuContent() {
        $masterMenu = array(
            'Section Master' => array(
                array(
                    'link' => __CLASS__ . '/manageSites/',
                    'text' => 'Gérer les sites et redirections',
                    'icon' => 'picto_list.png'
                )
            )
        );
        return $masterMenu;
    }

    public function admin_getMenuContent() {
        $adminMenu = array( );
        return $adminMenu;
    }

    protected function manageSite_save() {
        $version = $this->getClassInstalledVersion();
        $isNew = empty($_GET['site']);
        // We get the old list.php variables
        $site = str_replace(
            array(
                '\'','"',' ','`'
            ),
            '_',
            $_POST['siteName']
        );
        
        include(SH_SITES_FOLDER . 'list.php');

        // We add the site name...
        $sitesNames[$site] = $_POST['siteName'];

        // the site main domain...
        $mainDomains[$site] = $_POST['mainDomain'];

        // the list of the domains...
        $oldSites = $sites;
        $sites = array( );
        foreach( $oldSites as $regexp => $siteName ) {
            if( $siteName != $site ) {
                $sites[$regexp] = $siteName;
            }
        }
        foreach( explode( "\n", $_POST['domains'] ) as $domain ) {
            // We translate the domain to a regular expression
            $domain = '`' . str_replace(
                    array( '.', '*' ),
                    array( '\.', '.*' ),
                    trim( $domain )
                ) . '`';
            $sites[$domain] = $site;
        }

        // and we save it
        $fileContent = '<?php' . "\n/*Auto generated file*/\n";
        $fileContent .= '$version = ' . var_export( $version, true ) . ";\n\n";
        $fileContent .= '$default = ' . var_export( $default, true ) . ";\n\n";
        $fileContent .= '$sitesNames = ' . var_export( $sitesNames, true ) . ";\n\n";
        $fileContent .= '$mainDomains = ' . var_export( $mainDomains, true ) . ";\n\n";
        $fileContent .= '$sites = ' . var_export( $sites, true ) . ";\n\n";
        $fileContent .= '$redirections = ' . var_export( $redirections, true ) . ";\n\n";
        $fileContent .= '$masterServer = ' . var_export( $masterServer, true ) . ";\n\n";
        $fileContent .= '$localMasterServers = ' . var_export( $localMasterServers, true ) . ";\n\n";
        $fileContent .= '$devMasterServer = ' . var_export( $devMasterServer, true ) . ";\n\n";
        $fileContent .= '$prodMasterServer = ' . var_export( $prodMasterServer, true ) . ";\n\n";

        $this->helper->writeInFile(
            SH_SITES_FOLDER . 'list.php',
            $fileContent
        );

        if($isNew){
            $this->copy_directory(SH_SITES_FOLDER.'site_model',SH_SITES_FOLDER.$site);
        }
        // Database
        $this->linker->db->site_configure_save( $site, $_POST['db'],$isNew );


        // Allowed templates
        if( is_array( $_POST['templates'] ) ) {
            $templates = $this->linker->template->restricted_setAll( array_keys( $_POST['templates'] ), $site );
        }
        if($isNew){
            // W"e should ask the master to allow this site
            $allowedSiteCode = $this->linker->masterServer->getAllowedSiteCode();
            
            if($allowedSiteCode){
                $paramsFile = SH_SITES_FOLDER.$site.'/sh_params/sh_masterServer.params.php';
                $this->linker->params->addElement($paramsFile, true);
                $this->linker->params->set($paramsFile,'master_site_code',$allowedSiteCode);
                $this->linker->params->set($paramsFile,'master_domain',SH_MASTERSERVER_DOMAIN);
                $this->linker->params->write($paramsFile);

                $this->helper->writeInFile(
                    SH_SITES_FOLDER . 'list.php',
                    $fileContent
                );

                $content = file_get_contents('http://'.$_POST['mainDomain'].'/sh_updater/ajax_echo_ok.php');

                $this->linker->html->addMessage('Called page : '.'http://'.$_POST['mainDomain'].'/sh_updater/ajax_echo_ok.php',false);
                $this->linker->html->addMessage('Site généré avec succès!',false);
            }else{
                $this->linker->html->addMessage('Site généré avec succès!');
            }
            
            $this->linker->path->redirect(__CLASS__.'/manageSite.php?site='.$site);
        }
    }

    protected function copy_directory( $source, $destination ) {
        if ( is_dir( $source ) ) {
            @mkdir( $destination );
            $directory = dir( $source );
            while ( FALSE !== ( $readdirectory = $directory->read() ) ) {
                if ( $readdirectory == '.' || $readdirectory == '..' ) {
                    continue;
                }
                $PathDir = $source . '/' . $readdirectory;
                if ( is_dir( $PathDir ) ) {
                    $this->copy_directory( $PathDir, $destination . '/' . $readdirectory );
                    continue;
                }
                copy( $PathDir, $destination . '/' . $readdirectory );
            }

            $directory->close();
        }else {
            copy( $source, $destination );
        }
    }


    public function manageSite() {
        $this->onlyMaster();
        if( $this->formSubmitted( 'site_manage' ) ) {
            $this->manageSite_save();
        }
        if( isset( $_POST['site'] ) ) {
            $site = $_POST['site'];
        } else {
            $site = $_GET['site'];
        }
        $values['site']['id'] = $site;

        $values['db'] = $this->linker->db->site_configure_form( $site );

        include(SH_SITES_FOLDER . 'list.php');
        if( $default == $site ) {
            $values['default']['state'] = 'checked';
        }
        $values['site']['name'] = $sitesNames[$site];
        $values['mainDomain']['value'] = $mainDomains[$site];

        // domains
        $values['domains']['list'] = '';
        foreach( $sites as $regexp => $siteName ) {
            if( $site == $siteName ) {
                // We should add this domain
                $domain = str_replace( array( '.*', '\.', '`' ), array( '*', '.', '' ), $regexp );
                $values['domains']['list'] .= $separator . $domain;
                $separator = "\n";
            }
        }

        //Cron - calls are made on http only
        $cron = '# Crontab entries for the Shopsailors\'s site "' . $sitesNames[$site] . '" (http://' . $mainDomains[$site] . ')' . "\n";
        $cron .= '# Every 5 minutes' . "\n";
        $cron .= '0,5,10,15,20,25,30,35,40,45,50,55 * * * * curl http://' . $mainDomains[$site] . '/cron/job/0.php' . "\n";
        $cron .= '# end of the crontab entries for the Shopsailors\'s site "' . $sitesNames[$site] . "\"\n\n";
        $values['cron']['content'] = $cron;

        // Locked templates
        $templates = $this->linker->template->restricted_getAll( $site );
        $values['templates'] = $templates;

        $this->render( 'manageSite', $values );
    }

    /**
     * This page only shows the list of the sites present on this server, with a link to edit their parameters
     */
    public function manageSites() {
        $this->onlyMaster();

        if( $this->formSubmitted( 'sites_manage' ) ) {
            $this->linker->path->redirect(
                $this->linker->path->getLink(
                    __CLASS__ . '/manageSite/'
                ) . '?site=' . $_POST['editSite']
            );
        }

        $values['existing'] = $this->sites_list();

        $values['redirections'] = $this->get_redirections();

        $this->render( 'manageSites', $values );
    }

    protected function get_redirections() {
        include(SH_SITES_FOLDER . 'list.php');
        $readableRedirections = array( );
        if( is_array( $redirections ) ) {
            foreach( $redirections as $domainRegExp => $destination ) {
                $domainRegExp = str_replace(
                        array(
                            '`',
                            '\\.'
                        ),
                        array(
                            '',
                            '.'
                        ),
                        $domainRegExp
                );
                $type = 'simple';
                if( substr( $domainRegExp, 0, 6 ) == '(.*.)?' ) {
                    if( substr( $destination, 0, 2 ) == '$1' ) {
                        $type = 'copie';
                    } else {
                        $type = 'redirection';
                    }
                }
                $domain = str_replace(
                        '(.*.)?',
                        '',
                        $domainRegExp
                );
                $destination = str_replace(
                        '$1',
                        '',
                        $destination
                );
                $readableRedirections[] = array(
                    'regexp' => $domainRegExp,
                    'domain' => $domain,
                    'destination' => $destination,
                    'type' => $type
                );
            }
        }
        return $readableRedirections;
    }

    public function sites_add() {
        
    }

    protected function sites_list() {
        include(SH_SITES_FOLDER . 'list.php');

        $cpt = 1;
        foreach( $sitesNames as $siteCode => $siteName ) {
            if( is_dir( SH_SITES_FOLDER . $siteCode ) ) {
                $ret[$cpt]['id'] = $siteCode;
                $ret[$cpt]['name'] = $siteName;
                $ret[$cpt]['domain'] = $mainDomains[$siteCode];
                if( $siteCode == $default ) {
                    $ret[$cpt]['state'] = 'selected';
                }
                $ret[$cpt]['borderColor'] = 'grey';
                if( $mainDomains[$siteCode] == $masterServer ) {
                    $ret[$cpt]['borderColor'] = 'red';
                }


                $cpt++;
            }
        }
        //foreach($redirections as $redirection
        return $ret;
    }

    public function addSite( $siteName ) {
        
    }

    /* backup the db OR just a table */

    protected function backup_tables( $file ) {
        $return = '<?php exit;?>' . "\n";
        $this->linker->db->updateQueries( __CLASS__ );
        //get all of the tables
        $tables = array( );
        $tables = $this->db_execute( 'backup_show_tables', array( ), $qry );
        //cycle through
        foreach( $tables as $table ) {
            $table = array_pop( $table );
            $return .= '-- ------------------------------------------------------------' . "\n";
            $return .= '-- BEGINNING OF THE EXPORT FOR TABLE ' . $table . "\n\n";
            $return .= 'DROP TABLE ' . $table . ';' . "\n\n";


            list($create_table) = $this->db_execute( 'backup_show_create_table', array( 'table' => $table ) );
            $return .= $create_table['Create Table'] . ';' . "\n\n";

            $contents = $this->db_execute( 'backup_select_everything', array( 'table' => $table ), $qry );
            if( !empty( $contents ) ) {
                $return .= 'INSERT INTO ' . $table . "\n";
                $return .= '(`' . implode( '`,`', array_keys( $contents[0] ) ) . '`' . ')' . "\n";
                $return .= 'VALUES' . "\n";
                $separator = '';
                foreach( $contents as $content ) {
                    $content = array_map( 'addslashes', $content );
                    $return .= $separator . '("' . implode( '","', $content ) . '")';
                    $separator = ",\n";
                }
            }
            $return .= ";\n\n";
            $return .= '-- END OF THE EXPORT FOR TABLE ' . $table . "\n";
            $return .= '-- ------------------------------------------------------------' . "\n";
            $return .= "\n\n";
        }
        /* header( "Content-disposition: attachment; filename=sql_site.sql" );
          header( "Content-Type: application/force-download" );
          header( 'Content-Type: text/plain' ); // plain text file

          echo $return; */
        // save file
        $handle = fopen( $file, 'w+' );
        fwrite( $handle, $return );
        fclose( $handle );
    }

    public function backupSite( $siteName = '', $pathToBeZipped = '' ) {
        $file = SH_SITE_FOLDER . '/backup_database.php';
        $this->backup_tables( $file );
    }

    public function restoreSite( $siteName, $unzippedPath ) {
        
    }

    public function installMasterServer() {
        
    }

    public function install() {
        // We construct every class in order to have them built completely
        $classes = scandir( SH_CLASS_FOLDER );
        foreach( $classes as $class ) {
            if( substr( $class, 0, 3 ) == 'sh_' ) {
                $this->linker->$class;
            } elseif( substr( $class, 0, 3 ) == 'cm_' ) {
                $this->linker->$class;
            }
        }

        /*
          // Listing of the classes
          $classes = scandir(SH_CLASS_FOLDER);
          foreach($classes as $class){
          // Splitting into sh and cm classes
          if(substr($class,0,3) == 'sh_'){
          $sh_dependencies = $this->linker->$class->shopsailors_dependencies;
          $this->create_dependencies($sh_tree,$class,$sh_dependencies);
          }elseif(substr($class,0,3) == 'cm_'){
          $cm_dependencies = $this->linker->$class->shopsailors_dependencies;
          $this->create_dependencies($cm_tree,$class,$cm_dependencies);
          }
          }
          echo '<div>$sh_tree = '.nl2br(str_replace(' ','&#160;',htmlspecialchars(print_r($sh_tree,true)))).'</div>';
          // Creating dependencies tree for $class
         */
    }

    protected function create_dependencies( &$tree, $class, $dependencies ) {
        static $all_dependencies = array( );
        if( is_array( $dependencies ) ) {
            foreach( $dependencies as $oneDependency ) {
                $all_dependencies[$oneDependency][] = $class;
            }
        }
        $newTree = array( );
        $done = false;
        if( is_array( $tree ) ) {
            $reversed = array_reverse( $tree );
            foreach( $reversed as $element ) {
                if( !$done && is_array( $all_dependencies[$element] ) && in_array( $class, $all_dependencies[$element] ) ) {
                    $newTree[] = $class;
                    $done = true;
                }
                $newTree[] = $element;
            }
        }
        if( !$done ) {
            $newTree[] = $class;
        }
        $tree = array_reverse( $newTree );
    }

    public function addClass() {
        
    }

    public function removeClass() {
        
    }

    public function addTemplate() {
        
    }

    public function removeTemplate() {
        
    }

    /**
     * Allows some sites to use the restricted templates.
     */
    public function editTemplateParams() {
        
    }

    public function getPageName( $action, $id = null, $forUrl = false ) {
        if( $action == 'manageSites' ) {
            return '';
        }
        return false;
    }

    public function __tostring() {
        return get_class();
    }

}

