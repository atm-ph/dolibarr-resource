<?php
/* Copyright (C) 2007-2010  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2013       Jean-François Ferry <jfefe@aternatik.fr>
 * Copyright (C) 2014       Raphaël Doursenaud  <rdoursenaud@gpcsolutions.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       resource/resource_planning
 *  \ingroup    resource
 *  \brief      Resource planning view
 */

$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';					// to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../../main.inc.php")) $res=@include '../../../main.inc.php';			// to work if your module directory is into a subdir of root htdocs directory

if (! $res) die("Include of main fails");

// Translations
$langs->load("companies");
$langs->load("other");
$langs->load("resource@resource");

$select_start_date=dol_mktime(0,0,0,GETPOST('datemonth','int'),GETPOST('dateday','int'),GETPOST('dateyear','int'));
$nomenu = GETPOST('nomenu','int');

$form = new Form($db);

//FIXME: missing rights enforcement

/***************************************************
* VIEW
****************************************************/
$morecss=array(
	"/resource/js/fullcalendar/fullcalendar.css",
	"/resource/js/jquery.qtip.css"
	,"/resource/css/resource.css"
);

if (GETPOST('optioncss') == 'print') $morecss[] = '/resource/css/resource.print.css';

$morejs=array(
	"/resource/js/fullcalendar/fullcalendar.js",
	"/resource/js/jquery.qtip.min.js"
);

$title = $langs->trans('ResourcePlaning');

$monthNames=array(	'"'.$langs->trans('Month01').'"',
				  	'"'.$langs->trans('Month02').'"',
				 	'"'.$langs->trans('Month03').'"',
					'"'.$langs->trans('Month04').'"',
					'"'.$langs->trans('Month05').'"',
					'"'.$langs->trans('Month06').'"',
					'"'.$langs->trans('Month07').'"',
					'"'.$langs->trans('Month08').'"',
					'"'.$langs->trans('Month09').'"',
					'"'.$langs->trans('Month10').'"',
					'"'.$langs->trans('Month11').'"',
					'"'.$langs->trans('Month12').'"');
$monthNamesShort=array(	'"'.$langs->trans('MonthShort01').'"',
		'"'.$langs->trans('MonthShort02').'"',
		'"'.$langs->trans('MonthShort03').'"',
		'"'.$langs->trans('MonthShort04').'"',
		'"'.$langs->trans('MonthShort05').'"',
		'"'.$langs->trans('MonthShort06').'"',
		'"'.$langs->trans('MonthShort07').'"',
		'"'.$langs->trans('MonthShort08').'"',
		'"'.$langs->trans('MonthShort09').'"',
		'"'.$langs->trans('MonthShort10').'"',
		'"'.$langs->trans('MonthShort11').'"',
		'"'.$langs->trans('MonthShort12').'"');
$dayNames=array('"'.$langs->trans('Sunday').'"',
		'"'.$langs->trans('Monday').'"',
		'"'.$langs->trans('Tuesday').'"',
		'"'.$langs->trans('Wednesday').'"',
		'"'.$langs->trans('Thursday').'"',
		'"'.$langs->trans('Friday').'"',
		'"'.$langs->trans('Saturday').'"',
		);
$dayNamesShort=array('"'.$langs->trans('SundayMin').'"',
		'"'.$langs->trans('MondayMin').'"',
		'"'.$langs->trans('TuesdayMin').'"',
		'"'.$langs->trans('WednesdayMin').'"',
		'"'.$langs->trans('ThursdayMin').'"',
		'"'.$langs->trans('FridayMin').'"',
		'"'.$langs->trans('SaturdayMin').'"',
		);

$fullcalendar = '<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {
	var alreadyDone = false;
	$("#calendar").fullCalendar({
		header: {
			left: "prev,next today",
			center: "title",
			right: "resourceDay,resourceWeek,resourceNextWeeks,resourceMonth"
		},
		monthNames: ['.implode(',',$monthNames).'],
		monthNamesShort: ['.implode(',',$monthNamesShort).'],
		dayNames: ['.implode(',',$dayNames).'],
		dayNamesShort: ['.implode(',',$dayNamesShort).'],
		buttonText: {
		largePrev: "<span class=\'fc-text-arrow\'>&laquo;</span>",
		largeNext: "<span class=\'fc-text-arrow\'>&raquo;</span>",
		resourceDay: \''.$langs->trans('ByDay').'\',
		resourceWeek: \''.$langs->trans('ByWeek').'\',
		resourceNextWeeks: \''.$langs->trans('ByNextWeek').'\',
		resourceMonth: \''.$langs->trans('ByMonth').'\',
		today: \''.$langs->trans('Today').'\',
		},
		defaultView: "resourceMonth",
		titleFormat: {
		month: \'MMMM yyyy\',
		week: "d[ yyyy]{ \'&#8212;\'d [ MMM] yyyy} M\MM",
		day: \'dddd, d MMM, yyyy\'
		},
		columnFormat: {
			month: \'ddd\',
			week: \'ddd M/d\',
			day: \'dddd M/d\',
			resourceDay: \'H:mm\',
			resourceMonth: \'d/M\',
			resourceWeek: \'ddd d/M\',
			resourceNextWeeks: \'ddd d/M\'
		},
		maxTime: 23.9, // Work around a display bug on the resourceDay view, see https://github.com/jarnokurlin/fullcalendar/issues/15
		resources: "' . dol_buildpath('/resource/core/ajax/resource_action.json.php?action=resource', 1) . '",
		events: "' . dol_buildpath('/resource/core/ajax/resource_action.json.php?action=events', 1) . '",
		eventRender: function(event, element) {
			element.qtip({
				content: {
					title: event.title + " (" + event.action_code + ")" ,
					text: event.description
				},
				position: {
					at: "bottomLeft"
				}
			});
		},
		eventAfterAllRender: function(view) {
			if (!alreadyDone) {
				alreadyDone = true;
				// FIX unaligned box => @see http://stackoverflow.com/questions/21519947/jquery-fullcalendar-can-i-programmatically-resize-the-calendar
				$("#calendar").fullCalendar("option", "aspectRatio", 1.35);
			}
		}
	});
				
	// Click Function
	$(":button[name=gotodate]").click(function() {
		day=$("#select_start_dateday").val();
		month=$("#select_start_datemonth").val()-1;
		year=$("#select_start_dateyear").val();
		datewished= new Date(year, month, day);
		$("#calendar").fullCalendar( \'gotoDate\', datewished );
	});
});
</script>';

if (!empty($nomenu)) {
	$conf->dol_hide_topmenu=1;
	$conf->dol_hide_leftmenu=1;
}

llxHeader($fullcalendar, $title, '', '', 0, 0, $morejs, $morecss);

if (!empty($select_start_date)) {
	print '<script type="text/javascript" language="javascript">
			jQuery(document).ready(function() {
					$(":button[name=gotodate]").click();		
			});
			</script>';
}

if (!empty($nomenu)) {
	print '<style>;
	div.tmenudiv {display:none;}
	div#tmenu_tooltip {display:none;}
	div.login_block {display:none;}
	div.blockvmenuimpair {display:none;}
	div.blockvmenupair {display:none;}
	div.blockvmenusearch {display:none;}
	div.blockvmenuhelp {display:none;}
	/*div.vmenu {display:none;}*/
	</style>';
}

print $form->select_date($select_start_date, 'select_start_date', 0, 0, 1,'',1,1);
print '<input type="button" value="'.$langs->trans('GotoDate').'" id="gotodate" name="gotodate">';

print '<div id="calendar"></div>';

// Page end
llxFooter();
$db->close();
?>
