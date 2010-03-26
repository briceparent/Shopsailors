<?php
/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')) {header('location: directCallForbidden.php');}

/**
 * Class that builds date pickers
 */
class sh_datePicker extends sh_core {
    public $minimal = array('showMonth'=>true);
    static $year = '';
    static $month = '';
    static $day = '';
    
    const FROM = '(=1900,=1,=1)';
    const TO = '(=2050,=12,=31)';
    
    public function construct() {
        $this->linker->html->addScript('/'.__CLASS__.'/singles/datePicker.js');
        $this->linker->html->addCSS('/templates/global/datePicker.css','DATEPICKER');
        self::$year = date('Y');
        self::$month = date('n');
        self::$day = date('j');
    }
    
    protected function toDateArray($date = '(0,0,0)') {
        if($date=='NOW') {
            $date = '(0,0,0)';
        }
        $regExp = '`^\(([-=])?([0-9]+),([-=])?([0-9]+),([-=])?([0-9]+)\)$`';
        if(preg_match($regExp,$date,$matches)) {
        // We use a format like ([year],[month],[day])
        // Year
            if($matches[1] == '=') {
                $year = $matches[2];
            }elseif($matches[1] == '-') {
                $year = self::$year - $matches[2];
            }else {
                $year = self::$year + $matches[2];
            }
            // Month
            if($matches[3] == '=') {
                $month = $matches[4];
            }elseif($matches[3] == '-') {
                $month = self::$month;
                for($a=0;$a<$matches[4];$a++) {
                    $month = $this->getPreviousMonth(&$year, $month);
                }
            }else {
                $month = self::$month;
                for($a=0;$a<$matches[4];$a++) {
                    $month = $this->getNextMonth(&$year, $month);
                }
            }
            // Day
            if($matches[5] == '=') {
                $day = $matches[6];
            }elseif($matches[5] == '-') {
                $day = self::$day;
                for($a=0;$a<$matches[6];$a++) {
                    $day = $this->getPreviousDay(&$year, &$month, $day);
                }
            }else {
                $day = self::$day;
                for($a=0;$a<$matches[6];$a++) {
                    $day = $this->getNextDay(&$year, &$month, $day);
                }
            }
            return array('year'=>$year,'month'=>$month,'day'=>$day);
        }
    }
    
