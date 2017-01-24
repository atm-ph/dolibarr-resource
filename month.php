<?php

$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';					// to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../../main.inc.php")) $res=@include '../../../main.inc.php';			// to work if your module directory is into a subdir of root htdocs directory

if (! $res) die("Include of main fails");

$langs->load("resource@resource");

$month = GETPOST('month');
$year = GETPOST('year');

$date_start = strtotime($year.'-'.$month.'-01');
$date_end = strtotime(date($year.'-'.$month.'-t', $date_start).' +1 day');
$today = strtotime(date('Y-m-d'));

$nb_jours = date('t', $date_start);

$TMonth = array(
	'01' => 'Janvier'
	,'02' => 'Février'
	,'03' => 'Mars'
	,'04' => 'Avril'
	,'05' => 'Mai'
	,'06' => 'Juin'
	,'07' => 'Juillet'
	,'08' => 'Aôut'
	,'09' => 'Septembre'
	,'10' => 'Octobre'
	,'11' => 'Novembre'
	,'12' => 'Décembre'
);


$conf->dol_hide_topmenu = $conf->dol_hide_topmenu = 1;

llxHeader('', 'Planning '.$month.' '.$year, '', '', 0, 0, array(), array('/resource/css/month.css'));

$width_col = floor( 90 / (int)date('t',$today) * 100) / 100;

?>
<style type="text/css">
#planning {
	width:420mm;
}
#planning td.resource_name {
	width:10%;
}

#planning td.date,#planning td.colDay {
	width:<?php echo $width_col ?>%;
}
</style>
<p id="title_planning">Planning : <?php echo $TMonth[$month].' '.$year; ?></p>
<table id="planning">
	<tr id="row-day">
		<td class="noborder"></td>
	</tr>
</table>


