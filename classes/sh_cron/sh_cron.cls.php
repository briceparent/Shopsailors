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
 * Class that renders the css files, replacing the colors by those that should
 * be used in the variation.
 */
class sh_cron extends sh_core {

    const CLASS_VERSION = '1.1.11.03.29';

    public $callWithoutId = array( );
    public $callWithId = array(
        'job'
    );
    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    protected $minimal = array( 'job' => true );
    protected $allowedActionsWhenNotOpen = array(
        'job'
    );
    /**
     * Constant value is 0
     * Job that is launched every five minutes, that replaces all the previous ones.
     * It tests the time, and launches automatically the jobs form 1 to 9
     */
    const JOB_ALL = 0;
    /**
     * Constant value is 1<br />
     * Minimal job id
     */
    const JOB_FROM = 1;
    /**
     * Constant value is 1<br />
     * Job launched once a year
     */
    const JOB_YEAR = 1;
    /**
     * Constant value is 2<br />
     * Job launched twice a year
     */
    const JOB_HALFYEAR = 2;
    /**
     * Constant value is 3<br />
     * Job launched four times a year
     */
    const JOB_QUARTERYEAR = 3;
    /**
     * Constant value is 4<br />
     * Job launched every month
     */
    const JOB_MONTH = 4;
    /**
     * Constant value is 5<br />
     * Job launched every week
     */
    const JOB_WEEK = 5;
    /**
     * Constant value is 6<br />
     * Job launched every day
     */
    const JOB_DAY = 6;
    /**
     * Constant value is 7<br />
     * Job launched twice a day
     */
    const JOB_HALFDAY = 7;
    /**
     * Constant value is 8<br />
     * Job launched every hour
     */
    const JOB_HOUR = 8;
    /**
     * Constant value is 9<br />
     * Job launched twice an hour
     */
    const JOB_HALFHOUR = 9;
    /**
     * Constant value is 10<br />
     * Job launched evry quarter hour
     */
    const JOB_QUARTERHOUR = 10;
    /**
     * Constant value is 10<br />
     * Maximum job id
     */
    const JOB_TO = 10;

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
    }

    /**
     * public function get
     *
     */
    public function job() {
        sh_cache::disable();
        $log = '';
        $allowed = false;
        list($part1, $part2, $part3, $part4) = explode( '.', $_SERVER[ 'REMOTE_ADDR' ] );
        foreach( $this->getParam( 'launchers', array( ) ) as $launcher ) {
            list($launcherPart1, $launcherPart2, $launcherPart3, $launcherPart4) = explode( '.', $_SERVER[ 'REMOTE_ADDR' ] );
            if( $launcherPart1 == '*' ) {
                $allowed = true;
                break;
            } elseif( $launcherPart1 == $part1 ) {
                if( $launcherPart2 == '*' ) {
                    $allowed = true;
                    break;
                } elseif( $launcherPart2 == $part2 ) {
                    if( $launcherPart3 == '*' ) {
                        $allowed = true;
                        break;
                    } elseif( $launcherPart3 == $part3 ) {
                        if( $launcherPart4 == '*' || $launcherPart2 == $part2 ) {
                            $allowed = true;
                            break;
                        }
                    }
                }
            }
        }

        if( $allowed ) {
            $id = ( int ) $this->linker->path->page[ 'id' ];
            if( $id == 0 ) {
                // We ask not to launch new actions after 4 minutes, in order
                // not to have 2 jobs running at the same time
                $stopAt = microtime( true ) + 4 * 60;
                $ret = true;
                $classes = $this->get_shared_methods();

                $lastLaunchedJobs = $this->getParam( 'lastLaunchedJobs', array( ) );

                list($now, $y, $m, $d, $h, $i, $s) = explode( '-', date( 'U-Y-m-d-H-i-s' ) );

                $datesFor = array(
                    self::JOB_YEAR => date( 'U', mktime( $h, $i, $s, $m, $d, $y - 1 ) ),
                    self::JOB_HALFYEAR => date( 'U', mktime( $h, $i, $s, $m - 6, $d, $y ) ),
                    self::JOB_QUARTERYEAR => date( 'U', mktime( $h, $i, $s, $m - 3, $d, $y ) ),
                    self::JOB_MONTH => date( 'U', mktime( $h, $i, $s, $m - 1, $d, $y ) ),
                    self::JOB_WEEK => date( 'U', mktime( $h, $i, $s, $m, $d - 7, $y ) ),
                    self::JOB_DAY => date( 'U', mktime( $h, $i, $s, $m, $d - 1, $y ) ),
                    self::JOB_HALFDAY => date( 'U', mktime( $h - 12, $i, $s, $m, $d, $y ) ),
                    self::JOB_HOUR => date( 'U', mktime( $h - 1, $i, $s, $m, $d, $y ) ),
                    self::JOB_HALFHOUR => date( 'U', mktime( $h, $i - 30, $s, $m, $d, $y ) ),
                    self::JOB_QUARTERHOUR => date( 'U', mktime( $h, $i - 15, $s, $m, $d, $y ) )
                );
                $method = 'cron_job';
                for( $job = self::JOB_FROM; $job <= self::JOB_TO; $job++ ) {
                    $lastDate = $lastLaunchedJobs[ $job ];
                    if( empty( $lastDate ) || $datesFor[ $job ] > $lastDate ) {
                        $log .= 'Launching a job #' . $job . ' (last : ' . $lastDate . ')' . "\n";
                        foreach( $classes as $class ) {
                            if( microtime( true ) > $stopAt ) {
                                $log .= 'Cron stopped because of its durations. Will be started again later' . "\n";
                                $ret = false;
                                break;
                            }
                            $log .= 'Cron (' . $job . ') on ' . $class . "\n";
                            $tempRet = $this->linker->$class->$method( $job, $stopAt ) && ($ret !== false);
                            if( !empty( $tempRet ) ) {
                                $ret = $ret && $tempRet;
                            }
                        }
                        if( $ret !== false ) {
                            $this->setParam( 'lastLaunchedJobs>' . $job, date( 'U' ) );
                            $this->writeParams();
                        }
                    } else {
                        $log .= 'No need for a cron job #' . $job . '. Last one was on ' . date( 'Y-m-d \a\t H:i:s',
                                                                                                 $lastDate ) . "\n";
                    }
                }
            } else {
                $ret = true;
                $classes = $this->get_shared_methods();
                foreach( $classes as $class ) {
                    $method = 'cron_job';
                    $log .= 'Cron (' . $id . ') on ' . $class . "\n";
                    $ret = $this->linker->$class->$method( $id ) && $ret;
                }
            }
            $this->helper->writeInFile(
                SH_TEMP_FOLDER . __CLASS__ . '/' . SH_SITENAME . '_last.log',
                'Called cron job is ' . $id . ' - ' . date( 'H:i:s' ) . "\n" . $log
            );

            echo 'OK';
            return $ret;
        }
        echo 'YOU ARE NOT ALLOWED TO LAUNCH CRON JOBS FROM YOUR IP (' . $_SERVER[ 'REMOTE_ADDR' ] . ')!' . "\n";
        return false;
    }

    public function __tostring() {
        return get_class();
    }

}
