<?php
$w			= (object) $words['index'];
$helper		= new View_Helper_TimePhraser( $env );

$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );
if( $env->getModules()->get( 'UI_Font_FontAwesome' ) ){
	$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
}

$list		= '<div><small class="muted"><em>'.$w->noEntries.'</em></small></div>';
if( $documents ){
	$rows	= [];
	foreach( $documents as $entry ){
		$attributes	= array(
			'class'		=> 'btn btn-mini btn-danger pull-right',
			'href'		=> './manage/content/document/remove/?documentId='.base64_encode( $entry ).( $page ? '&page='.$page : '' ),
			'title'		=> $w->buttonRemove,
		);
		$remove		= UI_HTML_Tag::create( 'a', $iconRemove, $attributes );
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
	$colgroup	= UI_HTML_Elements::ColumnGroup( "", "140px", "80px", "40px" );
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$list		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array(
		'class'	=> 'table table-striped table-fixed',
		'id'	=> 'table-documents',
	) );
}


$buttonbar	= '';
if( $total > $limit ){
	$pagination	= new \CeusMedia\Bootstrap\PageControl( './manage/content/document', $page, ceil( $total / $limit ) );
	$buttonbar	= '<div class="buttonbar">'.$pagination.'</div>';
}

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
		'.$buttonbar.'
	</div>
</div>';
