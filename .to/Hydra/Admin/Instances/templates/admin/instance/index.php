<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['index'];

//	@todo		extract labels to locale
$labelsStatusHttp	= [
	'unchecked'		=> $words['availability']['unchecked'],
	'online'		=> $words['availability']['online'],
	'offline'		=> $words['availability']['offline']
];

function formatUrl( $url ){
	$parts	= parse_url( $url );
	$parts['host']	.= !empty( $parts['port'] ) ? ":".$parts['port'] : "";
//	remark( $url );
//	print_m( $parts );
	$scheme	= HtmlTag::create( 'small', strtoupper( $parts['scheme'] ), ['class' => 'muted'] );
	$host	= HtmlTag::create( 'strong', "&nbsp;&nbsp;".$parts['host'], ['class' => ''] );
	$path	= explode( "/", preg_replace( "/^(\/*)(.*)(\/+)$/", "\\2", $parts['path'] ) );
	$main	= HtmlTag::create( 'strong', array_pop( $path ), ['class' => ''] );
	$path	= str_replace( "//", "/", "/".implode( "/", $path )."/" );
	$path	= HtmlTag::create( 'small', $path, ['class' => 'muted'] );
	$path	.= "&nbsp;&nbsp;".$main;
	$path	= HtmlTag::create( 'span', "&nbsp;&nbsp;".$path, ['class' => ''] );
	return $scheme.$host.$path;
}

$rows	= [];
foreach( $instances as $instanceId => $instance ){
	$instance->host     = $instance->host === "localhost" ? $env->host . ( $env->port && $env->port != 80 ? ":".$env->port : "" ) : $instance->host;
	$instance->protocol	= empty( $instance->protocol ) ? 'http://' : $instance->protocol;
	$link			= HtmlElements::Link( './admin/instance/edit/'.$instanceId, $instance->title, 'instance' );
	$url			= $instance->protocol.$instance->host.$instance->path;
	$uriExists		= file_exists( $instance->uri );
	$linkInstance	= HtmlTag::create( 'a', formatUrl( $url ), ['href' => $url] );
	$codeUri		= HtmlTag::create( 'code', $instance->uri );
	$codeUri		= HtmlTag::create( 'small', $codeUri, ['class' => 'muted'] );
	$titleStatus	= $uriExists ? "Checked and found on file system" : "NOT FOUND on file system (not installed or path invalid)";
	$titleStatus	= $uriExists ? "Ordner auf dem Server gefunden" : "Ordner NICHT GEFUNDEN (nicht installiert oder ungültiger Pfad)";
	$indicators		= join( "", array(
		'<div class="status-file status-box status-box-'.( $uriExists ? 'half' : 'no' ).'" title="'.$titleStatus.'"></div>',
		'<div class="status-http status-box" title="'.$labelsStatusHttp['unchecked'].'"></div>',
	) );
	$cells	= array(
		HtmlTag::create( 'td', $link, ['class' => 'instance-label'] ),
		HtmlTag::create( 'td', $linkInstance/*.'<br/>'.$codeUri*/ ),
		HtmlTag::create( 'td', $indicators, ['class' => 'status-http'] ),
		HtmlTag::create( 'td', "", ['class' => 'status-todos'] ),
	);
	$hasTodoTool	= isset( $instance->checkTodos ) && $instance->checkTodos ? "yes" : "no";
	$attributes		= [
		'class'				=> $uriExists ? '' : 'error',
		'data-check'		=> $hasTodoTool,
		'data-url'			=> $url,
		'data-url-todos'	=> $url.'tools/Todos/',
	];
	$rows[$instance->title]	= HtmlTag::create( 'tr', $cells, $attributes );
}
ksort( $rows );

$panelList	= '
<fieldset>
	<legend>'.$w->legend.'</legend>
	<table>
		<tr><th>'.$w->headTitle.'</th><th>'.$w->headAddress.'</th><th>'.$w->headAvailability.'</th><th>'.$w->headTasks.'</th></tr>
		'.join( $rows ).'
	</table>
	'.HtmlElements::LinkButton( './admin/instance/add', $w->buttonAdd, 'button add' ).'
	'.HtmlTag::create( 'button', 'check', ['class' => 'button', 'id' => 'button_check'] ).'
</fieldset>';

return '
<script>
$(document).ready(function(){
	ModuleAdminInstancesIndex.labelsReachabilities = '.json_encode( $labelsStatusHttp ).';
	$("#button_check").on("click", function(){
		ModuleAdminInstancesIndex.checkReachabilities();
	//	ModuleAdminInstancesIndex.loadTodos();
	});
});
</script>
<div class="column-left-75">
	'.$panelList.'
</div>
';

?>
