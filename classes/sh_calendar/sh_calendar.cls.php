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
 * Class that builds colorpickers, using the refresh_web's javascript colorPicker.
 */
class sh_calendar extends sh_core {

    const CLASS_VERSION = '1.1.12.02.01';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    public $minimal = array( );
    public $callWithoutId = array( 'manage' );
    public $callWithId = array( 'show', 'modify', 'edit_date' );
    protected $id_to_days = array(
        0 => 'sunday',
        1 => 'monday',
        2 => 'tuesday',
        3 => 'wednesday',
        4 => 'thursday',
        5 => 'friday',
        6 => 'saturday',
        7 => 'sunday',
    );
    protected $days_to_ids = array(
        'sunday' => 0,
        'monday' => 1,
        'tuesday' => 2,
        'wednesday' => 3,
        'thursday' => 4,
        'friday' => 5,
        'saturday' => 6,
        'sunday' => 7,
    );

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            $this->linker->db->updateQueries( __CLASS__ );
            if( version_compare( $installedVersion, '1.1.11.11.03', '<=' ) ) {
                $this->linker->renderer->add_render_tag( 'render_calendarbox', __CLASS__, 'render_calendarbox' );
            }
            if( version_compare( $installedVersion, '1.1.11.11.03.2', '<=' ) ) {
                $this->db_execute( 'create_table_1' );
                $this->db_execute( 'create_table_2' );
                $this->db_execute( 'create_table_3' );
            }
            if( version_compare( $installedVersion, '1.1.11.11.04', '<=' ) ) {
                $this->helper->addClassesSharedMethods( 'sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__ );
            }
            if( version_compare( $installedVersion, '1.1.12.02.01', '<=' ) ) {
                $this->db_execute( 'add_state_field' );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
    }

    public function master_getMenuContent() {
        return array( );
    }

    public function admin_getMenuContent() {
        $adminMenu[ 'Contenu' ][ ] = array(
            'link' => 'calendar/manage/', 'text' => 'Gérer les calendriers', 'icon' => 'picto_details.png'
        );
        return $adminMenu;
    }

    public function modify() {
        $this->onlyAdmin();
        $id = ( int ) $this->linker->path->page[ 'id' ];

        if( $this->formSubmitted( 'calendar_modify' ) ) {
            // We save the results of the form
            if( isset( $_POST[ 'add_type' ] ) ) {
                $_SESSION[ __CLASS__ ][ 'addType' ] = true;
            }
            if( $id == 0 ) {
                // This is a new calendar
                $id = $this->create_calendar();
                $redirect = true;
            }
            foreach( array_keys( $_POST[ 'title' ] ) as $lang ) {
                $this->calendar_set_name( $id, $_POST[ 'title' ][ $lang ], $lang );
                $this->calendar_set_description( $id, $_POST[ 'description' ][ $lang ], $lang );
            }
            if( isset( $_POST[ 'types' ] ) ) {
                foreach( $_POST[ 'types' ] as $typeId => $type ) {

                    if( $typeId == 0 ) {
                        // This is a new type
                        $typeId = $this->create_type( $id, $type[ 'color' ] );
                    } else {
                        $this->type_set_color( $id, $typeId, $type[ 'color' ] );
                    }
                    foreach( array_keys( $type[ 'name' ] ) as $lang ) {
                        $this->type_set_name( $id, $typeId, $type[ 'name' ][ $lang ], $lang );
                    }
                }
            } else {
                $this->linker->html->addMessage( $this->getI18n( 'modify_noTypes_message' ) );
            }
            if( $redirect ) {
                $this->linker->path->redirect( __CLASS__, __FUNCTION__, $id );
            }
        }

        if( $id > 0 ) {
            $this->linker->html->setTitle( $this->getI18n( 'modify_title' ) );
            list($calendar) = $this->db_execute( 'get', array( 'id' => $id ) );

            $values[ 'calendar' ] = array(
                'id' => $calendar[ 'id' ],
                'name' => $calendar[ 'name' ],
                'description' => $calendar[ 'description' ],
                'addDate' => $this->linker->path->getLink( __CLASS__ . '/edit_date/' . $calendar[ 'id' ] ),
            );
            $this->linker->db->updateQueries( __CLASS__ );
            $values[ 'types' ] = $this->db_execute( 'types_get', array( 'calendar' => $id ) );
        } else {
            $this->linker->html->setTitle( $this->getI18n( 'modify_title_new' ) );
        }
        if( $_SESSION[ __CLASS__ ][ 'addType' ] ) {
            $values[ 'types' ][ ] = array(
                'color' => '336699',
                'name' => 0,
                'id' => 0
            );
            unset( $_SESSION[ __CLASS__ ][ 'addType' ] );
        }

        $this->render( 'modify', $values );
    }

    public function getLIst() {
        $calendars = $this->db_execute( 'get_list' );
        if( is_array( $calendars ) ) {
            foreach( $calendars as $calendarId => $calendar ) {
                $calendars[ $calendarId ][ 'name' ] = $this->getI18n( $calendar[ 'name' ] );
                $calendars[ $calendarId ][ 'description' ] = $this->getI18n( $calendar[ 'name' ] );
            }
        } else {
            $calenars = array( );
        }
        return $calendars;
    }

    public function edit_date() {
        $this->onlyAdmin();
        $calendar = ( int ) $this->linker->path->page[ 'id' ];
        $id = isset( $_GET[ 'date' ] ) ? $_GET[ 'date' ] : 0;
        if( $this->formSubmitted( 'edit_date' ) ) {
            if( $id == 0 ) {
                // This is a new event
                $id = $this->create_date( $calendar, 1, $_POST[ 'date' ] );
                $redirect = true;
            }
            list($y, $m, $d) = explode( '-', $_POST[ 'date' ] );
            foreach( array_keys( $_POST[ 'title' ] ) as $lang ) {
                $this->date_set_title( $calendar, $id, $_POST[ 'title' ][ $lang ], $lang );
                $this->date_set_content( $calendar, $id, $_POST[ 'content' ][ $lang ], $lang );
            }
            $this->date_set_date( $calendar, $id, $_POST[ 'date' ] );
            $this->date_set_type( $calendar, $id, $_POST[ 'type' ] );
            $this->date_set_state( $calendar, $id, isset( $_POST[ 'active' ] ) );
            if( isset( $_POST[ 'active' ] ) ) {
                $this->linker->path->redirect(
                    $this->linker->path->getLink( __CLASS__ . '/show/' . $calendar ) . '?day=' . $d . '&month=' . $m . '&year=' . $y
                );
            }
            if( $redirect ) {
                $this->linker->path->redirect(
                    $this->linker->path->getLink( __CLASS__ . '/' . __FUNCTION__ . '/' . $calendar ) . '?date=' . $id
                );
            }
        }
        $this->linker->html->setTitle( '' );
        $values[ 'types' ] = $this->db_execute( 'types_get', array( 'calendar' => $calendar ) );
        foreach( $values[ 'types' ] as $typeId => $type ) {
            $values[ 'types' ][ $typeId ][ 'name' ] = $this->getI18n( $type[ 'name' ] );
        }
        if( $id == 0 ) {
            // New date
            $values[ 'calendar' ] = array(
                'id' => 0,
                'date' => '',
                'title' => 0,
                'content' => 0,
                'type' => 1
            );
        } else {
            list($values[ 'calendar' ]) = $this->db_execute( 'date_get', array( 'calendar' => $calendar, 'id' => $id ) );

            foreach( $values[ 'types' ] as $typeId => $type ) {
                if( $type[ 'id' ] == $values[ 'calendar' ][ 'type' ] ) {
                    $values[ 'types' ][ $typeId ][ 'state' ] = 'selected';
                }
            }
            $values[ 'calendar' ][ 'active' ] = $values[ 'calendar' ][ 'active' ] ? 'checked' : '';
        }
        $this->render( 'edit_date', $values );
    }

    public function manage() {
        $this->onlyAdmin();
        $this->linker->html->setTitle( $this->getI18n( 'manager_title' ) );
        $calendars = $this->db_execute( 'get_list' );
        foreach( $calendars as $calendar ) {
            $values[ 'calendars' ][ ] = array(
                'id' => $calendar[ 'id' ],
                'name' => $this->getI18n( $calendar[ 'name' ] ),
                'description' => $this->getI18n( $calendar[ 'description' ] ),
                'modify' => $this->linker->path->getLink( __CLASS__ . '/modify/' . $calendar[ 'id' ] ),
                'addDate' => $this->linker->path->getLink( __CLASS__ . '/edit_date/' . $calendar[ 'id' ] ),
            );
        }
        $values[ 'links' ][ 'new' ] = $this->linker->path->getLink( __CLASS__ . '/modify/0' );

        $this->render( 'manage', $values );
    }

    /**
     * This method allows other classes to create calendars.
     * @return type 
     */
    public function create_calendar() {
        $title = $this->setI18n( 0, 'NEW' );
        $content = $this->setI18n( 0, 'NEW' );
        $this->db_execute( 'create_calendar', array( 'name' => $title, 'description' => $content ) );
        $id = $this->db_insertId();
        return $id;
    }

    public function calendar_set_name( $calendar, $name, $lang = null ) {
        list($calendar) = $this->db_execute( 'get', array( 'id' => $calendar ) );
        return $this->linker->i18n->set( __CLASS__, $calendar[ 'name' ], $name, $lang );
    }

    public function calendar_set_description( $calendar, $description, $lang = null ) {
        list($calendar) = $this->db_execute( 'get', array( 'id' => $calendar ) );
        return $this->linker->i18n->set( __CLASS__, $calendar[ 'description' ], $description, $lang );
    }

    public function create_type( $calendar, $color ) {
        $name = $this->setI18n( 0, 'NEW' );
        list($max) = $this->db_execute( 'type_get_max', array( 'calendar' => $calendar ) );
        $this->db_execute( 'type_create',
                           array( 'calendar' => $calendar, 'id' => $max[ 'max' ] + 1, 'color' => $color, 'name' => $name ) );
        $id = $max[ 'max' ] + 1;

        return $id;
    }

    public function type_set_color( $calendar, $type, $color ) {
        $this->db_execute( 'type_set_color', array( 'calendar' => $calendar, 'id' => $type, 'color' => $color ) );
    }

    public function type_set_name( $calendar, $type, $name, $lang = null ) {
        list($type) = $this->db_execute( 'type_get', array( 'calendar' => $calendar, 'id' => $type ) );
        return $this->linker->i18n->set( __CLASS__, $type[ 'name' ], $name, $lang );
    }

    public function create_date( $calendar, $type, $date ) {
        $title = $this->setI18n( 0, 'NEW' );
        $content = $this->setI18n( 0, 'NEW' );
        list($max) = $this->db_execute( 'date_get_max', array( 'calendar' => $calendar ) );
        $this->db_execute(
            'date_create',
            array(
            'calendar' => $calendar,
            'id' => $max[ 'max' ] + 1,
            'date' => $date,
            'title' => $title,
            'content' => $content,
            'type' => $type
            )
        );
        $id = $max[ 'max' ] + 1;

        return $id;
    }

    public function date_set_state( $calendar, $date, $state ) {
        $state = $state ? '1' : '0';
        $this->db_execute( 'date_set_state', array( 'calendar' => $calendar, 'id' => $date, 'active' => $state ) );
    }

    public function date_set_title( $calendar, $date, $title, $lang = null ) {
        list($date) = $this->db_execute( 'date_get', array( 'calendar' => $calendar, 'id' => $date ) );
        return $this->linker->i18n->set( __CLASS__, $date[ 'title' ], $title, $lang );
    }

    public function date_set_content( $calendar, $date, $content, $lang = null ) {
        list($date) = $this->db_execute( 'date_get', array( 'calendar' => $calendar, 'id' => $date ) );
        return $this->linker->i18n->set( __CLASS__, $date[ 'content' ], $content, $lang );
    }

    public function date_set_type( $calendar, $date, $type ) {
        $this->db_execute( 'date_set_type', array( 'calendar' => $calendar, 'id' => $date, 'type' => $type ) );
    }

    public function date_set_date( $calendar, $dateId, $date ) {
        $this->linker->db->updateQueries( __CLASS__ );
        $this->db_execute( 'date_set_date', array( 'calendar' => $calendar, 'id' => $dateId, 'date' => $date ) );
    }

    public function show() {
        $id = ( int ) $this->linker->path->page[ 'id' ];
        if( !isset( $_GET[ 'year' ] ) ) {
            // We take to day as the desired day
            list($year, $month, $day) = explode( '-', date( 'Y-m-d' ) );
        } else {
            $year = $_GET[ 'year' ];
            $month = $_GET[ 'month' ];
            $day = $_GET[ 'day' ];
        }
        $date = $year . '-' . $month . '-' . $day;
        $dayOfTheWeek = date( 'w', mktime( 0, 0, 0, $month, $day, $year ) );

        $title = str_replace(
            array( 'DAYNAME', 'DAY', 'MONTH', 'YEAR' ),
            array(
            $this->getI18n( 'day_' . $dayOfTheWeek ),
            ( int ) $day,
            $this->getI18n( 'month_' . (( int ) $month) ),
            $year
            ), $this->getI18n( 'show_title' )
        );
        $this->linker->html->setTitle( $title );
        if( !$this->isAdmin() ) {
            $values[ 'dates' ] = $this->db_execute( 'dates_get_active', array( 'calendar' => $id, 'date' => $date ) );
        } else {
            $values[ 'dates' ] = $this->db_execute( 'dates_get', array( 'calendar' => $id, 'date' => $date ) );
        }
        foreach( $values[ 'dates' ] as $dateId => $date ) {
            $values[ 'dates' ][ $dateId ][ 'title' ] = $this->getI18n( $date[ 'title' ] );
            if( isset( $date[ 'active' ] ) && $date[ 'active' ] == '0' ) {
                $this->linker->html->addMessage(
                    str_replace(
                        '[TITLE]', $values[ 'dates' ][ $dateId ][ 'title' ], $this->getI18n( 'showing_an_inactive_date' )
                    ), false
                );
            }
            $values[ 'dates' ][ $dateId ][ 'content' ] = $this->getI18n( $date[ 'content' ] );
            list($type) = $this->db_execute( 'type_get', array( 'id' => $date[ 'type' ], 'calendar' => $id ) );
            $values[ 'dates' ][ $dateId ][ 'type_name' ] = $this->getI18n( $type[ 'name' ] );
            $values[ 'dates' ][ $dateId ][ 'type_color' ] = $type[ 'color' ];
            if( $this->isAdmin() ) {
                $values[ 'dates' ][ $dateId ][ 'edit' ] = $this->linker->path->getLink( __CLASS__ . '/edit_date/' . $date[ 'calendar' ] ) . '?date=' . $date[ 'id' ];
            }
        }

        $uri = $this->linker->path->uri;
        list($previousDay[ 'year' ], $previousDay[ 'month' ], $previousDay[ 'day' ]) = explode(
            '-', date(
                'Y-m-d', mktime( 0, 0, 0, $month, $day - 1, $year )
            )
        );

        $values[ 'links' ][ 'previousDayLink' ] = $uri . '?' . http_build_query( $previousDay );
        list($nextDay[ 'year' ], $nextDay[ 'month' ], $nextDay[ 'day' ]) = explode(
            '-', date(
                'Y-m-d', mktime( 0, 0, 0, $month, $day + 1, $year )
            )
        );
        $values[ 'links' ][ 'nextDayLink' ] = $uri . '?' . http_build_query( $nextDay );

        if( empty( $values[ 'dates' ] ) ) {
            $this->render( 'nothing_to_show', $values );
        } else {
            $this->render( 'calendar', $values );
        }
    }
    
    public function shallWe_render_calendarbox($attributes = array()){
        $this->isRenderingWEditor = $this->isRenderingWEditor || $this->linker->wEditor->isRendering();
        $rep = !$this->isRenderingWEditor;
        return $rep;
    }

    public function render_calendarbox( $attributes = array( ) ) {
        if( isset( $attributes[ 'id' ] ) ) {
            $id = ( int ) $attributes[ 'id' ];
            $values[ 'calendar' ][ 'id' ] = $id;
        } else {
            return false;
        }
        if( isset( $attributes[ 'filter' ] ) ) {
            $filter = explode( '|', $attributes[ 'filter' ] );
        } else {
            $filter = array( '*' );
        }
        if( isset( $_GET[ 'cal_year' ] ) ) {
            $year = $_GET[ 'cal_year' ];
        } elseif( isset( $attributes[ 'year' ] ) ) {
            $year = $attributes[ 'year' ];
        } else {
            $year = date( 'Y' );
        }
        if( isset( $_GET[ 'cal_month' ] ) ) {
            $month = ( int ) $_GET[ 'cal_month' ];
        } elseif( isset( $attributes[ 'month' ] ) ) {
            $month = ( int ) $attributes[ 'month' ];
        } else {
            $month = ( int ) date( 'm' );
        }
        if( isset( $attributes[ 'weekstart' ] ) ) {
            $weekstart = strtolower( $attributes[ 'weekstart' ] );
        } else {
            $weekstart = $this->getI18n( 'weekstart' );
        }
        if( !is_int( $weekstart ) ) {
            $weekstart = $this->days_to_ids[ $weekstart ];
        }
        $values[ 'calendar' ][ 'month_name' ] = str_replace(
            array( 'MONTH', 'YEAR' ), array( $this->getI18n( 'month_' . $month ), $year ),
                                                             $this->getI18n( 'date_format_ym' )
        );

        $firstDayOfMonth = date( 'w', mktime( 0, 0, 0, $month, 1, $year ) );
        if( $firstDayOfMonth == 0 ) {
            $firstDayOfMonth += 7;
        }
        $numberOfDayInMonth = date( 't', mktime( 0, 0, 0, $month, 1, $year ) );

        $numberOfDaysBeforeMonth = $firstDayOfMonth - 1;

        $numberOfDaysAfterMonth = 7 - ($numberOfDayInMonth + $numberOfDaysBeforeMonth) % 7;
        $linksBase = $this->linker->path->getLink( __CLASS__ . '/show/' . $values[ 'calendar' ][ 'id' ] );

        for( $a = 1; $a <= $numberOfDayInMonth + $numberOfDaysBeforeMonth; $a++ ) {
            if( $a <= $numberOfDaysBeforeMonth ) {
                $values[ 'days' ][ ] = array(
                    'day' => ''
                );
            } else {

                $color = 'transparent';
                $date = $year . '-' . $month . '-' . ($a - $numberOfDaysBeforeMonth);
                if( !$this->isAdmin() ) {
                    $values[ 'dates' ] = $this->db_execute( 'dates_get_active',
                                                            array( 'calendar' => $id, 'date' => $date ) );
                } else {
                    $values[ 'dates' ] = $this->db_execute( 'dates_get', array( 'calendar' => $id, 'date' => $date ) );
                }

                if( !empty( $values[ 'dates' ] ) ) {
                    foreach( $values[ 'dates' ] as $dateId => $date ) {
                        list($type) = $this->db_execute( 'type_get', array( 'id' => $date[ 'type' ], 'calendar' => $id ) );
                        $color = '#' . $type[ 'color' ];
                    }
                }

                $values[ 'days' ][ ] = array(
                    'day' => $a - $numberOfDaysBeforeMonth,
                    'link' => $linksBase . '?day=' . ($a - $numberOfDaysBeforeMonth) . '&month=' . $month . '&year=' . $year,
                    'color' => $color
                );
            }
        }
        $uri = $this->linker->path->uri;
        $_GET[ 'cal_year' ] = date( 'Y', mktime( 0, 0, 0, $month - 1, 1, $year ) );
        $_GET[ 'cal_month' ] = date( 'm', mktime( 0, 0, 0, $month - 1, 1, $year ) );
        $values[ 'links' ][ 'previousMonthLink' ] = $uri . '?' . http_build_query( $_GET );
        $_GET[ 'cal_year' ] = date( 'Y', mktime( 0, 0, 0, $month + 1, 1, $year ) );
        $_GET[ 'cal_month' ] = date( 'm', mktime( 0, 0, 0, $month + 1, 1, $year ) );
        $values[ 'links' ][ 'nextMonthLink' ] = $uri . '?' . http_build_query( $_GET );


        return $this->render( 'calendarBox', $values, false, false );
    }

    public function getPageName( $action, $id, $forUrl = false ) {
        if( $action == 'show' ) {
            return 'Calendrier';
        }
        return false;
    }

    public function __tostring() {
        return get_class();
    }

}

