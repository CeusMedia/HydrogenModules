<?php

$list	= '<div><small class="muted"><em>Keine.</em></small></div>';
if( $documents ){
	$rows	= array();
	foreach( $documents as $entry ){
		$attributes	= array(
			'class'		=> 'btn btn-mini btn-danger pull-right',
			'href'		=> './manage/document/remove/'.$entry
		);
		$remove	= UI_HTML_Tag::create( 'a', '<i class="icon-remove icon-white"></i>', $attributes );
		$link	= '../documents/'.$entry;
		$link	= UI_HTML_Tag::create( 'a', $entry, array( 'href' => $link ) );
		$rows[]	= UI_HTML_Tag::create( 'tr',
			UI_HTML_Tag::create( 'td', $link ).
			UI_HTML_Tag::create( 'td', $remove )
		);
	}
	$thead	= UI_HTML_Tag::create( 'thead',
		UI_HTML_Tag::create( 'tr',
			UI_HTML_Tag::create( 'th', 'Titel').
			UI_HTML_Tag::create( 'th', '' )
		)
	);

	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$list	= UI_HTML_Tag::create( 'table', $thead.$tbody, array( 'class' => 'table table-condensed table-striped' ) );
}

return '
'.$this->renderTabs().'
<div class="row-fluid">
	<div class="span6">
		<h3>Dokumente</h3>
		'.$list.'
	</div>
	<div class="span6">
		<h3>Dokument hinzufügen</h3>
		<form action="./manage/content/document/add" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_upload">Datei</label>
					<input type="file" name="upload" id="input_upload"/>
				</div>
				<div class="buttonbar">
					<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-plus icon-white"></i> hochladen</button>
				</div>
			</div>
		</form>
	</div>
<!--	<div class="span3">
		<h4>Info</h4>
		...
	</div>-->
</div>
';

?>
