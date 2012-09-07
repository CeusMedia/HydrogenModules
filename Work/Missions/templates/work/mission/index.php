<?php
$w	= (object) $words['index'];
$panelFilter	= $view->loadTemplateFile( 'work/mission/index.filter.php' );

$panelAdd	= '<div>
	'.UI_HTML_Elements::LinkButton( './work/mission/add?type=0', 'Aufgabe', 'button add task-add' ).'
	'.UI_HTML_Elements::LinkButton( './work/mission/add?type=1', 'Termin', 'button add event-add' ).'
</div><br/>';

$panelExport	= '';
if( 0 && $filterStates != array( 4 ) ){
	$panelExport	= '<fieldset>
		<legend class="icon export">Export / Import</legend>
		<b>Export als:</b>&nbsp;<br/>
		'.UI_HTML_Elements::LinkButton( './work/mission/export/ical', 'ICS', 'button icon export ical' ).'
		'.UI_HTML_Elements::LinkButton( './work/mission/export', 'Archiv', 'button icon export archive' ).'
		<hr/>
		<form action="./work/mission/import" method="post" enctype="multipart/form-data">
			<b>Import aus:</b>&nbsp;
			<input type="text" name="import" id="input-import" class="m" readonly="readonly"/>
			<input type="file" name="serial" id="input-serial" accept="application/gzip"/>
		</form>
	</fieldset>';
}

if( count( $missions ) )
	$panelList	= $view->loadTemplateFile( 'work/mission/index.days.php' );
else
	$panelList	= $view->loadContentFile( 'html/work/mission/index.empty.html' );

$content	= '
<script>
function makeTableSortable(jq,options){
	var options = $.extend({order: null, direction: "ASC"},options);
	$("body").data("tablesort-options",options);
	jq.find("tr th div.sortable").each(function(){
		if($(this).data("column")){
			$(this).removeClass("sortable").parent().addClass("sortable");
			if($(this).data("column") == options.order){
				$(this).parent().addClass("ordered");
				$(this).parent().addClass("direction-"+options.direction.toLowerCase());
			}
			$(this).bind("click",function(){
				var head = $(this);
				var options = $("body").data("tablesort-options");
				var column = head.data("column");
				var direction = options.direction;
				if( options.order == column )
					direction = direction == "ASC" ? "DESC" : "ASC";
				var url = "./work/mission/filter/?order="+column+"&direction="+direction;
				document.location.href = url;
			});
		}
	});
}


$(document).ready(function(){
	makeTableSortable($("#layout-content table"),{
		url: "./work/mission/filter/",
		order: "'.$filterOrder.'",
		direction: "'.$filterDirection.'",
	});
});
</script>
<div class="column-left-20" style="float: left; width: 200px">
	'.$panelAdd.'
	'.$panelFilter.'
	'.$panelExport.'
</div>
<div style="margin-left: 220px">
	<div id="mission-folders" style="position: relative; width: 100%">
		'.$panelList.'
	</div>
</div>
<div class="column-clear"></div>';

return $content;
?>
