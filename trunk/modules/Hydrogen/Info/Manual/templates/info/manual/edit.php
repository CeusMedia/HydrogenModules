<?php
$list	= '<div><em class="muted">Keine Dokumente</em></div><br/>';
if( $files ){
	$list	= array();
	foreach( $order as $entry ){
		$entry	= preg_replace( "/\.md$/", "", $entry );
		$link	= UI_HTML_Tag::create( 'a', $entry, array( 'href' => './info/manual/edit/'.base64_encode( $entry ) ) );
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
		<h3><span class="muted">Editor: </span> '.htmlentities( $file, ENT_QUOTES, 'UTF-8' ).'</h3>
		<form action="./info/manual/edit/'.base64_encode( $file ).'" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_content">Inhalt</label>
					<textarea class="span12 CodeMirror" name="content" id="input_content" rows="'.$moduleConfig->get( 'editor.rows' ).'">'.$content.'</textarea>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_filename">Dateiname</label>
					<input type="text" name="filename" class="span12" value="'.htmlentities( $file, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span4">
					<label>&nbsp;</label>
					<label class="checkbox">
						<input type="checkbox" name="backup"/>
						vorherige Version sichern
					</label>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./info/manual/view/'.$view->urlencode( $file ).'" class="btn btn-small"><i class="icon-arrow-left"></i> view</a>
				<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> save</button>
				<a href="./info/manual/remove/'.base64_encode( $file ).'" class="btn btn-small btn-danger" onclick="return confirm(\'Wirklich ?\');"><i class="icon-remove icon-white"></i> entfernen</a>
				&nbsp;&nbsp;|&nbsp;&nbsp;
				<a href="./info/manual/moveUp/'.base64_encode( $file ).'" class="btn btn-small"><i class="icon-arrow-up"></i> hoch</a>
				<a href="./info/manual/moveDown/'.base64_encode( $file ).'" class="btn btn-small"><i class="icon-arrow-down"></i> runter</a>
			</div>
		</form>
	</div>
</div>';

?>
