<?php
$w	= (object) $words['index.files'];

$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );
$iconDownload	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-down' ) );

$list	= '<div class="alert alert-error">'.$w->noEntries.'</div>';


if( $files ){
	$list	= array();
	foreach( $files as $file ){
		$link	= UI_HTML_Tag::create( 'a', $file->fileName, array(
			'href'		=> './admin/mail/attachment/download/'.urlencode( $file->fileName ),
			'title'		=> 'Datei herunterladen',
		) );
		$label	= UI_HTML_Tag::create( 'big', $file->fileName );
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'		=> './admin/mail/attachment/remove/'.urlencode( $file->fileName ),
			'class'		=> 'btn btn-mini btn-danger',
		) );
		$buttonDownload	= UI_HTML_Tag::create( 'a', $iconDownload, array(
			'href'		=> './admin/mail/attachment/download/'.urlencode( $file->fileName ),
			'class'		=> 'btn btn-mini',
		) );

		$mimeType	= UI_HTML_Tag::create( 'span', $w->labelMimeType.': '.$file->mimeType );
		$fileSize	= UI_HTML_Tag::create( 'span', $w->labelFileSize.': '.Alg_UnitFormater::formatBytes( filesize( $path.$file->fileName ) ) );
		$info		= UI_HTML_Tag::create( 'small', $fileSize.' | '.$mimeType, array( 'class' => 'muted' ) );

		$buttons	= array( $buttonDownload, $buttonRemove );
		$buttons	= UI_HTML_Tag::create( 'div', $buttons, array( 'class' => 'btn-group pull-right' ) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $label.'<br/>'.$info ),
			UI_HTML_Tag::create( 'td', $buttons ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "", "60px" );
	$thead	= UI_HTML_Tag::create( 'thead', '' );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );

	$list	= '
<div class="content-panel">
	<h3>Dateien</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
}
return $list;
