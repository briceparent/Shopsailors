<?php
/**
 * Active <RENDER_MODIFYVALUE parametters :
 * The argument "new", which is optionnal, allows you to keep the unmodified version of the element, and have
 * a copy of with which is modified, under another name
 * <RENDER_MODIFYVALUE what="foo:bar" class="datePicker" method="toLocalDateAndTime" new="foo:bar2"/>
 * → {foo:bar2} will contain the modified version of {foo:bar}, while {foo:bar} remains the same.
 * <RENDER_MODIFYVALUE what="foo:bar" class="datePicker" method="toLocalDateAndTime"/>
 * → {foo:bar} will be changed to the new version.
 * 
 * <RENDER_MODIFYVALUE what="foo:bar" class="datePicker" method="toLocalDateAndTime"/>
 * → modifies the date {foo:bar} to the user's locale, with date and time (like "Le 01/01/2012 à 17:30:25" for french)
 * 
 * <RENDER_MODIFYVALUE what="foo:bar" class="datePicker" method="toLocalDate"/>
 * → modifies the date {foo:bar} to the user's locale, with only date (like "01/01/2012" for french)
 * 
 * <RENDER_MODIFYVALUE what="foo:bar" class="datePicker" method="toTime"/>
 * → on keeps the time from the date {foo:bar} (like "14:25:00")
 * 
 * <RENDER_MODIFYVALUE what="foo:bar" method="toUpperCase"/>
 * → sets the string {foo:bar} to upper case
 * 
 * <RENDER_MODIFYVALUE what="foo:bar" method="toLowerCase"/>
 * → sets the string {foo:bar} to lower case
 * 
 * <RENDER_MODIFYVALUE what="foo:bar" method="toUpperCase_firstLetter"/>
 * → sets the first letter of the string {foo:bar} to upper case, changing nothing else.
 * 
 * <RENDER_MODIFYVALUE what="foo:bar" method="toUpperCase_firstLetters"/>
 * → sets the first letter of the every word of the string {foo:bar} to upper case, changing nothing else.
 * 
 * <RENDER_MODIFYVALUE what="foo:bar" method="removeTags"/>
 * → removes all the html tags from the {foo:bar} string.
 * 
 * <RENDER_MODIFYVALUE what="foo:bar" method="cut|56" new="cut:beginning"/>
 * → removes every chars that is after the 56th of the {foo:bar} string.
 * 
 * <RENDER_MODIFYVALUE what="foo:bar" method="cut|56|word" new="cut:beginning"/>
 * → removes every chars that is after last word that is within the 56th first of the {foo:bar} string.
 * 
 * <RENDER_MODIFYVALUE what="foo:bar" method="cut|56|word|ellipsis" new="cut:beginning"/>
 * → removes every chars that is after last word that is within the 53rd first of the {foo:bar} string, 
 * and adds "..." at the end if the string was cut.
 * 
 */