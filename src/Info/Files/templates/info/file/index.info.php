<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array $words */
/** @var array $files */
/** @var array $folders */

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

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h4', 'Informationen' ),
	HtmlTag::create( 'div', $facts, ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );
