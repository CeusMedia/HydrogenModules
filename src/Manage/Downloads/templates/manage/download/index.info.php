<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as Environment;

/** @var Environment $env */
/** @var array<int|string,string> $words */
/** @var array<object> $files */
/** @var array<object> $folders */

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

return join( [
	HtmlTag::create( 'h4', 'Informationen' ),
	HtmlTag::create( 'ul', [
		HtmlTag::create( 'li', sprintf( $w->filesInFolders, count( $files ), count( $folders ) ) ),
		HtmlTag::create( 'li', sprintf( $w->downloads, $downloads ) ),
		HtmlTag::create( 'li', sprintf( $w->lastUpload, $helper->convert( $lastUpload, TRUE ) ) ),
		HtmlTag::create( 'li', sprintf( $w->lastDownload, $helper->convert( $lastDownload, TRUE ) ) ),
	] )
] );