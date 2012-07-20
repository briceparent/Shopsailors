<?php

/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if( !defined( 'SH_MARKER' ) ) {
    header( 'location: directCallForbidden.php' );
}

/**
 * Class that builds date pickers
 */
class sh_datePicker extends sh_core {

    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    public $minimal = array( 'showMonth' => true );
    static $year = '';
    static $month = '';
    static $day = '';

    const FROM = '(=1900,=1,=1)';
    const TO = '(=2050,=12,=31)';
    const HIDE_TODAY = 1;
    const SHOW_SECONDS = 2;
    const IS_FROM = 4;
    const IS_TO = 8;

    protected $scriptAdded = false;

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            $this->linker->renderer->add_render_tag( 'render_datePicker', __CLASS__, 'render_datePicker' );
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }

        self::$year = date( 'Y' );
        self::$month = date( 'n' );
        self::$day = date( 'j' );
    }

    public function getMonthName( $num ) {
        $num = ( int ) $num;
        return $this->getI18n( 'month_' . $num );
    }

    protected function toDateArray( $date = '(0,0,0)' ) {
        if( $date == 'NOW' ) {
            $date = '(0,0,0)';
        }
        $regExp = '`^\(([-=])?([0-9]+),([-=])?([0-9]+),([-=])?([0-9]+)\)$`';
        if( preg_match( $regExp, $date, $matches ) ) {
            // We use a format like ([year],[month],[day])
            // Year
            if( $matches[ 1 ] == '=' ) {
                $year = $matches[ 2 ];
            } elseif( $matches[ 1 ] == '-' ) {
                $year = self::$year - $matches[ 2 ];
            } else {
                $year = self::$year + $matches[ 2 ];
            }
            // Month
            if( $matches[ 3 ] == '=' ) {
                $month = $matches[ 4 ];
            } elseif( $matches[ 3 ] == '-' ) {
                $month = self::$month;
                for( $a = 0; $a < $matches[ 4 ]; $a++ ) {
                    $month = $this->getPreviousMonth( $year, $month );
                }
            } else {
                $month = self::$month;
                for( $a = 0; $a < $matches[ 4 ]; $a++ ) {
                    $month = $this->getNextMonth( $year, $month );
                }
            }
            // Day
            if( $matches[ 5 ] == '=' ) {
                $day = $matches[ 6 ];
            } elseif( $matches[ 5 ] == '-' ) {
                $day = self::$day;
                for( $a = 0; $a < $matches[ 6 ]; $a++ ) {
                    $day = $this->getPreviousDay( $year, $month, $day );
                }
            } else {
                $day = self::$day;
                for( $a = 0; $a < $matches[ 6 ]; $a++ ) {
                    $day = $this->getNextDay( $year, $month, $day );
                }
            }
            return array( 'year' => $year, 'month' => $month, 'day' => $day );
        }
    }

    public function render_datePicker( $attributes = array( ) ) {
        $lang = $this->linker->i18n->getLang();
        $lang = array_shift( explode( '_', $lang ) );

        if( isset( $attributes[ 'name' ] ) ) {
            $name = $attributes[ 'name' ];
        } elseif( isset( $attributes[ 'id' ] ) ) {
            $name = $attributes[ 'id' ];
        } else {
            return false;
        }

        if( isset( $attributes[ 'id' ] ) ) {
            $id = $attributes[ 'id' ];
        } else {
            $id = 'dp_' . substr( md5( microtime() ), 0, 8 );
        }

        if( isset( $attributes[ 'value' ] ) ) {
            $value = $attributes[ 'value' ];
        } elseif( isset( $attributes[ 'initDate' ] ) ) {
            $value = $attributes[ 'initDate' ];
        } else {
            $value = '';
        }
        if( isset( $attributes[ 'from' ] ) ) {
            $from = $this->toDateArray( $attributes[ 'from' ] );
        } else {
            $from = $this->toDateArray( self::FROM );
        }

        if( isset( $attributes[ 'to' ] ) ) {
            $to = $this->toDateArray( $attributes[ 'to' ] );
        } else {
            $to = $this->toDateArray( self::TO );
        }

        if( isset( $attributes[ 'onchange' ] ) ) {
            $values[ 'datePicker' ][ 'callback' ] = $attributes[ 'onchange' ];
        }

        $_SESSION[ __CLASS__ ][ $id ][ 'from' ] = $from;
        $_SESSION[ __CLASS__ ][ $id ][ 'to' ] = $to;


        if( $value == '' ) {
            $value = date( 'Y-m-d' );
        }

        $values[ 'datePicker' ][ 'id' ] = $id;
        $values[ 'datePicker' ][ 'name' ] = $name;
        $values[ 'datePicker' ][ 'date' ] = $value;

        // We take the initial value
        list($year, $month, $day) = explode( '-', $value );

        $values[ 'datePicker' ][ 'year' ] = $year;
        $values[ 'datePicker' ][ 'month' ] = $month;
        $values[ 'datePicker' ][ 'day' ] = $day;

        $values[ 'from' ][ 'date' ] = $from[ 'year' ] . '-' . str_pad( $from[ 'month' ], 2, '0', STR_PAD_LEFT ) . '-' . str_pad( $from[ 'day' ],
                                                                                                                                 2,
                                                                                                                                 '0',
                                                                                                                                 STR_PAD_LEFT );
        $values[ 'to' ][ 'date' ] = $to[ 'year' ] . '-' . str_pad( $to[ 'month' ], 2, '0', STR_PAD_LEFT ) . '-' . str_pad( $to[ 'day' ],
                                                                                                                           2,
                                                                                                                           '0',
                                                                                                                           STR_PAD_LEFT );

        if( isset( $attributes[ 'type' ] ) && $attributes[ 'type' ] == 'month' ) {
            $ret = $this->render( 'sh_datePicker_month', $values, false, false );
            return $ret;
        }

        if( !$this->scriptAdded ) {
            if( sh_html::$willRender ) {
                $this->linker->html->addScript( '/' . __CLASS__ . '/singles/date-picker-v5/datepicker.packed.js' );
                $this->linker->html->addCSS( '/' . __CLASS__ . '/singles/sh_datePicker.css' );
            } else {
                $values[ 'scripts' ][ ][ 'src' ] = '/' . __CLASS__ . '/singles/date-picker-v5/datepicker.packed.js';
                $values[ 'style' ][ ][ 'href' ] = '/' . __CLASS__ . '/singles/sh_datePicker.css';
            }
            $this->scriptAdded = true;
        }
        $ret = $this->render( 'sh_datePicker', $values, false, false );

        return $ret;
    }

    public function form_verifier_content( $data ) {
        $data = $data[ 'y' ] . '-' . $data[ 'm' ] . '-' . $data[ 'd' ];
        return $data;
    }

    public function toTime( $date ) {
        list($onlyDate, $returnTime) = explode( ' ', $date );
        return $returnTime;
    }

    public function toLocalDateAndTime( $date ) {
        $date = $this->dateAndTimeToLocal( $date, true );
        return $date;
    }

    /**
     * Returns an array containing the date and time for $completeDate in the local format
     * @param str $completeDate If empty (default behaviour), will use the actual date and time.<br />
     * If not, should be a date using this format : "[year]-[month]-[day] [hour]:[minutes]"
     * @return array an array like (for "2010-01-01 12:15" to the french format)<br />
     * array(<br />
     * 'date' => '01/01/2010',<br />
     * 'time' => '12:15'<br />
     * )
     */
    public function dateAndTimeToLocal( $completeDate = '', $asString = false, $settings = 0 ) {
        if( empty( $completeDate ) ) {
            $onlyDate = date( 'Y-m-d' );
            if( $settings & self::SHOW_SECONDS ) {
                $returnTime = date( 'H:i:s' );
            } else {
                $returnTime = date( 'H:i' );
            }
        } else {
            list($onlyDate, $returnTime) = explode( ' ', $completeDate );
            $time = explode( ':', $returnTime );
            $returnTime = $time[ 0 ] . ':' . $time[ 1 ];
            if( $settings & self::SHOW_SECONDS ) {
                if( isset( $time[ 2 ] ) ) {
                    $returnTime = ':' . $time[ 2 ];
                } else {
                    $returnTime = ':00';
                }
            }
        }
        $returnDate = $this->dateToLocal( $onlyDate );
        if( $asString ) {
            if( $settings & self::IS_FROM ) {
                $suf = '_from';
            } elseif( $settings & self::IS_TO ) {
                $suf = '_to';
            }
            if( $onlyDate == date( 'Y-m-d' ) ) {
                if( $settings & self::HIDE_TODAY ) {
                    $model = $this->getI18n( 'date_and_time_format_today_hidden' . $suf );
                } else {
                    $model = $this->getI18n( 'date_and_time_format_today' . $suf );
                }
                return str_replace( array( '{date}', '{time}' ), array( $returnDate, $returnTime ), $model );
            } elseif( $onlyDate == date( 'Y-m-d', mktime( 0, 0, 0, date( m ), date( 'd' ) - 1, date( 'Y' ) ) ) ) {
                $model = $this->getI18n( 'date_and_time_format_yesterday' . $suf );
                return str_replace( array( '{date}', '{time}' ), array( $returnDate, $returnTime ), $model );
            } else {
                $model = $this->getI18n( 'date_and_time_format' . $suf );
                return str_replace( array( '{date}', '{time}' ), array( $returnDate, $returnTime ), $model );
            }
        }
        return array(
            'date' => $returnDate,
            'time' => $returnTime
        );
    }

    public function toLocalDate( $date ) {
        $date = $this->dateToLocal( $date );
        return $date;
    }

    public function dateToLocal( $dateOrYear = null, $month = null, $day = null ) {
        if( is_null( $dateOrYear ) ) {
            $dateOrYear = date( 'Y' );
            $month = date( 'm' );
            $day = date( 'd' );
        } elseif( is_null( $month ) ) {
            // The date has been given only using the first parametter
            list($dateOrYear, $month, $day) = preg_split(
                '`([-_\.,\/ :;])`', $dateOrYear, -1, PREG_SPLIT_NO_EMPTY
            );
        }
        return date(
                $this->getI18n( 'date_format' ), mktime( 0, 0, 0, $month, $day, $dateOrYear )
        );
    }

    /**
     * Verifies if a given date is today
     * @param int $year The year to check
     * @param int $month The month to check
     * @param int $day The day of the month to check
     * @return bool True if yes, false of no
     */
    protected function isToday( $year, $month, $day ) {
        return ($year == self::$year) && ($month == self::$month) && ($day == self::$day);
    }

    protected function isBefore( $date, $comp ) {
        $yearIsBefore = $date[ 'year' ] < $comp[ 'year' ];
        $monthIsBefore = $date[ 'month' ] < $comp[ 'month' ];
        $dayIsBefore = $date[ 'day' ] < $comp[ 'day' ];
        return
            $yearIsBefore
            || ($date[ 'year' ] == $comp[ 'year' ] && $monthIsBefore)
            || ($date[ 'year' ] == $comp[ 'year' ] && $date[ 'month' ] == $comp[ 'month' ] && $dayIsBefore)
        ;
    }

    protected function isAfter( $date, $comp ) {
        return $this->isBefore( $comp, $date );
    }

    /**
     * This method sends the render html for a month, in which the user can pick
     * a date.
     * It is called using Ajax
     * @return str The xml
     */
    public function showMonth() {
        $from = $_SESSION[ __CLASS__ ][ $_GET[ 'picker' ] ][ 'from' ];
        $to = $_SESSION[ __CLASS__ ][ $_GET[ 'picker' ] ][ 'to' ];

        // We get the local date format
        $dateFormat = $this->getI18n( 'date_format' );
        // We get the picker's datas
        $picker = $_GET[ 'picker' ];
        if( isset( $_GET[ 'year' ] ) ) {
            $year = $_GET[ 'year' ];
            $month = $_GET[ 'month' ];
        } elseif( isset( $_GET[ 'monthAndYear' ] ) ) {
            list($year, $month) = explode( '-', $_GET[ 'monthAndYear' ] );
        }
        // We calculate some extra datas
        $firstDay = $this->getFirstDayOfMonth( $year, $month );
        $numberOfDays = $this->getNumberOfDaysInMonth( $year, $month );
        // And we generate the dates
        $values[ 'dates' ] = array( );
        $cpt = 0;

        // If the month doesn't start on a monday, we show the last days of the
        // previous month
        if( $firstDay > 1 ) {
            $newYear = $year;
            $previousMonth = $this->getPreviousMonth( $newYear, $month );
            $prevNumberOfDays = $this->getNumberOfDaysInMonth( $newYear, $previousMonth );

            for( $a = 1; $a < $firstDay; $a++ ) {
                $cpt++;
                if( !$this->isToday( $newYear, $previousMonth, $prevNumberOfDays ) ) {
                    $addToClass = '';
                } else {
                    $addToClass = ' oneDatePicker_today';
                }
                $selectable = !$this->isBefore(
                        array(
                        'year' => $newYear, 'month' => $previousMonth, 'day' => $prevNumberOfDays
                        ), $from
                );
                $values[ 'dates' ][ $firstDay - $cpt ] = array(
                    'day_short' => $prevNumberOfDays,
                    'day' => str_pad( $prevNumberOfDays, 2, '0', STR_PAD_LEFT ),
                    'month' => str_pad( $previousMonth, 2, '0', STR_PAD_LEFT ),
                    'year' => $newYear,
                    'complete' => date(
                        $dateFormat, mktime( 0, 0, 0, $previousMonth, $prevNumberOfDays, $newYear )
                    ),
                    'class' => 'oneDatePicker_otherMonth' . $addToClass,
                    'selectable' => $selectable
                );
                $prevNumberOfDays--;
            }
        }

        // We show the days of the month
        for( $a = 1; $a <= $numberOfDays; $a++ ) {
            $cpt++;
            if( !$this->isToday( $year, $month, $a ) ) {
                $addToClass = '';
            } else {
                $addToClass = ' oneDatePicker_today';
            }
            $selectable = !$this->isBefore(
                    array( 'year' => $year, 'month' => $month, 'day' => $a ), $from
            );
            $selectable = $selectable && !$this->isAfter(
                    array( 'year' => $year, 'month' => $month, 'day' => $a ), $to
            );

            $values[ 'dates' ][ $cpt ] = array(
                'day_short' => $a,
                'day' => str_pad( $a, 2, '0', STR_PAD_LEFT ),
                'month' => str_pad( $month, 2, '0', STR_PAD_LEFT ),
                'year' => $year,
                'complete' => date(
                    $dateFormat, mktime( 0, 0, 0, $month, $a, $year )
                ),
                'class' => $addToClass,
                'selectable' => $selectable
            );
        }

        // If the month doesn't end on a sunday, we showfirst days of the
        // following month
        $a = 1;
        while( ($cpt % 7) > 0 ) {
            $cpt++;
            $newYear = $year;
            $nextMonth = $this->getnextMonth( $newYear, $month );
            if( !$this->isToday( $newYear, $nextMonth, $a ) ) {
                $addToClass = '';
            } else {
                $addToClass = ' oneDatePicker_today';
            }
            $selectable = !$this->isAfter(
                    array( 'year' => $newYear, 'month' => $nextMonth, 'day' => $a ), $to
            );
            $values[ 'dates' ][ $cpt ] = array(
                'day_short' => $a,
                'day' => str_pad( $a, 2, '0', STR_PAD_LEFT ),
                'month' => str_pad( $nextMonth, 2, '0', STR_PAD_LEFT ),
                'year' => $newYear,
                'complete' => date(
                    $dateFormat, mktime( 0, 0, 0, $nextMonth, $a, $newYear )
                ),
                'class' => 'oneDatePicker_otherMonth' . $addToClass,
                'selectable' => $selectable
            );
            $a++;
        }
        // We reorder the values (the first ones may be inverted)
        ksort( $values[ 'dates' ] );

        $values[ 'dp' ][ 'picker' ] = $picker;

        // And we render the month
        $ret = $this->render( 'oneMonth', $values, false, false );
        echo json_encode( array( 'ret' => $ret, 'hasPreviousMonth' => 1, 'previousYear_year' => '2010', 'previousYear_month' => '5' ) );
        return true;
    }

    /**
     * This method returns the number of the month before the given month. It may
     * also give the year of that previous month.
     * @param int $year
     * @param int $month
     * @return int The number of the month (from 1 to 12). To know the year,
     * $year has to be passed by reference.
     */
    protected function getPreviousMonth( &$year, $month ) {
        if( $month == 1 ) {
            $month = 13;
            $year -= 1;
        }
        return $month - 1;
    }

    /**
     * This method returns the number of the day before the given day. It may
     * also give the month and year of that previous day.
     * @param int $year
     * @param int $month
     * @param int $month
     * @return int The number of the day (from 1 to 31). To know the month and
     * year, $month and $year have to be passed by references.
     */
    protected function getPreviousDay( &$year, &$month, $day ) {
        if( $day == 1 ) {
            $month = $this->getPreviousMonth( $year, $month );
            $numberOfDays = $this->getNumberOfDaysInMonth( $year, $month );
            $day = $numberOfDays + 1;
        }
        return $day - 1;
    }

    /**
     * This method returns the number of the day after the given day. It may
     * also give the month and year of that next day.
     * @param int $year
     * @param int $month
     * @param int $month
     * @return int The number of the day (from 1 to 31). To know the month and
     * year, $month and $year have to be passed by references.
     */
    protected function getNextDay( &$year, &$month, $day ) {
        $numberOfDays = $this->getNumberOfDaysInMonth( $year, $month );
        if( $day == $numberOfDays ) {
            $month = $this->getNextMonth( $year, $month );
            $day = 0;
        }
        return $day + 1;
    }

    /**
     * This method returns the number of the month after the given month. It may
     * also give the year of that next month.
     * @param int $year
     * @param int $month
     * @return int The number of the month (from 1 to 12). To know the year,
     * $year has to be passed by reference.
     */
    protected function getNextMonth( &$year, $month ) {
        if( $month == 12 ) {
            $month = 0;
            $year += 1;
        }
        return $month + 1;
    }

    /**
     * This method returns the number of days the given month has.
     * @param int $year The given year
     * @param int $month The given month
     * @return int The number of days
     */
    public function getNumberOfDaysInMonth( $year, $month ) {
        return date( 't', mktime( 0, 0, 0, $month, 1, $year ) );
    }

    /**
     * This method returns the number of the day in the week of the first day
     * of the given month in ISO-8601
     * @param int $year The given year
     * @param int $month The given month
     * @return int The day of the week of the first day of the month (from 1 for
     * monday, to 7 for sunday)
     */
    public function getFirstDayOfMonth( $year, $month ) {
        return date( 'N', mktime( 0, 0, 0, $month, 1, $year ) );
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri( $page ) {
        if( $page == $this->shortClassName . '/showMonth/' ) {
            return '/' . $this->shortClassName . '/showMonth.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage( $uri ) {
        if( $uri == '/' . $this->shortClassName . '/showMonth.php' ) {
            return $this->shortClassName . '/showMonth/';
        }
        return false;
    }

    public function __tostring() {
        return get_class();
    }

}

