<?php

/** @var View_Manage_Content_Style $view */
/** @var array<string,array<string,string>> $words  */
/** @var array $files */
/** @var ?string $file */

$w			= (object) $words['index.filter'];
$files		= $view->listFiles( $files, $file );

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$files.'
	</div>
</div>';
