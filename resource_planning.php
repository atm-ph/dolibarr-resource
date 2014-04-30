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

//FIXME: missing rights enforcement

/***************************************************
* VIEW
****************************************************/
$morecss=array(
	"/resource/js/fullcalendar/fullcalendar.css",
	"/resource/js/jquery.qtip.css"
);

$morejs=array(
	"/resource/js/fullcalendar/fullcalendar.min.js",
	"/resource/js/jquery.qtip.min.js"
);

$title = $langs->trans('ResourcePlaning');

$fullcalendar = '<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {
	$("#calendar").fullCalendar({
		header: {
			left: "prev,next today",
			center: "title",
			right: "resourceDay,resourceWeek,resourceNextWeeks,resourceMonth"
		},
		defaultView: "resourceWeek",
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
		}
	});
});
</script>';

llxHeader($fullcalendar, $title, '', '', 0, 0, $morejs, $morecss);

print '<div id="calendar"></div>';

// Page end
llxFooter();
$db->close();
?>
