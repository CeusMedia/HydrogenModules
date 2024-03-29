<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['info'];

$helper			= new View_Helper_TimePhraser( $env );
$lastUpload		= 0;
$lastDownload	= 0;
$downloads		= 0;
foreach( $files as $entry ){
	$lastUpload		= max( $lastUpload, $entry->uploadedAt );
	$lastDownload	= max( $lastDownload, $entry->downloadedAt );
	$downloads		+= $entry->nrDownloads;
}

$word	= count( $folders ) ? $w->filesInFolders : $w->files;

$facts		= [];
$facts[]	= sprintf( $word, count( $files ), count( $folders ) );
if( $downloads > 6 )
	$facts[]	= sprintf( $w->downloads, $downloads );
if( $lastUpload )
	$facts[]	= sprintf( $w->lastUpload, $helper->convert( $lastUpload, TRUE ) );
if( $lastDownload )
	$facts[]	= sprintf( $w->lastDownload, $helper->convert( $lastDownload, TRUE ) );
foreach( $facts as $nr => $fact )
	$facts[$nr]	= HtmlTag::create( 'li', $fact );
$facts	= HtmlTag::create( 'ul', $facts );

return '
<div class="content-panel">
	<h4>Informationen</h4>
	<div class="content-panel-inner">
		'.$facts.'
	</div>
</div>';
