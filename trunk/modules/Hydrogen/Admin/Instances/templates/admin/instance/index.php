<?php

$rows	= array();
foreach( $instances as $instance ){
	$url	= '';
	$todos	= NULL;
	try{
		$reader	= new Net_Reader( $url.'?format=json' );
		$serial	= $reader->read();
		if( substr( $serial, 0, 1 ) == '{' && $data = @json_decode( $serial ) )
			$todos	= $data->todos;
	}
	catch( Exception $e ){}
	
	$link	= UI_HTML_Elements::Link( './admin/instance/edit/'.$instance->id, $instance->title );
	$todos	= is_int( $todos ) ? UI_HTML_Tag::create( 'a', $todos, array( 'href' => $url ) ) : '';
	$rows[$instance->title]	= '<tr data-url="http://'.getEnv( 'HTTP_HOST' ).'/'.$instance->path.'tools/Todos/"><td>'.$link.'</td><td>'.$instance->path.'</td><td>'.$todos.'</td></tr>';
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