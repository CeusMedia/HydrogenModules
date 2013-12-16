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

return '
<div class="row-fluid">
	<div class="span3">
		<h3>Dokumente</h3>
		'.$list.'
		<a href="./info/manual/add" class="btn btn-small btn-primary"><i class="icon-plus icon-white"></i> neues Dokument</a>
	</div>
	<div class="span9">
		<div class="markdown" style="display: none">'.$content.'</div>
		<div class="buttonbar">
			<a href="./info/manual/edit/'.base64_encode( $file).'" class="btn btn-small"><i class="icon-pencil"></i> ändern</a>
		</div>
	</div>
</div>';
?>
