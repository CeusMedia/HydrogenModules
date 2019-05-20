<?php

$w			= (object) $words['index.filter'];
$files		= $view->listFiles( $files, $file );

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$files.'
	</div>
</div>';
?>
