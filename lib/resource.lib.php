<?php
/* Copyright (C) 2008-2013 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2014     Jean-François Ferry   <jfefe@aternatik.fr>
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
 *	\file			resource/lib/resource.lib.php
*	\ingroup		resource
*  \brief			Library for common resource functions
*/



/**
 * Show header
*
* @param 	string	$title		Title of page
* @param 	string	$head		Head string to add int head section
* @return	void
*/
function llxHeaderResource($head, $title, $disablejs, $disablehead, $arrayofjs, $arrayofcss)
{
	global $user, $conf, $langs;

	top_htmlhead($head, $title, $disablejs, $disablehead, $arrayofjs, $arrayofcss);
	print '<body style="margin: 20px;">'."\n";
}
