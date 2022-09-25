<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['index.files'];

use CeusMedia\Bootstrap\Icon;

$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );
$iconDownload	= HtmlTag::create( 'i', '', array( 'class' => 'icon-arrow-down' ) );

$iconRemove		= new Icon( 'remove' );
$iconDownload	= new Icon( 'download' );


$list	= '<div class="alert alert-error">'.$w->noEntries.'</div>';


if( $files ){
	$list	= [];
	foreach( $files as $file ){
		$link	= HtmlTag::create( 'a', $file->fileName, array(
			'href'		=> './admin/mail/attachment/download/'.urlencode( $file->fileName ),
			'title'		=> 'Datei herunterladen',
		) );
		$label	= HtmlTag::create( 'big', $file->fileName );
		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, array(
			'href'		=> './admin/mail/attachment/remove/'.urlencode( $file->fileName ),
			'class'		=> 'btn btn-small btn-danger',
		) );
		$buttonDownload	= HtmlTag::create( 'a', $iconDownload, array(
			'href'		=> './admin/mail/attachment/download/'.urlencode( $file->fileName ),
			'class'		=> 'btn btn-small',
		) );

		$mimeType	= HtmlTag::create( 'span', $w->labelMimeType.': '.$file->mimeType );
		$fileSize	= HtmlTag::create( 'span', $w->labelFileSize.': '.Alg_UnitFormater::formatBytes( filesize( $path.$file->fileName ) ) );
		$info		= HtmlTag::create( 'small', $fileSize.' | '.$mimeType, array( 'class' => 'muted' ) );

		$buttons	= array( $buttonDownload, $buttonRemove );
		$buttons	= HtmlTag::create( 'div', $buttons, array( 'class' => 'btn-group pull-right' ) );
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $label.'<br/>'.$info ),
			HtmlTag::create( 'td', $buttons ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "", "60px" );
	$thead	= HtmlTag::create( 'thead', '' );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );

	$list	= '
<div class="content-panel">
	<h3>Dateien</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
}
return $list;
