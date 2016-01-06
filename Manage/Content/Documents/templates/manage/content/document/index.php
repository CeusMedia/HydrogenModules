<?php
$w		= (object) $words['index'];

$w			= (object) $words['index'];
$helper		= new View_Helper_TimePhraser( $env );

$list		= '<div><small class="muted"><em>'.$w->noEntries.'</em></small></div>';
if( $documents ){
	$rows	= array();
	foreach( $documents as $entry ){
		$attributes	= array(
			'class'		=> 'btn btn-mini btn-danger pull-right',
			'href'		=> './manage/content/document/remove/?documentId='.base64_encode( $entry ),
			'title'		=> $w->buttonRemove,
		);
		$remove		= UI_HTML_Tag::create( 'a', '<i class="icon-remove icon-white"></i>', $attributes );
		$filePath	= $frontendPath.$pathDocuments.$entry;
		$link		= UI_HTML_Tag::create( 'a', $entry, array(
			'href'		=> $filePath,
			'target'	=> '_blank'
		) );
		$uploadedAt	= $helper->convert( filemtime( $filePath ), TRUE, $w->timePhrasePrefix, $w->timePhraseSuffix );
		$size		= Alg_UnitFormater::formatBytes( filesize( $filePath ) );
		$actions	= in_array( 'remove', $rights ) ? $remove : '';
		$rows[]		= UI_HTML_Tag::create( 'tr',
			UI_HTML_Tag::create( 'td', $link, array( 'class' => 'cell-title autocut' ) ).
			UI_HTML_Tag::create( 'td', $uploadedAt, array( 'class' => 'cell-timestamp' ) ).
			UI_HTML_Tag::create( 'td', $size, array( 'class' => 'cell-size' ) ).
			UI_HTML_Tag::create( 'td', $actions, array( 'class' => 'cell-actions' ) )
		);
	}
	$thead	= UI_HTML_Tag::create( 'thead',
		UI_HTML_Tag::create( 'tr',
			UI_HTML_Tag::create( 'th', $w->headTitle ).
			UI_HTML_Tag::create( 'th', $w->headUploaded ).
			UI_HTML_Tag::create( 'th', $w->headSize ).
			UI_HTML_Tag::create( 'th', $w->headActions )
		)
	);
	$colgroup	= UI_HTML_Elements::ColumnGroup( "", "120px", "70px", "40px" );
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$list		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array(
		'class'	=> 'table table-condensed table-striped',
		'id'	=> 'table-documents',
	) );
}
$panelList	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';

$panelAdd	= $view->loadTemplateFile( 'manage/content/document/index.add.php' );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/manage/content/document/' ) );

return $textTop.'
<div class="row-fluid">
	<div class="span8">
		'.$panelList.'
	</div>
	<div class="span4">
		'.$panelAdd.'
	</div>
</div>
'.$textBottom.'
<style>
#table-documents {
	table-layout: fixed;
	}
td.cell-timestamp,
td.cell-size {
	font-size: 0.9em;
	}
td.cell-size {
	text-align: right;
	}
</style>';
?>
