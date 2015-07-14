<?php

$listFolders	= $view->listFolders( dirname( $imagePath ) );
$panelFolders	= '
<div class="content-panel">
	<h4>Ordner</h4>
	<div class="content-panel-inner">
		'.$listFolders.'
		<div class="buttonbar">
			<a href="./manage/content/image/addFolder?path='.$path.'" class="btn btn-info btn-small"><i class="icon-plus icon-white"></i> neuer Ordner</a>
		</div>
	</div>
</div>';

return $panelFolders;
?>
