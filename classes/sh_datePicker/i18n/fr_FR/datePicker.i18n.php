<?php
$i18n = array(
    'choose' => 'Choisir',
    'month_1' => 'Janvier',
    'month_2' => 'Février',
    'month_3' => 'Mars',
    'month_4' => 'Avril',
    'month_5' => 'Mai',
    'month_6' => 'Juin',
    'month_7' => 'Juillet',
    'month_8' => 'Août',
    'month_9' => 'Septembre',
    'month_10' => 'Octobre',
    'month_11' => 'Novembre',
    'month_12' => 'Décembre',

    'day_1' => 'L',
    'day_2' => 'M',
    'day_3' => 'M',
    'day_4' => 'J',
    'day_5' => 'V',
    'day_6' => 'S',
    'day_7' => 'D',

    'time_format' => 'H:i:s',
    'date_format' => 'd/m/Y',
    'date_format_toEn_regExp' => '`^([0-3]?[0-9])([-\/ ])([01]?[0-9])\2(([12][0-9])?[0-9]{2})$`',
    'date_format_toEn_Replace' => '$4-$3-$1',
    'date_and_time_format' => 'Le {date} à {time}',
    'date_and_time_format_from' => 'Du {date} à {time}',
    'date_and_time_format_to' => 'Au {date} à {time}',
    'date_and_time_format_today_hidden' => strtoupper('à').' {time}',
    'date_and_time_format_today_hidden_from' => 'De {time}',
    'date_and_time_format_today_hidden_to' => strtoupper('à').' {time}',
    'date_and_time_format_today' => 'Aujourd\'hui à {time}',
    'date_and_time_format_today_from' => 'De aujourd\'hui à {time}',
    'date_and_time_format_today_to' => strtoupper('à').' aujourd\'hui à {time}',
    'date_and_time_format_yesterday' => 'Hier à {time}',
    'date_and_time_format_yesterday_from' => 'Depuis hier à {time}',
    'date_and_time_format_yesterday_to' => 'Jusqu\'à hier à {time}',
    
    'prviousYear' => 'Année précédente',
    'previousMonth' => 'Mois précedent',
    'nextMonth' => 'Mois suivant',
    'nextYear' => 'Année suivante',
    
    'dp_form' => '
        <table class="one_datePicker">
                <tr>
                    <td>
                        <input type="text" style="width:2em;" id="{datePicker:id}_d" name="{datePicker:name}[d]" value="{datePicker:day}" maxlength="2" placeholder="JJ"/>/
                    </td>
                    <td>
                        <input type="text" style="width:2em;" id="{datePicker:id}_m" name="{datePicker:name}[m]" value="{datePicker:month}" maxlength="2" placeholder="MM" />/
                    </td>
                    <td style="width:8em;">
                        <input type="text" style="width:4em;" id="{datePicker:id}_y" name="{datePicker:name}[y]" value="{datePicker:year}" maxlength="4" placeholder="AAAA" />
                    </td>
                </tr>
        </table>
',
    'dp_month_form' => '
        <table class="one_datePicker_month">
                <tr>
                    <td>
                        <input type="hidden" id="{datePicker:id}_d" name="{datePicker:name}[d]" value="01"/>
                        <input type="text" style="width:2em;" id="{datePicker:id}_m" name="{datePicker:name}[m]" value="{datePicker:month}" maxlength="2" placeholder="MM" />/
                    </td>
                    <td style="width:8em;">
                        <input type="text" style="width:4em;" id="{datePicker:id}_y" name="{datePicker:name}[y]" value="{datePicker:year}" maxlength="4" placeholder="AAAA" />
                    </td>
                </tr>
        </table>
',

);
