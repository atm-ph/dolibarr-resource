<?php
/* Copyright (C) 2007-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013       Jean-François Ferry <jfefe@aternatik.fr>
 * Copyright (C) 2014       Raphaël Doursenaud  <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2014       Florian Henry		<florian.henry@open-concept.pro>
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
// Change this following line to use the correct relative path from htdocs
dol_include_once('/resource/class/resource.class.php');
dol_include_once('/resource/class/html.formresource.class.php');

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");

// Get parameters
$id				= GETPOST('id','int');
$action			= GETPOST('action','alpha');

$start			= GETPOST('start','int');
$end			= GETPOST('end','int');
$fk_resource 	= GETPOST('fk_resource','int');


$form = new Form($db);


/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/
$morecss=array("/resource/js/fullcalendar/fullcalendar.css");

$morejs=array("/resource/js/fullcalendar/fullcalendar.js");
llxHeader('','ResourcePlaning','','','','',$morejs,$morecss,0,0);

$form=new Form($db);

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
$dayNames=array(	'"'.$langs->trans('Monday').'"',
		'"'.$langs->trans('Tuesday').'"',
		'"'.$langs->trans('Wednesday').'"',
		'"'.$langs->trans('Thursday').'"',
		'"'.$langs->trans('Friday').'"',
		'"'.$langs->trans('Saturday').'"',
		'"'.$langs->trans('Sunday').'"');
$dayNamesShort=array(	'"'.$langs->trans('MondayMin').'"',
		'"'.$langs->trans('TuesdayMin').'"',
		'"'.$langs->trans('WednesdayMin').'"',
		'"'.$langs->trans('ThursdayMin').'"',
		'"'.$langs->trans('FridayMin').'"',
		'"'.$langs->trans('SaturdayMin').'"',
		'"'.$langs->trans('SundayMin').'"');

print '<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {
	$("#calendar").fullCalendar({
	header: {
		left: \'prev,next today\',
		center: \'title\',
		right: \'month,agendaWeek,agendaDay\'
	},
	monthNames: ['.implode(',',$monthNames).'],
	monthNamesShort: ['.implode(',',$monthNamesShort).'],
	dayNames: ['.implode(',',$dayNames).'],
	dayNamesShort: ['.implode(',',$dayNamesShort).'],
	defaultView: \'agendaWeek\',
	buttonText: {
		today: \''.$langs->trans('Today').'\',
		month: \''.$langs->trans('Month').'\',
		week: \''.$langs->trans('Week').'\',
		day: \''.$langs->trans('Day').'\',
	},
    eventSources: [

        // your event source
        {
            url: "'.dol_buildpath('/resource/core/ajax/resource_action.json.php',1).'",
            type: "POST",
            data: {
                fk_resource: "'.$fk_resource.'"
            },
            error: function() {
                alert("there was an error while fetching events!");
            },
        }

	]

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

$formresource = new FormResource($db);

print $form->select_date($select_start_date, 'select_start_date', 0, 0, 1,'',1,1);
print '<input type="button" value="'.$langs->trans('GotoDate').'" id="gotodate" name="gotodate">';

print '<div id="calendar"></div>';




// End of page
llxFooter();
$db->close();
?>
