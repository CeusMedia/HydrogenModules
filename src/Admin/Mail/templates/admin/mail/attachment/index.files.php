<?php

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var string $path */
/** @var string[] $files */

$w	= (object) $words['index.files'];

$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'icon-remove icon-white'] );
$iconDownload	= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-down'] );

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
		$fileSize	= HtmlTag::create( 'span', $w->labelFileSize.': '.UnitFormater::formatBytes( filesize( $path.$file->fileName ) ) );
		$info		= HtmlTag::create( 'small', $fileSize.' | '.$mimeType, ['class' => 'muted'] );

		$buttons	= [$buttonDownload, $buttonRemove];
		$buttons	= HtmlTag::create( 'div', $buttons, ['class' => 'btn-group pull-right'] );
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $label.'<br/>'.$info ),
			HtmlTag::create( 'td', $buttons ),
		) );
	}
	$colgroup	= HtmlElements::ColumnGroup( "", "60px" );
	$thead	= HtmlTag::create( 'thead', '' );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped'] );

	$list	= '
<div class="content-panel">
	<h3>Dateien</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
}
return $list;
