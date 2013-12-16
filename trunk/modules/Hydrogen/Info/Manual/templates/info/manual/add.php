<?php
$list	= '<div><em class="muted">Keine Dokumente</em></div><br/>';
if( $files ){
	$list	= array();
	foreach( $order as $entry ){
		$entry	= preg_replace( "/\.md$/", "", $entry );
		$link	= UI_HTML_Tag::create( 'a', $entry, array( 'href' => './info/manual/view/'.$view->urlencode( $entry ) ) );
		$list[]	= UI_HTML_Tag::create( 'li', $link );
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
		<h3>Editor</h3>
		<form action="./info/manual/add" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_content">Inhalt</label>
					<textarea class="span12 CodeMirror" name="content" id="input_content" rows="'.$moduleConfig->get( 'editor.rows' ).'">'.$content.'</textarea>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_filename">Dateiname</label>
					<input type="text" name="filename" class="span12" value="'.htmlentities( $filename, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
				</div>
				<div class="span4">
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> speichern</button>
			</div>
		</form>
	</div>
</div>';

?>