    public function render_datePicker($attributes = array()) {
        $this->linker->html->addScript('/'.__CLASS__.'/singles/sh_datePicker.js');
        $this->linker->html->addCSS('/'.__CLASS__.'/singles/sh_datePicker.css');
        if(isset($attributes['name'])) {
            $name = $attributes['name'];
        }elseif(isset($attributes['id'])) {
            $name = $attributes['id'];
        }else {
            return false;
        }

        if(isset($attributes['id'])){
            $id=$attributes['id'];
        }else{
            $id='dp_'.substr(md5(microtime()),0,8);
        }
        
        if(isset($attributes['value'])) {
            $value = $attributes['value'];
        }elseif(isset($attributes['initDate'])) {
            $value = $attributes['initDate'];
        }else {
            $value = '';
        }
        if(isset($attributes['from'])) {
            $from = $this->toDateArray($attributes['from']);
        }else {
            $from = $this->toDateArray(self::FROM);
        }
        
        if(isset($attributes['to'])) {
            $to = $this->toDateArray($attributes['to']);
        }else {
            $to = $this->toDateArray(self::TO);
        }

        $_SESSION[__CLASS__][$id]['from'] = $from;
        $_SESSION[__CLASS__][$id]['to'] = $to;
        
        if($value == '') {
            $value=date('Y-m-d');
        }
        
        // We take the initial value
        list($year,$month,$day) = explode('-',$value);
        
        /**
         * To list the monthes and years, we have 2 cases:
         * 1: There is 13 or more consecutive monthes
         * 2: There is less than 13 consecutive monthes
         */
        if($to['year'] - $from['year'] >= 2) {
        // We don't really count, we just have to know that we are in the case #1
            $numberOfMonthes = 13;
        }elseif($from['year'] == $to['year']) {
        // We are in case #2
            $numberOfMonthes = $to['month'] - $from['month'];
        }elseif($to['month'] > $from['month']) {
        // We don't really count, we just have to know that we are in the case #1
            $numberOfMonthes =  13;
        }else {
        // We are in case #2
            $numberOfMonthes = $to['month'] - $from['month'] + 12;
        }
        
        if($numberOfMonthes>12) {
            $values['year']['separatedFromMonth'] = true;
            $values['year']['method'] = 'separated';
            // We will allow the user to select separatly the month and the year
            // Year selector
            for($a = $from['year'];$a<=$to['year'];$a++) {
                if($year != $a) {
                    $values['years'][]['value'] = $a;
                }else {
                    $values['years'][] = array(
                        'value' => $a,
                        'state' => 'selected'
                    );
                    $values['year']['selected'] = $a;
                    $yearSelected = true;
                }
            }
            if(!$yearSelected) {
                $values['years'][0]['state'] = 'selected';
                $year = $from['year'];
                $month = $from['month'];
                $day = $from['day'];
                $values['year']['selected'] = $year;
            }
            
            // Month selector
            $initMonth = 1;
            $lastMonth = 12;
            for($a = $initMonth;$a<=$lastMonth;$a++) {
                if($month != $a) {
                    $values['monthes'][]['id'] = $a;
                }else {
                    $values['monthes'][] = array(
                        'id' => $a,
                        'state' => 'selected'
                    );
                    $values['month']['selected'] = $a;
                    $monthSelected = true;
                }
            }
            if(!$monthSelected) {
                $values['monthes'][0]['state'] = 'selected';
                $month = $from['month'];
                $day = $from['day'];
                $values['month']['selected'] = $month;
            }
        }elseif($numberOfMonthes>0) {
            // The user will have to select the month and year in a single select
            $values['year']['groupedWithMonth'] = true;
            $values['year']['method'] = 'grouped';
            // First month
            $thisYear = $from['year'];
            $thisMonth = $from['month'];
            for($a = 0;$a<=$numberOfMonthes;$a++){
                if($year == $thisYear && $month == $thisMonth){
                    $state = 'selected';
                    $monthSelected = true;
                    $values['monthAndYear'] = array(
                        'month'=>$thisMonth,
                        'year' => $thisYear
                    );
                }else{
                    $state = '';
                }
                $values['monthesAndYears'][] = array(
                    'state' => $state,
                    'month'=>$thisMonth,
                    'year' => $thisYear,
                    'value' => $thisYear.'-'.$thisMonth
                );
                $thisMonth = $this->getNextMonth(&$thisYear, $thisMonth);
            }
            if(!$monthSelected){
                $values['monthesAndYears'][0]['state'] = 'selected';
                $year = $from['year'];
                $day = $from['month'];
                $day = $from['day'];
                $values['monthAndYear'] = array(
                    'month'=>$thisMonth,
                    'year' => $thisYear
                );
            }
        }else {
            // From and To are in the same month, so the user may not select them
            $values['year']['method'] = 'none';
            $values['year']['singleProposal'] = true;
            $values['monthAndYear'] = array(
                'month'=>$month,
                'year' => $year,
                'value' => $year.'-'.$month
            );
            $done = true;
        }

        if(
            $year < $from['year']
            || ($year == $from['year'] && $month < $from['month'])
            || ($year == $from['year'] && $month == $from['month'] && $day < $from['day'])
        ){
            $year = $from['year'];
            $month = $from['month'];
            $day = $from['day'];
        }

        $value = date(
            $this->getI18n('date_format'),
            mktime(0,0,0,$month,$day,$year)
        );
        
        $values['datePicker']['data'] = ' value="' .$value . '" name="'.$name.'_i18ned" id="'.$id.'" class="oneDatePicker_input"';
        if(isset($attributes['onchange'])){
            $values['datePicker']['callBack'] = $attributes['onchange'];
        }
        $values['datePicker']['name'] = $name;
        $values['datePicker']['id'] = $id;
        
        $dateFormat = $this->getI18n('date_format');
        $regExp = $this->getI18n('date_format_toEn_regExp');
        $replace = $this->getI18n('date_format_toEn_Replace');
        $values['datePicker']['value'] = preg_replace($regExp,$replace,$value);
        
        
        $ret = $this->render('sh_datePicker', $values, false, false);
        return $ret;
    }

