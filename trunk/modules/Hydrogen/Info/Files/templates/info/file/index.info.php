<?php
$w	= (object) $words['info'];



$helper			= new View_Helper_TimePhraser( $env );
$lastUpload		= 0;
$lastDownload	= 0;
$downloads		= 0;
foreach( $files as $entry ){
	$lastUpload		= max( $lastUpload, $entry->timestamp );
	$lastDownload	= max( $lastDownload, $entry->downloaded );
	$downloads		+= $entry->downloads;
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

?>