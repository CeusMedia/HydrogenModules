<?php

$rows	= array();
foreach( $instances as $instance ){
	$url	= 'http://'.getEnv( 'HTTP_HOST' ).'/'.$instance->path;
	$link	= UI_HTML_Elements::Link( './admin/instance/edit/'.$instance->id, $instance->title );
	$cells	= array(
		UI_HTML_Tag::create( 'td', $link ),
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'a', $instance->path, array( 'href' => $url ) ) ),
		UI_HTML_Tag::create( 'td', '' ),
	);
	$rows[$instance->title]	= '<tr data-url="'.$url.'tools/Todos/">'.join( $cells ).'</tr>';
}
ksort( $rows );

$panelList	= '
<fieldset>
	<legend>Instanzen</legend>
	<table>
		<tr><th>Instanz</th><th>Pfad</th><th>Todos</th></tr>
		'.join( $rows ).'
	</table>
	'.UI_HTML_Elements::LinkButton( './admin/instance/add', 'neue Instanz', 'button add' ).'
</fieldset>
';

return '
<script>
function loadTodos(){
	$("tr").each(function(){
		if(!$(this).data("url"))
			return;
		$.ajax({
			url: $(this).data("url") + "?format=json",
			dataType: "json",
			context: this,
			success: function(response){
				if(response !== null && typeof(response) == "object"){			//  
					var link = $("<a></a>").attr("href",$(this).data("url"));	//  
					$(this).find("td").eq(2).html(link.html(response.todos))	//  
				}
			}
		});
	})
}
$(document).ready(function(){
	loadTodos();
});
</script>
<div class="column-left-75">
	'.$panelList.'
</div>
';

?>