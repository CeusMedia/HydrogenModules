<?php

$w	= (object) $words['index'];

//	@todo		extract labels to locale
$labelsStatusHttp	= array(
	'unchecked'		=> $words['availability']['unchecked'],
	'online'		=> $words['availability']['online'],
	'offline'		=> $words['availability']['offline']
);

function formatUrl( $url ){
	$parts	= parse_url( $url );
	$parts['host']	.= !empty( $parts['port'] ) ? ":".$parts['port'] : "";
//	remark( $url );
//	print_m( $parts );
	$scheme	= UI_HTML_Tag::create( 'small', strtoupper( $parts['scheme'] ), array( 'class' => 'muted' ) );
	$host	= UI_HTML_Tag::create( 'strong', "&nbsp;&nbsp;".$parts['host'], array( 'class' => '' ) );
	$path	= explode( "/", preg_replace( "/^(\/*)(.*)(\/+)$/", "\\2", $parts['path'] ) );
	$main	= UI_HTML_Tag::create( 'strong', array_pop( $path ), array( 'class' => '' ) );
	$path	= str_replace( "//", "/", "/".implode( "/", $path )."/" );
	$path	= UI_HTML_Tag::create( 'small', $path, array( 'class' => 'muted' ) );
	$path	.= "&nbsp;&nbsp;".$main;
	$path	= UI_HTML_Tag::create( 'span', "&nbsp;&nbsp;".$path, array( 'class' => '' ) );
	return $scheme.$host.$path;
}



$rows	= array();
foreach( $instances as $instanceId => $instance ){
	$instance->host     = $instance->host === "localhost" ? $env->host . ( $env->port && $env->port != 80 ? ":".$env->port : "" ) : $instance->host;
	$instance->protocol	= empty( $instance->protocol ) ? 'http://' : $instance->protocol;
	$link			= UI_HTML_Elements::Link( './admin/instance/edit/'.$instanceId, $instance->title, 'instance' );
	$url			= $instance->protocol.$instance->host.$instance->path;
	$uriExists		= file_exists( $instance->uri );
	$linkInstance	= UI_HTML_Tag::create( 'a', formatUrl( $url ), array( 'href' => $url ) );
	$codeUri		= UI_HTML_Tag::create( 'code', $instance->uri );
	$codeUri		= UI_HTML_Tag::create( 'small', $codeUri, array( 'class' => 'muted' ) );
	$titleStatus	= $uriExists ? "Checked and found on file system" : "NOT FOUND on file system (not installed or path invalid)";
	$titleStatus	= $uriExists ? "Ordner auf dem Server gefunden" : "Ordner NICHT GEFUNDEN (nicht installiert oder ungültiger Pfad)";
	$indicators		= join( "", array(
		'<div class="status-file status-box status-box-'.( $uriExists ? 'half' : 'no' ).'" title="'.$titleStatus.'"></div>',
		'<div class="status-http status-box" title="'.$labelsStatusHttp['unchecked'].'"></div>',
	) );
	$cells	= array(
		UI_HTML_Tag::create( 'td', $link, array( 'class' => 'instance-label' ) ),
		UI_HTML_Tag::create( 'td', $linkInstance.'<br/>'.$codeUri ),
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
	<legend>'.$w->legend.'</legend>
	<table>
		<tr><th>'.$w->headTitle.'</th><th>'.$w->headAddress.'</th><th>'.$w->headAvailability.'</th><th>'.$w->headTasks.'</th></tr>
		'.join( $rows ).'
	</table>
	'.UI_HTML_Elements::LinkButton( './admin/instance/add', $w->buttonAdd, 'button add' ).'
</fieldset>';

return '
<script>
$(document).ready(function(){
	labels = '.json_encode( $labelsStatusHttp ).';
	ModuleAdminInstances.checkReachabilities(labels);
	ModuleAdminInstances.loadTodos();
});
</script>
<div class="column-left-75">
	'.$panelList.'
</div>
';

?>
