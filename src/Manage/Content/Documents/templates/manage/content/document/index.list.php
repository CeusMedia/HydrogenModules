<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w			= (object) $words['index'];
$helper		= new View_Helper_TimePhraser( $env );

$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'icon-remove icon-white'] );
if( $env->getModules()->get( 'UI_Font_FontAwesome' ) ){
	$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
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
		$remove		= HtmlTag::create( 'a', $iconRemove, $attributes );
		$filePath	= $frontendPath.$pathDocuments.$entry;
		$link		= HtmlTag::create( 'a', $entry, array(
			'href'		=> $filePath,
			'target'	=> '_blank'
		) );
		$uploadedAt	= $helper->convert( filemtime( $filePath ), TRUE, $w->timePhrasePrefix, $w->timePhraseSuffix );
		$size		= Alg_UnitFormater::formatBytes( filesize( $filePath ) );
		$actions	= in_array( 'remove', $rights ) ? $remove : '';
		$rows[]		= HtmlTag::create( 'tr',
			HtmlTag::create( 'td', $link, ['class' => 'cell-title autocut'] ).
			HtmlTag::create( 'td', $uploadedAt, ['class' => 'cell-timestamp'] ).
			HtmlTag::create( 'td', $size, ['class' => 'cell-size'] ).
			HtmlTag::create( 'td', $actions, ['class' => 'cell-actions'] )
		);
	}
	$thead	= HtmlTag::create( 'thead',
		HtmlTag::create( 'tr',
			HtmlTag::create( 'th', $w->headTitle ).
			HtmlTag::create( 'th', $w->headUploaded ).
			HtmlTag::create( 'th', $w->headSize ).
			HtmlTag::create( 'th', $w->headActions )
		)
	);
	$colgroup	= HtmlElements::ColumnGroup( "", "140px", "80px", "40px" );
	$tbody		= HtmlTag::create( 'tbody', $rows );
	$list		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array(
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
