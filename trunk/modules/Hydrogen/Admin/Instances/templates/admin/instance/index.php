<?php
//	@todo		extract labels to locale
$labelsStatusHttp	= array(
	'unchecked'		=> 'Nicht geprüft',
	'online'		=> 'Erreichbar',
	'offline'		=> 'NICHT erreichbar'
);

$rows	= array();
foreach( $instances as $instance ){
	$url			= 'http://'.getEnv( 'HTTP_HOST' ).'/'.$instance->path;
	$link			= UI_HTML_Elements::Link( './admin/instance/edit/'.$instance->id, $instance->title );
	$url			= $instance->protocol.$instance->host.$instance->path;
	$uriExists		= file_exists( $instance->uri );
	$linkInstance	= UI_HTML_Tag::create( 'a', $url, array( 'href' => $url ) );
	$codeUri		= UI_HTML_Tag::create( 'code', $instance->uri );
	$titleStatus	= $uriExists ? "Checked and found on file system" : "NOT FOUND on file system (not installed or path invalid)";
	$titleStatus	= $uriExists ? "Order auf dem Server gefunden" : "Ordner NICHT GEFUNDEN (nicht installiert oder ungültiger Pfad)";
	$indicators		= join( "", array(
		'<div class="status-file status-box status-box-'.( $uriExists ? 'half' : 'no' ).'" title="'.$titleStatus.'"></div>',
		'<div class="status-http status-box" title="'.$labelsStatusHttp['unchecked'].'"></div>',
	) );
	$cells	= array(
		UI_HTML_Tag::create( 'td', $link ),
		UI_HTML_Tag::create( 'td', $linkInstance.'<br/><small>'.$codeUri.'</small>' ),
		UI_HTML_Tag::create( 'td', $indicators, array( 'class' => 'status-http' ) ),
		UI_HTML_Tag::create( 'td', "", array( 'class' => 'status-todos' ) ),
	);
	$hasTodoTool	= isset( $instance->checkTodos ) && $instance->checkTodos ? "yes" : "no";
	$attributes		= array(
		'class'				=> $uriExists ? 'notice' : 'error',
		'data-check'		=> $hasTodoTool,
		'data-url'			=> $url,
		'data-url-todos'	=> $url.'tools/Todos/',
	);
	$rows[$instance->title]	= UI_HTML_Tag::create( 'tr', $cells, $attributes );
}
ksort( $rows );

$panelList	= '
<fieldset>
	<legend>Instanzen</legend>
	<table>
		<tr><th>Instanz</th><th>Pfad</th><th>Online</th><th>Todos</th></tr>
		'.join( $rows ).'
	</table>
	'.UI_HTML_Elements::LinkButton( './admin/instance/add', 'neue Instanz', 'button add' ).'
</fieldset>
';

return '
<style>
div.status-box {
	float: left;
	min-width: 16px;
	min-height: 16px;
	margin: 1px;
	background-repeat: no-repeat;
	background-position: 0px 0px;
	opacity: 0.5;
	cursor: help;
	}
div.status-box.status-box-yes {
	opacity: 1;
	}
div.status-box.status-box-no {
	opacity: 1;
	}
div.status-box.status-http{
	background-image: url(//cdn.int1a.net/img/famfamfam/silk/world.png);
	}
div.status-box.status-http.status-box-yes{
	background-image: url(//cdn.int1a.net/img/famfamfam/silk/world_add.png);
	}
div.status-box.status-http.status-box-no{
	background-image: url(//cdn.int1a.net/img/famfamfam/silk/world_delete.png);
	}
div.status-box.status-file{
	background-image: url(//cdn.int1a.net/img/famfamfam/silk/server_link.png);
	}
div.status-box.status-file.status-box-yes{
	background-image: url(//cdn.int1a.net/img/famfamfam/silk/server_add.png);
	}
div.status-box.status-file.status-box-no{
	background-image: url(//cdn.int1a.net/img/famfamfam/silk/server_delete.png);
	}

</style>
<script>
function checkReachabilities(labels){
	$("tr.notice").each(function(){
		if(!$(this).data("url"))
			return;
		$.ajax({
			url: $(this).data("url"),
			type: "HEAD",
			context: this,
			success: function(){
				var box = $(this).find("td.status-http div.status-http");
				box.addClass("status-box-yes").attr("title", labels["online"]);
				$(this).removeClass("notice").addClass("success");
			},
			error: function(){
				var box = $(this).find("td.status-http div.status-http");
				box.addClass("status-box-no").attr("title", labels["offline"]);
				$(this).removeClass("notice").addClass("error");
			}
		});
	});
}
function loadTodos(){
	$("tr").each(function(){
		if(!$(this).data("url-todos") || $(this).data("check") == "no")
			return;
		$.ajax({
			url: $(this).data("url-todos") + "?format=json",
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
	labels = '.json_encode( $labelsStatusHttp ).';
	checkReachabilities(labels);
	loadTodos();
});
</script>
<div class="column-left-75">
	'.$panelList.'
</div>
';

?>
