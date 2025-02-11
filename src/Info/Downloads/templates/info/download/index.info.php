<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var array $folders */
/** @var array $files */
/** @var array $words */

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

return '
<h4>Informationen</h4>
<ul>
	<li>'.sprintf( $w->filesInFolders, count( $files ), count( $folders ) ).'</li>
	<li>'.sprintf( $w->downloads, $downloads ).'</li>
	<li>'.sprintf( $w->lastUpload, $helper->convert( $lastUpload, TRUE ) ).'</li>
	<li>'.sprintf( $w->lastDownload, $helper->convert( $lastDownload, TRUE ) ).'</li>
</ul>
';