    public function dateAndTimeToLocal($completeDate){
        list($onlyDate,$returnTime) = explode(' ',$completeDate);
        $returnDate = $this->dateToLocal($onlyDate);
        return array(
            'date' => $returnDate,
            'time' => $returnTime
        );
    }

    public function dateToLocal($dateOrYear,$month = null, $day = null){
        if(is_null($month)){
            // The date has been given only using the first parametter
            list($dateOrYear,$month,$day) = preg_split(
                '`([-_\.,\/ :;])`',
                $dateOrYear,
                -1,
                PREG_SPLIT_NO_EMPTY
            );
        }
        return date(
            $this->getI18n('date_format'),
            mktime(0,0,0,$month,$day,$dateOrYear)
        );
    }
    
    /**
     * Verifies if a given date is today
     * @param int $year The year to check
     * @param int $month The month to check
     * @param int $day The day of the month to check
     * @return bool True if yes, false of no
     */
    protected function isToday($year, $month, $day) {
        return ($year == self::$year) && ($month == self::$month) && ($day == self::$day);
    }

    protected function isBefore($date,$comp){
        $yearIsBefore = $date['year'] < $comp['year'];
        $monthIsBefore = $date['month'] < $comp['month'];
        $dayIsBefore = $date['day'] < $comp['day'];
        return
            $yearIsBefore
            || ($date['year'] == $comp['year'] && $monthIsBefore)
            || ($date['year'] == $comp['year'] && $date['month'] == $comp['month'] && $dayIsBefore)
        ;
    }

    protected function isAfter($date,$comp){
        return $this->isBefore($comp,$date);
    }
    
