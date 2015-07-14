<?php

$listFolders	= $view->listFolders( $path );
$listImages		= $view->listImages( $path, 120, 80 );
if( !$listImages )
	$listImages		= '<div><em><small class="muted">Keine Bilder in diesem Ordner gefunden.</small></em></div>';

$linkEditFolder	= '';
if( $path != "." )
	$linkEditFolder	= '&nbsp;&nbsp;<a class="btn btn-mini" href="./manage/content/image/editFolder?path='.$path.'" title="ändern" alt="ändern"><i class="icon-pencil"></i></a>';

return '
<script>
$(document).ready(function(){
	$(".thumbs>li").bind("click", function(){
		var url = "./manage/content/image/editImage?path="+$(this).data("image-path");
		document.location.href = url;
	});
});
</script>
<div class="row-fluid">
	<div class="span3">
		<div class="content-panel">
			<h4>Ordner</h4>
			<div class="content-panel-inner">
				'.$listFolders.'
				<div class="buttonbar">
					<a href="./manage/content/image/addFolder?path='.$path.'" class="btn btn-info btn-small"><i class="icon-plus icon-white"></i> neuer Ordner</a>
				</div>
			</div>
		</div>
	</div>
	<div class="span9">
		<div class="content-panel">
			<h4><span class="muted">Ordner: </span>'.$path.$linkEditFolder.'</h4>
			<div class="content-panel-inner">
				<div style="position: not-relative">
					'.$listImages.'
					<div style="clear: left"></div>
				</div>
				<div class="buttonbar">
					<a href="./manage/content/image/addImage?path='.$path.'" class="btn btn-info btn-small"><i class="icon-plus icon-white"></i> neues Bild hochladen</a>
				</div>
			</div>
		</div>
	</div>
</div>
';
?>
