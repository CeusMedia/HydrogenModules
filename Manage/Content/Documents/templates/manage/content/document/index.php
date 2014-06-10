<?php

$helper	= new View_Helper_TimePhraser( $env );

$list	= '<div><small class="muted"><em>Keine.</em></small></div>';
if( $documents ){
	$rows	= array();
	foreach( $documents as $entry ){
		$attributes	= array(
			'class'		=> 'btn btn-mini btn-danger pull-right',
			'href'		=> './manage/content/document/remove/?documentId='.base64_encode( $entry ),
			'title'		=> $words['index']['buttonRemove'],
		);
		$remove	= UI_HTML_Tag::create( 'a', '<i class="icon-remove icon-white"></i>', $attributes );
		$link	= $frontendPath.$pathDocuments.$entry;
		$link	= UI_HTML_Tag::create( 'a', $entry, array( 'href' => $link ) );
		$rows[]	= UI_HTML_Tag::create( 'tr',
			UI_HTML_Tag::create( 'td', $link ).
			UI_HTML_Tag::create( 'td', $helper->convert( filemtime( $frontendPath.$pathDocuments.$entry ) ) ).
			UI_HTML_Tag::create( 'td', Alg_UnitFormater::formatBytes( filesize( $frontendPath.$pathDocuments.$entry ) ) ).
			UI_HTML_Tag::create( 'td', in_array( 'remove', $rights ) ? $remove : '' )
		);
	}
	$thead	= UI_HTML_Tag::create( 'thead',
		UI_HTML_Tag::create( 'tr',
			UI_HTML_Tag::create( 'th', 'Titel').
			UI_HTML_Tag::create( 'th', 'Upload vor' ).
			UI_HTML_Tag::create( 'th', 'Größe' ).
			UI_HTML_Tag::create( 'th', '' )
		)
	);
	$colgroup	= UI_HTML_Elements::ColumnGroup( "67%", "15%", "10%", "8%" );
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$list		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-condensed table-striped' ) );
}

$optFilename	= array( '' => '' );
foreach( $documents as $entry )
	$optFilename[$entry]	= $entry;
$optFilename	= UI_HTML_Elements::Options( $optFilename );

$panelAdd	= '';
if( in_array( 'add', $rights ) ){
	$panelAdd	= '
	<div class="span5">
		<h3>'.$words['add']['heading'].'</h3>
		<form action="./manage/content/document/add" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_upload">'.$words['add']['labelFile'].'</label>
					<input type="file" name="upload" id="input_upload"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_filename">'.$words['add']['labelFilename'].'</label>
					<select name="filename" id="input_filename">'.$optFilename.'</select>
				</div>
				<div class="buttonbar">
					<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-plus icon-white"></i> '.$words['add']['buttonSave'].'</button>
				</div>
			</div>
		</form>
	</div>';
}

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/manage/content/document/' ) );

return $textTop.'
<div class="row-fluid">
	<div class="span7">
		<h3>'.$words['index']['heading'].'</h3>
		'.$list.'
	</div>
	'.$panelAdd.'
</div>'.$textBottom;
?>