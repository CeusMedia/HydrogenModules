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
	$icon			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
	$buttonAdd		= UI_HTML_Tag::create( 'a', $icon.' '.$words['list']['buttonAdd'], array( 'href' => './info/manual/add', 'class' => "btn btn-small btn-info" ) );
	$icon			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) );
	$buttonEdit		= UI_HTML_Tag::create( 'a', $icon.' '.$words['view']['buttonEdit'], array( 'href' => './info/manual/edit/'.base64_encode( $file), 'class' => "btn btn-small" ) );
	$icon			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-refresh' ) );
	$buttonReload	= UI_HTML_Tag::create( 'a', $icon.' '.$words['list']['buttonReload'], array( 'href' => './info/manual/reload', 'class' => "btn btn-small" ) );
}

return '
<div class="row-fluid">
	<div class="span3">
		<h3>'.$words['list']['heading'].'</h3>
		'.$list.'
		'.$buttonAdd.'
		'.$buttonReload.'
	</div>
	<div class="span9">
		<div class="markdown" style="display: none">'.$content.'</div>
		<div class="buttonbar">
			'.$buttonEdit.'
		</div>
	</div>
</div>';
?>
