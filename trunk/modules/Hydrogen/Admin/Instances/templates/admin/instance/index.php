<?php

$rows	= array();
foreach( $instances as $instance ){
	$url			= 'http://'.getEnv( 'HTTP_HOST' ).'/'.$instance->path;
	$link			= UI_HTML_Elements::Link( './admin/instance/edit/'.$instance->id, $instance->title );
	$url			= $instance->protocol.$instance->host.$instance->path;
	$linkInstance	= UI_HTML_Tag::create( 'a', $url, array( 'href' => $url ) );
	$codeUri		= UI_HTML_Tag::create( 'code', $instance->uri );
	$cells	= array(
		UI_HTML_Tag::create( 'td', $link ),
		UI_HTML_Tag::create( 'td', $linkInstance.'<br/><small>'.$codeUri.'</small>' ),
		UI_HTML_Tag::create( 'td', '' ),
	);
	$hasTodoTool		= isset( $instance->checkTodos ) && $instance->checkTodos ? "yes" : "no";
	$rows[$instance->title]	= '<tr data-check="'.$hasTodoTool.'" data-url="'.$url.'tools/Todos/">'.join( $cells ).'</tr>';
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
		if(!$(this).data("url") || $(this).data("check") == "no")
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