    /**
     * This method sends the render html for a month, in which the user can pick
     * a date.
     * It is called using Ajax
     * @return str The xml
     */
    public function showMonth() {
        $from = $_SESSION[__CLASS__][$_GET['picker']]['from'];
        $to = $_SESSION[__CLASS__][$_GET['picker']]['to'];
        // We get the local date format
        $dateFormat = $this->getI18n('date_format');
        // We get the picker's datas
        $picker = $_GET['picker'];
        if(isset($_GET['year'])){
            $year = $_GET['year'];
            $month = $_GET['month'];
        }elseif(isset($_GET['monthAndYear'])){
            list($year,$month) = explode('-',$_GET['monthAndYear']);
        }
        // We calculate some extra datas
        $firstDay = $this->getFirstDayOfMonth($year, $month);
        $numberOfDays = $this->getNumberOfDaysInMonth($year, $month);
        // And we generate the dates
        $values['dates'] = array();
        $cpt = 0;
        
        // If the month doesn't start on a monday, we show the last days of the
        // previous month
        if($firstDay>1) {
            $newYear = $year;
            $previousMonth = $this->getPreviousMonth(
                &$newYear,
                $month
            );
            $prevNumberOfDays = $this->getNumberOfDaysInMonth(
                $newYear,
                $previousMonth
            );

            for($a = 1;$a<$firstDay;$a++) {
                $cpt ++;
                if(!$this->isToday($newYear,$previousMonth,$prevNumberOfDays)) {
                    $addToClass = '';
                }else {
                    $addToClass = ' oneDatePicker_today';
                }
                $selectable = !$this->isBefore(
                    array(
                        'year'=>$newYear,'month'=>$previousMonth,'day'=>$prevNumberOfDays
                    ),
                    $from
                );
                $values['dates'][$firstDay - $cpt] = array(
                    'day_short' => $prevNumberOfDays,
                    'day' => str_pad($prevNumberOfDays, 2, '0', STR_PAD_LEFT),
                    'month' => str_pad($previousMonth, 2, '0', STR_PAD_LEFT),
                    'year' => $newYear,
                    'complete' => date(
                        $dateFormat,
                        mktime(0,0,0,$previousMonth,$prevNumberOfDays,$newYear)
                    ),
                    'class' => 'oneDatePicker_otherMonth'.$addToClass,
                    'selectable' => $selectable
                );
                $prevNumberOfDays--;
            }
        }
        
        // We show the days of the month
        for($a = 1;$a<=$numberOfDays;$a++) {
            $cpt++;
            if(!$this->isToday($year,$month,$a)) {
                $addToClass = '';
            }else {
                $addToClass = ' oneDatePicker_today';
            }
            $selectable = !$this->isBefore(
                array('year'=>$year,'month'=>$month,'day'=>$a),$from
            );
            $selectable = $selectable && !$this->isAfter(
                array('year'=>$year,'month'=>$month,'day'=>$a),$to
            );
            
            $values['dates'][$cpt] = array(
                'day_short' => $a,
                'day' => str_pad($a, 2, '0', STR_PAD_LEFT),
                'month' => str_pad($month, 2, '0', STR_PAD_LEFT),
                'year' => $year,
                'complete' => date(
                    $dateFormat,
                    mktime(0,0,0,$month,$a,$year)
                ),
                'class' => $addToClass,
                'selectable' => $selectable
            );
        }
        
        // If the month doesn't end on a sunday, we showfirst days of the
        // following month
        $a=1;
        while(($cpt % 7)>0) {
            $cpt++;
            $newYear = $year;
            $nextMonth = $this->getnextMonth(
                &$newYear,
                $month
            );
            if(!$this->isToday($newYear,$nextMonth,$a)) {
                $addToClass = '';
            }else {
                $addToClass = ' oneDatePicker_today';
            }
            $selectable = !$this->isAfter(
                array('year'=>$newYear,'month'=>$nextMonth,'day'=>$a),$to
            );
            $values['dates'][$cpt] = array(
                'day_short' => $a,
                'day' => str_pad($a, 2, '0', STR_PAD_LEFT),
                'month' => str_pad($nextMonth, 2, '0', STR_PAD_LEFT),
                'year' => $newYear,
                'complete' => date(
                    $dateFormat,
                    mktime(0,0,0,$nextMonth,$a,$newYear)
                ),
                'class' => 'oneDatePicker_otherMonth'.$addToClass,
                'selectable' => $selectable
            );
            $a++;
        }
        // We reorder the values (the first ones may be inverted)
        ksort($values['dates']);
        
        $values['dp']['picker'] = $picker;
        
        // And we render the month
        echo $this->render('oneMonth', $values, false, false);
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
    protected function getPreviousMonth($year,$month) {
        if($month == 1) {
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
    protected function getPreviousDay($year,$month,$day) {
        if($day == 1) {
            $month = $this->getPreviousMonth(&$year, $month);
            $numberOfDays = $this->getNumberOfDaysInMonth($year, $month);
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
    protected function getNextDay($year,$month,$day) {
        $numberOfDays = $this->getNumberOfDaysInMonth($year, $month);
        if($day == $numberOfDays) {
            $month = $this->getNextMonth(&$year, $month);
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
    protected function getNextMonth($year,$month) {
        if($month == 12) {
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
    public function getNumberOfDaysInMonth($year,$month) {
        return date('t',mktime(0,0,0,$month,1,$year));
    }
    
    /**
     * This method returns the number of the day in the week of the first day
     * of the given month in ISO-8601
     * @param int $year The given year
     * @param int $month The given month
     * @return int The day of the week of the first day of the month (from 1 for
     * monday, to 7 for sunday)
     */
    public function getFirstDayOfMonth($year,$month) {
        return date('N',mktime(0,0,0,$month,1,$year));
    }
    
    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page) {
        if($page == $this->shortClassName.'/showMonth/') {
            return '/'.$this->shortClassName.'/showMonth.php';
        }
        return false;
    }
    
    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri) {
        if($uri == '/'.$this->shortClassName.'/showMonth.php') {
            return $this->shortClassName.'/showMonth/';
        }
        return false;
    }
    
    public function __tostring() {
        return get_class();
    }
}



