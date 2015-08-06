<?php

$w				= (object) $words['index.list'];
$listFolders	= $view->listFolders( $path );

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h4>
	<div class="content-panel-inner">
		'.$listFolders.'
		<div class="buttonbar">
			<a href="./manage/content/image/addFolder" class="btn btn-info btn-small not-btn-info btn-success"><i class="icon-plus icon-white"></i>&nbsp;'.$w->buttonAddFolder.'</a>
		</div>
	</div>
</div>';
?>