<script type="text/javascript">
	$(function() {
		$.ajax({
			url:"<?php echo dol_buildpath('/resource/core/ajax/resource_action.json.php?action=resource', 2); ?>"
			,dataType:'json'
			,data: {
				action:'resource'
				
			}
		}).done(function(data, textStatus, jqXHR) {
			callEventsAjax(data);
		});
		
		function callEventsAjax(data_resource) {
			$.ajax({
				url:"<?php echo dol_buildpath('/resource/core/ajax/resource_action.json.php?action=events', 2); ?>"
				,dataType:'json'
				,data: {
					action:'events'
					,start:<?php echo $date_start; ?>
					,end:<?php echo $date_end; ?>
				}
			}).done(function(data, textStatus, jqXHR) {
				constructTable(data_resource, data);
			});
		}
		
		function constructTable(data_resource, data_events) {
			var nbCol = <?php echo (int) $nb_jours; ?>
				,table = $('#planning')
				,today = <?php echo $today; ?>
				,year = <?php echo $year; ?>
				,month = <?php echo (int)$month - 1; ?>;
			
			if (data_resource.length <= 0) return;
			
			for(var i in data_resource)
			{
				var tr = $('<tr id="planning_resource_'+data_resource[i].id+'" class="resource"><td class="resource_name">'+data_resource[i].name+'</td></tr>');
				table.append(tr);
			}
			
			for (var i=1; i<=nbCol; i++)
			{
				var current_date = new Date(year, month, i);
				var time = current_date.getTime() / 1000; // timestamp in javascript is in milliseconds
				var day = current_date.getDay();
				var cls = day == 0 || day == 6 ? 'weekend' : '';
				var class_today = '';
				if (time == today) class_today = 'today';
				
				$('#row-day').append('<td class="'+cls+' date colDay">'+i+'/'+(month+1)+'</td>');
				$('#planning tr.resource').append('<td class="'+class_today+' date date_'+time+' date_string_'+year+'_'+(month+1)+'_'+i+'"></td>');
			}
			
			if (data_events.length <= 0) return;
			
			for(var i in data_events)
			{
				var start = data_events[i].start
					,end = data_events[i].end
					,d = new Date(start*1000)
					,e = new Date(end*1000);
				
				var class_start = 'date_string_'+d.getFullYear()+'_'+(d.getMonth()+1)+'_'+d.getDate();
				
				var class_end = '';
				if (e.getMonth()+1 != d.getMonth()+1) { // cas ou la tache se prolonge sur plusieurs jours et qu'elle ce termine le mois suivant
					class_end = 'date_string_'+d.getFullYear()+'_'+(d.getMonth()+1)+'_'+nbCol;
				} else {
					class_end = 'date_string_'+e.getFullYear()+'_'+(e.getMonth()+1)+'_'+e.getDate();
				}
				
				addBlock(class_start, class_end, data_events[i]);
			}
			
			function addBlock(class_start, class_end, event) {
				for (var i in event.resource)
				{
					var fk_resource = event.resource[i]
						,target_start = $('#planning_resource_'+fk_resource+' td.'+class_start)
						,target_end = $('#planning_resource_'+fk_resource+' td.'+class_end)
						,offset_start = target_start.offset()
						,offset_end = target_end.offset()
						,current_td = target_start
						,length_of_event=0;
						
					while (current_td.length > 0)
					{
						length_of_event++;
						incNbEvent(current_td);
						
						if (current_td.hasClass(class_end)) break;
						else current_td = current_td.next();
					}
					
					addEventInCell(event, target_start, length_of_event);
				}
			}
			
			function incNbEvent(td)
			{
				var nb_event = td.data('nb-event');
				if (typeof nb_event == 'undefinied') nb_event = 0;
				td.data('nb-event', ++nb_event);
			}
			
			function addEventInCell(event, td_start, length_of_event)
			{
				var top = 0
					,last_event = td_start.children('.event:last')
					,next_top = td_start.data('next-top');
					
				if (typeof next_top == 'undefined' || next_top == 0) 
				{
					if (last_event.length > 0)
					{
						top = last_event.position().top + last_event.outerHeight();
					}
					
					if (top > 1) top += 1 ; // Gestion du déclage pour éviter que les events ce colle
				}
				else {
					top = next_top + 2;
					td_start.data('next-top', 0);
				}
				
				var div = $('<div class="event" style="top:'+top+'px; background:'+event.backgroundColor+'">'+event.title+' '+event.id+'</div>');	
				div.css('width', td_start.width()*length_of_event); // Prend la largeur nécessaire s'il s'agit d'un event sur plusierus jours
				
				td_start.append(div);
				var height = top+div.outerHeight()+8;
				td_start.css('height', height); // Ajout d'une marge en bas de la cellule
				
				checkNextHeight(td_start, div, length_of_event, div.position().top-1);
			}
			
			function checkNextHeight(td_start, div, length_of_event, last_top)
			{
				if (length_of_event == 1) return;
				
				var i = 1 // Init à 1 car la présence dans la case td_start ne compte pas
					,prev_td = current_td = td_start
					,current_last_top = last_top;
					
				while (current_td = current_td.next())
				{
					i++;
					
					var last_event = current_td.children('.event:last');
					if (last_event.length > 0)
					{
						var ctopHeight = last_event.position().top - 1 + last_event.outerHeight();
						
						if (ctopHeight > current_last_top) current_last_top = ctopHeight;
					} 
					
					prev_td = current_td;
					if (i >= length_of_event) break;
				}
				
				var next_top = 0;
				if (current_last_top > last_top) // Un td suivant occupe plus de place que celui de départ
				{
					next_top = current_last_top+div.outerHeight();
					
					div.css('top', current_last_top+1);
					td_start.css('height', next_top+8);
				} else {
					next_top = div.position().top - 1 + div.outerHeight();
				}
				
				i = 1; // Init à 1 car la présence dans la case td_start ne compte pas
				current_td = td_start;
				while (current_td = current_td.next()) // Toutes les cellules suivantes sont plus courte => je doit leur préciser leur nouveau next-top
				{
					i++;
					current_td.data('next-top', next_top);
					
					if (i >= length_of_event) break;
				}	
				
			}
		}
	});
</script>

<?php

llxFooter();

/*
$res = file_get_contents(dol_buildpath('/resource/core/ajax/resource_action.json.php?action=events&start='.$date_start.'&end='.$date_end, 2));


var_dump($res);

var_dump(
	date('Y-m-d H:i:s', 1464732000)
	,date('Y-m-d H:i:s', 1467324000)
);
*/
