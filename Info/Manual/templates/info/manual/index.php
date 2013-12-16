<?php
$list	= '<div><em class="muted">'.$words['list']['empty'].'</em></div><br/>';
if( $files ){
	$list	= array();
	foreach( $files as $entry ){
		$entry	= preg_replace( "/\.md$/", "", $entry );
		$link	= UI_HTML_Tag::create( 'a', $entry, array( 'href' => './info/manual/view/'.$view->urlencode( $entry ) ) );
		$class	= $file == $entry ? 'active' : '';
		$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
	}
	$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked' ) );
}

$buttonAdd	= "";
$buttonReload	= "";
if( $moduleConfig->get( 'editor' ) ){
	$icon			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
	$buttonAdd		= UI_HTML_Tag::create( 'a', $icon.' '.$words['list']['buttonAdd'], array( 'href' => './info/manual/add', 'class' => "btn btn-small btn-primary" ) );
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
</div>';
?>
