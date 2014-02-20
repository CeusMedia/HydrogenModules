<?php
$list	= '<div><em class="muted">Keine Dokumente</em></div><br/>';
if( $files ){
	$list	= array();
	foreach( $order as $entry ){
		$entry	= preg_replace( "/\.md$/", "", $entry );
		$link	= UI_HTML_Tag::create( 'a', $entry, array( 'href' => './info/manual/view/'.$view->urlencode( $entry ) ) );
		$class	= $file == $entry ? 'active' : '';
		$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
	}
	$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked' ) );
}

$buttonAdd		= "";
$buttonEdit		= "";
$buttonReload	= "";
if( $moduleConfig->get( 'editor' ) ){
	$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
	$iconEdit		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) );
	$iconReload		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-refresh' ) );
	if( in_array( 'add', $rights ) )
		$buttonAdd		= UI_HTML_Tag::create( 'a', $iconAdd.' '.$words['list']['buttonAdd'], array( 'href' => './info/manual/add', 'class' => "btn btn-small btn-info" ) );
	if( in_array( 'edit', $rights ) )
		$buttonEdit		= UI_HTML_Tag::create( 'a', $iconEdit.' '.$words['view']['buttonEdit'], array( 'href' => './info/manual/edit/'.base64_encode( $file), 'class' => "btn btn-small" ) );
	if( in_array( 'reload', $rights ) )
		$buttonReload	= UI_HTML_Tag::create( 'a', $iconReload.' '.$words['list']['buttonReload'], array( 'href' => './info/manual/reload', 'class' => "btn btn-small" ) );
}

return '
<div class="row-fluid">
	<div class="span3">
		<h3>'.$words['list']['heading'].'</h3>
		'.$list.'
		'.$buttonAdd.'
		'.$buttonReload.'
	</div>
	<div class="span9" style="position: relative">
		<div id="content-index">
			<div class="heading">Inhalt</div>
		</div>
		<div class="markdown" id="content-container"style="display: none">
'.$content.'
		</div>
		<div class="buttonbar">
			'.$buttonEdit.'
		</div>
		<br/>
	</div>
</div>
<script>
$(document).ready(function(){
	InfoManual.init("#content-container", "#content-index");
});
</script>
<style>
#content-index {
	display: none;
	float: right;
	width: 18em;
	min-height: 50px;
	max-height: 70%;
	margin: 10px 0px 10px 10px;
	padding: 0.3em 0.5em;
	background-color: rgb(255, 255, 255);
	border-width: 1px 1px 1px 1px;
	border-style: solid;
	border-color: gray;
	box-shadow: 0px 0px 2px 2px rgba(0,0,0,0.10);
	overflow-x: hidden;
	overflow-y: auto;
	}
#content-index .heading {
	font-size: 1.2em;
	padding: 0.5em 0.5em 0.25em 0.5em;
	border-bottom: 1px solid #DDD;
	margin-bottom: 0.5em;
	background-color: white;
	}
#content-index ul {
	margin: 0;
	padding: 0;
	list-style: none;
	}
#content-index li {
	margin: 0;
	padding: 0;
	}
#content-index li a {
	display: block;
	text-overflow: ellipsis;
	white-space: nowrap;
	overflow: hidden;
	line-height: 1.5em;
	}

/*  --  RENDER STYLE: LIST  --  */
#content-index ul.index-list li {
	line-height: 1.3em;
	}
#content-index ul.index-list li.level-1 {
	margin-left: 0em;
	}
#content-index ul.index-list li.level-1 a {
	font-size: 1.4em;
	color: #228;
	}
#content-index ul.index-list li.level-2 {
	margin-left: 0.75em;
	}
#content-index ul.index-list li.level-2 a {
	font-size: 1.2em;
	color: #339;
	}
#content-index ul.index-list li.level-3 {
	margin-left: 1.5em;
	}
#content-index ul.index-list li.level-3 a {
	font-size: 1.1em;
	color: #44A;
	}
#content-index ul.index-list li.level-4 {
	margin-left: 2.25em;
	}
#content-index ul.index-list li.level-4 a {
	font-size: 1em;
	color: #55B;
	}
#content-index ul.index-list li.level-5 {
	margin-left: 3em;
	}
#content-index ul.index-list li.level-5 a {
	font-size: 0.9em;
	color: #66C;
	}
</style>
';
?>
