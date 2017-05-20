<?php
if( !in_array( 'scan', $rights ) )
	return '';
return '
<div class="content-panel">
	<h4>Dateien scannen</h4>
	<div class="content-panel-inner">
		<p>
			Wurden neue Dateien oder Ordner per FTP hochgeladen?
			Damit diese hier aufgelistet werden, müssen Sie den gesamten Dateienordner scannen.
		</p>
		<div class="buttonbar">
			<a href="./info/download/scan" class="btn btn-mini"><i class="icon-repeat"></i> nach neuen Dateien scannen</a>
		</div>
	</div>
</div>';
?>
