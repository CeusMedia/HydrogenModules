<?php
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
	<li><b>'.count( $files ).'</b> Dateien</li>
	<li>in <b>'.count( $folders ).'</b> Ordner</li>
	<li><b>'.$downloads.'</b> Downloads</li>
	<li>letzer Upload vor <b>'.$helper->convert( $lastUpload, TRUE ).'</b></li>
	<li>letzer Download vor <b>'.$helper->convert( $lastDownload, TRUE ).'</b></li>
</ul>
';

?>