<?php
if( !in_array( 'scan', $rights ) )
	return '';
return '
<h4>Dateien scannen</h4>
<p>
	Wurden neue Dateien oder Ordner per FTP hochgeladen?
	Damit diese hier aufgelistet werden, müssen Sie den gesamten Dateienordner scannen.
</p>
<a href="./info/file/scan" class="btn btn-mini"><i class="icon-repeat"></i> nach neuen Dateien scannen</a>
';
?>
