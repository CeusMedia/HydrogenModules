<?php
$list	= '<div><em class="muted">'.$words['list']['empty'].'</em></div><br/>';
if( $files ){
	$list	= array();
	foreach( $order as $entry ){
		$entry	= preg_replace( "/\.md$/", "", $entry );
		$link	= UI_HTML_Tag::create( 'a', $entry, array( 'href' => './info/manual/view/'.$view->urlencode( $entry ) ) );
		$list[]	= UI_HTML_Tag::create( 'li', $link );
	}
	$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked' ) );
}

$buttonAdd		= "";
$buttonReload	= "";
if( $moduleConfig->get( 'editor' ) ){
	$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
	$iconReload		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-refresh' ) );
	if( in_array( 'add', $rights ) )
		$buttonAdd		= UI_HTML_Tag::create( 'a', $iconAdd.' '.$words['list']['buttonAdd'], array( 'href' => './info/manual/add', 'class' => "btn btn-small btn-info" ) );
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
	<div class="span9">
		<h3>'.$words['edit']['heading'].'</h3>
		<form action="./info/manual/add" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_content">'.$words['edit']['labelContent'].'</label>
					<textarea class="span12 CodeMirror" name="content" id="input_content" rows="'.$moduleConfig->get( 'editor.rows' ).'">'.$content.'</textarea>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_filename">'.$words['edit']['labelFilename'].'</label>
					<input type="text" name="filename" class="span12" value="'.htmlentities( $filename, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
				</div>
				<div class="span4">
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> '.$words['edit']['buttonSave'].'</button>
			</div>
		</form>
	</div>
</div>';
?>